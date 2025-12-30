<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Absensi;
use Illuminate\Auth\Access\HandlesAuthorization;

class AbsensiPolicy
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
    public function view(User $user, Absensi $absensi): bool
    {
        // Admin can view all
        if ($user->isAdministrator()) {
            return true;
        }

        // Guru can only view absensi from their assigned classes
        if ($user->isGuru()) {
            $kelasIds = $user->kelasYangDiajar()->pluck('id_kelas');
            return $kelasIds->contains($absensi->id_kelas);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admin and Guru can create absensi
        return $user->isAdministrator() || $user->isGuru();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Absensi $absensi): bool
    {
        // Admin can update all
        if ($user->isAdministrator()) {
            return true;
        }

        // Guru can only update absensi from their assigned classes
        if ($user->isGuru()) {
            $kelasIds = $user->kelasYangDiajar()->pluck('id_kelas');
            return $kelasIds->contains($absensi->id_kelas);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Absensi $absensi): bool
    {
        // Only admin can delete
        return $user->isAdministrator();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Absensi $absensi): bool
    {
        // Only admin can restore
        return $user->isAdministrator();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Absensi $absensi): bool
    {
        // Only admin can force delete
        return $user->isAdministrator();
    }
}
