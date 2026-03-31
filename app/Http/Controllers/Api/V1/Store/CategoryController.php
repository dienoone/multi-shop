<?php

namespace App\Http\Controllers\Api\V1\Store;

use App\Contracts\Services\CategoryServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryServiceInterface $categoryService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $categories = $this->categoryService->list(
            $request->only(['search', 'is_active', 'per_page'])
        );

        return $this->paginated(CategoryResource::collection($categories));
    }

    public function show(int $id): JsonResponse
    {
        $category = $this->categoryService->findById($id);

        return $this->success(new CategoryResource($category));
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->create($request->validated());

        return $this->created(
            new CategoryResource($category),
            'Category created successfully.'
        );
    }

    public function update(UpdateCategoryRequest $request, int $id): JsonResponse
    {
        $category = $this->categoryService->findById($id);
        $category = $this->categoryService->update($category, $request->validated());

        return $this->success(
            new CategoryResource($category),
            'Category updated successfully.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $category = $this->categoryService->findById($id);
        $this->categoryService->delete($category);

        return $this->noContent('Category deleted successfully.');
    }
}
