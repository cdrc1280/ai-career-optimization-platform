<?php
$token = '1|iuGTUCD9F7MC54KPup6UOfEAq8ODF9No9rBvhcYJa1a07b45';
$url = 'http://127.0.0.1:8000/api/v1/resumes';

$opts = [
    'http' => [
        'method' => 'GET',
        'header' => "Authorization: Bearer $token\r\nUser-Agent: PHP-test\r\n",
        'ignore_errors' => true,
        'timeout' => 10,
    ],
];
$context = stream_context_create($opts);
$body = @file_get_contents($url, false, $context);
$status = $http_response_header[0] ?? 'HTTP/1.1 0';

echo "STATUS: $status\n\n";
if (!empty($http_response_header)) {
    foreach ($http_response_header as $h) {
        echo $h . "\n";
    }
}

echo "\nBODY:\n";
echo $body ?: "(no body)";
