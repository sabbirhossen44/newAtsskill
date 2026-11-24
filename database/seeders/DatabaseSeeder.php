<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

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
