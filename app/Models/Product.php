<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use PhpOffice\PhpSpreadsheet\Calculation\Category;
use Illuminate\Support\Str;


class Product extends Model
{
    use SoftDeletes;

    // protected $guard = [];

    protected $fillable = [
        'name',
        'barcode',
        'expiry_date',
        'product_category_id',
        'unit_price',
        'wholesales_price',
        'stock_alert_days',
        'stock_alert_qty_very_low',
        'stock_alert_qty_low',
        'inventory',
        'image',
        'created_by_id',
        'updated_by_id'
    ];


    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucwords($value);
    }


    public function inventories(){
        return $this->hasMany(ProductInventory::class);
    }

    public function category(){

        return $this->belongsTo(ProductCategory ::class, 'product_category_id');
    }

    public function orderDetailTemps()
{
    return $this->hasMany(OrderDetailTemp::class, 'product_id');
}



// protected static function boot()
// {
//     parent::boot();

//     static::creating(function ($product) {

//         if (!$product->barcode) {

//             do {
//                 // 12 digit numeric barcode
//                 $barcode = str_pad(random_int(1, 999999999999), 12, '0', STR_PAD_LEFT);
//             } while (self::where('barcode', $barcode)->exists());

//             $product->barcode = $barcode;
//         }
//     });
// }


protected static function boot()
{
    parent::boot();

    static::creating(function ($product) {

        // If barcode provided (supplier), keep it
        if (!empty($product->barcode)) {
            return;
        }

        $product->barcode = self::generateUniqueBarcode();
    });

    static::updating(function ($product) {

        // Prevent accidental duplicate when manually editing
        if (!empty($product->barcode)) {
            $exists = self::where('barcode', $product->barcode)
                ->where('id', '!=', $product->id)
                ->exists();

            if ($exists) {
                throw new \Exception("Barcode already exists");
            }
        }
    });
}



public static function generateUniqueBarcode()
{
    do {
        // 12-digit numeric (EAN-like)
        // $barcode = str_pad(random_int(1, 999999999999), 12, '0', STR_PAD_LEFT);

        $barcode = '20' . date('ymd') . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);

    } while (self::where('barcode', $barcode)->exists());

    return $barcode;
}

}
