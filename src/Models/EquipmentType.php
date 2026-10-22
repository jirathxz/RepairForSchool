<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

/**
 * EquipmentType Model (หมวดหมู่อุปกรณ์และงานซ่อม)
 */
class EquipmentType extends Model
{
    protected static string $table = 'equipment_types';

    /**
     * Get all types ordered by id
     *
     * @return self[]
     */
    public static function allSorted(): array
    {
        $sql = "SELECT * FROM `equipment_types` ORDER BY `id` ASC";
        $rows = Database::fetchAll($sql);

        return array_map(fn($r) => new self($r), $rows);
    }

    /**
     * Get all equipment types with usage counts (tickets, assets, parts)
     */
    public static function allWithStats(): array
    {
        $sql = "
            SELECT e.*,
                   (SELECT COUNT(*) FROM repair_tickets t WHERE t.equipment_type_id = e.id) AS ticket_count,
                   (SELECT COUNT(*) FROM assets a WHERE a.equipment_type_id = e.id) AS asset_count,
                   (SELECT COUNT(*) FROM spare_parts s WHERE s.equipment_type_id = e.id) AS part_count
            FROM equipment_types e
            ORDER BY e.id ASC
        ";
        return Database::fetchAll($sql);
    }

    /**
     * Check if an equipment type can be safely deleted
     */
    public static function canDelete(int $id): bool
    {
        $pdo = Database::getConnection();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM repair_tickets WHERE equipment_type_id = ?");
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) return false;

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM assets WHERE equipment_type_id = ?");
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) return false;

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pm_schedules WHERE equipment_type_id = ?");
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) return false;

        return true;
    }
}
