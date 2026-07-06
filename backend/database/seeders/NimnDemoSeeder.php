<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\MembershipPlan;
use App\Models\MembershipRenewal;
use App\Models\Payment;
use App\Models\Publication;
use App\Models\PublicationCategory;
use App\Models\PublicationDownload;
use App\Models\PublicationPurchase;
use App\Models\Receipt;
use App\Models\ResourceDocument;
use App\Models\TrainingEvent;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NimnDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@nimn.org.ng'],
            [
                'first_name' => 'NIMN',
                'last_name' => 'Admin',
                'phone_number' => '08000000000',
                'password' => 'password',
                'role' => 'super_admin',
                'status' => 'active',
            ],
        );

        $plans = collect([
            ['name' => 'Annual Associate Membership Renewal', 'grade' => 'associate', 'amount' => 25000],
            ['name' => 'Annual Full Membership Renewal', 'grade' => 'full', 'amount' => 40000],
            ['name' => 'Annual Fellow Membership Renewal', 'grade' => 'fellow', 'amount' => 60000],
        ])->mapWithKeys(function (array $plan) {
            $model = MembershipPlan::updateOrCreate(
                ['name' => $plan['name']],
                [
                    ...$plan,
                    'currency' => 'NGN',
                    'duration_months' => 12,
                    'is_active' => true,
                ],
            );

            return [$plan['grade'] => $model];
        });

        $members = collect([
            ['MNIMN/2024/001', 'Amina', 'Bello', 'amina.bello@example.test', 'associate', 'active', 'Lagos Retail Group', 'Marketing Manager', 'active'],
            ['MNIMN/2024/002', 'Tunde', 'Adewale', 'tunde.adewale@example.test', 'full', 'active', 'GrowthEdge Consulting', 'Brand Strategist', 'active'],
            ['MNIMN/2024/003', 'Ifeoma', 'Okeke', 'ifeoma.okeke@example.test', 'fellow', 'active', 'MarketBridge Africa', 'Director of Marketing', 'active'],
            ['MNIMN/2024/004', 'Sani', 'Usman', 'sani.usman@example.test', 'associate', 'pending', 'Northstar FMCG', 'Sales Executive', 'pending'],
            ['MNIMN/2024/005', 'Grace', 'Eze', 'grace.eze@example.test', 'full', 'active', 'BluePeak Communications', 'Account Lead', 'pending'],
            ['MNIMN/2024/006', 'Chinedu', 'Nwosu', 'chinedu.nwosu@example.test', 'associate', 'active', 'MetroFoods Limited', 'Trade Marketing Officer', 'expired'],
            ['MNIMN/2024/007', 'Fatima', 'Abubakar', 'fatima.abubakar@example.test', 'full', 'suspended', 'Prime Reach Media', 'Media Planner', 'expired'],
            ['MNIMN/2024/008', 'Daniel', 'Ojo', 'daniel.ojo@example.test', 'associate', 'active', 'Dara Analytics', 'Research Analyst', 'active'],
            ['MNIMN/2024/009', 'Kemi', 'Lawal', 'kemi.lawal@example.test', 'fellow', 'active', 'Nexus Brands', 'Chief Marketing Officer', 'pending'],
            ['MNIMN/2024/010', 'Musa', 'Garba', 'musa.garba@example.test', 'associate', 'pending', 'Sahel Stores', 'Customer Growth Officer', 'pending'],
            ['MNIMN/2024/011', 'Ngozi', 'Umeh', 'ngozi.umeh@example.test', 'full', 'active', 'Digital Compass', 'Product Marketing Lead', 'active'],
            ['MNIMN/2024/012', 'Bamidele', 'Sowunmi', 'bamidele.sowunmi@example.test', 'associate', 'inactive', 'Harbor Trade', 'Business Development Officer', 'expired'],
        ])->map(function (array $row) use ($plans) {
            [$number, $firstName, $lastName, $email, $grade, $status, $organization, $jobTitle, $renewalStatus] = $row;

            $member = User::updateOrCreate(
                ['email' => $email],
                [
                    'membership_number' => $number,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone_number' => '080' . random_int(10000000, 99999999),
                    'password' => 'password',
                    'role' => 'member',
                    'status' => $status,
                    'membership_grade' => $grade,
                    'organization' => $organization,
                    'job_title' => $jobTitle,
                    'address' => 'NIMN Demo Address, Lagos',
                ],
            );

            $plan = $plans[$grade];
            $paid = $renewalStatus === 'active';
            $expired = $renewalStatus === 'expired';

            MembershipRenewal::updateOrCreate(
                ['member_id' => $member->id, 'membership_plan_id' => $plan->id],
                [
                    'starts_at' => $expired ? now()->subMonths(15)->toDateString() : now()->subMonths(2)->toDateString(),
                    'expires_at' => $expired ? now()->subMonths(3)->toDateString() : now()->addMonths($paid ? 10 : 1)->toDateString(),
                    'amount' => $plan->amount,
                    'currency' => 'NGN',
                    'status' => $renewalStatus,
                    'paid_at' => $paid ? now()->subDays(random_int(5, 80)) : null,
                ],
            );

            return $member;
        });

        $categories = collect([
            ['name' => 'Research Reports', 'slug' => 'research-reports', 'description' => 'NIMN research and market intelligence reports.'],
            ['name' => 'Professional Guides', 'slug' => 'professional-guides', 'description' => 'Guides and resources for marketing professionals.'],
            ['name' => 'Journal Publications', 'slug' => 'journal-publications', 'description' => 'Academic and professional journal editions.'],
        ])->mapWithKeys(function (array $category) {
            return [
                $category['slug'] => PublicationCategory::updateOrCreate(
                    ['slug' => $category['slug']],
                    $category,
                ),
            ];
        });

        $publications = collect([
            ['2026 Nigerian Consumer Outlook', '2026-nigerian-consumer-outlook', 'research-reports', 2026, 15000, 'published', true],
            ['Digital Marketing Practice Guide', 'digital-marketing-practice-guide', 'professional-guides', 2025, 9000, 'published', true],
            ['NIMN Journal Vol. 18', 'nimn-journal-vol-18', 'journal-publications', 2025, 12000, 'published', false],
            ['Brand Strategy Field Manual', 'brand-strategy-field-manual', 'professional-guides', 2024, 7500, 'draft', false],
        ])->map(function (array $row) use ($admin, $categories) {
            [$title, $slug, $categorySlug, $year, $price, $status, $featured] = $row;

            return Publication::updateOrCreate(
                ['slug' => $slug],
                [
                    'publication_category_id' => $categories[$categorySlug]->id,
                    'uploaded_by_admin_id' => $admin->id,
                    'title' => $title,
                    'description' => "Demo publication for {$title}.",
                    'subject' => 'Marketing',
                    'edition' => 'Digital Edition',
                    'publication_year' => $year,
                    'department' => 'Research and Publications',
                    'price' => $price,
                    'currency' => 'NGN',
                    'file_path' => "publications/{$slug}.pdf",
                    'file_name' => Str::slug($title) . '.pdf',
                    'mime_type' => 'application/pdf',
                    'file_size' => random_int(850000, 3200000),
                    'status' => $status,
                    'is_featured' => $featured,
                    'published_at' => $status === 'published' ? now()->subDays(random_int(10, 120)) : null,
                ],
            );
        });

        collect([
            [
                'title' => 'National Advertising Conference',
                'slug' => 'national-advertising-conference',
                'tag' => 'Conference',
                'event_type' => 'upcoming',
                'date_label' => 'Nov 11 - 13, 2026',
                'starts_at' => '2026-11-11',
                'time_label' => 'Schedule to be announced',
                'location' => 'Details to be announced',
                'venue' => null,
                'description' => 'Training, professional development and event updates from the National Institute of Marketing of Nigeria.',
                'register_url' => 'https://nationaladvertisingconference.com/',
            ],
            [
                'title' => 'LeadHers In Marketing Conference',
                'slug' => 'leadhers-conference',
                'tag' => 'Past Event',
                'event_type' => 'past',
                'date_label' => 'March 6, 2026',
                'starts_at' => '2026-03-06',
                'time_label' => null,
                'location' => null,
                'venue' => null,
                'description' => 'LeadHers in Marketing is an NIMN initiative designed to empower women in the marketing profession through leadership, visibility, impact, growth, mentorship and professional development.',
                'register_url' => null,
            ],
            [
                'title' => 'Investiture of President & Fellows Induction',
                'slug' => 'president-investiture',
                'tag' => 'Past Event',
                'event_type' => 'past',
                'date_label' => 'December 15, 2025',
                'starts_at' => '2025-12-15',
                'time_label' => null,
                'location' => null,
                'venue' => null,
                'description' => "An event marking a significant milestone in NIMN's leadership journey, bringing together marketing professionals, industry leaders, policymakers and corporate executives while honoring distinguished Fellows of the Institute.",
                'register_url' => null,
            ],
            [
                'title' => 'Associates & Full Members Induction',
                'slug' => 'induction',
                'tag' => 'Past Event',
                'event_type' => 'past',
                'date_label' => 'March 6, 2026',
                'starts_at' => '2026-03-06',
                'time_label' => null,
                'location' => 'Civic Center, Ozumba Mbadiwe, Victoria Island',
                'venue' => 'Civic Center, Ozumba Mbadiwe, Victoria Island',
                'description' => 'Induction of new members into the Institute as Associates and Full Members.',
                'register_url' => null,
            ],
        ])->each(function (array $event) use ($admin) {
            TrainingEvent::updateOrCreate(
                ['slug' => $event['slug']],
                [
                    ...$event,
                    'created_by_admin_id' => $admin->id,
                    'status' => 'published',
                    'is_featured' => $event['event_type'] === 'upcoming',
                    'published_at' => now(),
                ],
            );
        });

        $members->take(8)->values()->each(function (User $member, int $index) use ($publications) {
            $publication = $publications[$index % $publications->count()];

            $purchase = PublicationPurchase::updateOrCreate(
                ['member_id' => $member->id, 'publication_id' => $publication->id],
                [
                    'amount' => $publication->price,
                    'currency' => 'NGN',
                    'status' => $index < 6 ? 'successful' : 'pending',
                    'paid_at' => $index < 6 ? now()->subDays($index + 2) : null,
                ],
            );

            if ($purchase->status === 'successful') {
                PublicationDownload::updateOrCreate(
                    ['member_id' => $member->id, 'publication_id' => $publication->id],
                    ['ip_address' => '127.0.0.1', 'user_agent' => 'NIMN Demo Browser'],
                );
            }
        });

        $members->take(10)->values()->each(function (User $member, int $index) {
            $successful = $index < 7;
            $payment = Payment::updateOrCreate(
                ['reference' => 'NIMN-DEMO-PAY-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT)],
                [
                    'member_id' => $member->id,
                    'purpose' => $index % 3 === 0 ? 'publication' : 'membership_renewal',
                    'provider' => 'xpouch',
                    'provider_reference' => 'XPOUCH-DEMO-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                    'amount' => [25000, 40000, 60000, 15000, 9000][$index % 5],
                    'currency' => 'NGN',
                    'status' => $successful ? 'successful' : ($index % 2 === 0 ? 'pending' : 'processing'),
                    'checkout_url' => 'https://checkout.xpouch.co/demo/nimn',
                    'provider_payload' => ['demo' => true, 'channel' => $index % 2 === 0 ? 'card' : 'transfer'],
                    'paid_at' => $successful ? now()->subDays($index + 1) : null,
                ],
            );

            if ($successful) {
                Receipt::updateOrCreate(
                    ['receipt_number' => 'NIMN-RCPT-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT)],
                    [
                        'payment_id' => $payment->id,
                        'member_id' => $member->id,
                        'amount' => $payment->amount,
                        'currency' => $payment->currency,
                        'issued_at' => $payment->paid_at ?? now(),
                    ],
                );
            }
        });

        collect([
            ['Marketing Ethics Handbook', 'Professional Standards', 'members', true],
            ['Membership Onboarding Pack', 'Membership', 'members', true],
            ['Chapter Event Planning Checklist', 'Events', 'admins', true],
        ])->each(function (array $row) use ($admin) {
            [$title, $category, $visibility, $published] = $row;

            ResourceDocument::updateOrCreate(
                ['title' => $title],
                [
                    'uploaded_by_admin_id' => $admin->id,
                    'description' => "Demo resource for {$category}.",
                    'category' => $category,
                    'file_path' => 'resources/' . Str::slug($title) . '.pdf',
                    'file_name' => Str::slug($title) . '.pdf',
                    'mime_type' => 'application/pdf',
                    'file_size' => random_int(350000, 1200000),
                    'visibility' => $visibility,
                    'is_published' => $published,
                    'published_at' => $published ? now()->subDays(random_int(3, 30)) : null,
                ],
            );
        });

        collect([
            ['Renewal window is open', 'Members can renew their annual subscription from the portal using xPouch.', 'members'],
            ['New publication added', 'The 2026 Nigerian Consumer Outlook is now available in the publication library.', 'members'],
            ['Admin review queue', 'Pending member applications and renewals require review this week.', 'admins'],
        ])->each(function (array $row) use ($admin) {
            [$title, $body, $audience] = $row;

            Announcement::updateOrCreate(
                ['title' => $title],
                [
                    'created_by_admin_id' => $admin->id,
                    'body' => $body,
                    'audience' => $audience,
                    'is_published' => true,
                    'published_at' => now()->subDays(random_int(1, 12)),
                ],
            );
        });
    }
}
