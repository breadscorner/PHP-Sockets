<?php

require './ca.php';
require './lib/debug.php';

$privateKey = '-----BEGIN PRIVATE KEY-----
MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCMOowywKSuy6F8
0e68pVxdcdt8se0Whwm5UXed/chJYTpXOvTcdaj5pKQV4ilSxz3lbxg+31wnivZs
peqR3Su/s+C0EmLl5OMXmcEVmBdrSQp+kb9fC/WgqT8hOU9r5Qwm+zj4FsrU4aiI
5mEwz3QtX7xbSan74U4WsWtEA/zqqKxrGKPPTmNoDqFxFSkulXxjaeR/qU0Dls23
V/KVfeX2OE2pH95esLlpkd25qEZ6LXsi1RUlaP78IZSpdijGpdDjBkXvTvGoMqCt
vr6hXV/z1MAAaXLBKYMInWw1HRPJ4ESBbuHwY8b0NSBxO7X0ri3HHpKmGu5SZRYD
QPI0WgMtAgMBAAECggEAExcdYwNq6Aj80RtbXv88FdScRtuKJVj47+uxVybnj2XX
JWz3TNQPzvylAf2qFoTdXlDDgjuyNgfrCFuGFZmAjTaVzq36HMYOTHY4HRJ5jbeB
4D1DSlM8e0TPPVyp/UxPXTcySEQCFP0rjoeej6COdmbkI6FhrNK9aMV6juXkFqXw
RijJoAckI19loZqDh2weWGza9io/p1AmRs8FWBCkjIFXPjJ0B5d06IyQRq0SYPku
FsyZLauSay2ftVtfP/wqXDYWAk3IZegBNMu8LdhJH8Hct2Zr1k4bE4yZirCvUFa/
ml9hRGsXYUMDZgSaR910ZyQzZS0ua8a6oK3eR1ec2QKBgQC3rdeZby9ctU5qRuEO
ZJ8/ZPuWKk9qyq1wc73Mie2p7A+65qUuXAQn3YqXbcWQxn3BQKX5YGMdA9rPuhxu
1UOvGyOeYnNDrPQSiCaSTM6iQ+jhMTY8p/PqwXA3z3x7F/OKg9jA9KZv7a796hW+
J9JuezToyC9n8NKo81IpmAUiowKBgQDDcRJy95lPvHZjUtQuA9/r8utPkQ4ySedP
aBAD++W478OUqqw0kkTqJrMQuDEvEo78R644ObmdJGe9rRiEpkgRlkp1ulBiEX+o
Hq5aicx19AKyTH+6o8x7Ir7ghMgT/RlROoCejNvuvpuUENFfQRK3MB7YXhq1PPvj
7RXHXu5v7wKBgQCstCtVHGLnA56wdOaltty5KcUY4716hwlfA6TBTisGK2x66uUD
WweZSEhIq7EouEmDzLqCaSuoG3jA+phDagjS+2yZPq5sQpHXXucNhmR/0+SC4NfD
XpQM9kcCYvgDcXjPk7rZau+XrF9uZYx+GElXEkekXJ2eWKRqsSZe745ciwKBgBep
1iEDZ5Wm7PKjsbsMjw0jcWhF2OEv34jWwbGpyyu0JAsZCxamax+qpd2tX48igRt8
llSKcLXdFY56qdBNzcYLW2Kbt2XYVouFg3jE3HOfor/x0TlI4dY647+NdCgvaeRS
4AXSakKi43Vu/9q3p0t00RdDdZpiEuGK8CsejGITAoGASn41HcGtAVD+UT7YYKOg
puPedHiM8zsazRY7/EHyvDjiSuyyJyoC/tUsS24CbgxY24JrMmhfPufXRcdLVAp7
of8xRXg8qkbyQDTavs1D+ripXzPkW7BM1kZolkNzfqUFgkwKzm4D0fgYKITl9RFi
gXDVOMiTscTiSLW/KaHWUTg=
-----END PRIVATE KEY-----';

$publicKey = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAjDqMMsCkrsuhfNHuvKVc
XXHbfLHtFocJuVF3nf3ISWE6Vzr03HWo+aSkFeIpUsc95W8YPt9cJ4r2bKXqkd0r
v7PgtBJi5eTjF5nBFZgXa0kKfpG/Xwv1oKk/ITlPa+UMJvs4+BbK1OGoiOZhMM90
LV+8W0mp++FOFrFrRAP86qisaxijz05jaA6hcRUpLpV8Y2nkf6lNA5bNt1fylX3l
9jhNqR/eXrC5aZHduahGei17ItUVJWj+/CGUqXYoxqXQ4wZF707xqDKgrb6+oV1f
89TAAGlywSmDCJ1sNR0TyeBEgW7h8GPG9DUgcTu19K4txx6SphruUmUWA0DyNFoD
LQIDAQAB
-----END PUBLIC KEY-----';

// Call socket_create using the options AF_INET, SOCK_STREAM, SOL_TCP and store the socket instance in a variable named $socket
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

// Use socket_bind to bind the socket to port 4443 - if the binding fails, print a message to the user and exit()
if (!socket_bind($socket, 'localhost', 4443)) {
  echo "Failed to bind to port 4443\n";
  exit();
}

// Call socket_listen to start listening for client connections
socket_listen($socket);

// Call socket_accept to accept an incoming connection, and store the returned value in a variable named $client
$client = socket_accept($socket);

echo "Server listening on port 4443\n";

// Step 1:
// Read 64 bytes, the "Client Random"
$clientRandom = socket_read($client, 64);

// Step 2a:
// Generate 32 random bytes and convert to hex (using bin2hex()), store the value in a variable named $serverRandom
$serverRandom = bin2hex(random_bytes(32));

// Write $serverRandom to the socket
socket_write($client, $serverRandom, strlen($serverRandom));

// Step 2b:
// Create a new instance of the CertificateAuthority class
$certificateAuthority = new CertificateAuthority();

// Hash the server's public key using the hash() method with sha256 algorithm
$hashedPublicKey = hash('sha256', $publicKey);

// Call the getSSLCertificate() method of the CertificateAuthority, passing in the hashed public key
$serverCertificate = $certificateAuthority->getSSLCertificate($hashedPublicKey);

// Write the returned certificate to the socket
socket_write($client, $serverCertificate, strlen($serverCertificate));

// Step 3:
// Read 304 bytes, the "Pre-Master Secret"
$encryptedPreMasterSecret = socket_read($client, 304);

// Step 4:
// Decrypt the Pre-Master Secret using openssl_private_decrypt()
openssl_private_decrypt($encryptedPreMasterSecret, $preMasterSecret, $privateKey);

// Step 5: 
// Use the hash() function with sha256 algorithm to hash the concatenated Pre-Master Secret, Client Random, and Server Random
$concatenatedData = $preMasterSecret . $clientRandom . $serverRandom;
$masterSecret = hash('sha256', $concatenatedData);

// Write the string READY to the socket to indicate to the client that the server is ready to accept messages
socket_write($client, "READY", strlen("READY"));

// Step 6:
// Begin receiving messages from the client
$algo = 'aes-256-cbc'; // encryption algorithm
$iv = random_bytes(16);

// Infinite loop to receive messages from the client
while (true) {
  echo "Server waiting for data\n";

  // Before sending data, the client must indicate how many bytes it will send
  // Read the LEN message
  $lenMessage = socket_read($client, 8);

  // Check for errors while reading the message length
  if ($lenMessage === false) {
      echo "Failed to read message length\n";
      break; // Exit the loop if there's an error
  }

  preg_match('/LEN=(\d{4})/', $lenMessage, $matches);
  $dataLength = intval($matches[1]);

  // Send an acknowledgment message
  socket_write($client, "ACK=" . str_pad($dataLength, 4, '0', STR_PAD_LEFT), strlen("ACK=" . str_pad($dataLength, 4, '0', STR_PAD_LEFT)));

  // Read n bytes
  $encryptedDataHex = socket_read($client, $dataLength);

  // Check for errors while reading the encrypted data
  if ($encryptedDataHex === false) {
      echo "Failed to read encrypted data\n";
      break; // Exit the loop if there's an error
  }

  // Convert the received bytes to binary
  $encryptedData = hex2bin($encryptedDataHex);

  // Decrypt the message
  $decryptedData = openssl_decrypt($encryptedData, $algo, $masterSecret, OPENSSL_RAW_DATA, $iv);

  // Check if decryption was successful
  if ($decryptedData === false) {
      echo "Failed to decrypt the message\n";
      continue; // Skip to the next iteration of the loop
  }

  // Echo the unencrypted message
  $decryptedData = trim($decryptedData); // Remove leading/trailing whitespace
  if (!empty($decryptedData)) {
      echo "Received message from client: $decryptedData\n";
  } else {
      echo "Received empty message from client\n";
  }
}

// Close the client socket
socket_close($client);

// Close the server socket
socket_close($socket);
