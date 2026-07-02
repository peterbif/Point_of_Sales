<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetailTemp extends Model
{

    protected $guard = [];


    // public function table()
    // {
    //     return $this->belongsTo(Table::class);
    // }

    public function  product(){

        return $this->belongsTo(Product::class, 'product_id');
    }

    public function user(){

       return $this->belongsTo(User::class, 'created_by_id');
    }


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
}
