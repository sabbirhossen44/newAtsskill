<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * 55 setting keys from the old dump.
     * SECURITY: values for credential-like keys (anything with
     * secret/password/token/key/app_id/client_id/username/host
     * in the name) are NOT copied from the dump. They are seeded
     * as env('KEY_NAME', '') placeholders -- set the real values
     * in .env, never in a seeder. The dump also held a trade
     * licence number inside `copyright_text` and upload paths for
     * `logo`/`favicon`; those are seeded empty on purpose.
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'app_name', 'value' => 'ATS Skill'],
            ['key' => 'version', 'value' => '1.5.0'],
            ['key' => 'logo', 'value' => env('LOGO', '')],
            ['key' => 'timezone', 'value' => 'Asia/Dhaka'],
            ['key' => 'favicon', 'value' => env('FAVICON', '')],
            ['key' => 'cookie_status', 'value' => '0'],
            ['key' => 'border', 'value' => 'normal'],
            ['key' => 'corners', 'value' => 'small'],
            ['key' => 'background_color', 'value' => '#3bec18'],
            ['key' => 'text_color', 'value' => '#fafafa'],
            ['key' => 'border_color', 'value' => '#850ad6'],
            ['key' => 'btn_bg_color', 'value' => '#fffceb'],
            ['key' => 'btn_text_color', 'value' => '#4455ee'],
            ['key' => 'link_text', 'value' => 'More Info'],
            ['key' => 'link', 'value' => '/'],
            ['key' => 'btn_text', 'value' => 'Yes'],
            ['key' => 'message', 'value' => 'This website uses essential cookies to ensure its proper operation and tracking cookies to understand how you interact with it. The latter will be set only upon approval.'],
            ['key' => 'copyright_text', 'value' => env('COPYRIGHT_TEXT', '')],
            ['key' => 'recaptcha_site_key', 'value' => env('RECAPTCHA_SITE_KEY', '')],
            ['key' => 'recaptcha_secret_key', 'value' => env('RECAPTCHA_SECRET_KEY', '')],
            ['key' => 'recaptcha_status', 'value' => 'active'],
            ['key' => 'tawk_status', 'value' => 'active'],
            ['key' => 'tawk_chat_link', 'value' => env('TAWK_CHAT_LINK', '')],
            ['key' => 'google_tagmanager_status', 'value' => 'active'],
            ['key' => 'google_tagmanager_id', 'value' => env('GOOGLE_TAGMANAGER_ID', '')],
            ['key' => 'pixel_status', 'value' => 'inactive'],
            ['key' => 'pixel_app_id', 'value' => env('PIXEL_APP_ID', '')],
            ['key' => 'facebook_login_status', 'value' => 'inactive'],
            ['key' => 'facebook_app_id', 'value' => env('FACEBOOK_APP_ID', '')],
            ['key' => 'facebook_app_secret', 'value' => env('FACEBOOK_APP_SECRET', '')],
            ['key' => 'facebook_redirect_url', 'value' => env('FACEBOOK_REDIRECT_URL', '')],
            ['key' => 'google_login_status', 'value' => 'active'],
            ['key' => 'gmail_client_id', 'value' => env('GMAIL_CLIENT_ID', '')],
            ['key' => 'gmail_secret_id', 'value' => env('GMAIL_SECRET_ID', '')],
            ['key' => 'gmail_redirect_url', 'value' => env('GMAIL_REDIRECT_URL', '')],
            ['key' => 'default_avatar', 'value' => 'uploads/website-images/default-avatar.png'],
            ['key' => 'breadcrumb_image', 'value' => 'uploads/website-images/breadcrumb-image.jpg'],
            ['key' => 'mail_host', 'value' => env('MAIL_HOST', '')],
            ['key' => 'mail_sender_email', 'value' => 'info@atsskill.com'],
            ['key' => 'mail_username', 'value' => env('MAIL_USERNAME', '')],
            ['key' => 'mail_password', 'value' => env('MAIL_PASSWORD', '')],
            ['key' => 'mail_port', 'value' => '465'],
            ['key' => 'mail_encryption', 'value' => 'ssl'],
            ['key' => 'mail_sender_name', 'value' => 'ATS Skill'],
            ['key' => 'contact_message_receiver_mail', 'value' => 'info@atsskill.com'],
            ['key' => 'pusher_app_id', 'value' => env('PUSHER_APP_ID', '')],
            ['key' => 'pusher_app_key', 'value' => env('PUSHER_APP_KEY', '')],
            ['key' => 'pusher_app_secret', 'value' => env('PUSHER_APP_SECRET', '')],
            ['key' => 'pusher_app_cluster', 'value' => 'pusher_app_cluster'],
            ['key' => 'pusher_status', 'value' => 'inactive'],
            ['key' => 'club_point_rate', 'value' => '1'],
            ['key' => 'club_point_status', 'value' => 'active'],
            ['key' => 'maintenance_mode', 'value' => '0'],
            ['key' => 'maintenance_title', 'value' => 'Website Under maintenance'],
            ['key' => 'maintenance_description', 'value' => '<p>We are currently performing maintenance on our website to<br>improve your experience. Please check back later. &nbsp'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}
