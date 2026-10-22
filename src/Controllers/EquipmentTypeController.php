<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\EquipmentType;

class EquipmentTypeController extends Controller
{
    /**
     * Display equipment types list with usage statistics
     */
    public function index(): void
    {
        Auth::requireRole('admin');

        $types = EquipmentType::allWithStats();

        $this->render('admin/equipment_types', [
            'pageTitle' => 'จัดการหมวดหมู่อุปกรณ์และงานซ่อม - RepairForSchool',
            'types' => $types,
        ]);
    }

    /**
     * Store or update equipment type
     */
    public function store(): void
    {
        Auth::requireRole('admin');
        $this->validateCsrf('index.php?page=equipment-types');

        $id = (int)($_POST['id'] ?? 0);
        $name = trim((string)($_POST['name'] ?? ''));
        $icon = trim((string)($_POST['icon'] ?? 'fa-solid fa-wrench'));
        $description = trim((string)($_POST['description'] ?? ''));

        if ($name === '') {
            Session::setFlash('danger', 'กรุณาระบุชื่อหมวดหมู่อุปกรณ์');
            $this->redirect('index.php?page=equipment-types');
        }

        if (empty($icon)) {
            $icon = 'fa-solid fa-wrench';
        }

        if ($id > 0) {
            // Update
            EquipmentType::update($id, [
                'name' => $name,
                'icon' => $icon,
                'description' => $description ?: null,
            ]);
            Session::setFlash('success', "อัพเดทหมวดหมู่ \"{$name}\" เรียบร้อยแล้ว");
        } else {
            // Create
            EquipmentType::create([
                'name' => $name,
                'icon' => $icon,
                'description' => $description ?: null,
            ]);
            Session::setFlash('success', "เพิ่มหมวดหมู่อุปกรณ์ \"{$name}\" สำเร็จ");
        }

        $this->redirect('index.php?page=equipment-types');
    }

    /**
     * Delete equipment type
     */
    public function delete(): void
    {
        Auth::requireRole('admin');
        $this->validateCsrf('index.php?page=equipment-types');

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            Session::setFlash('danger', 'ไม่พบหมวดหมู่ที่ต้องการลบ');
            $this->redirect('index.php?page=equipment-types');
        }

        $type = EquipmentType::find($id);
        if (!$type) {
            Session::setFlash('danger', 'ไม่พบหมวดหมู่อุปกรณ์');
            $this->redirect('index.php?page=equipment-types');
        }

        if (!EquipmentType::canDelete($id)) {
            Session::setFlash('warning', "ไม่สามารถลบหมวดหมู่ \"{$type->name}\" ได้ เนื่องจากมีประวัติใบแจ้งซ่อม หรือทะเบียนครุภัณฑ์ที่เชื่อมโยงอยู่");
            $this->redirect('index.php?page=equipment-types');
        }

        EquipmentType::delete($id);
        Session::setFlash('success', "ลบหมวดหมู่อุปกรณ์ \"{$type->name}\" เรียบร้อยแล้ว");
        $this->redirect('index.php?page=equipment-types');
    }
}
