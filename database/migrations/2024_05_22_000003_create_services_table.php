<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {        Schema::create('services', function (Blueprint $table) {            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->string('name');
            $table->string('type');
            $table->text('description')->nullable();
            $table->string('address');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->integer('capacity')->nullable();
            $table->json('availability')->nullable();
            $table->json('requirements')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();        });        // Verificar si estamos usando PostgreSQL para agregar columna geometry
        if (DB::connection()->getDriverName() === 'pgsql') {
            // Agregar columna de tipo geometry para PostGIS
            DB::statement('ALTER TABLE services ADD COLUMN location geometry(Point, 4326)');
            
            // Crear un índice espacial para mejorar el rendimiento de consultas geoespaciales
            DB::statement('CREATE INDEX services_location_idx ON services USING GIST (location)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};