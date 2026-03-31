<?php

namespace App\Http\Controllers\Api\V1\Store;

use App\Contracts\Services\ProductServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        protected ProductServiceInterface $productService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $products = $this->productService->list(
            $request->only([
                'search',
                'category_id',
                'is_active',
                'in_stock',
                'min_price',
                'max_price',
                'per_page'
            ])
        );

        return $this->paginated(ProductResource::collection($products));
    }

    public function show(int $id): JsonResponse
    {
        $product = $this->productService->findById($id);

        return $this->success(new ProductResource($product));
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->create($request->validated());

        return $this->created(
            new ProductResource($product),
            'Product created successfully.'
        );
    }

    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = $this->productService->findById($id);
        $product = $this->productService->update($product, $request->validated());

        return $this->success(
            new ProductResource($product),
            'Product updated successfully.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $product = $this->productService->findById($id);
        $this->productService->delete($product);

        return $this->noContent('Product deleted successfully.');
    }
}
