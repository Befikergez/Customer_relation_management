<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Opportunity;
use App\Models\PotentialCustomer;
use App\Models\RejectedOpportunity;
use Illuminate\Support\Facades\DB;
use App\Services\NavigationService;
use App\Traits\HandlesPermissions;

class OpportunitiesController extends Controller
{
    use HandlesPermissions;

    public function index()
    {
        $this->checkPermission('view opportunities');

        try {
            // Debug: Check if we have any opportunities
            $opportunityCount = Opportunity::count();
            \Log::info('Opportunities Count: ' . $opportunityCount);

            // Get opportunities with createdBy relationship
            $opportunities = Opportunity::with(['createdBy' => function($query) {
                $query->select('id', 'name'); // Only select needed fields
            }])->latest()->paginate(10);

            // Transform the data to ensure proper structure
            $opportunitiesData = $opportunities->toArray();
            
            // Debug the data
            \Log::info('Opportunities Data:', [
                'total' => $opportunities->total(),
                'count' => $opportunities->count(),
                'first_item' => $opportunitiesData['data'][0] ?? 'No data'
            ]);

            // Get tables for navigation
            $tables = NavigationService::getTablesForUser(auth()->user());

            return Inertia::render('Admin/Opportunities/Index', [
                'opportunities' => $opportunitiesData,
                'tables' => $tables,
                'permissions' => $this->getExtendedPermissions('opportunities')
            ]);

        } catch (\Exception $e) {
            \Log::error('Opportunities Index Error: ' . $e->getMessage());
            \Log::error('Stack Trace: ' . $e->getTraceAsString());
            
            // Return empty data but still render the page
            return Inertia::render('Admin/Opportunities/Index', [
                'opportunities' => [
                    'data' => [], 
                    'links' => [],
                    'meta' => [
                        'current_page' => 1,
                        'from' => null,
                        'last_page' => 1,
                        'links' => [],
                        'path' => url()->current(),
                        'per_page' => 10,
                        'to' => null,
                        'total' => 0
                    ]
                ],
                'tables' => NavigationService::getTablesForUser(auth()->user()),
                'permissions' => $this->getExtendedPermissions('opportunities')
            ]);
        }
    }

    public function create()
    {
        $this->checkPermission('create opportunities');

        $tables = NavigationService::getTablesForUser(auth()->user());

        return Inertia::render('Admin/Opportunities/Create', [
            'tables' => $tables,
            'permissions' => $this->getExtendedPermissions('opportunities')
        ]);
    }

    public function store(Request $request)
    {
        $this->checkPermission('create opportunities');

        $validated = $request->validate([
            'potential_customer_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'location' => 'nullable|url|max:500',
            'remarks' => 'nullable|string',
        ]);

        try {
            $validated['created_by'] = auth()->id();
            
            Opportunity::create($validated);

            return redirect()->route('opportunities.index')
                ->with('success', 'Opportunity created successfully.');

        } catch (\Exception $e) {
            \Log::error('Opportunity Store Error: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to create opportunity. Please try again.')
                ->withInput();
        }
    }

    public function edit($id)
    {
        $this->checkPermission('edit opportunities');

        try {
            $opportunity = Opportunity::findOrFail($id);
            $tables = NavigationService::getTablesForUser(auth()->user());

            return Inertia::render('Admin/Opportunities/Edit', [
                'opportunity' => $opportunity,
                'tables' => $tables,
                'permissions' => $this->getExtendedPermissions('opportunities')
            ]);

        } catch (\Exception $e) {
            \Log::error('Opportunity Edit Error: ' . $e->getMessage());
            
            return redirect()->route('opportunities.index')
                ->with('error', 'Opportunity not found.');
        }
    }

    public function update(Request $request, $id)
    {
        $this->checkPermission('edit opportunities');

        $validated = $request->validate([
            'potential_customer_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'location' => 'nullable|url|max:500',
            'remarks' => 'nullable|string',
        ]);

        try {
            $opportunity = Opportunity::findOrFail($id);
            $opportunity->update($validated);

            return redirect()->route('opportunities.index')
                ->with('success', 'Opportunity updated successfully.');

        } catch (\Exception $e) {
            \Log::error('Opportunity Update Error: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to update opportunity. Please try again.')
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $this->checkPermission('delete opportunities');

        try {
            $opportunity = Opportunity::findOrFail($id);
            $opportunity->delete();

            return redirect()->back()->with('success', 'Opportunity deleted successfully.');

        } catch (\Exception $e) {
            \Log::error('Opportunity Delete Error: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Failed to delete opportunity. Please try again.');
        }
    }

    public function approve($id)
    {
        $this->checkPermission('approve opportunities');

        try {
            $opportunity = Opportunity::findOrFail($id);

            DB::transaction(function () use ($opportunity) {
                // Create potential customer
                PotentialCustomer::create([
                    'potential_customer_name' => $opportunity->potential_customer_name,
                    'email' => $opportunity->email,
                    'phone' => $opportunity->phone,
                    'location' => $opportunity->location,
                    'remarks' => $opportunity->remarks,
                    'created_by' => $opportunity->created_by,
                    'status' => 'draft',
                ]);

                // Delete the opportunity
                $opportunity->delete();
            });

            return redirect()->back()->with('success', 'Opportunity approved and moved to potential customers.');

        } catch (\Exception $e) {
            \Log::error('Opportunity Approve Error: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Failed to approve opportunity. Please try again.');
        }
    }

   public function reject(Request $request, $id)
{
    $this->checkPermission('reject opportunities');

    $request->validate([
        'reason' => 'required|string|max:1000',
    ]);

    try {
        $opportunity = Opportunity::findOrFail($id);

        DB::transaction(function () use ($opportunity, $request) {
            // Create rejected opportunity record WITH original_id
            RejectedOpportunity::create([
                'opportunity_id' => $opportunity->id,
                'potential_customer_name' => $opportunity->potential_customer_name,
                'email' => $opportunity->email,
                'phone' => $opportunity->phone,
                'location' => $opportunity->location,
                'created_by' => auth()->id(),
                'remarks' => $opportunity->remarks,
                'reason' => $request->reason,
                'rejected_from' => 'opportunity',
                'original_id' => $opportunity->id, // Add this
            ]);

            // Delete the original opportunity
            $opportunity->delete();
        });

        return redirect()->back()->with('success', 'Opportunity rejected successfully.');

    } catch (\Exception $e) {
        \Log::error('Reject Error: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Failed to reject opportunity.');
    }
}

    /**
     * Get opportunities for API or other purposes
     */
    public function getOpportunities()
    {
        try {
            $opportunities = Opportunity::with(['createdBy' => function($query) {
                $query->select('id', 'name');
            }])->latest()->get();

            return response()->json([
                'success' => true,
                'data' => $opportunities,
                'count' => $opportunities->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Get Opportunities API Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch opportunities',
                'data' => []
            ], 500);
        }
    }

    /**
     * Get opportunity statistics
     */
    public function getStats()
    {
        try {
            $totalOpportunities = Opportunity::count();
            $todayOpportunities = Opportunity::whereDate('created_at', today())->count();
            $thisWeekOpportunities = Opportunity::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
            $thisMonthOpportunities = Opportunity::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $totalOpportunities,
                    'today' => $todayOpportunities,
                    'this_week' => $thisWeekOpportunities,
                    'this_month' => $thisMonthOpportunities
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Opportunity Stats Error: ' . $e->getMessage());
            
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