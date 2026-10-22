<?php
use App\Core\Auth;
use App\Models\Notification;

$currentUser = Auth::user();
$currentRole = Auth::role();
$currentPage = $_GET['page'] ?? 'dashboard';

$notifications = [];
$unreadNotifCount = 0;
if (Auth::check()) {
    $notifications = Notification::getRecentByUser((int)Auth::id(), 6);
    $unreadNotifCount = Notification::countUnreadByUser((int)Auth::id());
}
?>
<!doctype html>
<html lang="th" class="h-full">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle ?? 'RepairForSchool — ระบบแจ้งซ่อมบำรุงโรงเรียน') ?></title>

  <!-- Google Fonts: Sarabun & Plus Jakarta Sans & JetBrains Mono -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sarabun:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

  <!-- Tailwind CSS Play CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Sarabun', 'Plus Jakarta Sans', 'system-ui', '-apple-system', 'sans-serif'],
            display: ['Plus Jakarta Sans', 'Sarabun', 'sans-serif'],
            mono: ['JetBrains Mono', 'monospace'],
          },
          colors: {
            brand: {
              50: '#f5f3ff',
              100: '#ede9fe',
              200: '#ddd6fe',
              500: '#8b5cf6',
              600: '#7c3aed',
              700: '#6d28d9',
              800: '#5b21b6',
              900: '#4c1d95',
              950: '#2e1065',
            }
          }
        }
      }
    }
  </script>

  <!-- Font Awesome 6.5.2 Pro/Free CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

  <!-- Custom App CSS -->
  <link href="assets/css/app.css" rel="stylesheet">
</head>
<body class="bg-slate-100 text-slate-800 antialiased flex flex-col min-h-[100dvh]">

<!-- Skip Link for Accessibility -->
<a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2.5 focus:bg-violet-600 focus:text-white focus:rounded-2xl focus:outline-none font-bold text-xs">
  ข้ามไปยังเนื้อหาหลัก
</a>

<!-- Sticky Header (White, Flat, No Border, No Shadow, Compact) -->
<header class="sticky top-0 z-40 w-full bg-white text-slate-800 transition-all border-b border-slate-100/80">
  <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-14 gap-2">
      
      <!-- Brand Logo without Icon Background -->
      <div class="flex items-center gap-2 flex-shrink-0">
        <a href="<?= Auth::check() ? Auth::defaultRedirectUrl() : 'index.php?page=landing' ?>" class="flex items-center gap-2 group text-decoration-none">
          <i class="fa-solid fa-wrench text-violet-600 text-lg group-hover:rotate-12 transition-transform" aria-hidden="true"></i>
          <div>
            <span class="font-display font-black text-base tracking-tight text-slate-900 group-hover:text-violet-600 transition-colors leading-none block">RepairForSchool</span>
            <p class="hidden xl:block text-[10px] text-slate-400 font-normal leading-none mt-0.5">ระบบบริหารจัดการงานซ่อมบำรุง</p>
          </div>
        </a>
      </div>

      <!-- Navigation Links with Icons & Dropdowns (Compact & Grouped) -->
      <?php if (Auth::check()): ?>
        <nav class="hidden lg:flex items-center gap-1.5 text-xs font-bold" aria-label="เมนูหลักของผู้ใช้งาน">
          <?php if (Auth::isAdmin()): ?>
            <!-- 1. แดชบอร์ด KPI -->
            <a href="index.php?page=admin-dashboard" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl transition-all whitespace-nowrap group <?= $currentPage === 'admin-dashboard' ? 'bg-violet-600 text-white shadow-none' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
              <i class="fa-solid fa-chart-pie text-xs <?= $currentPage === 'admin-dashboard' ? 'text-white' : 'text-slate-400 group-hover:text-violet-600' ?>" aria-hidden="true"></i>
              <span>แดชบอร์ด KPI</span>
            </a>

            <!-- 2. ใบแจ้งซ่อม -->
            <a href="index.php?page=tickets" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl transition-all whitespace-nowrap group <?= in_array($currentPage, ['tickets', 'tickets-show']) ? 'bg-violet-600 text-white shadow-none' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
              <i class="fa-solid fa-clipboard-list text-xs <?= in_array($currentPage, ['tickets', 'tickets-show']) ? 'text-white' : 'text-slate-400 group-hover:text-violet-600' ?>" aria-hidden="true"></i>
              <span>ใบแจ้งซ่อม</span>
            </a>

            <!-- 3. Dropdown: พัสดุและบำรุงรักษา -->
            <?php $isMaintActive = in_array($currentPage, ['assets', 'assets-qr', 'spare-parts', 'pm-schedules']); ?>
            <div class="relative group" id="maintNavWrapper">
              <button 
                type="button" 
                id="maintNavButton" 
                onclick="toggleNavMenu('maintNavMenu')"
                aria-haspopup="true"
                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl transition-all whitespace-nowrap <?= $isMaintActive ? 'bg-violet-600 text-white shadow-none' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>"
              >
                <i class="fa-solid fa-boxes-stacked text-xs <?= $isMaintActive ? 'text-white' : 'text-slate-400 group-hover:text-violet-600' ?>" aria-hidden="true"></i>
                <span>พัสดุและบำรุงรักษา</span>
                <i class="fa-solid fa-chevron-down text-[9px] opacity-70 group-hover:rotate-180 transition-transform" aria-hidden="true"></i>
              </button>
              <div id="maintNavMenu" class="hidden group-hover:block absolute left-0 mt-1 w-52 rounded-2xl bg-white text-slate-800 py-1.5 z-50 shadow-xl border border-slate-100" role="menu">
                <a href="index.php?page=assets" role="menuitem" class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-bold transition-colors <?= in_array($currentPage, ['assets', 'assets-qr']) ? 'text-violet-600 bg-violet-50' : 'text-slate-700 hover:bg-slate-50 hover:text-violet-600' ?>">
                  <i class="fa-solid fa-qrcode text-xs w-4 text-center <?= in_array($currentPage, ['assets', 'assets-qr']) ? 'text-violet-600' : 'text-slate-400' ?>" aria-hidden="true"></i>
                  <span>ทะเบียนครุภัณฑ์</span>
                </a>
                <a href="index.php?page=spare-parts" role="menuitem" class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-bold transition-colors <?= $currentPage === 'spare-parts' ? 'text-violet-600 bg-violet-50' : 'text-slate-700 hover:bg-slate-50 hover:text-violet-600' ?>">
                  <i class="fa-solid fa-boxes-stacked text-xs w-4 text-center <?= $currentPage === 'spare-parts' ? 'text-violet-600' : 'text-slate-400' ?>" aria-hidden="true"></i>
                  <span>คลังอะไหล่</span>
                </a>
                <a href="index.php?page=pm-schedules" role="menuitem" class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-bold transition-colors <?= $currentPage === 'pm-schedules' ? 'text-violet-600 bg-violet-50' : 'text-slate-700 hover:bg-slate-50 hover:text-violet-600' ?>">
                  <i class="fa-solid fa-calendar-check text-xs w-4 text-center <?= $currentPage === 'pm-schedules' ? 'text-violet-600' : 'text-slate-400' ?>" aria-hidden="true"></i>
                  <span>แผนบำรุงรักษา</span>
                </a>
              </div>
            </div>

            <!-- 4. Dropdown: จัดการระบบ -->
            <?php $isSystemActive = in_array($currentPage, ['users', 'equipment-types']); ?>
            <div class="relative group" id="systemNavWrapper">
              <button 
                type="button" 
                id="systemNavButton" 
                onclick="toggleNavMenu('systemNavMenu')"
                aria-haspopup="true"
                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl transition-all whitespace-nowrap <?= $isSystemActive ? 'bg-violet-600 text-white shadow-none' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>"
              >
                <i class="fa-solid fa-sliders text-xs <?= $isSystemActive ? 'text-white' : 'text-slate-400 group-hover:text-violet-600' ?>" aria-hidden="true"></i>
                <span>จัดการระบบ</span>
                <i class="fa-solid fa-chevron-down text-[9px] opacity-70 group-hover:rotate-180 transition-transform" aria-hidden="true"></i>
              </button>
              <div id="systemNavMenu" class="hidden group-hover:block absolute left-0 mt-1 w-48 rounded-2xl bg-white text-slate-800 py-1.5 z-50 shadow-xl border border-slate-100" role="menu">
                <a href="index.php?page=users" role="menuitem" class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-bold transition-colors <?= $currentPage === 'users' ? 'text-violet-600 bg-violet-50' : 'text-slate-700 hover:bg-slate-50 hover:text-violet-600' ?>">
                  <i class="fa-solid fa-users-gear text-xs w-4 text-center <?= $currentPage === 'users' ? 'text-violet-600' : 'text-slate-400' ?>" aria-hidden="true"></i>
                  <span>จัดการผู้ใช้งาน</span>
                </a>
                <a href="index.php?page=equipment-types" role="menuitem" class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-bold transition-colors <?= in_array($currentPage, ['equipment-types']) ? 'text-violet-600 bg-violet-50' : 'text-slate-700 hover:bg-slate-50 hover:text-violet-600' ?>">
                  <i class="fa-solid fa-tags text-xs w-4 text-center <?= in_array($currentPage, ['equipment-types']) ? 'text-violet-600' : 'text-slate-400' ?>" aria-hidden="true"></i>
                  <span>หมวดหมู่อุปกรณ์</span>
                </a>
              </div>
            </div>

          <?php elseif (Auth::isTechnician()): ?>
            <!-- 1. งานซ่อมของฉัน -->
            <a href="index.php?page=technician-dashboard" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl transition-all whitespace-nowrap group <?= $currentPage === 'technician-dashboard' ? 'bg-violet-600 text-white shadow-none' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
              <i class="fa-solid fa-screwdriver-wrench text-xs <?= $currentPage === 'technician-dashboard' ? 'text-white' : 'text-slate-400 group-hover:text-violet-600' ?>" aria-hidden="true"></i>
              <span>งานซ่อมของฉัน</span>
            </a>

            <!-- 2. รายการงานทั้งหมด -->
            <a href="index.php?page=tickets" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl transition-all whitespace-nowrap group <?= in_array($currentPage, ['tickets', 'tickets-show']) ? 'bg-violet-600 text-white shadow-none' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
              <i class="fa-solid fa-clipboard-list text-xs <?= in_array($currentPage, ['tickets', 'tickets-show']) ? 'text-white' : 'text-slate-400 group-hover:text-violet-600' ?>" aria-hidden="true"></i>
              <span>รายการงานทั้งหมด</span>
            </a>

            <!-- 3. Dropdown: พัสดุและบำรุงรักษา -->
            <?php $isTechMaintActive = in_array($currentPage, ['assets', 'assets-qr', 'spare-parts', 'pm-schedules']); ?>
            <div class="relative group" id="techMaintNavWrapper">
              <button 
                type="button" 
                id="techMaintNavButton" 
                onclick="toggleNavMenu('techMaintNavMenu')"
                aria-haspopup="true"
                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl transition-all whitespace-nowrap <?= $isTechMaintActive ? 'bg-violet-600 text-white shadow-none' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>"
              >
                <i class="fa-solid fa-boxes-stacked text-xs <?= $isTechMaintActive ? 'text-white' : 'text-slate-400 group-hover:text-violet-600' ?>"></i>
                <span>พัสดุและบำรุงรักษา</span>
                <i class="fa-solid fa-chevron-down text-[9px] opacity-70 group-hover:rotate-180 transition-transform" aria-hidden="true"></i>
              </button>
              <div id="techMaintNavMenu" class="hidden group-hover:block absolute left-0 mt-1 w-52 rounded-2xl bg-white text-slate-800 py-1.5 z-50 shadow-xl border border-slate-100" role="menu">
                <a href="index.php?page=assets" role="menuitem" class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-bold transition-colors <?= in_array($currentPage, ['assets', 'assets-qr']) ? 'text-violet-600 bg-violet-50' : 'text-slate-700 hover:bg-slate-50 hover:text-violet-600' ?>">
                  <i class="fa-solid fa-qrcode text-xs w-4 text-center <?= in_array($currentPage, ['assets', 'assets-qr']) ? 'text-violet-600' : 'text-slate-400' ?>" aria-hidden="true"></i>
                  <span>ทะเบียนครุภัณฑ์</span>
                </a>
                <a href="index.php?page=spare-parts" role="menuitem" class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-bold transition-colors <?= $currentPage === 'spare-parts' ? 'text-violet-600 bg-violet-50' : 'text-slate-700 hover:bg-slate-50 hover:text-violet-600' ?>">
                  <i class="fa-solid fa-boxes-stacked text-xs w-4 text-center <?= $currentPage === 'spare-parts' ? 'text-violet-600' : 'text-slate-400' ?>"></i>
                  <span>คลังอะไหล่</span>
                </a>
                <a href="index.php?page=pm-schedules" role="menuitem" class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-bold transition-colors <?= $currentPage === 'pm-schedules' ? 'text-violet-600 bg-violet-50' : 'text-slate-700 hover:bg-slate-50 hover:text-violet-600' ?>">
                  <i class="fa-solid fa-calendar-check text-xs w-4 text-center <?= $currentPage === 'pm-schedules' ? 'text-violet-600' : 'text-slate-400' ?>" aria-hidden="true"></i>
                  <span>แผนบำรุงรักษา</span>
                </a>
              </div>
            </div>

          <?php elseif (Auth::isTeacher()): ?>
            <a href="index.php?page=teacher-dashboard" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl transition-all whitespace-nowrap group <?= $currentPage === 'teacher-dashboard' ? 'bg-violet-600 text-white shadow-none' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
              <i class="fa-solid fa-clock-rotate-left text-xs <?= $currentPage === 'teacher-dashboard' ? 'text-white' : 'text-slate-400 group-hover:text-violet-600' ?>" aria-hidden="true"></i>
              <span>ประวัติการแจ้งซ่อม</span>
            </a>
            <a href="index.php?page=assets" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl transition-all whitespace-nowrap group <?= in_array($currentPage, ['assets', 'assets-qr']) ? 'bg-violet-600 text-white shadow-none' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
              <i class="fa-solid fa-qrcode text-xs <?= in_array($currentPage, ['assets', 'assets-qr']) ? 'text-white' : 'text-slate-400 group-hover:text-violet-600' ?>" aria-hidden="true"></i>
              <span>ทะเบียนครุภัณฑ์</span>
            </a>
            <a href="index.php?page=tickets-create" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl transition-all whitespace-nowrap text-violet-600 hover:bg-violet-50 group font-bold">
              <i class="fa-solid fa-circle-plus text-xs text-violet-500 group-hover:text-violet-700" aria-hidden="true"></i>
              <span>ส่งคำขอแจ้งซ่อมใหม่</span>
            </a>
          <?php endif; ?>
        </nav>
      <?php else: ?>
        <!-- Public Navigation -->
        <nav class="hidden lg:flex items-center gap-1 text-xs font-bold" aria-label="เมนูหลัก">
          <a href="index.php?page=landing#features" class="inline-flex items-center gap-1.5 text-slate-600 hover:text-slate-900 hover:bg-slate-100 px-3 py-1.5 rounded-xl transition-colors group">
            <i class="fa-solid fa-star text-xs text-slate-400 group-hover:text-amber-500 transition-colors" aria-hidden="true"></i>
            <span>จุดเด่นระบบ</span>
          </a>
          <a href="index.php?page=landing#workflow" class="inline-flex items-center gap-1.5 text-slate-600 hover:text-slate-900 hover:bg-slate-100 px-3 py-1.5 rounded-xl transition-colors group">
            <i class="fa-solid fa-diagram-project text-xs text-slate-400 group-hover:text-violet-600 transition-colors" aria-hidden="true"></i>
            <span>ขั้นตอนการทำงาน</span>
          </a>
          <a href="index.php?page=landing#metrics" class="inline-flex items-center gap-1.5 text-slate-600 hover:text-slate-900 hover:bg-slate-100 px-3 py-1.5 rounded-xl transition-colors group">
            <i class="fa-solid fa-chart-line text-xs text-slate-400 group-hover:text-emerald-500 transition-colors" aria-hidden="true"></i>
            <span>ผลการดำเนินงาน</span>
          </a>
          <a href="index.php?page=login" class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white font-bold text-xs tracking-wide transition-all active:scale-95 shadow-none">
            <i class="fa-solid fa-arrow-right-to-bracket text-xs" aria-hidden="true"></i>
            <span>เข้าสู่ระบบ</span>
          </a>
        </nav>
      <?php endif; ?>

      <!-- User Profile & Action Menu (Compact) -->
      <div class="flex items-center gap-2">
        <?php if (Auth::check()): ?>
          <?php if (!Auth::isTeacher()): ?>
            <a href="index.php?page=tickets-create" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold transition-all active:scale-95 shadow-none">
              <i class="fa-solid fa-plus text-[10px]" aria-hidden="true"></i>
              <span>แจ้งซ่อม</span>
            </a>
          <?php endif; ?>

          <!-- In-App Notification Bell Component -->
          <div class="relative" id="notificationMenuWrapper">
            <button 
              type="button" 
              id="notificationMenuButton"
              onclick="toggleNotificationDropdown()" 
              aria-haspopup="true" 
              aria-expanded="false" 
              aria-label="การแจ้งเตือน"
              class="relative p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 hover:text-slate-900 transition-all focus:outline-none"
            >
              <i class="fa-solid fa-bell text-xs" aria-hidden="true"></i>
              <?php if ($unreadNotifCount > 0): ?>
                <span class="absolute -top-1 -right-1 min-w-[16px] h-[16px] px-1 rounded-full bg-rose-500 text-white text-[9px] font-black flex items-center justify-center tabular-nums">
                  <?= $unreadNotifCount > 9 ? '9+' : $unreadNotifCount ?>
                </span>
              <?php endif; ?>
            </button>

            <!-- Notification Dropdown Menu -->
            <div id="notificationDropdownMenu" class="hidden absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl bg-white text-slate-800 z-50 overflow-hidden divide-y divide-slate-100 shadow-xl border border-slate-100" role="menu">
              <div class="px-4 py-3 bg-slate-50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span class="font-black text-xs text-slate-900">การแจ้งเตือน</span>
                  <?php if ($unreadNotifCount > 0): ?>
                    <span class="px-2 py-0.5 rounded-full bg-violet-100 text-violet-700 text-[10px] font-black"><?= $unreadNotifCount ?> ใหม่</span>
                  <?php endif; ?>
                </div>
                <?php if ($unreadNotifCount > 0): ?>
                  <a href="index.php?page=notifications-read-all" class="text-[11px] font-bold text-violet-600 hover:text-violet-800 transition-colors">
                    อ่านทั้งหมด
                  </a>
                <?php endif; ?>
              </div>

              <div class="max-h-80 overflow-y-auto divide-y divide-slate-50">
                <?php if (!empty($notifications)): ?>
                  <?php foreach ($notifications as $n): ?>
                    <a 
                      href="index.php?page=notifications-read&id=<?= $n['id'] ?>&redirect=<?= urlencode($n['link'] ?? 'index.php') ?>" 
                      class="block px-4 py-3 transition-colors <?= empty($n['is_read']) ? 'bg-violet-50/40 hover:bg-violet-50/70' : 'hover:bg-slate-50' ?>"
                    >
                      <div class="flex items-start gap-2.5">
                        <div class="w-1.5 h-1.5 rounded-full mt-1.5 flex-shrink-0 <?= empty($n['is_read']) ? 'bg-violet-600' : 'bg-slate-300' ?>"></div>
                        <div class="flex-1 space-y-0.5">
                          <p class="text-xs font-bold text-slate-900 leading-tight"><?= htmlspecialchars($n['title']) ?></p>
                          <p class="text-[11px] text-slate-600 leading-relaxed"><?= htmlspecialchars($n['message']) ?></p>
                          <span class="text-[10px] text-slate-400 block pt-0.5"><?= date('d/m/Y H:i น.', strtotime($n['created_at'])) ?></span>
                        </div>
                      </div>
                    </a>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div class="py-8 text-center text-xs text-slate-400">
                    <i class="fa-regular fa-bell-slash text-xl mb-1.5 opacity-30 block"></i>
                    <span>ไม่มีรายการแจ้งเตือนใหม่ในขณะนี้</span>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- User Dropdown Component (Compact) -->
          <div class="relative" id="userMenuWrapper">
            <button 
              type="button" 
              id="userMenuButton"
              onclick="toggleUserDropdown()" 
              aria-haspopup="true" 
              aria-expanded="false" 
              aria-label="เมนูบัญชีผู้ใช้งาน"
              class="flex items-center gap-2 py-1 px-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-left transition-all focus:outline-none"
            >
              <div class="w-7 h-7 rounded-lg bg-violet-100 text-violet-700 flex items-center justify-center text-[11px] font-bold flex-shrink-0">
                <i class="fa-solid <?= match ($currentRole) {
                  'admin' => 'fa-user-shield',
                  'technician' => 'fa-user-gear',
                  'teacher' => 'fa-user-tie',
                  default => 'fa-user'
                } ?>" aria-hidden="true"></i>
              </div>
              <div class="hidden sm:block">
                <div class="text-xs font-bold text-slate-900 leading-tight max-w-[110px] xl:max-w-[140px] truncate"><?= htmlspecialchars($currentUser->full_name ?? '') ?></div>
                <div class="text-[9px] text-slate-500 font-medium leading-tight">
                  <?= match ($currentRole) {
                    'admin' => 'ผู้ดูแลระบบ',
                    'technician' => 'ช่างเทคนิค',
                    'teacher' => 'ครูและบุคลากร',
                    default => 'ผู้ใช้งาน'
                  } ?>
                </div>
              </div>
              <i class="fa-solid fa-chevron-down text-[9px] text-slate-400" aria-hidden="true"></i>
            </button>

            <!-- Dropdown Menu -->
            <div id="userDropdownMenu" class="hidden absolute right-0 mt-2 w-56 rounded-3xl bg-white text-slate-800 py-2 z-50 overflow-hidden shadow-xl border border-slate-100" role="menu" aria-labelledby="userMenuButton">
              <div class="px-4 py-3 bg-slate-50">
                <p class="text-xs font-bold text-slate-900 leading-tight"><?= htmlspecialchars($currentUser->full_name ?? '') ?></p>
                <p class="text-[11px] text-slate-500 truncate mt-0.5"><?= htmlspecialchars($currentUser->department ?? $currentUser->email) ?></p>
              </div>

              <div class="py-1" role="none">
                <a href="index.php?page=profile" role="menuitem" class="flex items-center gap-2.5 px-4 py-2.5 text-xs text-slate-700 hover:bg-slate-100 hover:text-violet-600 transition-colors font-medium">
                  <i class="fa-solid fa-user-pen text-slate-400 w-4 text-center" aria-hidden="true"></i>
                  <span>โปรไฟล์ส่วนตัว</span>
                </a>
              </div>

              <?php if (Auth::isAdmin()): ?>
              <div class="py-1" role="none">
                <a href="http://localhost:8090/phpmyadmin" target="_blank" role="menuitem" class="flex items-center gap-2.5 px-4 py-2.5 text-xs text-slate-700 hover:bg-slate-100 hover:text-violet-600 transition-colors font-medium">
                  <i class="fa-solid fa-database text-slate-400 w-4 text-center" aria-hidden="true"></i>
                  <span>จัดการฐานข้อมูล</span>
                </a>
              </div>
              <?php endif; ?>

              <div class="pt-1" role="none">
                <a href="index.php?page=logout" role="menuitem" class="flex items-center gap-2.5 px-4 py-2.5 text-xs text-rose-600 hover:bg-rose-50 transition-colors font-bold">
                  <i class="fa-solid fa-arrow-right-from-bracket text-xs w-4 text-center" aria-hidden="true"></i>
                  <span>ออกจากระบบ</span>
                </a>
              </div>
            </div>
          </div>
        <?php else: ?>
          <a href="index.php?page=login" class="md:hidden inline-flex items-center gap-1.5 px-4 py-2 rounded-2xl bg-violet-600 text-white text-xs font-bold">
            <i class="fa-solid fa-arrow-right-to-bracket text-xs" aria-hidden="true"></i>
            <span>เข้าสู่ระบบ</span>
          </a>
        <?php endif; ?>

        <!-- Mobile Drawer Toggle -->
        <button 
          type="button" 
          id="mobileMenuToggle"
          onclick="toggleMobileMenu()" 
          aria-label="เปิดเมนูหลัก"
          aria-expanded="false"
          class="lg:hidden p-2 rounded-2xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:outline-none transition-colors"
        >
          <i class="fa-solid fa-bars text-base" aria-hidden="true"></i>
        </button>
      </div>

    </div>
  </div>

  <!-- Mobile Dropdown Menu -->
  <div id="mobileMenu" class="hidden lg:hidden px-4 pt-2 pb-4 bg-white space-y-3 text-sm font-medium border-t border-slate-100" role="menu" aria-label="เมนูมือถือ">
    <?php if (Auth::check()): ?>
      <?php if (Auth::isAdmin()): ?>
        <div class="space-y-1">
          <div class="px-2 py-0.5 text-[10px] font-black uppercase text-slate-400 tracking-wider">ภาพรวมและงานซ่อม</div>
          <a href="index.php?page=admin-dashboard" role="menuitem" class="min-h-[40px] flex items-center gap-3 px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-100 font-bold text-xs">
            <i class="fa-solid fa-chart-pie w-5 text-center text-violet-600" aria-hidden="true"></i>
            <span>แดชบอร์ด KPI</span>
          </a>
          <a href="index.php?page=tickets" role="menuitem" class="min-h-[40px] flex items-center gap-3 px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-100 font-bold text-xs">
            <i class="fa-solid fa-clipboard-list w-5 text-center text-violet-600" aria-hidden="true"></i>
            <span>ใบแจ้งซ่อม</span>
          </a>
        </div>
        <div class="space-y-1 pt-2 border-t border-slate-100">
          <div class="px-2 py-0.5 text-[10px] font-black uppercase text-slate-400 tracking-wider">พัสดุและบำรุงรักษา</div>
          <a href="index.php?page=assets" role="menuitem" class="min-h-[40px] flex items-center gap-3 px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-100 font-bold text-xs">
            <i class="fa-solid fa-qrcode w-5 text-center text-violet-600" aria-hidden="true"></i>
            <span>ทะเบียนครุภัณฑ์</span>
          </a>
          <a href="index.php?page=spare-parts" role="menuitem" class="min-h-[40px] flex items-center gap-3 px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-100 font-bold text-xs">
            <i class="fa-solid fa-boxes-stacked w-5 text-center text-violet-600" aria-hidden="true"></i>
            <span>คลังอะไหล่</span>
          </a>
          <a href="index.php?page=pm-schedules" role="menuitem" class="min-h-[40px] flex items-center gap-3 px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-100 font-bold text-xs">
            <i class="fa-solid fa-calendar-check w-5 text-center text-violet-600" aria-hidden="true"></i>
            <span>แผนบำรุงรักษา</span>
          </a>
        </div>
        <div class="space-y-1 pt-2 border-t border-slate-100">
          <div class="px-2 py-0.5 text-[10px] font-black uppercase text-slate-400 tracking-wider">จัดการระบบ</div>
          <a href="index.php?page=users" role="menuitem" class="min-h-[40px] flex items-center gap-3 px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-100 font-bold text-xs">
            <i class="fa-solid fa-users-gear w-5 text-center text-violet-600" aria-hidden="true"></i>
            <span>จัดการผู้ใช้งาน</span>
          </a>
          <a href="index.php?page=equipment-types" role="menuitem" class="min-h-[40px] flex items-center gap-3 px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-100 font-bold text-xs">
            <i class="fa-solid fa-tags w-5 text-center text-violet-600" aria-hidden="true"></i>
            <span>หมวดหมู่อุปกรณ์</span>
          </a>
        </div>
      <?php elseif (Auth::isTechnician()): ?>
        <div class="space-y-1">
          <div class="px-2 py-0.5 text-[10px] font-black uppercase text-slate-400 tracking-wider">งานซ่อมบำรุง</div>
          <a href="index.php?page=technician-dashboard" role="menuitem" class="min-h-[40px] flex items-center gap-3 px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-100 font-bold text-xs">
            <i class="fa-solid fa-screwdriver-wrench w-5 text-center text-violet-600" aria-hidden="true"></i>
            <span>งานซ่อมของฉัน</span>
          </a>
          <a href="index.php?page=tickets" role="menuitem" class="min-h-[40px] flex items-center gap-3 px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-100 font-bold text-xs">
            <i class="fa-solid fa-clipboard-list w-5 text-center text-violet-600" aria-hidden="true"></i>
            <span>รายการงานทั้งหมด</span>
          </a>
        </div>
        <div class="space-y-1 pt-2 border-t border-slate-100">
          <div class="px-2 py-0.5 text-[10px] font-black uppercase text-slate-400 tracking-wider">พัสดุและบำรุงรักษา</div>
          <a href="index.php?page=assets" role="menuitem" class="min-h-[40px] flex items-center gap-3 px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-100 font-bold text-xs">
            <i class="fa-solid fa-qrcode w-5 text-center text-violet-600" aria-hidden="true"></i>
            <span>ทะเบียนครุภัณฑ์</span>
          </a>
          <a href="index.php?page=spare-parts" role="menuitem" class="min-h-[40px] flex items-center gap-3 px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-100 font-bold text-xs">
            <i class="fa-solid fa-boxes-stacked w-5 text-center text-violet-600" aria-hidden="true"></i>
            <span>คลังอะไหล่</span>
          </a>
          <a href="index.php?page=pm-schedules" role="menuitem" class="min-h-[40px] flex items-center gap-3 px-3 py-2 rounded-xl text-slate-700 hover:bg-slate-100 font-bold text-xs">
            <i class="fa-solid fa-calendar-check w-5 text-center text-violet-600" aria-hidden="true"></i>
            <span>แผนบำรุงรักษา</span>
          </a>
        </div>
      <?php elseif (Auth::isTeacher()): ?>
        <a href="index.php?page=teacher-dashboard" role="menuitem" class="min-h-[44px] flex items-center gap-3 px-4 py-2.5 rounded-2xl text-slate-700 hover:bg-slate-100 font-bold text-xs">
          <i class="fa-solid fa-clock-rotate-left w-5 text-center text-violet-600" aria-hidden="true"></i>
          <span>ประวัติการแจ้งซ่อม</span>
        </a>
        <a href="index.php?page=assets" role="menuitem" class="min-h-[44px] flex items-center gap-3 px-4 py-2.5 rounded-2xl text-slate-700 hover:bg-slate-100 font-bold text-xs">
          <i class="fa-solid fa-qrcode w-5 text-center text-violet-600" aria-hidden="true"></i>
          <span>ทะเบียนครุภัณฑ์</span>
        </a>
        <a href="index.php?page=tickets-create" role="menuitem" class="min-h-[44px] flex items-center gap-3 px-4 py-2.5 rounded-2xl text-violet-600 font-bold hover:bg-slate-100 text-xs">
          <i class="fa-solid fa-circle-plus w-5 text-center text-violet-600" aria-hidden="true"></i>
          <span>ส่งคำขอแจ้งซ่อมใหม่</span>
        </a>
      <?php endif; ?>
    <?php else: ?>
      <a href="index.php?page=landing#features" role="menuitem" class="min-h-[44px] flex items-center gap-3 px-4 py-2.5 rounded-2xl text-slate-700 hover:bg-slate-100 font-bold text-xs">
        <i class="fa-solid fa-star w-5 text-center text-slate-400" aria-hidden="true"></i>
        <span>จุดเด่นระบบ</span>
      </a>
      <a href="index.php?page=landing#workflow" role="menuitem" class="min-h-[44px] flex items-center gap-3 px-4 py-2.5 rounded-2xl text-slate-700 hover:bg-slate-100 font-bold text-xs">
        <i class="fa-solid fa-diagram-project w-5 text-center text-slate-400" aria-hidden="true"></i>
        <span>ขั้นตอนการทำงาน</span>
      </a>
      <a href="index.php?page=landing#metrics" role="menuitem" class="min-h-[44px] flex items-center gap-3 px-4 py-2.5 rounded-2xl text-slate-700 hover:bg-slate-100 font-bold text-xs">
        <i class="fa-solid fa-chart-line w-5 text-center text-slate-400" aria-hidden="true"></i>
        <span>ผลการดำเนินงาน</span>
      </a>
      <a href="index.php?page=login" role="menuitem" class="min-h-[44px] flex items-center gap-3 px-4 py-2.5 rounded-2xl text-violet-600 font-bold hover:bg-slate-100 text-xs">
        <i class="fa-solid fa-arrow-right-to-bracket w-5 text-center text-violet-600" aria-hidden="true"></i>
        <span>เข้าสู่ระบบ</span>
      </a>
    <?php endif; ?>
  </div>
</header>

<!-- Flash Alerts Notification System -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4" aria-live="polite" aria-atomic="true">
  <?php if (!empty($flashes)): ?>
    <?php foreach ($flashes as $type => $messages): ?>
      <?php foreach ($messages as $msg): ?>
        <?php
          $alertClasses = match ($type) {
            'success' => 'bg-emerald-100 text-emerald-900',
            'danger' => 'bg-rose-100 text-rose-900',
            'warning' => 'bg-amber-100 text-amber-900',
            default => 'bg-violet-100 text-violet-900',
          };
          $iconClass = match ($type) {
            'success' => 'fa-circle-check text-emerald-700',
            'danger' => 'fa-circle-xmark text-rose-700',
            'warning' => 'fa-triangle-exclamation text-amber-700',
            default => 'fa-circle-info text-violet-700',
          };
        ?>
        <div class="flex items-center justify-between p-4 mb-3 rounded-2xl <?= $alertClasses ?> transition-all duration-200" role="alert">
          <div class="flex items-center gap-3">
            <i class="fa-solid <?= $iconClass ?>"></i>
            <span class="text-sm font-bold"><?= htmlspecialchars($msg) ?></span>
          </div>
          <button 
            type="button" 
            onclick="this.parentElement.remove()" 
            aria-label="ปิดการแจ้งเตือนนี้"
            class="text-slate-500 hover:text-slate-700 p-1.5 rounded-xl focus:outline-none"
          >
            <i class="fa-solid fa-xmark text-sm"></i>
          </button>
        </div>
      <?php endforeach; ?>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<main id="main-content" class="flex-grow">
