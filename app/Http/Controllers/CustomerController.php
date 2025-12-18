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
use Illuminate\Support\Facades\Notification;
use App\Notifications\CustomerStatusNotification;
use App\Notifications\ContractStatusNotification;

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
        $paymentProgress = $customer->payment_progress;

        // Calculate commission progress
        $commissionProgress = $customer->commission_progress;

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

        // Debug: Log the incoming request
        Log::debug('=== CUSTOMER UPDATE DEBUG START ===');
        Log::debug('Customer Update Request:', [
            'customer_id' => $id,
            'data' => $request->all(),
            'is_basic_update' => $request->has('is_basic_update'),
            'is_ajax' => $request->ajax(),
            'user' => auth()->user()->id,
        ]);

        // Check if this is a basic info update or full update
        $isBasicUpdate = $request->has('is_basic_update') && $request->is_basic_update === true;
        $isAjaxRequest = $request->ajax() || $request->wantsJson();
        
        if ($isBasicUpdate) {
            Log::debug('Performing basic update');
            // Basic information update (from edit modal)
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'location' => 'nullable|string|max:255',
                'specific_location' => 'nullable|string|max:255',
                'remarks' => 'nullable|string',
                'city_id' => 'nullable|exists:cities,id',
                'subcity_id' => 'nullable|exists:subcities,id',
                'status' => 'required|in:draft,contract_created,accepted,rejected',
            ]);
        } else {
            Log::debug('Performing full update');
            // Full update (from other pages)
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
                'commission_rate' => 'nullable|numeric|min:0|max:100',
                'commission_amount' => 'nullable|numeric|min:0',
                'paid_commission' => 'nullable|numeric|min:0|lte:commission_amount',
                'status' => 'required|in:draft,contract_created,accepted,rejected',
            ]);
        }

        Log::debug('Validation passed:', $validated);

        try {
            $customer = Customer::findOrFail($id);
            $oldStatus = $customer->status;
            
            // Debug: Log before update
            Log::debug('Before Update:', [
                'old_name' => $customer->name,
                'old_location' => $customer->location,
                'old_specific_location' => $customer->specific_location,
                'old_status' => $oldStatus,
            ]);
            
            if (!$isBasicUpdate) {
                // Calculate remaining amount for full update
                $validated['remaining_amount'] = max(0, $validated['total_payment_amount'] - ($validated['paid_amount'] ?? 0));
                
                // Calculate commission amount if commission user is selected
                if (isset($validated['commission_user_id']) && isset($validated['commission_rate'])) {
                    $validated['commission_amount'] = ($validated['total_payment_amount'] * $validated['commission_rate']) / 100;
                } else {
                    // Reset commission if no commission user selected
                    $validated['commission_user_id'] = null;
                    $validated['commission_rate'] = 0;
                    $validated['commission_amount'] = 0;
                    $validated['paid_commission'] = 0;
                }
                
                // Calculate payment status
                $paymentStatus = 'not_paid';
                if ($validated['total_payment_amount'] > 0) {
                    if (($validated['paid_amount'] ?? 0) >= $validated['total_payment_amount']) {
                        $paymentStatus = 'paid';
                    } elseif (($validated['paid_amount'] ?? 0) >= ($validated['total_payment_amount'] * 0.5)) {
                        $paymentStatus = 'half_paid';
                    } elseif (($validated['paid_amount'] ?? 0) > 0) {
                        $paymentStatus = 'pending';
                    }
                }
                $validated['payment_status'] = $paymentStatus;
                
                // Calculate commission status
                $commissionStatus = 'not_applicable';
                if ($validated['commission_amount'] > 0) {
                    if (($validated['paid_commission'] ?? 0) >= $validated['commission_amount']) {
                        $commissionStatus = 'paid';
                    } elseif (($validated['paid_commission'] ?? 0) > 0) {
                        $commissionStatus = 'pending';
                    } else {
                        $commissionStatus = 'not_paid';
                    }
                }
                $validated['commission_status'] = $commissionStatus;
            }
            
            // Debug: Log validated data
            Log::debug('Validated Data for update:', $validated);
            
            // Update the customer
            $customer->update($validated);
            
            // Debug: Log after update
            Log::debug('After Update:', [
                'new_name' => $customer->fresh()->name,
                'new_location' => $customer->fresh()->location,
                'new_specific_location' => $customer->fresh()->specific_location,
                'new_status' => $customer->fresh()->status,
            ]);

            // Send notification if status changed
            if ($oldStatus !== $customer->fresh()->status) {
                Log::debug('Status changed:', [
                    'old' => $oldStatus,
                    'new' => $customer->fresh()->status,
                ]);
                
                $action = match($customer->fresh()->status) {
                    'accepted' => 'approved',
                    'rejected' => 'rejected',
                    default => 'updated'
                };
                
                // Get all users who should receive notifications
                $allRelevantUsers = User::all();
                
                Notification::send(
                    $allRelevantUsers,
                    new CustomerStatusNotification($customer->fresh(), $action, auth()->user()->name)
                );
                
                Log::debug('Notification sent for status change');
            }

            // Check if this is an AJAX request (from modal)
            if ($isAjaxRequest) {
                Log::debug('Returning JSON response for AJAX request');
                return response()->json([
                    'success' => true,
                    'message' => 'Customer information updated successfully!',
                    'customer' => $customer->fresh()->load(['city', 'subcity'])
                ]);
            }

            // For regular form submissions (non-AJAX)
            Log::debug('Redirecting to show page with customer ID: ' . $id);
            return redirect()->route('admin.customers.show', $id)
                ->with('success', 'Customer information updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error: ' . json_encode($e->errors()));
            
            // Check if this is an AJAX request
            if ($isAjaxRequest) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors(),
                    'message' => 'Validation failed. Please check your input.'
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
                
        } catch (\Exception $e) {
            Log::error('Customer update error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Check if this is an AJAX request
            if ($isAjaxRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update customer: ' . $e->getMessage()
                ], 500);
            }
            
            // For errors, redirect back to edit page with error message
            return redirect()->back()
                ->with('error', 'Failed to update customer: ' . $e->getMessage());
        } finally {
            Log::debug('=== CUSTOMER UPDATE DEBUG END ===');
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

            // Send notification for customer approval - REMOVED ROLE FILTERS
            $allRelevantUsers = User::all();
            
            Notification::send(
                $allRelevantUsers,
                new CustomerStatusNotification($customer, 'approved', auth()->user()->name)
            );

            return redirect()->back()->with('success', 'Customer approved successfully.');

        } catch (\Exception $e) {
            Log::error('Customer approve error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to approve customer: ' . $e->getMessage());
        }
    }

    // Show reject form
    public function showRejectForm($id)
    {
        $this->checkPermission('reject customers');

        $customer = Customer::with(['city', 'subcity'])->findOrFail($id);
        $tables = NavigationService::getTablesForUser(auth()->user());

        return Inertia::render('Admin/Customers/RejectForm', [
            'customer' => $customer,
            'tables' => $tables,
            'permissions' => $this->getExtendedPermissions('customers')
        ]);
    }

    // Updated reject method - Returns JSON for Vue component
    public function reject(Request $request, $id)
    {
        $this->checkPermission('reject customers');

        try {
            // Validate the reason
            $validated = $request->validate([
                'reason' => 'required|string|min:5'
            ]);

            $customer = Customer::findOrFail($id);
            
            // Check if already rejected
            if ($customer->status === 'rejected') {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer is already rejected.'
                ], 400);
            }

            DB::beginTransaction();

            try {
                // Create rejected opportunity record
                $rejectedOpportunity = RejectedOpportunity::create([
                    'potential_customer_name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'location' => $customer->location,
                    'address' => $customer->specific_location,
                    'reason' => $validated['reason'],
                    'rejected_from' => 'customer',
                    'original_id' => $customer->id,
                    'city_id' => $customer->city_id,
                    'subcity_id' => $customer->subcity_id,
                    'created_by' => auth()->id(),
                    'remarks' => $customer->remarks,
                ]);

                // Update customer status
                $customer->update([
                    'status' => 'rejected',
                    'rejected_at' => now(),
                    'rejection_reason' => $validated['reason'],
                    'rejected_by' => auth()->id(),
                ]);

                // Send notification for customer rejection - REMOVED ROLE FILTERS
                $allRelevantUsers = User::all();
                
                Notification::send(
                    $allRelevantUsers,
                    new CustomerStatusNotification($customer, 'rejected', auth()->user()->name)
                );

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Customer rejected successfully and moved to rejected opportunities.'
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Validation failed. Please check your input.'
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Customer reject error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject customer: ' . $e->getMessage()
            ], 500);
        }
    }

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
                'status' => 'draft',
                'created_by' => auth()->id(),
                'payment_status' => 'not_paid',
                'commission_status' => 'not_applicable',
                'total_payment_amount' => $potentialCustomer->payment_amount ?? 0,
                'paid_amount' => 0,
                'remaining_amount' => $potentialCustomer->payment_amount ?? 0,
            ]);

            // Update potential customer status
            $potentialCustomer->update([
                'status' => 'converted',
                'converted_at' => now(),
                'converted_customer_id' => $customer->id,
            ]);

            // Send notification for customer creation - REMOVED ROLE FILTERS
            $allRelevantUsers = User::all();
            
            Notification::send(
                $allRelevantUsers,
                new CustomerStatusNotification($customer, 'created', auth()->user()->name)
            );

            return redirect()->to('/admin/customers/' . $customer->id)
                ->with('success', 'Customer created from potential customer successfully.');

        } catch (\Exception $e) {
            Log::error('Create customer from potential error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create customer from potential customer: ' . $e->getMessage());
        }
    }

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

    public function updatePaymentInfo(Request $request, $id)
    {
        $this->checkPermission('edit customers');

        try {
            $request->validate([
                'total_payment_amount' => 'required|numeric|min:0',
                'paid_amount' => 'required|numeric|min:0|lte:total_payment_amount',
                'remaining_amount' => 'nullable|numeric|min:0',
                'payment_status' => 'nullable|in:not_paid,pending,half_paid,paid',
            ]);

            $customer = Customer::with('commissionUser')->findOrFail($id);
            
            // Calculate remaining amount if not provided
            $remainingAmount = $request->remaining_amount ?? max(0, $request->total_payment_amount - $request->paid_amount);
            
            // Update payment status if not provided
            $paymentStatus = $request->payment_status;
            if (!$paymentStatus) {
                $paymentStatus = 'not_paid';
                if ($request->total_payment_amount > 0) {
                    if ($request->paid_amount >= $request->total_payment_amount) {
                        $paymentStatus = 'paid';
                    } elseif ($request->paid_amount >= ($request->total_payment_amount * 0.5)) {
                        $paymentStatus = 'half_paid';
                    } elseif ($request->paid_amount > 0) {
                        $paymentStatus = 'pending';
                    }
                }
            }
            
            $updateData = [
                'total_payment_amount' => $request->total_payment_amount,
                'paid_amount' => $request->paid_amount,
                'remaining_amount' => $remainingAmount,
                'payment_status' => $paymentStatus,
            ];

            $customer->update($updateData);

            // Recalculate commission if commission user exists
            if ($customer->commission_user_id && $customer->commission_rate > 0) {
                $commissionAmount = ($request->total_payment_amount * $customer->commission_rate) / 100;
                
                // Calculate commission status
                $commissionStatus = 'not_applicable';
                if ($commissionAmount > 0) {
                    if ($customer->paid_commission >= $commissionAmount) {
                        $commissionStatus = 'paid';
                    } elseif ($customer->paid_commission > 0) {
                        $commissionStatus = 'pending';
                    } else {
                        $commissionStatus = 'not_paid';
                    }
                }
                
                $customer->update([
                    'commission_amount' => $commissionAmount,
                    'commission_status' => $commissionStatus,
                ]);
            }

            // Calculate payment progress
            $paymentProgress = 0;
            if ($request->total_payment_amount > 0) {
                $paymentProgress = min(100, ($request->paid_amount / $request->total_payment_amount) * 100);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment information updated successfully.',
                'data' => [
                    'total_payment_amount' => $customer->total_payment_amount,
                    'paid_amount' => $customer->paid_amount,
                    'remaining_amount' => $customer->remaining_amount,
                    'payment_status' => $customer->payment_status,
                    'payment_progress' => $paymentProgress,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Update payment info error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment information: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateCommissionInfo(Request $request, $id)
    {
        $this->checkPermission('edit customers');

        try {
            $request->validate([
                'commission_user_id' => 'nullable|exists:users,id',
                'commission_rate' => 'nullable|numeric|min:0|max:100',
                'commission_amount' => 'nullable|numeric|min:0',
                'paid_commission' => 'nullable|numeric|min:0',
                'commission_status' => 'nullable|in:not_applicable,not_paid,pending,paid',
            ]);

            $customer = Customer::findOrFail($id);
            
            $updateData = [];
            
            // If commission user is selected
            if ($request->commission_user_id) {
                // Get the commission user
                $commissionUser = User::find($request->commission_user_id);
                
                // Calculate commission amount if not provided
                $commissionAmount = $request->commission_amount;
                if (!$commissionAmount && $commissionUser && $request->commission_rate) {
                    $commissionAmount = ($customer->total_payment_amount * $request->commission_rate) / 100;
                }
                
                // Calculate commission status if not provided
                $commissionStatus = $request->commission_status;
                if (!$commissionStatus) {
                    $commissionStatus = 'not_applicable';
                    if ($commissionAmount > 0) {
                        if (($request->paid_commission ?? 0) >= $commissionAmount) {
                            $commissionStatus = 'paid';
                        } elseif (($request->paid_commission ?? 0) > 0) {
                            $commissionStatus = 'pending';
                        } else {
                            $commissionStatus = 'not_paid';
                        }
                    }
                }
                
                $updateData = [
                    'commission_user_id' => $request->commission_user_id,
                    'commission_rate' => $request->commission_rate ?? $commissionUser->commission_rate ?? 0,
                    'commission_amount' => $commissionAmount ?? 0,
                    'paid_commission' => $request->paid_commission ?? 0,
                    'commission_status' => $commissionStatus,
                ];
            } else {
                // No commission user selected
                $updateData = [
                    'commission_user_id' => null,
                    'commission_rate' => 0,
                    'commission_amount' => 0,
                    'paid_commission' => 0,
                    'commission_status' => 'not_applicable',
                ];
            }
            
            $customer->update($updateData);

            // Load the updated relationship
            $customer->load('commissionUser');

            // Calculate commission progress
            $commissionProgress = 0;
            if ($customer->commission_amount > 0) {
                $commissionProgress = min(100, ($customer->paid_commission / $customer->commission_amount) * 100);
            }

            return response()->json([
                'success' => true,
                'message' => 'Commission information updated successfully.',
                'data' => [
                    'commissionUser' => $customer->commissionUser,
                    'commission_rate' => $customer->commission_rate,
                    'commission_amount' => $customer->commission_amount,
                    'paid_commission' => $customer->paid_commission,
                    'commission_status' => $customer->commission_status,
                    'commission_progress' => $commissionProgress,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Update commission info error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update commission information: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCommissionUsers()
    {
        $users = User::where('has_commission', true)
            ->select('id', 'name', 'commission_rate')
            ->get();
            
        return response()->json($users);
    }

    public function create()
    {
        $this->checkPermission('create customers');
        
        $tables = NavigationService::getTablesForUser(auth()->user());
        $cities = City::select('id', 'name')->orderBy('name')->get();
        $subcities = Subcity::select('id', 'name', 'city_id')->orderBy('name')->get();
        $commissionUsers = User::where('has_commission', true)
            ->select('id', 'name', 'commission_rate')
            ->get();

        return Inertia::render('Admin/Customers/Create', [
            'tables' => $tables,
            'cities' => $cities,
            'subcities' => $subcities,
            'commissionUsers' => $commissionUsers,
            'permissions' => $this->getExtendedPermissions('customers')
        ]);
    }

    public function store(Request $request)
    {
        $this->checkPermission('create customers');

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
            'commission_user_id' => 'nullable|exists:users,id',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            // Calculate commission amount
            $commissionAmount = 0;
            if (isset($validated['commission_user_id']) && isset($validated['commission_rate'])) {
                $commissionAmount = ($validated['total_payment_amount'] * $validated['commission_rate']) / 100;
            }

            $customer = Customer::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'location' => $validated['location'],
                'specific_location' => $validated['specific_location'],
                'remarks' => $validated['remarks'],
                'city_id' => $validated['city_id'],
                'subcity_id' => $validated['subcity_id'],
                'status' => 'draft',
                'created_by' => auth()->id(),
                'payment_status' => 'not_paid',
                'commission_status' => 'not_applicable',
                'total_payment_amount' => $validated['total_payment_amount'],
                'paid_amount' => 0,
                'remaining_amount' => $validated['total_payment_amount'],
                'commission_user_id' => $validated['commission_user_id'] ?? null,
                'commission_rate' => $validated['commission_rate'] ?? 0,
                'commission_amount' => $commissionAmount,
                'paid_commission' => 0,
            ]);

            // Send notification for customer creation - REMOVED ROLE FILTERS
            $allRelevantUsers = User::all();
            
            Notification::send(
                $allRelevantUsers,
                new CustomerStatusNotification($customer, 'created', auth()->user()->name)
            );

            return redirect()->to('/admin/customers/' . $customer->id)
                ->with('success', 'Customer created successfully.');

        } catch (\Exception $e) {
            Log::error('Customer store error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to create customer: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function createPayment($id)
    {
        $this->checkPermission('create payments');
        
        $customer = Customer::findOrFail($id);
        $tables = NavigationService::getTablesForUser(auth()->user());
        
        return Inertia::render('Admin/Payments/Create', [
            'customer' => $customer,
            'tables' => $tables,
            'permissions' => $this->getExtendedPermissions('payments')
        ]);
    }

    public function updateCommissionStatus(Request $request, $id)
    {
        $this->checkPermission('edit customers');

        try {
            $request->validate([
                'commission_status' => 'required|in:not_applicable,not_paid,pending,paid'
            ]);

            $customer = Customer::findOrFail($id);
            $customer->update(['commission_status' => $request->commission_status]);

            return redirect()->back()->with('success', 'Commission status updated successfully.');

        } catch (\Exception $e) {
            Log::error('Update commission status error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update commission status: ' . $e->getMessage());
        }
    }

    public function getByCity($cityId)
    {
        $this->checkPermission('view customers');
        
        $customers = Customer::where('city_id', $cityId)
            ->with(['city', 'subcity', 'createdBy'])
            ->latest()
            ->paginate(10);
            
        return response()->json($customers);
    }

    public function getBySubcity($subcityId)
    {
        $this->checkPermission('view customers');
        
        $customers = Customer::where('subcity_id', $subcityId)
            ->with(['city', 'subcity', 'createdBy'])
            ->latest()
            ->paginate(10);
            
        return response()->json($customers);
    }

    public function getSubcitiesByCity($cityId)
    {
        $subcities = Subcity::where('city_id', $cityId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
            
        return response()->json($subcities);
    }

    public function getLocationData()
    {
        $cities = City::with(['subcities' => function($query) {
            $query->select('id', 'name', 'city_id');
        }])->select('id', 'name')->get();
        
        return response()->json($cities);
    }

    public function filterByLocation(Request $request)
    {
        $this->checkPermission('view customers');
        
        $query = Customer::query();
        
        if ($request->city_id) {
            $query->where('city_id', $request->city_id);
        }
        
        if ($request->subcity_id) {
            $query->where('subcity_id', $request->subcity_id);
        }
        
        $customers = $query->with(['city', 'subcity', 'createdBy'])
            ->latest()
            ->paginate(10);
            
        return response()->json($customers);
    }

    public function getCustomerProposals($id)
    {
        $this->checkPermission('view proposals');
        
        $proposals = Contract::where('customer_id', $id)
            ->with(['createdBy', 'customer'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json($proposals);
    }

    public function getCustomerNotifications($id)
    {
        $this->checkPermission('view customers');
        
        try {
            $customer = Customer::findOrFail($id);
            $notifications = $customer->notifications()
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'type' => $notification->data['type'] ?? 'info',
                        'message' => $notification->data['message'] ?? 'Notification',
                        'action' => $notification->data['action'] ?? 'created',
                        'actor_name' => $notification->data['actor_name'] ?? 'System',
                        'created_at' => $notification->created_at->diffForHumans(),
                        'is_read' => $notification->read_at !== null,
                    ];
                });
            
            return response()->json([
                'success' => true,
                'notifications' => $notifications
            ]);
            
        } catch (\Exception $e) {
            Log::error('Get customer notifications error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notifications',
                'notifications' => []
            ], 500);
        }
    }

    public function markCustomerNotificationsAsRead($id)
    {
        $this->checkPermission('view customers');
        
        try {
            $customer = Customer::findOrFail($id);
            $customer->unreadNotifications->markAsRead();
            
            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read.'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Mark customer notifications as read error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notifications as read.'
            ], 500);
        }
    }

    public function getCustomerActivity($id)
    {
        $this->checkPermission('view customers');
        
        try {
            $customer = Customer::with([
                'payments' => function($query) {
                    $query->with(['createdBy'])->orderBy('created_at', 'desc');
                },
                'contracts' => function($query) {
                    $query->with(['createdBy'])->orderBy('created_at', 'desc');
                },
                'commissionUser'
            ])->findOrFail($id);
            
            $activities = [];
            
            // Add payment activities
            foreach ($customer->payments as $payment) {
                $activities[] = [
                    'type' => 'payment_created',
                    'message' => "Payment of {$payment->amount} was recorded",
                    'time' => $payment->created_at->diffForHumans(),
                    'user' => [
                        'name' => $payment->createdBy->name ?? 'Unknown',
                        'email' => $payment->createdBy->email ?? 'unknown@example.com',
                    ],
                    'timestamp' => $payment->created_at->toISOString(),
                    'url' => "/admin/payments/{$payment->id}"
                ];
            }
            
            // Add contract activities
            foreach ($customer->contracts as $contract) {
                $activities[] = [
                    'type' => 'contract_' . $contract->status,
                    'message' => "Contract '{$contract->contract_title}' was {$contract->status}",
                    'time' => $contract->created_at->diffForHumans(),
                    'user' => [
                        'name' => $contract->createdBy->name ?? 'Unknown',
                        'email' => $contract->createdBy->email ?? 'unknown@example.com',
                    ],
                    'timestamp' => $contract->created_at->toISOString(),
                    'url' => "/admin/contracts/{$contract->id}"
                ];
            }
            
            // Add customer status changes
            if ($customer->approved_at) {
                $activities[] = [
                    'type' => 'customer_approved',
                    'message' => "Customer was approved",
                    'time' => $customer->approved_at->diffForHumans(),
                    'user' => [
                        'name' => 'System',
                        'email' => 'system@example.com',
                    ],
                    'timestamp' => $customer->approved_at->toISOString(),
                ];
            }
            
            if ($customer->rejected_at) {
                $activities[] = [
                    'type' => 'customer_rejected',
                    'message' => "Customer was rejected: " . ($customer->rejection_reason ?? 'No reason provided'),
                    'time' => $customer->rejected_at->diffForHumans(),
                    'user' => [
                        'name' => 'System',
                        'email' => 'system@example.com',
                    ],
                    'timestamp' => $customer->rejected_at->toISOString(),
                ];
            }
            
            // Add commission activities
            if ($customer->commissionUser) {
                $activities[] = [
                    'type' => 'commission_assigned',
                    'message' => "Commission assigned to {$customer->commissionUser->name} at rate {$customer->commission_rate}%",
                    'time' => $customer->created_at->diffForHumans(),
                    'user' => [
                        'name' => 'System',
                        'email' => 'system@example.com',
                    ],
                    'timestamp' => $customer->created_at->toISOString(),
                ];
            }
            
            // Sort activities by timestamp
            usort($activities, function ($a, $b) {
                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
            });
            
            return response()->json([
                'success' => true,
                'activities' => array_slice($activities, 0, 20)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Get customer activity error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch activity',
                'activities' => []
            ], 500);
        }
    }
}