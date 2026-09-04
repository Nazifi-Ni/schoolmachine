<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/login');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['username' => 'admin', 'password' => 'admin123']));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies_ht.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies_ht.txt');
curl_exec($ch);

curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/finance/bills/generate');
curl_setopt($ch, CURLOPT_POST, 1);
echo curl_exec($ch) . "\n";
