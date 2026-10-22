<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

/**
 * TicketComment Model (ความคิดเห็นและข้อความสอบถามในใบแจ้งซ่อม)
 */
class TicketComment extends Model
{
    protected static string $table = 'ticket_comments';

    /**
     * Add comment to ticket
     */
    public static function addComment(int $ticketId, int $userId, string $comment): self
    {
        return self::create([
            'ticket_id' => $ticketId,
            'user_id' => $userId,
            'comment' => trim($comment),
        ]);
    }

    /**
     * Get all comments for a ticket ordered chronologically with user profile
     *
     * @return array
     */
    public static function getByTicket(int $ticketId): array
    {
        $sql = "
            SELECT 
                c.*,
                u.full_name AS author_name,
                u.role AS author_role,
                u.department AS author_department
            FROM `ticket_comments` c
            JOIN `users` u ON c.user_id = u.id
            WHERE c.ticket_id = :ticket_id
            ORDER BY c.created_at ASC
        ";

        return Database::fetchAll($sql, ['ticket_id' => $ticketId]);
    }
}
