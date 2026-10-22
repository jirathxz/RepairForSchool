<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class TicketPart
{
    public static function getByTicket(int $ticketId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT u.*, 
                   u.quantity AS quantity_used,
                   u.unit_cost AS unit_price_at_time,
                   u.total_cost AS subtotal,
                   p.name AS part_name, p.part_code, p.unit, p.category
            FROM ticket_parts_used u
            JOIN spare_parts p ON u.spare_part_id = p.id
            WHERE u.ticket_id = ?
            ORDER BY u.id ASC
        ");
        $stmt->execute([$ticketId]);
        return $stmt->fetchAll();
    }

    public static function addPart(int $ticketId, int $partId, int $quantity): bool
    {
        $pdo = Database::getConnection();
        $part = SparePart::find($partId);
        if (!$part || $quantity <= 0) {
            return false;
        }

        $unitCost = (float)$part['unit_cost'];
        $totalCost = $unitCost * $quantity;

        $pdo->beginTransaction();
        try {
            // 1. Insert record in ticket_parts_used
            $stmt = $pdo->prepare("
                INSERT INTO ticket_parts_used (ticket_id, spare_part_id, quantity, unit_cost, total_cost)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$ticketId, $partId, $quantity, $unitCost, $totalCost]);

            // 2. Deduct inventory from spare_parts
            $stmtDeduct = $pdo->prepare("
                UPDATE spare_parts 
                SET stock_quantity = GREATEST(0, stock_quantity - ?) 
                WHERE id = ?
            ");
            $stmtDeduct->execute([$quantity, $partId]);

            // 3. Recalculate and update repair_tickets.repair_cost
            self::recalculateTicketCost($ticketId, $pdo);

            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function removePart(int $ticketPartId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM ticket_parts_used WHERE id = ?");
        $stmt->execute([$ticketPartId]);
        $row = $stmt->fetch();
        if (!$row) {
            return false;
        }

        $pdo->beginTransaction();
        try {
            // Restore stock
            $stmtRestore = $pdo->prepare("UPDATE spare_parts SET stock_quantity = stock_quantity + ? WHERE id = ?");
            $stmtRestore->execute([$row['quantity'], $row['spare_part_id']]);

            // Delete usage row
            $stmtDel = $pdo->prepare("DELETE FROM ticket_parts_used WHERE id = ?");
            $stmtDel->execute([$ticketPartId]);

            // Recalculate ticket cost
            self::recalculateTicketCost((int)$row['ticket_id'], $pdo);

            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private static function recalculateTicketCost(int $ticketId, PDO $pdo): void
    {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_cost), 0) FROM ticket_parts_used WHERE ticket_id = ?");
        $stmt->execute([$ticketId]);
        $totalPartsCost = (float)$stmt->fetchColumn();

        // Get list of part names to summarize in cost_description
        $stmtNames = $pdo->prepare("
            SELECT CONCAT(p.name, ' (', u.quantity, ' ', p.unit, ')') AS item_str
            FROM ticket_parts_used u
            JOIN spare_parts p ON u.spare_part_id = p.id
            WHERE u.ticket_id = ?
        ");
        $stmtNames->execute([$ticketId]);
        $items = $stmtNames->fetchAll(PDO::FETCH_COLUMN);
        $costDesc = !empty($items) ? implode(', ', $items) : 'ค่าอะไหล่และอุปกรณ์';

        $stmtUpdate = $pdo->prepare("
            UPDATE repair_tickets 
            SET repair_cost = ?, cost_description = ? 
            WHERE id = ?
        ");
        $stmtUpdate->execute([$totalPartsCost, $costDesc, $ticketId]);
    }
}
