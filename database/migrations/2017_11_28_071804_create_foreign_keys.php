<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateForeignKeys extends Migration
{

    public function up()
    {
        $db = DB::connection('mysql_sms_api')->getDatabaseName();

        Schema::table('AD_USER', function(Blueprint $table) use ($db) {
            $table->foreign('client_id')
                    ->references('CLIENT_ID')
                    ->on(new Expression($db . '.CLIENT'))
                    ->onDelete('CASCADE')
                    ->onUpdate('CASCADE');
        });
        Schema::table('AD_PRIVILEGE_ROLE', function(Blueprint $table) {
            $table->foreign('privilege_id')
                    ->references('privilege_id')
                    ->on('AD_PRIVILEGES')
                    ->onDelete('CASCADE')
                    ->onUpdate('CASCADE');
        });
        Schema::table('AD_PRIVILEGE_ROLE', function(Blueprint $table) {
            $table->foreign('role_id')
                    ->references('role_id')
                    ->on('AD_ROLES')
                    ->onDelete('CASCADE')
                    ->onUpdate('CASCADE');
        });
        Schema::table('AD_ROLE_USER', function(Blueprint $table) {
            $table->foreign('role_id')
                    ->references('role_id')
                    ->on('AD_ROLES')
                    ->onDelete('CASCADE')
                    ->onUpdate('CASCADE');
        });
        Schema::table('AD_ROLE_USER', function(Blueprint $table) {
            $table->foreign('user_id')
                    ->references('ad_user_id')
                    ->on('AD_USER')
                    ->onDelete('CASCADE')
                    ->onUpdate('CASCADE');
        });

        Schema::table('AD_USER_APIUSER', function(Blueprint $table) {
            $table->foreign('ad_user_id')
                    ->references('ad_user_id')
                    ->on('AD_USER')
                    ->onDelete('CASCADE')
                    ->onUpdate('CASCADE');
        });
        Schema::table('AD_USER_APIUSER', function(Blueprint $table) use ($db) {
            $table->foreign('api_user_id')
                    ->references('USER_ID')
                    ->on(new Expression($db . '.USER'))
                    ->onDelete('CASCADE')
                    ->onUpdate('CASCADE');
        });

        Schema::table('AD_API_USER_REPORT', function(Blueprint $table) {
            $table->foreign('report_id')
                    ->references('report_id')
                    ->on('AD_REPORT')
                    ->onDelete('CASCADE')
                    ->onUpdate('CASCADE');
        });
        Schema::table('AD_API_USER_REPORT', function(Blueprint $table) use ($db) {
            $table->foreign('api_user_id')
                    ->references('USER_ID')
                    ->on(new Expression($db . '.USER'))
                    ->onDelete('CASCADE')
                    ->onUpdate('CASCADE');
        });
    }

    public function down()
    {
        Schema::table('AD_USER', function(Blueprint $table) {
            $table->dropForeign('AD_USER_client_id_foreign');
        });
        Schema::table('AD_PRIVILEGE_ROLE', function(Blueprint $table) {
            $table->dropForeign('AD_PRIVILEGE_ROLE_privilege_id_foreign');
        });
        Schema::table('AD_PRIVILEGE_ROLE', function(Blueprint $table) {
            $table->dropForeign('AD_PRIVILEGE_ROLE_role_id_foreign');
        });
        Schema::table('AD_ROLE_USER', function(Blueprint $table) {
            $table->dropForeign('AD_ROLE_USER_role_id_foreign');
        });
        Schema::table('AD_ROLE_USER', function(Blueprint $table) {
            $table->dropForeign('AD_ROLE_USER_user_id_foreign');
        });
        Schema::table('AD_USER_APIUSER', function(Blueprint $table) {
            $table->dropForeign('AD_USER_APIUSER_ad_user_id_foreign');
        });
        Schema::table('AD_USER_APIUSER', function(Blueprint $table) {
            $table->dropForeign('AD_USER_APIUSER_api_user_id_foreign');
        });
        Schema::table('AD_API_USER_REPORT', function(Blueprint $table) {
            $table->dropForeign('AD_API_USER_REPORT_report_id_foreign');
        });
        Schema::table('AD_API_USER_REPORT', function(Blueprint $table) {
            $table->dropForeign('AD_API_USER_REPORT_api_user_id_foreign');
        });
    }

}
