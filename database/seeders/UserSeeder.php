<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
	    //
	    User::create(['name' => 'Gian', 'email' => 'gian@evence.com.br', 'password' => bcrypt('c1b3rn3t')]);
    }
}
