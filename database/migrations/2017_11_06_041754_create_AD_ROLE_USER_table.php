<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateADROLEUSERTable extends Migration {

	public function up()
	{
		Schema::create('AD_ROLE_USER', function(Blueprint $table) {
			$table->integer('role_id')->unsigned();
			$table->integer('user_id')->unsigned();
		});
	}

	public function down()
	{
		Schema::dropIfExists('AD_ROLE_USER');
	}
}