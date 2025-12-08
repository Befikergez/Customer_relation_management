<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DebugController extends Controller
{
    public function checkCustomers()
    {
        try {
            Log::info('=== DEBUG: CHECK CUSTOMERS ===');
            
            // 1. Check raw database count
            $rawCount = DB::table('customers')->whereNull('deleted_at')->count();
            Log::info("Raw DB count: {$rawCount}");
            
            // 2. Check Eloquent count
            $eloquentCount = Customer::whereNull('deleted_at')->count();
            Log::info("Eloquent count: {$eloquentCount}");
            
            // 3. Get sample customers
            $sampleCustomers = Customer::whereNull('deleted_at')
                ->limit(5)
                ->get(['id', 'name', 'status', 'email', 'created_at', 'deleted_at']);
            
            Log::info("Sample customers:", $sampleCustomers->toArray());
            
            // 4. Test the exact query from controller
            $controllerQuery = Customer::with([
                'createdBy' => function($query) {
                    $query->select('id', 'name');
                },
                'potentialCustomer' => function($query) {
                    $query->select('id', 'potential_customer_name');
                },
                'city' => function($query) {
                    $query->select('id', 'name');
                },
                'subcity' => function($query) {
                    $query->select('id', 'name');
                },
                'salesperson' => function($query) {
                    $query->select('id', 'name');
                }
            ])
            ->whereNull('deleted_at')
            ->latest()
            ->paginate(10);
            
            Log::info("Pagination info:", [
                'total' => $controllerQuery->total(),
                'current_page' => $controllerQuery->currentPage(),
                'per_page' => $controllerQuery->perPage(),
                'data_count' => count($controllerQuery->items()),
                'first_item_id' => $controllerQuery->items()[0]->id ?? null,
                'first_item_name' => $controllerQuery->items()[0]->name ?? null,
            ]);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'raw_db_count' => $rawCount,
                    'eloquent_count' => $eloquentCount,
                    'sample_customers' => $sampleCustomers,
                    'pagination' => [
                        'total' => $controllerQuery->total(),
                        'current_page' => $controllerQuery->currentPage(),
                        'per_page' => $controllerQuery->perPage(),
                        'data_count' => count($controllerQuery->items()),
                        'first_customer' => $controllerQuery->items()[0] ?? null,
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Debug error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
    
    public function debugCustomers()
    {
        $this->checkPermission('view customers');
        
        try {
            // Get customers using the same logic as the controller
            $customers = Customer::with([
                'createdBy' => function($query) {
                    $query->select('id', 'name');
                },
                'potentialCustomer' => function($query) {
                    $query->select('id', 'potential_customer_name');
                },
                'city' => function($query) {
                    $query->select('id', 'name');
                },
                'subcity' => function($query) {
                    $query->select('id', 'name');
                },
                'salesperson' => function($query) {
                    $query->select('id', 'name');
                }
            ])
            ->whereNull('deleted_at')
            ->latest()
            ->paginate(10);
            
            return Inertia::render('Admin/Debug/Customers', [
                'customers' => $customers,
                'debug_info' => [
                    'total_customers' => $customers->total(),
                    'current_page' => $customers->currentPage(),
                    'per_page' => $customers->perPage(),
                    'data_count' => count($customers->items()),
                    'has_data' => count($customers->items()) > 0,
                    'first_customer' => $customers->items()[0] ?? null,
                ]
            ]);
            
        } catch (\Exception $e) {
            return Inertia::render('Admin/Debug/Customers', [
                'customers' => ['data' => [], 'links' => []],
                'debug_info' => [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]
            ]);
        }
    }
    
    public function customerCount()
    {
        try {
            $count = Customer::whereNull('deleted_at')->count();
            
            return response()->json([
                'success' => true,
                'count' => $count,
                'message' => "Found {$count} customers in database"
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function syncCheck()
    {
        try {
            // Check payments sync
            $customersWithPayments = Customer::whereHas('payments')->count();
            $totalPayments = Payment::count();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'customers_with_payments' => $customersWithPayments,
                    'total_payments' => $totalPayments,
                    'total_customers' => Customer::count(),
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    // Add permission check helper
    private function checkPermission($permission)
    {
        if (!auth()->user()->can($permission)) {
            abort(403, 'Unauthorized action.');
        }
    }
}