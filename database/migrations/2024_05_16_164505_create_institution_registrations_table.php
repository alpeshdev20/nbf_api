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
        Schema::create('institution_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('institute_name', 255);
            $table->string('student_enrollment', 255);
            $table->string('contact_person', 255);
            $table->string('contact_person_email', 255)->default(null)->nullable();
            $table->string('contact_person_mobile_no', 20);
            $table->text('summary')->default(null)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institution_registrations');
    }
};
