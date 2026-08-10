<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * List all categories.
     */
    public function index(): View
    {
        $categories = Category::withCount('events')->orderBy('name')->get();

        return view('admin.categories.index', ['categories' => $categories]);
    }

    /**
     * Show the form to add a new category.
     */
    public function create(): View
    {
        return view('admin.categories.create');
    }

    /**
     * Store a new category.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        Category::create($request->validated());

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created.');
    }

    /**
     * Show the form to edit an existing category.
     */
    public function edit(Category $category): View
    {
        return view('admin.categories.edit', ['category' => $category]);
    }

    /**
     * Update an existing category.
     */
    public function update(StoreCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated.');
    }

    /**
     * Delete a category.
     *
     * Per the build plan, the events FK uses cascadeOnDelete(), so removing a
     * category also removes its events.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted.');
    }
}
