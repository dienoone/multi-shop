<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'category_id'    => ['sometimes', 'nullable', 'exists:categories,id'],
            'name'           => ['required', 'string', 'max:255'],
            'description'    => ['sometimes', 'nullable', 'string'],
            'price'          => ['required', 'numeric', 'min:0'],
            'compare_price'  => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['sometimes', 'integer', 'min:0'],
            'sku'            => ['sometimes', 'nullable', 'string', 'max:100'],
            'is_active'      => ['sometimes', 'boolean'],
            'images'         => ['sometimes', 'array'],
            'images.*'       => ['url'],
        ];
    }
}
