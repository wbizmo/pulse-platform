<?php

namespace App\Http\Requests\Admin;

use App\Domain\Commerce\StockMovement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InventoryAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('commerce.inventory.manage') ?? false;
    }

    public function rules(): array
    {
        return ['quantity' => ['required', 'integer', 'not_in:0', 'between:-1000000,1000000'], 'movement' => ['required', Rule::in([StockMovement::Opening->value, StockMovement::Receipt->value, StockMovement::AdjustmentIncrease->value, StockMovement::AdjustmentDecrease->value])], 'reason' => ['required', 'string', 'max:300']];
    }
}
