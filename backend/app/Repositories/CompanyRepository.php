<?php

namespace App\Repositories;

use App\Models\Company;

class CompanyRepository extends BaseRepository
{
    private const int CURRENT_COMPANY_ID = 1;

    public function model(): string
    {
        return Company::class;
    }

    public function getCurrentCompany(): Company|null
    {
        return $this->find(self::CURRENT_COMPANY_ID);
    }

    public function getCurrentCompanyWithRole(): Company|null
    {
        return $this->query()
            ->with('role')
            ->find(self::CURRENT_COMPANY_ID);
    }

    public function saveCurrentCompany(array $data): Company
    {
        unset($data['id']);

        $company = $this->find(self::CURRENT_COMPANY_ID);

        if (!$company instanceof Company) {
            return $this->create(['id' => self::CURRENT_COMPANY_ID, ...$data]);
        }

        return $this->update(self::CURRENT_COMPANY_ID, $data);
    }
}
