<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Only 2 email templates exist in the old dump (the brief
     * said 17 -- that count is not in the dump; see
     * plan/db-schema.md). Subjects are kept verbatim; the HTML
     * bodies below are short placeholders -- port the full old
     * template bodies from the old mail views when rebuilding
     * the mail screens, keeping the {{user_name}} placeholder.
     */
    public function run(): void
    {
        $templates = [
            ['name' => 'password_reset', 'subject' => 'Password Reset', 'message' => '<p>Dear {{user_name}},</p><p>Do you want to reset your password? Please click the following link and reset your password.</p><p>{{reset_link}}</p>'],
            ['name' => 'contact_mail', 'subject' => 'Contact Email', 'message' => '<p>Hello there,</p><p>You have received a new contact message.</p><p>{{message}}</p>'],
        ];

        foreach ($templates as $template) {
            DB::table('email_templates')->updateOrInsert(
                ['name' => $template['name']], $template
            );
        }
    }
}
