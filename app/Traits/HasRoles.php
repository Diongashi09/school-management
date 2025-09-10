<?php

namespace App\Traits;

use App\Models\Role;

trait HasRoles
{
    /**
     * Check if the user has a specific role
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role && $this->role->name === $roleName;
    }

    /**
     * Check if the user has any of the given roles
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->role && in_array($this->role->name, $roleNames);
    }

    /**
     * Assign a role to the user
     */
    public function assignRole(Role $role): void
    {
        $this->role()->associate($role);
        $this->save();
    }

    /**
     * Remove the role from the user
     */
    public function removeRole(): void
    {
        $this->role()->dissociate();
        $this->save();
    }
}
