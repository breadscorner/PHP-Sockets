<?php

require './ca.php';
require './lib/debug.php';

$publicKey = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAjDqMMsCkrsuhfNHuvKVc
XXHbfLHtFocJuVF3nf3ISWE6Vzr03HWo+aSkFeIpUsc95W8YPt9cJ4r2bKXqkd0r
v7PgtBJi5eTjF5nBFZgXa0kKfpG/Xwv1oKk/ITlPa+UMJvs4+BbK1OGoiOZhMM90
LV+8W0mp++FOFrFrRAP86qisaxijz05jaA6hcRUpLpV8Y2nkf6lNA5bNt1fylX3l
9jhNqR/eXrC5aZHduahGei17ItUVJWj+/CGUqXYoxqXQ4wZF707xqDKgrb6+oV1f
89TAAGlywSmDCJ1sNR0TyeBEgW7h8GPG9DUgcTu19K4txx6SphruUmUWA0DyNFoD
LQIDAQAB
-----END PUBLIC KEY-----';

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_connect($socket, 'localhost', 4443);

if (!$socket) {
    echo "Failed to create socket.\n";
    exit();
}

echo "Connected to server on port 4443\n";

// Step 1: Generate 32 random bytes
$clientRandom = random_bytes(32);

// Convert to hex using bin2hex()
$clientRandomHex = bin2hex($clientRandom);

// Write $clientRandom to the socket
socket_write($socket, $clientRandomHex, strlen($clientRandomHex));

// Step 2: Read 64 bytes from the socket into $serverRandom
$serverRandom = socket_read($socket, 64);

// Read 108 bytes from the socket into $serverCertificate
$serverCertificate = socket_read($socket, 108);

// Create an instance of the CertificateAuthority class
$certificateAuthority = new CertificateAuthority();

// Call its validateCertificate() method
if (!$certificateAuthority->validateCertificate('server.php', $serverCertificate)) {
    // If validation fails, echo a message to the user, close the socket, and exit
    echo "Certificate validation failed. Closing connection.";
    socket_close($socket);
    exit();
}

// Generate 48 random bytes
$preMasterSecret = random_bytes(48);

// Convert them to hex using bin2hex()
$preMasterSecretHex = bin2hex($preMasterSecret);

// Encrypt the Pre-Master Secret using openssl_public_encrypt() with the server's public key ($publicKey)
$encryptedPreMasterSecret = '';
openssl_public_encrypt($preMasterSecretHex, $encryptedPreMasterSecret, $publicKey);

// Write the encrypted Pre-Master Secret to the socket
socket_write($socket, $encryptedPreMasterSecret, strlen($encryptedPreMasterSecret));

// Concatenate Pre-Master Secret, Client Random, and Server Random
$concatenatedData = $preMasterSecretHex . $clientRandomHex . $serverRandom;

// Hash the concatenated data using the sha256 algorithm
$masterSecret = hash('sha256', $concatenatedData);

// Read 5 bytes from the socket
$readyIndicator = socket_read($socket, 5);

// Check if the value is "READY"
if ($readyIndicator !== "READY") {
    // If not "READY", close the socket and exit
    socket_close($socket);
    exit();
}

echo "Server is ready\n";

$LEN = 0;
$algo = 'aes-256-cbc';
$iv = random_bytes(16);

while (true) {
    // Capture a message from the user
    $data = readline("Enter your message: ");

    // Encrypt the message
    $encryptedData = openssl_encrypt($data, $algo, $preMasterSecretHex, OPENSSL_RAW_DATA, $iv);

    // Convert the encrypted message to hex
    $encryptedDataHex = bin2hex($encryptedData);

    // Get the length of the encrypted hex message
    $LEN = strlen($encryptedDataHex);

    // Send the length of the encrypted message to the server
    $lenMessage = "LEN=" . str_pad($LEN, 4, '0', STR_PAD_LEFT);
    $bytesWritten = socket_write($socket, $lenMessage, strlen($lenMessage));

    // Check if anything was sent
    if ($bytesWritten === 0) {
        echo "Error: Nothing was sent to the server. Closing connection.";
        socket_close($socket);
        exit();
    }

    // Read the server's acknowledgment message
    $ackMessage = socket_read($socket, 8);

    // Extract ACK length
    preg_match('/ACK=(\d{4})/', $ackMessage, $matches);
    $ackLength = intval($matches[1]);

    // Check if ACK length matches the length transmitted previously
    if ($ackLength === $LEN) {
        // Write the encrypted data to the socket
        $bytesWritten = socket_write($socket, $encryptedDataHex, strlen($encryptedDataHex));
        // Check if anything was sent
        if ($bytesWritten === 0) {
            echo "Error: Nothing was sent to the server. Closing connection.";
            socket_close($socket);
            exit();
        }
    } else {
        echo "Error: Server did not respond properly. Closing connection.";
        socket_close($socket);
        exit();
    }
}

?>
