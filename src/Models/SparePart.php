<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class SparePart
{
    public int $id;
    public string $part_code;
    public string $name;
    public string $unit;
    public float $unit_cost;
    public int $stock_quantity;
    public int $min_quantity;
    public ?int $equipment_type_id;
    public string $created_at;

    public static function all(array $filters = []): array
    {
        $pdo = Database::getConnection();
        $sql = "
            SELECT p.*,
                   p.stock_quantity AS quantity_in_stock,
                   p.unit_cost AS unit_price,
                   COALESCE(e.name, p.category, 'ทั่วไป') AS equipment_type_name
            FROM spare_parts p
            LEFT JOIN equipment_types e ON p.equipment_type_id = e.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (p.part_code LIKE ? OR p.name LIKE ?)";
            $term = '%' . $filters['search'] . '%';
            $params = [$term, $term];
        }

        if (isset($filters['low_stock']) && $filters['low_stock']) {
            $sql .= " AND p.stock_quantity <= p.min_quantity";
        }

        $sql .= " ORDER BY p.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT p.*,
                   p.stock_quantity AS quantity_in_stock,
                   p.unit_cost AS unit_price,
                   COALESCE(e.name, p.category, 'ทั่วไป') AS equipment_type_name
            FROM spare_parts p
            LEFT JOIN equipment_types e ON p.equipment_type_id = e.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO spare_parts (part_code, name, unit, unit_cost, stock_quantity, min_quantity, equipment_type_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['part_code'],
            $data['name'],
            $data['unit'] ?? 'ชิ้น',
            (float)($data['unit_cost'] ?? 0),
            (int)($data['stock_quantity'] ?? 0),
            (int)($data['min_quantity'] ?? 5),
            !empty($data['equipment_type_id']) ? (int)$data['equipment_type_id'] : null
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function adjustStock(int $id, int $quantityChange): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE spare_parts SET stock_quantity = stock_quantity + ? WHERE id = ?");
        return $stmt->execute([$quantityChange, $id]);
    }

    public static function getLowStockCount(): int
    {
        $pdo = Database::getConnection();
        return (int)$pdo->query("SELECT COUNT(*) FROM spare_parts WHERE stock_quantity <= min_quantity")->fetchColumn();
    }
}
