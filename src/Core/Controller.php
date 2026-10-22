<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Base Abstract OOP Controller
 */
abstract class Controller
{
    /**
     * Render a PHP view file with injected variables
     *
     * @param string $view Relative view name (e.g. 'auth/login' or 'admin/dashboard')
     * @param array<string, mixed> $data Data passed to view
     */
    protected function render(string $view, array $data = []): void
    {
        $viewFile = dirname(__DIR__, 2) . '/pages/' . ltrim($view, '/') . '.php';

        if (!file_exists($viewFile)) {
            throw new RuntimeException("View file not found: {$viewFile}");
        }

        // Extract variables for view template
        extract($data, EXTR_SKIP);

        // Flash messages available to every view
        $flashes = Session::getFlashes();
        $currentUser = Auth::user();

        require $viewFile;
        exit;
    }

    /**
     * Send JSON response
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        Response::json($data, $statusCode);
    }

    /**
     * Redirect to another URL
     */
    protected function redirect(string $url): void
    {
        Response::redirect($url);
    }

    /**
     * Redirect back or to a fallback URL (safely validated against open redirects)
     */
    protected function redirectBack(string $fallback = 'index.php'): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if ($referer !== '') {
            $parsed = parse_url($referer);
            $host = $parsed['host'] ?? '';
            $currentHost = explode(':', $_SERVER['HTTP_HOST'] ?? '')[0];
            if ($host === '' || $host === $currentHost) {
                Response::redirect($referer);
                return;
            }
        }
        Response::redirect($fallback);
    }

    /**
     * Validate CSRF token for state-changing POST requests
     */
    protected function validateCsrf(string $fallback = 'index.php'): void
    {
        $token = $_POST['csrf_token'] ?? null;
        if (!Session::verifyCsrf($token)) {
            Session::setFlash('danger', 'คำขอไม่ถูกต้องหรือหมดอายุ (CSRF token ไม่ถูกต้อง) กรุณาลองใหม่อีกครั้ง');
            $this->redirectBack($fallback);
        }
    }
}
