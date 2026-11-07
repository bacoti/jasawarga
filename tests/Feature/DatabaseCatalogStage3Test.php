<?php

namespace Tests\Feature;

use App\Models\PartnerProfile;
use App\Models\PartnerService;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\ServiceCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseCatalogStage3Test extends TestCase
{
    use RefreshDatabase;

    private function account(string $role, string $name): User
    {
        return User::create([
            'name' => $name, 'email' => strtolower(str_replace(' ', '.', $name)).'@example.test',
            'role' => $role, 'account_status' => 'active', 'password' => 'SecurePass2026!',
        ]);
    }

    private function partner(string $name = 'Mitra Satu', string $status = 'approved'): array
    {
        $user = $this->account('partner', $name);
        $profile = PartnerProfile::create([
            'user_id' => $user->id, 'business_name' => $name.' Teknik',
            'business_phone' => '081234567890', 'service_area' => 'Panakkukang',
            'verification_status' => $status,
        ]);
        return [$user, $profile];
    }

    private function category(): ServiceCategory
    {
        return ServiceCategory::create([
            'name' => 'Servis AC', 'slug' => 'servis-ac', 'icon' => 'ac_unit',
            'sort_order' => 1, 'is_active' => true,
        ]);
    }

    private function payload(int $categoryId): array
    {
        return [
            'name' => 'Cuci AC Split', 'service_category_id' => $categoryId,
            'description' => 'Pembersihan AC split rumah.', 'base_price' => 75000,
            'duration_minutes' => 60, 'is_active' => 1,
        ];
    }

    public function test_guest_can_view_empty_public_catalog_but_not_management_pages(): void
    {
        $this->get(route('db-catalog.index'))->assertOk();
        $this->get(route('admin.db-categories.index'))->assertRedirect('/masuk');
        $this->get(route('partner.db-services.index'))->assertRedirect('/masuk');
    }

    public function test_admin_can_create_update_and_disable_categories_but_citizen_cannot(): void
    {
        $citizen = $this->account('citizen', 'Warga Penguji');
        $admin = $this->account('admin', 'Admin Penguji');
        $this->actingAs($citizen)->get(route('admin.db-categories.index'))->assertForbidden();
        $this->actingAs($admin)->post(route('admin.db-categories.store'), [
            'name' => 'Servis AC', 'slug' => 'servis-ac', 'icon' => 'ac_unit', 'sort_order' => 1,
        ])->assertRedirect(route('admin.db-categories.index'));
        $category = ServiceCategory::firstOrFail();
        $this->assertTrue($category->is_active);
        $this->post(route('admin.db-categories.store'), [
            'name' => 'Servis AC Lain', 'slug' => 'servis-ac', 'icon' => 'ac_unit', 'sort_order' => 2,
        ])->assertSessionHasErrors('slug');
        $this->patch(route('admin.db-categories.update', $category), [
            'name' => 'AC dan Pendingin', 'slug' => 'pendingin', 'icon' => 'ac_unit', 'sort_order' => 4,
        ])->assertRedirect();
        $this->patch(route('admin.db-categories.status', $category), ['is_active' => 0])->assertRedirect();
        $this->assertDatabaseHas('service_categories', [
            'id' => $category->id, 'slug' => 'pendingin', 'is_active' => false,
        ]);
    }

    public function test_only_approved_partner_can_manage_own_services_and_public_only_sees_published(): void
    {
        $category = $this->category();
        [$pending] = $this->partner('Mitra Pending', 'pending');
        [$approved, $profile] = $this->partner('Mitra Approved');
        [$stranger] = $this->partner('Mitra Stranger');

        $this->actingAs($pending)->get(route('partner.db-services.index'))
            ->assertRedirect(route('partner.portal'));
        $this->actingAs($approved)->post(route('partner.db-services.store'), [
            ...$this->payload($category->id), 'partner_profile_id' => 999999,
        ])->assertRedirect(route('partner.db-services.index'));

        $service = PartnerService::firstOrFail();
        $this->assertSame($profile->id, $service->partner_profile_id);
        $this->get(route('db-catalog.index'))->assertSee('Cuci AC Split');
        $this->get(route('db-catalog.show', $service))->assertOk()->assertSee($profile->business_name);
        $this->get(route('db-catalog.index', ['q' => 'CUCI']))->assertSee('Cuci AC Split');

        // Mitra lain tidak dapat melihat atau mengubah milik Mitra ini melalui route mutasi.
        $this->actingAs($stranger)->patch(route('partner.db-services.update', $service), $this->payload($category->id))
            ->assertNotFound();
        $this->patch(route('partner.db-services.status', $service), ['is_active' => 0])->assertNotFound();
        $this->delete(route('partner.db-services.destroy', $service))->assertNotFound();

        $this->actingAs($approved)->patch(route('partner.db-services.status', $service), ['is_active' => 0])->assertRedirect();
        $this->get(route('db-catalog.index'))->assertDontSee('Cuci AC Split');
        $this->get(route('db-catalog.show', $service))->assertNotFound();
        $this->patch(route('partner.db-services.status', $service), ['is_active' => 1])->assertRedirect();
        $this->get(route('db-catalog.index'))->assertSee('Cuci AC Split');

        $profile->update(['verification_status' => 'rejected']);
        $this->get(route('db-catalog.index'))->assertDontSee('Cuci AC Split');
        $this->get(route('db-catalog.show', $service))->assertNotFound();
    }

    public function test_category_deactivation_blocks_new_services_and_hides_published_services(): void
    {
        $category = $this->category();
        $admin = $this->account('admin', 'Admin Kedua');
        [$user] = $this->partner();
        $this->actingAs($user)->post(route('partner.db-services.store'), $this->payload($category->id))
            ->assertRedirect();
        $service = PartnerService::firstOrFail();

        $this->actingAs($admin)->patch(route('admin.db-categories.status', $category), ['is_active' => 0])
            ->assertRedirect();
        $this->get(route('db-catalog.index'))->assertDontSee($service->name);
        $this->get(route('db-catalog.show', $service))->assertNotFound();

        $this->actingAs($user)->post(route('partner.db-services.store'), $this->payload($category->id))
            ->assertSessionHasErrors('service_category_id');
        $this->patch(route('partner.db-services.status', $service), ['is_active' => 1])
            ->assertSessionHasErrors('service_category_id');
        $this->actingAs($admin)->delete(route('admin.db-categories.destroy', $category))
            ->assertSessionHasErrors('delete');
        $this->assertDatabaseHas('service_categories', ['id' => $category->id]);
    }

    public function test_archived_service_is_not_public_and_keeps_category_referential_history(): void
    {
        $category = $this->category();
        [$user] = $this->partner();
        $admin = $this->account('admin', 'Admin Ketiga');
        $this->actingAs($user)->post(route('partner.db-services.store'), $this->payload($category->id))
            ->assertRedirect();
        $service = PartnerService::firstOrFail();
        $this->delete(route('partner.db-services.destroy', $service))->assertRedirect();
        $this->assertSoftDeleted('partner_services', ['id' => $service->id]);
        $this->get(route('db-catalog.show', $service->id))->assertNotFound();
        $this->actingAs($admin)->delete(route('admin.db-categories.destroy', $category))
            ->assertSessionHasErrors('delete');
    }

    public function test_category_seeder_only_seeds_taxonomy_and_is_idempotent(): void
    {
        $this->seed(ServiceCategorySeeder::class);
        $firstCount = ServiceCategory::count();
        $this->assertSame(count(config('jasawarga.categories')), $firstCount);
        $this->seed(ServiceCategorySeeder::class);
        $this->assertSame($firstCount, ServiceCategory::count());
        $this->assertSame(0, PartnerService::count());
        $this->assertSame(0, PartnerProfile::count());
    }

    public function test_citizen_and_unapproved_partner_cannot_write_services_and_invalid_price_is_rejected(): void
    {
        $category = $this->category();
        $citizen = $this->account('citizen', 'Warga Lain');
        $this->actingAs($citizen)->post(route('partner.db-services.store'), $this->payload($category->id))
            ->assertForbidden();
        [$user] = $this->partner();
        $this->actingAs($user)->post(route('partner.db-services.store'), [
            ...$this->payload($category->id), 'base_price' => -500,
        ])->assertSessionHasErrors('base_price');
        $this->assertSame(0, PartnerService::count());
    }
}
