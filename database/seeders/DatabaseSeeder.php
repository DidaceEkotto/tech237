<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Only SuperAdminSeeder and SiteSettingSeeder as explicitly required.
     */
    public function run(): void
    {
        $this->call([
            SuperAdminSeeder::class,
            SiteSettingSeeder::class,
            CitySeeder::class,
        ]);
    }
}
