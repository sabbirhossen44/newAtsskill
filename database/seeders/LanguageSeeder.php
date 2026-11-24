<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            ['name' => 'English', 'code' => 'en', 'icon' => null, 'direction' => 'ltr', 'status' => 1, 'is_default' => true],
            ['name' => 'বাংলা', 'code' => 'bn', 'icon' => null, 'direction' => 'ltr', 'status' => 1, 'is_default' => false],
        ];

        foreach ($languages as $language) {
            DB::table('languages')->updateOrInsert(
                ['code' => $language['code']], $language
            );
        }
    }
}
