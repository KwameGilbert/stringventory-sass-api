<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\OrderItem;
use App\Models\AuditLog;
use App\Helper\ResponseHelper;
use App\Controllers\OrderController;
use App\Controllers\ProductController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class OrderItemController
{
    private OrderController $orderController;
    private ProductController $productController;

    public function __construct(OrderController $orderController, ProductController $productController)
    {
        $this->orderController = $orderController;
        $this->productController = $productController;
    }

    /**
     * Get all order items
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $orderItems = OrderItem::with(['product', 'order'])->get();
            return ResponseHelper::success($response, 'Order items fetched successfully', $orderItems->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch order items', 500, $e->getMessage());
        }
    }

    /**
     * Get single order item
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $orderItem = OrderItem::with(['product', 'order'])->find($args['id']);
            if (!$orderItem) {
                return ResponseHelper::error($response, 'Order item not found', 404);
            }
            return ResponseHelper::success($response, 'Order item fetched successfully', $orderItem->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch order item', 500, $e->getMessage());
        }
    }

    /**
     * Create order item
     */
    public function create(Request $request, Response $response): Response
    {
        try {
            $data = (array)($request->getParsedBody() ?? []);

            if (empty($data['orderId']) || empty($data['productId']) || empty($data['quantity'])) {
                return ResponseHelper::error($response, 'orderId, productId, and quantity are required', 400);
            }

            $orderId = (int)$data['orderId'];
            $productId = (int)$data['productId'];

            // Verify order exists using OrderController
            $orderResponse = $this->orderController->show($request, new \Slim\Psr7\Response(), ['id' => (string)$orderId]);
            if ($orderResponse->getStatusCode() === 404) {
                return ResponseHelper::error($response, "Invalid Order ID {$orderId}. Order not found.", 400);
            }

            // Verify product exists using ProductController
            $productResponse = $this->productController->show($request, new \Slim\Psr7\Response(), ['id' => (string)$productId]);
            if ($productResponse->getStatusCode() === 404) {
                return ResponseHelper::error($response, "Invalid Product ID {$productId}. Product not found.", 400);
            }

            $orderItem = OrderItem::create($data);

            $user = $request->getAttribute('user');
            AuditLog::log($request, $user ? $user->businessId : null, $user ? $user->id : null, 'order_item_created', [
                'orderItemId' => $orderItem->id,
                'orderId' => $orderItem->orderId,
                'productId' => $orderItem->productId,
            ]);

            return ResponseHelper::success($response, 'Order item created successfully', $orderItem->toArray(), 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create order item', 500, $e->getMessage());
        }
    }

    /**
     * Update order item
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $orderItem = OrderItem::find($args['id']);
            if (!$orderItem) {
                return ResponseHelper::error($response, 'Order item not found', 404);
            }

            $data = (array)($request->getParsedBody() ?? []);
            $orderItem->update($data);

            $user = $request->getAttribute('user');
            AuditLog::log($request, $user ? $user->businessId : null, $user ? $user->id : null, 'order_item_updated', [
                'orderItemId' => $orderItem->id,
                'orderId' => $orderItem->orderId,
            ]);

            return ResponseHelper::success($response, 'Order item updated successfully', $orderItem->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update order item', 500, $e->getMessage());
        }
    }

    /**
     * Delete order item
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $orderItem = OrderItem::find($args['id']);
            if (!$orderItem) {
                return ResponseHelper::error($response, 'Order item not found', 404);
            }

            $orderItemId = $orderItem->id;
            $orderId = $orderItem->orderId;
            $orderItem->delete();

            $user = $request->getAttribute('user');
            AuditLog::log($request, $user ? $user->businessId : null, $user ? $user->id : null, 'order_item_deleted', [
                'orderItemId' => $orderItemId,
                'orderId' => $orderId,
            ]);

            return ResponseHelper::success($response, 'Order item deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete order item', 500, $e->getMessage());
        }
    }
}
