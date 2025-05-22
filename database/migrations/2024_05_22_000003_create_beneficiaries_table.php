<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('beneficiaries', function (Blueprint $table) {            $table->id();
            $table->string('identification')->unique();
            $table->string('name');
            $table->date('birth_date');
            $table->string('gender');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->json('needs')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Agregar columna de tipo geometry para PostGIS
        DB::statement('ALTER TABLE beneficiaries ADD COLUMN last_known_location geometry(Point, 4326)');
    }

    public function down()
    {
        Schema::dropIfExists('beneficiaries');
    }
};
