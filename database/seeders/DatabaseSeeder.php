<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // 1. Seed roles and permissions first
        $this->call(PermissionsSeeder::class);

        // 2. Create super admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@platform.test'],
            [
                'ulid'              => (string) Str::ulid(),
                'name'              => 'Super Admin',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
                'timezone'          => 'Asia/Kolkata',
                'locale'            => 'en',
                'is_active'         => true,
            ]
        );
        $admin->assignRole('super_admin');

        // 3. Seed plans and features
        $this->seedPlans();

        $this->command->info('Database seeded successfully.');
    }

    private function seedPlans(): void
    {
        $plans = [
            [
                'plan' => [
                    'name'          => 'Free',
                    'slug'          => 'free',
                    'description'   => 'Get started for free with basic link shortening.',
                    'price_monthly' => 0,
                    'price_yearly'  => 0,
                    'currency'      => 'INR',
                    'is_active'     => true,
                    'is_public'     => true,
                    'sort_order'    => 1,
                    'trial_days'    => 0,
                ],
                'features' => [
                    'links_per_month'          => '20',
                    'custom_domains_count'     => '0',
                    'domain_whitelist'         => 'false',
                    'custom_subdomain'         => 'false',
                    'meta_tracking'            => 'false',
                    'google_tracking'          => 'false',
                    'analytics_level'          => 'basic',
                    'qrcode'                   => 'false',
                    'password_protected_links' => 'false',
                    'geo_metrics'              => 'false',
                    'bot_detection'            => 'false',
                    'spam_detection'           => 'false',
                    'team_members_count'       => '0',
                    'link_expiry'              => 'false',
                    'bulk_links'               => 'false',
                    'campaign_tracking'        => 'false',
                ],
            ],
            [
                'plan' => [
                    'name'          => 'Pro',
                    'slug'          => 'pro',
                    'description'   => 'For professionals and growing businesses.',
                    'price_monthly' => 499,
                    'price_yearly'  => 4999,
                    'currency'      => 'INR',
                    'is_active'     => true,
                    'is_public'     => true,
                    'sort_order'    => 2,
                    'trial_days'    => 14,
                ],
                'features' => [
                    'links_per_month'          => '500',
                    'custom_domains_count'     => '2',
                    'domain_whitelist'         => 'false',
                    'custom_subdomain'         => 'true',
                    'meta_tracking'            => 'true',
                    'google_tracking'          => 'true',
                    'analytics_level'          => 'advanced',
                    'qrcode'                   => 'true',
                    'password_protected_links' => 'true',
                    'geo_metrics'              => 'true',
                    'bot_detection'            => 'true',
                    'spam_detection'           => 'true',
                    'team_members_count'       => '0',
                    'link_expiry'              => 'true',
                    'bulk_links'               => 'false',
                    'campaign_tracking'        => 'true',
                ],
            ],
            [
                'plan' => [
                    'name'          => 'Business',
                    'slug'          => 'business',
                    'description'   => 'For teams and enterprises with full feature access.',
                    'price_monthly' => 1499,
                    'price_yearly'  => 14999,
                    'currency'      => 'INR',
                    'is_active'     => true,
                    'is_public'     => true,
                    'sort_order'    => 3,
                    'trial_days'    => 14,
                ],
                'features' => [
                    'links_per_month'          => '-1',  // -1 = unlimited
                    'custom_domains_count'     => '10',
                    'domain_whitelist'         => 'true',
                    'custom_subdomain'         => 'true',
                    'meta_tracking'            => 'true',
                    'google_tracking'          => 'true',
                    'analytics_level'          => 'advanced',
                    'qrcode'                   => 'true',
                    'password_protected_links' => 'true',
                    'geo_metrics'              => 'true',
                    'bot_detection'            => 'true',
                    'spam_detection'           => 'true',
                    'team_members_count'       => '-1',  // -1 = unlimited
                    'link_expiry'              => 'true',
                    'bulk_links'               => 'true',
                    'campaign_tracking'        => 'true',
                ],
            ],
        ];

        foreach ($plans as $data) {
            $plan = Plan::firstOrCreate(
                ['slug' => $data['plan']['slug']],
                array_merge($data['plan'], ['ulid' => (string) Str::ulid()])
            );

            foreach ($data['features'] as $key => $value) {
                PlanFeature::updateOrCreate(
                    ['plan_id' => $plan->id, 'feature_key' => $key],
                    ['feature_value' => $value]
                );
            }
        }

        $this->command->info('Plans and features seeded.');
    }
}

