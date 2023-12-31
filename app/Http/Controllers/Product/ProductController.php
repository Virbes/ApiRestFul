<?php

namespace App\Http\Controllers\Product;

use App\Models\Product;
use App\Http\Controllers\ApiController;

class ProductController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->showAll(Product::all());
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return $this->showOne($product);
    }
}
