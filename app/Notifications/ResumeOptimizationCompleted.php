<?php
namespace App\Notifications;

use App\Models\ResumeVersion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;

class ResumeOptimizationCompleted extends Notification implements ShouldQueue {
    use Queueable;

    public function __construct(public readonly ResumeVersion $version) {}

    public function via(object $notifiable): array { 
        return ['database', 'mail']; 
    }

    public function toArray(object $notifiable): array {
        return [
            'type' => 'optimization_completed',
            'title' => 'Resume Optimization Ready',
            'message' => 'Your optimized resume "' . $this->version->label . '" is ready to download.',
            'version_id' => $this->version->id,
            'resume_id' => $this->version->resume_id,
            'url' => '/resumes/' . $this->version->resume_id,
        ];
    }

    public function toMail(object $notifiable): MailMessage {
        return (new MailMessage)
            ->subject('Your Optimized Resume is Ready')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your AI-optimized resume "' . $this->version->label . '" has been generated.')
            ->action('View Resume', url('/resumes/' . $this->version->resume_id))
            ->line('Review the changes and download your new resume.');
    }
}
