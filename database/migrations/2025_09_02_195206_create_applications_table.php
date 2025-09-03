<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('job_posts')->onDelete('cascade');
            $table->foreignId('applicant_id')->constrained('applicant_profiles')->onDelete('cascade');
            $table->text('cover_letter')->nullable();
            $table->enum('status', ['applied', 'shortlisted', 'rejected'])->default('applied');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
