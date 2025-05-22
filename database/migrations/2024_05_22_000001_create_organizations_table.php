<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Asegurarse de que PostGIS está instalado
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis');

        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('address');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('description')->nullable();
            $table->string('website')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Agregar columna de tipo geometry para PostGIS
        DB::statement('ALTER TABLE organizations ADD COLUMN location geometry(Point, 4326)');
    }

    public function down()
    {
        Schema::dropIfExists('organizations');
    }
};
