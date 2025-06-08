<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('nome_fantasia')->nullable()->after('nome');
            $table->string('razao_social')->nullable()->after('nome_fantasia');
            $table->boolean('termos_aceitos')->default(false)->after('senha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn(['nome_fantasia', 'razao_social', 'termos_aceitos']);
        });
    }
};
