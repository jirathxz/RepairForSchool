<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Asset
{
    public int $id;
    public string $asset_code;
    public string $name;
    public int $equipment_type_id;
    public ?string $brand;
    public ?string $model;
    public ?string $serial_number;
    public string $building;
    public string $room;
    public string $status;
    public ?string $purchase_date;
    public ?string $warranty_expiry;
    public float $price;
    public string $qr_token;
    public string $created_at;

    public static function all(array $filters = []): array
    {
        $pdo = Database::getConnection();
        $sql = "
            SELECT a.*, e.name AS equipment_type_name, e.icon AS equipment_icon,
                   (SELECT COUNT(*) FROM repair_tickets t WHERE t.asset_id = a.id) AS repair_count
            FROM assets a
            JOIN equipment_types e ON a.equipment_type_id = e.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (a.asset_code LIKE ? OR a.name LIKE ? OR a.building LIKE ? OR a.room LIKE ? OR a.serial_number LIKE ?)";
            $term = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$term, $term, $term, $term, $term]);
        }

        if (!empty($filters['status'])) {
            $sql .= " AND a.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['equipment_type_id'])) {
            $sql .= " AND a.equipment_type_id = ?";
            $params[] = (int)$filters['equipment_type_id'];
        }

        if (!empty($filters['building'])) {
            $sql .= " AND a.building = ?";
            $params[] = $filters['building'];
        }

        $sql .= " ORDER BY a.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT a.*, e.name AS equipment_type_name, e.icon AS equipment_icon
            FROM assets a
            JOIN equipment_types e ON a.equipment_type_id = e.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function findByQrToken(string $token): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT a.*, e.name AS equipment_type_name, e.icon AS equipment_icon
            FROM assets a
            JOIN equipment_types e ON a.equipment_type_id = e.id
            WHERE a.qr_token = ?
        ");
        $stmt->execute([$token]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $qrToken = 'QR-' . strtoupper(bin2hex(random_bytes(6)));

        $stmt = $pdo->prepare("
            INSERT INTO assets (
                asset_code, name, equipment_type_id, brand, model,
                serial_number, building, room, status, purchase_date,
                warranty_expiry, price, qr_token
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['asset_code'],
            $data['name'],
            (int)$data['equipment_type_id'],
            $data['brand'] ?? null,
            $data['model'] ?? null,
            $data['serial_number'] ?? null,
            $data['building'],
            $data['room'],
            $data['status'] ?? 'active',
            !empty($data['purchase_date']) ? $data['purchase_date'] : null,
            !empty($data['warranty_expiry']) ? $data['warranty_expiry'] : null,
            (float)($data['price'] ?? 0.00),
            $qrToken
        ]);

        return (int)$pdo->lastInsertId();
    }

    public static function updateStatus(int $id, string $status): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE assets SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public static function getRepairHistory(int $assetId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT t.*, u.full_name AS member_name, tech.full_name AS technician_name
            FROM repair_tickets t
            LEFT JOIN users u ON t.member_id = u.id
            LEFT JOIN users tech ON t.technician_id = tech.id
            WHERE t.asset_id = ?
            ORDER BY t.id DESC
        ");
        $stmt->execute([$assetId]);
        return $stmt->fetchAll();
    }
}
