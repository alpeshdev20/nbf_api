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
            $table->date('birthday')->nullable()->default(null)->after('mobile');
            $table->enum('gender',['Male','Female'])->nullable()->default(null)->after('birthday');
            $table->enum('preferred_segment',['K12/School','Higher Education'])->nullable()->default(null)->after('gender');
            $table->bigInteger('class')->nullable()->default(null)->after('preferred_segment');
            $table->text('personal_address')->nullable()->default(null)->after('class');
            $table->text('institute_address')->nullable()->default(null)->after('personal_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('u_logins', function (Blueprint $table) {
            //
        });
    }
};
