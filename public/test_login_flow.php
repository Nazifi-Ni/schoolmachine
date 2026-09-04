<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/login');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['username' => 'headteacher', 'password' => 'password']));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
$loginRes = curl_exec($ch);
echo "Login: " . $loginRes . "\n";

curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/me');
curl_setopt($ch, CURLOPT_POST, 0);
$meRes = curl_exec($ch);
echo "Me: " . $meRes . "\n";
