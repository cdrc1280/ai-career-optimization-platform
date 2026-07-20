<?php

namespace App\Enums;

enum UserRole: string
{
    case User = 'user';
    case PremiumUser = 'premium_user';
    case Admin = 'admin';
    case SuperAdmin = 'super_admin';

    // Guest is intentionally not a case here — it represents the
    // unauthenticated state and is handled by middleware, not this enum.
}
