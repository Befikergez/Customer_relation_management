<?php

namespace App\Notifications;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class CustomerStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Customer $customer,
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
            ->action('View Customer', url("/admin/customers/{$this->customer->id}"))
            ->line('Thank you for using our CRM!');
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->toArray($notifiable);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'customer_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'action' => $this->action,
            'actor_name' => $this->actorName,
            'message' => $this->getMessage(),
            'timestamp' => now(),
            'type' => 'customer',
            'url' => "/admin/customers/{$this->customer->id}",
            'icon' => $this->getIcon(),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'type' => 'customer',
            'title' => $this->getSubject(),
            'message' => $this->getMessage(),
            'action' => $this->action,
            'timestamp' => now()->toISOString(),
            'url' => "/admin/customers/{$this->customer->id}",
            'icon' => $this->getIcon(),
        ]);
    }

    private function getSubject(): string
    {
        return match($this->action) {
            'approved' => "Customer Approved: {$this->customer->name}",
            'rejected' => "Customer Rejected: {$this->customer->name}",
            'converted' => "Potential Customer Converted: {$this->customer->name}",
            'created' => "New Customer Created: {$this->customer->name}",
            'updated' => "Customer Updated: {$this->customer->name}",
            default => "Customer Status Update: {$this->customer->name}"
        };
    }

    private function getMessage(): string
    {
        return match($this->action) {
            'approved' => "Customer '{$this->customer->name}' has been approved by {$this->actorName}.",
            'rejected' => "Customer '{$this->customer->name}' has been rejected by {$this->actorName}.",
            'converted' => "Potential customer '{$this->customer->name}' has been converted to a regular customer by {$this->actorName}.",
            'created' => "New customer '{$this->customer->name}' has been created by {$this->actorName}.",
            'updated' => "Customer '{$this->customer->name}' has been updated by {$this->actorName}.",
            default => "Customer '{$this->customer->name}' status has been updated by {$this->actorName}."
        };
    }

    private function getIcon(): string
    {
        return match($this->action) {
            'approved' => 'user-check',
            'rejected' => 'user-x',
            'converted' => 'refresh-ccw',
            'created' => 'user-plus',
            'updated' => 'user-edit',
            default => 'user'
        };
    }
}