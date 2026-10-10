<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->latest()->paginate(10);
        return view('admin.categories.index', compact('categories'));
    }

  public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255|unique:categories,name',
    ], [
        'name.unique' => 'هذا التصنيف موجود بالفعل!',
    ]);

    $category = Category::create([
        'name' => $request->name,
    ]);

    if ($request->wantsJson() || $request->ajax()) {
        return response()->json([
            'success' => true,
            'message' => 'تم إضافة التصنيف بنجاح',
            'category' => $category
        ], 200);
    }

    return redirect()->back()->with('success', 'تم إضافة التصنيف بنجاح');
}

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category->update($request->only('name', 'description'));

        return redirect()->back()->with('success', 'تم تحديث التصنيف بنجاح');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->back()->with('success', 'تم حذف التصنيف بنجاح');
    }
}