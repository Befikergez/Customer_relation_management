<?php

namespace App\Notifications;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

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
        return ['database', 'mail'];
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

    public function toArray($notifiable)
    {
        return [
            'contract_id' => $this->contract->id,
            'contract_title' => $this->contract->contract_title,
            'action' => $this->action,
            'actor_name' => $this->actorName,
            'actor_role' => $this->actorRole,
            'message' => $this->getMessage(),
            'timestamp' => now(),
            'type' => 'contract',
            'url' => "/admin/contracts/{$this->contract->id}",
        ];
    }

    private function getSubject(): string
    {
        return match($this->action) {
            'created' => 'New Contract Created',
            'updated' => 'Contract Updated',
            'sent_to_customer' => 'Contract Sent to Customer',
            'manager_reviewed' => 'Manager Review Added',
            'approved' => 'Contract Approved',
            'rejected' => 'Contract Rejected',
            default => 'Contract Status Update'
        };
    }

    private function getMessage(): string
    {
        $contractTitle = $this->contract->contract_title;
        $actor = $this->actorName . ($this->actorRole ? " ({$this->actorRole})" : '');

        return match($this->action) {
            'created' => "A new contract '{$contractTitle}' has been created by {$actor}.",
            'updated' => "The contract '{$contractTitle}' has been updated by {$actor}.",
            'sent_to_customer' => "The contract '{$contractTitle}' has been sent to the customer by {$actor}.",
            'manager_reviewed' => "Manager review has been added to contract '{$contractTitle}' by {$actor}.",
            'approved' => "The contract '{$contractTitle}' has been approved by {$actor}.",
            'rejected' => "The contract '{$contractTitle}' has been rejected by {$actor}.",
            default => "Contract '{$contractTitle}' status has been updated by {$actor}."
        };
    }
}