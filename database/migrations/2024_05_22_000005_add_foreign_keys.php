<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Doctrine\DBAL\Exception;

return new class extends Migration
{
    public function up()
    {
        try {
            // Añadimos solo las claves foráneas que no se han definido en otras migraciones
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('set null');
            });
        } catch (Exception $e) {
            // La clave foránea ya existe, ignoramos el error
        }

        try {
            Schema::table('interventions', function (Blueprint $table) {
                $table->foreign('beneficiary_id')->references('id')->on('beneficiaries')->onDelete('cascade');
                $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (Exception $e) {
            // Las claves foráneas ya existen, ignoramos el error
        }
    }    public function down()
    {
        try {
            Schema::table('interventions', function (Blueprint $table) {
                $table->dropForeign(['beneficiary_id']);
                $table->dropForeign(['service_id']);
                $table->dropForeign(['user_id']);
            });
        } catch (Exception $e) {
            // Las claves foráneas no existen, ignoramos el error
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['organization_id']);
            });
        } catch (Exception $e) {
            // La clave foránea no existe, ignoramos el error
        }
    }
};
