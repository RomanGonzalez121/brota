<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A level benefit used on an order (free delivery, free order) and how much
     * it took off what the customer pays. Restaurant and courier are paid in full.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('benefit')->nullable()->after('total');
            $table->unsignedInteger('discount')->default(0)->after('benefit');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['benefit', 'discount']);
        });
    }
};
