<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder {

	public function run()
	{
		Model::unguard();

		$this->call('UserTableSeeder');
		$this->command->info('User table seeded!');
		$this->call('RolePrivilegeTableSeeder');
		$this->command->info('Privilege table seeded!');
	}
}