<?php

namespace Tests\Feature;

use App\Livewire\Admin\Cities\Index as AdminCitiesIndex;
use App\Models\Admin;
use App\Models\City;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminCityManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_cities()
    {
        $response = $this->get('/admin/villes');
        $response->assertRedirect('/admin/login');
    }

    public function test_authenticated_admin_can_view_cities_page()
    {
        $admin = Admin::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'admin')->get('/admin/villes');
        $response->assertStatus(200);
        $response->assertSee('Gestion des Villes (Cameroun)');
    }

    public function test_admin_can_create_a_city()
    {
        $admin = Admin::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin, 'admin')
            ->test(AdminCitiesIndex::class)
            ->set('name', 'Bafia')
            ->set('region', 'Centre')
            ->set('sort_order', 5)
            ->set('is_active', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('cities', [
            'name' => 'Bafia',
            'slug' => 'bafia',
            'region' => 'Centre',
            'sort_order' => 5,
            'is_active' => true,
        ]);
    }

    public function test_admin_cannot_create_duplicate_city_with_exact_name()
    {
        $admin = Admin::factory()->create(['role' => 'admin']);
        City::create([
            'name' => 'Douala',
            'slug' => 'douala',
            'region' => 'Littoral',
            'is_active' => true,
        ]);

        Livewire::actingAs($admin, 'admin')
            ->test(AdminCitiesIndex::class)
            ->set('name', 'Douala')
            ->call('save')
            ->assertHasErrors(['name']);

        $this->assertEquals(1, City::where('slug', 'douala')->count());
    }

    public function test_admin_cannot_create_duplicate_city_case_insensitive()
    {
        $admin = Admin::factory()->create(['role' => 'admin']);
        City::create([
            'name' => 'Yaoundé',
            'slug' => 'yaounde',
            'region' => 'Centre',
            'is_active' => true,
        ]);

        // Attempting with lowercase 'yaoundé' or with trailing spaces
        Livewire::actingAs($admin, 'admin')
            ->test(AdminCitiesIndex::class)
            ->set('name', ' yaoundé ')
            ->call('save')
            ->assertHasErrors(['name']);

        $this->assertEquals(1, City::where('slug', 'yaounde')->count());
    }

    public function test_admin_can_edit_an_existing_city()
    {
        $admin = Admin::factory()->create(['role' => 'admin']);
        $city = City::create([
            'name' => 'Kribi',
            'slug' => 'kribi',
            'region' => 'Sud',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Livewire::actingAs($admin, 'admin')
            ->test(AdminCitiesIndex::class)
            ->call('edit', $city->id)
            ->assertSet('cityId', $city->id)
            ->assertSet('name', 'Kribi')
            ->assertSet('isEditing', true)
            ->set('region', 'Sud (Océan)')
            ->set('sort_order', 10)
            ->call('save')
            ->assertHasNoErrors();

        $city->refresh();
        $this->assertEquals('Sud (Océan)', $city->region);
        $this->assertEquals(10, $city->sort_order);
    }

    public function test_admin_cannot_rename_city_to_existing_another_city_name()
    {
        $admin = Admin::factory()->create(['role' => 'admin']);
        $city1 = City::create(['name' => 'Buea', 'slug' => 'buea', 'region' => 'Sud-Ouest']);
        $city2 = City::create(['name' => 'Limbe', 'slug' => 'limbe', 'region' => 'Sud-Ouest']);

        Livewire::actingAs($admin, 'admin')
            ->test(AdminCitiesIndex::class)
            ->call('edit', $city2->id)
            ->set('name', 'Buea')
            ->call('save')
            ->assertHasErrors(['name']);

        $city2->refresh();
        $this->assertEquals('Limbe', $city2->name);
    }

    public function test_admin_can_toggle_city_active_status()
    {
        $admin = Admin::factory()->create(['role' => 'admin']);
        $city = City::create([
            'name' => 'Garoua',
            'slug' => 'garoua',
            'is_active' => true,
        ]);

        Livewire::actingAs($admin, 'admin')
            ->test(AdminCitiesIndex::class)
            ->call('toggleStatus', $city->id);

        $city->refresh();
        $this->assertFalse($city->is_active);

        Livewire::actingAs($admin, 'admin')
            ->test(AdminCitiesIndex::class)
            ->call('toggleStatus', $city->id);

        $city->refresh();
        $this->assertTrue($city->is_active);
    }

    public function test_admin_can_delete_a_city()
    {
        $admin = Admin::factory()->create(['role' => 'admin']);
        $city = City::create([
            'name' => 'VilleTemporaire',
            'slug' => 'ville-temporaire',
            'is_active' => true,
        ]);

        Livewire::actingAs($admin, 'admin')
            ->test(AdminCitiesIndex::class)
            ->call('delete', $city->id);

        $this->assertDatabaseMissing('cities', [
            'id' => $city->id,
        ]);
    }
}
