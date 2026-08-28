<?php

namespace App\Services\Teams;

class TeamService
{
    public function inviteStaff(int $tenantId, string $email): void
    {
        // Future scaling:
        // - Create team invitation flow
        // - Assign staff role under tenant workspace
    }
}
