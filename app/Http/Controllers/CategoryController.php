<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CategoryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:manage categories', only: ['index']),
            new Middleware('permission:create categories', only: ['create', 'store']),
            new Middleware('permission:edit categories', only: ['edit', 'update']),
            new Middleware('permission:delete categories', only: ['destroy']),
        ];
    }


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

   public function edit($id)
{
    $category = Category::findOrFail($id);
    return view('admin.categories.edit', compact('category'));
}

public function update(Request $request, $id)
{
    $category = Category::findOrFail($id);
    
    $request->validate([
        'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        'description' => 'nullable|string',
    ]);

    $category->update([
        'name' => $request->name,
        'description' => $request->description,
    ]);

    return redirect()->route('categories.index')->with('success', 'تم تعديل التصنيف بنجاح.');
}

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->back()->with('success', 'تم حذف التصنيف بنجاح');
    }
}