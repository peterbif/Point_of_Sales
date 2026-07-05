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
        //
         // order
         Schema::create('orders', function (Blueprint $table) {
            // Set the storage engine to InnoDB
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('transaction_id')->unique;
            // $table->string('invoice_no', 20);
            $table->integer('discount')->default(0);
            $table->decimal('total_discount')->default(0);
            $table->decimal('total');
            $table->decimal('sub_total');
            $table->decimal('vat_amount');
            $table->decimal('grand_total');
            $table->string('mode_of_payment');
            $table->decimal('receive_amount');
            $table->bigInteger('created_by_id')->default(0);
            $table->bigInteger('updated_by_id')->default(0);
            $table->bigInteger('deleted_by_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // order detail
        Schema::create('order_details', function (Blueprint $table) {
            // Set the storage engine to InnoDB
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('transaction_id');
            $table->foreignId('order_id');
            $table->foreignId('product_id');
            $table->foreignId('product_category_id');
            $table->string('description', 100);
            $table->integer('qty');
            $table->decimal('unit_price');
            $table->tinyInteger('discount')->default(0);
            $table->bigInteger('created_by_id')->default(0);
            $table->bigInteger('updated_by_id')->default(0);
            $table->bigInteger('deleted_by_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // order detail temp
        Schema::create('order_detail_temps', function (Blueprint $table) {
            // Set the storage engine to InnoDB
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('transaction_id');
            $table->foreignId('product_id');
            $table->foreignId('product_category_id');
            $table->string('description', 100);
            $table->integer('qty');
            $table->decimal('unit_price');
            $table->tinyInteger('discount')->default(0);
            $table->bigInteger('created_by_id')->default(0);
            $table->bigInteger('updated_by_id')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //

        Schema::dropIfExists('orders');
        Schema::dropIfExists('order_details');
        Schema::dropIfExists('order_detail_temps');
    }
};
