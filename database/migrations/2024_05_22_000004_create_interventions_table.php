<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('interventions', function (Blueprint $table) {            $table->id();
            $table->unsignedBigInteger('beneficiary_id');
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('user_id')->nullable(); // trabajador social
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('type'); // tipo de intervención
            $table->text('notes')->nullable();
            $table->string('status'); // active, pending, completed, cancelled
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('interventions');
    }
};
