<?php

declare(strict_types=1);

namespace App\Services;

/**
 * File Upload Service
 * Handles secure image uploads for before and after repair evidence.
 */
class FileUploadService
{
    private const MAX_SIZE_BYTES = 5242880; // 5 MB
    private const ALLOWED_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    /**
     * Upload an image file for a ticket
     *
     * @param array $file $_FILES['image'] structure
     * @param string $type 'before' or 'after'
     * @param string $ticketNo Ticket number for file naming
     * @return array{success: bool, fileName?: string, originalName?: string, fileSize?: int, mimeType?: string, error?: string}
     */
    public static function uploadImage(array $file, string $type, string $ticketNo): array
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['success' => false, 'error' => 'พารามิเตอร์การอัพโหลดไม่ถูกต้อง'];
        }

        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['success' => false, 'error' => 'ไม่มีไฟล์ถูกอัพโหลด'];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'เกิดข้อผิดพลาดในการอัพโหลดไฟล์ (รหัส: ' . $file['error'] . ')'];
        }

        if ($file['size'] > self::MAX_SIZE_BYTES) {
            return ['success' => false, 'error' => 'ขนาดไฟล์เกินกำหนด (สูงสุด 5MB)'];
        }

        // Validate MIME type using finfo
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!array_key_exists($mimeType, self::ALLOWED_MIMES)) {
            return ['success' => false, 'error' => 'ชนิดไฟล์ไม่ถูกต้อง อนุญาตเฉพาะ JPG, PNG, WEBP หรือ GIF'];
        }

        $extension = self::ALLOWED_MIMES[$mimeType];

        // Target upload directory
        $subDir = $type === 'after' ? 'after' : 'before';
        $uploadBaseDir = dirname(__DIR__, 2) . '/uploads/' . $subDir;

        if (!is_dir($uploadBaseDir)) {
            if (!mkdir($uploadBaseDir, 0755, true) && !is_dir($uploadBaseDir)) {
                return ['success' => false, 'error' => 'ไม่สามารถสร้างโฟลเดอร์สำหรับจัดเก็บไฟล์ได้'];
            }
        }

        // Clean ticket prefix for safe filenames
        $safeTicket = preg_replace('/[^a-zA-Z0-9_-]/', '', $ticketNo);
        $randomSuffix = bin2hex(random_bytes(6));
        $newFileName = sprintf('%s_%s_%s.%s', $subDir, $safeTicket, $randomSuffix, $extension);
        $targetPath = $uploadBaseDir . '/' . $newFileName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['success' => false, 'error' => 'ไม่สามารถบันทึกไฟล์ลงเซิร์ฟเวอร์ได้'];
        }

        return [
            'success' => true,
            'fileName' => $newFileName,
            'originalName' => $file['name'],
            'fileSize' => (int)$file['size'],
            'mimeType' => $mimeType,
        ];
    }
}
