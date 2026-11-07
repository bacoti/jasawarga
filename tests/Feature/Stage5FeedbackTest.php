<?php

namespace Tests\Feature;

use App\Models\PartnerProfile;
use App\Models\PartnerService;
use App\Models\ServiceCategory;
use App\Models\ServiceComplaint;
use App\Models\ServiceComplaintAction;
use App\Models\ServiceReview;
use App\Models\ServiceReviewModerationLog;
use App\Models\ServiceReviewReply;
use App\Models\User;
use App\Services\Feedback\ReviewBookingVerifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Stage5FeedbackTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 0;

    private function user(string $role = 'citizen'): User
    {
        $this->sequence++;
        return User::create([
            'name' => ucfirst($role).' '.$this->sequence,
            'email' => "stage5.{$role}.{$this->sequence}@example.test",
            'role' => $role,
            'account_status' => 'active',
            'password' => 'SecurePass2026!',
        ]);
    }

    private function service(): array
    {
        $partnerUser = $this->user('partner');
        $profile = PartnerProfile::create([
            'user_id' => $partnerUser->id,
            'business_name' => 'Jasa Bersih Profesional',
            'business_phone' => '081234567899',
            'service_area' => 'Makassar',
            'verification_status' => 'approved',
        ]);
        $category = ServiceCategory::create([
            'name' => 'Kebersihan', 'slug' => 'kebersihan', 'icon' => 'cleaning_services',
            'sort_order' => 1, 'is_active' => true,
        ]);
        $service = PartnerService::create([
            'partner_profile_id' => $profile->id,
            'service_category_id' => $category->id,
            'name' => 'Bersih rumah menyeluruh',
            'description' => 'Pembersihan rumah',
            'base_price' => 100000,
            'duration_minutes' => 120,
            'is_active' => true,
        ]);
        return [$partnerUser, $profile, $service];
    }

    private function reviewPayload(PartnerService $service): array
    {
        return [
            'partner_service_id' => $service->id,
            'booking_reference' => 'JW-REAL-2026-01',
            'rating' => 5,
            'quality_rating' => 5,
            'timeliness_rating' => 4,
            'friendliness_rating' => 5,
            'comment' => 'Pekerjaan diselesaikan dengan rapi, komunikatif dan sesuai pesanan.',
        ];
    }

    private function verifiedGateway(): void
    {
        $this->app->instance(ReviewBookingVerifier::class, new class extends ReviewBookingVerifier {
            public function isEligible(int $citizenId, int $partnerServiceId, string $bookingReference): bool
            {
                return $bookingReference === 'JW-REAL-2026-01';
            }
        });
    }

    public function test_guest_can_only_access_public_reviews(): void
    {
        $this->get(route('db-feedback.reviews.public'))->assertOk();
        $this->get(route('db-feedback.reviews.create'))->assertRedirect('/masuk');
        $this->get(route('db-feedback.complaints.mine'))->assertRedirect('/masuk');
        $this->get(route('admin.db-feedback.reviews.index'))->assertRedirect('/masuk');
    }

    public function test_citizen_review_defaults_pending_and_cannot_appear_public(): void
    {
        [,,$service] = $this->service();
        $citizen = $this->user();
        $this->actingAs($citizen)->post(route('db-feedback.reviews.store'), $this->reviewPayload($service))
            ->assertRedirect(route('db-feedback.reviews.mine'));
        $review = ServiceReview::firstOrFail();
        $this->assertSame('pending', $review->verification_status);
        $this->assertSame('visible', $review->moderation_status);
        $this->assertSame($citizen->id, $review->citizen_id);
        $this->assertSame($service->partner_profile_id, $review->partner_profile_id);
        $this->get(route('db-feedback.reviews.public'))->assertDontSee('Pekerjaan diselesaikan');
        $this->get(route('db-feedback.reviews.mine'))->assertSee('Menunggu verifikasi pesanan');
        $this->post(route('db-feedback.reviews.store'), $this->reviewPayload($service))
            ->assertSessionHasErrors('booking_reference');
    }

    public function test_ineligible_review_cannot_be_verified_just_by_admin_click(): void
    {
        [,,$service] = $this->service();
        $citizen = $this->user();
        $admin = $this->user('admin');
        $this->actingAs($citizen)->post(route('db-feedback.reviews.store'), $this->reviewPayload($service));
        $review = ServiceReview::firstOrFail();
        $this->actingAs($admin)->post(route('admin.db-feedback.reviews.recheck', $review))
            ->assertSessionHasErrors('verifikasi');
        $this->assertSame('pending', $review->fresh()->verification_status);
        $this->patch(route('admin.db-feedback.reviews.moderate', $review), [
            'moderation_status' => 'hidden', 'reason' => 'Komentar sedang diperiksa lebih lanjut.',
        ])->assertRedirect();
        $this->assertSame(1, ServiceReviewModerationLog::count());
        $this->get(route('db-feedback.reviews.public'))->assertDontSee($review->comment);
    }

    public function test_verified_review_publishes_and_only_own_partner_can_reply(): void
    {
        [$partner, $profile, $service] = $this->service();
        $citizen = $this->user();
        $stranger = $this->user('partner');
        PartnerProfile::create([
            'user_id' => $stranger->id, 'business_name' => 'Usaha Lain',
            'business_phone' => '081234567811', 'service_area' => 'Tallo',
            'verification_status' => 'approved',
        ]);
        $this->verifiedGateway();
        $this->actingAs($citizen)->post(route('db-feedback.reviews.store'), $this->reviewPayload($service))
            ->assertRedirect();
        $review = ServiceReview::firstOrFail();
        $this->assertSame('verified', $review->verification_status);
        $this->get(route('db-feedback.reviews.public'))->assertSee($review->comment);

        $this->actingAs($stranger)->post(route('partner.db-feedback.reviews.reply', $review), [
            'body' => 'Terima kasih sudah menggunakan layanan kami.',
        ])->assertNotFound();
        $this->assertSame(0, ServiceReviewReply::count());
        $this->actingAs($partner)->post(route('partner.db-feedback.reviews.reply', $review), [
            'body' => 'Terima kasih, kami akan terus meningkatkan pelayanan.',
        ])->assertRedirect();
        $this->assertDatabaseHas('service_review_replies', [
            'service_review_id' => $review->id, 'partner_profile_id' => $profile->id,
        ]);
        $this->get(route('db-feedback.reviews.public'))->assertSee('meningkatkan pelayanan');
        $this->post(route('partner.db-feedback.reviews.reply', $review), [
            'body' => 'Terima kasih atas masukan dan dukungan yang diberikan.',
        ])->assertRedirect();
        $this->assertSame(1, ServiceReviewReply::count());
        $this->delete(route('partner.db-feedback.reviews.destroy-reply', $review))->assertRedirect();
        $this->assertSame(0, ServiceReviewReply::count());
        $this->assertSame(5, $review->fresh()->rating);
    }

    public function test_admin_moderation_hides_and_restores_verified_review_with_log(): void
    {
        [,,$service] = $this->service();
        $citizen = $this->user();
        $admin = $this->user('admin');
        $this->verifiedGateway();
        $this->actingAs($citizen)->post(route('db-feedback.reviews.store'), $this->reviewPayload($service));
        $review = ServiceReview::firstOrFail();
        $this->actingAs($admin)->patch(route('admin.db-feedback.reviews.moderate', $review), [
            'moderation_status' => 'hidden', 'reason' => 'Pemeriksaan indikasi pelanggaran standar komunitas.',
        ])->assertRedirect();
        $this->get(route('db-feedback.reviews.public'))->assertDontSee($review->comment);
        $this->patch(route('admin.db-feedback.reviews.moderate', $review), [
            'moderation_status' => 'visible', 'reason' => 'Review selesai ditinjau dan telah diperbolehkan.',
        ])->assertRedirect();
        $this->get(route('db-feedback.reviews.public'))->assertSee($review->comment);
        $this->assertSame(2, ServiceReviewModerationLog::count());
    }

    public function test_admin_can_verify_pending_review_only_when_gateway_confirms_completed_order(): void
    {
        [,,$service] = $this->service();
        $citizen = $this->user();
        $admin = $this->user('admin');
        $this->actingAs($citizen)->post(route('db-feedback.reviews.store'), $this->reviewPayload($service));
        $review = ServiceReview::firstOrFail();
        $this->verifiedGateway();
        $this->actingAs($admin)->post(route('admin.db-feedback.reviews.recheck', $review))->assertRedirect();
        $this->assertSame('verified', $review->fresh()->verification_status);
        $this->assertNotNull($review->fresh()->verified_at);
    }

    public function test_unverified_review_delete_checks_owner_and_deletes_photo(): void
    {
        Storage::fake('public');
        [,,$service] = $this->service();
        $citizen = $this->user();
        $other = $this->user();
        $this->actingAs($citizen)->post(route('db-feedback.reviews.store'), [
            ...$this->reviewPayload($service),
            'photo' => UploadedFile::fake()->image('hasil.jpg'),
        ])->assertRedirect();
        $review = ServiceReview::firstOrFail();
        Storage::disk('public')->assertExists($review->photo_path);
        $this->actingAs($other)->delete(route('db-feedback.reviews.destroy-pending', $review))->assertNotFound();
        $this->actingAs($citizen)->delete(route('db-feedback.reviews.destroy-pending', $review))->assertRedirect();
        Storage::disk('public')->assertMissing($review->photo_path);
    }

    public function test_only_reporter_sees_own_complaints_and_cannot_link_another_users_review(): void
    {
        [,,$service] = $this->service();
        $first = $this->user();
        $second = $this->user();
        $this->actingAs($first)->post(route('db-feedback.reviews.store'), $this->reviewPayload($service));
        $review = ServiceReview::firstOrFail();
        $payload = [
            'service_review_id' => $review->id,
            'subject' => 'Permasalahan layanan rumah',
            'description' => 'Saya merasa jadwal dan pelaksanaan pekerjaan berbeda dari informasi awal yang diberikan.',
        ];
        $this->actingAs($second)->post(route('db-feedback.complaints.store'), $payload)->assertNotFound();
        $this->assertSame(0, ServiceComplaint::count());
        $this->actingAs($first)->post(route('db-feedback.complaints.store'), $payload)
            ->assertRedirect(route('db-feedback.complaints.mine'));
        $complaint = ServiceComplaint::firstOrFail();
        $this->assertSame($first->id, $complaint->reporter_id);
        $this->assertSame($review->partner_profile_id, $complaint->partner_profile_id);
        $this->assertSame($review->booking_reference, $complaint->booking_reference);
        $this->actingAs($second)->get(route('db-feedback.complaints.mine'))
            ->assertDontSee('Permasalahan layanan rumah');
        $this->actingAs($first)->get(route('db-feedback.complaints.mine'))
            ->assertSee('Permasalahan layanan rumah');
    }

    public function test_admin_complaint_workflow_writes_audit_and_rejects_invalid_transition(): void
    {
        $citizen = $this->user();
        $admin = $this->user('admin');
        $this->actingAs($citizen)->post(route('db-feedback.complaints.store'), [
            'subject' => 'Kualitas layanan tidak sesuai',
            'description' => 'Saya ingin melaporkan adanya informasi yang tidak sesuai dengan ketentuan saat pemesanan.',
        ])->assertRedirect();
        $complaint = ServiceComplaint::firstOrFail();
        $this->actingAs($admin)->patch(route('admin.db-feedback.complaints.update', $complaint), [
            'status' => 'resolved', 'priority' => 'normal', 'note' => 'Tidak boleh menutup aduan sebelum diproses.',
        ])->assertSessionHasErrors('status');
        $this->patch(route('admin.db-feedback.complaints.update', $complaint), [
            'status' => 'in_review', 'priority' => 'high', 'note' => 'Permasalahan diteruskan ke proses pemeriksaan internal.',
        ])->assertRedirect();
        $this->patch(route('admin.db-feedback.complaints.update', $complaint), [
            'status' => 'resolved', 'priority' => 'high', 'note' => 'Pemeriksaan dinyatakan selesai dengan catatan penanganan.',
        ])->assertRedirect();
        $this->assertSame('resolved', $complaint->fresh()->status);
        $this->assertNotNull($complaint->fresh()->closed_at);
        $this->assertSame(2, ServiceComplaintAction::count());
        $this->actingAs($citizen)->patch(route('admin.db-feedback.complaints.update', $complaint), [
            'status' => 'in_review', 'priority' => 'normal', 'note' => 'Tidak boleh menulis log admin.',
        ])->assertForbidden();
    }

    public function test_unapproved_partner_and_citizen_cannot_moderate_or_reply_as_partner(): void
    {
        [,,$service] = $this->service();
        $citizen = $this->user();
        $pending = $this->user('partner');
        PartnerProfile::create([
            'user_id' => $pending->id, 'business_name' => 'Usaha Pending',
            'business_phone' => '081234568888', 'service_area' => 'Mariso',
            'verification_status' => 'pending',
        ]);
        $this->actingAs($citizen)->post(route('db-feedback.reviews.store'), $this->reviewPayload($service));
        $review = ServiceReview::firstOrFail();
        $this->patch(route('admin.db-feedback.reviews.moderate', $review), [
            'moderation_status' => 'hidden', 'reason' => 'Hanya admin yang berwenang.',
        ])->assertForbidden();
        $this->actingAs($pending)->get(route('partner.db-feedback.reviews.index'))
            ->assertRedirect(route('partner.portal'));
    }
}
