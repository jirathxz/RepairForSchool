<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class PMSchedule
{
    public static function all(array $filters = []): array
    {
        $pdo = Database::getConnection();
        $sql = "
            SELECT pm.*, e.name AS equipment_type_name, e.icon AS equipment_icon,
                   a.name AS asset_name, a.asset_code,
                   tech.full_name AS technician_name
            FROM pm_schedules pm
            JOIN equipment_types e ON pm.equipment_type_id = e.id
            LEFT JOIN assets a ON pm.asset_id = a.id
            LEFT JOIN users tech ON pm.assigned_technician_id = tech.id
            WHERE 1=1
        ";
        $params = [];

        if (isset($filters['active'])) {
            $sql .= " AND pm.is_active = ?";
            $params[] = (int)$filters['active'];
        }

        $sql .= " ORDER BY pm.next_due_date ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT pm.*, e.name AS equipment_type_name, a.name AS asset_name, a.asset_code
            FROM pm_schedules pm
            JOIN equipment_types e ON pm.equipment_type_id = e.id
            LEFT JOIN assets a ON pm.asset_id = a.id
            WHERE pm.id = ?
        ");
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO pm_schedules (
                title, description, equipment_type_id, asset_id, building, room,
                frequency_days, next_due_date, assigned_technician_id, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([
            $data['title'],
            $data['description'] ?? null,
            (int)$data['equipment_type_id'],
            !empty($data['asset_id']) ? (int)$data['asset_id'] : null,
            $data['building'],
            $data['room'] ?? null,
            (int)($data['frequency_days'] ?? 90),
            $data['next_due_date'],
            !empty($data['assigned_technician_id']) ? (int)$data['assigned_technician_id'] : null
        ]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Generate a repair ticket from a PM schedule
     */
    public static function generateTicket(int $pmId, int $creatorUserId): int
    {
        $pdo = Database::getConnection();
        $pm = self::find($pmId);
        if (!$pm) {
            throw new \RuntimeException("ไม่พบแผนบำรุงรักษา");
        }

        $ticketNo = RepairTicket::generateTicketNo();
        $title = 'งานบำรุงรักษาเชิงป้องกัน ' . $pm['title'];
        $desc = "งานบำรุงรักษาเชิงป้องกันตามรอบ " . $pm['frequency_days'] . " วัน\n" . ($pm['description'] ?? '');

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("
                INSERT INTO repair_tickets (
                    ticket_no, member_id, technician_id, equipment_type_id, asset_id,
                    title, description, building, room, status, priority, due_date
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'in_process', 'normal', ?)
            ");
            $stmt->execute([
                $ticketNo,
                $creatorUserId,
                $pm['assigned_technician_id'],
                $pm['equipment_type_id'],
                $pm['asset_id'],
                $title,
                $desc,
                $pm['building'],
                $pm['room'] ?? 'บริเวณจุดติดตั้ง',
                $pm['next_due_date']
            ]);
            $ticketId = (int)$pdo->lastInsertId();

            // Log status
            $stmtLog = $pdo->prepare("
                INSERT INTO repair_status_logs (ticket_id, changed_by_user_id, previous_status, new_status, notes)
                VALUES (?, ?, NULL, 'in_process', 'สร้างใบงานซ่อมบำรุงเชิงป้องกันตามรอบ (PM)')
            ");
            $stmtLog->execute([$ticketId, $creatorUserId]);

            // Update PM schedule next due date
            $frequencyDays = (int)$pm['frequency_days'];
            $nextDate = date('Y-m-d', strtotime("+{$frequencyDays} days"));
            $stmtUpdate = $pdo->prepare("
                UPDATE pm_schedules 
                SET last_run_at = CURDATE(), next_due_date = ? 
                WHERE id = ?
            ");
            $stmtUpdate->execute([$nextDate, $pmId]);

            $pdo->commit();

            if (!empty($pm['assigned_technician_id'])) {
                Notification::send((int)$pm['assigned_technician_id'], 'มีใบงานบำรุงรักษาเชิงป้องกัน (PM)', "ใบงาน [{$ticketNo}]: {$title}", $ticketId, 'info');
            }
            Notification::sendToRole('admin', 'เปิดใบงานบำรุงรักษาเชิงป้องกัน (PM)', "ใบงาน [{$ticketNo}]: {$title}", $ticketId, 'info');

            return $ticketId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Generate repair tickets for all active PM schedules where next_due_date <= CURDATE()
     * @return int[] List of created ticket IDs
     */
    public static function generateDueTickets(int $creatorUserId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT id FROM pm_schedules 
            WHERE is_active = 1 AND next_due_date <= CURDATE()
            ORDER BY next_due_date ASC
        ");
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $createdIds = [];
        foreach ($rows as $row) {
            $createdIds[] = self::generateTicket((int)$row['id'], $creatorUserId);
        }
        return $createdIds;
    }
}
