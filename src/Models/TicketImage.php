<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

/**
 * TicketImage Model (ภาพถ่ายก่อน-หลังซ่อม)
 */
class TicketImage extends Model
{
    protected static string $table = 'ticket_images';

    /**
     * Store new image record
     */
    public static function addImage(
        int $ticketId,
        int $userId,
        string $type,
        string $fileName,
        ?string $originalName = null,
        ?int $fileSize = null,
        ?string $mimeType = null,
        ?string $caption = null
    ): self {
        return self::create([
            'ticket_id' => $ticketId,
            'uploaded_by_user_id' => $userId,
            'type' => $type,
            'file_name' => $fileName,
            'original_name' => $originalName,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'caption' => $caption,
        ]);
    }

    /**
     * Get images for a ticket with uploader information
     *
     * @return array
     */
    public static function getByTicket(int $ticketId, ?string $type = null): array
    {
        $params = ['ticket_id' => $ticketId];
        $typeCondition = '';

        if ($type !== null) {
            $typeCondition = 'AND img.type = :type';
            $params['type'] = $type;
        }

        $sql = "
            SELECT 
                img.*,
                u.full_name AS uploader_name,
                u.role AS uploader_role
            FROM `ticket_images` img
            JOIN `users` u ON img.uploaded_by_user_id = u.id
            WHERE img.ticket_id = :ticket_id {$typeCondition}
            ORDER BY img.id ASC
        ";

        return Database::fetchAll($sql, $params);
    }
}
