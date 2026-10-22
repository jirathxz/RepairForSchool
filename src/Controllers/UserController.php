<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Models\User;

/**
 * User Controller (Admin Only)
 * Manages school accounts: Admin, Teachers, and Technicians.
 */
class UserController extends Controller
{
    /**
     * List all users
     */
    public function index(): void
    {
        Auth::requireRole('admin');

        $users = User::all('`role` ASC, `full_name` ASC');

        $this->render('admin/users', [
            'pageTitle' => 'จัดการบัญชีผู้ใช้งาน - RepairForSchool',
            'users' => $users,
        ]);
    }

    /**
     * Create new user
     */
    public function store(): void
    {
        Auth::requireRole('admin');
        $this->validateCsrf('index.php?page=users');

        $username = trim((string)($_POST['username'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $fullName = trim((string)($_POST['full_name'] ?? ''));
        $role = in_array($_POST['role'] ?? '', ['admin', 'teacher', 'technician'], true) ? $_POST['role'] : 'teacher';
        $department = trim((string)($_POST['department'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? ''));
        $password = (string)($_POST['password'] ?? 'password123');

        if ($username === '' || $email === '' || $fullName === '' || $password === '') {
            Session::setFlash('danger', 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน');
            $this->redirect('index.php?page=users');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::setFlash('danger', 'รูปแบบอีเมลไม่ถูกต้อง กรุณาระบุอีเมลที่ถูกต้อง');
            $this->redirect('index.php?page=users');
        }

        // Check duplicate username/email
        $existsUser = User::findWhere('username', $username);
        if ($existsUser) {
            Session::setFlash('danger', "ชื่อผู้ใช้งาน '{$username}' มีอยู่ในระบบแล้ว");
            $this->redirect('index.php?page=users');
        }

        $existsEmail = User::findWhere('email', $email);
        if ($existsEmail) {
            Session::setFlash('danger', "อีเมล '{$email}' มีอยู่ในระบบแล้ว");
            $this->redirect('index.php?page=users');
        }

        User::registerUser([
            'username' => $username,
            'email' => $email,
            'full_name' => $fullName,
            'role' => $role,
            'department' => $department !== '' ? $department : null,
            'phone' => $phone !== '' ? $phone : null,
            'password' => $password,
            'is_active' => 1,
        ]);

        Session::setFlash('success', "เพิ่มผู้ใช้งาน '{$fullName}' เรียบร้อยแล้ว (รหัสผ่านเริ่มต้น: {$password})");
        $this->redirect('index.php?page=users');
    }

    /**
     * Toggle active status of a user
     */
    public function toggleStatus(): void
    {
        Auth::requireRole('admin');
        $this->validateCsrf('index.php?page=users');

        $userId = (int)($_POST['user_id'] ?? 0);

        if ($userId === (int)Auth::id()) {
            Session::setFlash('danger', 'ไม่สามารถระงับการใช้งานบัญชีของตนเองได้');
            $this->redirect('index.php?page=users');
        }

        $targetUser = User::find($userId);
        if ($targetUser) {
            $newStatus = (int)$targetUser->is_active === 1 ? 0 : 1;
            User::update($userId, ['is_active' => $newStatus]);
            $statusText = $newStatus === 1 ? 'เปิดใช้งาน' : 'ระงับการใช้งาน';
            Session::setFlash('info', "เปลี่ยนสถานะผู้ใช้งาน '{$targetUser->full_name}' เป็น {$statusText} เรียบร้อยแล้ว");
        }

        $this->redirect('index.php?page=users');
    }
}
