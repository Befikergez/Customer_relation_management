<?php

namespace App\Notifications;

use App\Models\Proposal;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ProposalStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Proposal $proposal,
        public string $action,
        public string $actorName
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->getSubject();
        $message = $this->getMessage();

        return (new MailMessage)
            ->subject($subject)
            ->line($message)
            ->action('View Proposal', url("/proposals/{$this->proposal->id}"))
            ->line('Thank you for using our CRM!');
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->toArray($notifiable);
    }

    public function toArray(object $notifiable): array
    {
        $customerName = $this->proposal->potentialCustomer->name ?? 'Unknown Customer';
        
        return [
            'proposal_id' => $this->proposal->id,
            'proposal_title' => $this->proposal->title,
            'action' => $this->action,
            'actor_name' => $this->actorName,
            'customer_name' => $customerName,
            'message' => $this->getMessage(),
            'timestamp' => now(),
            'type' => 'proposal',
            'url' => "/proposals/{$this->proposal->id}",
            'icon' => $this->getIcon(),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'type' => 'proposal',
            'title' => $this->getSubject(),
            'message' => $this->getMessage(),
            'action' => $this->action,
            'timestamp' => now()->toISOString(),
            'url' => "/proposals/{$this->proposal->id}",
            'icon' => $this->getIcon(),
        ]);
    }

    private function getSubject(): string
    {
        return match($this->action) {
            'created' => "New Proposal Created: {$this->proposal->title}",
            'updated' => "Proposal Updated: {$this->proposal->title}",
            'approved' => "Proposal Approved: {$this->proposal->title}",
            'rejected' => "Proposal Rejected: {$this->proposal->title}",
            'manager_reviewed' => "Manager Review Added: {$this->proposal->title}",
            'customer_approved' => "Customer Approved Proposal: {$this->proposal->title}",
            'customer_rejected' => "Customer Rejected Proposal: {$this->proposal->title}",
            default => "Proposal Update: {$this->proposal->title}"
        };
    }

    private function getMessage(): string
    {
        $customerName = $this->proposal->potentialCustomer->name ?? 'Unknown Customer';
        
        return match($this->action) {
            'created' => "A new proposal '{$this->proposal->title}' for {$customerName} has been created by {$this->actorName}.",
            'updated' => "The proposal '{$this->proposal->title}' for {$customerName} has been updated by {$this->actorName}.",
            'approved' => "The proposal '{$this->proposal->title}' for {$customerName} has been approved by {$this->actorName} and marked as signed.",
            'rejected' => "The proposal '{$this->proposal->title}' for {$customerName} has been rejected by {$this->actorName} and marked as unsigned.",
            'manager_reviewed' => "Sales manager {$this->actorName} has added a review to proposal '{$this->proposal->title}' for {$customerName}.",
            'customer_approved' => "Customer {$this->actorName} has approved the proposal '{$this->proposal->title}'.",
            'customer_rejected' => "Customer {$this->actorName} has rejected the proposal '{$this->proposal->title}'.",
            default => "Proposal '{$this->proposal->title}' for {$customerName} has been updated by {$this->actorName}."
        };
    }

    private function getIcon(): string
    {
        return match($this->action) {
            'created' => 'file-text',
            'approved', 'customer_approved' => 'check-circle',
            'rejected', 'customer_rejected' => 'x-circle',
            'updated' => 'edit',
            'manager_reviewed' => 'eye',
            default => 'bell'
        };
    }
}