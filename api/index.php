<?php
/**
 * Noor Al-Quloob API Cache & Processing Hub
 * Designed for EXTREME speed and reliability.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$action = $_GET['action'] ?? '';

// Support for Vercel Serverless (Read-only FS except /tmp)
if (isset($_SERVER['VERCEL'])) {
    $cacheDir = '/tmp/cache';
} else {
    // Local development: try to find a writable cache dir
    $cacheDir = __DIR__ . '/../cache';
}

if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0777, true);
}

function fetchWithCache($url, $cacheFile, $ttl = 86400) {
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
        return file_get_contents($cacheFile);
    }
    
    // Set a fast timeout
    $ctx = stream_context_create(['http' => ['timeout' => 5]]);
    $data = @file_get_contents($url, false, $ctx);
    
    if ($data) {
        file_put_contents($cacheFile, $data);
        return $data;
    }
    
    // Fallback to stale cache if API is down
    if (file_exists($cacheFile)) {
        return file_get_contents($cacheFile);
    }
    
    return json_encode(['error' => 'Network error and no cache']);
}

switch ($action) {
    case 'surahs':
        echo fetchWithCache('https://api.alquran.cloud/v1/surah', "$cacheDir/surahs.json", 604800); // Cache 1 week
        break;
        
    case 'reciters':
        echo fetchWithCache('https://www.mp3quran.net/api/v3/reciters?language=ar', "$cacheDir/reciters.json", 604800);
        break;
        
    case 'radios':
        echo fetchWithCache('https://www.mp3quran.net/api/v3/radios?language=ar', "$cacheDir/radios.json", 86400);
        break;
        
    case 'surah_text':
        $id = (int)($_GET['id'] ?? 1);
        echo fetchWithCache("https://api.alquran.cloud/v1/surah/$id", "$cacheDir/surah_$id.json", 604800);
        break;
        
    case 'tafsir':
        $id = (int)($_GET['id'] ?? 1);
        $type = preg_replace('/[^a-z.]/', '', $_GET['type'] ?? 'ar.muyassar');
        echo fetchWithCache("https://api.alquran.cloud/v1/surah/$id/$type", "$cacheDir/tafsir_{$id}_{$type}.json", 604800);
        break;
        
    case 'download_full_quran_script':
        // Generate a bat/sh script that downloads the full Quran at maximum speed using curl/wget
        $server = $_GET['server'] ?? '';
        $reciterName = preg_replace('/[^a-zA-Z0-9_ \p{Arabic}]/u', '', $_GET['name'] ?? 'Reciter');
        
        if (!$server) {
            die("Server required");
        }
        
        header('Content-Type: application/bat');
        header('Content-Disposition: attachment; filename="Download_Quran_' . str_replace(' ', '_', $reciterName) . '.bat"');
        
        echo "@echo off\n";
        echo "echo ==================================================\n";
        echo "echo Downloading Full Quran - $reciterName\n";
        echo "echo ==================================================\n";
        echo "mkdir \"Quran_$reciterName\"\n";
        echo "cd \"Quran_$reciterName\"\n";
        for ($i = 1; $i <= 114; $i++) {
            $num = str_pad($i, 3, '0', STR_PAD_LEFT);
            echo "echo Downloading Surah $num...\n";
            echo "curl -O -# {$server}{$num}.mp3\n";
        }
        echo "echo ==================================================\n";
        echo "echo Download Complete!\n";
        echo "pause\n";
        exit;

    case 'download_file':
        $url = $_GET['url'] ?? '';
        $filename = $_GET['filename'] ?? 'surah.mp3';
        if ($url) {
            header('Content-Type: audio/mpeg');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            readfile($url);
            exit;
        }
        break;

    case 'download_zip_resolver':
        $server = $_GET['server'] ?? '';
        if (!$server) die("Server required");
        
        $folder = basename(rtrim($server, '/'));
        
        // Patterns to try
        $patterns = [
            "{$server}{$folder}_archive.zip",
            "{$server}{$folder}.zip",
            "{$server}zip/{$folder}.zip",
            "https://download.mp3quran.net/download/{$_GET['reciter']}/{$_GET['moshaf']}"
        ];
        
        foreach ($patterns as $url) {
            $headers = @get_headers($url);
            if ($headers && strpos($headers[0], '200') !== false) {
                header("Location: $url");
                exit;
            }
        }
        
        // Final fallback: just redirect to the most likely one even if HEAD fails
        header("Location: {$server}{$folder}_archive.zip");
        exit;

    default:
        echo json_encode([
            'status' => 'success',
            'message' => 'Welcome to Noor Al-Quloob Fast API',
            'endpoints' => [
                '?action=surahs',
                '?action=reciters',
                '?action=radios',
                '?action=surah_text&id=1',
                '?action=tafsir&id=1&type=ar.muyassar',
                '?action=download_full_quran_script&server=URL&name=RECITER'
            ]
        ], JSON_UNESCAPED_UNICODE);
        break;
}
