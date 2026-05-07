<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

  protected $fillable = [
    'name',
    'price',
    'stock_quantity',
    'category_id',
];


    // Relationship: Category has many products
    // inside app/Models/Product.php

public function category()
{
    return $this->belongsTo(Category::class);
}

}