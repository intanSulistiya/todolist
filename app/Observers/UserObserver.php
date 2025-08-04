<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Role;

class UserObserver
{
    /**
     * Handle the User "creating" event.
     */
    public function creating(User $user): void
    {
        // Jika role_id adalah manager (ID = 1), set manager_id sesuai urutan
        if ($user->role_id == 1) {
            // Hitung jumlah manager yang sudah ada
            $managerCount = User::where('role_id', 1)->count();
            // Manager baru akan memiliki manager_id = jumlah manager + 1
            $user->manager_id = $managerCount + 1;
        } else {
            // Jika bukan manager, set manager_id = null
            $user->manager_id = null;
        }
    }

    /**
     * Handle the User "updating" event.
     */
    public function updating(User $user): void
    {
        // Jika role berubah menjadi manager (ID = 1)
        if ($user->role_id == 1) {
            // Jika user ini belum memiliki manager_id atau manager_id tidak sesuai urutan
            if (!$user->manager_id || $user->manager_id != $user->id) {
                // Hitung jumlah manager yang sudah ada (termasuk user ini)
                $managerCount = User::where('role_id', 1)->count();
                $user->manager_id = $managerCount;
            }
        } else {
            // Jika role berubah bukan manager, set manager_id = null
            $user->manager_id = null;
        }
    }
}
