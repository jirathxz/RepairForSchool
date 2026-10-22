<?php
require_once __DIR__ . '/../layout/header.php';
use App\Core\Auth;
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

  <!-- Header -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <div class="flex items-center gap-2 text-xs font-bold text-violet-600 uppercase tracking-wider mb-1">
        <i class="fa-solid fa-calendar-check" aria-hidden="true"></i>
        <span>การบำรุงรักษาเชิงป้องกัน</span>
      </div>
      <h1 class="text-2xl font-black text-slate-900 tracking-tight font-display">แผนบำรุงรักษาเชิงป้องกันตามรอบ</h1>
      <p class="text-xs text-slate-500 mt-0.5">กำหนดรอบการตรวจเช็ค ล้างแอร์ ตรวจระบบไฟฟ้า และสร้างใบงานซ่อมบำรุงอัตโนมัติก่อนอุปกรณ์ชำรุด</p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <?php if ($dueCount > 0): ?>
        <form action="index.php?page=pm-schedules-trigger-due" method="POST" class="inline" onsubmit="return confirm('ยืนยันการเปิดใบงานซ่อมบำรุงเชิงป้องกันสำหรับทุกแผนงานที่ถึงกำหนดในขณะนี้ (<?= $dueCount ?> รายการ)?');">
          <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">
          <button 
            type="submit" 
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all active:scale-95"
            title="เปิดใบงานซ่อมบำรุงทั้งหมดที่ถึงกำหนดทันที"
          >
            <i class="fa-solid fa-bolt text-xs" aria-hidden="true"></i>
            <span>เปิดใบงานที่ถึงกำหนดทั้งหมด (<?= $dueCount ?>)</span>
          </button>
        </form>
      <?php endif; ?>

      <?php if (Auth::isAdmin()): ?>
        <button 
          type="button" 
          onclick="openAddPMModal()"
          class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold transition-all active:scale-95"
        >
          <i class="fa-solid fa-plus text-xs" aria-hidden="true"></i>
          <span>สร้างแผนงานบำรุงรักษา</span>
        </button>
      <?php endif; ?>
    </div>
  </div>

  <!-- Summary Tiles -->
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="bg-white rounded-3xl p-6 flex items-center justify-between">
      <div>
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">แผนงานที่เปิดใช้งาน</span>
        <div class="text-3xl font-black text-slate-900 mt-2 font-display tabular-nums"><?= count($schedules) ?> <span class="text-xs font-normal text-slate-400">แผนงาน</span></div>
      </div>
      <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center text-lg">
        <i class="fa-solid fa-list-check" aria-hidden="true"></i>
      </div>
    </div>

    <div class="bg-white rounded-3xl p-6 flex items-center justify-between">
      <div>
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">แผนที่ถึงกำหนดหรือเลยกำหนด</span>
        <div class="text-3xl font-black text-violet-600 mt-2 font-display tabular-nums"><?= $dueCount ?> <span class="text-xs font-normal text-slate-400">รายการ</span></div>
      </div>
      <div class="w-12 h-12 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center text-lg">
        <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
      </div>
    </div>
  </div>

  <!-- PM Schedules Card -->
  <div class="bg-white rounded-3xl overflow-hidden">
    <div class="px-6 py-4 flex items-center justify-between bg-slate-50/60">
      <div class="flex items-center gap-2">
        <span class="text-xs font-black text-slate-800">ตารางรอบการบำรุงรักษา</span>
        <span class="px-2 py-0.5 rounded-full bg-violet-100 text-violet-700 text-[11px] font-bold tabular-nums"><?= count($schedules) ?> รายการ</span>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-100 text-xs">
        <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-left">
          <tr>
            <th class="px-5 py-3.5">หัวข้อแผนบำรุงรักษา</th>
            <th class="px-5 py-3.5">ประเภท สถานที่</th>
            <th class="px-5 py-3.5">ครุภัณฑ์เป้าหมาย</th>
            <th class="px-5 py-3.5">ความถี่รอบ</th>
            <th class="px-5 py-3.5">รอบถัดไป</th>
            <th class="px-5 py-3.5">ช่างผู้รับผิดชอบ</th>
            <th class="px-5 py-3.5 text-right">การเปิดใบงาน</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700">
          <?php if (empty($schedules)): ?>
            <tr>
              <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                <i class="fa-solid fa-calendar-xmark text-3xl mb-2 text-slate-300" aria-hidden="true"></i>
                <p class="font-bold">ยังไม่มีแผนบำรุงรักษาในระบบ</p>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($schedules as $s): ?>
              <?php 
                $today = date('Y-m-d');
                $isDue = ($s['next_due_date'] <= $today);
                $daysRemaining = (int)ceil((strtotime($s['next_due_date']) - strtotime($today)) / 86400);
              ?>
              <tr class="hover:bg-slate-50/70 transition-colors <?= $isDue ? 'bg-violet-50/30' : '' ?>">
                <td class="px-5 py-4 max-w-[240px]">
                  <strong class="text-slate-900 block truncate"><?= htmlspecialchars($s['title']) ?></strong>
                  <span class="text-[11px] text-slate-400 block truncate"><?= htmlspecialchars($s['description'] ?? '') ?></span>
                </td>
                <td class="px-5 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-100 text-slate-700 text-[11px] font-medium block w-fit mb-1">
                    <i class="<?= htmlspecialchars($s['equipment_icon'] ?? 'fa-solid fa-wrench') ?> text-slate-400" aria-hidden="true"></i>
                    <span><?= htmlspecialchars($s['equipment_type_name']) ?></span>
                  </span>
                  <span class="text-slate-500 text-[11px]"><?= htmlspecialchars($s['building']) ?> · <?= htmlspecialchars($s['room'] ?? 'ทุกห้อง') ?></span>
                </td>
                <td class="px-5 py-4 whitespace-nowrap">
                  <?php if (!empty($s['asset_name'])): ?>
                    <span class="font-bold text-slate-800 block truncate max-w-[160px]"><?= htmlspecialchars($s['asset_name']) ?></span>
                    <span class="font-mono text-[10px] text-slate-400"><?= htmlspecialchars($s['asset_code'] ?? '') ?></span>
                  <?php else: ?>
                    <span class="text-slate-400">ทั้งอาคารหรือบริเวณ</span>
                  <?php endif; ?>
                </td>
                <td class="px-5 py-4 whitespace-nowrap font-bold text-slate-700 tabular-nums">
                  ทุก <?= (int)$s['frequency_days'] ?> วัน
                </td>
                <td class="px-5 py-4 whitespace-nowrap tabular-nums">
                  <div class="font-bold text-xs <?= $isDue ? 'text-violet-600' : 'text-slate-800' ?>">
                    <?= date('d/m/Y', strtotime($s['next_due_date'])) ?>
                  </div>
                  <?php if ($isDue): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-violet-100 text-violet-700 mt-0.5">ถึงรอบแล้ว</span>
                  <?php else: ?>
                    <span class="text-[10px] text-slate-400">อีก <?= $daysRemaining ?> วัน</span>
                  <?php endif; ?>
                </td>
                <td class="px-5 py-4 whitespace-nowrap text-slate-600">
                  <?= htmlspecialchars($s['technician_name'] ?? 'ช่างเวรประจำวัน') ?>
                </td>
                <td class="px-5 py-4 whitespace-nowrap text-right">
                  <form action="index.php?page=pm-schedules-trigger" method="POST" class="inline" onsubmit="return confirm('ยืนยันการเปิดใบงานซ่อมบำรุงตามรอบนี้?');">
                    <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">
                    <input type="hidden" name="pm_id" value="<?= $s['id'] ?>">
                    <button 
                      type="submit" 
                      class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white font-bold text-xs transition-all active:scale-95"
                      title="สร้างใบงานซ่อมบำรุงทันที"
                    >
                      <i class="fa-solid fa-play text-[10px]" aria-hidden="true"></i>
                      <span>เปิดใบงาน</span>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- Modal: Add New PM Schedule -->
<div id="addPMModal" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
  <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closeAddPMModal()"></div>
  <div class="flex min-h-full items-center justify-center p-4 sm:p-0">
    <div class="relative transform overflow-hidden rounded-3xl bg-white text-left transition-all sm:my-8 sm:w-full sm:max-w-xl">
      <div class="px-6 py-5 bg-slate-50/60 flex items-center justify-between">
        <h2 class="flex items-center gap-2 font-black text-sm text-slate-900">
          <i class="fa-solid fa-calendar-plus text-violet-600"></i>
          <span>สร้างแผนงานบำรุงรักษาเชิงป้องกัน</span>
        </h2>
        <button type="button" onclick="closeAddPMModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <form action="index.php?page=pm-schedules-store" method="POST" class="p-6 space-y-4 text-xs">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">

        <div>
          <label class="block font-bold text-slate-700 mb-1.5">หัวข้อแผนบำรุงรักษา <span class="text-rose-500">*</span></label>
          <input type="text" name="title" required class="w-full px-4 py-2.5 rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20" placeholder="เช่น ล้างฟิลเตอร์แอร์ประจำไตรมาส อาคาร 1">
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-bold text-slate-700 mb-1.5">หมวดหมู่งาน <span class="text-rose-500">*</span></label>
            <select name="equipment_type_id" required class="w-full px-4 py-2.5 rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
              <?php foreach ($equipmentTypes as $eq): ?>
                <option value="<?= $eq->id ?>"><?= htmlspecialchars($eq->name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1.5">ครุภัณฑ์เป้าหมาย</label>
            <select name="asset_id" class="w-full px-4 py-2.5 rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
              <option value="">ไม่ระบุเฉพาะเจาะจง</option>
              <?php foreach ($assets as $a): ?>
                <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['asset_code'] . ' - ' . $a['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-bold text-slate-700 mb-1.5">อาคารสถานที่ <span class="text-rose-500">*</span></label>
            <input type="text" name="building" required list="pmBuildingList" class="w-full px-4 py-2.5 rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20" placeholder="อาคาร 1 เฉลิมพระเกียรติ">
            <datalist id="pmBuildingList">
              <?php foreach ($buildings as $b): ?><option value="<?= htmlspecialchars($b) ?>"><?php endforeach; ?>
            </datalist>
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1.5">ห้อง หรือบริเวณ</label>
            <input type="text" name="room" class="w-full px-4 py-2.5 rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20" placeholder="ห้องพักครู 101 หรือ ทุกห้อง">
          </div>
        </div>

        <div class="grid grid-cols-3 gap-3">
          <div>
            <label class="block font-bold text-slate-700 mb-1.5">ความถี่รอบ วัน</label>
            <input type="number" name="frequency_days" value="90" required class="w-full px-4 py-2.5 rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20 font-mono">
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1.5">วันครบกำหนดรอบแรก <span class="text-rose-500">*</span></label>
            <input type="date" name="next_due_date" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required class="w-full px-4 py-2.5 rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20 font-mono">
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1.5">มอบหมายช่าง</label>
            <select name="assigned_technician_id" class="w-full px-4 py-2.5 rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
              <option value="">ไม่เจาะจงช่าง</option>
              <?php foreach ($technicians as $t): ?>
                <option value="<?= $t->id ?>"><?= htmlspecialchars($t->full_name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div>
          <label class="block font-bold text-slate-700 mb-1.5">รายละเอียดและข้อกำหนดการตรวจเช็ค</label>
          <textarea name="description" rows="3" class="w-full px-4 py-3 rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20" placeholder="เช่น ตรวจสอบแรงดันน้ำยาแอร์ ล้างแผ่นฟิลเตอร์กรองฝุ่น และเป่าทำความสะอาดคอยล์ร้อนภายนอก"></textarea>
        </div>

        <div class="flex items-center justify-end gap-2 pt-3">
          <button type="button" onclick="closeAddPMModal()" class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold">ยกเลิก</button>
          <button type="submit" class="px-6 py-2.5 rounded-2xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold active:scale-95">บันทึกแผนงาน</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openAddPMModal() {
  const m = document.getElementById('addPMModal');
  if (m) { m.classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
}
function closeAddPMModal() {
  const m = document.getElementById('addPMModal');
  if (m) { m.classList.add('hidden'); document.body.style.overflow = 'auto'; }
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
