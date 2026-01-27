<?php
namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;

class AdminProductCategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::latest()->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
        dd( $request->all());

        ProductCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->is_active,
        ]);



        return redirect('/admin/categories')->with('success', 'Category created');
    }
}