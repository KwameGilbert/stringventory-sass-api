<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\PurchaseItem;
use App\Models\AuditLog;
use App\Helper\ResponseHelper;
use App\Controllers\PurchaseController;
use App\Controllers\ProductController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class PurchaseItemController
{
    private PurchaseController $purchaseController;
    private ProductController $productController;

    public function __construct(PurchaseController $purchaseController, ProductController $productController)
    {
        $this->purchaseController = $purchaseController;
        $this->productController = $productController;
    }

    /**
     * Get all purchase items
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $purchaseItems = PurchaseItem::with(['product', 'purchase'])->get();
            return ResponseHelper::success($response, 'Purchase items fetched successfully', $purchaseItems->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch purchase items', 500, $e->getMessage());
        }
    }

    /**
     * Get single purchase item
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $purchaseItem = PurchaseItem::with(['product', 'purchase'])->find($args['id']);
            if (!$purchaseItem) {
                return ResponseHelper::error($response, 'Purchase item not found', 404);
            }
            return ResponseHelper::success($response, 'Purchase item fetched successfully', $purchaseItem->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch purchase item', 500, $e->getMessage());
        }
    }

    /**
     * Create purchase item
     */
    public function create(Request $request, Response $response): Response
    {
        try {
            $data = (array)($request->getParsedBody() ?? []);

            if (empty($data['purchaseId']) || empty($data['productId']) || empty($data['quantity'])) {
                return ResponseHelper::error($response, 'purchaseId, productId, and quantity are required', 400);
            }

            $purchaseId = (int)$data['purchaseId'];
            $productId = (int)$data['productId'];

            // Verify purchase exists using PurchaseController
            $purchaseResponse = $this->purchaseController->show($request, new \Slim\Psr7\Response(), ['id' => (string)$purchaseId]);
            if ($purchaseResponse->getStatusCode() === 404) {
                return ResponseHelper::error($response, "Invalid Purchase ID {$purchaseId}. Purchase not found.", 400);
            }

            // Verify product exists using ProductController
            $productResponse = $this->productController->show($request, new \Slim\Psr7\Response(), ['id' => (string)$productId]);
            if ($productResponse->getStatusCode() === 404) {
                return ResponseHelper::error($response, "Invalid Product ID {$productId}. Product not found.", 400);
            }

            $purchaseItem = PurchaseItem::create($data);

            $user = $request->getAttribute('user');
            AuditLog::log($request, $user ? $user->businessId : null, $user ? $user->id : null, 'purchase_item_created', [
                'purchaseItemId' => $purchaseItem->id,
                'purchaseId' => $purchaseItem->purchaseId,
                'productId' => $purchaseItem->productId,
            ]);

            return ResponseHelper::success($response, 'Purchase item created successfully', $purchaseItem->toArray(), 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create purchase item', 500, $e->getMessage());
        }
    }

    /**
     * Update purchase item
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $purchaseItem = PurchaseItem::find($args['id']);
            if (!$purchaseItem) {
                return ResponseHelper::error($response, 'Purchase item not found', 404);
            }

            $data = (array)($request->getParsedBody() ?? []);
            $purchaseItem->update($data);

            $user = $request->getAttribute('user');
            AuditLog::log($request, $user ? $user->businessId : null, $user ? $user->id : null, 'purchase_item_updated', [
                'purchaseItemId' => $purchaseItem->id,
                'purchaseId' => $purchaseItem->purchaseId,
            ]);

            return ResponseHelper::success($response, 'Purchase item updated successfully', $purchaseItem->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update purchase item', 500, $e->getMessage());
        }
    }

    /**
     * Delete purchase item
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $purchaseItem = PurchaseItem::find($args['id']);
            if (!$purchaseItem) {
                return ResponseHelper::error($response, 'Purchase item not found', 404);
            }

            $purchaseItemId = $purchaseItem->id;
            $purchaseId = $purchaseItem->purchaseId;
            $purchaseItem->delete();

            $user = $request->getAttribute('user');
            AuditLog::log($request, $user ? $user->businessId : null, $user ? $user->id : null, 'purchase_item_deleted', [
                'purchaseItemId' => $purchaseItemId,
                'purchaseId' => $purchaseId,
            ]);

            return ResponseHelper::success($response, 'Purchase item deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete purchase item', 500, $e->getMessage());
        }
    }
}
