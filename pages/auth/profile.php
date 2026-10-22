<?php
require_once __DIR__ . '/../layout/header.php';
use App\Core\Session;
use App\Core\Auth;
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

  <!-- Breadcrumbs -->
  <nav class="flex items-center gap-2 text-xs text-slate-500">
    <a href="index.php" class="hover:text-violet-600 transition-colors font-medium">หน้าหลัก</a>
    <i class="fa-solid fa-chevron-right text-[10px] text-slate-400" aria-hidden="true"></i>
    <span class="text-slate-900 font-bold">ข้อมูลบัญชีผู้ใช้งานส่วนตัว</span>
  </nav>

  <!-- Main Card -->
  <div class="bg-white rounded-3xl overflow-hidden">
    <!-- Header -->
    <div class="p-6 bg-slate-50/60 flex items-center justify-between gap-4">
      <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center text-xl flex-shrink-0">
          <i class="fa-solid fa-user-pen" aria-hidden="true"></i>
        </div>
        <div>
          <h1 class="text-lg font-black text-slate-900">โปรไฟล์ส่วนตัว และความปลอดภัย</h1>
          <p class="text-xs text-slate-500 mt-0.5">จัดการข้อมูลติดต่อส่วนตัวและเปลี่ยนรหัสผ่านเพื่อความปลอดภัยในการเข้าใช้งาน</p>
        </div>
      </div>
      <div>
        <?= Auth::getRoleBadge((string)$user->role) ?>
      </div>
    </div>

    <!-- Form -->
    <form action="index.php?page=profile-update" method="POST" class="p-6 sm:p-8 space-y-6">
      <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <!-- Username (Readonly) -->
        <div>
          <label class="block text-xs font-bold text-slate-500 mb-1.5">ชื่อผู้ใช้งานในระบบ (Username)</label>
          <input 
            type="text" 
            value="<?= htmlspecialchars((string)$user->username) ?>" 
            disabled 
            class="w-full px-4 py-2.5 rounded-2xl bg-slate-100 text-slate-500 text-xs font-mono font-bold cursor-not-allowed"
          >
          <span class="text-[10px] text-slate-400 block mt-1">ชื่อผู้ใช้งานไม่สามารถเปลี่ยนแปลงได้</span>
        </div>

        <!-- Email (Readonly) -->
        <div>
          <label class="block text-xs font-bold text-slate-500 mb-1.5">อีเมลที่ลงทะเบียน</label>
          <input 
            type="email" 
            value="<?= htmlspecialchars((string)$user->email) ?>" 
            disabled 
            class="w-full px-4 py-2.5 rounded-2xl bg-slate-100 text-slate-500 text-xs font-mono font-bold cursor-not-allowed"
          >
          <span class="text-[10px] text-slate-400 block mt-1">ติดต่อผู้ดูแลระบบหากต้องการเปลี่ยนอีเมล</span>
        </div>

        <!-- Full Name -->
        <div>
          <label for="full_name" class="block text-xs font-bold text-slate-700 mb-1.5">
            ชื่อ-นามสกุล <span class="text-rose-500">*</span>
          </label>
          <input 
            type="text" 
            id="full_name" 
            name="full_name" 
            required 
            value="<?= htmlspecialchars((string)$user->full_name) ?>"
            class="w-full px-4 py-2.5 rounded-2xl bg-slate-100 focus:bg-white text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all"
          >
        </div>

        <!-- Phone -->
        <div>
          <label for="phone" class="block text-xs font-bold text-slate-700 mb-1.5">
            หมายเลขโทรศัพท์ติดต่อ
          </label>
          <input 
            type="text" 
            id="phone" 
            name="phone" 
            value="<?= htmlspecialchars((string)($user->phone ?? '')) ?>"
            placeholder="เช่น 081-234-5678"
            class="w-full px-4 py-2.5 rounded-2xl bg-slate-100 focus:bg-white text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-violet-500/20 font-mono transition-all"
          >
        </div>

        <!-- Department -->
        <div class="sm:col-span-2">
          <label for="department" class="block text-xs font-bold text-slate-700 mb-1.5">
            กลุ่มสาระการเรียนรู้ หรือฝ่ายงานที่สังกัด
          </label>
          <input 
            type="text" 
            id="department" 
            name="department" 
            value="<?= htmlspecialchars((string)($user->department ?? '')) ?>"
            placeholder="เช่น กลุ่มสาระการเรียนรู้วิทยาศาสตร์และเทคโนโลยี"
            class="w-full px-4 py-2.5 rounded-2xl bg-slate-100 focus:bg-white text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all"
          >
        </div>
      </div>

      <!-- Password Change Section -->
      <div class="pt-6 border-t border-slate-100 space-y-4">
        <div>
          <h2 class="text-sm font-black text-slate-900 flex items-center gap-2">
            <i class="fa-solid fa-key text-violet-600" aria-hidden="true"></i>
            <span>เปลี่ยนรหัสผ่าน (เว้นว่างไว้หากไม่ต้องการเปลี่ยน)</span>
          </h2>
          <p class="text-xs text-slate-400 mt-0.5">ระบุรหัสผ่านปัจจุบันและรหัสผ่านใหม่ที่ต้องการตั้งค่า (อย่างน้อย 6 ตัวอักษร)</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-5 rounded-2xl bg-slate-50">
          <div>
            <label for="current_password" class="block text-xs font-bold text-slate-700 mb-1">
              รหัสผ่านปัจจุบัน
            </label>
            <input 
              type="password" 
              id="current_password" 
              name="current_password" 
              placeholder="ระบุรหัสผ่านเดิม"
              autocomplete="current-password"
              class="w-full px-4 py-2.5 rounded-2xl bg-white text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all font-mono"
            >
          </div>

          <div>
            <label for="new_password" class="block text-xs font-bold text-slate-700 mb-1">
              รหัสผ่านใหม่
            </label>
            <input 
              type="password" 
              id="new_password" 
              name="new_password" 
              placeholder="รหัสผ่านใหม่อย่างน้อย 6 ตัวอักษร"
              autocomplete="new-password"
              class="w-full px-4 py-2.5 rounded-2xl bg-white text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all font-mono"
            >
          </div>
        </div>
      </div>

      <!-- Submit Button -->
      <div class="flex items-center justify-end gap-3 pt-4">
        <a href="<?= Auth::defaultRedirectUrl() ?>" class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all">
          ยกเลิก
        </a>
        <button 
          type="submit" 
          class="px-6 py-2.5 rounded-2xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold transition-all active:scale-95 flex items-center gap-2"
        >
          <i class="fa-solid fa-floppy-disk text-xs" aria-hidden="true"></i>
          <span>บันทึกการเปลี่ยนแปลง</span>
        </button>
      </div>
    </form>
  </div>

</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
