<?php

return [
    // Toggle Report-Only based on production
    'report_only' => ! env('APP_ENV', 'production') === 'production',

    // Policy directives
    'policy' => [
        // Defaults to Report URI CSP Wizard URL, which is the easiest way to add a CSP to an existing site.
        // Check it out at: https://report-uri.com/
        'report-uri' => ['https://<subdomain>.report-uri.com/r/d/csp/wizard'],

        'default-src' => ["'none'"],
        'connect-src' => ["'none'"],
        'font-src' => ["'none'"],
        'frame-src' => ["'none'"],
        'img-src' => ["'self'"],
        'manifest-src' => ["'self'"],
        'script-src' => ["'report-sample'", "'self'"],
        'style-src' => ["'self'"],

        'form-action' => ["'none'"],
        'frame-ancestors' => ["'none'"],
    ],
];
