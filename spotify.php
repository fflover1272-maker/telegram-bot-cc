<?php

function generateSpotifyUserAgent() {
    $androidVersions = [
        "9", "10", "11", "12", "13", "21", "22", "23", "24", "25", "26", "27", "28", "29"
    ];
    $devices = [
        "SM-N970F", "SM-N971N", "SM-N975F", "SM-N976B", "SM-N976N", "SM-N976U1", "SM-N976U", "SM-N9760", "SM-N9768", "SM-N977U", "SM-N977V", "SM-N977U1"
    ];
    $chromeVersions = [
        "87.0.4280.141", "88.0.4324.152", "89.0.4389.72", "90.0.4430.212", "91.0.4472.77", "92.0.4515.107", "93.0.4577.63", "94.0.4606.54", "95.0.4638.54", "96.0.4664.45", "97.0.4692.71", "98.0.4758.80", "99.0.4844.51", "100.0.4896.60"
    ];
    $spotifyVersions = [
        "8.6.72", "8.6.73", "8.6.74", "8.6.75", "8.6.76", "8.6.77", "8.6.78", "8.6.79", "8.6.80", "8.6.81", "8.6.82", "8.6.83", "8.6.84", "8.6.85"
    ];

    $androidVersion = $androidVersions[array_rand($androidVersions)];
    $device = $devices[array_rand($devices)];
    $chromeVersion = $chromeVersions[array_rand($chromeVersions)];
    $spotifyVersion = $spotifyVersions[array_rand($spotifyVersions)];

    return "Mozilla/5.0 (Linux; Android $androidVersion; $device Build/UNKNOWN; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/$chromeVersion Mobile Safari/537.36 Spotify/$spotifyVersion";
}

?>
