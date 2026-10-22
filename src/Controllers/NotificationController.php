<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Notification;

class NotificationController extends Controller
{
    /**
     * Mark single notification as read and redirect to destination link
     */
    public function read(): void
    {
        Auth::requireLogin();
        $userId = (int)Auth::id();
        $id = (int)($_GET['id'] ?? 0);

        if ($id > 0) {
            Notification::markAsRead($id, $userId);
        }

        $link = $_GET['redirect'] ?? 'index.php';
        // Prevent open redirect
        if (!str_starts_with($link, 'index.php')) {
            $link = 'index.php';
        }

        $this->redirect($link);
    }

    /**
     * Mark all notifications as read for current user
     */
    public function readAll(): void
    {
        Auth::requireLogin();
        $userId = (int)Auth::id();

        Notification::markAllAsRead($userId);

        $ref = $_SERVER['HTTP_REFERER'] ?? 'index.php';
        $this->redirect($ref);
    }
}
