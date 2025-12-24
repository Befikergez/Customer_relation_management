<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Application;
use Inertia\Inertia;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\{
    DashboardController,
    ProductController,
    CustomerController,
    IndustryController,
    OpportunitiesController,
    PotentialCustomerController,
    RejectedOpportunitiesController,
    ContractController,
    ProposalController,
    UsersController,
    RolesController,
    ProductCategoryController,
    PaymentController,
    DebugController,
    RoleCommissionSettingController
};

// ==========================
// Public Pages
// ==========================
Route::get('/', fn() => Inertia::render('Welcome', [
    'canLogin' => Route::has('login'),
    'canRegister' => Route::has('register'),
    'laravelVersion' => Application::VERSION,
    'phpVersion' => PHP_VERSION,
]))->name('welcome');

Route::get('/features', fn() => Inertia::render('Features'))->name('features');
Route::get('/contacts', fn() => Inertia::render('Contacts'))->name('contacts');

// ==========================
// Debug/Test Routes (Public for testing)
// ==========================
Route::get('/test-auth', function () {
    return response()->json([
        'authenticated' => auth()->check(),
        'user_id' => auth()->id(),
        'user_email' => auth()->check() ? auth()->user()->email : null,
        'session_id' => session()->getId(),
    ]);
});

// ==========================
// Authenticated Routes
// ==========================
Route::middleware(['auth'])->group(function () {

    // DASHBOARD
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ==========================
    // ADMIN ROUTES
    // ==========================
    Route::prefix('admin')->name('admin.')->group(function () {
        
        // DEBUG ROUTES (with auth)
        Route::get('/debug/customers', [DebugController::class, 'debugCustomers'])->name('debug.customers');
        Route::get('/debug/customer-count', [DebugController::class, 'customerCount'])->name('debug.customer-count');
        Route::get('/debug/sync-check', [DebugController::class, 'syncCheck'])->name('debug.sync-check');
        Route::get('/debug/permissions', [DebugController::class, 'debugPermissions'])->name('debug.permissions');

        // SETTINGS
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
        Route::get('/search', [SearchController::class, 'index'])->name('search');

        // ROLE COMMISSION SETTINGS
        Route::prefix('role-commission-settings')->name('role-commission-settings.')->group(function () {
            Route::get('/', [RoleCommissionSettingController::class, 'index'])->name('index');
            Route::post('/', [RoleCommissionSettingController::class, 'store'])->name('store');
            Route::put('/{setting}', [RoleCommissionSettingController::class, 'update'])->name('update');
            Route::delete('/{setting}', [RoleCommissionSettingController::class, 'destroy'])->name('destroy');
        });

        // USERS MANAGEMENT
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UsersController::class, 'index'])->name('index');
            Route::get('/create', [UsersController::class, 'create'])->name('create');
            Route::post('/', [UsersController::class, 'store'])->name('store');
            Route::get('/{user}', [UsersController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [UsersController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UsersController::class, 'update'])->name('update');
            Route::delete('/{user}', [UsersController::class, 'destroy'])->name('destroy');
            Route::post('/{user}/remove-image', [UsersController::class, 'removeImage'])->name('remove-image');
            Route::post('/{user}/update-commission', [UsersController::class, 'updateCommission'])->name('update-commission');
            Route::get('/commission-settings', [UsersController::class, 'getCommissionSettings'])->name('commission-settings');
            Route::get('/with-commission', [UsersController::class, 'getUsersWithCommission'])->name('with-commission');
        });

        // ROLES MANAGEMENT
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [RolesController::class, 'index'])->name('index');
            Route::get('/create', [RolesController::class, 'create'])->name('create');
            Route::post('/', [RolesController::class, 'store'])->name('store');
            Route::get('/{role}', [RolesController::class, 'show'])->name('show');
            Route::get('/{role}/edit', [RolesController::class, 'edit'])->name('edit');
            Route::put('/{role}', [RolesController::class, 'update'])->name('update');
            Route::delete('/{role}', [RolesController::class, 'destroy'])->name('destroy');
        });

        // PRODUCT CATEGORIES
        Route::prefix('product-categories')->name('product-categories.')->group(function () {
            Route::get('/', [ProductCategoryController::class, 'index'])->name('index');
            Route::get('/create', [ProductCategoryController::class, 'create'])->name('create');
            Route::post('/', [ProductCategoryController::class, 'store'])->name('store');
            Route::get('/{product_category}', [ProductCategoryController::class, 'show'])->name('show');
            Route::get('/{product_category}/edit', [ProductController::class, 'edit'])->name('edit');
            Route::put('/{product_category}', [ProductCategoryController::class, 'update'])->name('update');
            Route::delete('/{product_category}', [ProductCategoryController::class, 'destroy'])->name('destroy');
        });

        // PRODUCTS
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [ProductController::class, 'index'])->name('index');
            Route::get('/create', [ProductController::class, 'create'])->name('create');
            Route::post('/', [ProductController::class, 'store'])->name('store');
            Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
            Route::patch('/{product}', [ProductController::class, 'update'])->name('update');
            Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
        });

        // INDUSTRIES
        Route::prefix('industries')->name('industries.')->group(function () {
            Route::get('/export', [IndustryController::class, 'export'])->name('export');
            Route::get('/', [IndustryController::class, 'index'])->name('index');
            Route::get('/create', [IndustryController::class, 'create'])->name('create');
            Route::post('/', [IndustryController::class, 'store'])->name('store');
            Route::get('/{industry}/edit', [IndustryController::class, 'edit'])->name('edit');
            Route::patch('/{industry}', [IndustryController::class, 'update'])->name('update');
            Route::delete('/{industry}', [IndustryController::class, 'destroy'])->name('destroy');
        });

        // ==========================
        // CUSTOMERS ROUTES - UPDATED WITH GET REJECT FORM
        // ==========================
        Route::prefix('customers')->name('customers.')->group(function () {
            // Main customers list - This is the route for /admin/customers
            Route::get('/', [CustomerController::class, 'index'])->name('index');
            
            // Customer creation
            Route::get('/create', [CustomerController::class, 'create'])->name('create');
            Route::post('/', [CustomerController::class, 'store'])->name('store');
            
            // Customer CRUD
            Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
            Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
            Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
            Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');
            
            // Customer approval/rejection
            Route::post('/{customer}/approve', [CustomerController::class, 'approve'])->name('approve');
            
            // ADDED: GET route for reject form
            Route::get('/{customer}/reject', [CustomerController::class, 'showRejectForm'])->name('reject.form');
            // POST route for reject action
            Route::post('/{customer}/reject', [CustomerController::class, 'reject'])->name('reject');
            
            // NEW API ROUTES FOR INLINE EDITING
            Route::post('/{customer}/update-payment-info', [CustomerController::class, 'updatePaymentInfo'])->name('update-payment-info');
            Route::post('/{customer}/update-commission-info', [CustomerController::class, 'updateCommissionInfo'])->name('update-commission-info');
            
            // Get commission users
            Route::get('/{customer}/commission-users', [CustomerController::class, 'getCommissionUsers'])->name('commission-users');
            
            // From potential customer
            Route::post('/create-from-potential/{potentialCustomerId}', [CustomerController::class, 'createFromPotential'])->name('create-from-potential');
            
            // Payment management
            Route::get('/{customer}/payments/create', [CustomerController::class, 'createPayment'])->name('payments.create');
            Route::get('/{customer}/payments', [PaymentController::class, 'customerPayments'])->name('payments.index');
            
            // Payment status updates
            Route::post('/{customer}/update-payment-status', [CustomerController::class, 'updatePaymentStatus'])->name('update-payment-status');
            Route::post('/{customer}/update-commission-status', [CustomerController::class, 'updateCommissionStatus'])->name('update-commission-status');
            
            // Location filter routes
            Route::get('/city/{cityId}', [CustomerController::class, 'getByCity'])->name('by-city');
            Route::get('/subcity/{subcityId}', [CustomerController::class, 'getBySubcity'])->name('by-subcity');
            Route::get('/cities/{cityId}/subcities', [CustomerController::class, 'getSubcitiesByCity'])->name('subcities-by-city');
            Route::get('/location-data', [CustomerController::class, 'getLocationData'])->name('location-data');
            Route::get('/filter-by-location', [CustomerController::class, 'filterByLocation'])->name('filter-by-location');
            
            // Create contract from customer
            Route::post('/{customer}/contracts', [ContractController::class, 'store'])->name('contracts.store');
            Route::get('/{customer}/proposals', [CustomerController::class, 'getCustomerProposals'])->name('proposals');
        });

        // POTENTIAL CUSTOMERS
        Route::prefix('potential-customers')->name('potential-customers.')->group(function () {
            Route::get('/', [PotentialCustomerController::class, 'index'])->name('index');
            Route::get('/create', [PotentialCustomerController::class, 'create'])->name('create');
            Route::post('/', [PotentialCustomerController::class, 'store'])->name('store');
            Route::get('/{id}', [PotentialCustomerController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [PotentialCustomerController::class, 'edit'])->name('edit');
            Route::put('/{id}', [PotentialCustomerController::class, 'update'])->name('update');
            Route::delete('/{id}', [PotentialCustomerController::class, 'destroy'])->name('destroy');
            
            // CRITICAL FIX: Approve and reject routes must be POST
            Route::post('/{id}/approve', [PotentialCustomerController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [PotentialCustomerController::class, 'reject'])->name('reject');
            Route::post('/{id}/approve-with-payment', [PotentialCustomerController::class, 'approveWithPayment'])->name('approve-with-payment');
            
            // Proposals
            Route::post('/{id}/create-proposal', [PotentialCustomerController::class, 'createProposal'])->name('create-proposal');
            Route::put('/{customerId}/proposals/{proposalId}/status', [PotentialCustomerController::class, 'updateProposalStatus'])->name('update-proposal-status');
            
            // City/Subcity filters
            Route::get('/city/{cityId}', [PotentialCustomerController::class, 'getByCity'])->name('by-city');
            Route::get('/subcity/{subcityId}', [PotentialCustomerController::class, 'getBySubcity'])->name('by-subcity');
            
            // Subcities by city
            Route::get('/cities/{cityId}/subcities', [PotentialCustomerController::class, 'getSubcitiesByCity'])->name('subcities-by-city');
            
            // Dropdown list
            Route::get('/dropdown/list', [PotentialCustomerController::class, 'getPotentialCustomers'])->name('dropdown');
            
            // Payment routes for potential customers
            Route::get('/{id}/payments', [PaymentController::class, 'index'])->name('payments.index');
            Route::get('/{id}/payments/create', [PaymentController::class, 'create'])->name('payments.create');
            Route::post('/{id}/payments', [PaymentController::class, 'store'])->name('payments.store');
            Route::get('/{id}/payments/{payment}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
            Route::put('/{id}/payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');
            Route::delete('/{id}/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
        });

        // OPPORTUNITIES
        Route::prefix('opportunities')->name('opportunities.')->group(function () {
            Route::get('/', [OpportunitiesController::class, 'index'])->name('index');
            Route::get('/create', [OpportunitiesController::class, 'create'])->name('create');
            Route::post('/', [OpportunitiesController::class, 'store'])->name('store');
            Route::get('/{id}', [OpportunitiesController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [OpportunitiesController::class, 'edit'])->name('edit');
            Route::put('/{id}', [OpportunitiesController::class, 'update'])->name('update');
            Route::delete('/{id}', [OpportunitiesController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/approve', [OpportunitiesController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [OpportunitiesController::class, 'reject'])->name('reject');
        });

        // REJECTED OPPORTUNITIES
        Route::prefix('rejected-opportunities')->name('rejected-opportunities.')->group(function () {
            Route::get('/', [RejectedOpportunitiesController::class, 'index'])->name('index');
            Route::get('/{id}', [RejectedOpportunitiesController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [RejectedOpportunitiesController::class, 'edit'])->name('edit');
            Route::put('/{id}', [RejectedOpportunitiesController::class, 'update'])->name('update');
            Route::post('/{id}/revert', [RejectedOpportunitiesController::class, 'revert'])->name('revert');
            Route::delete('/{id}', [RejectedOpportunitiesController::class, 'destroy'])->name('destroy');
            Route::get('/api/stats', [RejectedOpportunitiesController::class, 'getStats'])->name('stats');
        });

        // CONTRACTS ROUTES - FIXED WITH BOTH PUT AND PATCH
        Route::prefix('contracts')->name('contracts.')->group(function () {
            // Main contracts page
            Route::get('/', [ContractController::class, 'index'])->name('index');
            
            // Contract creation
            Route::get('/create', [ContractController::class, 'create'])->name('create');
            
            // Contract CRUD operations - Support both PUT and PATCH
            Route::post('/', [ContractController::class, 'store'])->name('store');
            Route::get('/{contract}', [ContractController::class, 'show'])->name('show');
            Route::get('/{contract}/edit', [ContractController::class, 'edit'])->name('edit');
            Route::patch('/{contract}', [ContractController::class, 'update'])->name('update'); // PATCH for Vue router.patch()
            Route::put('/{contract}', [ContractController::class, 'update'])->name('update.put'); // PUT for form submissions
            Route::delete('/{contract}', [ContractController::class, 'destroy'])->name('destroy');
            
            // Contract status actions
            Route::post('/{contract}/accept', [ContractController::class, 'accept'])->name('accept');
            Route::post('/{contract}/reject', [ContractController::class, 'reject'])->name('reject');
            Route::post('/{contract}/mark-unsigned', [ContractController::class, 'markAsUnsigned'])->name('mark-unsigned');
            Route::get('/{contract}/download', [ContractController::class, 'download'])->name('download');
            
            // Customer contracts API
            Route::get('/customer/{customerId}', [ContractController::class, 'getCustomerContracts'])->name('customer-contracts');
            
            // Contract modal data API
            Route::get('/{customer}/customer-proposals', [ContractController::class, 'getCustomerProposals'])->name('customer-proposals');
        });

        // PROPOSALS
        Route::prefix('proposals')->name('proposals.')->group(function () {
            Route::get('/', [ProposalController::class, 'index'])->name('index');
            Route::get('/create', [ProposalController::class, 'create'])->name('create');
            Route::post('/', [ProposalController::class, 'store'])->name('store');
            Route::get('/{proposal}', [ProposalController::class, 'show'])->name('show');
            Route::get('/{proposal}/edit', [ProposalController::class, 'edit'])->name('edit');
            Route::put('/{proposal}', [ProposalController::class, 'update'])->name('update');
            Route::delete('/{proposal}', [ProposalController::class, 'destroy'])->name('destroy');
            Route::post('/{proposal}/approve', [ProposalController::class, 'approve'])->name('approve');
            Route::post('/{proposal}/reject', [ProposalController::class, 'reject'])->name('reject');
            Route::get('/{proposal}/download', [ProposalController::class, 'download'])->name('download');
        });

        // PAYMENT ROUTES FOR POTENTIAL CUSTOMERS
        Route::prefix('potential-customers/{id}/payments')->name('potential-customers.payments.')->group(function () {
            Route::get('/', [PaymentController::class, 'index'])->name('index');
            Route::get('/create', [PaymentController::class, 'create'])->name('create');
            Route::post('/', [PaymentController::class, 'store'])->name('store');
            Route::get('/{payment}/edit', [PaymentController::class, 'edit'])->name('edit');
            Route::put('/{payment}', [PaymentController::class, 'update'])->name('update');
            Route::delete('/{payment}', [PaymentController::class, 'destroy'])->name('destroy');
        });

        // PAYMENT ROUTES FOR REGULAR CUSTOMERS
        Route::prefix('customers/{customer}/payments')->name('customers.payments.')->group(function () {
            Route::get('/', [PaymentController::class, 'customerPayments'])->name('index');
            Route::get('/create', [PaymentController::class, 'createForCustomer'])->name('create');
            Route::post('/', [PaymentController::class, 'store'])->name('store');
            Route::get('/{payment}/edit', [PaymentController::class, 'edit'])->name('edit');
            Route::put('/{payment}', [PaymentController::class, 'update'])->name('update');
            Route::delete('/{payment}', [PaymentController::class, 'destroy'])->name('destroy');
        });

        // GLOBAL PAYMENT ROUTES - FIXED: Added missing DELETE and EDIT routes
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [PaymentController::class, 'index'])->name('index');
            Route::get('/recent', [PaymentController::class, 'recent'])->name('recent');
            Route::get('/upcoming', [PaymentController::class, 'upcoming'])->name('upcoming');
            Route::get('/overdue', [PaymentController::class, 'overdue'])->name('overdue');
            Route::get('/summary', [PaymentController::class, 'summary'])->name('summary');
            
            // NEW PAYMENT TRANSFER ROUTE
            Route::post('/transfer/{customerId}', [PaymentController::class, 'transferPayments'])->name('transfer');
            
            // Payment commission routes
            Route::post('/{payment}/mark-commission-as-paid', [PaymentController::class, 'markCommissionAsPaid'])->name('mark-commission-as-paid');
            Route::post('/{payment}/mark-commission-as-unpaid', [PaymentController::class, 'markCommissionAsUnpaid'])->name('mark-commission-as-unpaid');
            
            // Sync payments route
            Route::post('/sync-to-customer/{id}', [PaymentController::class, 'syncPaymentsToCustomer'])
                ->name('sync-to-customer');
            
            // CRITICAL FIX: Add missing global payment routes for individual payments
            // These are needed when accessing payments directly without customer context
            Route::get('/{payment}/edit', [PaymentController::class, 'globalEdit'])->name('edit');
            Route::put('/{payment}', [PaymentController::class, 'globalUpdate'])->name('update');
            Route::delete('/{payment}', [PaymentController::class, 'globalDestroy'])->name('destroy');
            
            // Optionally add a show route if needed
            Route::get('/{payment}', [PaymentController::class, 'show'])->name('show');
        });

        // ADMIN TEST ROUTE (for debugging)
        Route::get('/test-route', function() {
            return response()->json([
                'message' => 'Admin test route works',
                'authenticated' => auth()->check(),
                'user_id' => auth()->id(),
                'user_email' => auth()->check() ? auth()->user()->email : null,
            ]);
        })->name('test');
    });
});

// ==========================
// Authentication
// ==========================
require __DIR__.'/auth.php';

Route::post('/logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])
    ->name('logout');

// ==========================
// Force Logout
// ==========================
Route::match(['get', 'post'], '/force-logout', function () {
    Auth::logout();
    return redirect('/');
})->name('force.logout');

// ==========================
// Fallback route for debugging
// ==========================
Route::fallback(function () {
    return response()->json([
        'error' => 'Route not found',
        'request_url' => request()->fullUrl(),
        'request_method' => request()->method(),
        'authenticated' => auth()->check(),
        'user_id' => auth()->id(),
    ], 404);
});

// ==========================
// Debug Routes (Keep for debugging)
// ==========================
Route::get('/debug-session', function() {
    return response()->json([
        'session_id' => session()->getId(),
        'session_data' => session()->all(),
        'auth_check' => auth()->check(),
        'auth_user' => auth()->user() ? [
            'id' => auth()->user()->id,
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
        ] : null,
        'cookies' => request()->cookie(),
    ]);
});

Route::get('/debug/my-permissions', function() {
    $user = auth()->user();
    
    if (!$user) {
        return response()->json(['error' => 'Not authenticated']);
    }
    
    // Check specific permission
    $canCreateContracts = $user->can('create contracts');
    
    // Get all permissions
    $allPermissions = $user->getAllPermissions()->pluck('name');
    
    // Check via gate
    $gateCheck = app(\Illuminate\Contracts\Auth\Access\Gate::class)->allows('create contracts');
    
    return response()->json([
        'user_id' => $user->id,
        'user_name' => $user->name,
        'user_email' => $user->email,
        'user_roles' => $user->roles->pluck('name'),
        'can_create_contracts_direct' => $canCreateContracts,
        'can_create_contracts_gate' => $gateCheck,
        'all_permissions' => $allPermissions,
        'all_roles' => $user->roles->pluck('name'),
        'has_any_role' => $user->hasAnyRole(['admin', 'super-admin', 'manager']),
        'is_super_admin' => $user->hasRole('super-admin'),
        'session_id' => session()->getId()
    ]);
})->middleware('auth');

Route::get('/debug/handles-permissions-trait', function() {
    $traitPath = app_path('Traits/HandlesPermissions.php');
    
    if (!file_exists($traitPath)) {
        return response()->json(['error' => 'Trait file not found']);
    }
    
    $content = file_get_contents($traitPath);
    
    // Return just the checkPermission method
    preg_match('/protected function checkPermission.*?\n    \}/s', $content, $matches);
    
    return response()->json([
        'trait_exists' => true,
        'checkPermission_method' => $matches[0] ?? 'Method not found',
        'full_trait' => $content
    ]);
})->middleware('auth');

Route::get('/debug/role-check', function() {
    $user = auth()->user();
    
    return response()->json([
        'user_id' => $user->id,
        'user_name' => $user->name,
        'all_roles' => $user->getRoleNames(),
        'hasRole_superadmin' => $user->hasRole('superadmin'),
        'hasRole_super-admin' => $user->hasRole('super-admin'),
        'hasRole_admin' => $user->hasRole('admin'),
        'hasRole_administrator' => $user->hasRole('administrator'),
        'hasExactRole' => $user->roles->pluck('name'),
        'role_names_case_sensitive' => $user->roles->map(function($role) {
            return [
                'name' => $role->name,
                'name_lower' => strtolower($role->name),
                'guard_name' => $role->guard_name
            ];
        })
    ]);
})->middleware('auth');

Route::get('/debug/customer/{id}', function($id) {
    $customer = \App\Models\Customer::find($id);
    
    if (!$customer) {
        return response()->json(['error' => 'Customer not found']);
    }
    
    return response()->json([
        'id' => $customer->id,
        'name' => $customer->name,
        'status' => $customer->status,
        'rejected_at' => $customer->rejected_at,
        'rejection_reason' => $customer->rejection_reason,
        'rejected_by' => $customer->rejected_by,
        'created_at' => $customer->created_at,
        'updated_at' => $customer->updated_at,
    ]);
})->middleware('auth');