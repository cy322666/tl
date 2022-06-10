<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('company_id')->nullable();
            $table->integer('transaction_id')->nullable();
            $table->integer('amount')->nullable();
            $table->integer('record_id')->nullable();
            $table->integer('client_id')->nullable();
            $table->integer('visit_id')->nullable();
            $table->string('comment')->nullable();
            $table->timestamps();

            $table->index('company_id');
            $table->index('transaction_id');
            $table->index('amount');
            $table->index('record_id');
            $table->index('client_id');
            $table->index('visit_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
