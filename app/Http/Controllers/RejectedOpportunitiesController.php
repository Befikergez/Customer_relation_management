<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\RejectedOpportunity;
use App\Models\Opportunity;
use App\Models\PotentialCustomer;
use App\Models\Customer;
use App\Services\NavigationService;
use App\Traits\HandlesPermissions;
use Illuminate\Support\Facades\DB;

class RejectedOpportunitiesController extends Controller
{
    use HandlesPermissions;

    public function index()
    {
        $this->checkPermission('view rejected opportunities');

        try {
            $rejectedOpportunities = RejectedOpportunity::with(['createdBy' => function($query) {
                $query->select('id', 'name');
            }])
            ->latest()
            ->paginate(10);
            
            $tables = NavigationService::getTablesForUser(auth()->user());

            \Log::info('Rejected Opportunities Loaded', [
                'count' => $rejectedOpportunities->count(),
                'total' => $rejectedOpportunities->total()
            ]);

            return Inertia::render('Admin/RejectedOpportunities/Index', [
                'rejectedOpportunities' => $rejectedOpportunities,
                'tables' => $tables,
                'permissions' => $this->getExtendedPermissions('rejected opportunities')
            ]);

        } catch (\Exception $e) {
            \Log::error('Rejected Opportunities Index Error: ' . $e->getMessage());
            
            return Inertia::render('Admin/RejectedOpportunities/Index', [
                'rejectedOpportunities' => ['data' => [], 'links' => []],
                'tables' => NavigationService::getTablesForUser(auth()->user()),
                'permissions' => $this->getExtendedPermissions('rejected opportunities')
            ]);
        }
    }

    public function show($id)
    {
        $this->checkPermission('view rejected opportunities');

        try {
            $rejectedOpportunity = RejectedOpportunity::with(['createdBy' => function($query) {
                $query->select('id', 'name');
            }])->findOrFail($id);
            
            $tables = NavigationService::getTablesForUser(auth()->user());

            return Inertia::render('Admin/RejectedOpportunities/Show', [
                'rejectedOpportunity' => $rejectedOpportunity,
                'tables' => $tables,
                'permissions' => $this->getExtendedPermissions('rejected opportunities')
            ]);

        } catch (\Exception $e) {
            \Log::error('Rejected Opportunity Show Error: ' . $e->getMessage());
            
            return redirect()->route('rejected-opportunities.index')
                ->with('error', 'Rejected opportunity not found.');
        }
    }

    /**
     * Revert rejected opportunity back to its original source
     */
    public function revert($id)
    {
        $this->checkPermission('create opportunities');

        try {
            $rejectedOpportunity = RejectedOpportunity::findOrFail($id);

            \Log::info('Reverting Rejected Opportunity', [
                'id' => $rejectedOpportunity->id,
                'name' => $rejectedOpportunity->potential_customer_name,
                'source' => $rejectedOpportunity->rejected_from
            ]);

            DB::transaction(function () use ($rejectedOpportunity) {
                // Determine where to revert based on the rejection source
                switch ($rejectedOpportunity->rejected_from) {
                    case 'opportunity':
                        // Restore to opportunities
                        $this->revertToOpportunity($rejectedOpportunity);
                        break;
                        
                    case 'potential_customer':
                        // Restore to potential customers
                        $this->revertToPotentialCustomer($rejectedOpportunity);
                        break;
                        
                    case 'customer':
                        // Restore to customers
                        $this->revertToCustomer($rejectedOpportunity);
                        break;
                        
                    default:
                        // Default to opportunity
                        $this->revertToOpportunity($rejectedOpportunity);
                        break;
                }

                // Delete the rejected opportunity
                $rejectedOpportunity->delete();
            });

            return redirect()->route('rejected-opportunities.index')
                ->with('success', 'Rejected opportunity reverted successfully.');

        } catch (\Exception $e) {
            \Log::error('Rejected Opportunity Revert Error: ' . $e->getMessage());
            \Log::error('Stack Trace: ' . $e->getTraceAsString());
            
            return redirect()->back()->with('error', 'Failed to revert rejected opportunity. Please try again.');
        }
    }

    /**
     * Revert to Opportunity
     */
    protected function revertToOpportunity($rejectedOpportunity)
    {
        $opportunity = Opportunity::create([
            'potential_customer_name' => $rejectedOpportunity->potential_customer_name,
            'email' => $rejectedOpportunity->email,
            'phone' => $rejectedOpportunity->phone,
            'location' => $rejectedOpportunity->location,
            'remarks' => $rejectedOpportunity->remarks,
            'created_by' => auth()->id(),
        ]);

        \Log::info('Opportunity created from rejected opportunity', [
            'opportunity_id' => $opportunity->id,
            'name' => $opportunity->potential_customer_name
        ]);
    }

    /**
     * Revert to Potential Customer
     */
    protected function revertToPotentialCustomer($rejectedOpportunity)
    {
        $potentialCustomer = PotentialCustomer::create([
            'potential_customer_name' => $rejectedOpportunity->potential_customer_name,
            'email' => $rejectedOpportunity->email,
            'phone' => $rejectedOpportunity->phone,
            'location' => $rejectedOpportunity->location,
            'remarks' => $rejectedOpportunity->remarks,
            'created_by' => auth()->id(),
            'status' => 'draft',
        ]);

        \Log::info('Potential Customer created from rejected opportunity', [
            'potential_customer_id' => $potentialCustomer->id,
            'name' => $potentialCustomer->potential_customer_name
        ]);
    }

    /**
     * Revert to Customer
     */
    protected function revertToCustomer($rejectedOpportunity)
    {
        $customer = Customer::create([
            'name' => $rejectedOpportunity->potential_customer_name,
            'email' => $rejectedOpportunity->email,
            'phone' => $rejectedOpportunity->phone,
            'location' => $rejectedOpportunity->location,
            'remarks' => $rejectedOpportunity->remarks,
        ]);

        \Log::info('Customer created from rejected opportunity', [
            'customer_id' => $customer->id,
            'name' => $customer->name
        ]);
    }

    public function destroy($id)
    {
        $this->checkPermission('delete rejected opportunities');

        try {
            $rejectedOpportunity = RejectedOpportunity::findOrFail($id);
            $rejectedOpportunity->delete();

            return redirect()->back()->with('success', 'Rejected opportunity deleted permanently.');

        } catch (\Exception $e) {
            \Log::error('Rejected Opportunity Delete Error: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Failed to delete rejected opportunity. Please try again.');
        }
    }

    /**
     * Get rejected opportunities statistics
     */
    public function getStats()
    {
        try {
            $totalRejected = RejectedOpportunity::count();
            $fromOpportunities = RejectedOpportunity::fromOpportunities()->count();
            $fromPotentialCustomers = RejectedOpportunity::fromPotentialCustomers()->count();
            $fromCustomers = RejectedOpportunity::fromCustomers()->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $totalRejected,
                    'from_opportunities' => $fromOpportunities,
                    'from_potential_customers' => $fromPotentialCustomers,
                    'from_customers' => $fromCustomers,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Rejected Opportunities Stats Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'data' => [
                    'total' => 0,
                    'from_opportunities' => 0,
                    'from_potential_customers' => 0,
                    'from_customers' => 0,
                ]
            ]);
        }
    }
}