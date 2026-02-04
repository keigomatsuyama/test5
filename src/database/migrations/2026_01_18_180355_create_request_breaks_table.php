<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendance_request_breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_request_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->time('break_start');
            $table->time('break_end')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_request_breaks');
    }
};
