<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PotentialCustomer;
use App\Models\Customer;
use App\Models\Proposal;
use App\Models\RejectedOpportunity;
use Illuminate\Support\Facades\DB;
use App\Services\NavigationService;
use App\Traits\HandlesPermissions;

class PotentialCustomerController extends Controller
{
    use HandlesPermissions;

    public function index()
    {
        $this->checkPermission('view potential_customers');

        try {
            $potentialCustomers = PotentialCustomer::with(['createdBy' => function($query) {
                $query->select('id', 'name');
            }, 'proposals'])->latest()->paginate(10);
            
            $tables = NavigationService::getTablesForUser(auth()->user());

            return Inertia::render('Admin/PotentialCustomers/Index', [
                'potentialCustomers' => $potentialCustomers,
                'tables' => $tables,
                'permissions' => $this->getExtendedPermissions('potential_customers')
            ]);

        } catch (\Exception $e) {
            \Log::error('Potential Customers Index Error: ' . $e->getMessage());
            
            return Inertia::render('Admin/PotentialCustomers/Index', [
                'potentialCustomers' => ['data' => [], 'links' => []],
                'tables' => NavigationService::getTablesForUser(auth()->user()),
                'permissions' => $this->getExtendedPermissions('potential_customers')
            ]);
        }
    }

    public function show($id)
    {
        $this->checkPermission('view potential_customers');

        try {
            $potentialCustomer = PotentialCustomer::with([
                'createdBy' => function($query) {
                    $query->select('id', 'name');
                },
                'proposals.createdBy'
            ])->findOrFail($id);
            
            $tables = NavigationService::getTablesForUser(auth()->user());

            return Inertia::render('Admin/PotentialCustomers/Show', [
                'potentialCustomer' => $potentialCustomer,
                'tables' => $tables,
                'permissions' => $this->getExtendedPermissions('potential_customers')
            ]);

        } catch (\Exception $e) {
            \Log::error('Potential Customer Show Error: ' . $e->getMessage());
            
            return redirect()->route('potential-customers.index')
                ->with('error', 'Potential customer not found.');
        }
    }

    public function destroy($id)
    {
        $this->checkPermission('delete potential_customers');

        try {
            $potentialCustomer = PotentialCustomer::findOrFail($id);
            $potentialCustomer->delete();

            return redirect()->route('potential-customers.index')
                ->with('success', 'Potential customer deleted successfully.');

        } catch (\Exception $e) {
            \Log::error('Potential Customer Delete Error: ' . $e->getMessage());
            
            return redirect()->route('potential-customers.index')
                ->with('error', 'Failed to delete potential customer.');
        }
    }

    public function approve($id)
    {
        $this->checkPermission('approve potential_customers');

        try {
            $potentialCustomer = PotentialCustomer::findOrFail($id);

            DB::transaction(function () use ($potentialCustomer) {
                // Create customer record
                $customer = Customer::create([
                    'name' => $potentialCustomer->potential_customer_name,
                    'email' => $potentialCustomer->email,
                    'phone' => $potentialCustomer->phone,
                    'location' => $potentialCustomer->location,
                    'remarks' => $potentialCustomer->remarks,
                    'created_by' => auth()->id(),
                ]);

                // Update potential customer status to 'accepted' instead of deleting
                $potentialCustomer->update([
                    'status' => 'accepted',
                    'approved_at' => now(),
                    'approved_by' => auth()->id(),
                ]);
            });

            return redirect()->back()->with('success', 'Potential customer approved and moved to customers.');

        } catch (\Exception $e) {
            \Log::error('Potential Customer Approve Error: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Failed to approve potential customer. Please try again.');
        }
    }

    public function reject(Request $request, $id)
    {
        $this->checkPermission('reject potential_customers');

        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $potentialCustomer = PotentialCustomer::findOrFail($id);

            DB::transaction(function () use ($potentialCustomer, $request) {
                // Create rejected opportunity record
                RejectedOpportunity::create([
                    'potential_customer_name' => $potentialCustomer->potential_customer_name,
                    'email' => $potentialCustomer->email,
                    'phone' => $potentialCustomer->phone,
                    'location' => $potentialCustomer->location,
                    'created_by' => auth()->id(),
                    'remarks' => $potentialCustomer->remarks,
                    'reason' => $request->reason,
                    'rejected_from' => 'potential_customer',
                    'original_id' => $potentialCustomer->id,
                ]);

                // Update potential customer status to 'rejected' instead of deleting
                $potentialCustomer->update([
                    'status' => 'rejected',
                    'rejected_at' => now(),
                    'rejected_by' => auth()->id(),
                    'rejection_reason' => $request->reason,
                ]);
            });

            return redirect()->back()->with('success', 'Potential customer rejected successfully.');

        } catch (\Exception $e) {
            \Log::error('Potential Customer Reject Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to reject potential customer.');
        }
    }

    // Create proposal for potential customer
    public function createProposal(Request $request, $id)
    {
        $this->checkPermission('create proposals');

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
        ]);

        try {
            $potentialCustomer = PotentialCustomer::findOrFail($id);

            $proposal = Proposal::create([
                'potential_customer_id' => $potentialCustomer->id,
                'created_by' => auth()->id(),
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'status' => 'unsigned',
            ]);

            // Update potential customer status to 'proposal_sent'
            $potentialCustomer->update([
                'status' => 'proposal_sent'
            ]);

            return redirect()->back()->with('success', 'Proposal created successfully and sent to customer.');

        } catch (\Exception $e) {
            \Log::error('Create Proposal Error: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Failed to create proposal. Please try again.');
        }
    }
}