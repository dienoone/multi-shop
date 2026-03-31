<?php

namespace App\Http\Controllers\Api\V1\Store;

use App\Contracts\Services\OrderServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Enums\OrderStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function __construct(
        protected OrderServiceInterface $orderService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderService->listForTenant(
            $request->only(['status', 'payment_status', 'search', 'per_page'])
        );

        return $this->paginated(OrderResource::collection($orders));
    }

    public function show(int $id): JsonResponse
    {
        $order = $this->orderService->findForTenant($id);

        return $this->success(new OrderResource($order));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, int $id): JsonResponse
    {
        $order = $this->orderService->updateStatus(
            $id,
            OrderStatus::from($request->validated('status'))
        );

        return $this->success(
            new OrderResource($order),
            'Order status updated.'
        );
    }
}
