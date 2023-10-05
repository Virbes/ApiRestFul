<?php

namespace App\Http\Controllers\Product;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Models\Category;

class ProductCategoryController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Product $product)
    {
        return $this->showAll($product->categories);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product, Category $category)
    {
        // sync, attach, syncWithoutDetaching
        $product->categories()->syncWithoutDetaching([$category->id]);

        return $this->showAll($product->categories);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product, Category $category)
    {
        if (!$product->categories()->find($category->id)) {
            return $this->errorResponse('La categoria especificada no es una categoria de este producto', 404);
        }

        $product->categories()->detach([$category->id]);

        return $this->showAll($product->categories);
    }
}
