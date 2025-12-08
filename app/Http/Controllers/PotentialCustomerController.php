<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PotentialCustomer;
use App\Models\Customer;
use App\Models\Proposal;
use App\Models\RejectedOpportunity;
use App\Models\City;
use App\Models\Subcity;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Services\NavigationService;
use App\Traits\HandlesPermissions;
use Illuminate\Support\Facades\Log;

class PotentialCustomerController extends Controller
{
    use HandlesPermissions;

    // Helper method to get full potential customer with all relationships
    private function getFullPotentialCustomer($id)
    {
        return PotentialCustomer::with([
            'createdBy' => function($query) {
                $query->select('id', 'name');
            },
            'city' => function($query) {
                $query->select('id', 'name');
            },
            'subcity' => function($query) {
                $query->select('id', 'name');
            },
            'proposals' => function($query) {
                $query->with(['createdBy' => function($q) {
                    $q->select('id', 'name');
                }])->orderBy('created_at', 'desc');
            },
            'payments' => function($query) {
                $query->with(['createdBy' => function($q) {
                    $q->select('id', 'name');
                }])->orderBy('created_at', 'desc');
            }
        ])->findOrFail($id);
    }

    public function index()
    {
        $this->checkPermission('view potential_customers');

        try {
            $potentialCustomers = PotentialCustomer::with([
                'createdBy' => function($query) {
                    $query->select('id', 'name');
                },
                'city' => function($query) {
                    $query->select('id', 'name');
                },
                'subcity' => function($query) {
                    $query->select('id', 'name');
                },
                'proposals' => function($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'proposals.createdBy',
                'payments' => function($query) {
                    $query->with(['createdBy' => function($q) {
                        $q->select('id', 'name');
                    }])->orderBy('created_at', 'desc');
                }
            ])->latest()->paginate(10);
            
            $tables = NavigationService::getTablesForUser(auth()->user());
            $cities = City::select('id', 'name')->orderBy('name')->get();
            $subcities = Subcity::select('id', 'name', 'city_id')->orderBy('name')->get();

            return Inertia::render('Admin/PotentialCustomers/Index', [
                'potentialCustomers' => $potentialCustomers,
                'tables' => $tables,
                'permissions' => $this->getExtendedPermissions('potential_customers'),
                'cities' => $cities,
                'subcities' => $subcities
            ]);

        } catch (\Exception $e) {
            Log::error('PotentialCustomer index error: ' . $e->getMessage());
            return Inertia::render('Admin/PotentialCustomers/Index', [
                'potentialCustomers' => ['data' => [], 'links' => []],
                'tables' => NavigationService::getTablesForUser(auth()->user()),
                'permissions' => $this->getExtendedPermissions('potential_customers'),
                'cities' => [],
                'subcities' => []
            ]);
        }
    }

    public function create()
    {
        $this->checkPermission('create potential_customers');

        try {
            $tables = NavigationService::getTablesForUser(auth()->user());
            $cities = City::select('id', 'name')->orderBy('name')->get();
            $subcities = Subcity::select('id', 'name', 'city_id')->orderBy('name')->get();

            return Inertia::render('Admin/PotentialCustomers/Create', [
                'tables' => $tables,
                'cities' => $cities,
                'subcities' => $subcities,
                'permissions' => $this->getExtendedPermissions('potential_customers')
            ]);

        } catch (\Exception $e) {
            Log::error('PotentialCustomer create form error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load potential customer creation form.');
        }
    }

    public function store(Request $request)
    {
        $this->checkPermission('create potential_customers');

        $validated = $request->validate([
            'potential_customer_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'location' => 'nullable|string|max:255',
            'specific_location' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'map_location' => 'nullable|string',
            'text_location' => 'nullable|string',
            'remarks' => 'nullable|string',
            'status' => 'required|in:draft,proposal_sent,accepted,rejected',
            'city_id' => 'nullable|exists:cities,id',
            'subcity_id' => 'nullable|exists:subcities,id',
            'payment_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:255',
            'payment_schedule' => 'nullable|string|max:255',
            'payment_date' => 'nullable|date',
            'payment_remarks' => 'nullable|string',
            'payment_reference' => 'nullable|string|max:100',
        ]);

        try {
            $validated['created_by'] = auth()->id();
            
            PotentialCustomer::create($validated);

            return redirect()->route('admin.potential-customers.index')
                ->with([
                    'message' => 'Potential customer created successfully.',
                    'type' => 'success'
                ]);

        } catch (\Exception $e) {
            Log::error('PotentialCustomer store error: ' . $e->getMessage());
            return redirect()->back()
                ->with([
                    'message' => 'Failed to create potential customer. Please try again.',
                    'type' => 'error'
                ])
                ->withInput();
        }
    }

    public function show($id)
    {
        $this->checkPermission('view potential_customers');

        try {
            $potentialCustomer = $this->getFullPotentialCustomer($id);
            
            $tables = NavigationService::getTablesForUser(auth()->user());
            $permissions = $this->getExtendedPermissions('potential_customers');
            $cities = City::select('id', 'name')->orderBy('name')->get();
            $subcities = Subcity::select('id', 'name', 'city_id')->orderBy('name')->get();

            return Inertia::render('Admin/PotentialCustomers/Show', [
                'potentialCustomer' => $potentialCustomer,
                'tables' => $tables,
                'permissions' => $permissions,
                'cities' => $cities,
                'subcities' => $subcities,
                'payments' => $potentialCustomer->payments,
            ]);

        } catch (\Exception $e) {
            Log::error('PotentialCustomer show error: ' . $e->getMessage());
            return redirect()->back()->with([
                'message' => 'Potential customer not found.',
                'type' => 'error'
            ]);
        }
    }

    public function edit($id)
    {
        $this->checkPermission('edit potential_customers');

        try {
            $potentialCustomer = $this->getFullPotentialCustomer($id);
            
            $tables = NavigationService::getTablesForUser(auth()->user());
            $cities = City::select('id', 'name')->orderBy('name')->get();
            $subcities = Subcity::select('id', 'name', 'city_id')->orderBy('name')->get();

            return Inertia::render('Admin/PotentialCustomers/Edit', [
                'potentialCustomer' => $potentialCustomer,
                'tables' => $tables,
                'cities' => $cities,
                'subcities' => $subcities,
                'permissions' => $this->getExtendedPermissions('potential_customers')
            ]);

        } catch (\Exception $e) {
            Log::error('PotentialCustomer edit error: ' . $e->getMessage());
            return redirect('/admin/potential-customers')->with([
                'message' => 'Potential customer not found.',
                'type' => 'error'
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        $this->checkPermission('edit potential_customers');

        $validated = $request->validate([
            'potential_customer_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'location' => 'nullable|string|max:255',
            'specific_location' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'map_location' => 'nullable|string',
            'text_location' => 'nullable|string',
            'remarks' => 'nullable|string',
            'status' => 'required|in:draft,proposal_sent,accepted,rejected',
            'city_id' => 'nullable|exists:cities,id',
            'subcity_id' => 'nullable|exists:subcities,id',
            'payment_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:255',
            'payment_schedule' => 'nullable|string|max:255',
            'payment_date' => 'nullable|date',
            'payment_remarks' => 'nullable|string',
            'payment_reference' => 'nullable|string|max:100',
        ]);

        try {
            $potentialCustomer = PotentialCustomer::findOrFail($id);
            $potentialCustomer->update($validated);

            // If customer is accepted, also update customer record
            if ($potentialCustomer->status === 'accepted') {
                $customer = Customer::where('potential_customer_id', $potentialCustomer->id)->first();
                
                if ($customer) {
                    $customer->update([
                        'payment_amount' => $validated['payment_amount'] ?? null,
                        'payment_method' => $validated['payment_method'] ?? null,
                        'payment_schedule' => $validated['payment_schedule'] ?? null,
                        'payment_date' => $validated['payment_date'] ?? null,
                        'payment_remarks' => $validated['payment_remarks'] ?? null,
                        'payment_reference' => $validated['payment_reference'] ?? null,
                    ]);
                }
            }

            return redirect('/admin/potential-customers')->with([
                'message' => 'Potential customer updated successfully.',
                'type' => 'success'
            ]);

        } catch (\Exception $e) {
            Log::error('PotentialCustomer update error: ' . $e->getMessage());
            return redirect('/admin/potential-customers')->with([
                'message' => 'Failed to update potential customer. Please try again.',
                'type' => 'error'
            ]);
        }
    }

    public function destroy($id)
    {
        $this->checkPermission('delete potential_customers');

        try {
            $potentialCustomer = PotentialCustomer::findOrFail($id);
            
            // Delete related payments first
            Payment::where('potential_customer_id', $id)->delete();
            
            // Delete related proposals
            Proposal::where('potential_customer_id', $id)->delete();
            
            $potentialCustomer->delete();

            return redirect('/admin/potential-customers')->with([
                'message' => 'Potential customer deleted successfully.',
                'type' => 'success'
            ]);

        } catch (\Exception $e) {
            Log::error('PotentialCustomer destroy error: ' . $e->getMessage());
            return redirect('/admin/potential-customers')->with([
                'message' => 'Failed to delete potential customer.',
                'type' => 'error'
            ]);
        }
    }

    public function approve(Request $request, $id)
    {
        $this->checkPermission('approve potential_customers');
        
        try {
            $potentialCustomer = PotentialCustomer::with(['proposals', 'payments'])->findOrFail($id);
            
            if (!in_array($potentialCustomer->status, ['draft', 'proposal_sent'])) {
                return redirect()->route('admin.potential-customers.show', $id)
                    ->with([
                        'message' => 'Customer cannot be approved. Current status: ' . $potentialCustomer->status,
                        'type' => 'error'
                    ]);
            }
            
            DB::beginTransaction();
            
            try {
                // 1. Update potential customer status
                $potentialCustomer->update([
                    'status' => 'accepted',
                    'approved_at' => now(),
                    'approved_by' => auth()->id(),
                ]);
                
                // 2. Update all proposals to signed
                if ($potentialCustomer->proposals->count() > 0) {
                    foreach ($potentialCustomer->proposals as $proposal) {
                        $proposal->update([
                            'status' => 'signed',
                            'is_rejected' => false
                        ]);
                    }
                }
                
                // 3. Create customer record - SET TO DRAFT (FIXED)
                $customerData = [
                    'name' => $potentialCustomer->potential_customer_name,
                    'email' => $potentialCustomer->email,
                    'phone' => $potentialCustomer->phone,
                    'location' => $potentialCustomer->location,
                    'address' => $potentialCustomer->address,
                    'remarks' => ($potentialCustomer->remarks ?? '') . ' (Converted from potential customer on ' . now()->format('Y-m-d H:i') . ')',
                    'created_by' => auth()->id(),
                    'potential_customer_id' => $potentialCustomer->id,
                    'city_id' => $potentialCustomer->city_id,
                    'subcity_id' => $potentialCustomer->subcity_id,
                    'status' => 'draft', // FIXED: Changed from 'accepted' to 'draft'
                    'payment_status' => 'not_paid',
                    'commission_status' => 'not_applicable',
                ];
                
                if ($potentialCustomer->map_location) {
                    $customerData['map_location'] = $potentialCustomer->map_location;
                }
                if ($potentialCustomer->text_location) {
                    $customerData['text_location'] = $potentialCustomer->text_location;
                }
                
                // Check if customer already exists
                $existingCustomer = Customer::where('email', $potentialCustomer->email)->first();
                
                if ($existingCustomer) {
                    $existingCustomer->update($customerData);
                    $customer = $existingCustomer;
                } else {
                    $customer = Customer::create($customerData);
                }
                
                // 4. TRANSFER PAYMENTS FROM POTENTIAL CUSTOMER TO CUSTOMER
                if ($potentialCustomer->payments->count() > 0) {
                    $totalPaymentAmount = 0;
                    $paidAmount = 0;
                    $totalCommission = 0;
                    $paidCommission = 0;
                    
                    foreach ($potentialCustomer->payments as $payment) {
                        // Create a new payment for the customer
                        $customerPayment = Payment::create([
                            'customer_id' => $customer->id,
                            'potential_customer_id' => $payment->potential_customer_id,
                            'amount' => $payment->amount,
                            'method' => $payment->method,
                            'schedule' => $payment->schedule,
                            'payment_date' => $payment->payment_date,
                            'reference_number' => $payment->reference_number,
                            'remarks' => $payment->remarks . ' (Transferred from potential customer)',
                            'created_by' => auth()->id(),
                            'is_active' => $payment->is_active,
                            // Commission fields
                            'commission_earned' => $payment->commission_earned,
                            'commission_paid' => $payment->commission_paid,
                            'commission_payment_date' => $payment->commission_payment_date,
                            'commission_paid_status' => $payment->commission_paid_status,
                        ]);
                        
                        $totalPaymentAmount += $payment->amount;
                        if ($payment->is_active) {
                            $paidAmount += $payment->amount;
                        }
                        
                        $totalCommission += $payment->commission_earned ?? 0;
                        if ($payment->commission_paid_status) {
                            $paidCommission += $payment->commission_paid ?? 0;
                        }
                        
                        // CRITICAL FIX: Update the original payment to link to customer
                        $payment->update(['customer_id' => $customer->id]);
                    }
                    
                    // 5. Update customer payment totals
                    $customer->update([
                        'total_payment_amount' => $totalPaymentAmount,
                        'paid_amount' => $paidAmount,
                        'commission_amount' => $totalCommission,
                        'paid_commission' => $paidCommission,
                    ]);
                    
                    // Update payment status
                    $customer->updatePaymentStatus();
                    $customer->updateCommissionStatus();
                }
                
                // 6. If payment data exists in potential customer fields, also create a payment record
                if ($potentialCustomer->payment_amount > 0) {
                    Payment::create([
                        'customer_id' => $customer->id,
                        'potential_customer_id' => $potentialCustomer->id,
                        'amount' => $potentialCustomer->payment_amount,
                        'method' => $potentialCustomer->payment_method ?? 'Cash',
                        'schedule' => $potentialCustomer->payment_schedule ?? 'One-time',
                        'payment_date' => $potentialCustomer->payment_date ?? now(),
                        'reference_number' => $potentialCustomer->payment_reference,
                        'remarks' => $potentialCustomer->payment_remarks ?? 'Payment from potential customer conversion',
                        'created_by' => auth()->id(),
                        'is_active' => true,
                    ]);
                    
                    // Update customer payment totals
                    $customer->refresh();
                    $newTotal = $customer->total_payment_amount + $potentialCustomer->payment_amount;
                    $newPaid = $customer->paid_amount + $potentialCustomer->payment_amount;
                    
                    $customer->update([
                        'total_payment_amount' => $newTotal,
                        'paid_amount' => $newPaid,
                    ]);
                    
                    $customer->updatePaymentStatus();
                }
                
                // 7. Calculate commission if salesperson exists
                if ($customer->salesperson_id && method_exists($customer, 'calculateCommission')) {
                    $customer->calculateCommission();
                }
                
                DB::commit();
                
                return redirect()->route('admin.potential-customers.show', $id)
                    ->with([
                        'message' => 'Customer approved successfully and moved to customers list! Payments have been transferred.',
                        'type' => 'success'
                    ]);
                    
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('PotentialCustomer approve transaction error: ' . $e->getMessage());
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('PotentialCustomer approve error: ' . $e->getMessage());
            return redirect()->route('admin.potential-customers.show', $id)
                ->with([
                    'message' => 'Failed to approve customer: ' . $e->getMessage(),
                    'type' => 'error'
                ]);
        }
    }

    public function reject(Request $request, $id)
    {
        $this->checkPermission('reject potential_customers');
        
        try {
            $reason = $request->input('reason', 'No reason provided');
            if (empty(trim($reason))) {
                return redirect()->route('admin.potential-customers.show', $id)
                    ->with([
                        'message' => 'Rejection reason is required.',
                        'type' => 'error'
                    ]);
            }
            
            $potentialCustomer = PotentialCustomer::with('proposals')->findOrFail($id);
            
            DB::beginTransaction();
            
            try {
                // 1. Update all proposals to rejected
                if ($potentialCustomer->proposals->count() > 0) {
                    foreach ($potentialCustomer->proposals as $proposal) {
                        $proposal->update([
                            'status' => 'rejected',
                            'is_rejected' => true
                        ]);
                    }
                }
                
                // 2. Create rejected opportunity record
                $rejectedOpportunityData = [
                    'potential_customer_name' => $potentialCustomer->potential_customer_name,
                    'email' => $potentialCustomer->email,
                    'phone' => $potentialCustomer->phone,
                    'location' => $potentialCustomer->location,
                    'address' => $potentialCustomer->address,
                    'reason' => $reason,
                    'rejected_from' => 'potential_customer',
                    'original_id' => $potentialCustomer->id,
                    'city_id' => $potentialCustomer->city_id,
                    'subcity_id' => $potentialCustomer->subcity_id,
                    'created_by' => auth()->id(),
                    'remarks' => $potentialCustomer->remarks,
                ];
                
                RejectedOpportunity::create($rejectedOpportunityData);
                
                // 3. Update potential customer status to rejected
                $potentialCustomer->update([
                    'status' => 'rejected',
                    'rejected_at' => now(),
                    'rejected_by' => auth()->id(),
                    'rejection_reason' => $reason,
                ]);
                
                DB::commit();
                
                return redirect()->route('admin.potential-customers.show', $id)
                    ->with([
                        'message' => 'Customer rejected successfully and moved to rejected opportunities!',
                        'type' => 'success'
                    ]);
                    
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('PotentialCustomer reject transaction error: ' . $e->getMessage());
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('PotentialCustomer reject error: ' . $e->getMessage());
            return redirect()->route('admin.potential-customers.show', $id)
                ->with([
                    'message' => 'Failed to reject customer: ' . $e->getMessage(),
                    'type' => 'error'
                ]);
        }
    }

    // New method to approve with payment in modal
    public function approveWithPayment(Request $request, $id)
    {
        $this->checkPermission('approve potential_customers');
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'method' => 'required|string|max:255',
            'schedule' => 'required|string|max:255',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'remarks' => 'nullable|string',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'salesperson_id' => 'nullable|exists:users,id',
        ]);
        
        try {
            $potentialCustomer = PotentialCustomer::with(['proposals', 'payments'])->findOrFail($id);
            
            if (!in_array($potentialCustomer->status, ['draft', 'proposal_sent'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer cannot be approved. Current status: ' . $potentialCustomer->status
                ], 400);
            }
            
            DB::beginTransaction();
            
            try {
                // 1. Update potential customer status
                $potentialCustomer->update([
                    'status' => 'accepted',
                    'approved_at' => now(),
                    'approved_by' => auth()->id(),
                ]);
                
                // 2. Update all proposals to signed
                if ($potentialCustomer->proposals->count() > 0) {
                    foreach ($potentialCustomer->proposals as $proposal) {
                        $proposal->update([
                            'status' => 'signed',
                            'is_rejected' => false
                        ]);
                    }
                }
                
                // 3. Create customer record - SET TO DRAFT (MUST BE DRAFT)
                $customerData = [
                    'name' => $potentialCustomer->potential_customer_name,
                    'email' => $potentialCustomer->email,
                    'phone' => $potentialCustomer->phone,
                    'location' => $potentialCustomer->location,
                    'specific_location' => $potentialCustomer->specific_location,
                    'address' => $potentialCustomer->address,
                    'remarks' => ($potentialCustomer->remarks ?? '') . ' (Converted from potential customer on ' . now()->format('Y-m-d H:i') . ')',
                    'created_by' => auth()->id(),
                    'potential_customer_id' => $potentialCustomer->id,
                    'city_id' => $potentialCustomer->city_id,
                    'subcity_id' => $potentialCustomer->subcity_id,
                    'status' => 'draft', // CRITICAL: MUST be 'draft' not 'accepted'
                    'payment_status' => 'not_paid',
                    'commission_status' => 'not_applicable',
                    'commission_user_id' => $validated['commission_user_id'] ?? null,
                    'commission_rate' => $validated['commission_rate'] ?? null,
                ];
                
                if ($potentialCustomer->map_location) {
                    $customerData['map_location'] = $potentialCustomer->map_location;
                }
                if ($potentialCustomer->text_location) {
                    $customerData['text_location'] = $potentialCustomer->text_location;
                }
                
                // Check if customer already exists
                $existingCustomer = Customer::where('email', $potentialCustomer->email)->first();
                
                if ($existingCustomer) {
                    $existingCustomer->update($customerData);
                    $customer = $existingCustomer;
                } else {
                    $customer = Customer::create($customerData);
                }
                
                // 4. Calculate commission for the new payment
                $commissionEarned = 0;
                if ($customer->commission_rate > 0) {
                    $commissionEarned = ($validated['amount'] * $customer->commission_rate) / 100;
                }
                
                // 5. Create the new payment for customer
                $customerPayment = Payment::create([
                    'customer_id' => $customer->id,
                    'potential_customer_id' => $potentialCustomer->id,
                    'amount' => $validated['amount'],
                    'method' => $validated['method'],
                    'schedule' => $validated['schedule'],
                    'payment_date' => $validated['payment_date'],
                    'reference_number' => $validated['reference_number'] ?? null,
                    'remarks' => $validated['remarks'] ?? 'Initial payment from approval',
                    'created_by' => auth()->id(),
                    'is_active' => true,
                    'commission_earned' => $commissionEarned,
                ]);
                
                // Also create payment in potential customer for record keeping
                $potentialPayment = Payment::create([
                    'potential_customer_id' => $potentialCustomer->id,
                    'customer_id' => $customer->id,
                    'amount' => $validated['amount'],
                    'method' => $validated['method'],
                    'schedule' => $validated['schedule'],
                    'payment_date' => $validated['payment_date'],
                    'reference_number' => $validated['reference_number'] ?? null,
                    'remarks' => $validated['remarks'] ?? 'Initial payment from approval',
                    'created_by' => auth()->id(),
                    'is_active' => true,
                    'commission_earned' => $commissionEarned,
                ]);
                
                // 6. TRANSFER EXISTING PAYMENTS FROM POTENTIAL CUSTOMER TO CUSTOMER
                if ($potentialCustomer->payments->count() > 0) {
                    $totalPaymentAmount = $validated['amount']; // Start with the new payment
                    $paidAmount = $validated['amount']; // New payment is active
                    $totalCommission = $commissionEarned;
                    $paidCommission = 0;
                    
                    foreach ($potentialCustomer->payments as $payment) {
                        // Skip if this is the payment we just created
                        if ($payment->id === $potentialPayment->id) continue;
                        
                        // Create a new payment for the customer
                        $transferredPayment = Payment::create([
                            'customer_id' => $customer->id,
                            'potential_customer_id' => $payment->potential_customer_id,
                            'amount' => $payment->amount,
                            'method' => $payment->method,
                            'schedule' => $payment->schedule,
                            'payment_date' => $payment->payment_date,
                            'reference_number' => $payment->reference_number,
                            'remarks' => $payment->remarks . ' (Transferred from potential customer)',
                            'created_by' => auth()->id(),
                            'is_active' => $payment->is_active,
                            'commission_earned' => $payment->commission_earned,
                            'commission_paid' => $payment->commission_paid,
                            'commission_payment_date' => $payment->commission_payment_date,
                            'commission_paid_status' => $payment->commission_paid_status,
                        ]);
                        
                        $totalPaymentAmount += $payment->amount;
                        if ($payment->is_active) {
                            $paidAmount += $payment->amount;
                        }
                        
                        $totalCommission += $payment->commission_earned ?? 0;
                        if ($payment->commission_paid_status) {
                            $paidCommission += $payment->commission_paid ?? 0;
                        }
                        
                        // Update original payment to link to customer
                        $payment->update(['customer_id' => $customer->id]);
                    }
                    
                    // Update customer payment totals
                    $customer->update([
                        'total_payment_amount' => $totalPaymentAmount,
                        'paid_amount' => $paidAmount,
                        'commission_amount' => $totalCommission,
                        'paid_commission' => $paidCommission,
                    ]);
                    
                    // Update payment status
                    $customer->updatePaymentStatus();
                    $customer->updateCommissionStatus();
                } else {
                    // No existing payments, just use the new payment
                    $customer->update([
                        'total_payment_amount' => $validated['amount'],
                        'paid_amount' => $validated['amount'],
                        'commission_amount' => $commissionEarned,
                        'paid_commission' => 0,
                    ]);
                    
                    $customer->updatePaymentStatus();
                    $customer->updateCommissionStatus();
                }
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Customer approved with payment successfully!',
                    'customer_id' => $customer->id,
                    'payment_id' => $customerPayment->id,
                ]);
                    
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Approve with payment transaction error: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction error: ' . $e->getMessage()
                ], 500);
            }
            
        } catch (\Exception $e) {
            Log::error('Approve with payment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve customer with payment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function createProposal(Request $request, $id)
    {
        $this->checkPermission('create proposals');

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        try {
            $potentialCustomer = PotentialCustomer::findOrFail($id);

            $proposalData = [
                'potential_customer_id' => $potentialCustomer->id,
                'created_by' => auth()->id(),
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'status' => 'unsigned',
                'is_rejected' => false,
            ];

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('proposals', $fileName, 'public');
                $proposalData['file'] = '/storage/' . $filePath;
            }

            Proposal::create($proposalData);

            if ($potentialCustomer->status === 'draft') {
                $potentialCustomer->update([
                    'status' => 'proposal_sent'
                ]);
            }

            return redirect()->back()->with([
                'message' => 'Proposal created successfully.',
                'type' => 'success'
            ]);

        } catch (\Exception $e) {
            Log::error('Create proposal error: ' . $e->getMessage());
            return redirect()->back()->with([
                'message' => 'Failed to create proposal. Please try again.',
                'type' => 'error'
            ]);
        }
    }

    public function updateProposalStatus(Request $request, $customerId, $proposalId)
    {
        $this->checkPermission('edit proposals');

        $request->validate([
            'status' => 'required|in:signed,rejected,unsigned',
        ]);

        try {
            $proposal = Proposal::where('potential_customer_id', $customerId)
                ->where('id', $proposalId)
                ->firstOrFail();

            $proposal->update([
                'status' => $request->status,
                'is_rejected' => $request->status === 'rejected',
            ]);

            return redirect()->back()->with([
                'message' => 'Proposal status updated successfully.',
                'type' => 'success'
            ]);

        } catch (\Exception $e) {
            Log::error('Update proposal status error: ' . $e->getMessage());
            return redirect()->back()->with([
                'message' => 'Failed to update proposal status.',
                'type' => 'error'
            ]);
        }
    }

    public function getSubcitiesByCity($cityId)
    {
        try {
            $subcities = Subcity::where('city_id', $cityId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'subcities' => $subcities
            ]);

        } catch (\Exception $e) {
            Log::error('Get subcities by city error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subcities',
                'subcities' => []
            ], 500);
        }
    }

    public function getByCity($cityId)
    {
        $this->checkPermission('view potential_customers');
        
        try {
            $potentialCustomers = PotentialCustomer::with(['city', 'subcity', 'proposals'])
                ->where('city_id', $cityId)
                ->latest()
                ->paginate(10);

            return response()->json($potentialCustomers);

        } catch (\Exception $e) {
            Log::error('Get by city error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch potential customers'], 500);
        }
    }

    public function getBySubcity($subcityId)
    {
        $this->checkPermission('view potential_customers');
        
        try {
            $potentialCustomers = PotentialCustomer::with(['city', 'subcity', 'proposals'])
                ->where('subcity_id', $subcityId)
                ->latest()
                ->paginate(10);

            return response()->json($potentialCustomers);

        } catch (\Exception $e) {
            Log::error('Get by subcity error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch potential customers'], 500);
        }
    }
}