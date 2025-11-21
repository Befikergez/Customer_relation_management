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
                ->paginate(10);

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

    public function create()
    {
        $this->checkPermission('create proposals');

        $tables = NavigationService::getTablesForUser(auth()->user());
        $potentialCustomers = PotentialCustomer::where('status', 'draft')->get(['id', 'potential_customer_name', 'email']);

        return Inertia::render('Admin/Proposals/Create', [
            'tables' => $tables,
            'potentialCustomers' => $potentialCustomers,
            'permissions' => $this->getExtendedPermissions('proposals')
        ]);
    }

    public function store(Request $request)
    {
        $this->checkPermission('create proposals');

        $validated = $request->validate([
            'potential_customer_id' => 'required|exists:potential_customers,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|string|max:255',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        try {
            $data = $request->only(['potential_customer_id', 'title', 'description', 'price']);
            $data['created_by'] = auth()->id();
            $data['status'] = 'unsigned';
            $data['is_rejected'] = false;

            if ($request->hasFile('file')) {
                $data['file'] = $request->file('file')->store('proposals', 'public');
            }

            $proposal = Proposal::create($data);

            // Notify users who can view proposals
            $this->notifyStatusChange($proposal, 'created', auth()->user()->name);

            return redirect()->route('proposals.index')->with('success', 'Proposal created successfully!');

        } catch (\Exception $e) {
            \Log::error('Proposal Store Error: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to create proposal. Please try again.')
                ->withInput();
        }
    }

    public function show($id)
    {
        $this->checkPermission('view proposals');

        try {
            $proposal = Proposal::with(['createdBy', 'potentialCustomer'])->findOrFail($id);
            $tables = NavigationService::getTablesForUser(auth()->user());
            $permissions = $this->getExtendedPermissions('proposals');

            return Inertia::render('Admin/Proposals/Show', [
                'proposal' => $proposal,
                'tables' => $tables,
                'permissions' => $permissions,
            ]);

        } catch (\Exception $e) {
            \Log::error('Proposal Show Error: ' . $e->getMessage());
            
            return redirect()->route('proposals.index')
                ->with('error', 'Proposal not found.');
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
            
            return redirect()->route('proposals.index')
                ->with('error', 'Proposal not found.');
        }
    }

    public function update(Request $request, $id)
    {
        $this->checkPermission('edit proposals');

        $validated = $request->validate([
            'potential_customer_id' => 'required|exists:potential_customers,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|string|max:255',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        try {
            $proposal = Proposal::findOrFail($id);
            
            $data = $request->only(['potential_customer_id', 'title', 'description', 'price']);

            if ($request->hasFile('file')) {
                if ($proposal->file) {
                    Storage::disk('public')->delete($proposal->file);
                }
                $data['file'] = $request->file('file')->store('proposals', 'public');
            }

            $proposal->update($data);

            return redirect()->route('proposals.index')->with('success', 'Proposal updated successfully!');

        } catch (\Exception $e) {
            \Log::error('Proposal Update Error: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to update proposal. Please try again.')
                ->withInput();
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

            return redirect()->route('proposals.index')->with('success', 'Proposal deleted successfully!');

        } catch (\Exception $e) {
            \Log::error('Proposal Delete Error: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Failed to delete proposal. Please try again.');
        }
    }

    public function approve($id)
    {
        $this->checkPermission('approve proposals');

        try {
            $proposal = Proposal::findOrFail($id);
            
            $proposal->update([
                'customer_approved_at' => now(),
                'customer_rejected_at' => null,
                'status' => 'signed',
                'is_rejected' => false,
                'customer_review' => 'Approved by customer'
            ]);

            // Notify users who can view proposals
            $this->notifyStatusChange($proposal, 'approved', auth()->user()->name);

            return redirect()->back()->with('success', 'Proposal approved and marked as signed!');

        } catch (\Exception $e) {
            \Log::error('Proposal Approve Error: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Failed to approve proposal. Please try again.');
        }
    }

    public function reject(Request $request, $id)
    {
        $this->checkPermission('reject proposals');

        try {
            DB::beginTransaction();

            $updated = DB::table('proposals')
                ->where('id', $id)
                ->update([
                    'is_rejected' => 1,
                    'customer_rejected_at' => now(),
                    'customer_approved_at' => null,
                    'customer_review' => 'Rejected by user',
                    'updated_at' => now()
                ]);

            if ($updated === 0) {
                throw new \Exception("No rows were updated for proposal ID: {$id}");
            }

            $proposal = Proposal::with('potentialCustomer')->find($id);

            if ($proposal && $proposal->potentialCustomer) {
                RejectedOpportunity::create([
                    'opportunity_id' => $proposal->id,
                    'potential_customer_name' => $proposal->potentialCustomer->potential_customer_name,
                    'email' => $proposal->potentialCustomer->email,
                    'phone' => $proposal->potentialCustomer->phone ?? null,
                    'location' => $proposal->potentialCustomer->location ?? null,
                    'created_by' => auth()->id(),
                    'remarks' => "Proposal: {$proposal->title} - Price: {$proposal->price}",
                    'reason' => 'Rejected by user',
                    'rejected_from' => 'proposal',
                    'original_id' => $proposal->potential_customer_id
                ]);

                $proposal->potentialCustomer->update(['status' => 'rejected']);
            }

            DB::commit();

            if ($proposal) {
                // Notify users who can view proposals
                $this->notifyStatusChange($proposal, 'rejected', auth()->user()->name);
            }

            return redirect()->route('proposals.index')->with('success', 'Proposal rejected successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Proposal Reject Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to reject proposal.');
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