<?php

namespace App\Jobs;

use App\Models\Domain;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProvisionDomainSslJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60; // retry after 60s

    public function __construct(public Domain $domain) {}

    public function handle(): void
    {
        $domainName  = $this->domain->domain;
        $mainDomain  = config('hestia.main_domain');   // e.g. url.insanedev.in
        $hestiaUser  = config('hestia.user');           // e.g. powerhouse
        $hestiaCmd   = config('hestia.bin_path', '/usr/local/hestia/bin');

        // Step 1 — Add as a web domain alias of the main app domain
        // This makes HestiaCP serve the same document root without a separate vhost
        // sudo is required — grant via: visudo → powerhouse ALL=(ALL) NOPASSWD: /usr/local/hestia/bin/v-add-web-domain-alias,...
        $addAlias = "sudo {$hestiaCmd}/v-add-web-domain-alias {$hestiaUser} {$mainDomain} {$domainName} 2>&1";
        exec($addAlias, $aliasOut, $aliasCode);

        if ($aliasCode !== 0) {
            // Code 4 = already exists — that's fine, continue
            $alreadyExists = str_contains(implode(' ', $aliasOut), 'already exists') || $aliasCode === 4;
            if (! $alreadyExists) {
                $this->domain->update(['ssl_status' => 'failed']);
                Log::error("HestiaCP alias add failed for {$domainName}", ['output' => $aliasOut, 'code' => $aliasCode]);
                $this->fail("v-add-web-domain-alias failed (code {$aliasCode}): " . implode("\n", $aliasOut));
                return;
            }
        }

        Log::info("Domain alias added/confirmed for: {$domainName}");

        // Step 2 — Issue Let's Encrypt SSL via HestiaCP
        // HestiaCP handles nginx config regeneration + cert issuance automatically
        $addSsl = "sudo {$hestiaCmd}/v-add-letsencrypt-domain {$hestiaUser} {$mainDomain} {$domainName} no 2>&1";
        exec($addSsl, $sslOut, $sslCode);

        if ($sslCode === 0) {
            $this->domain->update(['ssl_status' => 'active']);
            Log::info("SSL provisioned for domain: {$domainName}");
        } else {
            $this->domain->update(['ssl_status' => 'failed']);
            Log::error("SSL provisioning failed for {$domainName}", ['output' => $sslOut, 'code' => $sslCode]);
            $this->fail("v-add-letsencrypt-domain failed (code {$sslCode}): " . implode("\n", $sslOut));
        }
    }
}
