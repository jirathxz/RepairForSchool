<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

/**
 * Authentication and Role Guard Class
 */
class Auth
{
    private const SESSION_KEY = 'auth_user_id';
    private const SESSION_ROLE = 'auth_user_role';

    private static ?User $cachedUser = null;

    /**
     * Check if user is currently authenticated
     */
    public static function check(): bool
    {
        return Session::has(self::SESSION_KEY);
    }

    /**
     * Get currently authenticated User model instance
     */
    public static function user(): ?User
    {
        if (!self::check()) {
            return null;
        }

        if (self::$cachedUser === null) {
            $userId = (int)Session::get(self::SESSION_KEY);
            self::$cachedUser = User::find($userId);

            // If user no longer exists or is deactivated, force logout
            if (!self::$cachedUser || (int)self::$cachedUser->is_active !== 1) {
                self::logout();
                return null;
            }
        }

        return self::$cachedUser;
    }

    /**
     * Get current user ID
     */
    public static function id(): ?int
    {
        return self::check() ? (int)Session::get(self::SESSION_KEY) : null;
    }

    /**
     * Get current user role ('admin', 'teacher', 'technician')
     */
    public static function role(): ?string
    {
        return self::check() ? (string)Session::get(self::SESSION_ROLE) : null;
    }

    /**
     * Get current user full name
     */
    public static function name(): string
    {
        $user = self::user();
        return $user ? (string)$user->full_name : 'ผู้ใช้งาน';
    }

    /**
     * Role checks
     */
    public static function isAdmin(): bool
    {
        return self::role() === 'admin';
    }

    public static function isTechnician(): bool
    {
        return self::role() === 'technician';
    }

    public static function isTeacher(): bool
    {
        return self::role() === 'teacher';
    }

    /**
     * Log in a user instance
     */
    public static function login(User $user): void
    {
        Session::start();
        Session::regenerate();
        Session::set(self::SESSION_KEY, (int)$user->id);
        Session::set(self::SESSION_ROLE, (string)$user->role);
        self::$cachedUser = $user;
    }

    /**
     * Log out current user
     */
    public static function logout(): void
    {
        Session::remove(self::SESSION_KEY);
        Session::remove(self::SESSION_ROLE);
        self::$cachedUser = null;
        Session::destroy();
    }

    /**
     * Guard: Require user to be logged in
     */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            Session::setFlash('warning', 'กรุณาเข้าสู่ระบบก่อนเข้าใช้งาน');
            Response::redirect('index.php?page=login');
        }
    }

    /**
     * Guard: Require specific role(s)
     */
    public static function requireRole(string ...$roles): void
    {
        self::requireLogin();

        $currentRole = self::role();
        if (!in_array($currentRole, $roles, true)) {
            Session::setFlash('danger', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
            Response::redirect(self::defaultRedirectUrl());
        }
    }

    /**
     * Guard alias for requireLogin
     */
    public static function requireAuth(): void
    {
        self::requireLogin();
    }

    /**
     * Guard alias for requireRole('admin')
     */
    public static function requireAdmin(): void
    {
        self::requireRole('admin');
    }

    /**
     * Get default dashboard URL based on role
     */
    public static function defaultRedirectUrl(): string
    {
        $role = self::role();
        return match ($role) {
            'admin' => 'index.php?page=admin-dashboard',
            'technician' => 'index.php?page=technician-dashboard',
            'teacher' => 'index.php?page=teacher-dashboard',
            default => 'index.php?page=login',
        };
    }

    /**
     * Get role badge label in Thai with Tailwind CSS & Font Awesome
     */
    public static function getRoleBadge(string $role): string
    {
        return match ($role) {
            'admin' => '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-violet-100 text-violet-800"><i class="fa-solid fa-shield-halved text-[11px]"></i>ผู้ดูแลระบบ</span>',
            'technician' => '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-200 text-slate-800"><i class="fa-solid fa-wrench text-[11px]"></i>เจ้าหน้าที่ช่าง</span>',
            'teacher' => '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700"><i class="fa-solid fa-chalkboard-user text-[11px]"></i>ครูและบุคลากร</span>',
            default => '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-700">' . htmlspecialchars($role) . '</span>',
        };
    }
}
