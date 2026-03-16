<?php

namespace App\Console\Commands;

use App\Jobs\ProvisionDomainSslJob;
use App\Models\Domain;
use Illuminate\Console\Command;

class RenewDomainSslCertificates extends Command
{
    protected $signature   = 'domains:renew-ssl {--force : Re-provision all active domains}';
    protected $description = 'Re-provision SSL certificates for verified custom domains expiring within 30 days';

    public function handle(): int
    {
        $domains = Domain::query()
            ->where('is_verified', true)
            ->where('is_active', true)
            ->where('type', 'custom_domain')
            ->get();

        $dispatched = 0;

        foreach ($domains as $domain) {
            $needsRenewal = $this->option('force')
                || $domain->ssl_status !== 'active'
                || $this->sslExpiresSoon($domain->domain);

            if ($needsRenewal) {
                ProvisionDomainSslJob::dispatch($domain)->onQueue('default');
                $this->line("Queued SSL renewal for: {$domain->domain}");
                $dispatched++;
            }
        }

        $this->info("Queued {$dispatched} SSL renewal job(s).");
        return self::SUCCESS;
    }

    private function sslExpiresSoon(string $domainName): bool
    {
        $hestiaUser = config('hestia.user');
        $sslPath    = "/home/{$hestiaUser}/conf/web/{$domainName}/ssl";
        $certFile   = "{$sslPath}/{$domainName}.crt";

        if (! file_exists($certFile)) {
            return true;
        }

        $output = shell_exec("openssl x509 -enddate -noout -in {$certFile} 2>/dev/null");
        if (! $output) {
            return true;
        }

        preg_match('/notAfter=(.+)/', $output, $matches);
        if (empty($matches[1])) {
            return true;
        }

        return \Carbon\Carbon::parse(trim($matches[1]))->diffInDays(now()) <= 30;
    }
}
