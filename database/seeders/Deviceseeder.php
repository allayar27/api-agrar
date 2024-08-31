<?php

namespace Database\Seeders;

use App\Models\Device;
use Illuminate\Database\Seeder;

class Deviceseeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $devices = [
            [
                'name' => 'agrar_11',
                'building_id' => 1,
                'type' => 'in',
                'description' => "korpus 1 in"
            ],
            [
                'name' => 'agrar_13',
                'building_id' => 1,
                'type' => 'in',
                'description' => "korpus 1 in"
            ],
            [
                'name' => 'agrar_15',
                'building_id' => 1,
                'type' => 'in',
                'description' => "korpus 1 in"
            ],
            [
                'name' => 'agrar_17',
                'building_id' => 1,
                'type' => 'in',
                'description' => "korpus 1 in"
            ],
            [
                'name' => 'agrar_12',
                'building_id' => 1,
                'type' => 'out',
                'description' => "korpus 1 out"
            ],
            [
                'name' => 'agrar_14',
                'building_id' => 1,
                'type' => 'out',
                'description' => "korpus 1 out"
            ],
            [
                'name' => 'agrar_16',
                'building_id' => 1,
                'type' => 'out',
                'description' => "korpus 1 out"
            ],
            [
                'name' => 'agrar_18',
                'building_id' => 1,
                'type' => 'out',
                'description' => "korpus 1 out"
            ],
            [
                'name' => 'agrar_21',
                'building_id' => 2,
                'type' => 'in',
                'description' => "korpus 2 in"
            ],
            [
                'name' => 'agrar_23',
                'building_id' => 2,
                'type' => 'in',
                'description' => "korpus 2 in"
            ],
            [
                'name' => 'agrar_25',
                'building_id' => 2,
                'type' => 'in',
                'description' => "korpus 2 in"
            ],
            [
                'name' => 'agrar_22',
                'building_id' => 2,
                'type' => 'out',
                'description' => "korpus 2 out"
            ],
            [
                'name' => 'agrar_24',
                'building_id' => 2,
                'type' => 'out',
                'description' => "korpus 2 out"
            ],
            [
                'name' => 'agrar_26',
                'building_id' => 2,
                'type' => 'out',
                'description' => "korpus 2 out"
            ],
            [
                'name' => 'agrar_31',
                'building_id' => 3,
                'type' => 'in',
                'description' => "korpus 3 in"
            ],
            [
                'name' => 'agrar_33',
                'building_id' => 3,
                'type' => 'in',
                'description' => "korpus 3 in"
            ],
            [
                'name' => 'agrar_35',
                'building_id' => 3,
                'type' => 'in',
                'description' => "korpus 3 in"
            ],
            [
                'name' => 'agrar_32',
                'building_id' => 3,
                'type' => 'out',
                'description' => "korpus 3 out"
            ],
            [
                'name' => 'agrar_34',
                'building_id' => 3,
                'type' => 'out',
                'description' => "korpus 3 out"
            ],
            [
                'name' => 'agrar_36',
                'building_id' => 3,
                'type' => 'out',
                'description' => "korpus 3 out"
            ],
            [
                'name' => 'agrar_41',
                'building_id' => 4,
                'type' => 'in',
                'description' => "Obsh 1 in"
            ],
            [
                'name' => 'agrar_42',
                'building_id' => 4,
                'type' => 'out',
                'description' => "Obsh 1 out"
            ],
            [
                'name' => 'agrar_51',
                'building_id' => 5,
                'type' => 'in',
                'description' => "Obsh 2 in"
            ],
            [
                'name' => 'agrar_52',
                'building_id' => 5,
                'type' => 'out',
                'description' => "Obsh 2 out"
            ],
        ];

        foreach ($devices as $device) {
            Device::query()->updateOrCreate([
                'name' => $device['name'],
            ], [
                    'building_id' => $device['building_id'],
                    'type' => $device['type'],
                    'description' => $device['description'],
                ]
            );
        }
    }
}
