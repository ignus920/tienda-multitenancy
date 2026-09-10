<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Excepciones de permisos por usuario.
 *
 * Se aplican ENCIMA de los permisos del perfil del usuario:
 *   - NULL  -> hereda el valor del perfil
 *   - 1     -> permitir (aunque el perfil no lo tenga)
 *   - 0     -> denegar  (aunque el perfil lo tenga)
 *
 * Misma convención de columnas que usr_permissions_profiles
 * (show / creater / editer / deleter).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('usr_permissions_users')) {
            return;
        }

        Schema::create('usr_permissions_users', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('userId')->index('usr_permissions_users_userid');
            $table->integer('permissionId')->index('usr_permissions_users_permissionid');
            $table->tinyInteger('show')->nullable();
            $table->tinyInteger('creater')->nullable();
            $table->tinyInteger('editer')->nullable();
            $table->tinyInteger('deleter')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->unique(['userId', 'permissionId'], 'usr_permissions_users_user_permission_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usr_permissions_users');
    }
};
