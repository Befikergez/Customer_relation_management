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
use Illuminate\Support\Facades\Notification;
use App\Notifications\CustomerStatusNotification;
use App\Notifications\ProposalStatusNotification;

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
                'subcities' => $subcities,
                'csrf_token' => csrf_token(),
            ]);

        } catch (\Exception $e) {
            Log::error('PotentialCustomer index error: ' . $e->getMessage());
            return Inertia::render('Admin/PotentialCustomers/Index', [
                'potentialCustomers' => ['data' => [], 'links' => []],
                'tables' => NavigationService::getTablesForUser(auth()->user()),
                'permissions' => $this->getExtendedPermissions('potential_customers'),
                'cities' => [],
                'subcities' => [],
                'csrf_token' => csrf_token(),
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
                'permissions' => $this->getExtendedPermissions('potential_customers'),
                'csrf_token' => csrf_token(),
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
            // Added total_payment_amount field
            'total_payment_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            $validated['created_by'] = auth()->id();
            
            // If total_payment_amount is provided, use it as the main payment amount
            if (isset($validated['total_payment_amount']) && $validated['total_payment_amount'] > 0) {
                $validated['payment_amount'] = $validated['total_payment_amount'];
            }
            
            $potentialCustomer = PotentialCustomer::create($validated);

            // Send notification for new potential customer creation
            $adminUsers = User::whereIn('role', ['admin', 'manager', 'sales_manager'])->get();
            $salesUsers = User::where('role', 'sales')->get();
            $allRelevantUsers = $adminUsers->merge($salesUsers)->unique('id');
            
            Notification::send(
                $allRelevantUsers,
                new CustomerStatusNotification($potentialCustomer, 'created', auth()->user()->name)
            );

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

            // Calculate total payment amount from all payments
            $totalPaymentAmount = $potentialCustomer->payments->sum('amount');
            
            return Inertia::render('Admin/PotentialCustomers/Show', [
                'potentialCustomer' => $potentialCustomer,
                'tables' => $tables,
                'permissions' => $permissions,
                'cities' => $cities,
                'subcities' => $subcities,
                'payments' => $potentialCustomer->payments,
                'total_payment_amount' => $totalPaymentAmount, // Pass calculated total
                'csrf_token' => csrf_token(),
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
                'permissions' => $this->getExtendedPermissions('potential_customers'),
                'csrf_token' => csrf_token(),
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
            // Added total_payment_amount field
            'total_payment_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            $potentialCustomer = PotentialCustomer::findOrFail($id);
            $oldStatus = $potentialCustomer->status;
            
            // If total_payment_amount is provided, update payment_amount
            if (isset($validated['total_payment_amount']) && $validated['total_payment_amount'] > 0) {
                $validated['payment_amount'] = $validated['total_payment_amount'];
            }
            
            $potentialCustomer->update($validated);

            // Send notification if status changed to accepted
            if ($oldStatus !== 'accepted' && $potentialCustomer->status === 'accepted') {
                $adminUsers = User::whereIn('role', ['admin', 'manager', 'sales_manager'])->get();
                $salesUsers = User::where('role', 'sales')->get();
                $allRelevantUsers = $adminUsers->merge($salesUsers)->unique('id');
                
                Notification::send(
                    $allRelevantUsers,
                    new CustomerStatusNotification($potentialCustomer, 'approved', auth()->user()->name)
                );
            }

            // If customer is accepted, also update customer record
            if ($potentialCustomer->status === 'accepted') {
                $customer = Customer::where('potential_customer_id', $potentialCustomer->id)->first();
                
                if ($customer) {
                    $customer->update([
                        'total_payment_amount' => $validated['total_payment_amount'] ?? $validated['payment_amount'] ?? null,
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
        
        Log::info('=== STARTING APPROVAL PROCESS ===', ['customer_id' => $id]);
        
        try {
            $potentialCustomer = PotentialCustomer::with(['proposals', 'payments'])->findOrFail($id);
            
            Log::info('Potential customer found', [
                'id' => $potentialCustomer->id,
                'name' => $potentialCustomer->potential_customer_name,
                'status' => $potentialCustomer->status,
                'payment_amount' => $potentialCustomer->payment_amount,
                'total_payment_amount' => $potentialCustomer->total_payment_amount ?? 'NOT SET'
            ]);
            
            if (!in_array($potentialCustomer->status, ['draft', 'proposal_sent'])) {
                $errorMessage = 'Customer cannot be approved. Current status: ' . $potentialCustomer->status;
                
                Log::error('Invalid status for approval', ['status' => $potentialCustomer->status]);
                
                return redirect()->back()->with([
                    'message' => $errorMessage,
                    'type' => 'error'
                ]);
            }
            
            DB::beginTransaction();
            
            try {
                Log::info('Step 1: Updating potential customer status');
                
                // 1. Update potential customer status
                $potentialCustomer->update([
                    'status' => 'accepted',
                    'approved_at' => now(),
                    'approved_by' => auth()->id(),
                ]);
                
                Log::info('Step 2: Updating proposals and sending notifications');
                
                // 2. Update all proposals to signed and send notifications
                if ($potentialCustomer->proposals->count() > 0) {
                    foreach ($potentialCustomer->proposals as $proposal) {
                        $oldProposalStatus = $proposal->status;
                        $proposal->update([
                            'status' => 'signed',
                            'is_rejected' => false
                        ]);
                        
                        Log::info('Proposal updated', [
                            'id' => $proposal->id,
                            'old_status' => $oldProposalStatus,
                            'new_status' => 'signed'
                        ]);
                        
                        // Send notification for each proposal approval
                        try {
                            $adminUsers = User::whereIn('role', ['admin', 'manager', 'sales_manager'])->get();
                            Notification::send(
                                $adminUsers,
                                new ProposalStatusNotification($proposal, 'approved', auth()->user()->name)
                            );
                        } catch (\Exception $e) {
                            Log::warning('Failed to send proposal notification: ' . $e->getMessage());
                        }
                    }
                }
                
                Log::info('Step 3: Calculating total payment amount');
                
                // 3. Calculate total payment amount from all payments
                $totalPaymentAmount = $potentialCustomer->payments->sum('amount');
                Log::info('Payment totals', [
                    'payments_count' => $potentialCustomer->payments->count(),
                    'payments_sum' => $totalPaymentAmount,
                    'existing_payment_amount' => $potentialCustomer->payment_amount
                ]);
                
                // If no payments but has payment_amount field, use that
                if ($totalPaymentAmount == 0 && $potentialCustomer->payment_amount > 0) {
                    $totalPaymentAmount = $potentialCustomer->payment_amount;
                    Log::info('Using payment_amount field', ['amount' => $totalPaymentAmount]);
                }
                
                Log::info('Step 4: Creating customer record');
                
                // 4. Prepare customer data
                $customerData = [
                    'name' => $potentialCustomer->potential_customer_name,
                    'email' => $potentialCustomer->email,
                    'phone' => $potentialCustomer->phone,
                    'location' => $potentialCustomer->location,
                    'remarks' => ($potentialCustomer->remarks ?? '') . ' (Converted from potential customer on ' . now()->format('Y-m-d H:i') . ')',
                    'created_by' => auth()->id(),
                    'potential_customer_id' => $potentialCustomer->id,
                    'city_id' => $potentialCustomer->city_id,
                    'subcity_id' => $potentialCustomer->subcity_id,
                    'status' => 'draft',
                    // Set total payment amount
                    'total_payment_amount' => $totalPaymentAmount,
                    'payment_status' => $totalPaymentAmount > 0 ? 'pending' : 'not_paid',
                ];
                
                // Add optional fields if they exist
                $optionalFields = [
                    'address',
                    'specific_location',
                    'map_location',
                    'text_location',
                    'payment_method',
                    'payment_schedule',
                    'payment_date',
                    'payment_remarks',
                    'payment_reference'
                ];
                
                foreach ($optionalFields as $field) {
                    if (!empty($potentialCustomer->$field)) {
                        $customerData[$field] = $potentialCustomer->$field;
                    }
                }
                
                Log::info('Customer data prepared', $customerData);
                
                // Check if customer already exists
                $existingCustomer = Customer::where('email', $potentialCustomer->email)
                    ->orWhere('phone', $potentialCustomer->phone)
                    ->first();
                
                if ($existingCustomer) {
                    Log::info('Updating existing customer', ['customer_id' => $existingCustomer->id]);
                    $existingCustomer->update($customerData);
                    $customer = $existingCustomer;
                } else {
                    Log::info('Creating new customer');
                    try {
                        $customer = Customer::create($customerData);
                        Log::info('Customer created successfully', ['customer_id' => $customer->id]);
                    } catch (\Exception $e) {
                        Log::error('Failed to create customer: ' . $e->getMessage());
                        throw $e;
                    }
                }
                
                Log::info('Step 5: Transferring payments');
                
                // 5. TRANSFER PAYMENTS FROM POTENTIAL CUSTOMER TO CUSTOMER
                if ($potentialCustomer->payments->count() > 0) {
                    Log::info('Transferring payments', ['count' => $potentialCustomer->payments->count()]);
                    foreach ($potentialCustomer->payments as $payment) {
                        try {
                            // Update the existing payment to link to customer
                            $payment->update([
                                'customer_id' => $customer->id,
                                'remarks' => ($payment->remarks ?? '') . ' (Transferred from potential customer)',
                            ]);
                            Log::info('Payment transferred', ['payment_id' => $payment->id]);
                        } catch (\Exception $e) {
                            Log::error('Failed to transfer payment ' . $payment->id . ': ' . $e->getMessage());
                        }
                    }
                } else {
                    Log::info('No payments to transfer');
                }
                
                Log::info('Step 6: Creating payment record if needed');
                
                // 6. Create a payment record if payment data exists but no payments
                if ($potentialCustomer->payment_amount > 0 && $totalPaymentAmount == 0) {
                    try {
                        Log::info('Creating payment from payment_amount field');
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
                    } catch (\Exception $e) {
                        Log::error('Failed to create payment: ' . $e->getMessage());
                    }
                }
                
                Log::info('Step 7: Sending notifications');
                
                // 7. Send notifications to relevant users
                try {
                    $adminUsers = User::whereIn('role', ['admin', 'manager', 'sales_manager'])->get();
                    $salesUsers = User::where('role', 'sales')->get();
                    $allRelevantUsers = $adminUsers->merge($salesUsers)->unique('id');
                    
                    // Send customer conversion notification
                    Notification::send(
                        $allRelevantUsers,
                        new CustomerStatusNotification($customer, 'converted', auth()->user()->name)
                    );
                    Log::info('Notifications sent successfully');
                } catch (\Exception $e) {
                    Log::warning('Failed to send notifications: ' . $e->getMessage());
                    // Don't fail the transaction if notifications fail
                }
                
                DB::commit();
                
                Log::info('=== APPROVAL SUCCESSFUL ===', [
                    'potential_customer_id' => $potentialCustomer->id,
                    'customer_id' => $customer->id
                ]);
                
                $successMessage = 'Customer approved successfully and moved to customers list!';
                
                // Redirect back to show page with success message
                return redirect()->route('admin.potential-customers.show', $id)
                    ->with([
                        'message' => $successMessage,
                        'type' => 'success'
                    ]);
                    
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('PotentialCustomer approve transaction error: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ]);
                
                return redirect()->back()
                    ->with([
                        'message' => 'Failed to approve customer: ' . $e->getMessage(),
                        'type' => 'error'
                    ]);
            }
            
        } catch (\Exception $e) {
            Log::error('PotentialCustomer approve error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            return redirect()->back()
                ->with([
                    'message' => 'Failed to approve customer: ' . $e->getMessage(),
                    'type' => 'error'
                ]);
        }
    }

    public function reject(Request $request, $id)
    {
        $this->checkPermission('reject potential_customers');
        
        Log::info('=== STARTING REJECTION PROCESS ===', ['customer_id' => $id]);
        
        try {
            // Validate reason input
            $validated = $request->validate([
                'reason' => 'required|string|min:5|max:1000'
            ]);
            
            $reason = $validated['reason'];
            
            $potentialCustomer = PotentialCustomer::with('proposals')->findOrFail($id);
            
            Log::info('Potential customer found for rejection', [
                'id' => $potentialCustomer->id,
                'name' => $potentialCustomer->potential_customer_name,
                'status' => $potentialCustomer->status
            ]);
            
            // Check if already rejected
            if ($potentialCustomer->status === 'rejected') {
                return redirect()->back()
                    ->with([
                        'message' => 'Customer is already rejected.',
                        'type' => 'error'
                    ]);
            }
            
            DB::beginTransaction();
            
            try {
                Log::info('Step 1: Updating proposals and sending notifications');
                
                // 1. Update all proposals to rejected and send notifications
                if ($potentialCustomer->proposals->count() > 0) {
                    foreach ($potentialCustomer->proposals as $proposal) {
                        $oldProposalStatus = $proposal->status;
                        $proposal->update([
                            'status' => 'rejected',
                            'is_rejected' => true,
                            'rejected_at' => now(),
                            'rejection_reason' => $reason
                        ]);
                        
                        Log::info('Proposal rejected', [
                            'id' => $proposal->id,
                            'old_status' => $oldProposalStatus,
                            'new_status' => 'rejected'
                        ]);
                        
                        // Send notification for proposal rejection
                        try {
                            $adminUsers = User::whereIn('role', ['admin', 'manager', 'sales_manager'])->get();
                            if (class_exists(ProposalStatusNotification::class)) {
                                Notification::send(
                                    $adminUsers,
                                    new ProposalStatusNotification($proposal, 'rejected', auth()->user()->name)
                                );
                            }
                        } catch (\Exception $e) {
                            Log::warning('Failed to send proposal rejection notification: ' . $e->getMessage());
                        }
                    }
                }
                
                Log::info('Step 2: Creating rejected opportunity record');
                
                // 2. Create rejected opportunity record with safe data extraction
                $rejectedOpportunityData = [
                    'potential_customer_name' => $potentialCustomer->potential_customer_name ?? '',
                    'email' => $potentialCustomer->email ?? null,
                    'phone' => $potentialCustomer->phone ?? null,
                    'location' => $potentialCustomer->location ?? null,
                    'address' => $potentialCustomer->address ?? null,
                    'specific_location' => $potentialCustomer->specific_location ?? null,
                    'map_location' => $potentialCustomer->map_location ?? null,
                    'text_location' => $potentialCustomer->text_location ?? null,
                    'reason' => $reason,
                    'rejected_from' => 'potential_customer',
                    'original_id' => $potentialCustomer->id,
                    'city_id' => $potentialCustomer->city_id ?? null,
                    'subcity_id' => $potentialCustomer->subcity_id ?? null,
                    'created_by' => auth()->id(),
                    'remarks' => $potentialCustomer->remarks ?? null,
                    'rejected_at' => now(),
                    'rejected_by' => auth()->id(),
                ];
                
                // Add payment related fields if they exist
                $paymentFields = [
                    'payment_amount' => $potentialCustomer->payment_amount ?? null,
                    'payment_method' => $potentialCustomer->payment_method ?? null,
                    'payment_schedule' => $potentialCustomer->payment_schedule ?? null,
                    'payment_date' => $potentialCustomer->payment_date ?? null,
                    'payment_remarks' => $potentialCustomer->payment_remarks ?? null,
                    'payment_reference' => $potentialCustomer->payment_reference ?? null,
                ];
                
                foreach ($paymentFields as $field => $value) {
                    if (!empty($value)) {
                        $rejectedOpportunityData[$field] = $value;
                    }
                }
                
                try {
                    $rejectedOpportunity = RejectedOpportunity::create($rejectedOpportunityData);
                    Log::info('Rejected opportunity created', ['id' => $rejectedOpportunity->id]);
                } catch (\Exception $e) {
                    Log::error('Failed to create rejected opportunity: ' . $e->getMessage());
                    // Try with minimal required fields
                    try {
                        $minimalData = [
                            'potential_customer_name' => $potentialCustomer->potential_customer_name ?? 'Unknown Customer',
                            'reason' => $reason,
                            'rejected_from' => 'potential_customer',
                            'original_id' => $potentialCustomer->id,
                            'created_by' => auth()->id(),
                            'rejected_at' => now(),
                            'rejected_by' => auth()->id(),
                        ];
                        $rejectedOpportunity = RejectedOpportunity::create($minimalData);
                        Log::info('Rejected opportunity created with minimal data', ['id' => $rejectedOpportunity->id]);
                    } catch (\Exception $minimalError) {
                        Log::error('Failed to create rejected opportunity even with minimal data: ' . $minimalError->getMessage());
                        // Continue with rejection even if rejected opportunity creation fails
                        $rejectedOpportunity = null;
                    }
                }
                
                Log::info('Step 3: Updating potential customer status to rejected');
                
                // 3. Update potential customer status to rejected
                $potentialCustomer->update([
                    'status' => 'rejected',
                    'rejected_at' => now(),
                    'rejected_by' => auth()->id(),
                    'rejection_reason' => $reason,
                ]);
                
                Log::info('Step 4: Sending notifications');
                
                // 4. Send notification for potential customer rejection
                try {
                    $adminUsers = User::whereIn('role', ['admin', 'manager', 'sales_manager'])->get();
                    $salesUsers = User::where('role', 'sales')->get();
                    $allRelevantUsers = $adminUsers->merge($salesUsers)->unique('id');
                    
                    if (class_exists(CustomerStatusNotification::class)) {
                        Notification::send(
                            $allRelevantUsers,
                            new CustomerStatusNotification($potentialCustomer, 'rejected', auth()->user()->name)
                        );
                        Log::info('Rejection notifications sent successfully');
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to send rejection notifications: ' . $e->getMessage());
                    // Don't fail the transaction if notifications fail
                }
                
                DB::commit();
                
                Log::info('=== REJECTION SUCCESSFUL ===', [
                    'potential_customer_id' => $potentialCustomer->id,
                    'rejected_opportunity_id' => $rejectedOpportunity->id ?? 'N/A'
                ]);
                
                $successMessage = 'Customer rejected successfully and moved to rejected opportunities!';
                
                // Redirect back to show page with success message
                return redirect()->route('admin.potential-customers.show', $id)
                    ->with([
                        'message' => $successMessage,
                        'type' => 'success'
                    ]);
                    
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('PotentialCustomer reject transaction error: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ]);
                
                return redirect()->back()
                    ->with([
                        'message' => 'Failed to reject customer: ' . $e->getMessage(),
                        'type' => 'error'
                    ]);
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('PotentialCustomer reject validation error', ['errors' => $e->errors()]);
            
            return redirect()->back()
                ->with([
                    'message' => 'Validation failed: ' . implode(', ', $e->errors()['reason']),
                    'type' => 'error'
                ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('PotentialCustomer not found for rejection: ' . $e->getMessage());
            
            return redirect()->back()
                ->with([
                    'message' => 'Potential customer not found.',
                    'type' => 'error'
                ]);
            
        } catch (\Exception $e) {
            Log::error('PotentialCustomer reject error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            return redirect()->back()
                ->with([
                    'message' => 'Failed to reject customer: ' . $e->getMessage(),
                    'type' => 'error'
                ]);
        }
    }

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
        
        Log::info('=== STARTING APPROVE WITH PAYMENT PROCESS ===', ['customer_id' => $id]);
        
        try {
            $potentialCustomer = PotentialCustomer::with(['proposals', 'payments'])->findOrFail($id);
            
            Log::info('Potential customer found', [
                'id' => $potentialCustomer->id,
                'name' => $potentialCustomer->potential_customer_name,
                'status' => $potentialCustomer->status
            ]);
            
            if (!in_array($potentialCustomer->status, ['draft', 'proposal_sent'])) {
                return redirect()->back()
                    ->with([
                        'message' => 'Customer cannot be approved. Current status: ' . $potentialCustomer->status,
                        'type' => 'error'
                    ]);
            }
            
            DB::beginTransaction();
            
            try {
                Log::info('Step 1: Updating potential customer status');
                
                // 1. Update potential customer status
                $potentialCustomer->update([
                    'status' => 'accepted',
                    'approved_at' => now(),
                    'approved_by' => auth()->id(),
                ]);
                
                Log::info('Step 2: Updating proposals and sending notifications');
                
                // 2. Update all proposals to signed and send notifications
                if ($potentialCustomer->proposals->count() > 0) {
                    foreach ($potentialCustomer->proposals as $proposal) {
                        $oldProposalStatus = $proposal->status;
                        $proposal->update([
                            'status' => 'signed',
                            'is_rejected' => false
                        ]);
                        
                        Log::info('Proposal updated', [
                            'id' => $proposal->id,
                            'old_status' => $oldProposalStatus,
                            'new_status' => 'signed'
                        ]);
                        
                        // Send notification for proposal approval
                        try {
                            $adminUsers = User::whereIn('role', ['admin', 'manager', 'sales_manager'])->get();
                            Notification::send(
                                $adminUsers,
                                new ProposalStatusNotification($proposal, 'approved', auth()->user()->name)
                            );
                        } catch (\Exception $e) {
                            Log::warning('Failed to send proposal notification: ' . $e->getMessage());
                        }
                    }
                }
                
                Log::info('Step 3: Calculating total payment amount');
                
                // 3. Calculate total payment amount
                $existingPaymentsTotal = $potentialCustomer->payments->sum('amount');
                $newPaymentAmount = $validated['amount'];
                $totalPaymentAmount = $existingPaymentsTotal + $newPaymentAmount;
                
                Log::info('Payment calculation', [
                    'existing_payments' => $existingPaymentsTotal,
                    'new_payment' => $newPaymentAmount,
                    'total' => $totalPaymentAmount
                ]);
                
                Log::info('Step 4: Creating/updating customer record');
                
                // 4. Create customer record - MINIMAL FIELDS ONLY
                $customerData = [
                    'name' => $potentialCustomer->potential_customer_name,
                    'email' => $potentialCustomer->email,
                    'phone' => $potentialCustomer->phone,
                    'location' => $potentialCustomer->location,
                    'remarks' => ($potentialCustomer->remarks ?? '') . ' (Converted from potential customer on ' . now()->format('Y-m-d H:i') . ')',
                    'created_by' => auth()->id(),
                    'potential_customer_id' => $potentialCustomer->id,
                    'city_id' => $potentialCustomer->city_id,
                    'subcity_id' => $potentialCustomer->subcity_id,
                    'status' => 'draft',
                    'salesperson_id' => $validated['salesperson_id'] ?? null,
                    'commission_rate' => $validated['commission_rate'] ?? null,
                    // Set total payment amount
                    'total_payment_amount' => $totalPaymentAmount,
                    'payment_status' => $totalPaymentAmount > 0 ? 'pending' : 'not_paid',
                ];
                
                // Add optional fields if they exist
                if (!empty($potentialCustomer->address)) {
                    $customerData['address'] = $potentialCustomer->address;
                }
                
                if (!empty($potentialCustomer->specific_location)) {
                    $customerData['specific_location'] = $potentialCustomer->specific_location;
                }
                
                if (!empty($potentialCustomer->map_location)) {
                    $customerData['map_location'] = $potentialCustomer->map_location;
                }
                
                if (!empty($potentialCustomer->text_location)) {
                    $customerData['text_location'] = $potentialCustomer->text_location;
                }
                
                // Check if customer already exists
                $existingCustomer = Customer::where('email', $potentialCustomer->email)
                    ->orWhere('phone', $potentialCustomer->phone)
                    ->first();
                
                if ($existingCustomer) {
                    Log::info('Updating existing customer', ['customer_id' => $existingCustomer->id]);
                    $existingCustomer->update($customerData);
                    $customer = $existingCustomer;
                } else {
                    Log::info('Creating new customer');
                    try {
                        $customer = Customer::create($customerData);
                        Log::info('Customer created successfully', ['customer_id' => $customer->id]);
                    } catch (\Exception $e) {
                        Log::error('Failed to create customer: ' . $e->getMessage());
                        throw $e;
                    }
                }
                
                Log::info('Step 5: Calculating commission for new payment');
                
                // 5. Calculate commission for the new payment
                $commissionEarned = 0;
                if (!empty($validated['commission_rate']) && $validated['commission_rate'] > 0) {
                    $commissionEarned = ($validated['amount'] * $validated['commission_rate']) / 100;
                    Log::info('Commission calculated', [
                        'rate' => $validated['commission_rate'],
                        'amount' => $validated['amount'],
                        'commission' => $commissionEarned
                    ]);
                }
                
                Log::info('Step 6: Creating the new payment for customer');
                
                // 6. Create the new payment for customer
                try {
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
                    Log::info('Payment created', ['payment_id' => $customerPayment->id]);
                } catch (\Exception $e) {
                    Log::error('Failed to create payment: ' . $e->getMessage());
                    throw $e;
                }
                
                Log::info('Step 7: Transferring existing payments');
                
                // 7. TRANSFER EXISTING PAYMENTS FROM POTENTIAL CUSTOMER TO CUSTOMER
                if ($potentialCustomer->payments->count() > 0) {
                    Log::info('Transferring existing payments', ['count' => $potentialCustomer->payments->count()]);
                    foreach ($potentialCustomer->payments as $payment) {
                        try {
                            // Update existing payment to link to customer
                            $payment->update([
                                'customer_id' => $customer->id,
                                'remarks' => ($payment->remarks ?? '') . ' (Transferred from potential customer)',
                            ]);
                            Log::info('Payment transferred', ['payment_id' => $payment->id]);
                        } catch (\Exception $e) {
                            Log::error('Failed to transfer payment ' . $payment->id . ': ' . $e->getMessage());
                        }
                    }
                }
                
                Log::info('Step 8: Sending notifications');
                
                // 8. Send notifications
                try {
                    $adminUsers = User::whereIn('role', ['admin', 'manager', 'sales_manager'])->get();
                    $salesUsers = User::where('role', 'sales')->get();
                    $allRelevantUsers = $adminUsers->merge($salesUsers)->unique('id');
                    
                    // Send customer conversion notification
                    Notification::send(
                        $allRelevantUsers,
                        new CustomerStatusNotification($customer, 'converted', auth()->user()->name)
                    );
                    Log::info('Notifications sent successfully');
                } catch (\Exception $e) {
                    Log::warning('Failed to send notifications: ' . $e->getMessage());
                    // Don't fail the transaction if notifications fail
                }
                
                DB::commit();
                
                Log::info('=== APPROVE WITH PAYMENT SUCCESSFUL ===', [
                    'potential_customer_id' => $potentialCustomer->id,
                    'customer_id' => $customer->id,
                    'payment_id' => $customerPayment->id
                ]);
                
                return redirect()->route('admin.potential-customers.show', $id)
                    ->with([
                        'message' => 'Customer approved with payment successfully!',
                        'type' => 'success'
                    ]);
                    
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Approve with payment transaction error: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ]);
                return redirect()->back()
                    ->with([
                        'message' => 'Failed to approve customer with payment: ' . $e->getMessage(),
                        'type' => 'error'
                    ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Approve with payment error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return redirect()->back()
                ->with([
                    'message' => 'Failed to approve customer with payment: ' . $e->getMessage(),
                    'type' => 'error'
                ]);
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

            $proposal = Proposal::create($proposalData);

            if ($potentialCustomer->status === 'draft') {
                $potentialCustomer->update([
                    'status' => 'proposal_sent'
                ]);
            }

            // Send notification for proposal creation
            $adminUsers = User::whereIn('role', ['admin', 'manager', 'sales_manager'])->get();
            $salesUsers = User::where('role', 'sales')->get();
            $allRelevantUsers = $adminUsers->merge($salesUsers)->unique('id');
            
            Notification::send(
                $allRelevantUsers,
                new ProposalStatusNotification($proposal, 'created', auth()->user()->name)
            );

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
            
            $oldStatus = $proposal->status;

            $proposal->update([
                'status' => $request->status,
                'is_rejected' => $request->status === 'rejected',
            ]);

            // Send notification for proposal status update
            $adminUsers = User::whereIn('role', ['admin', 'manager', 'sales_manager'])->get();
            $salesUsers = User::where('role', 'sales')->get();
            $allRelevantUsers = $adminUsers->merge($salesUsers)->unique('id');
            
            $action = match($request->status) {
                'signed' => 'approved',
                'rejected' => 'rejected',
                default => 'updated'
            };
            
            Notification::send(
                $allRelevantUsers,
                new ProposalStatusNotification($proposal, $action, auth()->user()->name)
            );

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

    // Add notification methods
    public function getPotentialCustomerNotifications($id)
    {
        $this->checkPermission('view potential_customers');
        
        try {
            $potentialCustomer = PotentialCustomer::findOrFail($id);
            $notifications = $potentialCustomer->notifications()
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
            Log::error('Get potential customer notifications error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notifications',
                'notifications' => []
            ], 500);
        }
    }

    public function markPotentialCustomerNotificationsAsRead($id)
    {
        $this->checkPermission('view potential_customers');
        
        try {
            $potentialCustomer = PotentialCustomer::findOrFail($id);
            $potentialCustomer->unreadNotifications->markAsRead();
            
            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read.'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Mark potential customer notifications as read error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notifications as read.'
            ], 500);
        }
    }

    public function getPotentialCustomerActivity($id)
    {
        $this->checkPermission('view potential_customers');
        
        try {
            $potentialCustomer = PotentialCustomer::with([
                'proposals' => function($query) {
                    $query->with(['createdBy'])->orderBy('created_at', 'desc');
                },
                'payments' => function($query) {
                    $query->with(['createdBy'])->orderBy('created_at', 'desc');
                }
            ])->findOrFail($id);
            
            $activities = [];
            
            // Add proposal activities
            foreach ($potentialCustomer->proposals as $proposal) {
                $activities[] = [
                    'type' => 'proposal_' . $proposal->status,
                    'message' => "Proposal '{$proposal->title}' was {$proposal->status}",
                    'time' => $proposal->created_at->diffForHumans(),
                    'user' => [
                        'name' => $proposal->createdBy->name ?? 'Unknown',
                        'email' => $proposal->createdBy->email ?? 'unknown@example.com',
                    ],
                    'timestamp' => $proposal->created_at->toISOString(),
                    'url' => "/admin/proposals/{$proposal->id}"
                ];
            }
            
            // Add payment activities
            foreach ($potentialCustomer->payments as $payment) {
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
            
            // Add customer status changes
            if ($potentialCustomer->approved_at) {
                $activities[] = [
                    'type' => 'customer_approved',
                    'message' => "Customer was approved",
                    'time' => $potentialCustomer->approved_at->diffForHumans(),
                    'user' => [
                        'name' => 'System',
                        'email' => 'system@example.com',
                    ],
                    'timestamp' => $potentialCustomer->approved_at->toISOString(),
                ];
            }
            
            if ($potentialCustomer->rejected_at) {
                $activities[] = [
                    'type' => 'customer_rejected',
                    'message' => "Customer was rejected: " . ($potentialCustomer->rejection_reason ?? 'No reason provided'),
                    'time' => $potentialCustomer->rejected_at->diffForHumans(),
                    'user' => [
                        'name' => 'System',
                        'email' => 'system@example.com',
                    ],
                    'timestamp' => $potentialCustomer->rejected_at->toISOString(),
                ];
            }
            
            // Sort activities by timestamp
            usort($activities, function ($a, $b) {
                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
            });
            
            return response()->json([
                'success' => true,
                'activities' => array_slice($activities, 0, 20) // Return only latest 20 activities
            ]);
            
        } catch (\Exception $e) {
            Log::error('Get potential customer activity error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch activity',
                'activities' => []
            ], 500);
        }
    }
}