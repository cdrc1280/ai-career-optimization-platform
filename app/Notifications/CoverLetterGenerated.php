<?php
namespace App\Notifications;

use App\Models\CoverLetter;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;

class CoverLetterGenerated extends Notification implements ShouldQueue {
    use Queueable;

    public function __construct(public readonly CoverLetter $coverLetter) {}

    public function via(object $notifiable): array { 
        return ['database', 'mail']; 
    }

    public function toArray(object $notifiable): array {
        return [
            'type' => 'cover_letter_generated',
            'title' => 'Cover Letter Generated',
            'message' => 'Your cover letter for ' . ($this->coverLetter->jobPosting?->job_title ?? 'the position') . ' is ready.',
            'cover_letter_id' => $this->coverLetter->id,
            'url' => '/cover-letters',
        ];
    }

    public function toMail(object $notifiable): MailMessage {
        return (new MailMessage)
            ->subject('Your Cover Letter is Ready')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your personalized cover letter has been generated.')
            ->action('View Cover Letter', url('/cover-letters'));
    }
}
