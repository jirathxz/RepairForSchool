<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\PMSchedule;
use App\Models\Asset;
use App\Models\EquipmentType;
use App\Models\User;

class PMScheduleController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();

        $schedules = PMSchedule::all();
        $equipmentTypes = EquipmentType::all();
        $assets = Asset::all();
        $technicians = User::getTechnicians();

        $buildings = [
            'อาคาร 1 เฉลิมพระเกียรติ',
            'อาคาร 2 วิทยาศาสตร์',
            'อาคาร 3 มัธยมศึกษา',
            'อาคารบริหารและธุรการ',
            'อาคารอเนกประสงค์ และโรงอาหาร',
            'หอประชุมใหญ่'
        ];

        $today = date('Y-m-d');
        $dueCount = 0;
        foreach ($schedules as $s) {
            if ($s['next_due_date'] <= $today) {
                $dueCount++;
            }
        }

        $this->render('pm_schedules/index', [
            'schedules' => $schedules,
            'equipmentTypes' => $equipmentTypes,
            'assets' => $assets,
            'technicians' => $technicians,
            'buildings' => $buildings,
            'dueCount' => $dueCount,
            'pageTitle' => 'แผนบำรุงรักษาเชิงป้องกันตามรอบ — RepairForSchool'
        ]);
    }

    public function store(): void
    {
        Auth::requireAdmin();
        $this->validateCsrf();

        $title = trim((string)($_POST['title'] ?? ''));
        $equipmentTypeId = (int)($_POST['equipment_type_id'] ?? 0);
        $building = trim((string)($_POST['building'] ?? ''));
        $frequencyDays = (int)($_POST['frequency_days'] ?? 90);
        $nextDueDate = trim((string)($_POST['next_due_date'] ?? ''));

        if (empty($title) || $equipmentTypeId <= 0 || empty($building) || empty($nextDueDate)) {
            Session::flash('danger', 'กรุณาระบุข้อมูลแผนบำรุงรักษาที่จำเป็นให้ครบถ้วน');
            $this->redirect('index.php?page=pm-schedules');
        }

        PMSchedule::create([
            'title' => $title,
            'description' => trim((string)($_POST['description'] ?? '')) ?: null,
            'equipment_type_id' => $equipmentTypeId,
            'asset_id' => !empty($_POST['asset_id']) ? (int)$_POST['asset_id'] : null,
            'building' => $building,
            'room' => trim((string)($_POST['room'] ?? '')) ?: null,
            'frequency_days' => $frequencyDays,
            'next_due_date' => $nextDueDate,
            'assigned_technician_id' => !empty($_POST['assigned_technician_id']) ? (int)$_POST['assigned_technician_id'] : null,
        ]);

        Session::flash('success', "บันทึกแผนงานบำรุงรักษาเชิงป้องกัน '{$title}' เรียบร้อยแล้ว");
        $this->redirect('index.php?page=pm-schedules');
    }

    public function trigger(): void
    {
        Auth::requireAuth();
        $this->validateCsrf();

        $pmId = (int)($_POST['pm_id'] ?? 0);
        if ($pmId <= 0) {
            Session::flash('danger', 'ไม่พบแผนงานที่ต้องการเปิดใบงาน');
            $this->redirect('index.php?page=pm-schedules');
        }

        try {
            $ticketId = PMSchedule::generateTicket($pmId, (int)Auth::id());
            Session::flash('success', "สร้างใบงานซ่อมบำรุงตามรอบสำเร็จ (รหัสงาน #{$ticketId}) พร้อมแจ้งเตือนเจ้าหน้าที่ช่างแล้ว");
            $this->redirect("index.php?page=tickets-show&id={$ticketId}");
        } catch (\Throwable $e) {
            Session::flash('danger', 'เกิดข้อผิดพลาดในการสร้างใบงาน: ' . $e->getMessage());
            $this->redirect('index.php?page=pm-schedules');
        }
    }

    public function triggerDue(): void
    {
        Auth::requireAuth();
        $this->validateCsrf();

        try {
            $ticketIds = PMSchedule::generateDueTickets((int)Auth::id());
            $count = count($ticketIds);
            if ($count === 0) {
                Session::flash('info', 'ไม่มีรายการแผนบำรุงรักษาที่ถึงกำหนดในขณะนี้');
            } else {
                Session::flash('success', "เปิดใบงานบำรุงรักษาเชิงป้องกันสำเร็จ {$count} รายการ เรียบร้อยแล้ว");
            }
        } catch (\Throwable $e) {
            Session::flash('danger', 'เกิดข้อผิดพลาดในการเปิดใบงานรอบบำรุงรักษา: ' . $e->getMessage());
        }
        $this->redirect('index.php?page=pm-schedules');
    }
}
