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

        // Get chart data
        $chartData = $this->getChartData($user);

        // Get top products
        $topProducts = $this->getTopProducts($user);

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
            'chartData' => $chartData,
            'topProducts' => $topProducts,
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
        
        // Define resources that definitely exist - REMOVED users and product_categories
        $resources = [
            'products' => Product::class,
            'customers' => Customer::class,
            'industries' => Industry::class,
            'opportunities' => Opportunity::class,
            'rejected_opportunities' => RejectedOpportunity::class,
            'contracts' => Contract::class,
            'proposals' => Proposal::class,
            // 'users' => User::class, // REMOVED
            // 'product_categories' => ProductCategory::class, // REMOVED
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
     * Get settings statistics for the settings page - UPDATED WITH ROLES
     */
    protected function getSettingsStatistics($user)
    {
        $stats = [];
        
        // Define settings resources that definitely exist - REMOVED users and product_categories
        $settingsResources = [
            // 'users' => [ // REMOVED
            //     'model' => User::class,
            //     'active_count_method' => 'where',
            //     'active_condition' => ['is_active', true]
            // ],
            'products' => [
                'model' => Product::class,
                'active_count_method' => 'where',
                'active_condition' => ['is_active', true]
            ],
            // 'product_categories' => [ // REMOVED
            //     'model' => ProductCategory::class,
            //     'active_count_method' => 'where', 
            //     'active_condition' => ['is_active', true]
            // ],
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
            return [
                'labels' => [],
                'datasets' => [
                    [
                        'label' => 'Opportunities',
                        'backgroundColor' => '#3B82F6',
                        'borderColor' => '#1D4ED8',
                        'data' => [],
                    ]
                ]
            ];
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

        return [
            'labels' => $chartLabels,
            'datasets' => [
                [
                    'label' => 'Opportunities Created (Last 7 Days)',
                    'backgroundColor' => '#3B82F6',
                    'borderColor' => '#1D4ED8',
                    'data' => $chartData,
                ]
            ]
        ];
    }

    /**
     * Get top products
     */
    protected function getTopProducts($user)
    {
        if (!$this->canViewResource($user, 'products')) {
            return [];
        }

        return Product::orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category,
                    'sales' => 0,
                ];
            });
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

        // Get recent products
        $recentProducts = Product::where('created_at', '>=', Carbon::now()->subDays(1))
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        foreach ($recentProducts as $product) {
            $activities[] = [
                'type' => 'product_created',
                'message' => "New product added: {$product->name}",
                'time' => $product->created_at->diffForHumans(),
                'user' => ['name' => 'System', 'email' => 'system@example.com'],
                'timestamp' => $product->created_at->toISOString()
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
}