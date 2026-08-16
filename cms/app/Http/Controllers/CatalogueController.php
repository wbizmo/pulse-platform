<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\Seo\SeoResolver;
use App\Services\Themes\ThemeResolver;
use Illuminate\View\View;

class CatalogueController extends Controller
{
    public function __construct(SeoResolver $seo, ThemeResolver $themes, private readonly FrontendController $frontend)
    {
        $this->resolver = $themes;
        $this->seoResolver = $seo;
    }

    private ThemeResolver $resolver;

    private SeoResolver $seoResolver;

    public function index(): View
    {
        $runtime = $this->resolver->resolve();
        $data = $this->frontend->frontendData($runtime);
        $products = Product::with(['featuredMedia', 'variants' => fn ($q) => $q->where('is_active', true)->orderBy('price_minor')])->publiclyVisible()->latest()->paginate(12);

        return view('frontend.catalogue.index', $data + ['products' => $products, 'seo' => $this->seoResolver->resolve((object) ['title' => 'Catalogue', 'meta_description' => 'Browse our product catalogue.'], $data['settings'], route('catalogue.index'), 'archive', request()->integer('page', 1))]);
    }

    public function category(ProductCategory $category): View
    {
        abort_unless($category->is_active, 404);
        $runtime = $this->resolver->resolve();
        $data = $this->frontend->frontendData($runtime);
        $products = $category->products()->with(['featuredMedia', 'variants' => fn ($q) => $q->where('is_active', true)->orderBy('price_minor')])->publiclyVisible()->paginate(12);

        return view('frontend.catalogue.index', $data + compact('products', 'category') + ['seo' => $this->seoResolver->resolve((object) ['title' => $category->name, 'meta_description' => $category->description], $data['settings'], route('catalogue.category', $category->slug), 'archive')]);
    }

    public function show(string $slug): View
    {
        $runtime = $this->resolver->resolve();
        $data = $this->frontend->frontendData($runtime);
        $product = Product::with(['featuredMedia', 'gallery', 'categories', 'variants' => fn ($q) => $q->where('is_active', true)->orderBy('price_minor')])->publiclyVisible()->where('slug', $slug)->firstOrFail();

        return view('frontend.catalogue.show', $data + compact('product') + ['seo' => $this->seoResolver->resolve((object) ['title' => $product->name, 'meta_description' => $product->short_description, 'featuredMedia' => $product->featuredMedia], $data['settings'], route('catalogue.show', $product->slug), 'website')]);
    }
}
