<?php

use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Schema\Blueprint;

use Illuminate\Support\Facades\Schema;

return new class extends Migration

{

    public function up(): void

    {

        Schema::create('job_posts', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('user_id'); // employer user

            $table->string('title');

            $table->text('description');

            $table->string('location')->nullable();

            $table->decimal('salary', 10, 2)->nullable();

            $table->date('deadline')->nullable(); // ✅ Deadline for applications

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

        });

    }

    public function down(): void

    {

        Schema::dropIfExists('job_posts');

    }

};