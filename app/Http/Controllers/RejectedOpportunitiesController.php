<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\RejectedOpportunity;
use App\Models\Opportunity;
use App\Models\PotentialCustomer;
use App\Models\Customer;
use App\Models\City;
use App\Models\Subcity;
use App\Services\NavigationService;
use App\Traits\HandlesPermissions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RejectedOpportunitiesController extends Controller
{
    use HandlesPermissions;

    public function index(Request $request)
    {
        $this->checkPermission('view rejected opportunities');

        try {
            $query = RejectedOpportunity::with([
                'createdBy' => function($query) {
                    $query->select('id', 'name');
                },
                'city' => function($query) {
                    $query->select('id', 'name');
                },
                'subcity' => function($query) {
                    $query->select('id', 'name');
                }
            ]);

            // Apply search filter
            if ($request->has('search') && $request->search) {
                $query->search($request->search);
            }

            // Apply city filter
            if ($request->has('city_id') && $request->city_id) {
                $query->byCity($request->city_id);
            }

            // Apply subcity filter
            if ($request->has('subcity_id') && $request->subcity_id) {
                $query->bySubcity($request->subcity_id);
            }

            // Apply source filter
            if ($request->has('source') && $request->source) {
                $query->fromSource($request->source);
            }

            $rejectedOpportunities = $query->latest()->paginate(10);
            
            $tables = NavigationService::getTablesForUser(auth()->user());
            $cities = City::select('id', 'name')->orderBy('name')->get();
            $subcities = Subcity::select('id', 'name', 'city_id')->orderBy('name')->get();

            \Log::info('Rejected Opportunities Loaded', [
                'count' => $rejectedOpportunities->count(),
                'total' => $rejectedOpportunities->total(),
                'filters' => $request->all()
            ]);

            return Inertia::render('Admin/RejectedOpportunities/Index', [
                'rejectedOpportunities' => $rejectedOpportunities,
                'tables' => $tables,
                'permissions' => $this->getExtendedPermissions('rejected opportunities'),
                'cities' => $cities,
                'subcities' => $subcities,
                'filters' => $request->only(['search', 'city_id', 'subcity_id', 'source'])
            ]);

        } catch (\Exception $e) {
            \Log::error('Rejected Opportunities Index Error: ' . $e->getMessage());
            
            return Inertia::render('Admin/RejectedOpportunities/Index', [
                'rejectedOpportunities' => ['data' => [], 'links' => []],
                'tables' => NavigationService::getTablesForUser(auth()->user()),
                'permissions' => $this->getExtendedPermissions('rejected opportunities'),
                'cities' => [],
                'subcities' => [],
                'filters' => $request->only(['search', 'city_id', 'subcity_id', 'source'])
            ]);
        }
    }

    public function show($id)
    {
        $this->checkPermission('view rejected opportunities');

        try {
            $rejectedOpportunity = RejectedOpportunity::with([
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
            
            // Debug log to check data
            \Log::info('Rejected Opportunity Data', [
                'id' => $rejectedOpportunity->id,
                'name' => $rejectedOpportunity->potential_customer_name,
                'text_location' => $rejectedOpportunity->text_location,
                'map_location' => $rejectedOpportunity->map_location,
                'email' => $rejectedOpportunity->email,
                'phone' => $rejectedOpportunity->phone,
                'city_id' => $rejectedOpportunity->city_id,
                'subcity_id' => $rejectedOpportunity->subcity_id,
                'has_city' => !is_null($rejectedOpportunity->city),
                'has_subcity' => !is_null($rejectedOpportunity->subcity)
            ]);
            
            $tables = NavigationService::getTablesForUser(auth()->user());
            $cities = City::select('id', 'name')->orderBy('name')->get();
            $subcities = Subcity::select('id', 'name', 'city_id')->orderBy('name')->get();

            return Inertia::render('Admin/RejectedOpportunities/Show', [
                'rejected' => $rejectedOpportunity,
                'tables' => $tables,
                'permissions' => $this->getExtendedPermissions('rejected opportunities'),
                'cities' => $cities,
                'subcities' => $subcities
            ]);

        } catch (\Exception $e) {
            \Log::error('Rejected Opportunity Show Error: ' . $e->getMessage());
            
            return redirect()->route('rejected-opportunities.index')
                ->with('error', 'Rejected opportunity not found.');
        }
    }

    /**
     * Show the form for editing the specified rejected opportunity.
     */
    public function edit($id)
    {
        $this->checkPermission('edit rejected opportunities');

        try {
            $rejectedOpportunity = RejectedOpportunity::with([
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
            $cities = City::select('id', 'name')->orderBy('name')->get();
            $subcities = Subcity::select('id', 'name', 'city_id')->orderBy('name')->get();

            return Inertia::render('Admin/RejectedOpportunities/Edit', [
                'rejected' => $rejectedOpportunity,
                'tables' => $tables,
                'permissions' => $this->getExtendedPermissions('rejected opportunities'),
                'cities' => $cities,
                'subcities' => $subcities
            ]);

        } catch (\Exception $e) {
            \Log::error('Rejected Opportunity Edit Error: ' . $e->getMessage());
            
            return redirect()->route('rejected-opportunities.index')
                ->with('error', 'Rejected opportunity not found.');
        }
    }

    /**
     * Update the specified rejected opportunity in storage.
     */
    public function update(Request $request, $id)
    {
        $this->checkPermission('edit rejected opportunities');

        try {
            $rejectedOpportunity = RejectedOpportunity::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'potential_customer_name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'city_id' => 'nullable|exists:cities,id',
                'subcity_id' => 'nullable|exists:subcities,id',
                'text_location' => 'nullable|string',
                'map_location' => 'nullable|string',
                'reason' => 'nullable|string|max:1000',
                'remarks' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $rejectedOpportunity->update([
                'potential_customer_name' => $request->potential_customer_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'city_id' => $request->city_id,
                'subcity_id' => $request->subcity_id,
                'text_location' => $request->text_location,
                'map_location' => $request->map_location,
                'reason' => $request->reason,
                'remarks' => $request->remarks,
                'updated_by' => auth()->id(),
            ]);

            \Log::info('Rejected Opportunity Updated', [
                'id' => $rejectedOpportunity->id,
                'name' => $rejectedOpportunity->potential_customer_name,
                'updated_by' => auth()->id()
            ]);

            return redirect()->route('rejected-opportunities.show', $rejectedOpportunity->id)
                ->with('success', 'Rejected opportunity updated successfully.');

        } catch (\Exception $e) {
            \Log::error('Rejected Opportunity Update Error: ' . $e->getMessage());
            \Log::error('Stack Trace: ' . $e->getTraceAsString());
            
            return redirect()->back()
                ->with('error', 'Failed to update rejected opportunity. Please try again.')
                ->withInput();
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
                'source' => $rejectedOpportunity->rejected_from,
                'text_location' => $rejectedOpportunity->text_location,
                'map_location' => $rejectedOpportunity->map_location,
                'city_id' => $rejectedOpportunity->city_id,
                'subcity_id' => $rejectedOpportunity->subcity_id
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
        $opportunityData = [
            'potential_customer_name' => $rejectedOpportunity->potential_customer_name,
            'email' => $rejectedOpportunity->email,
            'phone' => $rejectedOpportunity->phone,
            'text_location' => $rejectedOpportunity->text_location,
            'map_location' => $rejectedOpportunity->map_location,
            'remarks' => $rejectedOpportunity->remarks,
            'created_by' => auth()->id(),
            'city_id' => $rejectedOpportunity->city_id,
            'subcity_id' => $rejectedOpportunity->subcity_id,
        ];

        // Remove null values to use database defaults
        $opportunityData = array_filter($opportunityData, function($value) {
            return !is_null($value);
        });

        $opportunity = Opportunity::create($opportunityData);

        \Log::info('Opportunity created from rejected opportunity', [
            'opportunity_id' => $opportunity->id,
            'name' => $opportunity->potential_customer_name,
            'text_location' => $opportunity->text_location,
            'map_location' => $opportunity->map_location
        ]);
    }

    /**
     * Revert to Potential Customer
     */
    protected function revertToPotentialCustomer($rejectedOpportunity)
    {
        $potentialCustomerData = [
            'potential_customer_name' => $rejectedOpportunity->potential_customer_name,
            'email' => $rejectedOpportunity->email,
            'phone' => $rejectedOpportunity->phone,
            'text_location' => $rejectedOpportunity->text_location,
            'map_location' => $rejectedOpportunity->map_location,
            'remarks' => $rejectedOpportunity->remarks,
            'created_by' => auth()->id(),
            'status' => 'draft',
            'city_id' => $rejectedOpportunity->city_id,
            'subcity_id' => $rejectedOpportunity->subcity_id,
        ];

        // Remove null values to use database defaults
        $potentialCustomerData = array_filter($potentialCustomerData, function($value) {
            return !is_null($value);
        });

        $potentialCustomer = PotentialCustomer::create($potentialCustomerData);

        \Log::info('Potential Customer created from rejected opportunity', [
            'potential_customer_id' => $potentialCustomer->id,
            'name' => $potentialCustomer->potential_customer_name,
            'text_location' => $potentialCustomer->text_location,
            'map_location' => $potentialCustomer->map_location
        ]);
    }

    /**
     * Revert to Customer
     */
    protected function revertToCustomer($rejectedOpportunity)
    {
        $customerData = [
            'name' => $rejectedOpportunity->potential_customer_name,
            'email' => $rejectedOpportunity->email,
            'phone' => $rejectedOpportunity->phone,
            'text_location' => $rejectedOpportunity->text_location,
            'map_location' => $rejectedOpportunity->map_location,
            'remarks' => $rejectedOpportunity->remarks,
            'city_id' => $rejectedOpportunity->city_id,
            'subcity_id' => $rejectedOpportunity->subcity_id,
            'created_by' => auth()->id(),
        ];

        // Remove null values to use database defaults
        $customerData = array_filter($customerData, function($value) {
            return !is_null($value);
        });

        $customer = Customer::create($customerData);

        \Log::info('Customer created from rejected opportunity', [
            'customer_id' => $customer->id,
            'name' => $customer->name,
            'text_location' => $customer->text_location,
            'map_location' => $customer->map_location
        ]);
    }

    public function destroy($id)
    {
        $this->checkPermission('delete rejected opportunities');

        try {
            $rejectedOpportunity = RejectedOpportunity::findOrFail($id);
            
            \Log::info('Deleting Rejected Opportunity', [
                'id' => $rejectedOpportunity->id,
                'name' => $rejectedOpportunity->potential_customer_name,
                'deleted_by' => auth()->id()
            ]);

            $rejectedOpportunity->delete();

            return redirect()->route('rejected-opportunities.index')
                ->with('success', 'Rejected opportunity deleted permanently.');

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

    /**
     * Get subcities by city (for dependent dropdown)
     */
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
            \Log::error('Get Subcities by City Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subcities',
                'subcities' => []
            ], 500);
        }
    }
}