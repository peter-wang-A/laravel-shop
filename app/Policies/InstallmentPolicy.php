<?php

namespace App\Policies;

use App\Models\Insatallment;
use App\Models\Installment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InstallmentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function own(User $user, Installment $installment)
    {
        return $installment->user_id ===  $user->id;
    }
}
