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
        Schema::table('u_logins', function (Blueprint $table) {
            $table->enum('gender', ['Male', 'Female', 'Others'])->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('u_logins', function (Blueprint $table) {
            $table->enum('gender', ['Male', 'Female'])->nullable()->default(null)->change();
        });
    }
};
