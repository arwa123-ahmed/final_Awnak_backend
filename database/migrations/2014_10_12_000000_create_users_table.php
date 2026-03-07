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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->enum('role', ['customer', 'volunteer'])->nullable();
            $table->string('nationality')->nullable();
            $table->string('city');     
            $table->string('street');
            $table->string('phone');
            $table->string('gender');
            $table->string('national_id')->default('XXXX-XXXX-XXXX');
            $table->string('id_image')->nullable();
            $table->string('passport_image')->nullable();
            $table->string('national_id_image')->nullable();
            $table->string('password');
            $table->decimal('earnedBalance', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->default(0);
            $table->float('average_rating')->default(0);
            $table->integer('ratings_count')->default(0);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
