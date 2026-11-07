<?php

namespace Tests\Feature;

use App\Models\PartnerProfile;
use App\Models\PartnerService;
use App\Models\ServiceCategory;
use App\Models\ServiceReview;
use App\Models\ServiceReviewModerationLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Critical cross-module invariants: pending evidence is not a verified review. */
class Stage7DataBoundaryTest extends TestCase
{
    use RefreshDatabase;

    private function setupService(): array
    {
        $partner = User::create([
            'name'=>'Stage7 Mitra', 'email'=>'s7.partner@example.test',
            'role'=>'partner', 'account_status'=>'active', 'password'=>'StrongPass2026!',
        ]);
        $profile = PartnerProfile::create([
            'user_id'=>$partner->id, 'business_name'=>'Mitra Integrasi',
            'business_phone'=>'081234567890', 'service_area'=>'Makassar',
            'verification_status'=>'approved',
        ]);
        $category = ServiceCategory::create([
            'name'=>'Listrik', 'slug'=>'listrik-stage7', 'icon'=>'zap',
            'sort_order'=>1, 'is_active'=>true,
        ]);
        $service = PartnerService::create([
            'partner_profile_id'=>$profile->id,
            'service_category_id'=>$category->id,
            'name'=>'Servis Listrik Integrasi', 'base_price'=>150000,
            'duration_minutes'=>60, 'is_active'=>true,
        ]);
        return [$partner, $profile, $category, $service];
    }

    private function user(string $role, string $email): User
    {
        return User::create([
            'name'=>'Tester '.ucfirst($role), 'email'=>$email,
            'role'=>$role, 'account_status'=>'active', 'password'=>'StrongPass2026!',
        ]);
    }

    public function test_fake_booking_reference_never_publishes_a_review_even_after_admin_unhides_it(): void
    {
        [,,$category,$service] = $this->setupService();
        $citizen = $this->user('citizen', 's7.citizen@example.test');
        $admin = $this->user('admin', 's7.admin@example.test');
        $marker = 'MARKER_TAHAP7_REFERENSI_TIDAK_SAH';
        $this->actingAs($citizen)->post(route('db-feedback.reviews.store'), [
            'partner_service_id'=>$service->id,
            'booking_reference'=>'JW-FAKE-STAGE7-001',
            'rating'=>5, 'quality_rating'=>5,
            'timeliness_rating'=>5, 'friendliness_rating'=>5,
            'comment'=>'Komentar pengujian integrasi '.$marker.' yang tidak pernah terkait pesanan nyata.',
        ])->assertRedirect(route('db-feedback.reviews.mine'));
        $review = ServiceReview::firstOrFail();
        $this->assertSame('pending', $review->verification_status);
        $this->get(route('db-feedback.reviews.public'))->assertDontSee($marker);

        $this->actingAs($admin)->post(route('admin.db-feedback.reviews.recheck', $review))
            ->assertSessionHasErrors('verifikasi');
        $this->patch(route('admin.db-feedback.reviews.moderate', $review), [
            'moderation_status'=>'hidden', 'reason'=>'Ditahan untuk meninjau bukti pesanan.',
        ])->assertRedirect();
        $this->patch(route('admin.db-feedback.reviews.moderate', $review), [
            'moderation_status'=>'visible', 'reason'=>'Moderasi selesai, bukti transaksi masih harus diverifikasi.',
        ])->assertRedirect();
        $this->assertSame('pending', $review->fresh()->verification_status);
        $this->assertSame(2, ServiceReviewModerationLog::count());
        $this->get(route('db-feedback.reviews.public'))->assertDontSee($marker);
        $category->update(['is_active'=>false]);
        $this->get(route('db-catalog.index'))->assertDontSee('Servis Listrik Integrasi');
        $this->get(route('db-catalog.show', $service))->assertNotFound();
    }

    public function test_mitra_from_other_profile_cannot_modify_service_even_if_knows_id(): void
    {
        [,,$category,$service] = $this->setupService();
        $stranger = $this->user('partner', 's7.stranger@example.test');
        PartnerProfile::create([
            'user_id'=>$stranger->id, 'business_name'=>'Usaha Asing',
            'business_phone'=>'081234567811', 'service_area'=>'Makassar',
            'verification_status'=>'approved',
        ]);
        $this->actingAs($stranger)->patch(route('partner.db-services.update', $service), [
            'name'=>'Bajak Layanan', 'service_category_id'=>$category->id,
            'base_price'=>1000, 'duration_minutes'=>30,
        ])->assertNotFound();
        $this->delete(route('partner.db-services.destroy', $service))->assertNotFound();
        $this->assertSame('Servis Listrik Integrasi', $service->fresh()->name);
        $this->assertNull($service->fresh()->deleted_at);
    }
}
