<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = [
            'name' => 'admin',
            'password' =>'password',
        ];
        User::query()->create([
            'name' => $user['name'],
            'password' => Hash::make($user['password']),
        ]);
    }
}
