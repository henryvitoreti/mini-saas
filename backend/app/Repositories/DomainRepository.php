<?php

namespace App\Repositories;

use App\Models\Domain;

class DomainRepository extends BaseRepository
{
    public function model(): string
    {
        return Domain::class;
    }

    public function domainExists(string $domain): bool
    {
        return Domain::query()
            ->where('domain', $domain)
            ->exists();
    }
}
