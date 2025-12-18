<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\PotentialCustomer;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Services\NavigationService;
use App\Traits\HandlesPermissions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PaymentController extends Controller
{
    use HandlesPermissions;

    // Helper method to get full potential customer with all relationships
    private function getFullPotentialCustomer($customerId)
    {
        return PotentialCustomer::with([
            'city',
            'subcity',
            'createdBy',
            'proposals',
            'payments' => function($query) {
                $query->with(['createdBy' => function($q) {
                    $q->select('id', 'name');
                }])->orderBy('created_at', 'desc');
            }
        ])->findOrFail($customerId);
    }

    // Helper method to get full customer with all relationships
    private function getFullCustomer($customerId)
    {
        return Customer::with([
            'city',
            'subcity',
            'createdBy',
            'salesperson',
            'payments' => function($query) {
                $query->with(['createdBy' => function($q) {
                    $q->select('id', 'name');
                }])->orderBy('created_at', 'desc');
            }
        ])->findOrFail($customerId);
    }

    /**
     * Show payment details (global route)
     */
    public function show($paymentId)
    {
        $this->checkPermission('view payments');
        
        try {
            $payment = Payment::with(['potentialCustomer', 'customer', 'createdBy'])->findOrFail($paymentId);
            $tables = NavigationService::getTablesForUser(auth()->user());
            
            return Inertia::render('Admin/Payments/Show', [
                'payment' => $payment,
                'tables' => $tables,
                'permissions' => $this->getExtendedPermissions('payments'),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Payment show error: ' . $e->getMessage());
            return redirect()->back()->with([
                'message' => 'Failed to load payment details.',
                'type' => 'error'
            ]);
        }
    }

    /**
     * Global edit route for payments (without customer context)
     */
    public function globalEdit($paymentId)
    {
        $this->checkPermission('edit payments');
        
        try {
            $payment = Payment::with(['potentialCustomer', 'customer', 'createdBy'])->findOrFail($paymentId);
            $tables = NavigationService::getTablesForUser(auth()->user());
            
            // Determine which customer this payment belongs to
            $customer = null;
            $customerType = 'global';
            $customerId = null;
            
            if ($payment->customer_id) {
                $customer = $payment->customer;
                $customerType = 'customer';
                $customerId = $payment->customer_id;
            } elseif ($payment->potential_customer_id) {
                $customer = $payment->potentialCustomer;
                $customerType = 'potential';
                $customerId = $payment->potential_customer_id;
            }
            
            return Inertia::render('Admin/Payments/Edit', [
                'payment' => $payment,
                'customer' => $customer,
                'customerType' => $customerType,
                'customerId' => $customerId,
                'tables' => $tables,
                'permissions' => $this->getExtendedPermissions('payments'),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Payment global edit error: ' . $e->getMessage());
            return redirect()->back()->with([
                'message' => 'Failed to load payment edit form.',
                'type' => 'error'
            ]);
        }
    }

    /**
     * Global update route for payments (without customer context)
     */
    public function globalUpdate(Request $request, $paymentId)
    {
        $this->checkPermission('edit payments');
        
        // Increase timeout
        set_time_limit(120);
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'method' => 'required|string|max:255',
            'schedule' => 'required|string|max:255',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'remarks' => 'nullable|string',
        ]);
        
        try {
            DB::beginTransaction();
            
            $payment = Payment::with(['potentialCustomer', 'customer'])->findOrFail($paymentId);
            
            $oldAmount = $payment->amount;
            
            $payment->update([
                'amount' => $validated['amount'],
                'method' => $validated['method'],
                'schedule' => $validated['schedule'],
                'payment_date' => $validated['payment_date'],
                'reference_number' => $validated['reference_number'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
            ]);
            
            // Recalculate commission for this payment
            if ($payment->customer_id) {
                $customer = $payment->customer;
                $commissionRate = $customer->commission_rate ?? 0;
                $commissionEarned = ($validated['amount'] * $commissionRate) / 100;
                
                $payment->update([
                    'commission_earned' => $commissionEarned,
                ]);
                
                // Update customer total payment amount
                $this->updateCustomerTotals($customer);
                
                // Redirect back to potential customer if it exists, otherwise to customer
                if ($payment->potential_customer_id) {
                    $redirectRoute = route('potential-customers.show', $payment->potential_customer_id);
                } else {
                    $redirectRoute = route('customers.show', $payment->customer_id);
                }
            } elseif ($payment->potential_customer_id) {
                $potentialCustomer = $payment->potentialCustomer;
                
                // Update the potential customer's total payment amount
                $totalPaymentAmount = $potentialCustomer->payments()->sum('amount');
                $potentialCustomer->update([
                    'payment_amount' => $totalPaymentAmount,
                ]);
                
                // If this was the main payment, update other fields
                if ($potentialCustomer->payment_amount == $oldAmount) {
                    $updateData = [
                        'payment_method' => $validated['method'],
                        'payment_schedule' => $validated['schedule'],
                        'payment_date' => $validated['payment_date'],
                        'payment_remarks' => $validated['remarks'] ?? null,
                    ];
                    
                    // Only add payment_reference if the column exists
                    if (Schema::hasColumn('potential_customers', 'payment_reference')) {
                        $updateData['payment_reference'] = $validated['reference_number'] ?? null;
                    }
                    
                    $potentialCustomer->update($updateData);
                }
                
                // Redirect back to potential customer
                $redirectRoute = route('potential-customers.show', $payment->potential_customer_id);
            } else {
                // If no customer context, redirect to payments index
                $redirectRoute = route('admin.payments.index');
            }
            
            DB::commit();
            
            return redirect($redirectRoute)
                ->with([
                    'message' => 'Payment updated successfully.',
                    'type' => 'success'
                ]);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment global update error: ' . $e->getMessage());
            return redirect()->back()
                ->with([
                    'message' => 'Failed to update payment.',
                    'type' => 'error'
                ])
                ->withInput();
        }
    }

    /**
     * Global destroy route for payments (without customer context)
     */
    public function globalDestroy($paymentId)
    {
        $this->checkPermission('delete payments');
        
        // Increase timeout
        set_time_limit(120);
        
        try {
            DB::beginTransaction();
            
            $payment = Payment::with(['potentialCustomer', 'customer'])->findOrFail($paymentId);
            
            Log::info('Attempting to delete payment', [
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'customer_id' => $payment->customer_id,
                'potential_customer_id' => $payment->potential_customer_id,
                'deleted_by' => auth()->id()
            ]);
            
            // Store payment details before deletion for logging
            $paymentDetails = [
                'id' => $payment->id,
                'amount' => $payment->amount,
                'customer_id' => $payment->customer_id,
                'potential_customer_id' => $payment->potential_customer_id,
                'method' => $payment->method,
                'payment_date' => $payment->payment_date
            ];
            
            // Determine redirect route - ALWAYS redirect to potential customer if it exists
            if ($payment->potential_customer_id) {
                $redirectRoute = route('potential-customers.show', $payment->potential_customer_id);
                $customerType = 'potential';
            } elseif ($payment->customer_id) {
                $redirectRoute = route('customers.show', $payment->customer_id);
                $customerType = 'customer';
            } else {
                $redirectRoute = route('admin.payments.index');
                $customerType = 'global';
            }
            
            $oldAmount = $payment->amount;
            
            // FORCE DELETE the payment (not soft delete)
            $deleted = $payment->forceDelete();
            
            if (!$deleted) {
                // Try direct DB deletion if forceDelete doesn't work
                $deleted = DB::table('payments')->where('id', $paymentId)->delete() > 0;
            }
            
            if (!$deleted) {
                throw new \Exception('Failed to delete payment from database.');
            }
            
            Log::info('Payment deleted from database', $paymentDetails);
            
            // Update customer totals if applicable
            if ($customerType === 'customer' && $paymentDetails['customer_id']) {
                $customer = Customer::find($paymentDetails['customer_id']);
                if ($customer) {
                    $this->updateCustomerTotals($customer);
                    Log::info('Customer totals updated after payment deletion', [
                        'customer_id' => $customer->id,
                        'deleted_payment_amount' => $oldAmount
                    ]);
                }
            } elseif ($customerType === 'potential' && $paymentDetails['potential_customer_id']) {
                $potentialCustomer = PotentialCustomer::find($paymentDetails['potential_customer_id']);
                
                if ($potentialCustomer) {
                    // Update potential customer's total payment amount
                    $totalPaymentAmount = $potentialCustomer->payments()->sum('amount');
                    $potentialCustomer->update([
                        'payment_amount' => $totalPaymentAmount,
                    ]);
                    
                    // If this was the main payment and no payments left, clear fields
                    if ($potentialCustomer->payment_amount == 0) {
                        $updateData = [
                            'payment_method' => null,
                            'payment_schedule' => null,
                            'payment_date' => null,
                            'payment_remarks' => null,
                        ];
                        
                        // Only clear payment_reference if the column exists
                        if (Schema::hasColumn('potential_customers', 'payment_reference')) {
                            $updateData['payment_reference'] = null;
                        }
                        
                        $potentialCustomer->update($updateData);
                    }
                    
                    Log::info('Potential customer updated after payment deletion', [
                        'potential_customer_id' => $potentialCustomer->id,
                        'new_payment_amount' => $totalPaymentAmount
                    ]);
                }
            }
            
            DB::commit();
            
            Log::info('Payment deletion completed successfully', [
                'payment_id' => $paymentId,
                'redirect_route' => $redirectRoute
            ]);
            
            return redirect($redirectRoute)
                ->with([
                    'message' => 'Payment deleted successfully.',
                    'type' => 'success'
                ]);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment global delete error: ' . $e->getMessage(), [
                'payment_id' => $paymentId,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with([
                'message' => 'Failed to delete payment: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    // For potential customers (index method)
    public function index($customerId)
    {
        $this->checkPermission('view payments');
        
        try {
            $potentialCustomer = $this->getFullPotentialCustomer($customerId);
            $tables = NavigationService::getTablesForUser(auth()->user());
            
            // Calculate total payment amount
            $totalPaymentAmount = $potentialCustomer->payments->sum('amount');
            
            return Inertia::render('Admin/Payments/Index', [
                'potentialCustomer' => $potentialCustomer,
                'customer' => null,
                'payments' => $potentialCustomer->payments,
                'total_payment_amount' => $totalPaymentAmount,
                'tables' => $tables,
                'permissions' => $this->getExtendedPermissions('payments'),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Payment index error: ' . $e->getMessage());
            return redirect()->back()->with([
                'message' => 'Failed to load payments.',
                'type' => 'error'
            ]);
        }
    }

    // For regular customers (customerPayments method)
    public function customerPayments($customerId)
    {
        $this->checkPermission('view payments');
        
        try {
            $customer = $this->getFullCustomer($customerId);
            $tables = NavigationService::getTablesForUser(auth()->user());
            
            return Inertia::render('Admin/Payments/Index', [
                'potentialCustomer' => null,
                'customer' => $customer,
                'payments' => $customer->payments,
                'tables' => $tables,
                'permissions' => $this->getExtendedPermissions('payments'),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Customer Payments index error: ' . $e->getMessage());
            return redirect()->back()->with([
                'message' => 'Failed to load payments.',
                'type' => 'error'
            ]);
        }
    }

    public function create($customerId)
    {
        $this->checkPermission('create payments');
        
        try {
            $potentialCustomer = PotentialCustomer::findOrFail($customerId);
            $tables = NavigationService::getTablesForUser(auth()->user());
            
            return Inertia::render('Admin/Payments/Create', [
                'potentialCustomer' => $potentialCustomer,
                'tables' => $tables,
                'permissions' => $this->getExtendedPermissions('payments'),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Payment create form error: ' . $e->getMessage());
            return redirect()->back()->with([
                'message' => 'Failed to load payment creation form.',
                'type' => 'error'
            ]);
        }
    }

    // Create payment for regular customer
    public function createForCustomer($customerId)
    {
        $this->checkPermission('create payments');
        
        try {
            $customer = Customer::findOrFail($customerId);
            $tables = NavigationService::getTablesForUser(auth()->user());
            
            return Inertia::render('Admin/Customers/Payments/Create', [
                'customer' => $customer,
                'tables' => $tables,
                'permissions' => $this->getExtendedPermissions('payments'),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Customer Payment create form error: ' . $e->getMessage());
            return redirect()->back()->with([
                'message' => 'Failed to load payment creation form.',
                'type' => 'error'
            ]);
        }
    }

    // MAIN FIX: Updated store method to update total_payment_amount instead of paid_amount
    public function store(Request $request, $customerId)
    {
        $this->checkPermission('create payments');
        
        // Increase timeout to handle potential loops
        set_time_limit(120);
        
        // Debug: Log incoming request
        Log::info('Payment store request received', [
            'customer_id' => $customerId,
            'data' => $request->all(),
            'user_id' => auth()->id()
        ]);
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|string|max:255',
            'schedule' => 'required|string|max:255',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'remarks' => 'nullable|string',
            'customer_type' => 'nullable|in:potential,customer',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'salesperson_id' => 'nullable|exists:users,id',
        ]);
        
        try {
            $customerType = $request->customer_type ?? 'potential';
            
            DB::beginTransaction();
            
            if ($customerType === 'potential') {
                $potentialCustomer = PotentialCustomer::findOrFail($customerId);
                
                Log::info('Creating payment for potential customer', [
                    'customer_id' => $customerId,
                    'customer_name' => $potentialCustomer->potential_customer_name,
                    'payment_data' => $validated
                ]);
                
                // Check if this is a duplicate submission within the last 10 seconds
                $recentPayment = Payment::where('potential_customer_id', $potentialCustomer->id)
                    ->where('amount', $validated['amount'])
                    ->where('method', $validated['method'])
                    ->where('schedule', $validated['schedule'])
                    ->where('payment_date', $validated['payment_date'])
                    ->where('created_by', auth()->id())
                    ->where('created_at', '>=', now()->subSeconds(10))
                    ->first();

                if ($recentPayment) {
                    Log::warning('Duplicate payment submission detected', [
                        'potential_customer_id' => $potentialCustomer->id,
                        'existing_payment_id' => $recentPayment->id,
                        'amount' => $validated['amount']
                    ]);
                    
                    DB::rollBack();
                    
                    // Redirect back to the potential customer page with the existing payment
                    return redirect()->route('potential-customers.show', $customerId)
                        ->with([
                            'message' => 'Payment was already created. Redirecting to existing payment.',
                            'type' => 'warning'
                        ]);
                }
                
                // Calculate commission if rate is provided
                $commissionEarned = 0;
                if (!empty($validated['commission_rate'])) {
                    $commissionEarned = ($validated['amount'] * $validated['commission_rate']) / 100;
                }
                
                // Check if customer is accepted and already has a regular customer record
                $isCustomerApproved = $potentialCustomer->status === 'accepted';
                $customerRecord = $isCustomerApproved ? Customer::where('potential_customer_id', $potentialCustomer->id)->first() : null;
                
                // Create ONE payment record - decide whether to link to potential or customer
                $paymentData = [
                    'amount' => $validated['amount'],
                    'method' => $validated['method'],
                    'schedule' => $validated['schedule'],
                    'payment_date' => $validated['payment_date'],
                    'reference_number' => $validated['reference_number'] ?? null,
                    'remarks' => $validated['remarks'] ?? null,
                    'created_by' => auth()->id(),
                    'is_active' => true,
                    'commission_earned' => $commissionEarned,
                ];
                
                // CRITICAL FIX: Only create ONE payment, not two
                // If customer is approved, create payment linked to customer with potential customer reference
                // If not approved, create payment linked only to potential customer
                if ($isCustomerApproved && $customerRecord) {
                    $paymentData['customer_id'] = $customerRecord->id;
                    $paymentData['potential_customer_id'] = $potentialCustomer->id;
                    Log::info('Creating payment for APPROVED customer (customer_id)', [
                        'customer_id' => $customerRecord->id,
                        'potential_customer_id' => $potentialCustomer->id
                    ]);
                } else {
                    $paymentData['potential_customer_id'] = $potentialCustomer->id;
                    Log::info('Creating payment for POTENTIAL customer only', [
                        'potential_customer_id' => $potentialCustomer->id
                    ]);
                }
                
                $payment = Payment::create($paymentData);
                
                Log::info('Payment created successfully', [
                    'payment_id' => $payment->id,
                    'amount' => $payment->amount,
                    'customer_id' => $payment->customer_id,
                    'potential_customer_id' => $payment->potential_customer_id,
                    'is_approved_customer' => $isCustomerApproved
                ]);
                
                // FIX: Update potential customer's total payment amount field
                $updateData = [];
                
                // Update total payment amount (sum of all payments)
                $totalPaymentAmount = $potentialCustomer->payments()->sum('amount') + $payment->amount;
                $updateData['payment_amount'] = $totalPaymentAmount;
                
                // Only update main payment fields if this is the first payment
                if (!$potentialCustomer->payment_method) {
                    $updateData['payment_method'] = $validated['method'];
                }
                if (!$potentialCustomer->payment_schedule) {
                    $updateData['payment_schedule'] = $validated['schedule'];
                }
                if (!$potentialCustomer->payment_date) {
                    $updateData['payment_date'] = $validated['payment_date'];
                }
                if (!$potentialCustomer->payment_remarks) {
                    $updateData['payment_remarks'] = $validated['remarks'] ?? null;
                }
                
                // Only add payment_reference if the column exists
                if (Schema::hasColumn('potential_customers', 'payment_reference') && !$potentialCustomer->payment_reference) {
                    $updateData['payment_reference'] = $validated['reference_number'] ?? null;
                }
                
                if (!empty($updateData)) {
                    $potentialCustomer->update($updateData);
                }
                
                // Update customer totals if payment is linked to a customer
                if ($payment->customer_id) {
                    $customer = Customer::find($payment->customer_id);
                    if ($customer) {
                        // MAIN FIX: Update total_payment_amount instead of paid_amount for new payments
                        $newTotalAmount = $customer->total_payment_amount + $payment->amount;
                        
                        // Update payment status based on paid amount vs total amount
                        $paymentStatus = 'not_paid';
                        if ($newTotalAmount > 0) {
                            // Check if there are existing paid amounts
                            if ($customer->paid_amount >= $newTotalAmount) {
                                $paymentStatus = 'paid';
                            } elseif ($customer->paid_amount >= ($newTotalAmount * 0.5)) {
                                $paymentStatus = 'half_paid';
                            } elseif ($customer->paid_amount > 0) {
                                $paymentStatus = 'pending';
                            }
                        }
                        
                        $customer->update([
                            'total_payment_amount' => $newTotalAmount,
                            'payment_status' => $paymentStatus,
                        ]);
                        
                        Log::info('Customer payment totals updated', [
                            'customer_id' => $customer->id,
                            'new_total_amount' => $newTotalAmount,
                            'payment_status' => $paymentStatus
                        ]);
                    }
                }
                
                DB::commit();
                
                // FIX: Redirect back to the potential customer page
                return redirect()->route('potential-customers.show', $customerId)
                    ->with([
                        'message' => 'Payment created successfully.' . 
                                    ($isCustomerApproved ? ' Payment is linked to customer record.' : ''),
                        'type' => 'success'
                    ]);
                    
            } else {
                // For regular customer (not from potential customer flow)
                $customer = Customer::findOrFail($customerId);
                
                Log::info('Creating payment for regular customer', [
                    'customer_id' => $customerId,
                    'customer_name' => $customer->name,
                    'payment_data' => $validated
                ]);
                
                // Calculate commission if rate is provided or use customer's commission rate
                $commissionRate = $validated['commission_rate'] ?? $customer->commission_rate;
                $commissionEarned = 0;
                if ($commissionRate > 0) {
                    $commissionEarned = ($validated['amount'] * $commissionRate) / 100;
                }
                
                // If salesperson is specified, update customer
                if (!empty($validated['salesperson_id'])) {
                    $customer->update([
                        'salesperson_id' => $validated['salesperson_id'],
                        'commission_rate' => $commissionRate,
                    ]);
                }
                
                $payment = Payment::create([
                    'customer_id' => $customer->id,
                    'potential_customer_id' => $customer->potential_customer_id,
                    'amount' => $validated['amount'],
                    'method' => $validated['method'],
                    'schedule' => $validated['schedule'],
                    'payment_date' => $validated['payment_date'],
                    'reference_number' => $validated['reference_number'] ?? null,
                    'remarks' => $validated['remarks'] ?? null,
                    'created_by' => auth()->id(),
                    'is_active' => true,
                    'commission_earned' => $commissionEarned,
                ]);
                
                Log::info('Payment created for regular customer', [
                    'payment_id' => $payment->id,
                    'amount' => $payment->amount,
                    'customer_id' => $customer->id
                ]);
                
                // FIX: Update customer total payment amount
                $newTotalAmount = $customer->total_payment_amount + $payment->amount;
                
                // Update payment status based on paid amount vs total amount
                $paymentStatus = 'not_paid';
                if ($newTotalAmount > 0) {
                    if ($customer->paid_amount >= $newTotalAmount) {
                        $paymentStatus = 'paid';
                    } elseif ($customer->paid_amount >= ($newTotalAmount * 0.5)) {
                        $paymentStatus = 'half_paid';
                    } elseif ($customer->paid_amount > 0) {
                        $paymentStatus = 'pending';
                    }
                }
                
                $customer->update([
                    'total_payment_amount' => $newTotalAmount,
                    'payment_status' => $paymentStatus,
                ]);
                
                // Update commission if needed
                if ($customer->commission_rate > 0) {
                    $commissionAmount = ($newTotalAmount * $customer->commission_rate) / 100;
                    
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
                
                Log::info('Customer updated after payment', [
                    'customer_id' => $customer->id,
                    'new_total_amount' => $newTotalAmount,
                    'payment_status' => $paymentStatus
                ]);
                
                DB::commit();
                
                return redirect()->route('customers.show', $customerId)
                    ->with([
                        'message' => 'Payment created successfully.',
                        'type' => 'success'
                    ]);
            }
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment store error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'customer_id' => $customerId,
                'data' => $request->all()
            ]);
            return redirect()->back()
                ->with([
                    'message' => 'Failed to create payment: ' . $e->getMessage(),
                    'type' => 'error'
                ])
                ->withInput();
        }
    }

    public function edit($customerId, $paymentId)
    {
        $this->checkPermission('edit payments');
        
        try {
            $payment = Payment::with(['potentialCustomer', 'customer', 'createdBy'])->findOrFail($paymentId);
            $tables = NavigationService::getTablesForUser(auth()->user());
            
            // Determine customer type and get the customer
            $customerType = $payment->customer_id ? 'customer' : 'potential';
            $customer = $customerType === 'potential' 
                ? $payment->potentialCustomer 
                : $payment->customer;
            
            // Always use the same edit component
            return Inertia::render('Admin/Payments/Edit', [
                'payment' => $payment,
                'customer' => $customer,
                'customerType' => $customerType,
                'customerId' => $customerId,
                'tables' => $tables,
                'permissions' => $this->getExtendedPermissions('payments'),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Payment edit error: ' . $e->getMessage());
            return redirect()->back()->with([
                'message' => 'Failed to load payment edit form.',
                'type' => 'error'
            ]);
        }
    }

    public function update(Request $request, $customerId, $paymentId)
    {
        $this->checkPermission('edit payments');
        
        // Increase timeout
        set_time_limit(120);
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'method' => 'required|string|max:255',
            'schedule' => 'required|string|max:255',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'remarks' => 'nullable|string',
            'customer_type' => 'nullable|in:potential,customer',
        ]);
        
        try {
            DB::beginTransaction();
            
            $payment = Payment::findOrFail($paymentId);
            $customerType = $request->customer_type ?? ($payment->customer_id ? 'customer' : 'potential');
            
            $oldAmount = $payment->amount;
            
            $payment->update([
                'amount' => $validated['amount'],
                'method' => $validated['method'],
                'schedule' => $validated['schedule'],
                'payment_date' => $validated['payment_date'],
                'reference_number' => $validated['reference_number'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
            ]);
            
            // Recalculate commission for this payment
            if ($payment->customer_id) {
                $customer = $payment->customer;
                $commissionRate = $customer->commission_rate ?? 0;
                $commissionEarned = ($validated['amount'] * $commissionRate) / 100;
                
                $payment->update([
                    'commission_earned' => $commissionEarned,
                ]);
                
                // Update customer total payment amount
                $this->updateCustomerTotals($customer);
            }
            
            // Update main payment fields if this is a potential customer's payment
            if ($payment->potential_customer_id) {
                $potentialCustomer = $payment->potentialCustomer;
                
                // Update the potential customer's total payment amount
                $totalPaymentAmount = $potentialCustomer->payments()->sum('amount');
                $potentialCustomer->update([
                    'payment_amount' => $totalPaymentAmount,
                ]);
                
                // If this was the main payment, update other fields
                if ($potentialCustomer->payment_amount == $oldAmount) {
                    $updateData = [
                        'payment_method' => $validated['method'],
                        'payment_schedule' => $validated['schedule'],
                        'payment_date' => $validated['payment_date'],
                        'payment_remarks' => $validated['remarks'] ?? null,
                    ];
                    
                    // Only add payment_reference if the column exists
                    if (Schema::hasColumn('potential_customers', 'payment_reference')) {
                        $updateData['payment_reference'] = $validated['reference_number'] ?? null;
                    }
                    
                    $potentialCustomer->update($updateData);
                }
            }
            
            DB::commit();
            
            // FIX: ALWAYS redirect to potential-customers.show when coming from potential customer context
            // Check if the request came from a potential customer route
            $isFromPotentialCustomer = $request->has('customer_type') && $request->customer_type === 'potential';
            
            if ($isFromPotentialCustomer || $payment->potential_customer_id) {
                // Use the potential_customer_id from the payment if available, otherwise use the customerId from the request
                $redirectId = $payment->potential_customer_id ?? $customerId;
                return redirect()->route('potential-customers.show', $redirectId)
                    ->with([
                        'message' => 'Payment updated successfully.',
                        'type' => 'success'
                    ]);
            } else {
                return redirect()->route('customers.show', $customerId)
                    ->with([
                        'message' => 'Payment updated successfully.',
                        'type' => 'success'
                    ]);
            }
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment update error: ' . $e->getMessage());
            return redirect()->back()
                ->with([
                    'message' => 'Failed to update payment.',
                    'type' => 'error'
                ])
                ->withInput();
        }
    }

    public function destroy($customerId, $paymentId)
    {
        $this->checkPermission('delete payments');
        
        // Increase timeout
        set_time_limit(120);
        
        try {
            DB::beginTransaction();
            
            $payment = Payment::with(['potentialCustomer', 'customer'])->findOrFail($paymentId);
            
            Log::info('Attempting to delete payment (with customer context)', [
                'payment_id' => $payment->id,
                'request_customer_id' => $customerId,
                'payment_amount' => $payment->amount,
                'payment_customer_id' => $payment->customer_id,
                'payment_potential_customer_id' => $payment->potential_customer_id
            ]);
            
            // FIX: ALWAYS check if we should redirect to potential customer
            // Priority: 1. Payment has potential_customer_id, 2. Request came from potential customer context
            
            // Check if the request came from a potential customer page
            $isPotentialCustomerRequest = PotentialCustomer::find($customerId) !== null;
            
            // Determine redirect ID
            $redirectId = null;
            $redirectToPotential = false;
            
            if ($payment->potential_customer_id) {
                // Payment has a potential customer ID, always redirect there
                $redirectId = $payment->potential_customer_id;
                $redirectToPotential = true;
                Log::info('Payment has potential_customer_id, redirecting to potential customer', [
                    'potential_customer_id' => $redirectId
                ]);
            } elseif ($isPotentialCustomerRequest) {
                // Request came from potential customer page
                $redirectId = $customerId;
                $redirectToPotential = true;
                Log::info('Request came from potential customer, redirecting to potential customer', [
                    'potential_customer_id' => $redirectId
                ]);
            } else {
                // Default to customer
                $redirectId = $customerId;
                $redirectToPotential = false;
                Log::info('Redirecting to customer', ['customer_id' => $redirectId]);
            }
            
            // Store payment details before deletion
            $paymentDetails = [
                'id' => $payment->id,
                'amount' => $payment->amount
            ];
            
            // FORCE DELETE the payment
            $deleted = $payment->forceDelete();
            
            if (!$deleted) {
                // Try direct DB deletion if forceDelete doesn't work
                $deleted = DB::table('payments')
                    ->where('id', $paymentId)
                    ->delete() > 0;
            }
            
            if (!$deleted) {
                throw new \Exception('Failed to delete payment from database.');
            }
            
            Log::info('Payment deleted from database (with customer context)', $paymentDetails);
            
            if ($payment->customer_id) {
                // Update customer totals if payment was linked to a customer
                $customer = Customer::find($payment->customer_id);
                if ($customer) {
                    $this->updateCustomerTotals($customer);
                }
            }
            
            if ($payment->potential_customer_id) {
                // Update potential customer's total payment amount
                $potentialCustomer = PotentialCustomer::find($payment->potential_customer_id);
                if ($potentialCustomer) {
                    $totalPaymentAmount = $potentialCustomer->payments()->sum('amount');
                    $potentialCustomer->update([
                        'payment_amount' => $totalPaymentAmount,
                    ]);
                    
                    // If this was the main payment and no payments left, clear fields
                    if ($potentialCustomer->payment_amount == 0) {
                        $updateData = [
                            'payment_method' => null,
                            'payment_schedule' => null,
                            'payment_date' => null,
                            'payment_remarks' => null,
                        ];
                        
                        // Only clear payment_reference if the column exists
                        if (Schema::hasColumn('potential_customers', 'payment_reference')) {
                            $updateData['payment_reference'] = null;
                        }
                        
                        $potentialCustomer->update($updateData);
                    }
                }
            }
            
            DB::commit();
            
            // FIX: ALWAYS redirect to potential customer when appropriate
            if ($redirectToPotential) {
                return redirect()->route('potential-customers.show', $redirectId)
                    ->with([
                        'message' => 'Payment deleted successfully.',
                        'type' => 'success'
                    ]);
            } else {
                return redirect()->route('customers.show', $redirectId)
                    ->with([
                        'message' => 'Payment deleted successfully.',
                        'type' => 'success'
                    ]);
            }
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment delete error: ' . $e->getMessage(), [
                'payment_id' => $paymentId,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with([
                'message' => 'Failed to delete payment: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }
    
    /**
     * Update customer payment and commission totals
     */
    private function updateCustomerTotals(Customer $customer): void
    {
        // Set timeout to prevent infinite loops
        set_time_limit(30);
        
        Log::info('Starting updateCustomerTotals', ['customer_id' => $customer->id]);
        
        try {
            // Use direct queries to avoid Eloquent events
            $totalPaymentAmount = DB::table('payments')
                ->where('customer_id', $customer->id)
                ->where('is_active', true)
                ->sum('amount');
            
            $paidAmount = DB::table('payments')
                ->where('customer_id', $customer->id)
                ->where('is_active', true)
                ->where('payment_date', '<=', now())
                ->sum('amount');
            
            $totalCommission = DB::table('payments')
                ->where('customer_id', $customer->id)
                ->where('is_active', true)
                ->sum('commission_earned');
            
            $paidCommission = DB::table('payments')
                ->where('customer_id', $customer->id)
                ->where('is_active', true)
                ->where('commission_paid_status', true)
                ->sum('commission_paid');
            
            Log::info('Calculated totals', [
                'customer_id' => $customer->id,
                'total_payment_amount' => $totalPaymentAmount,
                'paid_amount' => $paidAmount,
                'total_commission' => $totalCommission,
                'paid_commission' => $paidCommission
            ]);
            
            // Get current totals to avoid unnecessary updates
            $currentTotals = DB::table('customers')
                ->where('id', $customer->id)
                ->select('total_payment_amount', 'paid_amount', 'remaining_amount', 
                         'commission_amount', 'paid_commission', 'payment_status', 'commission_status')
                ->first();
            
            // Only update if values have changed
            $updateData = [];
            
            if (abs(($currentTotals->total_payment_amount ?? 0) - $totalPaymentAmount) > 0.01) {
                $updateData['total_payment_amount'] = $totalPaymentAmount;
            }
            
            if (abs(($currentTotals->paid_amount ?? 0) - $paidAmount) > 0.01) {
                $updateData['paid_amount'] = $paidAmount;
            }
            
            // Calculate remaining amount
            $remainingAmount = max(0, $totalPaymentAmount - $paidAmount);
            if (abs(($currentTotals->remaining_amount ?? 0) - $remainingAmount) > 0.01) {
                $updateData['remaining_amount'] = $remainingAmount;
            }
            
            if (abs(($currentTotals->commission_amount ?? 0) - $totalCommission) > 0.01) {
                $updateData['commission_amount'] = $totalCommission;
            }
            
            if (abs(($currentTotals->paid_commission ?? 0) - $paidCommission) > 0.01) {
                $updateData['paid_commission'] = $paidCommission;
            }
            
            // Calculate payment status based on paid amount vs total amount
            $paymentStatus = $this->calculatePaymentStatus($paidAmount, $totalPaymentAmount);
            if ($paymentStatus !== ($currentTotals->payment_status ?? 'not_paid')) {
                $updateData['payment_status'] = $paymentStatus;
            }
            
            // Calculate commission status
            $commissionStatus = $this->calculateCommissionStatus($paidCommission, $totalCommission);
            if ($commissionStatus !== ($currentTotals->commission_status ?? 'not_applicable')) {
                $updateData['commission_status'] = $commissionStatus;
            }
            
            // Only update if there are changes
            if (!empty($updateData)) {
                $updateData['updated_at'] = now();
                
                // Use direct DB update to avoid model events
                DB::table('customers')
                    ->where('id', $customer->id)
                    ->update($updateData);
                
                Log::info('Customer totals updated', [
                    'customer_id' => $customer->id,
                    'updates' => $updateData
                ]);
            } else {
                Log::info('No changes detected for customer totals', ['customer_id' => $customer->id]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error updating customer totals: ' . $e->getMessage(), [
                'customer_id' => $customer->id,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
        
    
    /**
     * Calculate payment status based on paid amount vs total amount
     */
    private function calculatePaymentStatus($paidAmount, $totalAmount): string
    {
        if ($totalAmount <= 0) return 'not_paid';
        if ($paidAmount >= $totalAmount) return 'paid';
        if ($paidAmount >= ($totalAmount * 0.5)) return 'half_paid';
        if ($paidAmount > 0) return 'pending';
        return 'not_paid';
    }
    
    /**
     * Calculate commission status
     */
    private function calculateCommissionStatus($paidCommission, $totalCommission): string
    {
        if ($totalCommission <= 0) return 'not_applicable';
        if ($paidCommission >= $totalCommission) return 'paid';
        if ($paidCommission > 0) return 'pending';
        return 'not_paid';
    }
    
    /**
     * Mark payment commission as paid
     */
    public function markCommissionAsPaid($paymentId)
    {
        $this->checkPermission('edit payments');
        
        try {
            DB::beginTransaction();
            
            $payment = Payment::findOrFail($paymentId);
            
            $payment->update([
                'commission_paid' => $payment->commission_earned,
                'commission_payment_date' => now(),
                'commission_paid_status' => true,
            ]);
            
            // Also update customer commission status
            if ($payment->customer_id) {
                $customer = $payment->customer;
                $this->updateCustomerTotals($customer);
            }
            
            DB::commit();
            
            return redirect()->back()->with('success', 'Commission marked as paid successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Mark commission as paid error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to mark commission as paid: ' . $e->getMessage());
        }
    }
    
    /**
     * Mark payment commission as unpaid
     */
    public function markCommissionAsUnpaid($paymentId)
    {
        $this->checkPermission('edit payments');
        
        try {
            DB::beginTransaction();
            
            $payment = Payment::findOrFail($paymentId);
            
            $payment->update([
                'commission_paid' => 0,
                'commission_payment_date' => null,
                'commission_paid_status' => false,
            ]);
            
            // Also update customer commission status
            if ($payment->customer_id) {
                $customer = $payment->customer;
                $this->updateCustomerTotals($customer);
            }
            
            DB::commit();
            
            return redirect()->back()->with('success', 'Commission marked as unpaid successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Mark commission as unpaid error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to mark commission as unpaid: ' . $e->getMessage());
        }
    }
    
    /**
     * Sync all potential customer payments to customer
     * Useful if payments weren't transferred during approval
     */
    public function syncPaymentsToCustomer($potentialCustomerId)
    {
        $this->checkPermission('edit payments');
        
        try {
            $potentialCustomer = PotentialCustomer::with('payments')->findOrFail($potentialCustomerId);
            
            if ($potentialCustomer->status !== 'accepted') {
                return redirect()->back()->with('error', 'Only accepted potential customers can sync payments.');
            }
            
            $customer = Customer::where('potential_customer_id', $potentialCustomerId)->first();
            
            if (!$customer) {
                return redirect()->back()->with('error', 'No customer found for this potential customer.');
            }
            
            DB::beginTransaction();
            
            $syncedCount = 0;
            $skippedCount = 0;
            $totalAmount = 0;
            
            foreach ($potentialCustomer->payments as $payment) {
                // Check if payment already exists for customer with same details
                $existingPayment = Payment::where('customer_id', $customer->id)
                    ->where('potential_customer_id', $potentialCustomerId)
                    ->where('amount', $payment->amount)
                    ->where('payment_date', $payment->payment_date)
                    ->where('method', $payment->method)
                    ->first();
                
                if (!$existingPayment) {
                    // Create a new payment for the customer
                    Payment::create([
                        'customer_id' => $customer->id,
                        'potential_customer_id' => $potentialCustomerId,
                        'amount' => $payment->amount,
                        'method' => $payment->method,
                        'schedule' => $payment->schedule,
                        'payment_date' => $payment->payment_date,
                        'reference_number' => $payment->reference_number,
                        'remarks' => $payment->remarks . ' (Synced)',
                        'created_by' => auth()->id(),
                        'is_active' => $payment->is_active,
                        'commission_earned' => $payment->commission_earned,
                        'commission_paid' => $payment->commission_paid,
                        'commission_payment_date' => $payment->commission_payment_date,
                        'commission_paid_status' => $payment->commission_paid_status,
                    ]);
                    
                    $totalAmount += $payment->amount;
                    $syncedCount++;
                } else {
                    $skippedCount++;
                }
            }
            
            // Update customer's total payment amount
            if ($syncedCount > 0) {
                $customer->update([
                    'total_payment_amount' => $customer->total_payment_amount + $totalAmount
                ]);
            }
            
            // Update customer totals
            $this->updateCustomerTotals($customer);
            
            DB::commit();
            
            if ($syncedCount > 0) {
                return redirect()->back()->with('success', "Successfully synced {$syncedCount} payments to customer. {$skippedCount} payments already existed.");
            } else {
                return redirect()->back()->with('info', "All payments were already synced. No new payments were created.");
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Sync payments error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to sync payments: ' . $e->getMessage());
        }
    }
    
    // Additional global payment methods
    
    public function recent()
    {
        $this->checkPermission('view payments');
        
        try {
            $payments = Payment::with(['potentialCustomer', 'customer', 'createdBy'])
                ->where('payment_date', '>=', now()->subDays(30))
                ->orderBy('payment_date', 'desc')
                ->get();
            
            $tables = NavigationService::getTablesForUser(auth()->user());
            
            return Inertia::render('Admin/Payments/Recent', [
                'payments' => $payments,
                'tables' => $tables,
                'permissions' => $this->getExtendedPermissions('payments'),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Recent payments error: ' . $e->getMessage());
            return redirect()->back()->with([
                'message' => 'Failed to load recent payments.',
                'type' => 'error'
            ]);
        }
    }
    
    public function upcoming()
    {
        $this->checkPermission('view payments');
        
        try {
            $payments = Payment::with(['potentialCustomer', 'customer', 'createdBy'])
                ->where('payment_date', '>=', now())
                ->where('payment_date', '<=', now()->addDays(30))
                ->orderBy('payment_date', 'asc')
                ->get();
            
            $tables = NavigationService::getTablesForUser(auth()->user());
            
            return Inertia::render('Admin/Payments/Upcoming', [
                'payments' => $payments,
                'tables' => $tables,
                'permissions' => $this->getExtendedPermissions('payments'),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Upcoming payments error: ' . $e->getMessage());
            return redirect()->back()->with([
                'message' => 'Failed to load upcoming payments.',
                'type' => 'error'
            ]);
        }
    }
    
    public function overdue()
    {
        $this->checkPermission('view payments');
        
        try {
            $payments = Payment::with(['potentialCustomer', 'customer', 'createdBy'])
                ->where('payment_date', '<', now())
                ->orderBy('payment_date', 'desc')
                ->get();
            
            $tables = NavigationService::getTablesForUser(auth()->user());
            
            return Inertia::render('Admin/Payments/Overdue', [
                'payments' => $payments,
                'tables' => $tables,
                'permissions' => $this->getExtendedPermissions('payments'),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Overdue payments error: ' . $e->getMessage());
            return redirect()->back()->with([
                'message' => 'Failed to load overdue payments.',
                'type' => 'error'
            ]);
        }
    }
    
    public function summary()
    {
        $this->checkPermission('view payments');
        
        try {
            $totalPayments = Payment::count();
            $totalAmount = Payment::sum('amount');
            $recentPayments = Payment::with(['potentialCustomer', 'customer'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            $tables = NavigationService::getTablesForUser(auth()->user());
            
            return Inertia::render('Admin/Payments/Summary', [
                'totalPayments' => $totalPayments,
                'totalAmount' => $totalAmount,
                'recentPayments' => $recentPayments,
                'tables' => $tables,
                'permissions' => $this->getExtendedPermissions('payments'),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Payments summary error: ' . $e->getMessage());
            return redirect()->back()->with([
                'message' => 'Failed to load payments summary.',
                'type' => 'error'
            ]);
        }
    }
}