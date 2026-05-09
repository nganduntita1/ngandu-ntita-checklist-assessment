<?php

namespace App\Policies;

use App\Models\User;

class ReportPolicy
{
    /**
     * Only admins can view the reports listing.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }
}
