<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\IdentidadeVisual;
use App\Models\User;

class IdentidadeVisualPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_platform_admin;
    }

    public function view(User $user, IdentidadeVisual $identidadeVisual): bool
    {
        return $user->is_platform_admin;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, IdentidadeVisual $identidadeVisual): bool
    {
        return $user->is_platform_admin;
    }

    public function delete(User $user, IdentidadeVisual $identidadeVisual): bool
    {
        return false;
    }
}
