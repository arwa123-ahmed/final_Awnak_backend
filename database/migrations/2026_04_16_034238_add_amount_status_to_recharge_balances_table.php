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
    Schema::table('recharge_balances', function (Blueprint $table) {
        $table->decimal('amount', 10, 2)->after('image');
        $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('amount');
    });
}

public function down(): void
{
    Schema::table('recharge_balances', function (Blueprint $table) {
        $table->dropColumn(['amount', 'status']);
    });
}
};
