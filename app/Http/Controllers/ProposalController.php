<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Models\User;
use App\Models\PotentialCustomer;
use App\Models\Customer;
use App\Models\RejectedOpportunity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ProposalStatusNotification;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use App\Services\NavigationService;
use App\Traits\HandlesPermissions;
use Illuminate\Support\Facades\DB;

class ProposalController extends Controller
{
    use HandlesPermissions;

    public function index()
    {
        $this->checkPermission('view proposals');

        try {
            $proposals = Proposal::with(['createdBy', 'potentialCustomer'])
                ->latest()
                ->paginate(12);

            $tables = NavigationService::getTablesForUser(auth()->user());
            $permissions = $this->getExtendedPermissions('proposals');

            return Inertia::render('Admin/Proposals/Index', [
                'proposals' => $proposals,
                'tables' => $tables,
                'permissions' => $permissions
            ]);

        } catch (\Exception $e) {
            \Log::error('Proposals Index Error: ' . $e->getMessage());
            
            return Inertia::render('Admin/Proposals/Index', [
                'proposals' => ['data' => [], 'links' => []],
                'tables' => NavigationService::getTablesForUser(auth()->user()),
                'permissions' => $this->getExtendedPermissions('proposals')
            ]);
        }
    }

    public function show($id)
    {
        $this->checkPermission('view proposals');

        try {
            $proposal = Proposal::with([
                'createdBy' => function($query) {
                    $query->select('id', 'name');
                },
                'potentialCustomer' => function($query) {
                    $query->select('id', 'potential_customer_name', 'email', 'phone', 'location');
                }
            ])->findOrFail($id);
            
            $tables = NavigationService::getTablesForUser(auth()->user());
            $permissions = $this->getExtendedPermissions('proposals');

            return Inertia::render('Admin/Proposals/Show', [
                'proposal' => $proposal,
                'tables' => $tables,
                'permissions' => $permissions,
            ]);

        } catch (\Exception $e) {
            \Log::error('Proposal Show Error: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Proposal not found.');
        }
    }

    public function edit($id)
    {
        $this->checkPermission('edit proposals');

        try {
            $proposal = Proposal::with(['createdBy', 'potentialCustomer'])->findOrFail($id);
            $tables = NavigationService::getTablesForUser(auth()->user());
            $potentialCustomers = PotentialCustomer::where('status', 'draft')->get(['id', 'potential_customer_name', 'email']);

            return Inertia::render('Admin/Proposals/Edit', [
                'proposal' => $proposal,
                'tables' => $tables,
                'potentialCustomers' => $potentialCustomers,
                'permissions' => $this->getExtendedPermissions('proposals')
            ]);

        } catch (\Exception $e) {
            \Log::error('Proposal Edit Error: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Proposal not found.');
        }
    }

    public function update(Request $request, $id)
    {
        $this->checkPermission('edit proposals');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|string|max:255',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        try {
            $proposal = Proposal::findOrFail($id);
            
            $data = $request->only(['title', 'description', 'price']);

            if ($request->hasFile('file')) {
                if ($proposal->file) {
                    Storage::disk('public')->delete($proposal->file);
                }
                $data['file'] = $request->file('file')->store('proposals', 'public');
            }

            $proposal->update($data);

            return redirect()->back()->with('success', 'Proposal updated successfully!');

        } catch (\Exception $e) {
            \Log::error('Proposal Update Error: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Failed to update proposal. Please try again.');
        }
    }

    public function destroy($id)
    {
        $this->checkPermission('delete proposals');

        try {
            $proposal = Proposal::findOrFail($id);
            
            if ($proposal->file) {
                Storage::disk('public')->delete($proposal->file);
            }
            
            $proposal->delete();

            return redirect()->back()->with('success', 'Proposal deleted successfully!');

        } catch (\Exception $e) {
            \Log::error('Proposal Delete Error: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Failed to delete proposal. Please try again.');
        }
    }

    public function approve($id)
    {
        $this->checkPermission('approve proposals');

        \Log::info('=== PROPOSAL APPROVE PROCESS STARTED ===');
        \Log::info('Proposal ID: ' . $id);
        \Log::info('User ID: ' . auth()->id());

        try {
            DB::beginTransaction();

            // Find proposal with customer relationship
            $proposal = Proposal::with('potentialCustomer')->findOrFail($id);
            \Log::info('Proposal found:', [
                'id' => $proposal->id,
                'title' => $proposal->title,
                'status' => $proposal->status,
                'potential_customer_id' => $proposal->potential_customer_id,
                'has_customer' => !is_null($proposal->potentialCustomer)
            ]);

            if ($proposal->potentialCustomer) {
                \Log::info('Potential customer found:', [
                    'id' => $proposal->potentialCustomer->id,
                    'name' => $proposal->potentialCustomer->potential_customer_name,
                    'email' => $proposal->potentialCustomer->email,
                    'status' => $proposal->potentialCustomer->status
                ]);
            }

            // Update proposal status to signed
            $proposal->update([
                'customer_approved_at' => now(),
                'customer_rejected_at' => null,
                'status' => 'signed',
                'is_rejected' => false,
                'customer_review' => 'Approved by system'
            ]);

            \Log::info('Proposal status updated to signed');

            // Process potential customer if exists
            if ($proposal->potentialCustomer) {
                $potentialCustomer = $proposal->potentialCustomer;
                
                // Check if customer already exists to avoid duplicates
                $existingCustomer = Customer::where('email', $potentialCustomer->email)
                    ->orWhere('name', $potentialCustomer->potential_customer_name)
                    ->first();

                if (!$existingCustomer) {
                    \Log::info('Creating new customer record');
                    
                    // Create customer record
                    $customerData = [
                        'name' => $potentialCustomer->potential_customer_name,
                        'email' => $potentialCustomer->email,
                        'phone' => $potentialCustomer->phone,
                        'location' => $potentialCustomer->location,
                        'remarks' => $potentialCustomer->remarks . ' - Approved from proposal: ' . $proposal->title,
                        'created_by' => auth()->id(),
                        'potential_customer_id' => $potentialCustomer->id,
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    
                    \Log::info('Customer data to create:', $customerData);
                    
                    $customer = Customer::create($customerData);
                    \Log::info('New customer created with ID: ' . $customer->id);
                } else {
                    \Log::info('Customer already exists with ID: ' . $existingCustomer->id);
                    
                    // Update existing customer with potential customer reference
                    $existingCustomer->update([
                        'potential_customer_id' => $potentialCustomer->id,
                        'remarks' => $existingCustomer->remarks . ' | Approved from proposal: ' . $proposal->title
                    ]);
                }

                // Update potential customer status to 'accepted'
                $potentialCustomer->update([
                    'status' => 'accepted',
                ]);
                \Log::info('Potential customer status updated to accepted');
            } else {
                \Log::warning('No potential customer associated with this proposal');
            }

            DB::commit();
            \Log::info('=== PROPOSAL APPROVE PROCESS COMPLETED SUCCESSFULLY ===');

            // Notify users who can view proposals
            $this->notifyStatusChange($proposal, 'approved', auth()->user()->name);

            return redirect('/admin/potential-customers')->with('success', 'Proposal approved successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('=== PROPOSAL APPROVE PROCESS FAILED ===');
            \Log::error('Error: ' . $e->getMessage());
            \Log::error('File: ' . $e->getFile());
            \Log::error('Line: ' . $e->getLine());
            \Log::error('Trace: ' . $e->getTraceAsString());
            
            return redirect('/admin/potential-customers')->with('error', 'Failed to approve proposal: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
{
    $this->checkPermission('reject proposals');

    \Log::info('=== PROPOSAL REJECT PROCESS STARTED ===');
    \Log::info('Proposal ID: ' . $id);
    \Log::info('User ID: ' . auth()->id());
    \Log::info('Reject reason: ' . ($request->reason ?? 'No reason provided'));

    try {
        DB::beginTransaction();

        $proposal = Proposal::with('potentialCustomer')->findOrFail($id);
        \Log::info('Proposal found for rejection:', [
            'id' => $proposal->id,
            'title' => $proposal->title,
            'status' => $proposal->status,
            'potential_customer_id' => $proposal->potential_customer_id
        ]);
        
        // Get the reason
        $reason = $request->reason ?? 'Rejected by user';
        
        // Update proposal status to rejected
        $proposal->update([
            'is_rejected' => true,
            'customer_rejected_at' => now(),
            'customer_approved_at' => null,
            'status' => 'unsigned',
            'customer_review' => $reason,
        ]);

        \Log::info('Proposal status updated to rejected');

        if ($proposal->potentialCustomer) {
            \Log::info('Creating rejected opportunity record');
            
            // Create rejected opportunity record
            RejectedOpportunity::create([
                'opportunity_id' => $proposal->id,
                'potential_customer_name' => $proposal->potentialCustomer->potential_customer_name,
                'email' => $proposal->potentialCustomer->email,
                'phone' => $proposal->potentialCustomer->phone ?? null,
                'location' => $proposal->potentialCustomer->location ?? null,
                'created_by' => auth()->id(),
                'remarks' => "Proposal: {$proposal->title} - Price: {$proposal->price}",
                'reason' => $reason,
                'rejected_from' => 'proposal',
                'original_id' => $proposal->potential_customer_id
            ]);

            \Log::info('Rejected opportunity record created');

            // Update potential customer status to 'rejected'
            $proposal->potentialCustomer->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejected_by' => auth()->id(),
                'rejection_reason' => $reason,
            ]);

            \Log::info('Potential customer status updated to rejected');
        }

        DB::commit();
        \Log::info('=== PROPOSAL REJECT PROCESS COMPLETED SUCCESSFULLY ===');

        // Notify users who can view proposals
        $this->notifyStatusChange($proposal, 'rejected', auth()->user()->name);

        return back()->with('success', 'Proposal rejected successfully!');

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('=== PROPOSAL REJECT PROCESS FAILED ===');
        \Log::error('Error: ' . $e->getMessage());
        \Log::error('File: ' . $e->getFile());
        \Log::error('Line: ' . $e->getLine());
        \Log::error('Trace: ' . $e->getTraceAsString());
        
        return back()->with('error', 'Failed to reject proposal: ' . $e->getMessage());
    }
}
    public function download($id)
    {
        $this->checkPermission('view proposals');

        try {
            $proposal = Proposal::findOrFail($id);

            if (!$proposal->file) {
                return redirect()->back()->with('error', 'No file available for download.');
            }

            $filePath = storage_path('app/public/' . $proposal->file);
            
            if (!file_exists($filePath)) {
                return redirect()->back()->with('error', 'File not found.');
            }

            $fileName = pathinfo($proposal->file, PATHINFO_BASENAME);
            
            return response()->download($filePath, $fileName);

        } catch (\Exception $e) {
            \Log::error('Proposal Download Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to download file. Please try again.');
        }
    }

    private function notifyStatusChange(Proposal $proposal, string $action, string $actorName)
    {
        try {
            // Get users who have permission to view proposals
            $users = User::whereHas('roles', function($q) {
                $q->whereHas('permissions', function($query) {
                    $query->where('name', 'view proposals');
                });
            })->get();

            foreach ($users as $user) {
                $user->notify(new ProposalStatusNotification(
                    $proposal, 
                    $action, 
                    $actorName,
                    $user->roles->first()->name
                ));
            }

        } catch (\Exception $e) {
            \Log::error('Proposal Notification Error: ' . $e->getMessage());
        }
    }
}