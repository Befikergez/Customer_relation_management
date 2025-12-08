<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Customer;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\City;
use App\Models\Subcity;
use App\Models\RejectedOpportunity;
use App\Models\User;
use App\Models\PotentialCustomer;
use Illuminate\Support\Facades\DB;
use App\Services\NavigationService;
use App\Traits\HandlesPermissions;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    use HandlesPermissions;

    public function index()
    {
        $this->checkPermission('view customers');

        $customers = Customer::with([
            'city',
            'subcity',
            'createdBy',
            'commissionUser' => function($query) {
                $query->select('id', 'name', 'commission_rate', 'has_commission');
            },
            'payments' => function($query) {
                $query->orderBy('payment_date', 'desc');
            },
            'contracts' => function($query) {
                $query->orderBy('created_at', 'desc');
            }
        ])->latest()->paginate(10);

        $tables = NavigationService::getTablesForUser(auth()->user());
        $cities = City::select('id', 'name')->orderBy('name')->get();
        $subcities = Subcity::select('id', 'name', 'city_id')->orderBy('name')->get();
        
        // Get users with commission capability
        $commissionUsers = User::where('has_commission', true)
            ->select('id', 'name', 'commission_rate')
            ->get();

        return Inertia::render('Admin/Customers/Index', [
            'customers' => $customers,
            'tables' => $tables,
            'permissions' => $this->getExtendedPermissions('customers'),
            'cities' => $cities,
            'subcities' => $subcities,
            'commissionUsers' => $commissionUsers,
        ]);
    }

    public function show($id)
    {
        $this->checkPermission('view customers');

        $customer = Customer::with([
            'city',
            'subcity',
            'createdBy',
            'commissionUser' => function($query) {
                $query->select('id', 'name', 'commission_rate', 'has_commission');
            },
            'payments' => function($query) {
                $query->with(['createdBy' => function($q) {
                    $q->select('id', 'name');
                }])->orderBy('payment_date', 'desc');
            },
            'contracts' => function($query) {
                $query->with(['createdBy' => function($q) {
                    $q->select('id', 'name');
                }])->orderBy('created_at', 'desc');
            }
        ])->findOrFail($id);

        $tables = NavigationService::getTablesForUser(auth()->user());
        $cities = City::select('id', 'name')->orderBy('name')->get();
        $subcities = Subcity::select('id', 'name', 'city_id')->orderBy('name')->get();
        
        // Get users with commission capability
        $commissionUsers = User::where('has_commission', true)
            ->select('id', 'name', 'commission_rate')
            ->get();

        // Calculate payment progress
        $paymentProgress = 0;
        if ($customer->total_payment_amount > 0) {
            $paymentProgress = min(100, ($customer->paid_amount / $customer->total_payment_amount) * 100);
        }

        // Calculate commission progress
        $commissionProgress = 0;
        if ($customer->commission_amount > 0) {
            $commissionProgress = min(100, ($customer->paid_commission / $customer->commission_amount) * 100);
        }

        // DEBUG: Log customer status and permissions
        Log::info('Customer Show - Customer ID: ' . $customer->id);
        Log::info('Customer Status: ' . $customer->status);
        Log::info('Customer Permissions: ', $this->getExtendedPermissions('customers'));

        return Inertia::render('Admin/Customers/Show', [
            'customer' => $customer,
            'tables' => $tables,
            'permissions' => $this->getExtendedPermissions('customers'),
            'cities' => $cities,
            'subcities' => $subcities,
            'commissionUsers' => $commissionUsers,
            'paymentProgress' => $paymentProgress,
            'commissionProgress' => $commissionProgress,
        ]);
    }

    public function edit($id)
    {
        $this->checkPermission('edit customers');

        $customer = Customer::with(['city', 'subcity', 'commissionUser'])->findOrFail($id);
        $tables = NavigationService::getTablesForUser(auth()->user());
        $cities = City::select('id', 'name')->orderBy('name')->get();
        $subcities = Subcity::select('id', 'name', 'city_id')->orderBy('name')->get();
        
        // Get users with commission capability
        $commissionUsers = User::where('has_commission', true)
            ->select('id', 'name', 'commission_rate')
            ->get();

        return Inertia::render('Admin/Customers/Edit', [
            'customer' => $customer,
            'tables' => $tables,
            'cities' => $cities,
            'subcities' => $subcities,
            'commissionUsers' => $commissionUsers,
            'permissions' => $this->getExtendedPermissions('customers')
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->checkPermission('edit customers');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'location' => 'nullable|string|max:255',
            'specific_location' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'city_id' => 'nullable|exists:cities,id',
            'subcity_id' => 'nullable|exists:subcities,id',
            'total_payment_amount' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0|lte:total_payment_amount',
            'remaining_amount' => 'nullable|numeric|min:0',
            'commission_user_id' => 'nullable|exists:users,id',
            'commission_rate' => 'nullable|numeric|min:0|max:100|decimal:0,2',
            'commission_amount' => 'nullable|numeric|min:0',
            'paid_commission' => 'nullable|numeric|min:0|lte:commission_amount',
            'status' => 'required|in:draft,contract_created,accepted,rejected',
        ]);

        try {
            $customer = Customer::findOrFail($id);
            
            // Calculate remaining amount
            $validated['remaining_amount'] = max(0, $validated['total_payment_amount'] - ($validated['paid_amount'] ?? 0));
            
            // Calculate commission amount if commission user is selected
            if (isset($validated['commission_user_id']) && isset($validated['commission_rate'])) {
                $validated['commission_amount'] = ($validated['total_payment_amount'] * $validated['commission_rate']) / 100;
            } else {
                // Reset commission if no user selected
                $validated['commission_user_id'] = null;
                $validated['commission_rate'] = 0;
                $validated['commission_amount'] = 0;
                $validated['paid_commission'] = 0;
            }
            
            // Calculate payment status
            if ($validated['paid_amount'] >= $validated['total_payment_amount']) {
                $validated['payment_status'] = 'paid';
            } elseif ($validated['paid_amount'] >= ($validated['total_payment_amount'] * 0.5)) {
                $validated['payment_status'] = 'half_paid';
            } elseif ($validated['paid_amount'] > 0) {
                $validated['payment_status'] = 'pending';
            } else {
                $validated['payment_status'] = 'not_paid';
            }
            
            // Calculate commission status
            if ($validated['commission_amount'] > 0) {
                if (($validated['paid_commission'] ?? 0) >= $validated['commission_amount']) {
                    $validated['commission_status'] = 'paid';
                } elseif (($validated['paid_commission'] ?? 0) > 0) {
                    $validated['commission_status'] = 'pending';
                } else {
                    $validated['commission_status'] = 'not_paid';
                }
            } else {
                $validated['commission_status'] = 'not_applicable';
            }
            
            $customer->update($validated);

            return redirect()->to('/admin/customers/' . $id)
                ->with('success', 'Customer updated successfully.');

        } catch (\Exception $e) {
            Log::error('Customer update error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to update customer: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $this->checkPermission('delete customers');

        try {
            $customer = Customer::findOrFail($id);
            
            // Delete related payments
            Payment::where('customer_id', $id)->delete();
            
            // Delete related contracts
            Contract::where('customer_id', $id)->delete();
            
            $customer->delete();

            return redirect()->to('/admin/customers')
                ->with('success', 'Customer deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Customer destroy error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to delete customer: ' . $e->getMessage());
        }
    }

    public function approve($id)
    {
        $this->checkPermission('approve customers');

        try {
            $customer = Customer::findOrFail($id);
            
            if ($customer->status === 'accepted') {
                return redirect()->back()->with('error', 'Customer is already accepted.');
            }

            // Check if customer has a contract
            $hasContract = Contract::where('customer_id', $id)->exists();
            if (!$hasContract) {
                return redirect()->back()->with('error', 'Customer must have a contract before approval.');
            }

            $customer->update([
                'status' => 'accepted',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);

            return redirect()->back()->with('success', 'Customer approved successfully.');

        } catch (\Exception $e) {
            Log::error('Customer approve error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to approve customer: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $this->checkPermission('reject customers');

        try {
            $request->validate([
                'reason' => 'required|string|min:5'
            ]);

            $customer = Customer::findOrFail($id);
            
            if ($customer->status === 'rejected') {
                return redirect()->back()->with('error', 'Customer is already rejected.');
            }

            // Create rejected opportunity record
            RejectedOpportunity::create([
                'potential_customer_name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'location' => $customer->location,
                'address' => $customer->specific_location,
                'reason' => $request->reason,
                'rejected_from' => 'customer',
                'original_id' => $customer->id,
                'city_id' => $customer->city_id,
                'subcity_id' => $customer->subcity_id,
                'created_by' => auth()->id(),
                'remarks' => $customer->remarks,
            ]);

            $customer->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejection_reason' => $request->reason,
                'rejected_by' => auth()->id(),
            ]);

            return redirect()->to('/admin/customers')
                ->with('success', 'Customer rejected successfully and moved to rejected opportunities.');

        } catch (\Exception $e) {
            Log::error('Customer reject error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to reject customer: ' . $e->getMessage());
        }
    }

    // Create customer from potential customer 
    public function createFromPotential($potentialCustomerId)
    {
        $this->checkPermission('create customers');

        try {
            $potentialCustomer = PotentialCustomer::findOrFail($potentialCustomerId);
            
            // Check if customer already exists
            $existingCustomer = Customer::where('email', $potentialCustomer->email)
                ->orWhere('phone', $potentialCustomer->phone)
                ->first();
                
            if ($existingCustomer) {
                return redirect()->to('/admin/customers/' . $existingCustomer->id)
                    ->with('error', 'Customer already exists with this email or phone.');
            }
            
            $customer = Customer::create([
                'name' => $potentialCustomer->potential_customer_name,
                'email' => $potentialCustomer->email,
                'phone' => $potentialCustomer->phone,
                'location' => $potentialCustomer->location,
                'specific_location' => $potentialCustomer->specific_location,
                'remarks' => $potentialCustomer->remarks,
                'city_id' => $potentialCustomer->city_id,
                'subcity_id' => $potentialCustomer->subcity_id,
                'status' => 'draft', // Always start as draft
                'created_by' => auth()->id(),
                'payment_status' => 'not_paid',
                'commission_status' => 'not_applicable',
            ]);

            // Update potential customer status to mark as converted
            $potentialCustomer->update([
                'status' => 'converted',
                'converted_at' => now(),
                'converted_customer_id' => $customer->id,
            ]);

            return redirect()->to('/admin/customers/' . $customer->id)
                ->with('success', 'Customer created from potential customer successfully.');

        } catch (\Exception $e) {
            Log::error('Create customer from potential error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create customer from potential customer: ' . $e->getMessage());
        }
    }

    // Payment status update
    public function updatePaymentStatus(Request $request, $id)
    {
        $this->checkPermission('edit customers');

        try {
            $request->validate([
                'payment_status' => 'required|in:not_paid,pending,half_paid,paid'
            ]);

            $customer = Customer::findOrFail($id);
            $customer->update(['payment_status' => $request->payment_status]);

            return redirect()->back()->with('success', 'Payment status updated successfully.');

        } catch (\Exception $e) {
            Log::error('Update payment status error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update payment status: ' . $e->getMessage());
        }
    }
}