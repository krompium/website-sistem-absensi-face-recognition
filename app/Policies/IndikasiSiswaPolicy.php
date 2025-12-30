<?php

namespace App\Policies;

use App\Models\User;
use App\Models\IndikasiSiswa;
use Illuminate\Auth\Access\HandlesAuthorization;

class IndikasiSiswaPolicy
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
    public function view(User $user, IndikasiSiswa $indikasiSiswa): bool
    {
        // Admin can view all
        if ($user->isAdministrator()) {
            return true;
        }

        // Guru can only view indikasi from their assigned classes
        if ($user->isGuru() && $indikasiSiswa->absensi) {
            $kelasIds = $user->kelasYangDiajar()->pluck('id_kelas');
            return $kelasIds->contains($indikasiSiswa->absensi->id_kelas);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admin can create (usually via API/system)
        return $user->isAdministrator();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, IndikasiSiswa $indikasiSiswa): bool
    {
        // Only admin can update
        return $user->isAdministrator();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, IndikasiSiswa $indikasiSiswa): bool
    {
        // Only admin can delete
        return $user->isAdministrator();
    }
}
