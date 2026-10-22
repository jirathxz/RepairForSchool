<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\SparePart;
use App\Models\EquipmentType;

class SparePartController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();

        $filters = [
            'search' => trim((string)($_GET['search'] ?? '')),
            'low_stock' => isset($_GET['low_stock']) && $_GET['low_stock'] === '1',
        ];

        $parts = SparePart::all($filters);
        $equipmentTypes = EquipmentType::all();
        $lowStockCount = SparePart::getLowStockCount();

        $totalInventoryValue = 0.0;
        foreach ($parts as $p) {
            $totalInventoryValue += ((float)$p['unit_cost'] * (int)$p['stock_quantity']);
        }

        $this->render('spare_parts/index', [
            'parts' => $parts,
            'equipmentTypes' => $equipmentTypes,
            'lowStockCount' => $lowStockCount,
            'totalInventoryValue' => $totalInventoryValue,
            'filters' => $filters,
            'pageTitle' => 'คลังอะไหล่และพัสดุซ่อมบำรุง — RepairForSchool'
        ]);
    }

    public function store(): void
    {
        // Only admin and technicians can manage parts inventory
        if (!Auth::isAdmin() && !Auth::isTechnician()) {
            $this->forbidden('คุณไม่มีสิทธิ์จัดการคลังอะไหล่');
        }
        $this->validateCsrf();

        $partCode = trim((string)($_POST['part_code'] ?? ''));
        $name = trim((string)($_POST['name'] ?? ''));
        $unit = trim((string)($_POST['unit'] ?? 'ชิ้น'));
        $unitCost = (float)($_POST['unit_cost'] ?? 0);
        $stockQuantity = (int)($_POST['stock_quantity'] ?? 0);
        $minQuantity = (int)($_POST['min_quantity'] ?? 5);
        $equipmentTypeId = !empty($_POST['equipment_type_id']) ? (int)$_POST['equipment_type_id'] : null;

        if (empty($partCode) || empty($name)) {
            Session::flash('danger', 'กรุณาระบุรหัสอะไหล่และชื่ออะไหล่');
            $this->redirect('index.php?page=spare-parts');
        }

        try {
            SparePart::create([
                'part_code' => $partCode,
                'name' => $name,
                'unit' => $unit,
                'unit_cost' => $unitCost,
                'stock_quantity' => $stockQuantity,
                'min_quantity' => $minQuantity,
                'equipment_type_id' => $equipmentTypeId,
            ]);

            Session::flash('success', "เพิ่มอะไหล่รหัส {$partCode} เข้าสู่คลังเรียบร้อยแล้ว");
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate')) {
                Session::flash('danger', "รหัสอะไหล่ {$partCode} มีอยู่ในระบบแล้ว กรุณาใช้รหัสอื่น");
            } else {
                Session::flash('danger', 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage());
            }
        }

        $this->redirect('index.php?page=spare-parts');
    }

    public function restock(): void
    {
        if (!Auth::isAdmin() && !Auth::isTechnician()) {
            $this->forbidden('คุณไม่มีสิทธิ์จัดการสต็อกอะไหล่');
        }
        $this->validateCsrf();

        $id = (int)($_POST['part_id'] ?? 0);
        $qty = (int)($_POST['add_quantity'] ?? 0);

        if ($id <= 0 || $qty <= 0) {
            Session::flash('danger', 'กรุณาระบุจำนวนอะไหล่ที่ต้องการรับเข้าสต็อกให้ถูกต้อง');
            $this->redirect('index.php?page=spare-parts');
        }

        $part = SparePart::find($id);
        if (!$part) {
            Session::flash('danger', 'ไม่พบข้อมูลอะไหล่ที่เลือก');
            $this->redirect('index.php?page=spare-parts');
        }

        SparePart::adjustStock($id, $qty);
        Session::flash('success', "รับเข้าอะไหล่ {$part['name']} จำนวน +{$qty} {$part['unit']} สำเร็จ");
        $this->redirect('index.php?page=spare-parts');
    }
}
