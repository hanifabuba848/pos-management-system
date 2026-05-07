<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reference_code',
        'total',
        'payment_method',
        'amount_tendered',
        'customer_id',
        'total_discount',
        'change_given',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

   public function items()
{
    return $this->hasMany(TransactionItem::class);
}

public function user()
{
    return $this->belongsTo(User::class);
}

}
