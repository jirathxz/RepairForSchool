<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\User;

/**
 * Authentication Controller
 */
class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect(Auth::defaultRedirectUrl());
        }

        $this->render('auth/login', [
            'pageTitle' => 'เข้าสู่ระบบ - RepairForSchool',
        ]);
    }

    /**
     * Process login authentication
     */
    public function login(): void
    {
        $this->validateCsrf('index.php?page=login');

        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            Session::setFlash('danger', 'กรุณากรอกชื่อผู้ใช้งานและรหัสผ่านให้ครบถ้วน');
            $this->redirect('index.php?page=login');
        }

        $user = User::authenticate($username, $password);

        if (!$user) {
            Session::setFlash('danger', 'ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง หรือบัญชีถูกระงับการใช้งาน');
            $this->redirect('index.php?page=login');
        }

        Auth::login($user);
        Session::setFlash('success', 'ยินดีต้อนรับคุณ ' . htmlspecialchars($user->full_name) . ' เข้าสู่ระบบ');

        $this->redirect(Auth::defaultRedirectUrl());
    }

    /**
     * Process logout
     */
    public function logout(): void
    {
        Auth::logout();
        Session::setFlash('info', 'ออกจากระบบเรียบร้อยแล้ว');
        $this->redirect('index.php?page=login');
    }

    /**
     * Display user profile
     */
    public function profile(): void
    {
        Auth::requireLogin();
        $user = Auth::user();

        $this->render('auth/profile', [
            'pageTitle' => 'โปรไฟล์ส่วนตัว — RepairForSchool',
            'user' => $user,
        ]);
    }

    /**
     * Update profile and password
     */
    public function updateProfile(): void
    {
        Auth::requireLogin();
        $this->validateCsrf('index.php?page=profile');
        $user = Auth::user();
        $userId = (int)$user->id;

        $fullName = trim((string)($_POST['full_name'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? ''));
        $department = trim((string)($_POST['department'] ?? ''));
        $currentPassword = (string)($_POST['current_password'] ?? '');
        $newPassword = (string)($_POST['new_password'] ?? '');

        if ($fullName === '') {
            Session::setFlash('danger', 'กรุณาระบุชื่อ-นามสกุล');
            $this->redirect('index.php?page=profile');
        }

        $updateData = [
            'full_name' => $fullName,
            'phone' => $phone ?: null,
            'department' => $department ?: null,
        ];

        // If password change is requested
        if ($newPassword !== '') {
            if ($currentPassword === '') {
                Session::setFlash('danger', 'กรุณาระบุรหัสผ่านปัจจุบันเพื่อยืนยันการเปลี่ยนรหัสผ่านใหม่');
                $this->redirect('index.php?page=profile');
            }

            if (!password_verify($currentPassword, (string)$user->password_hash)) {
                Session::setFlash('danger', 'รหัสผ่านปัจจุบันไม่ถูกต้อง');
                $this->redirect('index.php?page=profile');
            }

            if (strlen($newPassword) < 6) {
                Session::setFlash('danger', 'รหัสผ่านใหม่ต้องมีความยาวอย่างน้อย 6 ตัวอักษร');
                $this->redirect('index.php?page=profile');
            }

            $updateData['password_hash'] = password_hash($newPassword, PASSWORD_BCRYPT);
        }

        User::update($userId, $updateData);
        Session::setFlash('success', 'บันทึกข้อมูลโปรไฟล์เรียบร้อยแล้ว' . ($newPassword !== '' ? ' (เปลี่ยนรหัสผ่านสำเร็จ)' : ''));
        $this->redirect('index.php?page=profile');
    }
}
