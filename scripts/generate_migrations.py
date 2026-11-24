#!/usr/bin/env python3
"""Generate Laravel 13 migrations + seeders + db-schema.md for the ATS Skill rebuild.

Source (read-only): ~/workspace/user/files/atsskill.sql
  MySQL 8 dump of database `atsskill1` (112 tables, phpMyAdmin 5.2.1).

Outputs (all under ~/workspace/newAtsskill-build/):
  database/migrations/*.php   one migration per old table (112) + 6 media pivots
  database/seeders/*.php      RolePermissionSeeder, SettingSeeder,
                              LanguageSeeder, EmailTemplateSeeder, DatabaseSeeder
  plan/db-schema.md           full schema spec (tables, counts, permissions)

Rules enforced by this script:
  * Exact old table and column names are kept. No renames.
  * up() never drops anything. down() is the standard dropIfExists rollback.
  * NO secret values are ever written out. The dump contains real-looking
    gateway / mail / recaptcha credentials and hashed passwords. This script
    copies KEY NAMES only; secret-valued settings are seeded as
    env('KEY_NAME', '') placeholders. No user/admin rows are seeded.
  * The `migrations` table is Laravel-owned: its migration is emitted with a
    Schema::hasTable() guard so a fresh `php artisan migrate` does not fail.

Re-run:  python3 scripts/generate_migrations.py [--sql PATH] [--out DIR]
"""
from __future__ import annotations

import argparse
import re
import sys
from collections import Counter, OrderedDict
from datetime import datetime, timedelta
from pathlib import Path

HOME = Path.home()
DEFAULT_SQL = HOME / "workspace/user/files/atsskill.sql"
DEFAULT_OUT = HOME / "workspace/newAtsskill-build"

# ---------------------------------------------------------------------------
# Dependency order for migration timestamps (earliest first).
# FK targets always precede the tables that reference them.
# ---------------------------------------------------------------------------
TABLE_ORDER = [
    # 1. Central media + Spatie permission tables + languages/settings first.
    "media",
    "permissions", "roles", "model_has_permissions", "model_has_roles",
    "role_has_permissions",
    "languages", "settings", "configurations", "marketing_settings",
    "basic_payments", "payment_gateways", "custom_paginations", "custom_codes",
    "seo_settings", "section_settings", "footer_settings",
    # 2. Geography (countries -> states -> cities).
    "countries", "states", "cities",
    # 3. Users / admins / instructors.
    "users", "admins", "instructors", "user_education", "user_experiences",
    "user_skill_topics", "banned_histories", "instructor_requests",
    "instructor_request_settings", "instructor_request_setting_translations",
    "socialite_credentials", "zoom_credentials", "jitsi_settings",
    # 4. Courses and everything under them.
    "course_categories", "course_category_translations",
    "course_levels", "course_level_translations", "course_languages",
    "courses", "course_chapters", "course_chapter_items",
    "course_chapter_lessons", "course_live_classes",
    "course_partner_instructors", "course_reviews", "course_progress",
    "course_selected_filter_options", "course_selected_languages",
    "course_selected_levels", "course_delete_requests",
    "quizzes", "quiz_questions", "quiz_question_answers", "quiz_results",
    "lesson_questions", "lesson_replies", "announcements",
    "badges", "coupons", "coupon_histories",
    "certificate_builders", "certificate_builder_items",
    # 5. Orders / payments.
    "orders", "order_items", "enrollments", "refund_requests",
    "withdraw_methods", "withdraw_requests",
    # 6. Content: blog / faq / testimonial / brand / team / menus / pages.
    "blogs", "blog_categories", "blog_category_translations",
    "blog_translations", "blog_comments",
    "faqs", "faq_translations", "testimonials", "testimonial_translations",
    "brands", "team_members",
    "menus", "menu_translations", "menu_items", "menu_item_translations",
    "custom_pages", "custom_page_translations", "landing_pages",
    "email_templates",
    # 7. Sections / misc site content.
    "hero_sections", "hero_section_translations",
    "about_sections", "about_section_translations",
    "banner_sections",
    "counter_sections", "counter_section_translations",
    "faq_sections", "faq_section_translations",
    "featured_course_sections", "featured_instructors",
    "featured_instructor_translations",
    "newsletter_sections",
    "our_features_sections", "our_features_section_translations",
    "contact_sections", "contact_messages", "news_letters",
    "social_links", "multi_currencies",
    # 8. Laravel framework tables (stock Laravel 13 ships equivalents;
    #    see plan/db-schema.md before keeping both).
    "jobs", "failed_jobs", "password_reset_tokens",
    "personal_access_tokens", "migrations",
]

# New central-media pivot tables (media itself already exists in the dump).
# Each: id, <entity>_id, media_id, is_primary, order, timestamps.
MEDIA_PIVOTS = [
    ("course_media", "course"),
    ("blog_media", "blog"),
    ("testimonial_media", "testimonial"),
    ("brand_media", "brand"),
    ("team_member_media", "team_member"),
    ("instructor_media", "instructor"),
]

# Setting keys whose VALUES must never be copied out of the dump.
# Only the key name is seeded, with an env() placeholder as the value.
SENSITIVE_SUBSTRINGS = (
    "secret", "password", "token", "_key", "app_id", "client_id",
    "username", "host", "api_key", "auth_token", "site_key",
)
SENSITIVE_KEYS = {
    "logo", "favicon", "copyright_text",  # logo/favicon: re-upload in admin;
    # copyright_text contains a trade-licence number in the dump.
    "tawk_chat_link", "google_tagmanager_id", "pixel_app_id",
    "gmail_redirect_url", "facebook_redirect_url",
}

BASE_TS = datetime(2026, 11, 24, 0, 0, 1)


# ---------------------------------------------------------------------------
# SQL parsing
# ---------------------------------------------------------------------------
class Column:
    def __init__(self, name, raw_type, rest):
        self.name = name
        self.raw_type = raw_type          # e.g. varchar(255), enum('a','b')
        m = re.match(r"([a-z]+)(?:\((.*)\))?$", raw_type, re.S)
        self.base = m.group(1) if m else raw_type
        self.params = m.group(2) if m and m.group(2) else ""
        self.unsigned = "UNSIGNED" in rest.upper()
        self.not_null = "NOT NULL" in rest.upper()
        self.default = self._parse_default(rest)
        self.use_current = "DEFAULT CURRENT_TIMESTAMP" in rest.upper()

    @staticmethod
    def _parse_default(rest):
        m = re.search(r"DEFAULT '((?:[^'\\]|\\.)*)'", rest)
        if m:
            return ("str", m.group(1).replace("\\'", "'").replace("\\\\", "\\"))
        m = re.search(r"DEFAULT (-?\d+(?:\.\d+)?)\b", rest)
        if m:
            return ("num", m.group(2))
        if re.search(r"DEFAULT NULL", rest, re.I):
            return ("null", None)
        if re.search(r"DEFAULT CURRENT_TIMESTAMP", rest, re.I):
            return ("current", None)
        return None

    @property
    def nullable(self):
        return not self.not_null


class Table:
    def __init__(self, name):
        self.name = name
        self.columns = []          # list[Column], in dump order
        self.pk = []               # list[str]
        self.auto_increment = set()
        self.uniques = []          # list[list[str]]
        self.indexes = []          # list[list[str]] (ADD KEY)
        self.fks = []              # list[dict]
        self.rows = 0              # INSERT rows in the dump

    def col(self, name):
        for c in self.columns:
            if c.name == name:
                return c
        return None


def parse_create_blocks(sql_text):
    """Line-based parse of CREATE TABLE blocks (one table ends at a line
    that is exactly `) ENGINE=...;` or `) ;` -- landing_pages has no ENGINE)."""
    tables = OrderedDict()
    lines = sql_text.splitlines()
    i = 0
    while i < len(lines):
        m = re.match(r"CREATE TABLE `([^`]+)` \($", lines[i].strip())
        if not m:
            i += 1
            continue
        table = Table(m.group(1))
        i += 1
        while i < len(lines):
            line = lines[i].strip()
            if line.startswith(") ENGINE") or line == ") ;":
                break
            cm = re.match(r"`([^`]+)`\s+([a-z]+(?:\([^)]*\))?)(.*?),?$", line)
            if cm:
                table.columns.append(
                    Column(cm.group(1), cm.group(2), cm.group(3)))
            i += 1
        tables[table.name] = table
        i += 1
    return tables


def parse_alters(sql_text, tables):
    """PKs, UNIQUE KEYs, KEYs, AUTO_INCREMENT and FOREIGN KEYs all live in
    the ALTER TABLE section at the end of a phpMyAdmin dump."""
    for m in re.finditer(
            r"ALTER TABLE `([^`]+)`\s+ADD PRIMARY KEY \(([^)]+)\);", sql_text):
        t = tables.get(m.group(1))
        if t:
            t.pk = [c.strip(" `") for c in m.group(2).split(",")]
    for m in re.finditer(
            r"ALTER TABLE `([^`]+)`\n\s+ADD PRIMARY KEY \(([^)]+)\),\n"
            r"((?:\s+ADD (?:UNIQUE KEY|KEY) `[^`]+` \([^)]+\),?\n?)+);",
            sql_text):
        t = tables.get(m.group(1))
        if t:
            t.pk = [c.strip(" `") for c in m.group(2).split(",")]
            for kind, cols in re.findall(
                    r"ADD (UNIQUE KEY|KEY) `[^`]+` \(([^)]+)\)", m.group(3)):
                col_list = [c.strip(" `") for c in cols.split(",")]
                (t.uniques if kind == "UNIQUE KEY" else t.indexes).append(col_list)
    # Standalone ADD UNIQUE KEY / ADD KEY blocks (e.g. failed_jobs, jobs).
    for m in re.finditer(
            r"ALTER TABLE `([^`]+)`\n\s+ADD (?:PRIMARY KEY \([^)]+\),\n)?"
            r"\s*ADD (UNIQUE KEY|KEY) `[^`]+` \(([^)]+)\)", sql_text):
        t = tables.get(m.group(1))
        if not t:
            continue
        col_list = [c.strip(" `") for c in m.group(3).split(",")]
        bucket = t.uniques if m.group(2) == "UNIQUE KEY" else t.indexes
        if col_list not in bucket:
            bucket.append(col_list)
    for m in re.finditer(
            r"ALTER TABLE `([^`]+)`\n\s+MODIFY `([^`]+)` [^;]*AUTO_INCREMENT",
            sql_text):
        t = tables.get(m.group(1))
        if t:
            t.auto_increment.add(m.group(2))
    for m in re.finditer(
            r"ADD CONSTRAINT `([^`]+)` FOREIGN KEY \(`([^`]+)`\) "
            r"REFERENCES `([^`]+)` \(`([^`]+)`\)"
            r"(?: ON DELETE (CASCADE|SET NULL|SET DEFAULT|RESTRICT|NO ACTION))?"
            r"(?: ON UPDATE (CASCADE|SET NULL|SET DEFAULT|RESTRICT|NO ACTION))?",
            sql_text):
        # FKs are grouped per table in the constraints section; recover the
        # owning table from the preceding "Constraints for table" header is
        # unreliable, so match by constraint name prefix instead.
        name, col, ref_table, ref_col = m.group(1), m.group(2), m.group(3), m.group(4)
        owner = None
        for tname in tables:
            if name.startswith(tname + "_"):
                owner = tname
                break
        if owner is None:
            owner = name.rsplit("_", 3)[0]
        t = tables.get(owner)
        if t is not None:
            t.fks.append({
                "name": name, "column": col, "ref_table": ref_table,
                "ref_column": ref_col,
                "on_delete": (m.group(5) or "").strip().lower() or None,
                "on_update": (m.group(6) or "").strip().lower() or None,
            })


def parse_insert_counts(sql_text, tables):
    """Count INSERT rows per table (each VALUES row starts its own line)."""
    for m in re.finditer(
            r"INSERT INTO `([^`]+)` \([^)]+\) VALUES\n(.*?);", sql_text, re.S):
        t = tables.get(m.group(1))
        if t is None:
            continue
        t.rows += sum(1 for line in m.group(2).splitlines()
                      if line.startswith("("))


def split_sql_rows(values_text):
    """Split a VALUES block into row strings (rows start at line start)."""
    return [ln.rstrip(",") for ln in values_text.splitlines()
            if ln.startswith("(")]


def parse_quoted(row, nfields):
    """Parse the first nfields of an INSERT row: ints or 'quoted' strings.
    Returns a list of Python values (str/int/None). Only the leading fields
    are needed, so the parser stops after nfields fields."""
    out, i, n = [], 0, len(row)
    while len(out) < nfields and i < n:
        if row[i] == "(" or row[i] in ", ":
            i += 1
            continue
        if row[i] == "'":
            j = i + 1
            buf = []
            while j < n:
                if row[j] == "\\" and j + 1 < n:
                    buf.append(row[j + 1])
                    j += 2
                elif row[j] == "'":
                    break
                else:
                    buf.append(row[j])
                    j += 1
            out.append("".join(buf))
            i = j + 1
        elif row[i:i + 4] == "NULL":
            out.append(None)
            i += 4
        else:
            m = re.match(r"-?\d+", row[i:])
            if m:
                out.append(int(m.group(0)))
                i += len(m.group(0))
            else:
                i += 1
    return out


def parse_seed_data(sql_text):
    """Extract the seed reference data the seeders need (names only for
    secrets; see module docstring)."""
    data = {}

    def insert_block(table):
        m = re.search(r"INSERT INTO `" + table + r"` \(([^)]+)\) VALUES\n(.*?);",
                      sql_text, re.S)
        return m

    m = insert_block("permissions")
    perms = []
    for row in split_sql_rows(m.group(2)):
        v = parse_quoted(row, 4)
        perms.append({"id": v[0], "name": v[1], "guard_name": v[2],
                      "group_name": v[3]})
    data["permissions"] = sorted(perms, key=lambda p: p["id"])

    m = insert_block("roles")
    data["roles"] = [{"id": v[0], "name": v[1], "guard_name": v[2]}
                     for v in (parse_quoted(r, 3)
                               for r in split_sql_rows(m.group(2)))]

    m = insert_block("role_has_permissions")
    data["role_has_permissions"] = [
        (v[0], v[1]) for v in
        (parse_quoted(r, 2) for r in split_sql_rows(m.group(2)))]

    m = insert_block("settings")
    # (key, value) pairs. Values are filtered at render time: secret-valued
    # keys are NEVER written out -- only their names, with env() placeholders.
    data["settings"] = [(parse_quoted(r, 3)[1], parse_quoted(r, 3)[2])
                        for r in split_sql_rows(m.group(2))]

    m = insert_block("languages")
    data["languages"] = [
        {"id": v[0], "name": v[1], "code": v[2], "icon": v[3],
         "direction": v[4], "status": v[5], "is_default": v[6]}
        for v in (parse_quoted(r, 7) for r in split_sql_rows(m.group(2)))]

    m = insert_block("email_templates")
    data["email_templates"] = [
        {"id": v[0], "name": v[1], "subject": v[2]}
        for v in (parse_quoted(r, 3) for r in split_sql_rows(m.group(2)))]

    return data


# ---------------------------------------------------------------------------
# MySQL -> Laravel 13 column mapping
# ---------------------------------------------------------------------------
def php_str(s):
    return "'" + str(s).replace("\\", "\\\\").replace("'", "\\'") + "'"


def php_list(values):
    return "[" + ", ".join(php_str(v) for v in values) + "]"


def studly(table):
    return "".join(w.capitalize() for w in table.split("_"))


def enum_values(col):
    return re.findall(r"'((?:[^'\\]|\\.)*)'", col.params)


def column_call(col, single_unique_cols):
    """Return the Blueprint line for one column (without trailing comment),
    or None if the column is handled collectively (timestamps/softDeletes)."""
    n = php_str(col.name)
    d = col.default
    suffix = ""
    if col.name not in ("created_at", "updated_at", "deleted_at"):
        if col.nullable:
            suffix += "->nullable()"
        if d is not None and d[0] not in ("null", "current"):
            suffix += "->default(" + php_default(col, d) + ")"
    if col.name in single_unique_cols:
        suffix += "->unique()"

    base = col.base
    if base == "varchar":
        length = int(col.params) if col.params else 255
        call = f"$table->string({n})" if length == 255 else \
            f"$table->string({n}, {length})"
    elif base == "text":
        call = f"$table->text({n})"
    elif base == "longtext":
        call = f"$table->longText({n})"
    elif base == "date":
        call = f"$table->date({n})"
    elif base == "timestamp":
        call = f"$table->timestamp({n})"
        if col.use_current:
            suffix = "->useCurrent()" + suffix.replace("->nullable()", "")
            if col.nullable:
                suffix += "->nullable()"
        elif col.nullable and "->nullable()" not in suffix:
            suffix += "->nullable()"
    elif base == "decimal":
        p, s = (col.params.split(",") + ["2"])[:2]
        call = f"$table->decimal({n}, {p.strip()}, {s.strip()})"
    elif base == "double":
        call = f"$table->double({n})"
    elif base == "float":
        call = f"$table->float({n})"
    elif base == "enum":
        call = f"$table->enum({n}, {php_list(enum_values(col))})"
    elif base == "tinyint" and col.params in ("1", ""):
        # tinyint(1) is the MySQL boolean idiom; plain `tinyint` (jobs.attempts)
        # is handled below.
        if col.params == "1":
            call = f"$table->boolean({n})"
        else:
            call = (f"$table->unsignedTinyInteger({n})" if col.unsigned
                    else f"$table->tinyInteger({n})")
    elif base == "tinyint":
        call = (f"$table->unsignedTinyInteger({n})" if col.unsigned
                else f"$table->tinyInteger({n})")
    elif base == "int":
        call = (f"$table->unsignedInteger({n})" if col.unsigned
                else f"$table->integer({n})")
    elif base == "bigint":
        call = (f"$table->unsignedBigInteger({n})" if col.unsigned
                else f"$table->bigInteger({n})")
    else:
        # No exotic types exist in this dump (verified: bigint/int/tinyint/
        # varchar/text/longtext/decimal/double/float/date/timestamp/enum
        # only). Fall through to string so an unknown type fails loudly in
        # review instead of silently dropping a column.
        call = f"$table->string({n})  // TODO: unmapped MySQL type {col.raw_type}"
    return call + suffix + ";"


def php_default(col, d):
    kind, val = d
    if kind == "num":
        return val
    if kind == "str":
        if col.base == "tinyint" and col.params == "1":
            return "true" if val == "1" else "false"
        if col.base in ("int", "bigint", "decimal", "double", "float"):
            return val
        return php_str(val)
    return "null"


def render_migration(table, timestamp, class_name):
    """Render one migration file body for a parsed Table."""
    lines = []
    lines.append("<?php")
    lines.append("")
    lines.append("use Illuminate\\Database\\Migrations\\Migration;")
    lines.append("use Illuminate\\Database\\Schema\\Blueprint;")
    lines.append("use Illuminate\\Support\\Facades\\Schema;")
    lines.append("")
    lines.append(f"return new class extends Migration")
    lines.append("{")
    lines.append("    /**")
    lines.append(f"     * Create `{table.name}` exactly as in the old `atsskill1` dump.")
    lines.append("     * Table/column names are kept verbatim; no column is renamed.")
    if table.fks:
        lines.append("     * Declared foreign keys (most old relations are")
        lines.append("     * code-enforced only -- see plan/db-schema.md):")
        for fk in table.fks:
            lines.append(
                f"     *   {fk['column']} -> {fk['ref_table']}.{fk['ref_column']}"
                + (f" ON DELETE {fk['on_delete'].upper()}" if fk["on_delete"] else "")
                + (f" ON UPDATE {fk['on_update'].upper()}" if fk["on_update"] else ""))
    lines.append("     */")
    lines.append("    public function up(): void")
    lines.append("    {")
    guard = table.name == "migrations"
    if guard:
        lines.append("        // `migrations` is owned by Laravel's migrator itself;")
        lines.append("        // only create it when it does not already exist.")
        lines.append("        if (Schema::hasTable('migrations')) {")
        lines.append("            return;")
        lines.append("        }")
    lines.append(f"        Schema::create('{table.name}', function (Blueprint $table) {{")

    cols = table.columns
    has_ts_pair = (
        table.col("created_at") is not None
        and table.col("updated_at") is not None
        and table.col("created_at").base == "timestamp"
        and table.col("updated_at").base == "timestamp")
    has_soft = (table.col("deleted_at") is not None
                and table.col("deleted_at").base == "timestamp")
    single_uniques = {u[0] for u in table.uniques if len(u) == 1}
    is_single_id_pk = table.pk == ["id"]
    id_col = table.col("id")

    for col in cols:
        if col.name in ("created_at", "updated_at") and has_ts_pair:
            continue
        if col.name == "deleted_at" and has_soft:
            continue
        if col.name == "id" and is_single_id_pk and "id" in table.auto_increment:
            if col.unsigned and col.base == "bigint":
                lines.append("            $table->id();")
            elif col.unsigned and col.base == "int":
                lines.append("            $table->increments('id');")
            else:
                lines.append("            $table->id();  // was: "
                             + col.raw_type)
            continue
        call = column_call(col, single_uniques)
        lines.append("            " + call)

    if has_ts_pair:
        lines.append("            $table->timestamps();")
    elif table.col("created_at") is not None and not has_ts_pair:
        pass  # already emitted as a plain column above
    if has_soft:
        lines.append("            $table->softDeletes();")

    # Composite primary key (Spative pivot tables, password_reset_tokens).
    if table.pk and not is_single_id_pk:
        lines.append(f"            $table->primary({php_list(table.pk)});")
    for u in table.uniques:
        if len(u) > 1:
            lines.append(f"            $table->unique({php_list(u)});")
    for idx in table.indexes:
        if len(idx) == 1:
            lines.append(f"            $table->index({php_str(idx[0])});")
        else:
            lines.append(f"            $table->index({php_list(idx)});")
    for fk in table.fks:
        ref = (f"$table->foreign({php_str(fk['column'])})"
               f"->references({php_str(fk['ref_column'])})"
               f"->on({php_str(fk['ref_table'])})")
        if fk["on_delete"]:
            ref += f"->onDelete('{fk['on_delete']}')"
        if fk["on_update"]:
            ref += f"->onUpdate('{fk['on_update']}')"
        lines.append("            " + ref + ";")

    lines.append("        });")
    lines.append("    }")
    lines.append("")
    lines.append("    public function down(): void")
    lines.append("    {")
    lines.append(f"        Schema::dropIfExists('{table.name}');")
    lines.append("    }")
    lines.append("};")
    return "\n".join(lines) + "\n"


def render_pivot_migration(entity_table, entity, timestamp):
    """Central-media pivot: <entity>_media with is_primary + order."""
    target = {
        "course_media": "courses", "blog_media": "blogs",
        "testimonial_media": "testimonials", "brand_media": "brands",
        "team_member_media": "team_members",
        "instructor_media": "instructors",
    }[entity_table]
    col = entity + "_id"
    return f"""<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{{
    /**
     * Central-media pivot for `{entity}` (new in the rebuild; the old
     * dump kept image paths as plain varchar columns).
     * `is_primary` marks the featured image; `order` controls sorting.
     */
    public function up(): void
    {{
        Schema::create('{entity_table}', function (Blueprint $table) {{
            $table->id();
            $table->unsignedBigInteger('{col}');
            $table->unsignedBigInteger('media_id');
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
            $table->unique(['{col}', 'media_id']);
            $table->foreign('{col}')->references('id')->on('{target}')->onDelete('cascade');
            $table->foreign('media_id')->references('id')->on('media')->onDelete('cascade');
        }});
    }}

    public function down(): void
    {{
        Schema::dropIfExists('{entity_table}');
    }}
}};
"""


# ---------------------------------------------------------------------------
# Seeders (names only for anything credential-like; see module docstring)
# ---------------------------------------------------------------------------
def is_sensitive_setting(key):
    kl = key.lower()
    return key in SENSITIVE_KEYS or any(s in kl for s in SENSITIVE_SUBSTRINGS)


def safe_setting_value(key, value):
    """Value to seed for a setting key, or an env() placeholder expression.

    Returns (php_expression, is_placeholder)."""
    if is_sensitive_setting(key):
        env_name = key.upper()
        return f"env('{env_name}', '')", True
    if value is None:
        return "''", False
    return php_str(value), False


def render_role_permission_seeder(seed):
    perms = seed["permissions"]
    by_id = {p["id"]: p["name"] for p in perms}
    manager_ids = sorted({pid for pid, rid in seed["role_has_permissions"]
                          if rid == 2})
    manager_names = [by_id[i] for i in manager_ids if i in by_id]
    lines = []
    lines.append("<?php")
    lines.append("")
    lines.append("namespace Database\\Seeders;")
    lines.append("")
    lines.append("use Illuminate\\Database\\Seeder;")
    lines.append("use Spatie\\Permission\\Models\\Permission;")
    lines.append("use Spatie\\Permission\\Models\\Role;")
    lines.append("")
    lines.append("class RolePermissionSeeder extends Seeder")
    lines.append("{")
    lines.append("    /**")
    lines.append(f"     * {len(perms)} permissions exactly as in the old dump")
    lines.append("     * (guard `admin`, same names and group_name values).")
    lines.append("     * Super Admin gets all permissions; Manager gets the 64")
    lines.append("     * permissions it held in `role_has_permissions`.")
    lines.append("     * Requires spatie/laravel-permission and an Admin model")
    lines.append("     * using the HasRoles trait (guard `admin`).")
    lines.append("     */")
    lines.append("    public function run(): void")
    lines.append("    {")
    lines.append("        app()[\\Spatie\\Permission\\PermissionRegistrar::class]->forgetCachedPermissions();")
    lines.append("")
    lines.append("        $permissions = [")
    for p in perms:
        lines.append(
            f"            ['name' => {php_str(p['name'])}, "
            f"'guard_name' => {php_str(p['guard_name'])}, "
            f"'group_name' => {php_str(p['group_name'] or '')}],")
    lines.append("        ];")
    lines.append("")
    lines.append("        foreach ($permissions as $permission) {")
    lines.append("            Permission::firstOrCreate(")
    lines.append("                ['name' => $permission['name'],")
    lines.append("                 'guard_name' => $permission['guard_name']],")
    lines.append("                ['group_name' => $permission['group_name']]")
    lines.append("            );")
    lines.append("        }")
    lines.append("")
    lines.append("        $superAdmin = Role::firstOrCreate(")
    lines.append("            ['name' => 'Super Admin', 'guard_name' => 'admin']")
    lines.append("        );")
    lines.append("        $superAdmin->syncPermissions(Permission::all());")
    lines.append("")
    lines.append("        $manager = Role::firstOrCreate(")
    lines.append("            ['name' => 'Manager', 'guard_name' => 'admin']")
    lines.append("        );")
    lines.append("        $manager->syncPermissions([")
    for name in manager_names:
        lines.append(f"            {php_str(name)},")
    lines.append("        ]);")
    lines.append("    }")
    lines.append("}")
    return "\n".join(lines) + "\n"


def render_setting_seeder(seed):
    lines = []
    lines.append("<?php")
    lines.append("")
    lines.append("namespace Database\\Seeders;")
    lines.append("")
    lines.append("use Illuminate\\Database\\Seeder;")
    lines.append("use Illuminate\\Support\\Facades\\DB;")
    lines.append("")
    lines.append("class SettingSeeder extends Seeder")
    lines.append("{")
    lines.append("    /**")
    lines.append(f"     * {len(seed['settings'])} setting keys from the old dump.")
    lines.append("     * SECURITY: values for credential-like keys (anything with")
    lines.append("     * secret/password/token/key/app_id/client_id/username/host")
    lines.append("     * in the name) are NOT copied from the dump. They are seeded")
    lines.append("     * as env('KEY_NAME', '') placeholders -- set the real values")
    lines.append("     * in .env, never in a seeder. The dump also held a trade")
    lines.append("     * licence number inside `copyright_text` and upload paths for")
    lines.append("     * `logo`/`favicon`; those are seeded empty on purpose.")
    lines.append("     */")
    lines.append("    public function run(): void")
    lines.append("    {")
    lines.append("        $settings = [")
    for key, value in seed["settings"]:
        expr, _ = safe_setting_value(key, value)
        lines.append(f"            ['key' => {php_str(key)}, 'value' => {expr}],")
    lines.append("        ];")
    lines.append("")
    lines.append("        foreach ($settings as $setting) {")
    lines.append("            DB::table('settings')->updateOrInsert(")
    lines.append("                ['key' => $setting['key']],")
    lines.append("                ['value' => $setting['value']]")
    lines.append("            );")
    lines.append("        }")
    lines.append("    }")
    lines.append("}")
    return "\n".join(lines) + "\n"


def render_language_seeder(seed):
    lines = []
    lines.append("<?php")
    lines.append("")
    lines.append("namespace Database\\Seeders;")
    lines.append("")
    lines.append("use Illuminate\\Database\\Seeder;")
    lines.append("use Illuminate\\Support\\Facades\\DB;")
    lines.append("")
    lines.append("class LanguageSeeder extends Seeder")
    lines.append("{")
    lines.append("    public function run(): void")
    lines.append("    {")
    lines.append("        $languages = [")
    for lang in seed["languages"]:
        status = int(lang["status"]) if str(lang["status"]).isdigit() else 0
        default = "true" if str(lang["is_default"]) == "1" else "false"
        icon = php_str(lang["icon"]) if lang["icon"] else "null"
        lines.append(
            f"            ['name' => {php_str(lang['name'])}, "
            f"'code' => {php_str(lang['code'])}, 'icon' => {icon}, "
            f"'direction' => {php_str(lang['direction'])}, "
            f"'status' => {status}, 'is_default' => {default}],")
    lines.append("        ];")
    lines.append("")
    lines.append("        foreach ($languages as $language) {")
    lines.append("            DB::table('languages')->updateOrInsert(")
    lines.append("                ['code' => $language['code']], $language")
    lines.append("            );")
    lines.append("        }")
    lines.append("    }")
    lines.append("}")
    return "\n".join(lines) + "\n"


def render_email_template_seeder(seed):
    lines = []
    lines.append("<?php")
    lines.append("")
    lines.append("namespace Database\\Seeders;")
    lines.append("")
    lines.append("use Illuminate\\Database\\Seeder;")
    lines.append("use Illuminate\\Support\\Facades\\DB;")
    lines.append("")
    lines.append("class EmailTemplateSeeder extends Seeder")
    lines.append("{")
    lines.append("    /**")
    lines.append("     * Only 2 email templates exist in the old dump (the brief")
    lines.append("     * said 17 -- that count is not in the dump; see")
    lines.append("     * plan/db-schema.md). Subjects are kept verbatim; the HTML")
    lines.append("     * bodies below are short placeholders -- port the full old")
    lines.append("     * template bodies from the old mail views when rebuilding")
    lines.append("     * the mail screens, keeping the {{user_name}} placeholder.")
    lines.append("     */")
    lines.append("    public function run(): void")
    lines.append("    {")
    lines.append("        $templates = [")
    for t in seed["email_templates"]:
        if t["name"] == "password_reset":
            body = ("<p>Dear {{user_name}},</p>"
                    "<p>Do you want to reset your password? Please click "
                    "the following link and reset your password.</p>"
                    "<p>{{reset_link}}</p>")
        elif t["name"] == "contact_mail":
            body = ("<p>Hello there,</p>"
                    "<p>You have received a new contact message.</p>"
                    "<p>{{message}}</p>")
        else:
            body = "<p>{{message}}</p>"
        lines.append(
            f"            ['name' => {php_str(t['name'])}, "
            f"'subject' => {php_str(t['subject'])}, "
            f"'message' => {php_str(body)}],")
    lines.append("        ];")
    lines.append("")
    lines.append("        foreach ($templates as $template) {")
    lines.append("            DB::table('email_templates')->updateOrInsert(")
    lines.append("                ['name' => $template['name']], $template")
    lines.append("            );")
    lines.append("        }")
    lines.append("    }")
    lines.append("}")
    return "\n".join(lines) + "\n"


DATABASE_SEEDER = """<?php

namespace Database\\Seeders;

use Illuminate\\Database\\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the rebuild's reference data (roles/permissions, languages,
     * settings, email templates) in dependency order.
     *
     * Deliberately NOT seeded here: users, admins, courses, orders or any
     * other production rows from the old dump (real accounts, hashed
     * passwords, payment data). Import those only through a separate,
     * reviewed data-migration step if the business asks for it.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            LanguageSeeder::class,
            SettingSeeder::class,
            EmailTemplateSeeder::class,
        ]);
    }
}
"""


# ---------------------------------------------------------------------------
# plan/db-schema.md
# ---------------------------------------------------------------------------
GROUPS = [
    ("Foundation — media, permissions, languages, settings", [
        "media", "permissions", "roles", "model_has_permissions",
        "model_has_roles", "role_has_permissions", "languages", "settings",
        "configurations", "marketing_settings", "basic_payments",
        "payment_gateways", "custom_paginations", "custom_codes",
        "seo_settings", "section_settings", "footer_settings"]),
    ("Geography", ["countries", "states", "cities"]),
    ("People — users, admins, instructors", [
        "users", "admins", "instructors", "user_education",
        "user_experiences", "user_skill_topics", "banned_histories",
        "instructor_requests", "instructor_request_settings",
        "instructor_request_setting_translations", "socialite_credentials",
        "zoom_credentials", "jitsi_settings"]),
    ("Courses", [
        "course_categories", "course_category_translations", "course_levels",
        "course_level_translations", "course_languages", "courses",
        "course_chapters", "course_chapter_items", "course_chapter_lessons",
        "course_live_classes", "course_partner_instructors",
        "course_reviews", "course_progress",
        "course_selected_filter_options", "course_selected_languages",
        "course_selected_levels", "course_delete_requests", "quizzes",
        "quiz_questions", "quiz_question_answers", "quiz_results",
        "lesson_questions", "lesson_replies", "announcements", "badges",
        "coupons", "coupon_histories", "certificate_builders",
        "certificate_builder_items"]),
    ("Commerce — orders, enrolments, refunds, withdrawals", [
        "orders", "order_items", "enrollments", "refund_requests",
        "withdraw_methods", "withdraw_requests"]),
    ("Content — blog, FAQ, testimonials, menus, pages", [
        "blogs", "blog_categories", "blog_category_translations",
        "blog_translations", "blog_comments", "faqs", "faq_translations",
        "testimonials", "testimonial_translations", "brands",
        "team_members", "menus", "menu_translations", "menu_items",
        "menu_item_translations", "custom_pages", "custom_page_translations",
        "landing_pages", "email_templates"]),
    ("Sections & site content", [
        "hero_sections", "hero_section_translations", "about_sections",
        "about_section_translations", "banner_sections",
        "counter_sections", "counter_section_translations", "faq_sections",
        "faq_section_translations", "featured_course_sections",
        "featured_instructors", "featured_instructor_translations",
        "newsletter_sections", "our_features_sections",
        "our_features_section_translations", "contact_sections",
        "contact_messages", "news_letters", "social_links",
        "multi_currencies"]),
    ("Laravel framework tables", [
        "jobs", "failed_jobs", "password_reset_tokens",
        "personal_access_tokens", "migrations"]),
]

TYPE_MAP_MD = """| MySQL (old dump) | Laravel 13 migration |
|---|---|
| `bigint UNSIGNED` + `AUTO_INCREMENT` + PK `id` | `$table->id()` |
| `int UNSIGNED` + `AUTO_INCREMENT` + PK `id` (`migrations`) | `$table->increments('id')` |
| `bigint UNSIGNED` (other columns) | `$table->unsignedBigInteger()` |
| `bigint` | `$table->bigInteger()` |
| `int UNSIGNED` | `$table->unsignedInteger()` |
| `int` | `$table->integer()` |
| `tinyint(1)` | `$table->boolean()` (`DEFAULT '1'` → `->default(true)`) |
| `tinyint UNSIGNED` (no `(1)`, only `jobs.attempts`) | `$table->unsignedTinyInteger()` |
| `varchar(n)` | `$table->string()` (length kept when ≠ 255) |
| `text` / `longtext` | `$table->text()` / `$table->longText()` |
| `decimal(8,2)` | `$table->decimal(..., 8, 2)` |
| `double` / `float` | `$table->double()` / `$table->float()` |
| `date` | `$table->date()` |
| `timestamp NULL` | `$table->timestamp()->nullable()` |
| `timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP` | `$table->timestamp()->useCurrent()` |
| `created_at` + `updated_at` (both timestamp) | `$table->timestamps()` |
| `deleted_at` timestamp NULL | `$table->softDeletes()` |
| `enum('a','b',...)` | `$table->enum(..., ['a','b',...])` |
"""


def col_spec(table, col):
    parts = [col.raw_type]
    if col.unsigned and "UNSIGNED" not in col.raw_type.upper():
        parts.append("unsigned")
    parts.append("NOT NULL" if col.not_null else "NULL")
    if col.name in table.auto_increment:
        parts.append("AUTO_INCREMENT")
    if col.name in table.pk:
        parts.append("PK")
    if col.default is not None:
        kind, val = col.default
        if kind == "str":
            parts.append(f"DEFAULT '{val}'")
        elif kind == "num":
            parts.append(f"DEFAULT {val}")
        elif kind == "current":
            parts.append("DEFAULT CURRENT_TIMESTAMP")
    return f"`{col.name}` " + " ".join(parts)


def render_schema_md(tables, seed, ordered_tables, pivot_names, stats):
    L = []
    L.append("# ATS Skill — database schema spec (rebuild)")
    L.append("")
    L.append("Source: `~/workspace/user/files/atsskill.sql` — MySQL 8 dump of")
    L.append("database `atsskill1` (phpMyAdmin 5.2.1, generated 2026-11-24).")
    L.append("Generated by `scripts/generate_migrations.py` — re-run it to")
    L.append("regenerate the migrations, seeders and this file. The source dump")
    L.append("is never modified. Table and column names are kept **exactly** as")
    L.append("in the old database; nothing is renamed.")
    L.append("")
    L.append("## Counts")
    L.append("")
    L.append(f"- Old tables in the dump: **{len(tables)}**")
    L.append(f"- Total columns across old tables: **{stats['columns']}**")
    L.append(f"- Migration files generated: **{stats['migrations']}** "
             f"({len(tables)} old tables + {len(pivot_names)} new media pivots)")
    L.append(f"- Seeder files: 5 (`RolePermissionSeeder`, `SettingSeeder`, "
             f"`LanguageSeeder`, `EmailTemplateSeeder`, `DatabaseSeeder`)")
    L.append(f"- Declared foreign keys in the dump: **{stats['fks']}** "
             f"(see below)")
    L.append(f"- Single-column UNIQUE keys: **{stats['uniques']}**, "
             f"composite UNIQUE keys: **{stats['composite_uniques']}**")
    L.append(f"- INSERT rows in the dump: **{stats['rows']}** in total "
             f"({stats['rows_no_migrations']} excluding the `migrations` "
             f"table's 118 Laravel bookkeeping rows)")
    L.append(f"- Permissions: **{len(seed['permissions'])}**, "
             f"roles: **{len(seed['roles'])}**")
    L.append(f"- Setting keys: **{len(seed['settings'])}**, "
             f"email templates in dump: **{len(seed['email_templates'])}**, "
             f"languages: **{len(seed['languages'])}**")
    L.append("")
    L.append("## Discrepancies vs the brief (verified against the dump)")
    L.append("")
    L.append("- **Settings: 55 keys, not 88.** The `settings` INSERT holds 55")
    L.append("  rows. The `SettingSeeder` seeds all 55 (key names only for")
    L.append("  credential-like keys — see Security below).")
    L.append("- **Email templates: 2, not 17.** Only `password_reset` and")
    L.append("  `contact_mail` exist in the dump. The other 15 templates the")
    L.append("  brief expected are not in this dump; they must be recreated")
    L.append("  from the old mail views if still needed.")
    L.append("- **Foreign keys: 24 declared, not ~20.** Only 24 of the")
    L.append("  hundreds of `*_id` columns have a declared FK constraint; the")
    L.append("  rest are code-enforced in the old app and stay that way here")
    L.append("  (plain `unsignedBigInteger` columns, no constraint).")
    L.append("- **`media` already exists** in the dump (`id, name, src, type,")
    L.append("  extension, deleted_at, created_at, updated_at`) — it is kept")
    L.append("  as-is and the 6 new pivot tables reference it.")
    L.append("- **`migrations` table:** Laravel's migrator owns it. Its")
    L.append("  migration is generated with a `Schema::hasTable()` guard so a")
    L.append("  fresh `php artisan migrate` does not fail on it.")
    L.append("- **Framework tables duplicated:** `jobs`, `failed_jobs`,")
    L.append("  `password_reset_tokens`, `personal_access_tokens` are also")
    L.append("  created by stock Laravel 13 migrations. Keep either the stock")
    L.append("  ones or these generated ones, not both — the shapes match the")
    L.append("  old dump (note `jobs.created_at` is an `int`, not a timestamp,")
    L.append("  and `failed_jobs.failed_at` defaults to `CURRENT_TIMESTAMP`).")
    L.append("")
    L.append("## Security — what is NOT in the seeders")
    L.append("")
    L.append("The dump contains real-looking credentials (payment-gateway keys,")
    L.append("a Mailtrap SMTP password, a reCAPTCHA secret, a UddoktaPay API")
    L.append("key, Stripe test keys) and a bank account number inside")
    L.append("`basic_payments.bank_information`, plus hashed user/admin")
    L.append("passwords. **None of those values are copied into any generated")
    L.append("file.** `SettingSeeder` seeds credential-like keys as")
    L.append("`env('KEY_NAME', '')` placeholders; no users or admins are")
    L.append("seeded at all. Set real values in `.env` only.")
    L.append("")
    L.append("## MySQL → Laravel type mapping")
    L.append("")
    L.append(TYPE_MAP_MD)
    L.append("")
    L.append("## Declared foreign keys (24)")
    L.append("")
    L.append("| Table | Column | References | ON DELETE | ON UPDATE |")
    L.append("|---|---|---|---|---|")
    for tname in ordered_tables:
        t = tables[tname]
        for fk in t.fks:
            L.append(f"| `{tname}` | `{fk['column']}` | "
                     f"`{fk['ref_table']}.{fk['ref_column']}` | "
                     f"{fk['on_delete'] or '—'} | {fk['on_update'] or '—'} |")
    L.append("")
    L.append("Everything else that looks like a relation (`courses.instructor_id`,")
    L.append("`orders.buyer_id`/`seller_id`, `enrollments.user_id`,")
    L.append("`course_chapters.instructor_id`, `blogs.admin_id`, …) has **no**")
    L.append("declared constraint in the dump and is reproduced as a plain")
    L.append("unsigned integer column, exactly as before.")
    L.append("")
    L.append("## Tables by group")
    L.append("")
    for gname, gtable_names in GROUPS:
        present = [n for n in gtable_names if n in tables]
        L.append(f"### {gname} ({len(present)} tables)")
        L.append("")
        for n in present:
            t = tables[n]
            pk = ", ".join(t.pk) if t.pk else "—"
            L.append(f"- **`{n}`** — {len(t.columns)} cols, PK: `{pk}`, "
                     f"dump rows: {t.rows}")
            L.append("  - " + ", ".join(col_spec(t, c) for c in t.columns))
            if t.fks:
                L.append("  - FKs: " + "; ".join(
                    f"`{f['column']}` → `{f['ref_table']}.{f['ref_column']}`"
                    for f in t.fks))
            if t.uniques:
                L.append("  - UNIQUE: " + "; ".join(
                    "`" + "`, `".join(u) + "`" for u in t.uniques))
        L.append("")
    L.append("### New media pivot tables (rebuild only, 6 tables)")
    L.append("")
    L.append("The old dump stores image paths as plain `varchar` columns")
    L.append("(e.g. `courses.thumbnail`, `users.image`, `brands.image`). The")
    L.append("rebuild adds a central `media` table (already in the dump) plus")
    L.append("one pivot per entity. Each pivot has: `id`, `<entity>_id`")
    L.append("`unsignedBigInteger`, `media_id` `unsignedBigInteger`,")
    L.append("`is_primary` `boolean` default `false`, `order`")
    L.append("`unsignedInteger` default `0`, `timestamps()`, a unique")
    L.append("`[<entity>_id, media_id]` pair, and `ON DELETE CASCADE` FKs to")
    L.append("both sides.")
    L.append("")
    pivot_targets = {
        "course_media": "courses", "blog_media": "blogs",
        "testimonial_media": "testimonials", "brand_media": "brands",
        "team_member_media": "team_members",
        "instructor_media": "instructors",
    }
    for pivot, entity in MEDIA_PIVOTS:
        L.append(f"- **`{pivot}`** — `{entity}_id` → "
                 f"`{pivot_targets[pivot]}`, `media_id` → `media`")
    L.append("")
    L.append("## Permissions (105) — full list")
    L.append("")
    L.append("Guard is `admin` for every permission. `group_name` is the old")
    L.append("value verbatim (including the old typos `contect message` and")
    L.append("the capitalised group `Badges`).")
    L.append("")
    grouped = OrderedDict()
    for p in seed["permissions"]:
        grouped.setdefault(p["group_name"] or "(none)", []).append(p["name"])
    for group, names in grouped.items():
        L.append(f"### {group} ({len(names)})")
        L.append("")
        for name in names:
            L.append(f"- `{name}`")
        L.append("")
    L.append("## Roles")
    L.append("")
    L.append("| Role | Guard | Permissions |")
    L.append("|---|---|---|")
    perm_by_id = {p["id"]: p["name"] for p in seed["permissions"]}
    for role in seed["roles"]:
        ids = sorted(pid for pid, rid in seed["role_has_permissions"]
                     if rid == role["id"])
        L.append(f"| {role['name']} | {role['guard_name']} | "
                 f"{len(ids)} ({'all' if len(ids) == len(seed['permissions']) else 'subset'}) |")
    L.append("")
    L.append("**Manager (64 permissions)** — everything except: admin profile")
    L.append("(`admin.profile.view/update`), admin CRUD (`admin.*`), blog")
    L.append("comments (`blog.comment.*`), role create/store/assign/delete")
    L.append("(`role.create/store/assign/delete`), settings")
    L.append("(`setting.view/update`), basic payment")
    L.append("(`basic.payment.view/update`), languages (`language.*`), menu")
    L.append("builder (`menu.*`), instructor requests")
    L.append("(`instructor.request.list/setting`), order management, withdraw")
    L.append("management, appearance/section management, footer management,")
    L.append("social link management. The exact 64 names are in")
    L.append("`RolePermissionSeeder.php`.")
    L.append("")
    L.append("`model_has_roles` in the dump assigns Super Admin (role 1) to")
    L.append("admins 1, 2 and 4, and Manager (role 2) to admin 3 — that")
    L.append("assignment is **not** seeded (no admin accounts are seeded).")
    L.append("")
    L.append("## Setting keys (55 — names only)")
    L.append("")
    for key, _ in seed["settings"]:
        L.append(f"- `{key}`")
    L.append("")
    L.append("## Languages")
    L.append("")
    for lang in seed["languages"]:
        L.append(f"- {lang['name']} (`{lang['code']}`), direction "
                 f"`{lang['direction']}`, status {lang['status']}, "
                 f"default: {'yes' if str(lang['is_default']) == '1' else 'no'}")
    L.append("")
    L.append("## Email templates in the dump (2)")
    L.append("")
    for t in seed["email_templates"]:
        L.append(f"- `{t['name']}` — subject: “{t['subject']}”")
    L.append("")
    L.append("## Seed row counts per table (dump)")
    L.append("")
    L.append("| Table | Rows in dump |")
    L.append("|---|---:|")
    for tname in sorted(tables, key=lambda n: tables[n].rows, reverse=True):
        if tables[tname].rows:
            L.append(f"| `{tname}` | {tables[tname].rows} |")
    L.append("")
    L.append("Tables with no INSERT in the dump (empty in the old database):")
    L.append("")
    empty = [n for n in tables if not tables[n].rows]
    L.append(", ".join(f"`{n}`" for n in empty))
    L.append("")
    L.append("## Edge cases / things that did not map cleanly")
    L.append("")
    L.append("- **No exotic types.** Every column maps with the table above;")
    L.append("  there are no `json`, `mediumtext`, `datetime`, `time`, `char`,")
    L.append("  `set` or generated columns in the dump.")
    L.append("- **`landing_pages` has no `ENGINE=` clause** (ends `) ;`).")
    L.append("  Parsed line-by-line; nothing lost.")
    L.append("- **Binary collation longtexts:** `featured_instructors.instructor_ids`,")
    L.append("  `landing_pages.content`, `quiz_results.result` are `longtext")
    L.append("  CHARACTER SET utf8mb4 COLLATE utf8mb4_bin`. Mapped to plain")
    L.append("  `longText()`; Laravel's default collation applies. If byte-wise")
    L.append("  comparison matters for those three columns, add the collation")
    L.append("  back with `->collation('utf8mb4_bin')`.")
    L.append("- **`order` and `key` as column names** (`course_categories.order`,")
    L.append("  `settings.key`, `payment_gateways.key`, …) are kept verbatim;")
    L.append("  they are fine as string arguments in the Blueprint calls.")
    L.append("- **`users.status` / `users.is_banned` are `varchar(255)`**, not")
    L.append("  enums/booleans — kept as `string` with the old defaults")
    L.append("  (`'active'` / `'no'`). Same for `instructors.status`, which is")
    L.append("  a nullable `tinyint(1)` default `'1'` → nullable boolean.")
    L.append("- **Spatie pivot tables** (`model_has_permissions`,")
    L.append("  `model_has_roles`, `role_has_permissions`) use composite")
    L.append("  primary keys, reproduced with `$table->primary([...])`.")
    L.append("- **`password_reset_tokens`** has PK `email` (no `id`), reproduced")
    L.append("  as `string('email')->primary()` via `$table->primary(['email'])`.")
    L.append("- **Duplicate `configurations` rows:** `setup_complete` /")
    L.append("  `setup_stage` each appear twice (ids 1–2 and 3–4, different")
    L.append("  values). Not seeded; the installer flow should own these.")
    L.append("- **`basic_payments` / `payment_gateways` values are not seeded")
    L.append("  at all** — they hold gateway credentials. Only their key names")
    L.append("  are documented here via the `settings` list pattern; gateway")
    L.append("  keys present in the dump: `razorpay_*`, `flutterwave_*`,")
    L.append("  `paystack_*`, `mollie_*`, `instamojo_*`, `uddoktapay_*`,")
    L.append("  `stripe_*`, `paypal_*`, `bank_*` (names only, no values).")
    L.append("")
    L.append("## Files generated")
    L.append("")
    L.append("- `database/migrations/2026_11_24_000001_…_000158_*.php` — 118")
    L.append("  files: one migration per old table (dependency order) + 6 media pivots.")
    L.append("- `database/seeders/RolePermissionSeeder.php` — 105 permissions,")
    L.append("  Super Admin (all) + Manager (64).")
    L.append("- `database/seeders/SettingSeeder.php` — 55 keys, env()")
    L.append("  placeholders for credential-like values.")
    L.append("- `database/seeders/LanguageSeeder.php` — English (`en`, default)")
    L.append("  and বাংলা (`bn`).")
    L.append("- `database/seeders/EmailTemplateSeeder.php` — the 2 templates")
    L.append("  in the dump, subjects verbatim, short placeholder bodies.")
    L.append("- `database/seeders/DatabaseSeeder.php` — calls the four above.")
    L.append("- `scripts/generate_migrations.py` — this generator; re-run after")
    L.append("  any change to the dump analysis.")
    L.append("")
    return "\n".join(L)


# ---------------------------------------------------------------------------
# Main
# ---------------------------------------------------------------------------
def ordered_table_names(tables):
    ordered = [t for t in TABLE_ORDER if t in tables]
    missing = sorted(set(tables) - set(ordered))
    if missing:
        print(f"WARNING: tables not in TABLE_ORDER, appended "
              f"alphabetically: {missing}", file=sys.stderr)
        ordered += missing
    return ordered


def main():
    ap = argparse.ArgumentParser(description=__doc__)
    ap.add_argument("--sql", type=Path, default=DEFAULT_SQL)
    ap.add_argument("--out", type=Path, default=DEFAULT_OUT)
    args = ap.parse_args()

    sql_text = args.sql.read_text(encoding="utf-8", errors="ignore")
    tables = parse_create_blocks(sql_text)
    parse_alters(sql_text, tables)
    parse_insert_counts(sql_text, tables)
    seed = parse_seed_data(sql_text)

    mig_dir = args.out / "database" / "migrations"
    seed_dir = args.out / "database" / "seeders"
    plan_dir = args.out / "plan"
    mig_dir.mkdir(parents=True, exist_ok=True)
    seed_dir.mkdir(parents=True, exist_ok=True)
    plan_dir.mkdir(parents=True, exist_ok=True)

    # Clear previously generated migrations (idempotent re-runs).
    for old in mig_dir.glob("2026_11_24_*.php"):
        old.unlink()

    ordered = ordered_table_names(tables)
    stamp = BASE_TS
    written = []
    for tname in ordered:
        ts = stamp.strftime("%Y_%m_%d_%H%M%S")
        stamp += timedelta(seconds=1)
        fname = f"{ts}_create_{tname}_table.php"
        (mig_dir / fname).write_text(
            render_migration(tables[tname], ts, f"Create{studly(tname)}Table"),
            encoding="utf-8")
        written.append(fname)
    pivot_names = []
    for pivot_table, entity in MEDIA_PIVOTS:
        ts = stamp.strftime("%Y_%m_%d_%H%M%S")
        stamp += timedelta(seconds=1)
        fname = f"{ts}_create_{pivot_table}_table.php"
        (mig_dir / fname).write_text(
            render_pivot_migration(pivot_table, entity, ts), encoding="utf-8")
        written.append(fname)
        pivot_names.append(pivot_table)

    seeders = {
        "RolePermissionSeeder.php": render_role_permission_seeder(seed),
        "SettingSeeder.php": render_setting_seeder(seed),
        "LanguageSeeder.php": render_language_seeder(seed),
        "EmailTemplateSeeder.php": render_email_template_seeder(seed),
        "DatabaseSeeder.php": DATABASE_SEEDER,
    }
    for name, content in seeders.items():
        (seed_dir / name).write_text(content, encoding="utf-8")

    stats = {
        "columns": sum(len(t.columns) for t in tables.values()),
        "migrations": len(written),
        "fks": sum(len(t.fks) for t in tables.values()),
        "uniques": sum(len([u for u in t.uniques if len(u) == 1])
                       for t in tables.values()),
        "composite_uniques": sum(len([u for u in t.uniques if len(u) > 1])
                                 for t in tables.values()),
        "rows": sum(t.rows for t in tables.values()),
        "rows_no_migrations": sum(t.rows for n, t in tables.items()
                                  if n != "migrations"),
    }
    (plan_dir / "db-schema.md").write_text(
        render_schema_md(tables, seed, ordered, pivot_names, stats),
        encoding="utf-8")

    print(f"tables parsed: {len(tables)}")
    print(f"columns parsed: {stats['columns']}")
    print(f"foreign keys: {stats['fks']}")
    print(f"migrations written: {len(written)} "
          f"({len(ordered)} tables + {len(pivot_names)} pivots)")
    print(f"seeders written: {len(seeders)}")
    print(f"permissions: {len(seed['permissions'])}, "
          f"roles: {len(seed['roles'])}, "
          f"setting keys: {len(seed['settings'])}, "
          f"email templates: {len(seed['email_templates'])}, "
          f"languages: {len(seed['languages'])}")
    print(f"schema doc: {plan_dir / 'db-schema.md'}")


if __name__ == "__main__":
    main()
