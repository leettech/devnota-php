<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_nfses', function (Blueprint $table) {
            $table->bigInteger('id', true);

            $table->string('rps')->index();
            $table->string('gateway_payment_id')->index();
            $table->string('price');
            $table->timestamp('payment_date');

            $table->string('status')->nullable()->default('waiting');
            $table->string('number')->nullable();
            $table->string('verification_code')->nullable();
            $table->timestamp('issue_date')->nullable();

            $table->json('customer');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_nfses');
    }
};
