<?php
require_once __DIR__ . '/../layout/header.php';
use App\Core\Auth;
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

  <!-- Header -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <div class="flex items-center gap-2 text-xs font-bold text-violet-600 uppercase tracking-wider mb-1">
        <i class="fa-solid fa-boxes-stacked" aria-hidden="true"></i>
        <span>ระบบบริหารคลังพัสดุและอะไหล่</span>
      </div>
      <h1 class="text-2xl font-black text-slate-900 tracking-tight font-display">คลังอะไหล่และพัสดุซ่อมบำรุง</h1>
      <p class="text-xs text-slate-500 mt-0.5">จัดการสต็อกอะไหล่ ตรวจสอบจุดสั่งซื้อขั้นต่ำ และบันทึกการเบิกใช้อัตโนมัติเมื่อปิดงานซ่อม</p>
    </div>

    <?php if (Auth::isAdmin() || Auth::isTechnician()): ?>
      <button 
        type="button" 
        onclick="openAddPartModal()"
        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold transition-all active:scale-95"
      >
        <i class="fa-solid fa-plus text-xs" aria-hidden="true"></i>
        <span>เพิ่มอะไหล่ใหม่</span>
      </button>
    <?php endif; ?>
  </div>

  <!-- Metric Counters -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <!-- Total items -->
    <div class="bg-white rounded-3xl p-6">
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">รายการอะไหล่ทั้งหมด</span>
        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center text-lg">
          <i class="fa-solid fa-cubes" aria-hidden="true"></i>
        </div>
      </div>
      <div class="mt-4 flex items-baseline gap-2">
        <span class="text-3xl font-black text-slate-900 font-display tabular-nums"><?= count($parts) ?></span>
        <span class="text-xs text-slate-400 font-medium">รายการ</span>
      </div>
      <p class="text-[11px] text-slate-400 mt-1">พร้อมใช้งานในระบบซ่อมบำรุง</p>
    </div>

    <!-- Low stock alerts -->
    <div class="bg-white rounded-3xl p-6">
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">อะไหล่ใกล้หมดสต็อก</span>
        <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg">
          <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
        </div>
      </div>
      <div class="mt-4 flex items-baseline gap-2">
        <span class="text-3xl font-black text-rose-600 font-display tabular-nums"><?= $lowStockCount ?></span>
        <span class="text-xs text-slate-400 font-medium">รายการ</span>
      </div>
      <p class="text-[11px] text-slate-400 mt-1">จำนวนคงเหลือน้อยกว่าเกณฑ์ขั้นต่ำ</p>
    </div>

    <!-- Total inventory valuation -->
    <div class="bg-white rounded-3xl p-6">
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">มูลค่าพัสดุในคลังรวม</span>
        <div class="w-12 h-12 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center text-lg">
          <i class="fa-solid fa-coins" aria-hidden="true"></i>
        </div>
      </div>
      <div class="mt-4 flex items-baseline gap-2">
        <span class="text-3xl font-black text-violet-600 font-display tabular-nums"><?= number_format($totalInventoryValue, 2) ?></span>
        <span class="text-xs text-slate-400 font-medium">บาท</span>
      </div>
      <p class="text-[11px] text-slate-400 mt-1">คำนวณตามราคาต้นทุนปัจจุบัน</p>
    </div>
  </div>

  <!-- Search Filter Card -->
  <div class="bg-white rounded-3xl p-5 sm:p-6">
    <form action="index.php" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
      <input type="hidden" name="page" value="spare-parts">

      <div class="flex-1 w-full">
        <input 
          type="search" 
          name="search" 
          class="w-full px-4 py-2.5 text-xs rounded-2xl bg-slate-100 focus:bg-white text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all" 
          placeholder="ค้นหารหัสอะไหล่ หรือชื่ออะไหล่" 
          value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
        >
      </div>

      <div class="flex items-center gap-2 w-full sm:w-auto">
        <label class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 cursor-pointer text-xs font-bold text-slate-700 select-none">
          <input type="checkbox" name="low_stock" value="1" <?= !empty($filters['low_stock']) ? 'checked' : '' ?> class="rounded text-violet-600 focus:ring-0">
          <span>เฉพาะที่ใกล้หมด</span>
        </label>

        <button type="submit" class="px-5 py-2.5 rounded-2xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold transition-all active:scale-95">
          <span>ค้นหา</span>
        </button>

        <a href="index.php?page=spare-parts" class="p-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 text-xs font-bold transition-all">
          <i class="fa-solid fa-rotate-left"></i>
        </a>
      </div>
    </form>
  </div>

  <!-- Parts Table Card -->
  <div class="bg-white rounded-3xl overflow-hidden">
    <div class="px-6 py-4 flex items-center justify-between bg-slate-50/60">
      <div class="flex items-center gap-2">
        <span class="text-xs font-black text-slate-800">รายการพัสดุและอะไหล่</span>
        <span class="px-2 py-0.5 rounded-full bg-violet-100 text-violet-700 text-[11px] font-bold tabular-nums"><?= count($parts) ?> รายการ</span>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-100 text-xs">
        <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-left">
          <tr>
            <th class="px-5 py-3.5">รหัสอะไหล่</th>
            <th class="px-5 py-3.5">ชื่อพัสดุ อะไหล่</th>
            <th class="px-5 py-3.5">หมวดหมู่</th>
            <th class="px-5 py-3.5">ราคาต่อหน่วย</th>
            <th class="px-5 py-3.5">คงเหลือในคลัง</th>
            <th class="px-5 py-3.5">เกณฑ์ขั้นต่ำ</th>
            <th class="px-5 py-3.5 text-right">การจัดการ</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700">
          <?php if (empty($parts)): ?>
            <tr>
              <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                <i class="fa-solid fa-box-archive text-3xl mb-2 text-slate-300" aria-hidden="true"></i>
                <p class="font-bold">ไม่พบข้อมูลอะไหล่ในคลัง</p>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($parts as $p): ?>
              <?php $isLow = (int)$p['stock_quantity'] <= (int)$p['min_quantity']; ?>
              <tr class="hover:bg-slate-50/70 transition-colors <?= $isLow ? 'bg-rose-50/20' : '' ?>">
                <td class="px-5 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-xl bg-slate-100 text-slate-800 font-mono text-[11px] font-bold tabular-nums">
                    <?= htmlspecialchars($p['part_code']) ?>
                  </span>
                </td>
                <td class="px-5 py-4 max-w-[240px]">
                  <strong class="text-slate-900 block truncate"><?= htmlspecialchars($p['name']) ?></strong>
                </td>
                <td class="px-5 py-4 whitespace-nowrap text-slate-600">
                  <?= htmlspecialchars($p['equipment_type_name'] ?? 'ทั่วไป') ?>
                </td>
                <td class="px-5 py-4 whitespace-nowrap font-bold text-slate-800 tabular-nums">
                  <?= number_format((float)$p['unit_cost'], 2) ?> บาท / <?= htmlspecialchars($p['unit']) ?>
                </td>
                <td class="px-5 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-black tabular-nums <?= $isLow ? 'bg-rose-100 text-rose-800' : 'bg-emerald-50 text-emerald-800' ?>">
                    <?php if ($isLow): ?><i class="fa-solid fa-triangle-exclamation text-[10px]"></i><?php endif; ?>
                    <?= (int)$p['stock_quantity'] ?> <?= htmlspecialchars($p['unit']) ?>
                  </span>
                </td>
                <td class="px-5 py-4 whitespace-nowrap text-slate-400 tabular-nums">
                  เตือนเมื่อต่ำกว่า <?= (int)$p['min_quantity'] ?> <?= htmlspecialchars($p['unit']) ?>
                </td>
                <td class="px-5 py-4 whitespace-nowrap text-right">
                  <?php if (Auth::isAdmin() || Auth::isTechnician()): ?>
                    <button 
                      type="button" 
                      onclick="openRestockModal(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['name'])) ?>', '<?= htmlspecialchars($p['unit']) ?>')"
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-violet-100 text-slate-700 hover:text-violet-700 font-bold text-xs transition-all"
                    >
                      <i class="fa-solid fa-plus text-[10px]"></i>
                      <span>รับเข้าสต็อก</span>
                    </button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- Modal: Add New Part -->
<div id="addPartModal" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
  <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closeAddPartModal()"></div>
  <div class="flex min-h-full items-center justify-center p-4 sm:p-0">
    <div class="relative transform overflow-hidden rounded-3xl bg-white text-left transition-all sm:my-8 sm:w-full sm:max-w-lg">
      <div class="px-6 py-5 bg-slate-50/60 flex items-center justify-between">
        <h2 class="flex items-center gap-2 font-black text-sm text-slate-900">
          <i class="fa-solid fa-boxes-stacked text-violet-600"></i>
          <span>เพิ่มอะไหล่ใหม่เข้าสู่คลัง</span>
        </h2>
        <button type="button" onclick="closeAddPartModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <form action="index.php?page=spare-parts-store" method="POST" class="p-6 space-y-4 text-xs">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-bold text-slate-700 mb-1.5">รหัสอะไหล่ <span class="text-rose-500">*</span></label>
            <input type="text" name="part_code" required class="w-full px-4 py-2.5 rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20 font-mono" placeholder="เช่น SP-ELE-008">
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1.5">หมวดหมู่</label>
            <select name="equipment_type_id" class="w-full px-4 py-2.5 rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
              <option value="">ทั่วไป</option>
              <?php foreach ($equipmentTypes as $eq): ?>
                <option value="<?= $eq->id ?>"><?= htmlspecialchars($eq->name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div>
          <label class="block font-bold text-slate-700 mb-1.5">ชื่อพัสดุ อะไหล่ <span class="text-rose-500">*</span></label>
          <input type="text" name="name" required class="w-full px-4 py-2.5 rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20" placeholder="เช่น หลอดไฟ LED T8 18W">
        </div>

        <div class="grid grid-cols-3 gap-3">
          <div>
            <label class="block font-bold text-slate-700 mb-1.5">หน่วยนับ</label>
            <input type="text" name="unit" value="ชิ้น" required class="w-full px-4 py-2.5 rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20" placeholder="ชิ้น หลอด ชุด">
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1.5">ราคาต่อหน่วย บาท</label>
            <input type="number" step="0.01" name="unit_cost" value="0.00" required class="w-full px-4 py-2.5 rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20 font-mono">
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1.5">จำนวนเริ่มต้น</label>
            <input type="number" name="stock_quantity" value="10" required class="w-full px-4 py-2.5 rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20 font-mono">
          </div>
        </div>

        <div>
          <label class="block font-bold text-slate-700 mb-1.5">จุดเตือนสั่งซื้อขั้นต่ำ</label>
          <input type="number" name="min_quantity" value="5" required class="w-full px-4 py-2.5 rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20 font-mono">
          <p class="text-[10px] text-slate-400 mt-1">ระบบจะแจ้งเตือนเมื่อจำนวนคงเหลือเท่ากับหรือน้อยกว่าค่านี้</p>
        </div>

        <div class="flex items-center justify-end gap-2 pt-3">
          <button type="button" onclick="closeAddPartModal()" class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold">ยกเลิก</button>
          <button type="submit" class="px-6 py-2.5 rounded-2xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold active:scale-95">บันทึกอะไหล่</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Restock Part -->
<div id="restockModal" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
  <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closeRestockModal()"></div>
  <div class="flex min-h-full items-center justify-center p-4 sm:p-0">
    <div class="relative transform overflow-hidden rounded-3xl bg-white text-left transition-all sm:my-8 sm:w-full sm:max-w-md">
      <div class="px-6 py-5 bg-slate-50/60 flex items-center justify-between">
        <h2 class="flex items-center gap-2 font-black text-sm text-slate-900">
          <i class="fa-solid fa-arrow-down text-emerald-600"></i>
          <span>รับเข้าสต็อกอะไหล่</span>
        </h2>
        <button type="button" onclick="closeRestockModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <form action="index.php?page=spare-parts-restock" method="POST" class="p-6 space-y-4 text-xs">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">
        <input type="hidden" name="part_id" id="restock_part_id" value="">

        <div>
          <span class="text-[10px] text-slate-400 uppercase tracking-wider block mb-1">รายการที่รับเข้า</span>
          <p id="restock_part_name" class="font-bold text-sm text-slate-800"></p>
        </div>

        <div>
          <label class="block font-bold text-slate-700 mb-1.5">จำนวนที่รับเข้าเพิ่ม <span class="text-rose-500">*</span></label>
          <div class="flex items-center gap-2">
            <input type="number" min="1" name="add_quantity" value="10" required class="flex-1 px-4 py-2.5 rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20 font-mono text-sm font-bold">
            <span id="restock_unit_label" class="text-xs text-slate-500 font-bold px-2"></span>
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-3">
          <button type="button" onclick="closeRestockModal()" class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold">ยกเลิก</button>
          <button type="submit" class="px-6 py-2.5 rounded-2xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold active:scale-95">ยืนยันรับเข้าสต็อก</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openAddPartModal() {
  const m = document.getElementById('addPartModal');
  if (m) { m.classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
}
function closeAddPartModal() {
  const m = document.getElementById('addPartModal');
  if (m) { m.classList.add('hidden'); document.body.style.overflow = 'auto'; }
}
function openRestockModal(id, name, unit) {
  document.getElementById('restock_part_id').value = id;
  document.getElementById('restock_part_name').textContent = name;
  document.getElementById('restock_unit_label').textContent = unit;
  const m = document.getElementById('restockModal');
  if (m) { m.classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
}
function closeRestockModal() {
  const m = document.getElementById('restockModal');
  if (m) { m.classList.add('hidden'); document.body.style.overflow = 'auto'; }
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
