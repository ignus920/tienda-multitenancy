<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Limpia filas duplicadas (profileId, permissionId) en usr_permissions_profiles
 * y agrega el índice único que faltaba, para que el editor de permisos pueda
 * hacer updateOrCreate de forma segura.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('usr_permissions_profiles')) {
            return;
        }

        $connection = Schema::getConnection()->getName();

        // 1. Borrar duplicados dejando la fila de id más alto por (profileId, permissionId).
        DB::connection($connection)->statement("
            DELETE p FROM usr_permissions_profiles p
            INNER JOIN (
                SELECT profileId, permissionId, MAX(id) AS keep_id
                FROM usr_permissions_profiles
                WHERE profileId IS NOT NULL AND permissionId IS NOT NULL
                GROUP BY profileId, permissionId
                HAVING COUNT(*) > 1
            ) d
              ON p.profileId = d.profileId
             AND p.permissionId = d.permissionId
             AND p.id <> d.keep_id
        ");

        // 2. Agregar índice único (si no existe ya).
        $exists = collect(DB::connection($connection)->select("SHOW INDEX FROM usr_permissions_profiles"))
            ->contains(fn ($row) => ($row->Key_name ?? null) === 'usr_permissions_profiles_profile_permission_unique');

        if (! $exists) {
            Schema::table('usr_permissions_profiles', function (Blueprint $table) {
                $table->unique(['profileId', 'permissionId'], 'usr_permissions_profiles_profile_permission_unique');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('usr_permissions_profiles')) {
            return;
        }

        Schema::table('usr_permissions_profiles', function (Blueprint $table) {
            $table->dropUnique('usr_permissions_profiles_profile_permission_unique');
        });
    }
};
