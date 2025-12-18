<?php

namespace App\Notifications;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ContractStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $contract;
    public $action;
    public $actorName;
    public $actorRole;

    public function __construct(Contract $contract, string $action, string $actorName, string $actorRole = null)
    {
        $this->contract = $contract;
        $this->action = $action;
        $this->actorName = $actorName;
        $this->actorRole = $actorRole;
    }

    public function via($notifiable)
    {
        return ['database', 'mail', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        $subject = $this->getSubject();
        $message = $this->getMessage();

        return (new MailMessage)
            ->subject($subject)
            ->line($message)
            ->action('View Contract', url("/admin/contracts/{$this->contract->id}"))
            ->line('Thank you for using our application!');
    }

    public function toDatabase($notifiable)
    {
        return $this->toArray($notifiable);
    }

    public function toArray($notifiable)
    {
        return [
            'contract_id' => $this->contract->id,
            'contract_title' => $this->contract->contract_title,
            'customer_id' => $this->contract->customer_id,
            'customer_name' => $this->contract->customer->name ?? 'Unknown Customer',
            'action' => $this->action,
            'actor_name' => $this->actorName,
            'actor_role' => $this->actorRole,
            'message' => $this->getMessage(),
            'timestamp' => now(),
            'type' => 'contract',
            'url' => "/admin/contracts/{$this->contract->id}",
            'icon' => $this->getIcon(),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'type' => 'contract',
            'title' => $this->getSubject(),
            'message' => $this->getMessage(),
            'action' => $this->action,
            'timestamp' => now()->toISOString(),
            'url' => "/admin/contracts/{$this->contract->id}",
            'icon' => $this->getIcon(),
        ]);
    }

    private function getSubject(): string
    {
        $title = $this->contract->contract_title;
        
        return match($this->action) {
            'created' => "New Contract Created: {$title}",
            'updated' => "Contract Updated: {$title}",
            'sent_to_customer' => "Contract Sent to Customer: {$title}",
            'manager_reviewed' => "Manager Review Added: {$title}",
            'approved' => "Contract Approved: {$title}",
            'rejected' => "Contract Rejected: {$title}",
            'customer_approved' => "Customer Approved Contract: {$title}",
            'customer_rejected' => "Customer Rejected Contract: {$title}",
            default => "Contract Status Update: {$title}"
        };
    }

    private function getMessage(): string
    {
        $contractTitle = $this->contract->contract_title;
        $customerName = $this->contract->customer->name ?? 'Unknown Customer';
        $actor = $this->actorName . ($this->actorRole ? " ({$this->actorRole})" : '');

        return match($this->action) {
            'created' => "A new contract '{$contractTitle}' for {$customerName} has been created by {$actor}.",
            'updated' => "The contract '{$contractTitle}' for {$customerName} has been updated by {$actor}.",
            'sent_to_customer' => "The contract '{$contractTitle}' for {$customerName} has been sent to the customer by {$actor}.",
            'manager_reviewed' => "Manager review has been added to contract '{$contractTitle}' for {$customerName} by {$actor}.",
            'approved' => "The contract '{$contractTitle}' for {$customerName} has been approved by {$actor}.",
            'rejected' => "The contract '{$contractTitle}' for {$customerName} has been rejected by {$actor}.",
            'customer_approved' => "Customer {$actor} has approved the contract '{$contractTitle}'.",
            'customer_rejected' => "Customer {$actor} has rejected the contract '{$contractTitle}'.",
            default => "Contract '{$contractTitle}' for {$customerName} status has been updated by {$actor}."
        };
    }

    private function getIcon(): string
    {
        return match($this->action) {
            'created' => 'file-text',
            'approved', 'customer_approved' => 'check-circle',
            'rejected', 'customer_rejected' => 'x-circle',
            'sent_to_customer' => 'send',
            'updated' => 'edit',
            'manager_reviewed' => 'eye',
            default => 'file'
        };
    }
}