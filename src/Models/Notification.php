<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;
use PDO;

class Notification extends Model
{
    protected static string $table = 'notifications';

    /**
     * Send notification to a specific user
     */
    public static function send(
        int $userId,
        string $title,
        string $message,
        ?int $ticketId = null,
        string $type = 'info',
        ?string $link = null
    ): int {
        if ($link === null && $ticketId !== null) {
            $link = "index.php?page=tickets-show&id={$ticketId}";
        }

        $sql = "INSERT INTO notifications (user_id, ticket_id, title, message, type, link, is_read, created_at)
                VALUES (:user_id, :ticket_id, :title, :message, :type, :link, 0, NOW())";
        
        Database::query($sql, [
            'user_id' => $userId,
            'ticket_id' => $ticketId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'link' => $link,
        ]);

        return (int)Database::lastInsertId();
    }

    /**
     * Send notification to all active users with a specific role (e.g. 'admin')
     */
    public static function sendToRole(
        string $role,
        string $title,
        string $message,
        ?int $ticketId = null,
        string $type = 'info',
        ?string $link = null
    ): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE role = ? AND is_active = 1");
        $stmt->execute([$role]);
        $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $count = 0;
        foreach ($userIds as $userId) {
            self::send((int)$userId, $title, $message, $ticketId, $type, $link);
            $count++;
        }
        return $count;
    }

    /**
     * Count unread notifications for a user
     */
    public static function countUnreadByUser(int $userId): int
    {
        $sql = "SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0";
        $row = Database::fetchOne($sql, ['user_id' => $userId]);
        return (int)($row['COUNT(*)'] ?? 0);
    }

    /**
     * Get recent notifications for a user
     */
    public static function getRecentByUser(int $userId, int $limit = 8): array
    {
        $limit = max(1, min(50, $limit));
        $sql = "SELECT * FROM notifications 
                WHERE user_id = :user_id 
                ORDER BY is_read ASC, id DESC 
                LIMIT {$limit}";
        return Database::fetchAll($sql, ['user_id' => $userId]);
    }

    /**
     * Mark a single notification as read
     */
    public static function markAsRead(int $id, int $userId): bool
    {
        $sql = "UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id";
        Database::query($sql, ['id' => $id, 'user_id' => $userId]);
        return true;
    }

    /**
     * Mark all notifications as read for a user
     */
    public static function markAllAsRead(int $userId): bool
    {
        $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = :user_id AND is_read = 0";
        Database::query($sql, ['user_id' => $userId]);
        return true;
    }
}
