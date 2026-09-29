<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
          Admin::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            ['name' => 'Super Admin', 'password' => Hash::make('12345678')]
            );
    }
}
