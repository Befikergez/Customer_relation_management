<?php

namespace App\Notifications;

use App\Models\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

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
        return ['database', 'mail'];
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

    public function toArray(object $notifiable): array
    {
        return [
            'proposal_id' => $this->proposal->id,
            'proposal_title' => $this->proposal->title,
            'action' => $this->action,
            'actor_name' => $this->actorName,
            'message' => $this->getMessage(),
            'timestamp' => now(),
        ];
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
}