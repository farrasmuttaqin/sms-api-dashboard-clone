<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateADPRIVILEGEROLETable extends Migration {

	public function up()
	{
		Schema::create('AD_PRIVILEGE_ROLE', function(Blueprint $table) {
			$table->integer('privilege_id')->unsigned();
			$table->integer('role_id')->unsigned();
		});
	}

	public function down()
	{
		Schema::dropIfExists('AD_PRIVILEGE_ROLE');
	}
}