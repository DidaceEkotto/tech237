<?php

namespace App\Policies;

use App\Models\PortfolioSite;
use App\Models\User;

class PortfolioSitePolicy
{
    public function view(User $user, PortfolioSite $site): bool
    {
        return (int) $site->user_id === (int) $user->id;
    }

    public function update(User $user, PortfolioSite $site): bool
    {
        return (int) $site->user_id === (int) $user->id;
    }

    public function delete(User $user, PortfolioSite $site): bool
    {
        return (int) $site->user_id === (int) $user->id;
    }
}
