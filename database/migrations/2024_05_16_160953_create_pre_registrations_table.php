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
        Schema::create('pre_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('email', 255);
            $table->string('password', 255);
            $table->string('mobile', 20);
            $table->date('birthday', 20)->default(null)->nullable();
            $table->enum('gender', ['Male', 'Female'])->default(null)->nullable();
            $table->enum('preferred_segment', ['K12/School', 'Higher Education'])->default(null)->nullable();
            $table->bigInteger('class')->default(null)->nullable();
            $table->text('personal_address')->default(null)->nullable();
            $table->text('institute_address')->default(null)->nullable();
            $table->integer('otp')->default(null)->nullable();
            $table->integer('registration_type')->default(null)->nullable();
            $table->string('registration_token', 255)->default(null)->nullable();
            $table->enum('is_account_created', ['Yes', "No"])->default("No")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_registrations');
    }
};
