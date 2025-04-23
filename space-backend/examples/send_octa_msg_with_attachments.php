<?php
/**
 * Example script to send a message with attachments to Octa
 * 
 * This demonstrates how to:
 * 1. Convert files to base64
 * 2. Create the proper JSON payload
 * 3. Send the request to the controller
 */

// Example chat ID
$chatId = '06f4ea35-5ec0-4c7d-b52a-a10cf15e1cfe';

// Example URL (adjust according to your environment)
$url = "http://localhost:8000/api/octa/post-octa-chat-msg-with-attachments/{$chatId}/messages";

// Function to convert a file to base64
function fileToBase64($filePath) {
    $fileContent = file_get_contents($filePath);
    if ($fileContent === false) {
        throw new Exception("Could not read file: $filePath");
    }
    return base64_encode($fileContent);
}

// Create the payload
$payload = [
    'type' => 'public',
    'channel' => 'whatsapp',
    'body' => 'Oi, estou enviando anexo para testar.',
    'attachments' => [
        [
            'name' => 'a.png',
            'base64' => fileToBase64('path/to/a.png'), // Replace with actual path
            'mimeType' => 'image/png'
        ],
        [
            'name' => 'b.png',
            'base64' => fileToBase64('path/to/b.png'), // Replace with actual path
            'mimeType' => 'image/png'
        ]
    ]
];

// Convert to JSON
$jsonPayload = json_encode($payload);

// HTTP request using cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

// Execute the request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Output the result
echo "HTTP Status Code: $httpCode\n";
echo "Response: $response\n"; 