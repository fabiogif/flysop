<?php

namespace App\Observers;

use Illuminate\Support\Str;
use App\Models\Category;

class CategoryObserver
{
    /**
     * Handle the Category "created" event.
     *
     * @param  \App\Models\Models\Category  $category
     * @return void
     */
    public function created(Category $category)
    {
    }

    /**
     * Handle the Category "updated" event.
     *
     * @param  \App\Models\Models\Category  $category
     * @return void
     */
    public function updated(Category $category)
    {
        //
    }

    /**
     * Handle the Category "deleted" event.
     *
     * @param  \App\Models\Models\Category  $category
     * @return void
     */
    public function deleted(Category $category)
    {
        //
    }

    /**
     * Handle the Category "creating" event.
     *
     * @param  \App\Models\Models\Category  $category
     * @return void
     */
    public function creating(Category $category)
    {
        $category->url = Str::kebab($category->name);
    }

    /**
     * Handle the Category "updating" event.
     *
     * @param  \App\Models\Models\Category  $category
     * @return void
     */
    public function updating(Category $category)
    {
        $category->url = Str::kebab($category->name);
    }
}
