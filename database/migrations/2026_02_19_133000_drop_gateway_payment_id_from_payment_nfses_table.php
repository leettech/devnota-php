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
        Schema::table('payment_nfses', function (Blueprint $table) {
            $table->dropIndex(['gateway_payment_id']);
            $table->dropColumn('gateway_payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_nfses', function (Blueprint $table) {
            $table->string('gateway_payment_id')->index()->after('rps');
        });
    }
};
