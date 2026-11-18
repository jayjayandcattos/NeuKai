<?php
/**
 * Shared helpers: escaping, sessions, CSRF, auth guards, uploads.
 */

/* -------------------------------------------------------------------------
 * Output escaping
 * ---------------------------------------------------------------------- */

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        if (is_array($value) || is_object($value)) {
            return htmlspecialchars((string) json_encode($value), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

/* -------------------------------------------------------------------------
 * Session / headers
 * ---------------------------------------------------------------------- */

if (!function_exists('secure_session_start')) {
    function secure_session_start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.sid_length', '48');
        ini_set('session.sid_bits_per_character', '6');

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $https,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
    }
}

if (!function_exists('security_headers')) {
    function security_headers(): void
    {
        if (headers_sent()) {
            return;
        }

        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        header('Cross-Origin-Opener-Policy: same-origin');

        // Deliberately permissive enough for the existing Tailwind CDN + inline handlers,
        // while still restricting scripts/styles/images to trusted origins.
        header(
            "Content-Security-Policy: default-src 'self'; "
            . "base-uri 'self'; "
            . "form-action 'self'; "
            . "frame-ancestors 'self'; "
            . "object-src 'none'; "
            . "img-src 'self' data:; "
            . "font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com; "
            . "style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com https://fonts.googleapis.com; "
            . "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; "
            . "connect-src 'self'"
        );
    }
}

if (!function_exists('redirect')) {
    function redirect(string $location): never
    {
        header('Location: ' . $location, true, 303);
        exit;
    }
}

/* -------------------------------------------------------------------------
 * CSRF
 * ---------------------------------------------------------------------- */

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('csrf_verify')) {
    function csrf_verify(): void
    {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        if (!is_string($token) || $token === '' || !hash_equals(csrf_token(), $token)) {
            http_response_code(419);
            exit('Invalid or expired security token. Please reload the page and try again.');
        }
    }
}

/* -------------------------------------------------------------------------
 * Auth guards
 * ---------------------------------------------------------------------- */

if (!function_exists('require_admin')) {
    function require_admin(string $requiredRole = 'assistant_admin'): void
    {
        if (empty($_SESSION['admin_id'])) {
            redirect('../login.php');
        }

        if ($requiredRole === 'main_admin' && ($_SESSION['role'] ?? '') !== 'main_admin') {
            http_response_code(403);
            exit('Forbidden: this action requires a main administrator account.');
        }
    }
}

if (!function_exists('require_charity')) {
    function require_charity(): void
    {
        if (empty($_SESSION['charity_id'])) {
            redirect('../login.php');
        }
    }
}

if (!function_exists('require_donor')) {
    function require_donor(): void
    {
        if (empty($_SESSION['donator_id'])) {
            redirect('../login.php');
        }
    }
}

/* -------------------------------------------------------------------------
 * Flash messages
 * ---------------------------------------------------------------------- */

if (!function_exists('flash_set')) {
    function flash_set(string $key, string $message): void
    {
        $_SESSION['flash'][$key] = $message;
    }
}

if (!function_exists('flash_get')) {
    function flash_get(string $key): ?string
    {
        $message = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);

        return $message;
    }
}

/* -------------------------------------------------------------------------
 * File uploads
 * ---------------------------------------------------------------------- */

if (!function_exists('allowed_image_types')) {
    /**
     * @return array<string, string> mime => extension
     */
    function allowed_image_types(): array
    {
        return [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];
    }
}

if (!function_exists('store_uploaded_image')) {
    /**
     * Validate and move an uploaded image into $directory.
     * The stored name is random and the extension is derived from the real MIME type,
     * never from the client-supplied filename.
     *
     * @param array{name?:string,type?:string,tmp_name:string,error:int,size:int} $file
     * @return string Stored filename (not a full path).
     * @throws RuntimeException
     */
    function store_uploaded_image(array $file, string $directory, int $maxBytes = 5_242_880): string
    {
        if (!isset($file['tmp_name'], $file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('The file could not be uploaded.');
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('Invalid upload source.');
        }

        if (($file['size'] ?? 0) > $maxBytes) {
            throw new RuntimeException('The file is too large (maximum 5 MB).');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']) ?: '';
        $allowed = allowed_image_types();

        if (!isset($allowed[$mime])) {
            throw new RuntimeException('Only JPG, PNG, GIF and WEBP images are allowed.');
        }

        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Upload directory is not writable.');
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
        $target = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            throw new RuntimeException('The file could not be saved.');
        }

        @chmod($target, 0644);

        return $filename;
    }
}
