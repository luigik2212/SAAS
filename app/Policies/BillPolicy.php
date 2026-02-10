<?php

namespace App\Policies;

use App\Models\Bill;
use App\Models\User;

class BillPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->exists;
    }

    public function create(User $user): bool
    {
        return $user->exists;
    }

    public function view(User $user, Bill $bill): bool
    {
        return $bill->user_id === $user->id;
    }

    public function update(User $user, Bill $bill): bool
    {
        return $bill->user_id === $user->id;
    }

    public function delete(User $user, Bill $bill): bool
    {
        return $bill->user_id === $user->id;
    }

    public function pay(User $user, Bill $bill): bool
    {
        return $bill->user_id === $user->id;
    }

    public function reopen(User $user, Bill $bill): bool
    {
        return $bill->user_id === $user->id;
    }
}
