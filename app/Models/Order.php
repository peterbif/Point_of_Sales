<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use  SoftDeletes;

    // protected $guard = [];


    protected $fillable = [
            'transaction_id',
            'discount',
            'total_discount',
            'total',
            'sub_total',
            'vat_amount',
            'grand_total',
            'mode_of_payment',
            'receive_amount',
            'created_by_id',
            'updated_by_id',
           
            

    ];

    public function order_details()
    {
        return $this->hasMany(OrderDetail::class);
    }

  

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by_id', 'id');
    }
}
