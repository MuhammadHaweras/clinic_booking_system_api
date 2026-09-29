<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Business information provided during onboarding
            $table->string('business_name');
            $table->text('bio');
            $table->string('phone', 20);
            $table->string('address')->nullable();
            $table->string('city')->nullable();

            /**
             * Application lifecycle:
             *   pending  → submitted, awaiting admin review
             *   approved → admin approved; user gains the 'provider' role
             *   rejected → admin rejected; reason stored below
             */
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();

            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            // A user may only have one provider profile at a time
            $table->unique('user_id');
            // Admin listing queries filter by status
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_profiles');
    }
};
