<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('lrn', 12)->unique()->comment('Learner Reference Number');
            $table->string('name');
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('suffix')->nullable();
            $table->string('grade');
            $table->string('section');
            $table->enum('gender', ['Male', 'Female']);
            $table->date('birthdate')->nullable();
            $table->text('address')->nullable();
            $table->string('parent_name')->nullable();
            $table->string('parent_contact')->nullable();
            $table->string('parent_email')->nullable();
            $table->string('status')->default('Enrolled');
            $table->date('date_enrolled')->nullable();
            $table->string('teacher_assigned')->nullable();
            
            // Health information (SF8)
            $table->float('height')->nullable();
            $table->float('weight')->nullable();
            $table->float('bmi')->nullable();
            $table->string('nutritional_status')->nullable();
            $table->string('vision')->default('Normal');
            $table->string('hearing')->default('Normal');
            $table->string('vaccinations')->default('Complete');
            $table->string('dental_health')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('students');
    }
};