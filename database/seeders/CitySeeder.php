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
        $cityRegionMap = [
            // Littoral
            'Douala' => 'Littoral',
            'Édéa' => 'Littoral',
            'Nkongsamba' => 'Littoral',
            'Manjo' => 'Littoral',
            'Loum' => 'Littoral',
            'Melong' => 'Littoral',
            'Penja' => 'Littoral',
            'Souza' => 'Littoral',
            'Yabassi' => 'Littoral',
            'Dibombari' => 'Littoral',
            'Manoka' => 'Littoral',
            'Ngambe' => 'Littoral',
            'Pouma' => 'Littoral',

            // Centre
            'Yaoundé' => 'Centre',
            'Mbalmayo' => 'Centre',
            'Bafia' => 'Centre',
            'Akonolinga' => 'Centre',
            'Eseka' => 'Centre',
            'Ngomedzap' => 'Centre',
            'Obala' => 'Centre',
            'Soa' => 'Centre',
            'Mfou' => 'Centre',
            'Nanga-Eboko' => 'Centre',
            'Monatélé' => 'Centre',
            'Ntui' => 'Centre',
            'Mbandjock' => 'Centre',
            'Nkoteng' => 'Centre',
            'Ayos' => 'Centre',
            'Endom' => 'Centre',

            // Ouest
            'Bafoussam' => 'Ouest',
            'Dschang' => 'Ouest',
            'Foumban' => 'Ouest',
            'Mbouda' => 'Ouest',
            'Bafang' => 'Ouest',

            // Sud-Ouest
            'Buea' => 'Sud-Ouest',
            'Limbe' => 'Sud-Ouest',
            'Kumba' => 'Sud-Ouest',
            'Tiko' => 'Sud-Ouest',
            'Mamfe' => 'Sud-Ouest',
            'Mundemba' => 'Sud-Ouest',
            'Fontem' => 'Sud-Ouest',
            'Muyuka' => 'Sud-Ouest',
            'Ekondo-Titi' => 'Sud-Ouest',
            'Idenau' => 'Sud-Ouest',
            'Mutengene' => 'Sud-Ouest',

            // Nord-Ouest
            'Bamenda' => 'Nord-Ouest',
            'Bafut' => 'Nord-Ouest',
            'Bali' => 'Nord-Ouest',
            'Wum' => 'Nord-Ouest',
            'Kumbo' => 'Nord-Ouest',
            'Nkambe' => 'Nord-Ouest',
            'Fundong' => 'Nord-Ouest',
            'Ndop' => 'Nord-Ouest',
            'Mbengwi' => 'Nord-Ouest',

            // Sud
            'Ebolowa' => 'Sud',
            'Kribi' => 'Sud',
            'Sangmélima' => 'Sud',
            'Ngoulemakong' => 'Sud',
            'Ambam' => 'Sud',
            'Djoum' => 'Sud',
            'Mintom' => 'Sud',
            'Oveng' => 'Sud',
            'Akom II' => 'Sud',
            'Campo' => 'Sud',
            'Lolodorf' => 'Sud',
            'Bipindi' => 'Sud',
            'Meyomessala' => 'Sud',

            // Est
            'Bertoua' => 'Est',
            'Batouri' => 'Est',
            'Yokadouma' => 'Est',
            'Abong-Mbang' => 'Est',
            'Garoua-Boulaï' => 'Est',
            'Bélabo' => 'Est',
            'Dimako' => 'Est',
            'Doumé' => 'Est',
            'Lomié' => 'Est',
            'Messamena' => 'Est',
            'Nguelemendouka' => 'Est',
            'Kette' => 'Est',
            'Kentzou' => 'Est',

            // Adamaoua
            'Ngaoundéré' => 'Adamaoua',
            'Meiganga' => 'Adamaoua',
            'Tignère' => 'Adamaoua',
            'Banyo' => 'Adamaoua',
            'Tibati' => 'Adamaoua',
            'Ngaoundal' => 'Adamaoua',
            'Bankim' => 'Adamaoua',
            'Kontcha' => 'Adamaoua',

            // Nord
            'Garoua' => 'Nord',
            'Guider' => 'Nord',
            'Poli' => 'Nord',
            'Tcholliré' => 'Nord',
            'Rey-Bouba' => 'Nord',
            'Figuil' => 'Nord',

            // Extrême-Nord
            'Maroua' => 'Extrême-Nord',
            'Yagoua' => 'Extrême-Nord',
            'Mokolo' => 'Extrême-Nord',
            'Kousséri' => 'Extrême-Nord',
        ];

        $order = 1;
        foreach ($cityRegionMap as $cityName => $region) {
            City::updateOrCreate(
                ['name' => $cityName],
                [
                    'slug' => Str::slug($cityName),
                    'region' => $region,
                    'is_active' => true,
                    'sort_order' => $order++,
                ]
            );
        }
    }
}
