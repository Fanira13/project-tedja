<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\models\Role;

class RoleAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::create([
            'name' => 'admin'
        ]);

        $lenderRole = Role::create([
            'name' => 'lender'
        ]);

        $agentRole = Role::create([
            'name' => 'agent'
        ]);

        $customerRole = Role::create([
            'name' => 'customer'
        ]);

        $user = User::create([
            'name' => 'Team Tedja',
            'email' => 'team@tedja.com',
            'phone' => '0895631989276',
            'photo' => 'profil.com',
            'password' => bcrypt('123Aaa123!')
        ]);

        $user->assignRole($adminRole);

    }
}
