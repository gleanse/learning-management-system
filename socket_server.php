<?php
// php socket_server.php
// php -S 192.168.254.100:8080 -t public

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

define('SOCKET_ADDRESS', $_SERVER['SOCKET_ADDRESS'] ?? '0.0.0.0');
define('SOCKET_PORT', (int) ($_SERVER['SOCKET_PORT'] ?? 8888));

set_time_limit(0);

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) {
    die("Socket creation failed: " . socket_strerror(socket_last_error()) . "\n");
}

socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);

if (!socket_bind($socket, SOCKET_ADDRESS, SOCKET_PORT)) {
    die("Socket bind failed: " . socket_strerror(socket_last_error($socket)) . "\n");
}

if (!socket_listen($socket)) {
    die("Socket listen failed: " . socket_strerror(socket_last_error($socket)) . "\n");
}

echo "====================================\n";
echo " LMS Socket Server\n";
echo " Address : " . SOCKET_ADDRESS . "\n";
echo " Port    : " . SOCKET_PORT . "\n";
echo " Status  : Running\n";
echo "====================================\n";
echo "Waiting for connections...\n\n";

$clients = [$socket];

while (true) {
    $read = $clients;
    $write = null;
    $except = null;

    if (socket_select($read, $write, $except, null) === false) {
        echo "Socket select failed: " . socket_strerror(socket_last_error()) . "\n";
        break;
    }

    // new client connecting
    if (in_array($socket, $read)) {
        $new_client = socket_accept($socket);
        if ($new_client !== false) {
            $clients[] = $new_client;
            echo "[" . date('Y-m-d H:i:s') . "] Client connected. Total: " . (count($clients) - 1) . "\n";
        }

        $key = array_search($socket, $read);
        unset($read[$key]);
    }

    // handle messages from clients
    foreach ($read as $client) {
        $message = socket_read($client, 1024);

        // client disconnected
        if ($message === false || $message === '') {
            $key = array_search($client, $clients);
            unset($clients[$key]);
            socket_close($client);
            echo "[" . date('Y-m-d H:i:s') . "] Client disconnected. Total: " . (count($clients) - 1) . "\n";
            continue;
        }

        $message = trim($message);
        if (empty($message)) continue;

        echo "[" . date('Y-m-d H:i:s') . "] Event received: $message\n";

        // broadcast to all connected clients except sender
        $broadcast_count = 0;
        foreach ($clients as $broadcast_client) {
            if ($broadcast_client !== $socket && $broadcast_client !== $client) {
                socket_write($broadcast_client, $message, strlen($message));
                $broadcast_count++;
            }
        }

        echo "[" . date('Y-m-d H:i:s') . "] Broadcasted to $broadcast_count client(s)\n";
    }
}

socket_close($socket);
