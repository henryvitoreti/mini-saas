<?php

namespace App\Repositories;

use App\Models\Company;

class CompanyRepository extends BaseRepository
{
    public function model(): string
    {
        return Company::class;
    }

    public function getCurrentCompany(): Company|null
    {
        return $this->query()->first();
    }

    public function getCurrentCompanyWithRole(): Company|null
    {
        return $this->query()
            ->with('role')
            ->find(1);
    }

    public function saveCurrentCompany(array $data): Company
    {
        $company = $this->find(1);

        if (!$company instanceof Company) {
            return $this->create(['id' => 1, ...$data]);
        }

        return $this->update($company->id, $data);
    }
}
