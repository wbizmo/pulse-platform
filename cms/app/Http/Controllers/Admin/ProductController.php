<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Commerce\SaveProduct;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Http\Requests\Admin\VariantRequest;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $r): View
    {
        $q = Product::withCount('variants')->with('featuredMedia')->latest();
        if ($search = trim((string) $r->query('q'))) {
            $q->where(fn ($x) => $x->where('name', 'like', '%'.addcslashes(mb_substr($search, 0, 100), '%_\\').'%')->orWhere('slug', 'like', '%'.addcslashes(mb_substr($search, 0, 100), '%_\\').'%'));
        }

return view('admin.commerce.products.index', ['products' => $q->paginate(20)->withQueryString()]);
    }

    public function create(): View
    {
        return $this->form(new Product);
    }

    public function edit(Product $product): View
    {
        $product->load(['categories', 'gallery', 'variants']);

        return $this->form($product);
    }

    private function form(Product $product): View
    {
        return view('admin.commerce.products.form', ['product' => $product, 'categories' => ProductCategory::where('is_active', true)->orderBy('name')->limit(200)->get(), 'media' => Media::where('type', 'image')->latest()->limit(100)->get()]);
    }

    public function store(ProductRequest $r, SaveProduct $save): RedirectResponse
    {
        $p = $save->execute(new Product, $r->validated(), $r->user());

        return redirect()->route('admin.commerce.products.edit', $p)->with('success', 'Product created.');
    }

    public function update(ProductRequest $r, Product $product, SaveProduct $save): RedirectResponse
    {
        $save->execute($product, $r->validated(), $r->user());

        return back()->with('success', 'Product updated.');
    }

    public function storeVariant(VariantRequest $r, Product $product, SaveProduct $save): RedirectResponse
    {
        $save->saveVariant($product, null, $r->validated(), $r->user());

        return back()->with('success', 'Variant created.');
    }

    public function updateVariant(VariantRequest $r, Product $product, ProductVariant $variant, SaveProduct $save): RedirectResponse
    {
        $save->saveVariant($product, $variant, $r->validated(), $r->user());

        return back()->with('success','Variant updated.');
    }
}
