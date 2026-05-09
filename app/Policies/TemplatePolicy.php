<?php

namespace App\Policies;

use App\Models\ChecklistTemplate;
use App\Models\User;

class TemplatePolicy
{
    /**
     * Any authenticated user can list templates.
     * (Auditors see only active ones — that filtering is handled in the service layer.)
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Any authenticated user can view a single template.
     */
    public function view(User $user, ChecklistTemplate $template): bool
    {
        return true;
    }

    /**
     * Only admins can create templates.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Only admins can update templates.
     */
    public function update(User $user, ChecklistTemplate $template): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Only admins can delete templates.
     */
    public function delete(User $user, ChecklistTemplate $template): bool
    {
        return $user->role === 'admin';
    }
}
