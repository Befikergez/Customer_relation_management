<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Customer;
use App\Models\RejectedOpportunity;
use Illuminate\Support\Facades\DB; // Added this import
use App\Services\NavigationService;
use App\Traits\HandlesPermissions;

class CustomerController extends Controller
{
    use HandlesPermissions;

    public function index()
    {
        $this->checkPermission('view customers');

        try {
            $customers = Customer::latest()->paginate(10);
            $tables = NavigationService::getTablesForUser(auth()->user());

            \Log::info('Customers Loaded', [
                'count' => $customers->count(),
                'total' => $customers->total()
            ]);

            return Inertia::render('Admin/Customers/Index', [
                'customers' => $customers,
                'tables' => $tables,
                'permissions' => $this->getExtendedPermissions('customers')
            ]);

        } catch (\Exception $e) {
            \Log::error('Customers Index Error: ' . $e->getMessage());
            
            return Inertia::render('Admin/Customers/Index', [
                'customers' => ['data' => [], 'links' => []],
                'tables' => NavigationService::getTablesForUser(auth()->user()),
                'permissions' => $this->getExtendedPermissions('customers')
            ]);
        }
    }

    public function create()
    {
        $this->checkPermission('create customers');

        $tables = NavigationService::getTablesForUser(auth()->user());

        return Inertia::render('Admin/Customers/Create', [
            'tables' => $tables,
            'permissions' => $this->getExtendedPermissions('customers')
        ]);
    }

    public function store(Request $request)
    {
        $this->checkPermission('create customers');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'location' => 'nullable|url|max:500',
            'remarks' => 'nullable|string',
        ]);

        try {
            Customer::create($validated);

            return redirect()->route('customers.index')
                ->with('success', 'Customer created successfully.');

        } catch (\Exception $e) {
            \Log::error('Customer Store Error: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to create customer. Please try again.')
                ->withInput();
        }
    }

    public function show($id)
    {
        $this->checkPermission('view customers');

        try {
            $customer = Customer::findOrFail($id);
            $tables = NavigationService::getTablesForUser(auth()->user());

            return Inertia::render('Admin/Customers/Show', [
                'customer' => $customer,
                'tables' => $tables,
                'permissions' => $this->getExtendedPermissions('customers')
            ]);

        } catch (\Exception $e) {
            \Log::error('Customer Show Error: ' . $e->getMessage());
            
            return redirect()->route('customers.index')
                ->with('error', 'Customer not found.');
        }
    }

    public function edit($id)
    {
        $this->checkPermission('edit customers');

        try {
            $customer = Customer::findOrFail($id);
            $tables = NavigationService::getTablesForUser(auth()->user());

            return Inertia::render('Admin/Customers/Edit', [
                'customer' => $customer,
                'tables' => $tables,
                'permissions' => $this->getExtendedPermissions('customers')
            ]);

        } catch (\Exception $e) {
            \Log::error('Customer Edit Error: ' . $e->getMessage());
            
            return redirect()->route('customers.index')
                ->with('error', 'Customer not found.');
        }
    }

    public function update(Request $request, $id)
    {
        $this->checkPermission('edit customers');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'location' => 'nullable|url|max:500',
            'remarks' => 'nullable|string',
        ]);

        try {
            $customer = Customer::findOrFail($id);
            $customer->update($validated);

            return redirect()->route('customers.index')
                ->with('success', 'Customer updated successfully.');

        } catch (\Exception $e) {
            \Log::error('Customer Update Error: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to update customer. Please try again.')
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $this->checkPermission('delete customers');

        try {
            $customer = Customer::findOrFail($id);
            $customer->delete();

            return redirect()->back()->with('success', 'Customer deleted successfully.');

        } catch (\Exception $e) {
            \Log::error('Customer Delete Error: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Failed to delete customer. Please try again.');
        }
    }

    /**
     * Reject customer - move to rejected opportunities with reason
     */
    public function reject(Request $request, $id)
{
    $this->checkPermission('reject customers');

    $request->validate([
        'reason' => 'required|string|max:1000',
    ]);

    try {
        $customer = Customer::findOrFail($id);

        DB::transaction(function () use ($customer, $request) {
            // Create rejected opportunity WITH original_id
            RejectedOpportunity::create([
                'opportunity_id' => null,
                'potential_customer_name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'location' => $customer->location,
                'created_by' => auth()->id(),
                'remarks' => $customer->remarks,
                'reason' => $request->reason,
                'rejected_from' => 'customer',
                'original_id' => $customer->id, // Add this
            ]);

            // Delete the customer
            $customer->delete();
        });

        return redirect()->back()->with('success', 'Customer rejected and moved to rejected opportunities.');

    } catch (\Exception $e) {
        \Log::error('Customer Reject Error: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Failed to reject customer.');
    }
}

    /**
     * Get customer statistics
     */
    public function getStats()
    {
        try {
            $totalCustomers = Customer::count();
            $todayCustomers = Customer::whereDate('created_at', today())->count();
            $thisWeekCustomers = Customer::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
            $thisMonthCustomers = Customer::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $totalCustomers,
                    'today' => $todayCustomers,
                    'this_week' => $thisWeekCustomers,
                    'this_month' => $thisMonthCustomers
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Customer Stats Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'data' => [
                    'total' => 0,
                    'today' => 0,
                    'this_week' => 0,
                    'this_month' => 0
                ]
            ]);
        }
    }
}