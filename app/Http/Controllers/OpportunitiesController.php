<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Opportunity;
use App\Models\PotentialCustomer;
use App\Models\RejectedOpportunity;
use App\Models\City;
use App\Models\Subcity;
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
            // Get opportunities with all relationships including city and subcity
            $opportunities = Opportunity::with([
                'createdBy' => function($query) {
                    $query->select('id', 'name');
                },
                'city' => function($query) {
                    $query->select('id', 'name');
                },
                'subcity' => function($query) {
                    $query->select('id', 'name');
                }
            ])
            ->select([
                'id',
                'potential_customer_name',
                'email', 
                'phone',
                'text_location',
                'map_location',
                'remarks',
                'city_id',
                'subcity_id',
                'created_by',
                'created_at',
                'updated_at'
            ])
            ->latest()
            ->paginate(10);

            // Get tables for navigation
            $tables = NavigationService::getTablesForUser(auth()->user());

            // Get cities and subcities for filters
            $cities = City::select('id', 'name')->get();
            $subcities = Subcity::select('id', 'name')->get();

            return Inertia::render('Admin/Opportunities/Index', [
                'opportunities' => $opportunities,
                'tables' => $tables,
                'cities' => $cities,
                'subcities' => $subcities,
                'permissions' => $this->getExtendedPermissions('opportunities')
            ]);

        } catch (\Exception $e) {
            \Log::error('Opportunities Index Error: ' . $e->getMessage());
            \Log::error('Stack Trace: ' . $e->getTraceAsString());
            
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
                'cities' => [],
                'subcities' => [],
                'permissions' => $this->getExtendedPermissions('opportunities')
            ]);
        }
    }

    public function create()
    {
        $this->checkPermission('create opportunities');

        $tables = NavigationService::getTablesForUser(auth()->user());

        // Get cities and subcities for the form
        $cities = City::select('id', 'name')->get();
        $subcities = Subcity::select('id', 'name')->get();

        return Inertia::render('Admin/Opportunities/Create', [
            'tables' => $tables,
            'cities' => $cities,
            'subcities' => $subcities,
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
            'text_location' => 'nullable|string|max:500',
            'map_location' => 'nullable|string|max:500',
            'remarks' => 'nullable|string',
            'city_id' => 'nullable|exists:cities,id',
            'subcity_id' => 'nullable|exists:subcities,id',
        ]);

        try {
            $validated['created_by'] = auth()->id();
            
            $opportunity = Opportunity::create($validated);

            return redirect()->route('admin.opportunities.show', $opportunity->id) // FIXED
                ->with('success', 'Opportunity created successfully.');

        } catch (\Exception $e) {
            \Log::error('Opportunity Store Error: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to create opportunity. Please try again.')
                ->withInput();
        }
    }

    public function show($id)
    {
        $this->checkPermission('view opportunities');

        try {
            $opportunity = Opportunity::with([
                'createdBy' => function($query) {
                    $query->select('id', 'name');
                },
                'city' => function($query) {
                    $query->select('id', 'name');
                },
                'subcity' => function($query) {
                    $query->select('id', 'name');
                }
            ])->findOrFail($id);
            
            $tables = NavigationService::getTablesForUser(auth()->user());

            // Get cities and subcities for the form
            $cities = City::select('id', 'name')->get();
            $subcities = Subcity::select('id', 'name')->get();

            return Inertia::render('Admin/Opportunities/Show', [
                'opportunity' => $opportunity,
                'tables' => $tables,
                'cities' => $cities,
                'subcities' => $subcities,
                'permissions' => $this->getExtendedPermissions('opportunities')
            ]);

        } catch (\Exception $e) {
            \Log::error('Opportunity Show Error: ' . $e->getMessage());
            
            return redirect()->route('admin.opportunities.index') // FIXED
                ->with('error', 'Opportunity not found.');
        }
    }

    public function edit($id)
    {
        $this->checkPermission('edit opportunities');

        try {
            $opportunity = Opportunity::with(['city', 'subcity'])->findOrFail($id);
            $tables = NavigationService::getTablesForUser(auth()->user());

            // Get cities and subcities for the form
            $cities = City::select('id', 'name')->get();
            $subcities = Subcity::select('id', 'name')->get();

            return Inertia::render('Admin/Opportunities/Edit', [
                'opportunity' => $opportunity,
                'tables' => $tables,
                'cities' => $cities,
                'subcities' => $subcities,
                'permissions' => $this->getExtendedPermissions('opportunities')
            ]);

        } catch (\Exception $e) {
            \Log::error('Opportunity Edit Error: ' . $e->getMessage());
            
            return redirect()->route('admin.opportunities.index') // FIXED
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
            'text_location' => 'nullable|string|max:500',
            'map_location' => 'nullable|string|max:500',
            'remarks' => 'nullable|string',
            'city_id' => 'nullable|exists:cities,id',
            'subcity_id' => 'nullable|exists:subcities,id',
        ]);

        try {
            $opportunity = Opportunity::findOrFail($id);
            $opportunity->update($validated);

            return redirect()->route('admin.opportunities.show', $opportunity->id) // FIXED
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

            return redirect()->route('admin.opportunities.index') // FIXED
                ->with('success', 'Opportunity deleted successfully.');

        } catch (\Exception $e) {
            \Log::error('Opportunity Delete Error: ' . $e->getMessage());
            
            return redirect()->route('admin.opportunities.index') // FIXED
                ->with('error', 'Failed to delete opportunity. Please try again.');
        }
    }

    public function approve($id)
    {
        $this->checkPermission('approve opportunities');

        try {
            $opportunity = Opportunity::findOrFail($id);

            DB::transaction(function () use ($opportunity) {
                // Create potential customer with city and subcity
                PotentialCustomer::create([
                    'potential_customer_name' => $opportunity->potential_customer_name,
                    'email' => $opportunity->email,
                    'phone' => $opportunity->phone,
                    'text_location' => $opportunity->text_location,
                    'map_location' => $opportunity->map_location,
                    'remarks' => $opportunity->remarks,
                    'created_by' => $opportunity->created_by,
                    'city_id' => $opportunity->city_id,
                    'subcity_id' => $opportunity->subcity_id,
                    'status' => 'draft',
                ]);

                // Delete the opportunity
                $opportunity->delete();
            });

            return redirect()->route('admin.opportunities.index') // FIXED
                ->with('success', 'Opportunity approved and moved to potential customers.');

        } catch (\Exception $e) {
            \Log::error('Opportunity Approve Error: ' . $e->getMessage());
            
            return redirect()->route('admin.opportunities.index') // FIXED
                ->with('error', 'Failed to approve opportunity. Please try again.');
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
                // Create rejected opportunity record with city and subcity
                RejectedOpportunity::create([
                    'opportunity_id' => $opportunity->id,
                    'potential_customer_name' => $opportunity->potential_customer_name,
                    'email' => $opportunity->email,
                    'phone' => $opportunity->phone,
                    'text_location' => $opportunity->text_location,
                    'map_location' => $opportunity->map_location,
                    'created_by' => auth()->id(),
                    'remarks' => $opportunity->remarks,
                    'reason' => $request->reason,
                    'rejected_from' => 'opportunity',
                    'original_id' => $opportunity->id,
                    'city_id' => $opportunity->city_id,
                    'subcity_id' => $opportunity->subcity_id,
                ]);

                // Delete the original opportunity
                $opportunity->delete();
            });

            return redirect()->route('admin.opportunities.index') // FIXED
                ->with('success', 'Opportunity rejected successfully.');

        } catch (\Exception $e) {
            \Log::error('Reject Error: ' . $e->getMessage());
            return redirect()->route('admin.opportunities.index') // FIXED
                ->with('error', 'Failed to reject opportunity.');
        }
    }

    /**
     * Get opportunities for API or other purposes
     */
    public function getOpportunities()
    {
        try {
            $opportunities = Opportunity::with([
                'createdBy' => function($query) {
                    $query->select('id', 'name');
                },
                'city' => function($query) {
                    $query->select('id', 'name');
                },
                'subcity' => function($query) {
                    $query->select('id', 'name');
                }
            ])->latest()->get();

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

    /**
     * Get opportunities by city or subcity (for filtering)
     */
    public function filterByLocation(Request $request)
    {
        try {
            $query = Opportunity::with(['city', 'subcity', 'createdBy']);

            if ($request->has('city_id') && $request->city_id) {
                $query->where('city_id', $request->city_id);
            }

            if ($request->has('subcity_id') && $request->subcity_id) {
                $query->where('subcity_id', $request->subcity_id);
            }

            $opportunities = $query->latest()->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $opportunities,
                'message' => 'Opportunities filtered successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Filter Opportunities Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to filter opportunities',
                'data' => []
            ], 500);
        }
    }
}