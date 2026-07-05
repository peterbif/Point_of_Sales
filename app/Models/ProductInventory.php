<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductInventory extends Model
{
    //
    protected $fillable = [
        'inventory',
        'product_id',
        'user_id',
      
    ];


    public function product(){
        return $this->belongsTo(Product::class);
    }


}
