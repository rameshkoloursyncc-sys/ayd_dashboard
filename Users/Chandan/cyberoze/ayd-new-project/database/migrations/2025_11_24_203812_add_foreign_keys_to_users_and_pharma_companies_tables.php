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
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('pharma_company_id')->references('id')->on('pharma_companies')->onDelete('set null');
        });

        Schema::table('pharma_companies', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['pharma_company_id']);
        });

        Schema::table('pharma_companies', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
    }
};