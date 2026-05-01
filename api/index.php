<?php
/**
 * Noor Al-Quloob API - Serverless Entry
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$action = $_GET['action'] ?? '';

// Support for Vercel Serverless (Read-only FS except /tmp)
if (isset($_SERVER['VERCEL'])) {
    $cacheDir = '/tmp/cache';
} else {
    $cacheDir = __DIR__ . '/../cache';
}

if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0777, true);
}

function fetchWithCache($url, $cacheFile, $ttl = 86400) {
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
        return file_get_contents($cacheFile);
    }
    
    $ctx = stream_context_create(['http' => ['timeout' => 5]]);
    $data = @file_get_contents($url, false, $ctx);
    
    if ($data) {
        file_put_contents($cacheFile, $data);
        return $data;
    }
    
    if (file_exists($cacheFile)) {
        return file_get_contents($cacheFile);
    }
    
    return json_encode(['error' => 'Network error and no cache']);
}

switch ($action) {
    case 'surahs':
        echo fetchWithCache('https://api.alquran.cloud/v1/surah', "$cacheDir/surahs.json", 604800);
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
    default:
        echo json_encode(['status' => 'ok', 'message' => 'API is online']);
        break;
}
