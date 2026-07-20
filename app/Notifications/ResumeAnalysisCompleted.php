<?php
namespace App\Notifications;

use App\Models\ResumeAnalysis;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;

class ResumeAnalysisCompleted extends Notification implements ShouldQueue {
    use Queueable;

    public function __construct(public readonly ResumeAnalysis $analysis) {}

    public function via(object $notifiable): array { 
        return ['database', 'mail']; 
    }

    public function toArray(object $notifiable): array {
        return [
            'type' => 'analysis_completed',
            'title' => 'Resume Analysis Complete',
            'message' => 'Your resume analysis scored ' . $this->analysis->overall_match_score . '% overall match.',
            'analysis_id' => $this->analysis->id,
            'score' => $this->analysis->overall_match_score,
            'url' => '/analysis',
        ];
    }

    public function toMail(object $notifiable): MailMessage {
        return (new MailMessage)
            ->subject('Your Resume Analysis is Ready')
            ->greeting('Great news, ' . $notifiable->name . '!')
            ->line('Your resume analysis is complete with an overall match score of ' . $this->analysis->overall_match_score . '%.')
            ->action('View Analysis', url('/analysis'))
            ->line('Log in to view detailed skill gaps, keyword analysis, and interview preparation.');
    }
}
