<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inv_items')) {
            return;
        }

        DB::statement("ALTER TABLE `inv_items` MODIFY COLUMN `type` ENUM(
            'COMBO',
            'COMPRA NACIONAL',
            'IMPORTADO',
            'PRODUCIDO',
            'INSUMO',
            'ENSAMBLADO',
            'PROYECTADOS',
            'DESCONTINUADOS',
            'CZCL'
        ) NOT NULL");
    }

    public function down(): void
    {
        if (!Schema::hasTable('inv_items')) {
            return;
        }

        DB::statement("ALTER TABLE `inv_items` MODIFY COLUMN `type` ENUM(
            'COMBO',
            'COMPRA NACIONAL',
            'IMPORTADO',
            'PRODUCIDO'
        ) NOT NULL");
    }
};
