<?php

// Include other files
require_once 'card_generator.php';
require_once 'bin.php';
require_once 'spotify.php';

// Configuration
$botToken = '8392676528:AAHKVaGZL6MBAAr9CYyOHVDyWvR2tP7aGG8'; // Replace with your actual bot token
$adminUserId = 6490007953; // Replace with Anish's actual Telegram user ID
define('BOT_OWNER_USERNAME', 'Ravan_v2_bot'); // Replace with actual username

// API URL
$apiUrl = "https://api.telegram.org/bot$botToken/";

// Helper Functions
function bot($method, $params) {
    global $apiUrl;
    $ch = curl_init($apiUrl . $method);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}

function cmd($message, $command) {
    return strpos($message, '/' . $command) === 0;
}

function deleteprm($userId) {
    // Implement if needed (user permission cleanup)
}

function sendaction($chatId, $action) {
    bot('sendChatAction', [
        'chat_id' => $chatId,
        'action' => $action
    ]);
}

function reply_to($chatId, $message_id, $keyboard, $text) {
    bot('sendMessage', [
        'chat_id' => $chatId,
        'reply_to_message_id' => $message_id,
        'text' => $text,
        'parse_mode' => 'html',
        'reply_markup' => $keyboard
    ]);
}

function capture($json, $start, $end) {
    $startPos = strpos($json, $start) + strlen($start);
    $endPos = strpos($json, $end, $startPos);
    return substr($json, $startPos, $endPos - $startPos);
}

function answerCallbackQuery($queryId, $text, $showAlert) {
    bot('answerCallbackQuery', [
        'callback_query_id' => $queryId,
        'text' => $text,
        'show_alert' => $showAlert
    ]);
}

// Get incoming update
$update = file_get_contents('php://input');
$update = json_decode($update, true);

// Handle Messages
if (isset($update['message'])) {
    $message = $update['message']['text'] ?? '';
    $chatId = $update['message']['chat']['id'];
    $message_id = $update['message']['message_id'];
    $userId = $update['message']['from']['id'];
    $username = $update['message']['from']['username'] ?? 'User';
    $Rank = 'Admin'; // Placeholder
    $keyboard = null; // Placeholder

    // User registration check (placeholder - implement your logic)
    $user_info = true; // Set to false if user needs registration

    if (!$user_info) {
        bot('sendMessage', [
            'chat_id' => $chatId,
            'text' => "Please register first to use me. Use the /register command.",
            'parse_mode' => 'html'
        ]);
        exit;
    }

    // /start command
    if (cmd($message, "start")) {
        $inline_keyboard = json_encode([
            'inline_keyboard' => [
                [['text' => 'Owner', 'url' => 'https://t.me/' . BOT_OWNER_USERNAME]]
            ]
        ]);

        bot('sendMessage', [
            'chat_id' => $chatId,
            'text' => "<b>Hi,</b>\n<b>Welcome To Bot</b>\n<b>Type /cmds For Commands</b>\n\n",
            'parse_mode' => 'html',
            'reply_markup' => $inline_keyboard
        ]);
        exit;
    }

    // /cmds command
    if (cmd($message, "cmds")) {
        bot('sendMessage', [
            'chat_id' => $chatId,
            'text' => "<b>Bot: Running ✅🌧️</b>\n<b>• Welcome to my command panel,</b>\n<b>tooo.</b>\n<b>• Press the buttons to see my commands.</b>\n\n",
            'parse_mode' => 'html',
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text' => 'Menu 🔎', 'callback_data' => 'menu']]
                ]
            ])
        ]);
        exit;
    }

    // /gen command (Credit Card Generator)
    list($cmd) = explode(" ", $message);
    if (cmd($message, "gen")) {
        if ($userId !== $adminUserId) {
            bot('sendMessage', [
                'chat_id' => $chatId,
                'text' => "Access denied. Only admin user Anish can use this command.",
                'parse_mode' => 'html'
            ]);
            die();
        }

        $tiempo_inicial = microtime(true);
        deleteprm($userId);

        $input = substr($message, 4);
        $input = preg_replace("/\W/", " ", $input);
        $input = preg_replace("/\r|\n/", ' ', $input);
        $input = preg_replace("/[^0-9x]/", ' ', $input);
        $input = preg_replace('/\s+/', ' ', $input);
        $input = trim($input, ' ');
        $card = explode(" ", $input);

        if (empty($input) || $card[0][0] == "x") {
            reply_to($chatId, $message_id, $keyboard, '<b>Gen Card%0AFormat:<code>/gen cc|m|y|cvv</code></b>');
            return;
        }

        if (strlen($card[0]) < 6) {
            die();
        }

        $cc = $card[0];
        $mon = $card[1] ?? '';
        $year2 = $card[2] ?? '';
        $cvv2 = $card[3] ?? '';

        if (strlen($year2) == 2) {
            $year2 = "20" . $year2;
        }
        if (strlen($mon) == 1) {
            $mon = "0" . $mon;
        }
        if (strlen($cc) == 16) {
            $cc = substr($cc, 0, 12);
        }

        $messageidtoedit1 = bot('sendMessage', [
            'chat_id' => $chatId,
            'reply_to_message_id' => $message_id,
            'parse_mode' => 'HTML',
            'text' => "<code>Wait the Cc are being Generated</code>"
        ]);
        $messageidtoedit = capture(json_encode($messageidtoedit1), '"message_id":', ',');

        $chem = substr($cc, 0, 1);
        $vaut = array(1, 2, 7, 8, 9, 0);
        if (in_array($chem, $vaut)) {
            bot('editMessageText', [
                'chat_id' => $chatId,
                'message_id' => $messageidtoedit,
                'text' => "<b>Este bot solo soporta Amex, Visa, MasterCard y Discover.</b>",
                'parse_mode' => 'html',
            ]);
            exit();
        }

        $cc3 = substr($cc, 0, 6);
        $res = binlookUp($cc3);
        $type = $res->type;
        $bank = $res->bank;
        $brand = $res->brand;
        $scheme = $res->level;
        $country = $res->country;
        $emoji = $res->emoji;

        if (empty($mon)) $mon = 'rnd';
        if (empty($year2)) $year2 = 'rnd';
        if (empty($cvv2)) $cvv2 = 'rnd';

        if ($mon != 'rnd' && ($mon > 12 || $mon < 1)) {
            bot('editMessageText', [
                'chat_id' => $chatId,
                'message_id' => $messageidtoedit,
                'text' => "<b>The month entered is invalid</b>",
                'parse_mode' => 'html',
            ]);
            return;
        }

        if ($year2 != 'rnd' && ($year2 > 2060 || $year2 < 2024)) {
            bot('editMessageText', [
                'chat_id' => $chatId,
                'message_id' => $messageidtoedit,
                'text' => "<b>The year entered is incorrect</b>",
                'parse_mode' => 'html',
            ]);
            return;
        }

        $card = CreditCardGenerator::generateCC($cc, $mon, $year2, $cvv2, 10);
        $cco = str_pad($cc, 16, "x");
        $tiempo_final = microtime(true);
        $tiempo = substr($tiempo_final - $tiempo_inicial, 0, 4);

        bot('editMessageText', [
            'chat_id' => $chatId,
            'message_id' => $messageidtoedit,
            'text' => "<b>-🪁Info Bin : <code>$cc3 - $brand - $scheme - $type | $bank </code>[$emoji]
<b>Formato :</b><code> $cco|$mon|$year2|$cvv2|</code>
——————————————————
$card
——————————————————
<b>Time :</b> <code>$tiempo s</code></b>",
            'parse_mode' => 'html',
            'reply_markup' => json_encode(['inline_keyboard' => [
                [['text' => 'Generate Again!🎡', 'callback_data' => 'hod'],
                ['text' => 'Generate Mass!⛽️', 'callback_data' => 'gen2']]
            ]])
        ]);
        die();
    }

    // /bin command (BIN Lookup)
    if (strpos($message, '/bin') === 0 || strpos($message, '!bin') === 0 || strpos($message, '.bin') === 0) {
        if ($userId !== $adminUserId) {
            bot('sendMessage', [
                'chat_id' => $chatId,
                'text' => "Access denied. Only admin user Anish can use this command.",
                'parse_mode' => 'html'
            ]);
            die();
        }

        $bin = substr($message, 5);
        $lidsta = substr(trim($bin), 0, 6);
        $res = binlookUp($lidsta);

        $ty = $res->type ?? 'UNKNOWN';
        $bk = $res->bank ?? 'UNKNOWN';
        $bd = $res->brand ?? 'UNKNOWN';
        $sh = $res->level ?? 'UNKNOWN';
        $ct = $res->country ?? 'UNKNOWN';
        $emoji = $res->emoji ?? '🏳️';

        if (!$res->success || empty($res->brand)) {
            reply_to($chatId, $message_id, $keyboard, "Ingrese un bin Valido $lidsta");
            exit();
        }

        $messageidtoedit1 = bot('sendMessage', [
            'chat_id' => $chatId,
            'reply_to_message_id' => $message_id,
            'parse_mode' => 'HTML',
            'text' => "<b>Waitt</b>"
        ]);
        $messageidtoedit = capture(json_encode($messageidtoedit1), '"message_id":', ',');

        bot('editMessageText', [
            'chat_id' => $chatId,
            'disable_web_page_preview' => true,
            'message_id' => $messageidtoedit,
            'text' => "<b>[<a href='https://t.me/ritabotchk/35'>ϟ</a>] BIN LOOKUP ♻️: >_ $-Security System ⚠️
━━━━━━━━━━━━━━━━
[<a href='https://t.me/ritabotchk/35'>ϟ</a>] Bin : #Bin$lidsta
[<a href='https://t.me/ritabotchk/35'>ϟ</a>] Type: <code>$bd</code> - <code>$sh</code> - <code>$ty</code>
[<a href='https://t.me/ritabotchk/35'>ϟ</a>] Bank: <code>$bk</code>
[<a href='https://t.me/ritabotchk/35'>ϟ</a>] Country: <code>$ct </code>-[$emoji]
━━━━━━━━━━━━━━━━
[<a href='https://t.me/ritabotchk/35'>ϟ</a>] Checked by: <a href='tg://user?id=$userId'>$username</a>[<code>$Rank</code>]
</b>",
            'parse_mode' => 'html',
            'reply_to_message_id' => $message_id
        ]);
        exit();
    }

    // /spotify command (included from spotify.php)
    if ($message == "/spotify" || $message == ".spotify" || $message == "!spotify") {
        if ($userId !== $adminUserId) {
            bot('sendMessage', [
                'chat_id' => $chatId,
                'text' => "Access denied. Only admin user Anish can use this command.",
                'parse_mode' => 'html'
            ]);
            die();
        }

        sendaction($chatId, 'typing');
        $tiempo_inicial = microtime(true);
        deleteprm($userId);

        $userAgent = generateSpotifyUserAgent();

        $json = file_get_contents("https://randomuser.me/api/?nat=us");
        $data = json_decode($json, true);
        $user = $data["results"][0];
        $providers = array('gmail.com', 'hotmail.com', 'yahoo.com', 'outlook.com');
        $provider = $providers[array_rand($providers)];
        $email = strtolower($user["name"]["first"]) . '' . strtolower($user["name"]["last"]) . rand(11111, 22222) . '@' . $provider;
        $firstname = $user["name"]["first"];
        $lastname = $user["name"]["last"];

        $pass = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 12);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://spclient.wg.spotify.com/signup/public/v1/account/');
        $headers = array();
        $headers[] = 'Accept-Language: en-US';
        $headers[] = 'App-Platform: Android';
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        $headers[] = 'Host: spclient.wg.spotify.com';
        $headers[] = 'User-Agent: ' . $userAgent;
        $headers[] = 'Spotify-App-Version: 8.6.72';
        $headers[] = 'X-Client-Id:';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'gender=male&birth_year=' . rand(1995, 2000) . '&displayname=' . $firstname . '+' . $lastname . '&iagree=true&birth_month=' . rand(1, 9) . '&password_repeat=' . $pass . '&password=' . $pass . '&key=142b583129b2df829de3656f9eb484e6&platform=Android-ARM&email=' . $email . '&birth_day=' . rand(13, 28));
        $result = curl_exec($ch);
        curl_close($ch);

        bot('sendMessage', [
            'chat_id' => $chatId,
            'reply_to_message_id' => $message_id,
            'parse_mode' => 'HTML',
            'text' => "<b>Account by Rita Chk
Correo: <code>$email</code>
Password: <code>$pass</code>
Created BY: <a href='tg://user?id=$userId'>$username</a> [<code>$Rank</code>]</b>"
        ]);
    }
}

// Handle Callback Queries
if (isset($update['callback_query'])) {
    $cdata2 = $update['callback_query']['data'];
    $cchatid2 = $update['callback_query']['message']['chat']['id'];
    $cmessage_id2 = $update['callback_query']['message']['message_id'];
    $queryId = $update['callback_query']['id'];
    $queryUserId = $update['callback_query']['from']['id'];
    $queryOriginId = $update['callback_query']['message']['reply_to_message']['from']['id'] ?? $queryUserId;

    if ($queryUserId !== $adminUserId) {
        answerCallbackQuery($queryId, "Access denied. Only admin user Anish can use this.", true);
        die();
    }

    // Menu callback
    if ($cdata2 == "menu") {
        bot('editMessageText', [
            'chat_id' => $cchatid2,
            'message_id' => $cmessage_id2,
            'text' => "<b>📋 Bot Menu</b>\n\nHere are the available commands:\n- /gen: Generate CC\n- /bin: Lookup BIN info\n- /spotify: Generate Spotify account\n\nFor help, contact Customer Care.",
            'parse_mode' => 'html',
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => 'Generate CC (/gen)', 'callback_data' => 'gen_info'],
                        ['text' => 'BIN Lookup (/bin)', 'callback_data' => 'bin_info']
                    ],
                    [
                        ['text' => 'Spotify (/spotify)', 'callback_data' => 'spotify_info'],
                        ['text' => 'Customer Care 📞', 'url' => 'https://t.me/yoursupportusername']
                    ],
                    [['text' => 'Back to Cmds', 'callback_data' => 'cmds']]
                ]
            ])
        ]);
        answerCallbackQuery($queryId, "Menu opened!", false);
        exit;
    }

    // Generate callbacks (gen2 and hod)
    if ($cdata2 == "gen2" || $cdata2 == "hod") {
        $tiempo_inicial = microtime(true);
        if ($queryOriginId != $queryUserId) {
            $response = "Access denied Generate your own Button🚫";
            answerCallbackQuery($queryId, $response, true);
            exit;
        }

        $messq = $update["callback_query"]["message"]["reply_to_message"]["text"];
        $lista = substr($messq, 1);
        $input = preg_replace("/\W/", " ", $lista);
        $input = preg_replace("/\r|\n/", ' ', $input);
        $input = preg_replace("/[^0-9x]/", ' ', $input);
        $input = preg_replace('/\s+/', ' ', $input);
        $input = trim($input, ' ');
        $card = explode(" ", $input);

        $cc = $card[0];
        $mon = $card[1] ?? '';
        $year = $card[2] ?? '';
        $cvv2 = $card[3] ?? '';

        if (strlen($year) == 2) $year = "20" . $year;
        if (strlen($mon) == 1) $mon = "0" . $mon;
        if (strlen($cc) == 16) $cc = substr($cc, 0, 12);

        $cc3 = substr($cc, 0, 6);
        $res = binlookUp($cc3);
        $type = $res->type;
        $bank = $res->bank;
        $brand = $res->brand;
        $scheme = $res->level;
        $country = $res->country;
        $emoji = $res->emoji;

        $quantidade = ($cdata2 == "gen2") ? 5 : 10;
        $card = CreditCardGenerator::generateCC($cc, $mon, $year, $cvv2, $quantidade);

        if (empty($mon)) $mon = 'rnd';
        if (empty($year)) $year = 'rnd';
        if (empty($cvv2)) $cvv2 = 'rnd';

        $cco = str_pad($cc, 16, "x");
        $tiempo_final = microtime(true);
        $tiempo = substr($tiempo_final - $tiempo_inicial, 0, 4);

        bot('editMessageText', [
            'chat_id' => $cchatid2,
            'message_id' => $cmessage_id2,
            'text' => "<b>-🪁Info Bin : <code>$cc3 - $brand - $scheme - $type | $bank </code>[$emoji]
<b>Formato :</b><code> $cco|$mon|$year|$cvv2|</code>
——————————————————
<code>$card</code>
——————————————————
<b>Time :</b> <code>$tiempo s</code></b>",
            'parse_mode' => 'html',
            'reply_to_message_id' => $cmessage_id2,
            'reply_markup' => json_encode(['inline_keyboard' => [
                [['text' => 'Generate Again!🎡', 'callback_data' => 'hod'],
                ['text' => 'Generate Mass!⛽️', 'callback_data' => 'gen2']]
            ]])
        ]);
    }
}

?>
