<?php
require_once __DIR__ . '/../layout/header.php';
use App\Core\Auth;
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

  <!-- Header -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <div class="flex items-center gap-2 text-xs font-bold text-violet-600 uppercase tracking-wider mb-1">
        <i class="fa-solid fa-qrcode" aria-hidden="true"></i>
        <span>ระบบทะเบียนครุภัณฑ์โรงเรียน</span>
      </div>
      <h1 class="text-2xl font-black text-slate-900 tracking-tight font-display">ทะเบียนครุภัณฑ์และรหัส QR Code</h1>
      <p class="text-xs text-slate-500 mt-0.5">บันทึกข้อมูลครุภัณฑ์ประจำห้อง พิมพ์สติกเกอร์ QR Code ติดอุปกรณ์ และสแกนแจ้งซ่อมได้ทันที</p>
    </div>
    
    <?php if (Auth::isAdmin()): ?>
      <button 
        type="button" 
        onclick="openAssetModal()"
        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold transition-all active:scale-95"
      >
        <i class="fa-solid fa-plus text-xs" aria-hidden="true"></i>
        <span>เพิ่มครุภัณฑ์ใหม่</span>
      </button>
    <?php endif; ?>
  </div>

  <!-- Search & Filter Card -->
  <div class="bg-white rounded-3xl p-5 sm:p-6">
    <form action="index.php" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-end">
      <input type="hidden" name="page" value="assets">

      <!-- Search Keyword (4 cols) -->
      <div class="sm:col-span-2 lg:col-span-4">
        <label for="filter_search" class="block text-xs font-bold text-slate-600 mb-1.5">
          <i class="fa-solid fa-magnifying-glass text-slate-400 mr-1" aria-hidden="true"></i>ค้นหาครุภัณฑ์
        </label>
        <input 
          id="filter_search"
          type="search" 
          name="search" 
          class="w-full px-4 py-2.5 text-xs rounded-2xl bg-slate-100 focus:bg-white text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all" 
          placeholder="รหัสครุภัณฑ์ หรือชื่อ หรือซีเรียล หรือห้อง" 
          value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
        >
      </div>

      <!-- Filter Equipment Type (3 cols) -->
      <div class="lg:col-span-3">
        <label for="filter_equipment_type" class="block text-xs font-bold text-slate-600 mb-1.5">ประเภทอุปกรณ์</label>
        <select id="filter_equipment_type" name="equipment_type_id" class="w-full px-4 py-2.5 text-xs rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all">
          <option value="">ทุกประเภท</option>
          <?php foreach ($equipmentTypes as $eq): ?>
            <option value="<?= $eq->id ?>" <?= ((int)($filters['equipment_type_id'] ?? 0) === (int)$eq->id) ? 'selected' : '' ?>>
              <?= htmlspecialchars($eq->name) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Filter Building (3 cols) -->
      <div class="lg:col-span-3">
        <label for="filter_building" class="block text-xs font-bold text-slate-600 mb-1.5">อาคารสถานที่</label>
        <select id="filter_building" name="building" class="w-full px-4 py-2.5 text-xs rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all">
          <option value="">ทุกอาคาร</option>
          <?php foreach ($buildings as $b): ?>
            <option value="<?= htmlspecialchars($b) ?>" <?= (($filters['building'] ?? '') === $b) ? 'selected' : '' ?>>
              <?= htmlspecialchars($b) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Buttons (2 cols) -->
      <div class="flex items-center gap-2 lg:col-span-2">
        <button 
          type="submit" 
          class="flex-1 min-h-[42px] py-2.5 px-3 rounded-2xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold transition-all text-center flex items-center justify-center gap-1.5 active:scale-95"
        >
          <i class="fa-solid fa-filter text-[10px]" aria-hidden="true"></i>
          <span>กรอง</span>
        </button>
        <a 
          href="index.php?page=assets" 
          class="min-w-[42px] min-h-[42px] p-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 text-xs font-bold transition-all flex items-center justify-center"
          title="ล้างค่าการกรอง"
        >
          <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
        </a>
      </div>
    </form>
  </div>

  <!-- Assets Table Card -->
  <div class="bg-white rounded-3xl overflow-hidden">
    <div class="px-6 py-4 flex items-center justify-between bg-slate-50/60">
      <div class="flex items-center gap-2">
        <span class="text-xs font-black text-slate-800">รายการครุภัณฑ์ทั้งหมด</span>
        <span class="px-2 py-0.5 rounded-full bg-violet-100 text-violet-700 text-[11px] font-bold tabular-nums"><?= count($assets) ?> รายการ</span>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-100 text-xs">
        <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-left">
          <tr>
            <th class="px-5 py-3.5">รหัสครุภัณฑ์</th>
            <th class="px-5 py-3.5">ชื่ออุปกรณ์ ยี่ห้อ รุ่น</th>
            <th class="px-5 py-3.5">ประเภท</th>
            <th class="px-5 py-3.5">สถานที่ติดตั้ง</th>
            <th class="px-5 py-3.5">สถานะ</th>
            <th class="px-5 py-3.5">ประวัติการซ่อม</th>
            <th class="px-5 py-3.5 text-right">สติกเกอร์ QR Code</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700">
          <?php if (empty($assets)): ?>
            <tr>
              <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                <i class="fa-solid fa-box-open text-3xl mb-2 text-slate-300" aria-hidden="true"></i>
                <p class="font-bold">ไม่พบข้อมูลครุภัณฑ์ตามเงื่อนไขที่ระบุ</p>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($assets as $a): ?>
              <tr class="hover:bg-slate-50/70 transition-colors">
                <td class="px-5 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-xl bg-slate-100 text-slate-900 font-mono text-[11px] font-bold tabular-nums">
                    <?= htmlspecialchars($a['asset_code']) ?>
                  </span>
                  <?php if (!empty($a['serial_number'])): ?>
                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">SN: <?= htmlspecialchars($a['serial_number']) ?></div>
                  <?php endif; ?>
                </td>
                <td class="px-5 py-4 max-w-[220px]">
                  <strong class="text-slate-900 block truncate"><?= htmlspecialchars($a['name']) ?></strong>
                  <span class="text-[11px] text-slate-400 block truncate">
                    <?= htmlspecialchars(($a['brand'] ?? '') . ' ' . ($a['model'] ?? '')) ?>
                  </span>
                </td>
                <td class="px-5 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-100 text-slate-700 text-[11px] font-medium">
                    <i class="<?= htmlspecialchars($a['equipment_icon'] ?? 'fa-solid fa-wrench') ?> text-slate-400" aria-hidden="true"></i>
                    <span><?= htmlspecialchars($a['equipment_type_name']) ?></span>
                  </span>
                </td>
                <td class="px-5 py-4 whitespace-nowrap">
                  <strong class="text-slate-800 block"><?= htmlspecialchars($a['building']) ?></strong>
                  <span class="text-[11px] text-slate-400">ห้อง <?= htmlspecialchars($a['room']) ?></span>
                </td>
                <td class="px-5 py-4 whitespace-nowrap">
                  <?php
                    $statusBadge = match($a['status']) {
                      'active' => 'bg-emerald-50 text-emerald-700',
                      'maintenance' => 'bg-amber-50 text-amber-700',
                      'damaged' => 'bg-rose-50 text-rose-700',
                      default => 'bg-slate-100 text-slate-600',
                    };
                    $statusLabel = match($a['status']) {
                      'active' => 'พร้อมใช้งาน',
                      'maintenance' => 'อยู่ระหว่างซ่อม',
                      'damaged' => 'ชำรุด',
                      default => 'จำหน่ายออก',
                    };
                  ?>
                  <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[11px] font-bold <?= $statusBadge ?>">
                    <?= $statusLabel ?>
                  </span>
                </td>
                <td class="px-5 py-4 whitespace-nowrap tabular-nums">
                  <?php if ((int)$a['repair_count'] > 0): ?>
                    <span class="text-violet-600 font-bold"><?= (int)$a['repair_count'] ?> ครั้ง</span>
                  <?php else: ?>
                    <span class="text-slate-400">ยังไม่เคยซ่อม</span>
                  <?php endif; ?>
                </td>
                <td class="px-5 py-4 whitespace-nowrap text-right">
                  <div class="inline-flex items-center gap-1.5">
                    <a 
                      href="index.php?page=tickets-create&asset_id=<?= $a['id'] ?>" 
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-violet-50 hover:bg-violet-100 text-violet-700 font-bold text-xs transition-all"
                      title="แจ้งซ่อมครุภัณฑ์นี้"
                    >
                      <i class="fa-solid fa-wrench text-[10px]" aria-hidden="true"></i>
                      <span>แจ้งซ่อม</span>
                    </a>
                    <a 
                      href="index.php?page=assets-qr&id=<?= $a['id'] ?>" 
                      target="_blank" 
                      class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs transition-all"
                      title="พิมพ์สติกเกอร์ QR Code"
                    >
                      <i class="fa-solid fa-qrcode text-xs" aria-hidden="true"></i>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- Modal: Add New Asset -->
<div id="addAssetModal" class="hidden fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="modal-asset-title">
  <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closeAssetModal()"></div>

  <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
    <div class="relative transform overflow-hidden rounded-3xl bg-white text-left transition-all sm:my-8 sm:w-full sm:max-w-xl">
      <div class="px-6 py-5 bg-slate-50/60 flex items-center justify-between">
        <h2 id="modal-asset-title" class="flex items-center gap-2 font-black text-sm text-slate-900">
          <i class="fa-solid fa-qrcode text-violet-600" aria-hidden="true"></i>
          <span>ลงทะเบียนครุภัณฑ์และสร้าง QR Code</span>
        </h2>
        <button type="button" onclick="closeAssetModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 focus:outline-none">
          <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
      </div>

      <form action="index.php?page=assets-store" method="POST" class="p-6 space-y-4 text-xs">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-bold text-slate-700 mb-1.5">รหัสครุภัณฑ์ <span class="text-rose-500">*</span></label>
            <input type="text" name="asset_code" required class="w-full px-4 py-2.5 rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20 font-mono" placeholder="เช่น ศธ 0401/67-009">
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1.5">หมวดหมู่อุปกรณ์ <span class="text-rose-500">*</span></label>
            <select name="equipment_type_id" required class="w-full px-4 py-2.5 rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
              <?php foreach ($equipmentTypes as $eq): ?>
                <option value="<?= $eq->id ?>"><?= htmlspecialchars($eq->name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div>
          <label class="block font-bold text-slate-700 mb-1.5">ชื่อครุภัณฑ์ รายละเอียด <span class="text-rose-500">*</span></label>
          <input type="text" name="name" required class="w-full px-4 py-2.5 rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20" placeholder="เช่น เครื่องฉายโปรเจกเตอร์ Epson EB-X51">
        </div>

        <div class="grid grid-cols-3 gap-3">
          <div>
            <label class="block font-bold text-slate-700 mb-1.5">ยี่ห้อ</label>
            <input type="text" name="brand" class="w-full px-4 py-2.5 rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20" placeholder="Epson">
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1.5">รุ่น</label>
            <input type="text" name="model" class="w-full px-4 py-2.5 rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20" placeholder="EB-X51">
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1.5">Serial Number</label>
            <input type="text" name="serial_number" class="w-full px-4 py-2.5 rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20 font-mono" placeholder="SN12345678">
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-bold text-slate-700 mb-1.5">อาคาร <span class="text-rose-500">*</span></label>
            <input type="text" name="building" required list="buildingList" class="w-full px-4 py-2.5 rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20" placeholder="เช่น อาคาร 1 เฉลิมพระเกียรติ">
            <datalist id="buildingList">
              <?php foreach ($buildings as $b): ?><option value="<?= htmlspecialchars($b) ?>"><?php endforeach; ?>
            </datalist>
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1.5">ห้อง หรือจุดติดตั้ง <span class="text-rose-500">*</span></label>
            <input type="text" name="room" required class="w-full px-4 py-2.5 rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20" placeholder="เช่น ห้องเรียน 201">
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-3">
          <button type="button" onclick="closeAssetModal()" class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition-all">ยกเลิก</button>
          <button type="submit" class="px-6 py-2.5 rounded-2xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold transition-all active:scale-95">บันทึกครุภัณฑ์</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openAssetModal() {
  const m = document.getElementById('addAssetModal');
  if (m) { m.classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
}
function closeAssetModal() {
  const m = document.getElementById('addAssetModal');
  if (m) { m.classList.add('hidden'); document.body.style.overflow = 'auto'; }
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
