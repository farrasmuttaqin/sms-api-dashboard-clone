<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateADUSERTable extends Migration {

	public function up()
	{
		Schema::create('AD_USER', function(Blueprint $table) {
			$table->increments('ad_user_id');
			$table->string('name', 100);
			$table->string('avatar', 100)->nullable();
			$table->string('email', 100)->unique();
			$table->integer('client_id')->nullable();
			$table->integer('created_by')->nullable();
			$table->string('password', 100);
			// $table->string('salt', 30);
			$table->timestamp('last_login');
			$table->timestamp('expired_token')->nullable();
			$table->string('forget_token', 100)->nullable();
			$table->rememberToken();
			$table->timestamps();
			$table->tinyInteger('active')->default('1');
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::dropIfExists('AD_USER');
	}
}