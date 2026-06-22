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
            [
                'slug' => 'semrushtoolz-login-otp',
                'name' => 'SemrushToolz — Login OTP',
                'subject' => 'Your Semrushtoolz login code',
                'type' => 'transactional',
                'html_body' => '<p>Hello {{name}},</p><p>{{intro}}</p><p>Your verification code is:</p><p style="font-size:24px;font-weight:bold;letter-spacing:4px;">{{otp}}</p><p>This code expires in {{minutes}} minutes.</p><p>If you did not request this, you can safely ignore this email.</p>',
                'text_body' => "Hello {{name}},\n\n{{intro}}\n\nYour verification code is: {{otp}}\n\nThis code expires in {{minutes}} minutes.\n\nIf you did not request this, you can safely ignore this email.",
            ],
            [
                'slug' => 'semrushtoolz-periodic-otp',
                'name' => 'SemrushToolz — Security OTP',
                'subject' => 'Security verification — Semrushtoolz login',
                'type' => 'transactional',
                'html_body' => '<p>Hello {{name}},</p><p>{{intro}}</p><p>Your verification code is:</p><p style="font-size:24px;font-weight:bold;letter-spacing:4px;">{{otp}}</p><p>This code expires in {{minutes}} minutes.</p><p>If you did not request this, you can safely ignore this email.</p>',
                'text_body' => "Hello {{name}},\n\n{{intro}}\n\nYour verification code is: {{otp}}\n\nThis code expires in {{minutes}} minutes.\n\nIf you did not request this, you can safely ignore this email.",
            ],
            [
                'slug' => 'semrushtoolz-verify-email',
                'name' => 'SemrushToolz — Email Verification',
                'subject' => 'Confirm your Semrushtoolz account',
                'type' => 'transactional',
                'html_body' => '<p>Hello {{name}},</p><p>Thanks for signing up. Please confirm your email address to activate your account and access the shop.</p><p><a href="{{verification_url}}" style="display:inline-block;padding:12px 24px;background:#7c3aed;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">Verify Email Address</a></p><p>This link expires in 60 minutes.</p><p>If you did not create an account, no action is required.</p>',
                'text_body' => "Hello {{name}},\n\nThanks for signing up. Please confirm your email address to activate your account.\n\nVerify: {{verification_url}}\n\nThis link expires in 60 minutes.\n\nIf you did not create an account, no action is required.",
            ],
            [
                'slug' => 'semrushtoolz-reset-password',
                'name' => 'SemrushToolz — Password Reset',
                'subject' => 'Reset your Semrushtoolz password',
                'type' => 'transactional',
                'html_body' => '<p>Hello {{name}},</p><p>We received a request to reset the password for your Semrushtoolz account.</p><p><a href="{{reset_url}}" style="display:inline-block;padding:12px 24px;background:#7c3aed;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">Reset Password</a></p><p>This link expires in {{expire_minutes}} minutes.</p><p>If you did not request a password reset, no action is required.</p>',
                'text_body' => "Hello {{name}},\n\nWe received a request to reset your Semrushtoolz password.\n\nReset: {{reset_url}}\n\nThis link expires in {{expire_minutes}} minutes.\n\nIf you did not request a password reset, no action is required.",
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
