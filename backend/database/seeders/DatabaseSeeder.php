<?php

namespace Database\Seeders;

use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@nimn.org.ng'],
            [
                'first_name' => 'NIMN',
                'last_name' => 'Admin',
                'phone_number' => null,
                'password' => 'password',
                'role' => 'super_admin',
                'status' => 'active',
            ],
        );

        $plans = [
            ['name' => 'Annual Associate Membership Renewal', 'grade' => 'associate', 'amount' => 25000, 'duration_months' => 12],
            ['name' => 'Annual Full Membership Renewal', 'grade' => 'full', 'amount' => 40000, 'duration_months' => 12],
            ['name' => 'Annual Fellow Membership Renewal', 'grade' => 'fellow', 'amount' => 60000, 'duration_months' => 12],
        ];

        foreach ($plans as $plan) {
            MembershipPlan::firstOrCreate(
                ['name' => $plan['name']],
                [
                    ...$plan,
                    'currency' => 'NGN',
                    'is_active' => true,
                ],
            );
        }
    }
}
