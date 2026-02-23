<?php

namespace App\Services\Analytics;

class BotDetectionService
{
    /**
     * Analyse a User-Agent string (and optional honeypot header value) and
     * return detection metadata.
     *
     * @return array{is_bot: bool, bot_name: string|null}
     */
    public function detect(string $userAgent, ?string $honeypotHeader = null): array
    {
        // 1. Honeypot header — instant bot
        if ($honeypotHeader !== null && $honeypotHeader !== '') {
            return ['is_bot' => true, 'bot_name' => 'Honeypot'];
        }

        // 2. EmptyUA
        if (trim($userAgent) === '') {
            return ['is_bot' => true, 'bot_name' => 'Empty UA'];
        }

        $ua = strtolower($userAgent);

        // 3. Known headless / automation signatures
        foreach (config('bots.headless', []) as $signature => $name) {
            if (str_contains($ua, strtolower($signature))) {
                return ['is_bot' => true, 'bot_name' => $name];
            }
        }

        // 4. Known bot UA substrings
        foreach (config('bots.user_agents', []) as $signature => $name) {
            if (str_contains($ua, strtolower($signature))) {
                return ['is_bot' => true, 'bot_name' => $name];
            }
        }

        return ['is_bot' => false, 'bot_name' => null];
    }
}
