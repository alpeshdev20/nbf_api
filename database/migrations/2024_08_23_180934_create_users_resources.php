<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('create_users_resources', function (Blueprint $table) {
            $table->id();
            $table->string('resource_type'); // e.g., Signup Page, Newsletter, etc.
            $table->string('name')->nullable();
            $table->string('email_address')->nullable();
            $table->string('mobile_number')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable();
            $table->text('personal_address')->nullable();
            $table->text('institution_address')->nullable();
            $table->string('preferred_segment')->nullable();
            $table->string('class')->nullable();
            $table->string('publisher_name')->nullable();
            $table->string('contact_number')->nullable();
            $table->text('resource_catalogue')->nullable();
            $table->string('school_college_university_name')->nullable();
            $table->string('student_enrollment')->nullable();
            $table->text('summary')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('create_users_resources');
    }
};
