<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class UtilsController
{
    /**
     * Generate secure random string/token helper
     */
    public function generateToken(Request $request, Response $response): Response
    {
        try {
            $queryParams = $request->getQueryParams();
            $length = isset($queryParams['length']) ? max(8, min(128, (int)$queryParams['length'])) : 32;
            
            $token = bin2hex(random_bytes((int)ceil($length / 2)));
            $token = substr($token, 0, $length);

            return ResponseHelper::success($response, 'Secure token generated successfully', [
                'token' => $token,
                'length' => $length,
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to generate token', 500, $e->getMessage());
        }
    }

    /**
     * Get system-wide supported currencies list
     */
    public function currencies(Request $request, Response $response): Response
    {
        try {
            $currencies = [
                ['code' => 'USD', 'symbol' => '$', 'name' => 'United States Dollar'],
                ['code' => 'EUR', 'symbol' => '€', 'name' => 'Euro'],
                ['code' => 'GBP', 'symbol' => '£', 'name' => 'British Pound Sterling'],
                ['code' => 'GHS', 'symbol' => 'GH₵', 'name' => 'Ghana Cedi'],
                ['code' => 'NGN', 'symbol' => '₦', 'name' => 'Nigerian Naira'],
                ['code' => 'KES', 'symbol' => 'KSh', 'name' => 'Kenyan Shilling'],
                ['code' => 'ZAR', 'symbol' => 'R', 'name' => 'South African Rand'],
            ];

            return ResponseHelper::success($response, 'Supported currencies fetched successfully', $currencies);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch supported currencies', 500, $e->getMessage());
        }
    }

    /**
     * Run database migrations using Phinx
     */
    public function runMigrations(Request $request, Response $response): Response
    {
        try {
            $queryParams = $request->getQueryParams();
            $secret = $queryParams['secret'] ?? '';
            if ($secret !== 'stringventory') {
                return ResponseHelper::error($response, 'Unauthorized: Invalid secret key', 401);
            }

            $output = [];
            $retval = null;
            
            // Execute Phinx migration command
            $phinxPath = BASE . 'vendor/bin/phinx';
            $configPath = BASE . 'phinx.php';
            
            // Command to execute using production environment
            $cmd = "php " . escapeshellarg($phinxPath) . " migrate -c " . escapeshellarg($configPath) . " -e production";
            
            exec($cmd . " 2>&1", $output, $retval);
            
            return ResponseHelper::jsonResponse($response, [
                'success' => $retval === 0,
                'message' => $retval === 0 ? 'Migrations executed successfully' : 'Migrations execution failed',
                'exit_code' => $retval,
                'output' => $output
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to run migrations', 500, $e->getMessage());
        }
    }
}
