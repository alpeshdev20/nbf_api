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
        Schema::create('otp_master', function (Blueprint $table) {
            $table->id();
            $table->enum('user_type', ['User', 'School User', 'Teachers'])->default(null)->nullable();
            $table->string('otp_for', 255)->nullable()->default(null);
            $table->string('email', 255)->nullable()->default(null);
            $table->integer('otp');
            $table->enum('status', ['Active', 'Inactive']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_master');
    }
};