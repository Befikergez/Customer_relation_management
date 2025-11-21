<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Application;
use Inertia\Inertia;

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
    ProductCategoryController
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
// Authenticated Routes
// ==========================
Route::middleware(['auth', 'verified'])->group(function () {

    // DASHBOARD
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Recent activities API route
   
    // ==========================
    // ADMIN ROUTES
    // ==========================
    Route::prefix('admin')->group(function () {

        // SETTINGS ROUTE - ADDED HERE INSIDE ADMIN PREFIX
        Route::get('/settings', [SettingsController::class, 'index'])->name('admin.settings');

        Route::prefix('users')->group(function () {
            
            // Resource routes
            Route::get('/', [UsersController::class, 'index'])->name('users.index');
            Route::get('/create', [UsersController::class, 'create'])->name('users.create');
            Route::post('/', [UsersController::class, 'store'])->name('users.store');
            
            // Parameterized routes LAST
            Route::get('/{user}', [UsersController::class, 'show'])->name('users.show');
            Route::get('/{user}/edit', [UsersController::class, 'edit'])->name('users.edit');
            Route::put('/{user}', [UsersController::class, 'update'])->name('users.update');
            Route::delete('/{user}', [UsersController::class, 'destroy'])->name('users.destroy');
            
            // Image removal route
            Route::post('/{user}/remove-image', [UsersController::class, 'removeImage'])->name('users.remove-image');
            
            // Bulk actions
            
        });

        // ROLES MANAGEMENT
        Route::prefix('roles')->group(function () {
           
            Route::get('/', [RolesController::class, 'index'])->name('roles.index');
            Route::get('/create', [RolesController::class, 'create'])->name('roles.create');
            Route::post('/', [RolesController::class, 'store'])->name('roles.store');
            Route::get('/{role}', [RolesController::class, 'show'])->name('roles.show');
            Route::get('/{role}/edit', [RolesController::class, 'edit'])->name('roles.edit');
            Route::put('/{role}', [RolesController::class, 'update'])->name('roles.update');
            Route::delete('/{role}', [RolesController::class, 'destroy'])->name('roles.destroy');
        });

        // Product Categories
        Route::prefix('product-categories')->group(function () {
           
            Route::get('/', [ProductCategoryController::class, 'index'])->name('product-categories.index');
            Route::get('/create', [ProductCategoryController::class, 'create'])->name('product-categories.create');
            Route::post('/', [ProductCategoryController::class, 'store'])->name('product-categories.store');
            Route::get('/{product_category}', [ProductCategoryController::class, 'show'])->name('product-categories.show');
            Route::get('/{product_category}/edit', [ProductCategoryController::class, 'edit'])->name('product-categories.edit');
            Route::put('/{product_category}', [ProductCategoryController::class, 'update'])->name('product-categories.update');
            Route::delete('/{product_category}', [ProductCategoryController::class, 'destroy'])->name('product-categories.destroy');
        });

        // PRODUCTS
        Route::prefix('products')->group(function () {
           
            Route::get('/', [ProductController::class, 'index'])->name('products.index');
            Route::get('/create', [ProductController::class, 'create'])->name('products.create');
            Route::post('/', [ProductController::class, 'store'])->name('products.store');
            Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
            Route::patch('/{product}', [ProductController::class, 'update'])->name('products.update');
            Route::delete('/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        });

        // INDUSTRIES
        Route::prefix('industries')->group(function () {
            Route::get('/export', [IndustryController::class, 'export'])->name('industries.export');
            Route::get('/', [IndustryController::class, 'index'])->name('industries.index');
            Route::get('/create', [IndustryController::class, 'create'])->name('industries.create');
            Route::post('/', [IndustryController::class, 'store'])->name('industries.store');
            Route::get('/{industry}/edit', [IndustryController::class, 'edit'])->name('industries.edit');
            Route::patch('/{industry}', [IndustryController::class, 'update'])->name('industries.update');
            Route::delete('/{industry}', [IndustryController::class, 'destroy'])->name('industries.destroy');
        });

        // CUSTOMERS
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::post('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/customers/{id}', [CustomerController::class, 'show'])->name('customers.show');
        Route::get('/customers/{id}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/customers/{id}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');
        Route::post('/customers/{id}/reject', [CustomerController::class, 'reject'])->name('customers.reject');

        // POTENTIAL CUSTOMERS
        Route::get('/potential-customers', [PotentialCustomerController::class, 'index'])->name('potential-customers.index');
        Route::get('/potential-customers/{id}', [PotentialCustomerController::class, 'show'])->name('potential-customers.show');
        Route::post('/potential-customers/{id}/approve', [PotentialCustomerController::class, 'approve'])->name('potential-customers.approve');
        Route::post('/potential-customers/{id}/reject', [PotentialCustomerController::class, 'reject'])->name('potential-customers.reject');
        Route::post('/potential-customers/{id}/status', [PotentialCustomerController::class, 'updateStatus'])->name('potential-customers.status');

        // OPPORTUNITIES
        Route::get('opportunities', [OpportunitiesController::class, 'index'])->name('opportunities.index');
        Route::get('opportunities/create', [OpportunitiesController::class, 'create'])->name('opportunities.create');
        Route::post('opportunities', [OpportunitiesController::class, 'store'])->name('opportunities.store');
        Route::get('opportunities/{id}/edit', [OpportunitiesController::class, 'edit'])->name('opportunities.edit');
        Route::patch('opportunities/{id}', [OpportunitiesController::class, 'update'])->name('opportunities.update');
        Route::delete('opportunities/{id}', [OpportunitiesController::class, 'destroy'])->name('opportunities.destroy');

        // Approve / Reject Opportunities
        Route::post('opportunities/{id}/approve', [OpportunitiesController::class, 'approve'])->name('opportunities.approve');
        Route::post('opportunities/{id}/reject', [OpportunitiesController::class, 'reject'])->name('opportunities.reject');

        // REJECTED OPPORTUNITIES
        Route::get('/rejected-opportunities', [RejectedOpportunitiesController::class, 'index'])->name('rejected-opportunities.index');
        Route::get('/rejected-opportunities/{id}', [RejectedOpportunitiesController::class, 'show'])->name('rejected-opportunities.show');
        Route::post('/rejected-opportunities/{id}/revert', [RejectedOpportunitiesController::class, 'revert'])->name('rejected-opportunities.revert');
        Route::delete('/rejected-opportunities/{id}', [RejectedOpportunitiesController::class, 'destroy'])->name('rejected-opportunities.destroy');

        // API routes for rejected opportunities
        Route::get('/api/rejected-opportunities/stats', [RejectedOpportunitiesController::class, 'getStats'])->name('rejected-opportunities.stats');

        // ==========================
        // CONTRACTS
        // ==========================
        Route::get('contracts', [ContractController::class, 'index'])->name('contracts.index');
        Route::get('contracts/create', [ContractController::class, 'create'])->name('contracts.create');
        Route::post('contracts', [ContractController::class, 'store'])->name('contracts.store');
        Route::get('contracts/{contract}', [ContractController::class, 'show'])->name('contracts.show');
        Route::get('contracts/{contract}/edit', [ContractController::class, 'edit'])->name('contracts.edit');
        Route::patch('contracts/{contract}', [ContractController::class, 'update'])->name('contracts.update');
        Route::delete('contracts/{contract}', [ContractController::class, 'destroy'])->name('contracts.destroy');

        // Contract Actions
        Route::post('contracts/{contract}/send', [ContractController::class, 'sendToCustomer'])->name('contracts.send');
        Route::post('contracts/{contract}/approve', [ContractController::class, 'approve'])->name('contracts.approve');
        Route::post('contracts/{contract}/reject', [ContractController::class, 'reject'])->name('contracts.reject');
        Route::patch('contracts/{contract}/review', [ContractController::class, 'updateManagerReview'])->name('contracts.review');
        Route::get('contracts/{contract}/download', [ContractController::class, 'download'])->name('contracts.download');

        // PROPOSALS
        Route::get('/proposals', [ProposalController::class, 'index'])->name('proposals.index');
        Route::get('/proposals/create', [ProposalController::class, 'create'])->name('proposals.create');
        Route::post('/proposals', [ProposalController::class, 'store'])->name('proposals.store');
        Route::get('/proposals/{proposal}', [ProposalController::class, 'show'])->name('proposals.show');
        Route::get('/proposals/{proposal}/edit', [ProposalController::class, 'edit'])->name('proposals.edit');
        Route::put('/proposals/{proposal}', [ProposalController::class, 'update'])->name('proposals.update');
        Route::patch('/proposals/{proposal}', [ProposalController::class, 'update']);
        Route::delete('/proposals/{proposal}', [ProposalController::class, 'destroy'])->name('proposals.destroy');
        
        // Additional custom routes for proposal actions
        Route::post('/proposals/{proposal}/approve', [ProposalController::class, 'approve'])->name('proposals.approve');
        Route::post('/proposals/{proposal}/reject', [ProposalController::class, 'reject'])->name('proposals.reject');
        Route::post('/proposals/{proposal}/customer-approve', [ProposalController::class, 'customerApprove'])->name('proposals.customer-approve');
        Route::post('/proposals/{proposal}/customer-reject', [ProposalController::class, 'customerReject'])->name('proposals.customer-reject');
        Route::post('/proposals/{proposal}/manager-review', [ProposalController::class, 'addManagerReview'])->name('proposals.manager-review');
        Route::get('/proposals/{proposal}/download', [ProposalController::class, 'download'])->name('proposals.download');

        // NOTIFICATIONS
        Route::post('/notifications/mark-as-read', [DashboardController::class, 'markAllNotificationsRead'])
            ->name('notifications.read');
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