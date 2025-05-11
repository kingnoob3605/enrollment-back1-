<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->date('check_date');
            $table->float('height')->comment('in cm');
            $table->float('weight')->comment('in kg');
            $table->float('bmi');
            $table->string('nutritional_status');
            $table->string('vision')->default('Normal');
            $table->string('hearing')->default('Normal');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_records');
    }
};