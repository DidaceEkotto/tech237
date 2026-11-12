<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            'Douala',
            'Yaoundé',
            'Garoua',
            'Bamenda',
            'Bafoussam',
            'Maroua',
            'Ngaoundéré',
            'Bertoua',
            'Ebolowa',
            'Buea',
            'Limbe',
            'Kribi',
            'Édéa',
            'Kumba',
            'Nkongsamba',
            'Dschang',
            'Foumban',
            'Mbouda',
            'Sangmélima',
            'Mbalmayo',
            'Bafang',
            'Yagoua',
            'Mokolo',
            'Kousséri',
            'Guider',
            'Meiganga',
            'Batouri',
            'Yokadouma',
            'Tiko',
            'Akonolinga',
            'Abong-Mbang',
            'Bafia',
            'Bafut',
            'Bali',
            'Wum',
            'Kumbo',
            'Nkambe',
            'Fundong',
            'Ndop',
            'Mbengwi',
            'Mamfe',
            'Mundemba',
            'Fontem',
            'Muyuka',
            'Ekondo-Titi',
            'Idenau',
            'Mutengene',
            'Manjo',
            'Loum',
            'Melong',
            'Penja',
            'Souza',
            'Yabassi',
            'Dibombari',
            'Manoka',
            'Ngambe',
            'Pouma',
            'Eseka',
            'Ngomedzap',
            'Obala',
            'Soa',
            'Mfou',
            'Nanga-Eboko',
            'Monatélé',
            'Ntui',
            'Mbandjock',
            'Nkoteng',
            'Ayos',
            'Endom',
            'Ngoulemakong',
            'Ambam',
            'Djoum',
            'Mintom',
            'Oveng',
            'Akom II',
            'Campo',
            'Lolodorf',
            'Bipindi',
            'Meyomessala',
            'Garoua-Boulaï',
            'Bélabo',
            'Dimako',
            'Doumé',
            'Lomié',
            'Messamena',
            'Nguelemendouka',
            'Kette',
            'Kentzou',
            'Tignère',
            'Banyo',
            'Tibati',
            'Ngaoundal',
            'Bankim',
            'Kontcha',
            'Poli',
            'Tcholliré',
            'Rey-Bouba',
            'Figuil',
        ];

        // Deduplicate and trim
        $uniqueCities = array_values(array_unique(array_map('trim', $cities)));

        foreach ($uniqueCities as $index => $cityName) {
            City::updateOrCreate(
                ['name' => $cityName],
                [
                    'slug' => Str::slug($cityName),
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}
