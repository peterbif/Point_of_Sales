<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAlerts extends Model
{
    //

    protected $fillable = ['very_low_stock', 'low_stock', 'stock_alert_days'];

}
