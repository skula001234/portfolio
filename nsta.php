<?php
header('Content-Type: application/json');

// Step 1: Validate Input
if (!isset($_GET['url']) || empty($_GET['url'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing Instagram URL']);
    exit;
}

$inputUrl = $_GET['url'];
$encodedUrl = urlencode($inputUrl);
$targetUrl = "https://snapdownloader.com/tools/instagram-reels-downloader/download?url={$encodedUrl}";

// Step 2: cURL request to snapdownloader
$ch = curl_init($targetUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

// Step 3: Error Handling
if ($error || !$response) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch data']);
    exit;
}

// Step 4: Extract video URL
preg_match('/<a[^>]+href="([^"]+\.mp4[^"]*)"[^>]*>/', $response, $videoMatch);
$videoUrl = html_entity_decode($videoMatch[1] ?? '');

// Step 5: Extract image thumbnail (try two methods)

// Method 1: from base64 image (not always useful)
preg_match('/<img[^>]+src="data:image\/jpg;base64,([^"]+)"/', $response, $thumbBase64Match);
$thumbBase64 = $thumbBase64Match[1] ?? null;

// Method 2: from external jpg link
preg_match('/<a[^>]+href="([^"]+\.jpg[^"]*)"[^>]*>/', $response, $thumbMatch);
$thumbUrl = html_entity_decode($thumbMatch[1] ?? '');

// Step 6: Return clean JSON
if ($videoUrl) {
    echo json_encode([
        'status' => 'success',
        'video' => $videoUrl,
        'thumbnail' => $thumbUrl,
        'thumbnail_base64' => $thumbBase64 ? 'data:image/jpg;base64,' . $thumbBase64 : null
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to extract video'
    ]);
}