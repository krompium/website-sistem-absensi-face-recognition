<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Siswa;
use Illuminate\Auth\Access\HandlesAuthorization;

class SiswaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admin and Guru can view list
        return $user->isAdministrator() || $user->isGuru();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Siswa $siswa): bool
    {
        // Admin can view all
        if ($user->isAdministrator()) {
            return true;
        }

        // Guru can only view siswa in their assigned classes
        if ($user->isGuru()) {
            $kelasIds = $user->kelasYangDiajar()->pluck('id_kelas');
            return $kelasIds->contains($siswa->id_kelas);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admin can create
        return $user->isAdministrator();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Siswa $siswa): bool
    {
        // Only admin can update
        return $user->isAdministrator();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Siswa $siswa): bool
    {
        // Only admin can delete
        return $user->isAdministrator();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Siswa $siswa): bool
    {
        // Only admin can restore
        return $user->isAdministrator();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Siswa $siswa): bool
    {
        // Only admin can force delete
        return $user->isAdministrator();
    }
}
