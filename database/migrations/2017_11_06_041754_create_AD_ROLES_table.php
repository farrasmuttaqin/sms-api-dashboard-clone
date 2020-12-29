<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateADROLESTable extends Migration {

	public function up()
	{
		Schema::create('AD_ROLES', function(Blueprint $table) {
			$table->increments('role_id');
			$table->string('role_name', 30);
		});
	}

	public function down()
	{
		Schema::dropIfExists('AD_ROLES');
	}
}