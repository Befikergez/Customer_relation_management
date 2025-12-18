<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Industry;
use App\Models\Opportunity;
use App\Models\RejectedOpportunity;
use App\Models\Contract;
use App\Models\Proposal;
use App\Models\ProductCategory;
use App\Models\User;
use App\Models\Role;
use App\Services\NavigationService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Get tables for navigation
        $tables = $this->getTablesWithUrls($user);

        // Get counts including settings-related counts
        $counts = $this->getCounts($user);

        // Get payment and commission status data
        $paymentCommissionData = $this->getPaymentCommissionData($user);

        // Get customer conversion analytics
        $customerAnalytics = $this->getCustomerAnalytics($user);

        // Get chart data
        $chartData = $this->getChartData($user);

        // Get notifications
        $notifications = $this->getNotifications($user);

        // Get recent activities for notifications
        $recentActivities = $this->getRecentActivities();

        // Get settings statistics for the settings page
        $settingsStats = $this->getSettingsStatistics($user);

        return Inertia::render('Admin/Dashboard', [
            'user' => $user,
            'tables' => $tables,
            'counts' => $counts,
            'paymentCommissionData' => $paymentCommissionData,
            'customerAnalytics' => $customerAnalytics,
            'chartData' => $chartData,
            'notifications' => $notifications,
            'userRole' => $user->roles->first()->name ?? 'user',
            'recentActivities' => $recentActivities,
            'settingsStats' => $settingsStats,
        ]);
    }

    /**
     * Get tables with proper URLs - UPDATED WITH ROLES
     */
    protected function getTablesWithUrls($user)
    {
        $tables = NavigationService::getTablesForUser($user);
        
        return array_map(function ($table) {
            return [
                'name' => (string) $table['name'],
                'route' => (string) $table['route'],
                'url' => $this->generateUrlFromRoute($table['route']),
            ];
        }, $tables);
    }

    /**
     * Generate URL from route name - UPDATED WITH ROLES
     */
    protected function generateUrlFromRoute($routeName)
    {
        $routeMap = [
            'users.index' => '/admin/users',
            'users.activity' => '/admin/users/activity',
            'users.analytics' => '/admin/users/analytics',
            'roles.index' => '/admin/roles',
            'products.index' => '/admin/products',
            'product-categories.index' => '/admin/product-categories',
            'customers.index' => '/admin/customers',
            'industries.index' => '/admin/industries',
            'opportunities.index' => '/admin/opportunities',
            'rejected-opportunities.index' => '/admin/rejected-opportunities',
            'contracts.index' => '/admin/contracts',
            'proposals.index' => '/admin/proposals',
            'settings.index' => '/settings',
        ];

        return $routeMap[$routeName] ?? '/dashboard';
    }

    /**
     * Check if user can view a resource
     */
    protected function canViewResource($user, $resource)
    {
        $permissions = [
            "view {$resource}",
            "{$resource}.view", 
            "{$resource}s.view",
            "read {$resource}",
            Str::singular($resource) . ".view",
            "{$resource} view",
            "show {$resource}",
            "{$resource}.read",
        ];
        
        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get counts for dashboard including settings-related counts - UPDATED WITH ROLES
     */
    protected function getCounts($user)
    {
        $counts = [];
        
        // Define resources that definitely exist
        $resources = [
            'customers' => Customer::class,
            'industries' => Industry::class,
            'opportunities' => Opportunity::class,
            'rejected_opportunities' => RejectedOpportunity::class,
            'contracts' => Contract::class,
            'proposals' => Proposal::class,
        ];

        // Conditionally add Role if the model exists
        if (class_exists('App\Models\Role') && \Schema::hasTable('roles')) {
            $resources['roles'] = Role::class;
        }

        foreach ($resources as $resource => $model) {
            try {
                $counts[$resource] = $this->canViewResource($user, $resource) 
                    ? $model::count() 
                    : 0;
            } catch (\Exception $e) {
                Log::warning("Failed to get count for {$resource}: " . $e->getMessage());
                $counts[$resource] = 0;
            }
        }

        return $counts;
    }

    /**
     * Get payment and commission status data for histograms - UPDATED TO MATCH YOUR CUSTOMER MODEL
     */
    protected function getPaymentCommissionData($user)
    {
        if (!$this->canViewResource($user, 'customers')) {
            return $this->getEmptyPaymentCommissionData();
        }

        try {
            // First check if we have any customers
            $totalCustomers = Customer::count();
            
            if ($totalCustomers === 0) {
                return $this->getEmptyPaymentCommissionData();
            }

            // Get all unique payment statuses from customers
            $paymentStatuses = Customer::distinct('payment_status')
                ->whereNotNull('payment_status')
                ->pluck('payment_status')
                ->filter()
                ->toArray();
                
            // If no specific statuses found, use defaults
            if (empty($paymentStatuses)) {
                $paymentStatuses = ['not_paid', 'pending', 'half_paid', 'paid'];
            }
            
            $paymentStatusData = collect();
            
            foreach ($paymentStatuses as $status) {
                $count = Customer::where('payment_status', $status)->count();
                $totalAmount = Customer::where('payment_status', $status)->sum('total_payment_amount');
                
                $paymentStatusData->push((object) [
                    'status' => $status,
                    'count' => $count,
                    'total_amount' => $totalAmount ?? 0
                ]);
            }

            // Get all unique commission statuses from customers
            $commissionStatuses = Customer::distinct('commission_status')
                ->whereNotNull('commission_status')
                ->pluck('commission_status')
                ->filter()
                ->toArray();
                
            // If no specific statuses found, use defaults
            if (empty($commissionStatuses)) {
                $commissionStatuses = ['not_applicable', 'not_paid', 'pending', 'paid'];
            }
            
            $commissionStatusData = collect();
            
            foreach ($commissionStatuses as $status) {
                $count = Customer::where('commission_status', $status)->count();
                $totalCommission = Customer::where('commission_status', $status)->sum('commission_amount');
                
                $commissionStatusData->push((object) [
                    'status' => $status,
                    'count' => $count,
                    'total_commission' => $totalCommission ?? 0
                ]);
            }

            // Calculate summary stats
            $totalPayments = Customer::sum('total_payment_amount');
            $totalCommissions = Customer::sum('commission_amount');
            $pendingPayments = Customer::whereIn('payment_status', ['pending', 'half_paid'])->count();
            $overduePayments = 0; // You don't have an overdue field, so this is 0

            // Calculate collection rate: paid / total
            $paidPayments = Customer::where('payment_status', 'paid')->sum('total_payment_amount');
            $paymentCollectionRate = $totalPayments > 0 ? 
                round(($paidPayments / $totalPayments) * 100, 2) : 0;

            return [
                'payment_status' => [
                    'labels' => $paymentStatusData->pluck('status')->toArray(),
                    'data' => $paymentStatusData->pluck('count')->toArray(),
                    'amounts' => $paymentStatusData->pluck('total_amount')->toArray(),
                    'colors' => $this->getPaymentStatusColors($paymentStatuses)
                ],
                'commission_status' => [
                    'labels' => $commissionStatusData->pluck('status')->toArray(),
                    'data' => $commissionStatusData->pluck('count')->toArray(),
                    'amounts' => $commissionStatusData->pluck('total_commission')->toArray(),
                    'colors' => $this->getCommissionStatusColors($commissionStatuses)
                ],
                'summary' => [
                    'total_payments' => $totalPayments,
                    'total_commissions' => $totalCommissions,
                    'pending_payments' => $pendingPayments,
                    'overdue_payments' => $overduePayments,
                    'payment_collection_rate' => $paymentCollectionRate
                ]
            ];

        } catch (\Exception $e) {
            Log::error("Error getting payment/commission data: " . $e->getMessage());
            return $this->getEmptyPaymentCommissionData();
        }
    }

    /**
     * Get colors for payment statuses
     */
    private function getPaymentStatusColors($statuses)
    {
        $colorMap = [
            'paid' => '#4CAF50',
            'half_paid' => '#FF9800',
            'pending' => '#FFB74D',
            'not_paid' => '#F44336',
            'overdue' => '#757575',
        ];
        
        $colors = [];
        foreach ($statuses as $status) {
            $colors[$status] = $colorMap[$status] ?? '#9E9E9E';
        }
        
        return $colors;
    }

    /**
     * Get colors for commission statuses
     */
    private function getCommissionStatusColors($statuses)
    {
        $colorMap = [
            'paid' => '#4CAF50',
            'pending' => '#2196F3',
            'not_paid' => '#FF9800',
            'not_applicable' => '#9E9E9E',
        ];
        
        $colors = [];
        foreach ($statuses as $status) {
            $colors[$status] = $colorMap[$status] ?? '#9E9E9E';
        }
        
        return $colors;
    }

    /**
     * Get empty payment commission data structure
     */
    protected function getEmptyPaymentCommissionData()
    {
        return [
            'payment_status' => [
                'labels' => ['not_paid', 'pending', 'half_paid', 'paid'],
                'data' => [0, 0, 0, 0],
                'amounts' => [0, 0, 0, 0],
                'colors' => [
                    'paid' => '#4CAF50',
                    'half_paid' => '#FF9800',
                    'pending' => '#FFB74D',
                    'not_paid' => '#F44336'
                ]
            ],
            'commission_status' => [
                'labels' => ['not_applicable', 'not_paid', 'pending', 'paid'],
                'data' => [0, 0, 0, 0],
                'amounts' => [0, 0, 0, 0],
                'colors' => [
                    'paid' => '#4CAF50',
                    'pending' => '#2196F3',
                    'not_paid' => '#FF9800',
                    'not_applicable' => '#9E9E9E'
                ]
            ],
            'summary' => [
                'total_payments' => 0,
                'total_commissions' => 0,
                'pending_payments' => 0,
                'overdue_payments' => 0,
                'payment_collection_rate' => 0
            ]
        ];
    }

    /**
     * Get customer conversion analytics - UPDATED TO MATCH YOUR CUSTOMER MODEL
     */
    protected function getCustomerAnalytics($user)
    {
        if (!$this->canViewResource($user, 'customers')) {
            return $this->getEmptyCustomerAnalytics();
        }

        try {
            // Get all customers count
            $totalCustomers = Customer::count();
            
            if ($totalCustomers === 0) {
                return $this->getEmptyCustomerAnalytics();
            }

            // Get counts by status - Using your actual status values from CustomerController
            $draftCount = Customer::where('status', 'draft')->count();
            $contractCreatedCount = Customer::where('status', 'contract_created')->count();
            $acceptedCount = Customer::where('status', 'accepted')->count();
            $rejectedCount = Customer::where('status', 'rejected')->count();

            // Map to conversion funnel (draft/contract_created = potential, accepted = approved, rejected = rejected)
            $potentialCount = $draftCount + $contractCreatedCount;
            $approvedCount = $acceptedCount;

            // Calculate rates based on potential customers (draft + contract_created + accepted + rejected)
            $totalProcessed = $potentialCount + $approvedCount + $rejectedCount;
            $approvalRate = $totalProcessed > 0 ? round(($approvedCount / $totalProcessed) * 100, 2) : 0;
            $rejectionRate = $totalProcessed > 0 ? round(($rejectedCount / $totalProcessed) * 100, 2) : 0;

            // Get today's conversions
            $todayConversions = Customer::select(
                DB::raw("COUNT(CASE WHEN status IN ('draft', 'contract_created') THEN 1 END) as potential_today"),
                DB::raw("COUNT(CASE WHEN status = 'accepted' THEN 1 END) as approved_today"),
                DB::raw("COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_today")
            )
            ->whereDate('created_at', Carbon::today())
            ->first();

            // Get total values (using total_payment_amount as value metric)
            $totalValuePotential = Customer::whereIn('status', ['draft', 'contract_created'])->sum('total_payment_amount') ?? 0;
            $totalValueApproved = Customer::where('status', 'accepted')->sum('total_payment_amount') ?? 0;

            // Get conversion timeline (last 30 days)
            $timelineData = Customer::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw("SUM(CASE WHEN status IN ('draft', 'contract_created') THEN 1 ELSE 0 END) as potential"),
                DB::raw("SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as approved"),
                DB::raw("SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected")
            )
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

            return [
                'conversion_data' => [
                    'labels' => ['potential', 'approved', 'rejected'],
                    'counts' => [$potentialCount, $approvedCount, $rejectedCount],
                    'values' => [$totalValuePotential, $totalValueApproved, 0],
                    'colors' => [
                        'potential' => '#2196F3',
                        'approved' => '#4CAF50',
                        'rejected' => '#F44336'
                    ]
                ],
                'timeline_data' => [
                    'dates' => $timelineData->pluck('date')->toArray(),
                    'potential' => $timelineData->pluck('potential')->toArray(),
                    'approved' => $timelineData->pluck('approved')->toArray(),
                    'rejected' => $timelineData->pluck('rejected')->toArray()
                ],
                'summary' => [
                    'total_customers' => $totalCustomers,
                    'potential_count' => $potentialCount,
                    'approved_count' => $approvedCount,
                    'rejected_count' => $rejectedCount,
                    'approval_rate' => $approvalRate,
                    'rejection_rate' => $rejectionRate,
                    'potential_today' => $todayConversions->potential_today ?? 0,
                    'approved_today' => $todayConversions->approved_today ?? 0,
                    'rejected_today' => $todayConversions->rejected_today ?? 0,
                    'total_value_potential' => $totalValuePotential,
                    'total_value_approved' => $totalValueApproved
                ]
            ];

        } catch (\Exception $e) {
            Log::error("Error getting customer analytics: " . $e->getMessage());
            return $this->getEmptyCustomerAnalytics();
        }
    }

    /**
     * Get empty customer analytics data structure
     */
    protected function getEmptyCustomerAnalytics()
    {
        return [
            'conversion_data' => [
                'labels' => ['potential', 'approved', 'rejected'],
                'counts' => [0, 0, 0],
                'values' => [0, 0, 0],
                'colors' => [
                    'potential' => '#2196F3',
                    'approved' => '#4CAF50',
                    'rejected' => '#F44336'
                ]
            ],
            'timeline_data' => [
                'dates' => [],
                'potential' => [],
                'approved' => [],
                'rejected' => []
            ],
            'summary' => [
                'total_customers' => 0,
                'potential_count' => 0,
                'approved_count' => 0,
                'rejected_count' => 0,
                'approval_rate' => 0,
                'rejection_rate' => 0,
                'potential_today' => 0,
                'approved_today' => 0,
                'rejected_today' => 0,
                'total_value_potential' => 0,
                'total_value_approved' => 0
            ]
        ];
    }

    /**
     * Get settings statistics for the settings page - UPDATED WITH ROLES
     */
    protected function getSettingsStatistics($user)
    {
        $stats = [];
        
        // Define settings resources that definitely exist
        $settingsResources = [
            'products' => [
                'model' => Product::class,
                'active_count_method' => 'where',
                'active_condition' => ['is_active', true]
            ],
            'industries' => [
                'model' => Industry::class,
                'active_count_method' => 'where',
                'active_condition' => ['is_active', true]
            ],
        ];

        // Conditionally add Role if the model exists
        if (class_exists('App\Models\Role') && \Schema::hasTable('roles')) {
            $settingsResources['roles'] = [
                'model' => Role::class,
                'active_count_method' => 'all', // Roles typically don't have active/inactive
                'active_condition' => []
            ];
        }

        foreach ($settingsResources as $resource => $config) {
            try {
                if ($this->canViewResource($user, $resource)) {
                    $model = $config['model'];
                    
                    if ($config['active_count_method'] === 'where') {
                        // Check if the active condition column exists
                        $table = (new $model)->getTable();
                        $hasActiveColumn = \Schema::hasColumn($table, $config['active_condition'][0]);
                        
                        $stats[$resource] = [
                            'total' => $model::count(),
                            'active' => $hasActiveColumn 
                                ? $model::where($config['active_condition'][0], $config['active_condition'][1])->count()
                                : $model::count(),
                        ];
                    } else {
                        // For resources without active/inactive status (like roles)
                        $stats[$resource] = [
                            'total' => $model::count(),
                            'active' => $model::count(), // All are considered active
                        ];
                    }
                } else {
                    $stats[$resource] = [
                        'total' => 0,
                        'active' => 0,
                    ];
                }
            } catch (\Exception $e) {
                Log::warning("Failed to get settings stats for {$resource}: " . $e->getMessage());
                $stats[$resource] = [
                    'total' => 0,
                    'active' => 0,
                ];
            }
        }

        return $stats;
    }

    /**
     * Get chart data for opportunities
     */
    protected function getChartData($user)
    {
        if (!$this->canViewResource($user, 'opportunities')) {
            return $this->getEmptyChartData();
        }

        $chartLabels = [];
        $chartData = [];
        
        // Get last 7 days including today
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $chartLabels[] = $date->format('M d');
            
            // Count opportunities for this specific date
            $count = Opportunity::whereDate('created_at', $date->format('Y-m-d'))->count();
            $chartData[] = $count;
        }

        $totalOpportunities = Opportunity::count();
        
        // If no opportunities in last 7 days but we have some overall, create demo data
        if (array_sum($chartData) === 0 && $totalOpportunities > 0) {
            return $this->getDemoChartData($chartLabels, $totalOpportunities);
        }

        // If we have actual data, use it
        if (array_sum($chartData) > 0) {
            return [
                'labels' => $chartLabels,
                'datasets' => [
                    [
                        'label' => 'Opportunities Created (Last 7 Days)',
                        'backgroundColor' => '#14b8a6',
                        'borderColor' => '#0d9488',
                        'data' => $chartData,
                    ]
                ]
            ];
        }

        // No opportunities at all - show empty state
        return $this->getEmptyChartData();
    }

    /**
     * Get demo chart data for visualization
     */
    protected function getDemoChartData($labels, $totalOpportunities)
    {
        // Create some sample data based on total opportunities
        $demoData = [];
        $remaining = $totalOpportunities;
        
        foreach ($labels as $index => $label) {
            if ($remaining > 0 && $index % 2 === 0) { // Distribute to every other day
                $maxForDay = min($remaining, rand(1, ceil($totalOpportunities / 2)));
                $demoData[] = $maxForDay;
                $remaining -= $maxForDay;
            } else {
                $demoData[] = 0;
            }
        }

        // Make sure we account for all opportunities
        if ($remaining > 0) {
            $demoData[count($demoData) - 1] += $remaining;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Opportunities Created (Last 7 Days) - Sample Distribution',
                    'backgroundColor' => '#14b8a6',
                    'borderColor' => '#0d9488',
                    'data' => $demoData,
                ]
            ]
        ];
    }

    /**
     * Get empty chart data
     */
    protected function getEmptyChartData()
    {
        $labels = [];
        for ($i = 6; $i >= 0; $i--) {
            $labels[] = Carbon::now()->subDays($i)->format('M d');
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Opportunities Created (Last 7 Days)',
                    'backgroundColor' => '#14b8a6',
                    'borderColor' => '#0d9488',
                    'data' => array_fill(0, 7, 0),
                ]
            ]
        ];
    }

    /**
     * Get notifications for user
     */
    protected function getNotifications($user)
    {
        try {
            $notifications = $user->unreadNotifications()
                ->take(10)
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'message' => $notification->data['message'] ?? 'New notification',
                        'type' => $notification->data['type'] ?? 'info',
                        'action' => $notification->data['action'] ?? 'created',
                        'created_at' => $notification->created_at->diffForHumans(),
                        'url' => $notification->data['url'] ?? '#',
                    ];
                });

            return $notifications;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get recent activities for notifications - UPDATED WITH ROLES
     */
    protected function getRecentActivities()
    {
        $activities = [];
        
        // Get recent users, products, opportunities, etc.
        $recentUsers = User::where('created_at', '>=', Carbon::now()->subDays(1))
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        foreach ($recentUsers as $user) {
            $activities[] = [
                'type' => 'user_created',
                'message' => "New user registered: {$user->name}",
                'time' => $user->created_at->diffForHumans(),
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'timestamp' => $user->created_at->toISOString()
            ];
        }

        // Get recent roles if Role model exists
        if (class_exists('App\Models\Role') && \Schema::hasTable('roles')) {
            $recentRoles = Role::where('created_at', '>=', Carbon::now()->subDays(1))
                ->orderBy('created_at', 'desc')
                ->take(3)
                ->get();

            foreach ($recentRoles as $role) {
                $activities[] = [
                    'type' => 'role_created',
                    'message' => "New role created: {$role->name}",
                    'time' => $role->created_at->diffForHumans(),
                    'user' => ['name' => 'System', 'email' => 'system@example.com'],
                    'timestamp' => $role->created_at->toISOString()
                ];
            }
        }

        // Get recent customers with conversion status - USING YOUR ACTUAL STATUS VALUES
        $recentCustomers = Customer::where('created_at', '>=', Carbon::now()->subDays(1))
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        foreach ($recentCustomers as $customer) {
            $statusLabel = match($customer->status) {
                'draft', 'contract_created' => 'Potential Customer',
                'accepted' => 'Approved Customer',
                'rejected' => 'Rejected Customer',
                default => 'Customer'
            };
            
            $activities[] = [
                'type' => 'customer_' . $customer->status,
                'message' => "{$statusLabel}: {$customer->name}",
                'time' => $customer->created_at->diffForHumans(),
                'user' => ['name' => 'Sales Team', 'email' => 'sales@example.com'],
                'timestamp' => $customer->created_at->toISOString(),
                'customer_status' => $customer->status
            ];
        }

        // Get recent opportunities
        $recentOpportunities = Opportunity::where('created_at', '>=', Carbon::now()->subDays(1))
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        foreach ($recentOpportunities as $opportunity) {
            $activities[] = [
                'type' => 'opportunity_created',
                'message' => "New opportunity created: {$opportunity->title}",
                'time' => $opportunity->created_at->diffForHumans(),
                'user' => ['name' => 'Sales Team', 'email' => 'sales@example.com'],
                'timestamp' => $opportunity->created_at->toISOString()
            ];
        }

        // Sort activities by timestamp
        usort($activities, function ($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        return array_slice($activities, 0, 10); // Return only latest 10 activities
    }

    /**
     * API endpoint for recent activities
     */
    public function getRecentActivitiesApi()
    {
        try {
            $activities = $this->getRecentActivities();
            
            return response()->json([
                'success' => true,
                'activities' => $activities,
                'timestamp' => now()->toISOString(),
                'total' => count($activities)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch activities',
                'error' => $e->getMessage(),
                'activities' => []
            ], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsRead()
    {
        try {
            $user = auth()->user();
            $user->unreadNotifications->markAsRead();

            return redirect()->back()->with('success', 'All notifications marked as read.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to mark notifications as read.');
        }
    }

    /**
     * Mark single notification as read
     */
    public function markNotificationRead($id)
    {
        try {
            $user = auth()->user();
            $notification = $user->notifications()->where('id', $id)->first();
            
            if ($notification) {
                $notification->markAsRead();
                return redirect()->back()->with('success', 'Notification marked as read.');
            }

            return redirect()->back()->with('error', 'Notification not found.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to mark notification as read.');
        }
    }

    /**
     * API endpoint for payment/commission data
     */
    public function getPaymentCommissionDataApi()
    {
        try {
            $user = auth()->user();
            $data = $this->getPaymentCommissionData($user);
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment/commission data',
                'error' => $e->getMessage(),
                'data' => $this->getEmptyPaymentCommissionData()
            ], 500);
        }
    }

    /**
     * API endpoint for customer analytics data
     */
    public function getCustomerAnalyticsApi()
    {
        try {
            $user = auth()->user();
            $data = $this->getCustomerAnalytics($user);
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch customer analytics',
                'error' => $e->getMessage(),
                'data' => $this->getEmptyCustomerAnalytics()
            ], 500);
        }
    }
}