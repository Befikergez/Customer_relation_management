<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Opportunity;
use App\Models\RejectedOpportunity;
use App\Models\PotentialCustomer;
use App\Models\Customer;
use App\Models\City;
use App\Models\Subcity;
use App\Services\NavigationService;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = $request->get('q', '');
        $filters = $request->get('filters', ['opportunities', 'customers', 'potential_customers', 'rejected_opportunities']);
        
        $results = [];
        
        if ($searchQuery) {
            $results = $this->performGlobalSearch($searchQuery, $filters);
        }
        
        return Inertia::render('Admin/Search', [
            'searchResults' => $results,
            'searchQuery' => $searchQuery,
            'filters' => $filters,
            'tables' => NavigationService::getTablesForUser(auth()->user())
        ]);
    }
    
    private function performGlobalSearch($query, $filters)
    {
        $results = [];
        
        // Search Opportunities
        if (in_array('opportunities', $filters)) {
            $opportunities = Opportunity::with(['city', 'subcity'])
                ->where(function ($q) use ($query) {
                    $q->where('potential_customer_name', 'LIKE', "%{$query}%")
                      ->orWhere('text_location', 'LIKE', "%{$query}%")
                      ->orWhere('map_location', 'LIKE', "%{$query}%")
                      ->orWhereHas('city', function ($cityQuery) use ($query) {
                          $cityQuery->where('name', 'LIKE', "%{$query}%");
                      })
                      ->orWhereHas('subcity', function ($subcityQuery) use ($query) {
                          $subcityQuery->where('name', 'LIKE', "%{$query}%");
                      });
                })
                ->get()
                ->map(function ($opportunity) use ($query) {
                    return $this->formatSearchResult($opportunity, 'opportunity', $query);
                });
            
            $results = array_merge($results, $opportunities->toArray());
        }
        
        // Search Customers
        if (in_array('customers', $filters)) {
            $customers = Customer::with(['city', 'subcity'])
                ->where(function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('text_location', 'LIKE', "%{$query}%")
                      ->orWhere('map_location', 'LIKE', "%{$query}%")
                      ->orWhereHas('city', function ($cityQuery) use ($query) {
                          $cityQuery->where('name', 'LIKE', "%{$query}%");
                      })
                      ->orWhereHas('subcity', function ($subcityQuery) use ($query) {
                          $subcityQuery->where('name', 'LIKE', "%{$query}%");
                      });
                })
                ->get()
                ->map(function ($customer) use ($query) {
                    return $this->formatSearchResult($customer, 'customer', $query);
                });
            
            $results = array_merge($results, $customers->toArray());
        }
        
        // Search Potential Customers
        if (in_array('potential_customers', $filters)) {
            $potentialCustomers = PotentialCustomer::with(['city', 'subcity'])
                ->where(function ($q) use ($query) {
                    $q->where('potential_customer_name', 'LIKE', "%{$query}%")
                      ->orWhere('text_location', 'LIKE', "%{$query}%")
                      ->orWhere('map_location', 'LIKE', "%{$query}%")
                      ->orWhereHas('city', function ($cityQuery) use ($query) {
                          $cityQuery->where('name', 'LIKE', "%{$query}%");
                      })
                      ->orWhereHas('subcity', function ($subcityQuery) use ($query) {
                          $subcityQuery->where('name', 'LIKE', "%{$query}%");
                      });
                })
                ->get()
                ->map(function ($potentialCustomer) use ($query) {
                    return $this->formatSearchResult($potentialCustomer, 'potential_customer', $query);
                });
            
            $results = array_merge($results, $potentialCustomers->toArray());
        }
        
        // Search Rejected Opportunities
        if (in_array('rejected_opportunities', $filters)) {
            $rejectedOpportunities = RejectedOpportunity::with(['city', 'subcity'])
                ->where(function ($q) use ($query) {
                    $q->where('potential_customer_name', 'LIKE', "%{$query}%")
                      ->orWhere('text_location', 'LIKE', "%{$query}%")
                      ->orWhere('map_location', 'LIKE', "%{$query}%")
                      ->orWhereHas('city', function ($cityQuery) use ($query) {
                          $cityQuery->where('name', 'LIKE', "%{$query}%");
                      })
                      ->orWhereHas('subcity', function ($subcityQuery) use ($query) {
                          $subcityQuery->where('name', 'LIKE', "%{$query}%");
                      });
                })
                ->get()
                ->map(function ($rejectedOpportunity) use ($query) {
                    return $this->formatSearchResult($rejectedOpportunity, 'rejected_opportunity', $query);
                });
            
            $results = array_merge($results, $rejectedOpportunities->toArray());
        }
        
        // Sort by relevance score
        usort($results, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        return $results;
    }
    
    private function formatSearchResult($model, $type, $query)
    {
        $matches = [];
        $score = 0;
        
        // Define fields to check based on model type
        $nameField = '';
        $fieldsToCheck = [];
        
        switch ($type) {
            case 'opportunity':
            case 'potential_customer':
            case 'rejected_opportunity':
                $nameField = 'potential_customer_name';
                $fieldsToCheck = ['potential_customer_name', 'text_location', 'map_location'];
                break;
            case 'customer':
                $nameField = 'name';
                $fieldsToCheck = ['name', 'text_location', 'map_location'];
                break;
        }
        
        // Calculate matches and score for direct fields
        foreach ($fieldsToCheck as $field) {
            if (isset($model->$field) && stripos($model->$field, $query) !== false) {
                $matches[] = [
                    'field' => $field,
                    'value' => $model->$field,
                    'highlighted_value' => $this->highlightMatch($model->$field, $query)
                ];
                
                // Score based on field importance
                if ($field === $nameField) {
                    $score += 50; // Highest score for name matches
                } else {
                    $score += 30; // Good score for location fields
                }
            }
        }
        
        // Check city with high weight
        if ($model->city && stripos($model->city->name, $query) !== false) {
            $matches[] = [
                'field' => 'city',
                'value' => $model->city->name,
                'highlighted_value' => $this->highlightMatch($model->city->name, $query)
            ];
            $score += 40;
        }
        
        // Check subcity with high weight
        if ($model->subcity && stripos($model->subcity->name, $query) !== false) {
            $matches[] = [
                'field' => 'subcity',
                'value' => $model->subcity->name,
                'highlighted_value' => $this->highlightMatch($model->subcity->name, $query)
            ];
            $score += 40;
        }
        
        // Calculate full location match
        $fullLocation = $this->getFullLocation($model, $type);
        if (stripos($fullLocation, $query) !== false) {
            $matches[] = [
                'field' => 'full_location',
                'value' => $fullLocation,
                'highlighted_value' => $this->highlightMatch($fullLocation, $query)
            ];
            $score += 35;
        }
        
        return [
            'id' => $model->id,
            'type' => $type,
            'title' => $this->getResultTitle($model, $type),
            'data' => $model->toArray(),
            'matches' => $matches,
            'full_location' => $fullLocation,
            'score' => min($score, 100)
        ];
    }
    
    /**
     * Get full location string for the model
     */
    private function getFullLocation($model, $type)
    {
        $locationParts = [];
        
        // Add text_location if available
        if (!empty($model->text_location)) {
            $locationParts[] = $model->text_location;
        }
        
        // Add map_location if available
        if (!empty($model->map_location)) {
            $locationParts[] = $model->map_location;
        }
        
        // Add subcity if available
        if ($model->subcity && !empty($model->subcity->name)) {
            $locationParts[] = $model->subcity->name;
        }
        
        // Add city if available
        if ($model->city && !empty($model->city->name)) {
            $locationParts[] = $model->city->name;
        }
        
        return implode(', ', $locationParts);
    }
    
    /**
     * Get display title for search result
     */
    private function getResultTitle($model, $type)
    {
        switch ($type) {
            case 'opportunity':
            case 'potential_customer':
            case 'rejected_opportunity':
                return $model->potential_customer_name;
            case 'customer':
                return $model->name;
            default:
                return 'Unknown';
        }
    }
    
    /**
     * Highlight matching text in result
     */
    private function highlightMatch($text, $query)
    {
        if (empty($text)) return $text;
        
        $pattern = '/' . preg_quote($query, '/') . '/i';
        return preg_replace($pattern, '<mark>$0</mark>', $text);
    }
}