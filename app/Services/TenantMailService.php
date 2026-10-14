<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class TenantMailService
{
    /**
     * Configure mailer for the given company or current authenticated user's company.
     *
     * @param Company|null $company
     * @return bool
     */
    public static function configureForCompany(Company $company = null)
    {
        if (!$company && auth()->check()) {
            $company = auth()->user()->company;
        }

        if (!$company) {
            return false;
        }

        if (empty($company->smtp_host) || empty($company->smtp_username) || empty($company->smtp_password)) {
            Log::warning("Company {$company->name} ({$company->id}) has missing SMTP configuration.");
            return false;
        }

        try {
            $password = Crypt::decryptString($company->smtp_password);
        } catch (\Exception $e) {
            Log::error("Failed to decrypt SMTP password for company {$company->id}: " . $e->getMessage());
            return false;
        }

        Config::set('mail.mailers.smtp.host', $company->smtp_host);
        Config::set('mail.mailers.smtp.port', $company->smtp_port);
        Config::set('mail.mailers.smtp.username', $company->smtp_username);
        Config::set('mail.mailers.smtp.password', $password);
        Config::set('mail.mailers.smtp.encryption', $company->smtp_encryption);
        
        if (!empty($company->from_address)) {
            Config::set('mail.from.address', $company->from_address);
        } else {
            Config::set('mail.from.address', $company->smtp_username);
        }
        
        if (!empty($company->from_name)) {
            Config::set('mail.from.name', $company->from_name);
        } else {
            Config::set('mail.from.name', $company->name);
        }

        return true;
    }
}
