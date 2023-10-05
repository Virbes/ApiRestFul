<?php

namespace App\Http\Controllers\Category;

use App\Models\Category;
use App\Http\Controllers\ApiController;

class CategoryProductController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Category $category)
    {
        return $this->showAll($category->products);
    }
}
