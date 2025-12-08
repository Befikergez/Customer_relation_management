<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\Proposal;
use App\Services\NavigationService;
use App\Traits\HandlesPermissions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContractController extends Controller
{
    use HandlesPermissions;

    public function index()
    {
        $this->checkPermission('view contracts');

        try {
            $contracts = Contract::with([
                'customer' => function($query) {
                    $query->select('id', 'name', 'email', 'phone', 'status');
                },
                'proposal' => function($query) {
                    $query->select('id', 'title', 'price', 'potential_customer_id')
                          ->with(['potentialCustomer' => function($q) {
                              $q->select('id', 'potential_customer_name');
                          }]);
                },
                'createdBy' => function($query) {
                    $query->select('id', 'name');
                }
            ])->latest()->paginate(10);
            
            $tables = NavigationService::getTablesForUser(auth()->user());

            return Inertia::render('Admin/Contracts/Index', [
                'contracts' => $contracts,
                'tables' => $tables,
                'permissions' => $this->getExtendedPermissions('contracts')
            ]);

        } catch (\Exception $e) {
            Log::error('Error in ContractController@index: ' . $e->getMessage());
            return Inertia::render('Admin/Contracts/Index', [
                'contracts' => ['data' => [], 'links' => []],
                'tables' => NavigationService::getTablesForUser(auth()->user()),
                'permissions' => $this->getExtendedPermissions('contracts')
            ]);
        }
    }

    public function create(Request $request)
    {
        $this->checkPermission('create contracts');

        try {
            // Get customer if provided via query parameter
            $selectedCustomer = null;
            $selectedCustomerId = null;
            
            if ($request->has('customer_id')) {
                $selectedCustomerId = $request->customer_id;
                $selectedCustomer = Customer::where('id', $selectedCustomerId)
                    ->where('status', '!=', 'rejected')
                    ->select('id', 'name', 'email', 'phone')
                    ->first();
                
                if (!$selectedCustomer) {
                    return redirect()->back()->with('error', 'Customer not found or is rejected.');
                }
            }

            // Get customers with draft or contract_created status for contract creation
            $customers = Customer::whereIn('status', ['draft', 'contract_created'])
                ->select('id', 'name', 'email', 'phone')
                ->get();
            
            $proposals = Proposal::where('status', 'signed')
                ->whereDoesntHave('contract')
                ->with(['potentialCustomer' => function($query) {
                    $query->select('id', 'potential_customer_name');
                }])
                ->select('id', 'title', 'price', 'potential_customer_id')
                ->get();
            
            $tables = NavigationService::getTablesForUser(auth()->user());

            return Inertia::render('Admin/Contracts/Create', [
                'customers' => $customers,
                'proposals' => $proposals,
                'selectedCustomer' => $selectedCustomer,
                'selectedCustomerId' => $selectedCustomerId,
                'tables' => $tables,
                'permissions' => $this->getExtendedPermissions('contracts')
            ]);

        } catch (\Exception $e) {
            Log::error('Error in ContractController@create: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load contract creation form.');
        }
    }

    public function store(Request $request)
    {
        $this->checkPermission('create contracts');

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'proposal_id' => 'nullable|exists:proposals,id',
            'contract_title' => 'required|string|max:255',
            'contract_description' => 'required|string',
            'total_value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'payment_terms' => 'required|string|max:1000',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $contractData = [
                'customer_id' => $validated['customer_id'],
                'proposal_id' => $validated['proposal_id'],
                'contract_title' => $validated['contract_title'],
                'contract_description' => $validated['contract_description'],
                'total_value' => $validated['total_value'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'payment_terms' => $validated['payment_terms'],
                'status' => 'contract_created',
                'created_by' => auth()->id(),
            ];

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('contracts', $fileName, 'public');
                $contractData['file'] = '/storage/' . $filePath;
            }

            $contract = Contract::create($contractData);

            // Update customer status and payment total
            $customer = Customer::find($validated['customer_id']);
            if ($customer) {
                $customer->update([
                    'status' => 'contract_created',
                    'total_payment_amount' => $validated['total_value'],
                    'remaining_amount' => $validated['total_value'],
                    'payment_status' => 'not_paid',
                ]);
                
                // Calculate commission if commission user exists
                if ($customer->commission_user_id && $customer->commission_rate) {
                    $customer->calculateCommission();
                }
            }

            DB::commit();

            if ($request->has('redirect_to_customer')) {
                return redirect()->route('customers.show', $validated['customer_id'])
                    ->with('success', 'Contract created successfully.');
            }

            return redirect('/admin/contracts')->with('success', 'Contract created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in ContractController@store: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create contract. Please try again.');
        }
    }

    public function show($id)
    {
        $this->checkPermission('view contracts');

        try {
            $contract = Contract::with([
                'customer' => function($query) {
                    $query->select('id', 'name', 'email', 'phone', 'location', 'status', 'city_id', 'subcity_id', 'total_payment_amount', 'paid_amount', 'remaining_amount', 'commission_user_id', 'commission_rate')
                          ->with(['city', 'subcity', 'commissionUser' => function($q) {
                              $q->select('id', 'name', 'commission_rate');
                          }]);
                },
                'proposal' => function($query) {
                    $query->select('id', 'title', 'price', 'description', 'potential_customer_id')
                          ->with(['potentialCustomer' => function($q) {
                              $q->select('id', 'potential_customer_name');
                          }]);
                },
                'createdBy' => function($query) {
                    $query->select('id', 'name');
                }
            ])->findOrFail($id);
            
            $tables = NavigationService::getTablesForUser(auth()->user());
            $permissions = $this->getExtendedPermissions('contracts');

            return Inertia::render('Admin/Contracts/Show', [
                'contract' => $contract,
                'tables' => $tables,
                'permissions' => $permissions,
            ]);

        } catch (\Exception $e) {
            Log::error('Error in ContractController@show: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Contract not found.');
        }
    }

    public function edit($id)
    {
        $this->checkPermission('edit contracts');

        try {
            $contract = Contract::findOrFail($id);
            
            $customers = Customer::whereIn('status', ['draft', 'contract_created', 'accepted'])
                ->select('id', 'name', 'email')
                ->get();
            
            $proposals = Proposal::where('status', 'signed')
                ->with(['potentialCustomer'])
                ->select('id', 'title', 'price', 'potential_customer_id')
                ->get();
            
            $tables = NavigationService::getTablesForUser(auth()->user());

            return Inertia::render('Admin/Contracts/Edit', [
                'contract' => $contract,
                'customers' => $customers,
                'proposals' => $proposals,
                'tables' => $tables,
                'permissions' => $this->getExtendedPermissions('contracts')
            ]);

        } catch (\Exception $e) {
            Log::error('Error in ContractController@edit: ' . $e->getMessage());
            return redirect('/admin/contracts')->with('error', 'Contract not found.');
        }
    }

    public function update(Request $request, $id)
    {
        $this->checkPermission('edit contracts');

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'proposal_id' => 'nullable|exists:proposals,id',
            'contract_title' => 'required|string|max:255',
            'contract_description' => 'required|string',
            'total_value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'payment_terms' => 'required|string|max:1000',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $contract = Contract::findOrFail($id);
            
            $oldTotalValue = $contract->total_value;
            $oldCustomerId = $contract->customer_id;

            $updateData = [
                'customer_id' => $validated['customer_id'],
                'proposal_id' => $validated['proposal_id'],
                'contract_title' => $validated['contract_title'],
                'contract_description' => $validated['contract_description'],
                'total_value' => $validated['total_value'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'payment_terms' => $validated['payment_terms'],
            ];

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('contracts', $fileName, 'public');
                $updateData['file'] = '/storage/' . $filePath;
            }

            $contract->update($updateData);

            // Update customer payment total if contract total value changed or customer changed
            if ($validated['customer_id'] != $oldCustomerId || $validated['total_value'] != $oldTotalValue) {
                $customer = Customer::find($validated['customer_id']);
                if ($customer) {
                    $customer->update([
                        'total_payment_amount' => $validated['total_value'],
                        'remaining_amount' => max(0, $validated['total_value'] - $customer->paid_amount),
                    ]);
                    
                    // Recalculate commission
                    if ($customer->commission_user_id && $customer->commission_rate) {
                        $customer->calculateCommission();
                    }
                    
                    $customer->updatePaymentStatus();
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'Contract updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in ContractController@update: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update contract. Please try again.');
        }
    }

    public function destroy($id)
    {
        $this->checkPermission('delete contracts');

        try {
            DB::beginTransaction();

            $contract = Contract::findOrFail($id);
            
            // Check if contract can be deleted
            if ($contract->status === 'accepted') {
                return redirect()->back()->with('error', 'Accepted contracts cannot be deleted.');
            }

            $customer = Customer::find($contract->customer_id);
            
            $contract->delete();

            // If this was the only contract, revert customer status to draft
            if ($customer) {
                $remainingContracts = Contract::where('customer_id', $customer->id)->count();
                if ($remainingContracts === 0) {
                    $customer->update([
                        'status' => 'draft',
                        'total_payment_amount' => null,
                        'remaining_amount' => null,
                    ]);
                }
            }

            DB::commit();

            return redirect('/admin/contracts')->with('success', 'Contract deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in ContractController@destroy: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete contract.');
        }
    }

    public function accept($id)
    {
        $this->checkPermission('approve contracts');

        try {
            DB::beginTransaction();

            $contract = Contract::findOrFail($id);
            
            if ($contract->status !== 'contract_created') {
                return redirect()->back()->with('error', 'Only contracts with status "Contract Created" can be accepted.');
            }

            $contract->update([
                'status' => 'accepted',
                'customer_signed_at' => now(),
            ]);

            // Update customer status to accepted
            $customer = Customer::find($contract->customer_id);
            if ($customer && $customer->status === 'contract_created') {
                $customer->update([
                    'status' => 'accepted',
                    'approved_at' => now(),
                    'approved_by' => auth()->id(),
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Contract accepted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in ContractController@accept: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to accept contract. Please try again.');
        }
    }

    public function reject(Request $request, $id)
    {
        $this->checkPermission('reject contracts');

        try {
            DB::beginTransaction();

            $contract = Contract::findOrFail($id);
            
            if ($contract->status !== 'contract_created') {
                return redirect()->back()->with('error', 'Only contracts with status "Contract Created" can be rejected.');
            }

            $request->validate([
                'reason' => 'required|string|min:5'
            ]);

            $contract->update([
                'status' => 'rejected',
                'customer_rejected_at' => now(),
                'customer_review' => $request->reason,
            ]);

            // Update customer status to rejected
            $customer = Customer::find($contract->customer_id);
            if ($customer && $customer->status === 'contract_created') {
                $customer->update([
                    'status' => 'rejected',
                    'rejected_at' => now(),
                    'rejection_reason' => $request->reason,
                    'rejected_by' => auth()->id(),
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Contract rejected successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in ContractController@reject: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to reject contract. Please try again.');
        }
    }

    public function markAsUnsigned($id)
    {
        $this->checkPermission('edit contracts');

        try {
            DB::beginTransaction();

            $contract = Contract::findOrFail($id);
            
            if ($contract->status === 'accepted') {
                $contract->update([
                    'status' => 'contract_created',
                    'customer_signed_at' => null,
                ]);

                // Update customer status back to contract_created
                $customer = Customer::find($contract->customer_id);
                if ($customer && $customer->status === 'accepted') {
                    $customer->update([
                        'status' => 'contract_created',
                        'approved_at' => null,
                        'approved_by' => null,
                    ]);
                }

                DB::commit();

                return redirect()->back()->with('success', 'Contract marked as unsigned successfully.');
            }

            return redirect()->back()->with('error', 'Only accepted contracts can be marked as unsigned.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in ContractController@markAsUnsigned: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update contract status.');
        }
    }

    public function download($id)
    {
        $this->checkPermission('view contracts');

        try {
            $contract = Contract::findOrFail($id);
            
            if (!$contract->file) {
                return redirect()->back()->with('error', 'No file attached to this contract.');
            }

            $filePath = storage_path('app/public/' . str_replace('/storage/', '', $contract->file));
            
            if (!file_exists($filePath)) {
                return redirect()->back()->with('error', 'File not found.');
            }

            return response()->download($filePath);

        } catch (\Exception $e) {
            Log::error('Error in ContractController@download: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to download contract file.');
        }
    }
    
    /**
     * Get contracts for a specific customer
     */
    public function getCustomerContracts($customerId)
    {
        $this->checkPermission('view contracts');

        try {
            $contracts = Contract::where('customer_id', $customerId)
                ->with(['createdBy' => function($query) {
                    $query->select('id', 'name');
                }])
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'contracts' => $contracts
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting customer contracts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch contracts'
            ], 500);
        }
    }
    
    /**
     * Get customer proposals for contract creation (Modal API)
     */
    public function getCustomerProposals($customerId)
    {
        $this->checkPermission('create contracts');
        
        try {
            $customer = Customer::findOrFail($customerId);
            
            // Get proposals linked to this customer through potential customers
            $proposals = Proposal::where('status', 'signed')
                ->whereDoesntHave('contract')
                ->where(function($query) use ($customer) {
                    $query->whereHas('potentialCustomer', function($q) use ($customer) {
                        $q->where('converted_customer_id', $customer->id)
                          ->orWhere('email', $customer->email)
                          ->orWhere('phone', $customer->phone);
                    });
                })
                ->with(['potentialCustomer' => function($query) {
                    $query->select('id', 'potential_customer_name');
                }])
                ->select('id', 'title', 'price', 'description', 'potential_customer_id')
                ->get();
                
            return response()->json([
                'success' => true,
                'proposals' => $proposals
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in ContractController@getCustomerProposals: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch proposals'
            ], 500);
        }
    }
}