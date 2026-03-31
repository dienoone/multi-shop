<?php

namespace App\Http\Controllers\Api\V1\Store;

use App\Contracts\Services\OrderServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\PlaceOrderRequest;
use App\Http\Resources\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        protected OrderServiceInterface $orderService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderService->listForCustomer(
            $request->user(),
            $request->only(['status', 'per_page'])
        );

        return $this->paginated(OrderResource::collection($orders));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $order = $this->orderService->findForCustomer($id, $request->user());

        return $this->success(new OrderResource($order));
    }

    public function store(PlaceOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->placeOrder(
            $request->user(),
            $request->validated()
        );

        return $this->created(
            new OrderResource($order),
            'Order placed successfully.'
        );
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $order = $this->orderService->cancelOrder($id, $request->user());

        return $this->success(
            new OrderResource($order),
            'Order cancelled successfully.'
        );
    }
}
