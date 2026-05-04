<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Filament;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('filament')->where('is_active', true);
        
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }
        if ($material = $request->input('material')) {
            $query->where('material', $material);
        }

        if ($sort = $request->input('sort')) {
            $dir = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';
            if (in_array($sort, ['name', 'print_time_minutes', 'price'])) {
                $query->orderBy($sort, $dir);
            }
        } else {
            $query->latest();
        }

        return Inertia::render('Products/Index', [
            'products' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'material', 'sort', 'direction'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Products/Create', [
            'filaments' => Filament::select('id', 'brand', 'name', 'color_hex')->get()
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Product::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',

            'weight_grams' => 'required|numeric',
            'print_time_minutes' => 'required|integer',
            'price' => 'required|numeric',
            'image' => 'nullable|image|max:5120',
            'model_file' => 'nullable|file|max:102400',
            'is_active' => 'boolean'
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        if ($request->hasFile('model_file')) {
            $file = $request->file('model_file');
            $validated['model_file_name'] = $file->getClientOriginalName();
            $validated['model_file_path'] = $file->store('models', 'public');
        }

        Product::create($validated);
        return redirect()->route('products.index');
    }

    public function edit(Product $product)
    {
        return Inertia::render('Products/Edit', [
            'product' => $product,
            'filaments' => Filament::select('id', 'brand', 'name', 'color_hex')->get()
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',

            'weight_grams' => 'required|numeric',
            'print_time_minutes' => 'required|integer',
            'price' => 'required|numeric',
            'image' => 'nullable|image|max:5120',
            'model_file' => 'nullable|file|max:102400',
            'is_active' => 'boolean'
        ]);

        if ($request->hasFile('image')) {
            if ($product->image_path) Storage::disk('public')->delete($product->image_path);
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        if ($request->hasFile('model_file')) {
            if ($product->model_file_path) Storage::disk('public')->delete($product->model_file_path);
            $file = $request->file('model_file');
            $validated['model_file_name'] = $file->getClientOriginalName();
            $validated['model_file_path'] = $file->store('models', 'public');
        }

        $product->update($validated);
        return redirect()->route('products.index');
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        $product->delete();
        return redirect()->route('products.index');
    }

    public function trash()
    {
        return Inertia::render('Products/Trash', [
            'products' => Product::onlyTrashed()->with('filament')->paginate(15)
        ]);
    }

    public function restore($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $product);
        $product->restore();
        return back();
    }

    public function forceDelete($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $product);
        
        if ($product->image_path) Storage::disk('public')->delete($product->image_path);
        if ($product->model_file_path) Storage::disk('public')->delete($product->model_file_path);
        
        $product->forceDelete();
        return back();
    }
}
