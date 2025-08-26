<?php

function binlookUp($bin) {
    $bin = substr(preg_replace('/\D/', '', $bin), 0, 8);
    $url = "https://api.voidex.dev/api/bin?bin={$bin}";
    $context = stream_context_create(['http' => ['timeout' => 10]]);
    $res = @file_get_contents($url, false, $context);

    if ($res !== false) {
        $data = json_decode($res, true);
        if ($data && isset($data['brand'])) {
            return (object)[
                'brand'   => strtoupper($data['brand'] ?? 'UNKNOWN'),
                'type'    => strtoupper($data['type'] ?? 'UNKNOWN'),
                'level'   => strtoupper($data['brand'] ?? 'UNKNOWN'),
                'bank'    => $data['bank'] ?? 'UNKNOWN',
                'country' => $data['country_name'] ?? 'UNKNOWN',
                'emoji'   => $data['country_flag'] ?? '🏳️',
                'success' => true
            ];
        }
    }
    return (object)[
        'brand'   => 'UNKNOWN',
        'type'    => 'UNKNOWN',
        'level'   => 'UNKNOWN',
        'bank'    => 'UNKNOWN',
        'country' => 'UNKNOWN',
        'emoji'   => '🏳️',
        'success' => false
    ];
}

?>
