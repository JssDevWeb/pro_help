<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Verificar si estamos usando PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            // PostGIS está instalado, así que podemos usar esta línea
            DB::statement('CREATE EXTENSION IF NOT EXISTS postgis');
        }

        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('address');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('description')->nullable();
            $table->string('website')->nullable();            $table->timestamps();
            $table->softDeletes();        });        // Verificar si estamos usando PostgreSQL para agregar columna geometry
        if (DB::connection()->getDriverName() === 'pgsql') {
            // Agregar columna de tipo geometry para PostGIS
            DB::statement('ALTER TABLE organizations ADD COLUMN location geometry(Point, 4326)');
            
            // Crear un índice espacial para mejorar el rendimiento de consultas geoespaciales
            DB::statement('CREATE INDEX organizations_location_idx ON organizations USING GIST (location)');
        }
        
        // Añadir la clave foránea para la tabla de usuarios
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('organizations');
    }
};
