<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\PartnerProfile;
use App\Models\PartnerWorkingHour;
use App\Models\User;
use App\Models\UserAddress;
use App\Services\Booking\BookingWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BookingStageFourTest extends TestCase
{
    use RefreshDatabase;

    protected User $warga;
    protected User $mitra;
    protected PartnerProfile $partner;
    protected UserAddress $address;
    protected int $serviceId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->warga = User::create(['name' => 'Penguji '.uniqid(), 'email' => uniqid('user', true).'@example.test', 'role' => 'citizen', 'password' => 'password', 'account_status' => 'active']);
        $this->warga->forceFill(['role' => 'citizen'])->save();
        $this->mitra = User::create(['name' => 'Penguji '.uniqid(), 'email' => uniqid('user', true).'@example.test', 'role' => 'citizen', 'password' => 'password', 'account_status' => 'active']);
        $this->mitra->forceFill(['role' => 'partner'])->save();
        $this->partner = PartnerProfile::create([
            'user_id' => $this->mitra->id,
            'business_name' => 'Mitra Uji', 'business_phone' => '081200000000', 'service_area' => 'Rappocini',
            'slug' => 'mitra-uji-'.uniqid(),
            'city' => 'Makassar',
        ]);
        $this->partner->forceFill(['verification_status' => 'approved', 'reverification_required' => false])->save();
        $this->address = UserAddress::create([
            'user_id' => $this->warga->id, 'label' => 'Rumah', 'recipient_name' => 'Warga Uji',
            'phone' => '081234567890', 'province' => 'Sulawesi Selatan', 'city' => 'Makassar',
            'district' => 'Rappocini', 'subdistrict' => 'Gunung Sari', 'address_detail' => 'Jalan Uji 1',
        ]);
        $categoryId = (int) DB::table('service_categories')->insertGetId([
            'name' => 'Servis AC', 'slug' => 'servis-ac-'.uniqid(), 'icon' => 'build',
            'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->serviceId = (int) DB::table('partner_services')->insertGetId([
            'partner_profile_id' => $this->partner->id, 'service_category_id' => $categoryId,
            'name' => 'Servis AC', 'base_price' => 150000, 'duration_minutes' => 60,
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $target = now()->addDays(3)->startOfDay();
        PartnerWorkingHour::create([
            'partner_profile_id' => $this->partner->id, 'day_of_week' => $target->dayOfWeek,
            'start_time' => '00:00:00', 'end_time' => '23:59:00', 'is_active' => true,
        ]);
    }

    public function test_warga_can_create_a_booking_with_deadline_and_status_history(): void
    {
        $target = now()->addDays(3)->startOfDay()->addHours(10);
        $booking = app(BookingWorkflowService::class)->create($this->warga, $this->partner, [
            'service_id' => $this->serviceId, 'scheduled_at' => $target->toDateTimeString(),
            'address_id' => $this->address->id, 'notes' => 'Mohon konfirmasi.',
        ]);

        $this->assertSame(Booking::PENDING_PARTNER, $booking->status);
        $this->assertNotNull($booking->partner_response_deadline);
        $this->assertDatabaseHas('booking_status_histories', ['booking_id' => $booking->id, 'to_status' => Booking::PENDING_PARTNER]);
    }

    public function test_overlapping_booking_for_same_partner_is_rejected(): void
    {
        $target = now()->addDays(3)->startOfDay()->addHours(10);
        $payload = ['service_id' => $this->serviceId, 'scheduled_at' => $target->toDateTimeString(), 'address_id' => $this->address->id];
        $workflow = app(BookingWorkflowService::class);
        $workflow->create($this->warga, $this->partner, $payload);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $workflow->create($this->warga, $this->partner, $payload);
    }

    public function test_booking_less_than_36_hours_ahead_is_rejected(): void
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(BookingWorkflowService::class)->create($this->warga, $this->partner, [
            'service_id' => $this->serviceId, 'scheduled_at' => now()->addHours(20)->toDateTimeString(),
            'address_id' => $this->address->id,
        ]);
    }
}
