<?php

namespace App\Actions\Commerce;

use App\Actions\Access\RecordAudit;
use App\Domain\Commerce\Sku;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class SaveProduct
{
    public function __construct(private readonly RecordAudit $audit) {}

    public function execute(Product $product, array $data, User $actor): Product
    {
        return DB::transaction(function () use ($product, $data, $actor) {
            $creating = ! $product->exists;
            $categories = $data['category_ids'] ?? [];
            $gallery = $data['gallery_media_ids'] ?? [];
            unset($data['category_ids'], $data['gallery_media_ids']);
            $product->fill($data)->save();
            $product->categories()->sync($categories);
            $product->gallery()->sync(collect($gallery)->values()->mapWithKeys(fn ($id, $position) => [$id => ['position' => $position]])->all());
            $this->audit->execute($actor, $creating ? 'commerce.product.created' : 'commerce.product.updated', $product, ['slug' => $product->slug, 'state' => $product->state->value]);

            return $product;
        });
    }

    public function saveVariant(Product $product, ?ProductVariant $variant, array $data, User $actor): ProductVariant
    {
        return DB::transaction(function () use ($product, $variant, $data, $actor) {
            $sku = new Sku($data['sku']);
            $options = collect($data['options'] ?? [])->mapWithKeys(fn ($v, $k) => [trim(mb_strtolower($k)) => trim($v)])->sortKeys()->all();
            $record = $variant ?? new ProductVariant(['product_id' => $product->id]);
            abort_if($record->exists && $record->product_id !== $product->id, 404);
            $record->fill($data + ['normalized_sku' => $sku->normalized, 'options' => $options ?: null, 'options_fingerprint' => hash('sha256', json_encode($options))]);
            $record->product_id = $product->id;
            $record->save();
            $this->audit->execute($actor, $variant ? 'commerce.variant.updated' : 'commerce.variant.created', $record, ['sku' => $record->sku, 'currency' => $record->currency]);

            return $record;
        });
    }
}
