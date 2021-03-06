<?php

use App\Track;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        $u = User::query()->firstOrCreate(
            [
                'email' => 'elcopro@gmail.com',
            ],
            [
                'name'  => 'Vladimir',
                'password' => Hash::make('123456'),
            ]
        );
        $u1 = User::query()->firstOrCreate(
            [
                'email' => 'test@test.test',
            ],
            [
                'name'  => 'Tester',
                'password' => Hash::make('123456'),
            ]
        );
        $root = Role::firstOrCreate([
            'name'       => 'root',
            'guard_name' => 'api'
        ]);
        if (!$u->hasRole('root')) $u->assignRole('root');
        $admin = Role::firstOrCreate([
            'name'       => 'admin',
            'guard_name' => 'api'
        ]);
        if (!$u1->hasRole('admin')) $u->assignRole('admin');
        $user = Role::firstOrCreate([
            'name'       => 'user',
            'guard_name' => 'api'
        ]);
        $guest = Role::firstOrCreate([
            'name'       => 'guest',
            'guard_name' => 'api'
        ]);
        $see_all = Permission::firstOrCreate([
            'name'       => 'see_all',
            'guard_name' => 'api'
        ]);
        $root->givePermissionTo($see_all);
        $see_own = Permission::firstOrCreate([
            'name'       => 'see_own',
            'guard_name' => 'api'
        ]);
        $admin->givePermissionTo($see_own);
        for ($i = 1; $i < 33; $i++) {
            Track::firstOrCreate([ 'name' => 'Дорожка '. $i ]);
        }
    }
}
