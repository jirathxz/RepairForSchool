<?php
require_once __DIR__ . '/../layout/header.php';
use App\Core\Auth;

$adminCount = 0;
$teacherCount = 0;
$techCount = 0;
foreach ($users as $u) {
    if ($u->role === 'admin') $adminCount++;
    elseif ($u->role === 'teacher') $teacherCount++;
    elseif ($u->role === 'technician') $techCount++;
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

  <!-- Header -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <div class="flex items-center gap-2 text-xs font-bold text-violet-600 uppercase tracking-wider mb-1">
        <i class="fa-solid fa-users-gear" aria-hidden="true"></i>
        <span>การบริหารจัดการระบบ</span>
      </div>
      <h1 class="text-2xl font-black text-slate-900 tracking-tight font-display">จัดการบัญชีผู้ใช้งานระบบ</h1>
      <p class="text-xs text-slate-500 mt-0.5">เพิ่ม แก้ไข และกำหนดสิทธิ์การเข้าใช้งานระหว่างผู้ดูแลระบบ ครู บุคลากร และช่างซ่อมบำรุง</p>
    </div>
    <button 
      type="button" 
      onclick="openUserModal()"
      class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-violet-600/30 active:scale-95"
    >
      <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
      <span>เพิ่มผู้ใช้งานใหม่</span>
    </button>
  </div>

  <!-- Role Summary Tiles -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <!-- Admin Tile -->
    <div class="bg-white rounded-3xl p-5 flex items-center justify-between">
      <div>
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">ผู้ดูแลระบบ</span>
        <div class="text-3xl font-black text-slate-900 mt-2 font-display tabular-nums"><?= $adminCount ?> <span class="text-xs font-normal text-slate-400">คน</span></div>
      </div>
      <div class="w-12 h-12 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center text-lg">
        <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
      </div>
    </div>

    <!-- Teacher Tile -->
    <div class="bg-white rounded-3xl p-5 flex items-center justify-between">
      <div>
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">ครู และบุคลากร</span>
        <div class="text-3xl font-black text-slate-900 mt-2 font-display tabular-nums"><?= $teacherCount ?> <span class="text-xs font-normal text-slate-400">คน</span></div>
      </div>
      <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center text-lg">
        <i class="fa-solid fa-chalkboard-user" aria-hidden="true"></i>
      </div>
    </div>

    <!-- Technician Tile -->
    <div class="bg-white rounded-3xl p-5 flex items-center justify-between">
      <div>
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">ช่างซ่อมบำรุง</span>
        <div class="text-3xl font-black text-slate-900 mt-2 font-display tabular-nums"><?= $techCount ?> <span class="text-xs font-normal text-slate-400">คน</span></div>
      </div>
      <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center text-lg">
        <i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i>
      </div>
    </div>
  </div>

  <!-- Users Table Card -->
  <div class="bg-white rounded-3xl overflow-hidden">
    <div class="px-6 py-4 flex items-center justify-between bg-slate-50/60">
      <div class="flex items-center gap-2">
        <span class="text-xs font-black text-slate-800">รายชื่อผู้ใช้งานทั้งหมด</span>
        <span class="px-2 py-0.5 rounded-full bg-violet-100 text-violet-700 text-[11px] font-bold tabular-nums"><?= count($users) ?> บัญชี</span>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-100">
        <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-left">
          <tr>
            <th class="px-5 py-3.5">ชื่อ-นามสกุล</th>
            <th class="px-5 py-3.5">ชื่อผู้ใช้</th>
            <th class="px-5 py-3.5">อีเมล</th>
            <th class="px-5 py-3.5">บทบาท สิทธิ์</th>
            <th class="px-5 py-3.5">ฝ่าย กลุ่มสาระ</th>
            <th class="px-5 py-3.5">เบอร์โทร</th>
            <th class="px-5 py-3.5">สถานะ</th>
            <th class="px-5 py-3.5 text-right">การจัดการ</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-xs">
          <?php foreach ($users as $u): ?>
            <tr class="hover:bg-slate-50/70 transition-colors">
              <td class="px-5 py-4 whitespace-nowrap">
                <strong class="text-slate-900 block"><?= htmlspecialchars($u->full_name) ?></strong>
                <span class="text-[10px] text-slate-400">สร้างเมื่อ <?= date('d/m/Y', strtotime($u->created_at)) ?></span>
              </td>
              <td class="px-5 py-4 whitespace-nowrap font-mono text-violet-600 font-bold">
                <?= htmlspecialchars($u->username) ?>
              </td>
              <td class="px-5 py-4 whitespace-nowrap text-slate-600">
                <?= htmlspecialchars($u->email) ?>
              </td>
              <td class="px-5 py-4 whitespace-nowrap">
                <?= Auth::getRoleBadge($u->role) ?>
              </td>
              <td class="px-5 py-4 whitespace-nowrap text-slate-600">
                <?= htmlspecialchars($u->department ?? '-') ?>
              </td>
              <td class="px-5 py-4 whitespace-nowrap text-slate-600">
                <?= htmlspecialchars($u->phone ?? '-') ?>
              </td>
              <td class="px-5 py-4 whitespace-nowrap">
                <?php if ((int)$u->is_active === 1): ?>
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[11px] font-bold bg-emerald-50 text-emerald-700">
                    <i class="fa-solid fa-circle-check text-[10px]" aria-hidden="true"></i>
                    <span>ใช้งานปกติ</span>
                  </span>
                <?php else: ?>
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[11px] font-bold bg-rose-50 text-rose-700">
                    <i class="fa-solid fa-circle-xmark text-[10px]" aria-hidden="true"></i>
                    <span>ถูกระงับ</span>
                  </span>
                <?php endif; ?>
              </td>
              <td class="px-5 py-4 whitespace-nowrap text-right">
                <?php if ((int)$u->id !== (int)Auth::id()): ?>
                  <form action="index.php?page=users-toggle" method="POST" class="inline" onsubmit="return confirm('ยืนยันการเปลี่ยนสถานะผู้ใช้งานรายนี้?');">
                    <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">
                    <input type="hidden" name="user_id" value="<?= $u->id ?>">
                    <?php if ((int)$u->is_active === 1): ?>
                      <button 
                        type="submit" 
                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition-all active:scale-95" 
                        title="ระงับการใช้งาน"
                      >
                        <i class="fa-solid fa-user-xmark text-[10px]" aria-hidden="true"></i>
                        <span>ระงับ</span>
                      </button>
                    <?php else: ?>
                      <button 
                        type="submit" 
                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold transition-all active:scale-95" 
                        title="เปิดการใช้งาน"
                      >
                        <i class="fa-solid fa-user-check text-[10px]" aria-hidden="true"></i>
                        <span>เปิดใช้</span>
                      </button>
                    <?php endif; ?>
                  </form>
                <?php else: ?>
                  <span class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-400 text-[10px] font-semibold">
                    บัญชีปัจจุบัน
                  </span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- Add User Modal with ARIA Dialog compliance -->
<div id="addUserModal" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="modal-title">
  <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closeUserModal()"></div>

  <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
    <div class="relative transform overflow-hidden rounded-3xl bg-white text-left transition-all sm:my-8 sm:w-full sm:max-w-lg">
      
      <!-- Modal Header -->
      <div class="px-6 py-5 bg-slate-50/60 flex items-center justify-between">
        <h2 id="modal-title" class="flex items-center gap-2 font-black text-sm text-slate-900">
          <i class="fa-solid fa-user-plus text-violet-600" aria-hidden="true"></i>
          <span>เพิ่มบัญชีผู้ใช้งานใหม่</span>
        </h2>
        <button 
          type="button" 
          onclick="closeUserModal()" 
          aria-label="ปิดหน้าต่างเพิ่มผู้ใช้งาน"
          class="text-slate-400 hover:text-slate-600 text-sm p-1.5 rounded-xl hover:bg-slate-100 focus:outline-none transition-colors"
        >
          <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
      </div>

      <!-- Modal Form -->
      <form action="index.php?page=users-store" method="POST" class="p-6 space-y-4 text-xs">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">
        <div>
          <label for="modal_full_name" class="block font-bold text-slate-700 mb-1.5">ชื่อ-นามสกุล <span class="text-rose-500">*</span></label>
          <input 
            type="text" 
            id="modal_full_name"
            name="full_name" 
            class="w-full px-4 py-2.5 text-xs rounded-2xl bg-slate-100 focus:bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all" 
            placeholder="ครูสมศรี สดใส" 
            required
          >
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label for="modal_username" class="block font-bold text-slate-700 mb-1.5">ชื่อผู้ใช้ <span class="text-rose-500">*</span></label>
            <input 
              type="text" 
              id="modal_username"
              name="username" 
              autocomplete="off"
              spellcheck="false"
              class="w-full px-4 py-2.5 text-xs rounded-2xl bg-slate-100 focus:bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all font-mono" 
              placeholder="somsri01" 
              required
            >
          </div>
          <div>
            <label for="modal_password" class="block font-bold text-slate-700 mb-1.5">รหัสผ่านเริ่มต้น <span class="text-rose-500">*</span></label>
            <input 
              type="text" 
              id="modal_password"
              name="password" 
              class="w-full px-4 py-2.5 text-xs rounded-2xl bg-slate-100 focus:bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all font-mono" 
              value="password123" 
              required
            >
          </div>
        </div>

        <div>
          <label for="modal_email" class="block font-bold text-slate-700 mb-1.5">อีเมล <span class="text-rose-500">*</span></label>
          <input 
            type="email" 
            id="modal_email"
            name="email" 
            class="w-full px-4 py-2.5 text-xs rounded-2xl bg-slate-100 focus:bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all" 
            placeholder="somsri@school.ac.th" 
            required
          >
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label for="modal_role" class="block font-bold text-slate-700 mb-1.5">บทบาทในระบบ <span class="text-rose-500">*</span></label>
            <select 
              id="modal_role"
              name="role" 
              class="w-full px-4 py-2.5 text-xs rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all" 
              required
            >
              <option value="teacher" selected>ครู และบุคลากร</option>
              <option value="technician">ช่างซ่อมบำรุง</option>
              <option value="admin">ผู้ดูแลระบบ</option>
            </select>
          </div>
          <div>
            <label for="modal_phone" class="block font-bold text-slate-700 mb-1.5">เบอร์โทรศัพท์</label>
            <input 
              type="tel" 
              id="modal_phone"
              name="phone" 
              inputmode="tel"
              class="w-full px-4 py-2.5 text-xs rounded-2xl bg-slate-100 focus:bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all font-mono" 
              placeholder="081-234-5678"
            >
          </div>
        </div>

        <div>
          <label for="modal_department" class="block font-bold text-slate-700 mb-1.5">ฝ่ายงาน กลุ่มสาระการเรียนรู้</label>
          <input 
            type="text" 
            id="modal_department"
            name="department" 
            class="w-full px-4 py-2.5 text-xs rounded-2xl bg-slate-100 focus:bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all" 
            placeholder="กลุ่มสาระวิทยาศาสตร์ ฝ่ายอาคารสถานที่" 
          >
        </div>

        <div class="flex items-center justify-end gap-2 pt-4">
          <button 
            type="button" 
            onclick="closeUserModal()" 
            class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition-all"
          >
            ยกเลิก
          </button>
          <button 
            type="submit" 
            class="px-6 py-2.5 rounded-2xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold transition-all active:scale-95"
          >
            บันทึกผู้ใช้งาน
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openUserModal() {
  const modal = document.getElementById('addUserModal');
  if (modal) {
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  }
}

function closeUserModal() {
  const modal = document.getElementById('addUserModal');
  if (modal) {
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
  }
}
</script>

<?php
require_once __DIR__ . '/../layout/footer.php';
?>
