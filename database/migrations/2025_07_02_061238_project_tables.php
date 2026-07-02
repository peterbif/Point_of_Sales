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
        // update existing user table
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique();
            // $table->string('email')->unique();
            $table->enum('role', ['admin', 'cashier', 'superadmin', 'superadmin2']);
            $table->boolean('active');
            $table->bigInteger('created_by_id')->default(0);
            $table->bigInteger('updated_by_id')->default(0);
            $table->bigInteger('deleted_by_id')->default(0);
            $table->dropColumn('name');
            // $table->dropColumn('email');
            $table->dropColumn('email_verified_at');
            $table->softDeletes();
        });
    
        // Schema::create('tables', function (Blueprint $table) {
        //     // Set the storage engine to InnoDB
        //     $table->engine = 'InnoDB';
        //     $table->id();
        //     $table->string('name', 50);
        //     $table->tinyInteger('status')->default(2);
        //     $table->string('invoice_no', 20)->nullable();
        //     $table->tinyInteger('discount')->default(0);
        //     $table->decimal('total_discount')->default(0);
        //     $table->decimal('grand_total')->default(0);
        //     $table->decimal('total')->default(0);
        //     $table->decimal('net_amount')->default(0);
        //     $table->integer('order')->default(1000);
        //     $table->bigInteger('created_by_id')->default(0);
        //     $table->bigInteger('updated_by_id')->default(0);
        //     $table->bigInteger('deleted_by_id')->default(0);
        //     $table->timestamps();
        //     $table->softDeletes();
        // });

        
   
      

       

        // balance adjustment
        // Schema::create('balance_adjustments', function (Blueprint $table) {
        //     $table->id();
        //     $table->decimal('amount');
        //     $table->tinyInteger('type_id');
        //     $table->date('adjustment_date');
        //     $table->string('remark');
        //     $table->bigInteger('created_by_id')->default(0);
        //     $table->bigInteger('updated_by_id')->default(0);
        //     $table->bigInteger('deleted_by_id')->default(0);
        //     $table->timestamps();
        //     $table->softDeletes();
        // });
    }

    /**
     * Reverse the migrations.
     */
    // public function down(): void
    // {
    //     // Schema::dropIfExists('tables');
      
    //     Schema::dropIfExists('balance_adjustments');
    // }
};
