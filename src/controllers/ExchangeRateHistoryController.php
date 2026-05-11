<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ExchangeRateHistory;
use App\Models\AuditLog;
use App\Helper\ResponseHelper;
use App\Controllers\SettingsController;
use App\Services\CurrencyService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class ExchangeRateHistoryController
{
    private SettingsController $settingsController;
    private CurrencyService $currencyService;

    public function __construct(SettingsController $settingsController, CurrencyService $currencyService)
    {
        $this->settingsController = $settingsController;
        $this->currencyService = $currencyService;
    }

    /**
     * Get all exchange rate histories
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $history = ExchangeRateHistory::orderBy('effectiveDate', 'desc')->get();
            return ResponseHelper::success($response, 'Exchange rate history fetched successfully', $history->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch exchange rate history', 500, $e->getMessage());
        }
    }

    /**
     * Get single exchange rate history entry
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $entry = ExchangeRateHistory::find($args['id']);
            if (!$entry) {
                return ResponseHelper::error($response, 'Exchange rate history entry not found', 404);
            }
            return ResponseHelper::success($response, 'Exchange rate history entry fetched successfully', $entry->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch exchange rate history entry', 500, $e->getMessage());
        }
    }

    /**
     * Create exchange rate history entry
     */
    public function create(Request $request, Response $response): Response
    {
        try {
            $data = (array)($request->getParsedBody() ?? []);

            if (empty($data['baseCurrency']) || empty($data['targetCurrency']) || empty($data['rate']) || empty($data['effectiveDate'])) {
                return ResponseHelper::error($response, 'baseCurrency, targetCurrency, rate, and effectiveDate are required', 400);
            }

            // Create entry
            $entry = ExchangeRateHistory::create($data);

            $user = $request->getAttribute('user');
            AuditLog::log($request, $user ? $user->businessId : null, $user ? $user->id : null, 'exchange_rate_created', [
                'entryId' => $entry->id,
                'baseCurrency' => $entry->baseCurrency,
                'targetCurrency' => $entry->targetCurrency,
                'rate' => $entry->rate,
            ]);

            return ResponseHelper::success($response, 'Exchange rate history entry created successfully', $entry->toArray(), 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create exchange rate history entry', 500, $e->getMessage());
        }
    }

    /**
     * Update exchange rate history entry
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $entry = ExchangeRateHistory::find($args['id']);
            if (!$entry) {
                return ResponseHelper::error($response, 'Exchange rate history entry not found', 404);
            }

            $data = (array)($request->getParsedBody() ?? []);
            $entry->update($data);

            $user = $request->getAttribute('user');
            AuditLog::log($request, $user ? $user->businessId : null, $user ? $user->id : null, 'exchange_rate_updated', [
                'entryId' => $entry->id,
                'baseCurrency' => $entry->baseCurrency,
                'targetCurrency' => $entry->targetCurrency,
                'rate' => $entry->rate,
            ]);

            return ResponseHelper::success($response, 'Exchange rate history entry updated successfully', $entry->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update exchange rate history entry', 500, $e->getMessage());
        }
    }

    /**
     * Delete exchange rate history entry
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $entry = ExchangeRateHistory::find($args['id']);
            if (!$entry) {
                return ResponseHelper::error($response, 'Exchange rate history entry not found', 404);
            }

            $entryId = $entry->id;
            $entry->delete();

            $user = $request->getAttribute('user');
            AuditLog::log($request, $user ? $user->businessId : null, $user ? $user->id : null, 'exchange_rate_deleted', [
                'entryId' => $entryId,
            ]);

            return ResponseHelper::success($response, 'Exchange rate history entry deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete exchange rate history entry', 500, $e->getMessage());
        }
    }
}
