<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderDetail extends Model
{
    use  SoftDeletes;

    // protected $guard = [];

    protected $fillable = [
        'transaction_id', 
        'product_id',
        'product_category_id',
        'description',
        'qty',
        'unit_price',
        'discount',
        'created_by_id',
        'updated_by_id'
    ];


    public function  product(){

        return $this->belongsTo(Product::class, 'product_id');
    }

    public function user(){

       return $this->belongsTo(User::class, 'created_by_id');
    }


    public function order(){

        return $this->belongsTo(Order::class, 'order_id');
     }
 
}
