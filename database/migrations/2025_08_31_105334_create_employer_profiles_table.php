<?php

use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Schema\Blueprint;

use Illuminate\Support\Facades\Schema;

return new class extends Migration

{

    public function up(): void

    {

        Schema::create('employer_profiles', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('company_name');

            $table->string('tin_number')->unique();

            $table->string('address');

            $table->string('phone_number');

            $table->string('website')->nullable();

            $table->string('image')->nullable(); // logo or profile image

            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');

            $table->text('rejection_reason')->nullable();

            $table->timestamps();

        });

    }

    public function down(): void

    {

        Schema::dropIfExists('employer_profiles');

    }

};