<?php

namespace App\Repositories;

use App\Models\User;
use Carbon\CarbonInterface;

class UserRepository extends BaseRepository
{
    public function model(): string
    {
        return User::class;
    }

    public function updateLastLogin(int $id, CarbonInterface $date): User|null
    {
        return $this->update($id, [
            'last_login_at' => $date,
        ]);
    }
}
