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

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            // $table->string('barcode')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->foreignId('product_category_id');
            $table->decimal('unit_price');
            $table->decimal('wholesales_price')->nullable();
            $table->string('image')->nullable();
            $table->integer('inventory');
            $table->date('expiry_date');
            $table->integer('stock_alert_days');
            $table->integer('stock_alert_qty_very_low');
            $table->integer('stock_alert_qty_low');
            $table->bigInteger('created_by_id')->default(0);
            $table->bigInteger('updated_by_id')->default(0);
            $table->bigInteger('deleted_by_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('products');

    }
};
