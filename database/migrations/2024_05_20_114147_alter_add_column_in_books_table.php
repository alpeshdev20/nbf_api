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
        Schema::table('books', function (Blueprint $table) {
            $table->string('rating', 50)->default(null)->nullable(); //->after('content');
            $table->string('reviews', 50)->default(null)->nullable()->after('rating');
            $table->enum('ai_consversion_status', ['Yes', 'No'])->default('No')->after('reviews');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['rating', 'reviews', 'ai_consversion_status']);
        });
    }
};
