<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateADREPORTTable extends Migration {

	public function up()
	{
		Schema::create('AD_REPORT', function(Blueprint $table) {
			$table->increments('report_id');
			$table->string('report_name', 100)->nullable();
			$table->timestamps();
			$table->timestamp('generated_at')->nullable();
			$table->integer('created_by')->unsigned()->index();
			$table->timestamp('start_date')->nullable();
			$table->timestamp('end_date')->nullable();
			$table->string('message_status')->nullable();
			//0 = queue
			//1 = process
			//2 = finished
			//3 = failed
			$table->integer('generate_status')->default(0);
			$table->integer('pid')->default(0);
			$table->string('file_type', 5)->default('csv');
			$table->string('file_path')->nullable();
			$table->decimal('percentage', 3,2)->default('0');
		});
	}

	public function down()
	{
		Schema::drop('AD_REPORT');
	}
}
