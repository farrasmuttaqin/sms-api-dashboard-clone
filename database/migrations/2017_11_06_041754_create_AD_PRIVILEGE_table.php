<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateADPRIVILEGETable extends Migration {

	public function up()
	{
		Schema::create('AD_PRIVILEGES', function(Blueprint $table) {
			$table->increments('privilege_id');
			$table->string('privilege_name', 100);
		});
	}

	public function down()
	{
		Schema::dropIfExists('AD_PRIVILEGES');
	}
}