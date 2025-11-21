<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Contract;
use App\Models\Proposal;
use App\Models\Customer;
use App\Services\NavigationService;
use App\Traits\HandlesPermissions;

class ContractController extends Controller
{
    use HandlesPermissions;

    public function index()
    {
        $this->checkPermission('view contracts');

        try {
            $contracts = Contract::with(['proposal', 'customer'])
                ->latest()
                ->paginate(10);
            
            $tables = NavigationService::getTablesForUser(auth()->user());

            return Inertia::render('Admin/Contracts/Index', [
                'contracts' => $contracts,
                'tables' => $tables,
                'permissions' => $this->getExtendedPermissions('contracts')
            ]);

        } catch (\Exception $e) {
            \Log::error('Contracts Index Error: ' . $e->getMessage());
            
            return Inertia::render('Admin/Contracts/Index', [
                'contracts' => ['data' => [], 'links' => []],
                'tables' => NavigationService::getTablesForUser(auth()->user()),
                'permissions' => $this->getExtendedPermissions('contracts')
            ]);
        }
    }

    public function create()
    {
        $this->checkPermission('create contracts');

        try {
            $proposals = Proposal::whereHas('potentialCustomer', function($query) {
                $query->where('status', 'accepted');
            })->with('potentialCustomer')->get();
            
            $tables = NavigationService::getTablesForUser(auth()->user());

            return Inertia::render('Admin/Contracts/Create', [
                'proposals' => $proposals,
                'tables' => $tables,
                'permissions' => $this->getExtendedPermissions('contracts')
            ]);

        } catch (\Exception $e) {
            \Log::error('Contract Create Error: ' . $e->getMessage());
            
            return redirect()->route('contracts.index')
                ->with('error', 'Failed to load contract creation form.');
        }
    }

    public function store(Request $request)
    {
        $this->checkPermission('create contracts');

        $validated = $request->validate([
            'proposal_id' => 'required|exists:proposals,id',
            'contract_title' => 'required|string|max:255',
            'contract_description' => 'required|string',
            'total_value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'payment_terms' => 'required|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        try {
            // Get customer from proposal
            $proposal = Proposal::with('potentialCustomer')->find($validated['proposal_id']);
            $customer = Customer::where('name', $proposal->potentialCustomer->potential_customer_name)->first();

            $contractData = [
                'proposal_id' => $validated['proposal_id'],
                'customer_id' => $customer->id ?? null,
                'contract_title' => $validated['contract_title'],
                'contract_description' => $validated['contract_description'],
                'total_value' => $validated['total_value'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'payment_terms' => $validated['payment_terms'],
                'status' => 'unsigned',
            ];

            // Handle file upload
            if ($request->hasFile('file')) {
                $contractData['file'] = $request->file('file')->store('contracts', 'public');
            }

            Contract::create($contractData);

            return redirect()->route('contracts.index')
                ->with('success', 'Contract created successfully.');

        } catch (\Exception $e) {
            \Log::error('Contract Store Error: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to create contract. Please try again.')
                ->withInput();
        }
    }

    public function show($id)
    {
        $this->checkPermission('view contracts');

        try {
            $contract = Contract::with(['proposal', 'customer'])->findOrFail($id);
            $tables = NavigationService::getTablesForUser(auth()->user());

            return Inertia::render('Admin/Contracts/Show', [
                'contract' => $contract,
                'tables' => $tables,
                'permissions' => $this->getExtendedPermissions('contracts')
            ]);

        } catch (\Exception $e) {
            \Log::error('Contract Show Error: ' . $e->getMessage());
            
            return redirect()->route('contracts.index')
                ->with('error', 'Contract not found.');
        }
    }

    public function edit($id)
    {
        $this->checkPermission('edit contracts');

        try {
            $contract = Contract::findOrFail($id);
            $proposals = Proposal::whereHas('potentialCustomer', function($query) {
                $query->where('status', 'accepted');
            })->with('potentialCustomer')->get();
            
            $tables = NavigationService::getTablesForUser(auth()->user());

            return Inertia::render('Admin/Contracts/Edit', [
                'contract' => $contract,
                'proposals' => $proposals,
                'tables' => $tables,
                'permissions' => $this->getExtendedPermissions('contracts')
            ]);

        } catch (\Exception $e) {
            \Log::error('Contract Edit Error: ' . $e->getMessage());
            
            return redirect()->route('contracts.index')
                ->with('error', 'Contract not found.');
        }
    }

    public function update(Request $request, $id)
    {
        $this->checkPermission('edit contracts');

        $validated = $request->validate([
            'proposal_id' => 'required|exists:proposals,id',
            'contract_title' => 'required|string|max:255',
            'contract_description' => 'required|string',
            'total_value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'payment_terms' => 'required|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        try {
            $contract = Contract::findOrFail($id);

            $updateData = [
                'proposal_id' => $validated['proposal_id'],
                'contract_title' => $validated['contract_title'],
                'contract_description' => $validated['contract_description'],
                'total_value' => $validated['total_value'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'payment_terms' => $validated['payment_terms'],
            ];

            // Handle file upload
            if ($request->hasFile('file')) {
                $updateData['file'] = $request->file('file')->store('contracts', 'public');
            }

            $contract->update($updateData);

            return redirect()->route('contracts.index')
                ->with('success', 'Contract updated successfully.');

        } catch (\Exception $e) {
            \Log::error('Contract Update Error: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to update contract. Please try again.')
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $this->checkPermission('delete contracts');

        try {
            $contract = Contract::findOrFail($id);
            $contract->delete();

            return redirect()->route('contracts.index')
                ->with('success', 'Contract deleted successfully.');

        } catch (\Exception $e) {
            \Log::error('Contract Delete Error: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Failed to delete contract.');
        }
    }

    public function approve($id)
    {
        $this->checkPermission('approve contracts');

        try {
            $contract = Contract::findOrFail($id);
            $contract->update([
                'status' => 'signed',
                'customer_signed_at' => now(),
            ]);

            return redirect()->back()->with('success', 'Contract approved and marked as signed.');

        } catch (\Exception $e) {
            \Log::error('Contract Approve Error: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Failed to approve contract.');
        }
    }

    public function reject($id)
    {
        $this->checkPermission('reject contracts');

        try {
            $contract = Contract::findOrFail($id);
            $contract->update([
                'status' => 'rejected',
                'customer_rejected_at' => now(),
            ]);

            return redirect()->back()->with('success', 'Contract rejected successfully.');

        } catch (\Exception $e) {
            \Log::error('Contract Reject Error: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Failed to reject contract.');
        }
    }
}