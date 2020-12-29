<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateADUSERAPIUSERTable extends Migration {

	public function up()
	{
		Schema::create('AD_USER_APIUSER', function(Blueprint $table) {
			$table->integer('ad_user_id')->unsigned();
			$table->integer('api_user_id');
		});
	}

	public function down()
	{
		Schema::dropIfExists('AD_USER_APIUSER');
	}
}