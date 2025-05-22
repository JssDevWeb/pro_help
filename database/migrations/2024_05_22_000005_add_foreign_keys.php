<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('set null');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
        });

        Schema::table('interventions', function (Blueprint $table) {
            $table->foreign('beneficiary_id')->references('id')->on('beneficiaries')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('interventions', function (Blueprint $table) {
            $table->dropForeign(['beneficiary_id']);
            $table->dropForeign(['service_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
        });
    }
};
