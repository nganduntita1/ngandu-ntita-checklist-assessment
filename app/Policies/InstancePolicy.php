<?php

namespace App\Policies;

use App\Models\ChecklistInstance;
use App\Models\User;

class InstancePolicy
{
    /**
     * Only the auditor who owns the instance can view it.
     */
    public function view(User $user, ChecklistInstance $instance): bool
    {
        return $user->id === $instance->auditor_id;
    }

    /**
     * Only the auditor who owns the instance can update it,
     * and only while it is still in draft status.
     */
    public function update(User $user, ChecklistInstance $instance): bool
    {
        return $user->id === $instance->auditor_id
            && $instance->status === 'draft';
    }
}
