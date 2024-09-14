<?php

namespace Database\Seeders;

use App\Models\Building;
use Illuminate\Database\Seeder;

class Buildingseeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = [
            [
                'name' => 'Glav korpus',
                'type' => 'educational',
            ],
            [
                'name' => 'Korpus_2',
                'type' => 'educational',
            ],
            [
                'name' => 'Korpus_3',
                'type' => 'educational',
            ],
            [
                'name' => '1_Obsh',
                'type' => 'residential',
            ],
            [
                'name' => '2_Obsh',
                'type' => 'residential',
            ]
        ];

        foreach ($names as $name) {
            Building::query()->updateOrCreate([
                'name' => $name['name'],
            ],[
                    'type' => $name['type']
                ]
            );
        }
    }
}
