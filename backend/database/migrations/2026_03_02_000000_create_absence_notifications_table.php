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
        Schema::create('absence_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attendance_record_id')->nullable();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('session_id')->nullable();
            $table->string('notification_type')->default('telegram'); // telegram, email, sms
            $table->string('status')->default('pending'); // pending, sent, failed
            $table->string('telegram_message_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('attendance_record_id')->references('id')->on('attendance_records')->onDelete('set null');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('session_id')->references('id')->on('sessions')->onDelete('set null');

            // Indexes
            $table->index('student_id');
            $table->index('session_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absence_notifications');
    }
};
