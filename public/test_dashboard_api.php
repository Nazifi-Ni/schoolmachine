<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/student-login');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['registration_number' => 'IAMS/2026/9139', 'pin' => '9139']));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies_dash.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies_dash.txt');
curl_exec($ch);

curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/student-portal/dashboard');
curl_setopt($ch, CURLOPT_POST, 0);
echo curl_exec($ch) . "\n";
