<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    DB::table('users')->insert([
      ['username' => 'ADMIN', 'password' => Hash::make('Admin@123'), 'role' => '1'],
      ['username' => 'test', 'password' => Hash::make('Admin@123'), 'role' => '2'],
      ['username' => 'hieuth', 'password' => Hash::make('Admin@123'), 'role' => '2'],
      ['username' => 'sonNN', 'password' => Hash::make('Admin@123'), 'role' => '2'],
    ]);
  }
}
