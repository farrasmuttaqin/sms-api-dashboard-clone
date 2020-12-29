<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateADAPIUSERREPORTTable extends Migration {

	public function up()
	{
		Schema::create('AD_API_USER_REPORT', function(Blueprint $table) {
			$table->integer('report_id')->unsigned();
			$table->integer('api_user_id');
		});
	}

	public function down()
	{
		Schema::drop('AD_API_USER_REPORT');
	}
}