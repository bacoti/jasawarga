<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\PartnerProfile;
use App\Models\PartnerService;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Services\Analytics\AdminAnalyticsService;
use App\Services\Booking\ServiceCatalog;
use App\Services\Feedback\ReviewBookingVerifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Cross-module acceptance tests to run against the merged database schema. */
class Stage7IntegratedModulesTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, string $name): User
    {
        return User::create([
            'name' => $name, 'email' => uniqid('integration-', true).'@example.test',
            'role' => $role, 'account_status' => 'active', 'password' => 'TestPassword2026!',
        ]);
    }

    private function service(): array
    {
        $partnerUser = $this->user('partner', 'Mitra Integrasi');
        $partner = PartnerProfile::create([
            'user_id' => $partnerUser->id, 'business_name' => 'Mitra Integrasi',
            'business_phone' => '081234567890', 'service_area' => 'Makassar',
            'slug' => 'mitra-integrasi-'.uniqid(), 'verification_status' => 'approved',
            'reverification_required' => false,
        ]);
        $category = ServiceCategory::create([
            'name' => 'Kebersihan', 'slug' => 'kebersihan-'.uniqid(), 'icon' => 'home',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $service = PartnerService::create([
            'partner_profile_id' => $partner->id,
            'service_category_id' => $category->id, 'name' => 'Cuci sofa',
            'description' => 'Jasa kebersihan sofa', 'base_price' => 120000,
            'duration_minutes' => 60, 'is_active' => true,
        ]);
        return [$partnerUser, $partner, $category, $service];
    }

    public function test_all_database_route_groups_exist_and_guest_must_login(): void
    {
        foreach (['account.profile.show', 'account.addresses.index', 'partner.profile.edit',
            'partner.portfolio.index', 'account.bookings.index', 'partner.bookings.index',
            'admin.bookings.index', 'account.analytics.dashboard',
            'partner.analytics.dashboard', 'admin.analytics.dashboard',
            'notifications.index'] as $routeName) {
            $this->assertTrue(\Illuminate\Support\Facades\Route::has($routeName), $routeName);
            $this->get(route($routeName))->assertRedirect(route('account.login'));
        }
        $this->assertSame('/admin/laporan', parse_url(route('admin.reports'), PHP_URL_PATH));
        $this->assertSame('/admin/laporan-db', parse_url(route('admin.analytics.reports'), PHP_URL_PATH));
        $this->assertSame('/admin/pesanan-db', parse_url(route('admin.bookings.index'), PHP_URL_PATH));
    }

    public function test_stage3_fixed_price_adapter_and_reverification_visibility(): void
    {
        [, $partner, $category, $service] = $this->service();
        $catalog = app(ServiceCatalog::class);
        $value = $catalog->findActiveForPartner($partner->id, $service->id);
        $this->assertSame('fixed', $value['price_type']);
        $this->assertSame(120000.0, $value['price']);
        $this->assertCount(1, $catalog->activeForPartner($partner->id));

        $category->update(['is_active' => false]);
        $this->assertCount(0, $catalog->activeForPartner($partner->id));
        $category->update(['is_active' => true]);
        $partner->update(['reverification_required' => true]);
        $this->assertFalse($partner->fresh()->isPubliclyVisible());
        $this->assertCount(0, $catalog->activeForPartner($partner->id));
    }

    public function test_stage5_verifier_requires_matching_completed_booking_in_postgresql(): void
    {
        [, $partner, , $service] = $this->service();
        $warga = $this->user('citizen', 'Warga Penguji');
        $other = $this->user('citizen', 'Warga Lain');
        $booking = Booking::create([
            'booking_code' => 'JW-261107-ABCDEF12',
            'user_id' => $warga->id, 'partner_profile_id' => $partner->id,
            'service_id' => $service->id, 'service_name_snapshot' => $service->name,
            'price_type_snapshot' => 'fixed', 'initial_price_snapshot' => 120000,
            'agreed_price' => 120000, 'duration_minutes' => 60,
            'scheduled_at' => now()->addDays(1), 'address_snapshot' => ['district' => 'Makassar'],
            'status' => Booking::PENDING_PARTNER, 'partner_response_deadline' => now()->addHours(2),
        ]);
        $verifier = app(ReviewBookingVerifier::class);
        $this->assertFalse($verifier->isEligible($warga->id, $service->id, $booking->booking_code));
        $booking->update(['status' => Booking::COMPLETED, 'completed_at' => now()]);
        $this->assertTrue($verifier->isEligible($warga->id, $service->id, $booking->booking_code));
        $this->assertFalse($verifier->isEligible($other->id, $service->id, $booking->booking_code));
        $this->assertFalse($verifier->isEligible($warga->id, $service->id + 999, $booking->booking_code));
        $this->assertFalse($verifier->isEligible($warga->id, $service->id, 'JW-DEMO-LOCALSTORAGE'));
    }

    public function test_stage6_counts_stage1_canonical_roles_and_stage3_catalog_tables(): void
    {
        $this->user('citizen', 'Warga A');
        [, , , $service] = $this->service();
        $admin = $this->user('admin', 'Admin A');
        $summary = app(AdminAnalyticsService::class)->dashboard();
        $this->assertSame(1, $summary['users']['warga']);
        $this->assertSame(1, $summary['users']['mitra']);
        $this->assertSame(1, $summary['users']['admin']);
        $this->assertSame(1, $summary['catalog']['categories']);
        $this->assertSame(1, $summary['catalog']['services']);
    }
}
