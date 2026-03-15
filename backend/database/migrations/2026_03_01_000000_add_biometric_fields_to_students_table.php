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
        Schema::table('students', function (Blueprint $table) {
            // Card ID for RFID/NFC card scanning
            $table->string('card_id')->unique()->nullable()->after('contact');
            
            // Fingerprint template data (encrypted/hashed)
            // Store as text for flexibility with different scanner SDKs
            $table->text('fingerprint_template')->nullable()->after('card_id');
            
            // Fingerprint enrollment status
            $table->boolean('fingerprint_enrolled')->default(false)->after('fingerprint_template');
            
            // Last biometric scan timestamp
            $table->timestamp('last_biometric_scan')->nullable()->after('fingerprint_enrolled');
            
            // Indexes for faster lookups
            $table->index('card_id');
            $table->index('fingerprint_enrolled');
            $table->index('last_biometric_scan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'card_id',
                'fingerprint_template',
                'fingerprint_enrolled',
                'last_biometric_scan',
            ]);
        });
    }
};
