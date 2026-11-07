<?php

namespace Tests\Feature;

use App\Models\PartnerProfile;
use App\Models\PartnerService;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MitraDiscoverySyncTest extends TestCase
{
    use RefreshDatabase;

    private function account(string $role, string $name): User
    {
        return User::create([
            'name' => $name, 'email' => strtolower(str_replace(' ', '.', $name)).'@example.test',
            'password' => 'PasswordKuat2026!', 'role' => $role, 'account_status' => 'active',
        ]);
    }

    public function test_admin_find_approved_partner_and_citizen_find_their_published_service(): void
    {
        $admin = $this->account('admin', 'Admin Sinkron');
        $mitra = $this->account('partner', 'Partner Sinkron');
        $warga = $this->account('citizen', 'Warga Sinkron');
        $profile = PartnerProfile::create([
            'user_id' => $mitra->id, 'business_name' => 'Bengkel Tunas Bandung',
            'business_phone' => '081234567899', 'service_area' => 'Regol',
            'city' => 'Bandung', 'slug' => 'bengkel-tunas-bandung-'.$mitra->id,
            'verification_status' => 'pending',
        ]);
        $category = ServiceCategory::create(['name'=>'Perbaikan Rumah', 'slug'=>'perbaikan-rumah',
            'icon'=>'home_repair_service', 'sort_order'=>1, 'is_active'=>true]);

        $this->actingAs($admin)
            ->post(route('admin.partner-applications.decide', $profile), [
                'decision'=>'approved', 'reason'=>'Data kontak usaha dan kepemilikan telah diperiksa.',
            ])->assertRedirect();
        $this->assertDatabaseHas('partner_profiles', [
            'id'=>$profile->id, 'verification_status'=>'approved',
        ]);
        $this->get(route('admin.partners', ['q'=>'Tunas','status'=>'approved']))
            ->assertOk()->assertSee('Bengkel Tunas Bandung');

        $this->actingAs($mitra)->post(route('partner.db-services.store'), [
            'name'=>'Cuci Kulkas Rumah', 'description'=>'Cuci dan servis kulkas untuk rumah tangga.',
            'service_category_id'=>$category->id, 'base_price'=>125000,
            'duration_minutes'=>90, 'is_active'=>1,
        ])->assertRedirect(route('partner.db-services.index'));
        $this->actingAs($warga)->get(route('categories.index', ['db_q'=>'Cuci Kulkas']))
            ->assertOk()->assertSee('Bengkel Tunas Bandung')->assertSee('Cuci Kulkas Rumah')
            ->assertSee(route('partners.public.show', $profile->slug), false)
            ->assertSee(route('bookings.database.create', $profile->slug), false);
        $this->get(route('home'))->assertOk()->assertSee('Bengkel Tunas Bandung');
        $this->get(route('partners.public.show', $profile->slug))
            ->assertOk()->assertSee('Cuci Kulkas Rumah');
    }

    public function test_no_public_service_when_category_or_service_inactive_or_partner_rejected(): void
    {
        $mitra = $this->account('partner', 'Partner Nonaktif');
        $profile = PartnerProfile::create([
            'user_id'=>$mitra->id,'business_name'=>'Teknik Nonaktif Bandung',
            'business_phone'=>'081234567899','service_area'=>'Regol',
            'slug'=>'teknik-nonaktif-'.$mitra->id, 'verification_status'=>'approved',
        ]);
        $cat = ServiceCategory::create(['name'=>'Listrik Bandung','slug'=>'listrik-bandung',
            'sort_order'=>2, 'is_active'=>true]);
        $service = PartnerService::create(['partner_profile_id'=>$profile->id,
            'service_category_id'=>$cat->id, 'name'=>'Perbaikan Stop Kontak',
            'base_price'=>100000,'duration_minutes'=>60,'is_active'=>true]);
        $this->get(route('categories.index'))->assertSee('Perbaikan Stop Kontak');
        $service->update(['is_active'=>false]);
        $this->get(route('categories.index'))->assertDontSee('Perbaikan Stop Kontak');
        $service->update(['is_active'=>true]);
        $cat->update(['is_active'=>false]);
        $this->get(route('categories.index'))->assertDontSee('Perbaikan Stop Kontak');
        $cat->update(['is_active'=>true]);
        $profile->update(['verification_status'=>'rejected']);
        $this->get(route('categories.index'))->assertDontSee('Teknik Nonaktif Bandung');
    }
}
