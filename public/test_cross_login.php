<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/student-login');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['registration_number' => 'IAMS/2026/9139', 'pin' => '9139']));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies2.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies2.txt');
echo "Student Login: " . curl_exec($ch) . "\n";

curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/me');
curl_setopt($ch, CURLOPT_POST, 0);
echo "Me (Student): " . curl_exec($ch) . "\n";

curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/login');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['username' => 'headteacher', 'password' => 'password']));
echo "Headteacher Login: " . curl_exec($ch) . "\n";

curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/me');
curl_setopt($ch, CURLOPT_POST, 0);
echo "Me (Headteacher): " . curl_exec($ch) . "\n";
