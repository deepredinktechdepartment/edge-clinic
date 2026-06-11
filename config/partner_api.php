<?php

$parsedKeys = [];
$parsedPaymentRules = [];

foreach (array_filter(array_map('trim', explode(',', (string) env('PARTNER_API_KEYS', '')))) as $entry) {
    if (str_contains($entry, ':')) {
        [$client, $token] = array_map('trim', explode(':', $entry, 2));
    } else {
        $client = 'default';
        $token = $entry;
    }

    if ($token !== '') {
        $parsedKeys[$client] = $token;
    }
}

foreach (array_filter(array_map('trim', explode(',', (string) env('PARTNER_API_PAYMENT_RULES', '')))) as $entry) {
    if (! str_contains($entry, ':')) {
        continue;
    }

    [$client, $rule] = array_map('trim', explode(':', $entry, 2));

    if ($client !== '' && $rule !== '') {
        $parsedPaymentRules[strtolower($client)] = strtolower($rule);
    }
}

return [
    'keys' => $parsedKeys,
    'default_country_code' => env('PARTNER_API_DEFAULT_COUNTRY_CODE', '+91'),
    'source_prefix' => env('PARTNER_API_SOURCE_PREFIX', 'API'),
    'payment_rules' => $parsedPaymentRules,
];
