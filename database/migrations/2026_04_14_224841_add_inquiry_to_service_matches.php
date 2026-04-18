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
    Schema::table('service_matches', function (Blueprint $table) {
        $table->unsignedInteger('inquiry_messages')->default(0);
    });
    // في migration جديد
DB::statement("ALTER TABLE service_matches MODIFY COLUMN status ENUM('inquiry','pending','accepted','completed') NOT NULL DEFAULT 'pending'");
}

public function down(): void
{
    Schema::table('service_matches', function (Blueprint $table) {
        $table->dropColumn('inquiry_messages');
    });
}
};
