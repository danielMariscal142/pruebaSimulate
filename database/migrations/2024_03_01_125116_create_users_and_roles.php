<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Role;
use App\Models\User;
use App\Models\Direction;

class CreateUsersAndRoles extends Migration
{
    public function up()
    {
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'superadmin']);
        Role::create(['name' => 'user']);

        $adminRole = Role::where('name', 'admin')->first();
        $superadminRole = Role::where('name', 'superadmin')->first();
        $userRole = Role::where('name', 'user')->first();

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'phone' => '+51 70791322',
            'is_synchronized' => true,
            'is_approved' => true
        ]);
        $direction = Direction::create([
            'status' => true,
            'user_id' => $admin->id,
            'label' => 'Calle Siempreviva'
        ]);

        $admin->roles()->attach($adminRole);

        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password'),
            'phone' => '+51 70791322',
            'is_synchronized' => true,
            'is_approved' => true
        ]);
        $direction2 = Direction::create([
            'status' => true,
            'user_id' => $superadmin->id,
            'label' => 'Calle Siempreviva2'
        ]);
        $superadmin->roles()->attach($superadminRole);

        $user = User::create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'phone' => '+51 70791322',
            'is_synchronized' => true,
            'is_approved' => true
        ]);
        $direction3 = Direction::create([
            'status' => true,
            'user_id' => $user->id,
            'label' => 'Calle Siempreviva3'
        ]);
        $user->roles()->attach($userRole);
    }

    public function down()
    {
    }
}

