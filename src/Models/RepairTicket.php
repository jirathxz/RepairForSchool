<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

/**
 * RepairTicket Model (ระบบจัดการใบแจ้งซ่อมโรงเรียน)
 */
class RepairTicket extends Model
{
    protected static string $table = 'repair_tickets';

    /**
     * Generate sequential ticket number (e.g. REP-202609-0007)
     */
    public static function generateTicketNo(): string
    {
        $prefix = 'REP-' . date('Ym') . '-';
        $sql = "SELECT `ticket_no` FROM `repair_tickets` WHERE `ticket_no` LIKE :prefix ORDER BY `id` DESC LIMIT 1";
        $lastTicket = Database::fetchValue($sql, ['prefix' => "{$prefix}%"]);

        if ($lastTicket && preg_match('/-(\d+)$/', (string)$lastTicket, $matches)) {
            $nextNum = (int)$matches[1] + 1;
        } else {
            $nextNum = 1;
        }

        return $prefix . str_pad((string)$nextNum, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create ticket with retry on ticket_no collision (prevents race condition)
     *
     * @param array<string, mixed> $data
     */
    public static function createWithUniqueTicketNo(array $data): self
    {
        $maxAttempts = 5;
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $data['ticket_no'] = self::generateTicketNo();
            try {
                return self::create($data);
            } catch (\PDOException $e) {
                // If duplicate entry collision, retry with next sequential number
                if ($e->getCode() === '23000' || $e->getCode() === 23000 || str_contains($e->getMessage(), 'Duplicate entry')) {
                    usleep(50000); // 50ms wait
                    continue;
                }
                throw $e;
            }
        }

        throw new \RuntimeException('ไม่สามารถสร้างรหัสใบแจ้งซ่อมได้เนื่องจากมีการชนกันของรหัส กรุณาลองใหม่อีกครั้ง');
    }

    /**
     * Get detailed list of tickets with dynamic filtering
     *
     * @param array<string, mixed> $filters
     * @return array
     */
    public static function getFiltered(array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "t.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['priority'])) {
            $where[] = "t.priority = :priority";
            $params['priority'] = $filters['priority'];
        }

        if (!empty($filters['technician_id'])) {
            $where[] = "t.technician_id = :technician_id";
            $params['technician_id'] = (int)$filters['technician_id'];
        }

        if (!empty($filters['equipment_type_id'])) {
            $where[] = "t.equipment_type_id = :equipment_type_id";
            $params['equipment_type_id'] = (int)$filters['equipment_type_id'];
        }

        if (!empty($filters['building'])) {
            $where[] = "t.building = :building";
            $params['building'] = $filters['building'];
        }

        if (!empty($filters['member_id'])) {
            $where[] = "t.member_id = :member_id";
            $params['member_id'] = (int)$filters['member_id'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(t.ticket_no LIKE :search OR t.title LIKE :search OR t.description LIKE :search OR t.building LIKE :search OR t.room LIKE :search OR m.full_name LIKE :search)";
            $params['search'] = '%' . trim((string)$filters['search']) . '%';
        }

        $whereSql = implode(' AND ', $where);

        $limitSql = '';
        if (isset($filters['limit']) && (int)$filters['limit'] > 0) {
            $limit = (int)$filters['limit'];
            $offset = isset($filters['offset']) ? (int)$filters['offset'] : 0;
            $limitSql = "LIMIT {$offset}, {$limit}";
        }

        $sql = "
            SELECT 
                t.*,
                m.full_name AS member_name,
                m.phone AS member_phone,
                m.department AS member_department,
                tech.full_name AS technician_name,
                tech.phone AS technician_phone,
                eq.name AS equipment_name,
                eq.icon AS equipment_icon,
                (SELECT COUNT(*) FROM `ticket_images` WHERE `ticket_id` = t.id) AS image_count,
                (SELECT COUNT(*) FROM `ticket_comments` WHERE `ticket_id` = t.id) AS comment_count
            FROM `repair_tickets` t
            JOIN `users` m ON t.member_id = m.id
            LEFT JOIN `users` tech ON t.technician_id = tech.id
            JOIN `equipment_types` eq ON t.equipment_type_id = eq.id
            WHERE {$whereSql}
            ORDER BY 
                CASE 
                    WHEN t.status = 'pending' THEN 1
                    WHEN t.status = 'in_process' THEN 2
                    WHEN t.status = 'completed' THEN 3
                    ELSE 4
                END ASC,
                CASE 
                    WHEN t.priority = 'urgent' THEN 1
                    WHEN t.priority = 'high' THEN 2
                    WHEN t.priority = 'normal' THEN 3
                    ELSE 4
                END ASC,
                t.id DESC
            {$limitSql}
        ";

        return Database::fetchAll($sql, $params);
    }

    /**
     * Get single ticket with full relationships
     */
    public static function getDetailedById(int $id): ?array
    {
        $sql = "
            SELECT 
                t.*,
                m.full_name AS member_name,
                m.email AS member_email,
                m.phone AS member_phone,
                m.department AS member_department,
                tech.full_name AS technician_name,
                tech.email AS technician_email,
                tech.phone AS technician_phone,
                tech.department AS technician_department,
                eq.name AS equipment_name,
                eq.icon AS equipment_icon,
                a.asset_code,
                a.name AS asset_name,
                a.brand AS asset_brand,
                a.model AS asset_model,
                acceptor.full_name AS acceptor_name
            FROM `repair_tickets` t
            JOIN `users` m ON t.member_id = m.id
            LEFT JOIN `users` tech ON t.technician_id = tech.id
            JOIN `equipment_types` eq ON t.equipment_type_id = eq.id
            LEFT JOIN `assets` a ON t.asset_id = a.id
            LEFT JOIN `users` acceptor ON t.accepted_by_user_id = acceptor.id
            WHERE t.id = :id
            LIMIT 1
        ";

        return Database::fetchOne($sql, ['id' => $id]);
    }

    /**
     * Get tickets submitted by a specific teacher/member
     */
    public static function getByMember(int $memberId): array
    {
        return self::getFiltered(['member_id' => $memberId]);
    }

    /**
     * Get tickets assigned to a specific technician
     */
    public static function getByTechnician(int $technicianId): array
    {
        return self::getFiltered(['technician_id' => $technicianId]);
    }

    /**
     * Get summary KPI status counts
     */
    public static function countByStatus(): array
    {
        $sql = "
            SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN `status` = 'pending' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN `status` = 'in_process' THEN 1 ELSE 0 END) AS in_process,
                SUM(CASE WHEN `status` = 'completed' THEN 1 ELSE 0 END) AS completed
            FROM `repair_tickets`
        ";
        $res = Database::fetchOne($sql);

        return [
            'total' => (int)($res['total'] ?? 0),
            'pending' => (int)($res['pending'] ?? 0),
            'in_process' => (int)($res['in_process'] ?? 0),
            'completed' => (int)($res['completed'] ?? 0),
        ];
    }

    /**
     * Count overdue tickets (past due_date and not completed)
     */
    public static function countOverdue(): int
    {
        $sql = "SELECT COUNT(*) FROM `repair_tickets` 
                WHERE `due_date` IS NOT NULL 
                  AND `due_date` < CURDATE() 
                  AND `status` != 'completed'";
        return (int)Database::fetchValue($sql);
    }

    /**
     * Get total repair cost sum
     */
    public static function getTotalCost(): float
    {
        $sql = "SELECT COALESCE(SUM(`repair_cost`), 0) FROM `repair_tickets` WHERE `status` = 'completed'";
        return (float)Database::fetchValue($sql);
    }

    /**
     * Get list of unique buildings in use for filter dropdowns
     */
    public static function getBuildingsList(): array
    {
        $sql = "SELECT DISTINCT `building` FROM `repair_tickets` WHERE `building` IS NOT NULL AND `building` != '' ORDER BY `building` ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get technician workload distribution
     */
    public static function getTechnicianWorkload(): array
    {
        $sql = "
            SELECT 
                u.id AS technician_id,
                u.full_name,
                u.phone,
                COUNT(t.id) AS total_assigned,
                SUM(CASE WHEN t.status = 'in_process' THEN 1 ELSE 0 END) AS active_jobs,
                SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) AS completed_jobs
            FROM `users` u
            LEFT JOIN `repair_tickets` t ON u.id = t.technician_id
            WHERE u.role = 'technician' AND u.is_active = 1
            GROUP BY u.id, u.full_name, u.phone
            ORDER BY active_jobs DESC, total_assigned DESC
        ";

        return Database::fetchAll($sql);
    }

    /**
     * Assign ticket to technician (Admin action)
     */
    public static function assignTechnician(
        int $ticketId,
        int $technicianId,
        ?string $dueDate,
        ?string $priority,
        int $adminUserId
    ): bool {
        $ticket = self::find($ticketId);
        if (!$ticket) {
            return false;
        }

        $prevStatus = (string)$ticket->status;
        $newStatus = 'in_process'; // assigning technician automatically transitions to in_process

        $updateData = [
            'technician_id' => $technicianId,
            'status' => $newStatus,
            'assigned_at' => date('Y-m-d H:i:s'),
        ];

        if (!empty($dueDate)) {
            $updateData['due_date'] = $dueDate;
        }
        if (!empty($priority)) {
            $updateData['priority'] = $priority;
        }

        $updated = self::update($ticketId, $updateData);

        if ($updated) {
            $tech = User::find($technicianId);
            $techName = $tech ? (string)$tech->full_name : "ID #{$technicianId}";
            $dueNote = !empty($dueDate) ? " (กำหนดเสร็จ: {$dueDate})" : "";

            self::addStatusLog(
                $ticketId,
                $adminUserId,
                $prevStatus,
                $newStatus,
                "ผู้ดูแลระบบมอบหมายงานให้ {$techName}{$dueNote}"
            );
        }

        return $updated;
    }

    /**
     * Technician updates job notes, costs, or completion
     */
    public static function updateTechnicianProgress(
        int $ticketId,
        string $newStatus,
        ?string $note,
        float $repairCost,
        ?string $costDescription,
        int $technicianUserId
    ): bool {
        $ticket = self::find($ticketId);
        if (!$ticket) {
            return false;
        }

        $prevStatus = (string)$ticket->status;

        $updateData = [
            'status' => $newStatus,
            'technician_note' => $note,
            'repair_cost' => $repairCost,
            'cost_description' => $costDescription,
        ];

        if ($newStatus === 'completed' && empty($ticket->completed_at)) {
            $updateData['completed_at'] = date('Y-m-d H:i:s');
        }

        $updated = self::update($ticketId, $updateData);

        if ($updated) {
            $logNote = "ช่างอัพเดทสถานะเป็น " . self::getStatusLabel($newStatus);
            if ($repairCost > 0) {
                $logNote .= " (ค่าใช้จ่าย: " . number_format($repairCost, 2) . " บาท)";
            }
            if (!empty($note)) {
                $logNote .= " - บันทึก: " . mb_substr($note, 0, 50) . (mb_strlen($note) > 50 ? '...' : '');
            }

            self::addStatusLog($ticketId, $technicianUserId, $prevStatus, $newStatus, $logNote);
        }

        return $updated;
    }

    /**
     * Add entry to status audit log
     */
    public static function addStatusLog(
        int $ticketId,
        ?int $userId,
        ?string $prevStatus,
        string $newStatus,
        ?string $note = null
    ): void {
        $sql = "INSERT INTO `repair_status_logs` (`ticket_id`, `changed_by_user_id`, `previous_status`, `new_status`, `notes`) 
                VALUES (:ticket_id, :user_id, :prev_status, :new_status, :note)";
        Database::query($sql, [
            'ticket_id' => $ticketId,
            'user_id' => $userId,
            'prev_status' => $prevStatus,
            'new_status' => $newStatus,
            'note' => $note,
        ]);
    }

    /**
     * Get audit history logs
     */
    public static function getLogs(int $ticketId): array
    {
        $sql = "
            SELECT 
                l.*,
                u.full_name AS changed_by_name,
                u.role AS changed_by_role
            FROM `repair_status_logs` l
            LEFT JOIN `users` u ON l.changed_by_user_id = u.id
            WHERE l.ticket_id = :ticket_id
            ORDER BY l.id ASC
        ";

        return Database::fetchAll($sql, ['ticket_id' => $ticketId]);
    }

    /**
     * UI Status Badge Helper with Flat Modern UI (No Border, No Shadow)
     */
    public static function getStatusBadge(string $status): string
    {
        return match ($status) {
            'pending' => '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-100 text-amber-900"><i class="fa-solid fa-hourglass-half text-[10px]"></i>รอดำเนินการ</span>',
            'in_process' => '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-violet-100 text-violet-800"><i class="fa-solid fa-wrench text-[10px]"></i>กำลังดำเนินการ</span>',
            'completed' => '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-200 text-slate-800"><i class="fa-solid fa-check text-[10px]"></i>เสร็จสิ้น</span>',
            default => '<span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-700">' . htmlspecialchars($status) . '</span>',
        };
    }

    /**
     * Thai Status Label
     */
    public static function getStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'รอดำเนินการ',
            'in_process' => 'กำลังดำเนินการ',
            'completed' => 'เสร็จสิ้น',
            default => $status,
        };
    }

    /**
     * UI Priority Badge Helper with Flat Modern UI (No Border, No Shadow)
     */
    public static function getPriorityBadge(string $priority): string
    {
        return match ($priority) {
            'urgent' => '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-rose-100 text-rose-800"><i class="fa-solid fa-triangle-exclamation text-[10px]"></i>ด่วนที่สุด</span>',
            'high' => '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-100 text-amber-800"><i class="fa-solid fa-arrow-up text-[10px]"></i>ด่วน</span>',
            'normal' => '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-700"><i class="fa-solid fa-minus text-[10px]"></i>ปกติ</span>',
            'low' => '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-500"><i class="fa-solid fa-arrow-down text-[10px]"></i>ไม่เร่งด่วน</span>',
            default => '<span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs bg-slate-100 text-slate-600">' . htmlspecialchars($priority) . '</span>',
        };
    }

    /**
     * Accept ticket and submit satisfaction rating (System B)
     */
    public static function acceptAndRate(int $ticketId, int $rating, ?string $comment, int $userId): bool
    {
        $rating = max(1, min(5, $rating));
        $sql = "UPDATE `repair_tickets` 
                SET `rating` = :rating, 
                    `rating_comment` = :comment, 
                    `accepted_at` = NOW(), 
                    `accepted_by_user_id` = :user_id 
                WHERE `id` = :id";
        
        Database::query($sql, [
            'rating' => $rating,
            'comment' => $comment,
            'user_id' => $userId,
            'id' => $ticketId,
        ]);

        self::addStatusLog($ticketId, $userId, 'completed', 'completed', "ผู้แจ้งตรวจรับงานเรียบร้อย ให้คะแนน {$rating} ดาวเต็ม 5");
        return true;
    }

    /**
     * Request rework if work not satisfactory (System B)
     */
    public static function requestRework(int $ticketId, string $reason, int $userId): bool
    {
        $sql = "UPDATE `repair_tickets` 
                SET `status` = 'in_process', 
                    `rework_count` = `rework_count` + 1 
                WHERE `id` = :id";
        
        Database::query($sql, ['id' => $ticketId]);

        self::addStatusLog($ticketId, $userId, 'completed', 'in_process', "ผู้แจ้งส่งกลับแก้ไขงานซ้ำ: {$reason}");
        return true;
    }

    /**
     * Alias for addStatusLog for backwards compatibility
     */
    public static function logStatus(int $ticketId, ?int $userId, ?string $prevStatus, string $newStatus, ?string $note = null): void
    {
        self::addStatusLog($ticketId, $userId, $prevStatus, $newStatus, $note);
    }
}
