<?php

use App\Models\Admin;
use Illuminate\Database\Seeder;

class MainAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = Admin::firstOrCreate([
            'email' => 'admin@admin.com',


        ], [
            'password' => bcrypt('1234admin'),
            'name' => 'ahmed'
        ]);
        $admin->assignRole(['super-admin']);
    }
}
