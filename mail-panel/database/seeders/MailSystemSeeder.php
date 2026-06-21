<?php

namespace Database\Seeders;

use App\Models\Domain;
use App\Models\Template;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MailSystemSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->firstOrCreate(
            ['name' => 'Main Account'],
            ['daily_limit' => 15, 'status' => 'active']
        );

        User::query()->firstOrCreate(
            ['email' => 'admin@mail.local'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'tenant_id' => $tenant->id,
            ]
        );

        Domain::query()->firstOrCreate(
            ['domain_name' => 'example.com'],
            [
                'tenant_id' => $tenant->id,
                'from_email' => 'noreply@example.com',
                'from_name' => 'Example',
            ]
        );

        $templates = [
            [
                'slug' => 'otp',
                'name' => 'OTP Verification',
                'subject' => 'Your OTP code: {{otp}}',
                'type' => 'transactional',
                'html_body' => '<p>Hello {{name}},</p><p>Your OTP is: <strong>{{otp}}</strong></p><p>Valid for {{minutes}} minutes.</p>',
                'text_body' => "Hello {{name}},\nYour OTP is: {{otp}}\nValid for {{minutes}} minutes.",
            ],
            [
                'slug' => 'welcome',
                'name' => 'Welcome Email',
                'subject' => 'Welcome to {{site_name}}',
                'type' => 'transactional',
                'html_body' => '<p>Hello {{name}},</p><p>Welcome to {{site_name}}!</p>',
                'text_body' => 'Hello {{name}}, Welcome to {{site_name}}!',
            ],
            [
                'slug' => 'promo',
                'name' => 'Promotional Email',
                'subject' => '{{subject_line}}',
                'type' => 'promo',
                'html_body' => '<p>Hello {{name}},</p><p>{{message}}</p><p><a href="{{unsubscribe_url}}">Unsubscribe</a></p>',
                'text_body' => "Hello {{name}},\n{{message}}\nUnsubscribe: {{unsubscribe_url}}",
            ],
        ];

        foreach ($templates as $template) {
            Template::query()->firstOrCreate(
                ['slug' => $template['slug'], 'tenant_id' => null],
                $template
            );
        }
    }
}
