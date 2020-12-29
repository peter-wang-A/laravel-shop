<?php

namespace App\Http\ViewComposers;

use App\Services\CategoryService;
use Illuminate\View\View;

class CategoryTreeComposer
{
    protected $cate;
    public function __construct(CategoryService $categoryService)
    {
        $this->cate = $categoryService;
    }

    public function compose(View $view)
    {
        $view->with('categoryTree', $this->cate->getCategoryTree());
    }
}
