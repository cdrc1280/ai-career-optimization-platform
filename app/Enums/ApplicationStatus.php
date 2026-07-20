<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Saved = 'saved';
    case Applied = 'applied';
    case Assessment = 'assessment';
    case Interview = 'interview';
    case Offer = 'offer';
    case Rejected = 'rejected';
    case Accepted = 'accepted';

    public function label(): string
    {
        return match ($this) {
            self::Saved => 'Saved',
            self::Applied => 'Applied',
            self::Assessment => 'Assessment',
            self::Interview => 'Interview',
            self::Offer => 'Offer',
            self::Rejected => 'Rejected',
            self::Accepted => 'Accepted',
        };
    }
}
