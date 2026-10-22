<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\Asset;
use App\Models\EquipmentType;

class AssetController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();

        $filters = [
            'search' => trim((string)($_GET['search'] ?? '')),
            'status' => trim((string)($_GET['status'] ?? '')),
            'equipment_type_id' => !empty($_GET['equipment_type_id']) ? (int)$_GET['equipment_type_id'] : null,
            'building' => trim((string)($_GET['building'] ?? '')),
        ];

        $assets = Asset::all($filters);
        $equipmentTypes = EquipmentType::all();

        $buildings = [
            'อาคาร 1 เฉลิมพระเกียรติ',
            'อาคาร 2 วิทยาศาสตร์',
            'อาคาร 3 มัธยมศึกษา',
            'อาคารบริหารและธุรการ',
            'อาคารอเนกประสงค์ และโรงอาหาร',
            'หอประชุมใหญ่'
        ];

        $this->render('assets/index', [
            'assets' => $assets,
            'equipmentTypes' => $equipmentTypes,
            'buildings' => $buildings,
            'filters' => $filters,
            'pageTitle' => 'ทะเบียนครุภัณฑ์และอุปกรณ์ — RepairForSchool'
        ]);
    }

    public function store(): void
    {
        Auth::requireAdmin();
        $this->validateCsrf();

        $assetCode = trim((string)($_POST['asset_code'] ?? ''));
        $name = trim((string)($_POST['name'] ?? ''));
        $equipmentTypeId = (int)($_POST['equipment_type_id'] ?? 0);
        $building = trim((string)($_POST['building'] ?? ''));
        $room = trim((string)($_POST['room'] ?? ''));

        if (empty($assetCode) || empty($name) || $equipmentTypeId <= 0 || empty($building) || empty($room)) {
            Session::flash('danger', 'กรุณากรอกข้อมูลครุภัณฑ์ที่จำเป็นให้ครบถ้วน');
            $this->redirect('index.php?page=assets');
        }

        try {
            Asset::create([
                'asset_code' => $assetCode,
                'name' => $name,
                'equipment_type_id' => $equipmentTypeId,
                'brand' => trim((string)($_POST['brand'] ?? '')) ?: null,
                'model' => trim((string)($_POST['model'] ?? '')) ?: null,
                'serial_number' => trim((string)($_POST['serial_number'] ?? '')) ?: null,
                'building' => $building,
                'room' => $room,
                'status' => $_POST['status'] ?? 'active',
                'purchase_date' => $_POST['purchase_date'] ?? null,
                'warranty_expiry' => $_POST['warranty_expiry'] ?? null,
                'price' => (float)($_POST['price'] ?? 0),
            ]);

            Session::flash('success', "เพิ่มครุภัณฑ์รหัส {$assetCode} สำเร็จ พร้อมสร้างรหัส QR Code เรียบร้อยแล้ว");
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate')) {
                Session::flash('danger', "รหัสครุภัณฑ์ {$assetCode} มีอยู่ในระบบแล้ว กรุณาใช้รหัสอื่น");
            } else {
                Session::flash('danger', 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage());
            }
        }

        $this->redirect('index.php?page=assets');
    }

    public function qrPrint(): void
    {
        Auth::requireAuth();
        $id = (int)($_GET['id'] ?? 0);
        $asset = Asset::find($id);

        if (!$asset) {
            Session::flash('danger', 'ไม่พบข้อมูลครุภัณฑ์ที่ต้องการพิมพ์');
            $this->redirect('index.php?page=assets');
        }

        // Direct printable sticker template
        require __DIR__ . '/../../pages/assets/qr_print.php';
        exit;
    }
}
