<?php

namespace Database\Seeders;

use App\Models\Building;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Buildingseeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = [
            'Glav korpus',
            'Korpus_2',
            'Korpus_3',
            '1_Obsh',
            '2_Obsh'
        ];

        foreach ($names as $name) {
            Building::query()->updateOrCreate([
                'name' => $name
            ]);
        }
    }
}
