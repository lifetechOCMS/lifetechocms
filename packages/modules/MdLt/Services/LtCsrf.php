<?php
namespace Lt\Modules\MdLt\Services;

class LtCsrf
{
    /**
     * Generate or retrieve the CSRF token.
     */
    public static function token(): string
    {
       self::requireSession();

        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(
                random_bytes(32)
            );
        }

        return $_SESSION['_csrf_token'];
    }

    /**
     * Validate CSRF token for the current request.
     *
     * GET, HEAD, OPTIONS are exempted.
     * POST, PUT, PATCH, DELETE require validation.
     */
    public static function validate(?string $token = null): bool
    {
        $method = self::resolveRequestMethod();

        // Exempt safe HTTP methods
        if (in_array(
            $method,
            ['GET', 'HEAD', 'OPTIONS'],
            true
        )) {
            return true;
        }

        // Reject unsupported HTTP methods
        if (!in_array(
            $method,
            ['POST', 'PUT', 'PATCH', 'DELETE'],
            true
        )) {
            return false;
        }

        self::requireSession();

        // Retrieve token when not supplied explicitly
        if ($token === null) {
            $request = self::getRequestData();

            $token = $_SERVER['HTTP_X_CSRF_TOKEN']
                ?? ($request['_csrf_token'] ?? null);
        }

        return is_string($token)
            && $token !== ''
            && isset($_SESSION['_csrf_token'])
            && is_string($_SESSION['_csrf_token'])
            && hash_equals(
                $_SESSION['_csrf_token'],
                $token
            );
    }

    /**
     * Generate a hidden CSRF input for HTML forms.
     */
    public static function input(): string
    {
        return '<input type="hidden" name="_csrf_token" id="_csrf_token" value="' .
            htmlspecialchars(
                self::token(),
                ENT_QUOTES,
                'UTF-8'
            ) .
            '">';
    }

    /**
     * Resolve HTTP method with method spoofing support.
     */
    private static function resolveRequestMethod(): string
    {
        $method = strtoupper(
            $_SERVER['REQUEST_METHOD'] ?? 'GET'
        );

        if (
            $method === 'POST' &&
            isset($_POST['_method']) &&
            is_string($_POST['_method'])
        ) {
            $spoofed = strtoupper($_POST['_method']);

            if (in_array(
                $spoofed,
                ['PUT', 'PATCH', 'DELETE'],
                true
            )) {
                return $spoofed;
            }
        }

        return $method;
    }

    /**
     * Extract request data based on content type.
     */
    private static function getRequestData(): array
    {
        $actualMethod = strtoupper(
            $_SERVER['REQUEST_METHOD'] ?? 'GET'
        );

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (stripos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');

            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : [];
        }

        // Standard HTML form and spoofed methods
        if ($actualMethod === 'POST') {
            return $_POST;
        }

        if (in_array(
            $actualMethod,
            ['PUT', 'PATCH', 'DELETE'],
            true
        )) {
            parse_str(
                file_get_contents('php://input'),
                $parsed
            );

            return $parsed;
        }

        return $_GET;
    }
 
      /**
     * Display Error message when CSRF input not valid from HTML forms.
     */
    public static function errorMessage(): string
    {
        return "Invalid CSRF token, Refresh the Page before submission";
    }
    
   /**
     * Ensure a PHP session is active.
     */
    private static function requireSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            if (!session_start()) {
                throw new \RuntimeException(
                    'Unable to start CSRF session.'
                );
            }
        }
    
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new \RuntimeException(
                'CSRF session is not active.'
            );
        }
    }
}
