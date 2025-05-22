<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('organization_id');
            $table->string('type'); // food, shelter, medical, etc.
            $table->text('description');
            $table->string('address');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->json('schedule')->nullable(); // Horario del servicio
            $table->integer('capacity')->nullable(); // Capacidad máxima
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Agregar columna de tipo geometry para PostGIS
        DB::statement('ALTER TABLE services ADD COLUMN location geometry(Point, 4326)');
    }

    public function down()
    {
        Schema::dropIfExists('services');
    }
};
