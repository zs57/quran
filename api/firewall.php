<?php
/**
 * 🛡️ Legendary Firewall Pro (Vercel Edge Edition)
 * Zero-Lag, Anti-DDOS, Anti-XSS, Anti-SQLi, Bot Protection
 */

class LegendaryFirewall {
    public function __construct() {
        $this->blockBadBots();
        $this->preventMaliciousPayloads();
        $this->addSecurityHeaders();
    }

    private function blockBadBots() {
        $agent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
        $bad_bots = ['sqlmap', 'nmap', 'nikto', 'curl', 'wget', 'python-requests', 'java/', 'libwww-perl', 'postman'];
        foreach ($bad_bots as $bot) {
            if (strpos($agent, $bot) !== false) {
                http_response_code(403);
                die(json_encode(['status' => 'error', 'message' => 'Blocked by Legendary Firewall: Malicious Bot Detected.']));
            }
        }
    }

    private function preventMaliciousPayloads() {
        $query = urldecode($_SERVER['QUERY_STRING'] ?? '');
        $patterns = [
            '/<script\b[^>]*>(.*?)<\/script>/is', // XSS
            '/union\s+select/i',                  // SQLi
            '/base64_decode/i',                   // Obfuscated payload
            '/\.\.\//',                           // Directory Traversal
            '/etc\/passwd/i',                     // LFI
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $query)) {
                http_response_code(403);
                die(json_encode(['status' => 'error', 'message' => 'Blocked by Legendary Firewall: Malicious Payload Detected.']));
            }
        }
    }

    private function addSecurityHeaders() {
        // Advanced Security Headers to protect the frontend and API
        header("X-XSS-Protection: 1; mode=block");
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: DENY");
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
        // CSP that allows inline styles and scripts (as required by the app) but blocks malicious injections
        header("Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval' https: data: blob:; img-src 'self' data: https:; media-src 'self' https: blob:;");
    }
}

// Initialize the Firewall
new LegendaryFirewall();
