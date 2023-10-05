<?php

namespace App\Http\Controllers\Product;

use App\Models\Product;
use App\Http\Controllers\ApiController;

class ProductTransactionController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Product $product)
    {
        return $this->showAll($product->transactions);
    }
}
