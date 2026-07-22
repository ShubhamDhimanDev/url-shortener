<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Known Bot User-Agent Substrings
    |--------------------------------------------------------------------------
    | Any UA containing one of these strings (case-insensitive) is treated as
    | a bot. The key is the canonical bot name returned by BotDetectionService.
    */

    'user_agents' => [
        'googlebot'                => 'Googlebot',
        'bingbot'                  => 'Bingbot',
        'slurp'                    => 'Yahoo! Slurp',
        'duckduckbot'              => 'DuckDuckBot',
        'baiduspider'              => 'Baiduspider',
        'yandexbot'                => 'YandexBot',
        'sogou'                    => 'Sogou Spider',
        'exabot'                   => 'Exabot',
        'facebot'                  => 'Facebook Crawler',
        'ia_archiver'              => 'Alexa/Archive.org',
        'semrushbot'               => 'SEMrushBot',
        'ahrefsbot'                => 'AhrefsBot',
        'mj12bot'                  => 'Majestic-12',
        'dotbot'                   => 'DotBot',
        'rogerbot'                 => 'Rogerbot',
        'linkdexbot'               => 'LinkdexBot',
        'sistrix'                  => 'Sistrix',
        'seznambot'                => 'SeznamBot',
        'ltx71'                    => 'LTX71',
        'twitterbot'               => 'Twitterbot',
        'linkedinbot'              => 'LinkedInBot',
        'slackbot'                 => 'Slackbot',
        'telegrambot'              => 'TelegramBot',
        'whatsapp'                 => 'WhatsApp',
        'discordbot'               => 'Discordbot',
        'applebot'                 => 'Applebot',
        'pingdom'                  => 'Pingdom',
        'uptimerobot'              => 'UptimeRobot',
        'crawl'                    => 'Generic Crawler',
        'spider'                   => 'Generic Spider',
        'scrapy'                   => 'Scrapy',
    ],

    /*
    |--------------------------------------------------------------------------
    | Headless Browser Signatures
    |--------------------------------------------------------------------------
    | These UA fragments indicate automation / headless browsers.
    */

    'headless' => [
        'headlesschrome'   => 'Headless Chrome',
        'phantomjs'        => 'PhantomJS',
        'slimerjs'         => 'SlimerJS',
        'htmlunit'         => 'HtmlUnit',
        'python-requests'  => 'python-requests',
        'python-urllib'    => 'Python urllib',
        'go-http-client'   => 'Go HTTP Client',
        'java/'            => 'Java HTTP Client',
        'curl/'            => 'cURL',
        'wget/'            => 'Wget',
        'libwww-perl'      => 'Libwww-perl',
        'axios/'           => 'Axios',
    ],

    /*
    |--------------------------------------------------------------------------
    | Honeypot Header Name
    |--------------------------------------------------------------------------
    | If a request carries this custom header, it is treated as a bot.
    */

    'honeypot_header' => 'X-Shortener-Bot',

];
