<?php
require_once __DIR__ . '/../layout/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

  <!-- Breadcrumbs -->
  <nav class="flex items-center gap-2 text-xs text-slate-500">
    <a href="index.php" class="hover:text-violet-600 transition-colors font-medium">หน้าหลัก</a>
    <i class="fa-solid fa-chevron-right text-[10px] text-slate-400" aria-hidden="true"></i>
    <a href="index.php?page=tickets" class="hover:text-violet-600 transition-colors font-medium">ใบแจ้งซ่อม</a>
    <i class="fa-solid fa-chevron-right text-[10px] text-slate-400" aria-hidden="true"></i>
    <span class="text-slate-900 font-bold">ส่งคำขอแจ้งซ่อมใหม่</span>
  </nav>

  <!-- If Scanned via QR Code Banner -->
  <?php if (!empty($selectedAsset)): ?>
    <div class="p-5 rounded-3xl bg-violet-50 flex items-center justify-between gap-4">
      <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 rounded-2xl bg-violet-600 text-white flex items-center justify-center text-xl flex-shrink-0">
          <i class="fa-solid fa-qrcode" aria-hidden="true"></i>
        </div>
        <div>
          <span class="text-[10px] font-bold text-violet-700 uppercase tracking-wider block">สแกนรหัส QR Code สำเร็จ</span>
          <h2 class="text-sm font-black text-slate-900 mt-0.5">
            <?= htmlspecialchars($selectedAsset['asset_code']) ?> · <?= htmlspecialchars($selectedAsset['name']) ?>
          </h2>
          <p class="text-[11px] text-slate-500">ระบบได้เติมข้อมูลสถานที่และหมวดหมู่อุปกรณ์ให้อัตโนมัติ</p>
        </div>
      </div>
      <a href="index.php?page=tickets-create" class="text-xs text-slate-400 hover:text-slate-700 font-bold">ล้างข้อมูล</a>
    </div>
  <?php endif; ?>

  <!-- Main Card -->
  <div class="bg-white rounded-3xl overflow-hidden">
    <!-- Card Header -->
    <div class="p-6 bg-slate-50/60 flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center text-xl flex-shrink-0">
        <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
      </div>
      <div>
        <h1 class="text-base font-black text-slate-900">แบบฟอร์มส่งคำขอแจ้งซ่อมบำรุงโรงเรียน</h1>
        <p class="text-xs text-slate-500 mt-0.5">กรอกข้อมูลสถานที่และอาการชำรุดเพื่อให้เจ้าหน้าที่ช่างเข้าตรวจสอบได้อย่างรวดเร็ว</p>
      </div>
    </div>

    <!-- Form -->
    <div class="p-6 sm:p-8">
      <form action="index.php?page=tickets-store" method="POST" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">

        <!-- Select Asset (Optional) -->
        <div>
          <div class="flex items-center justify-between mb-1.5">
            <label for="asset_id" class="block text-xs font-bold text-slate-700">
              <i class="fa-solid fa-qrcode text-violet-600 mr-1" aria-hidden="true"></i>เลือกครุภัณฑ์ในระบบ (หากมีรหัสครุภัณฑ์ติดอยู่)
            </label>
            <button 
              type="button" 
              onclick="openQrTokenModal()" 
              class="text-[11px] font-bold text-violet-600 hover:text-violet-800 flex items-center gap-1 transition-colors"
            >
              <i class="fa-solid fa-camera text-[10px]" aria-hidden="true"></i>
              <span>กรอก Token หรือรหัส QR</span>
            </button>
          </div>
          <select 
            id="asset_id" 
            name="asset_id" 
            onchange="handleAssetSelect(this)"
            class="w-full px-4 py-3 rounded-2xl bg-slate-100 focus:bg-white text-slate-900 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all font-mono"
          >
            <option value="">ไม่ได้ระบุครุภัณฑ์เฉพาะเจาะจง</option>
            <?php foreach ($assets as $a): ?>
              <?php $isSelected = !empty($selectedAsset) && (int)$selectedAsset['id'] === (int)$a['id']; ?>
              <option 
                value="<?= $a['id'] ?>" 
                data-name="<?= htmlspecialchars($a['name']) ?>"
                data-building="<?= htmlspecialchars($a['building']) ?>"
                data-room="<?= htmlspecialchars($a['room']) ?>"
                data-eq="<?= (int)$a['equipment_type_id'] ?>"
                <?= $isSelected ? 'selected' : '' ?>
              >
                <?= htmlspecialchars($a['asset_code'] . ' - ' . $a['name'] . ' (' . $a['building'] . ' ' . $a['room'] . ')') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Title -->
        <div>
          <label for="title" class="block text-xs font-bold text-slate-700 mb-1.5">
            <i class="fa-solid fa-tag text-slate-400 mr-1" aria-hidden="true"></i>หัวข้อปัญหาที่พบ <span class="text-rose-500">*</span>
          </label>
          <input 
            type="text" 
            class="w-full px-4 py-3 rounded-2xl bg-slate-100 focus:bg-white text-slate-900 text-xs sm:text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all" 
            id="title" 
            name="title" 
            placeholder="เช่น โปรเจกเตอร์ห้อง 302 สีเพี้ยน หรือ แอร์ห้องพักครูน้ำหยด" 
            value="<?= !empty($selectedAsset) ? htmlspecialchars('ซ่อมบำรุง ' . $selectedAsset['name']) : '' ?>"
            required 
            autofocus
          >
        </div>

        <!-- Location Grid: Building & Room -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="building" class="block text-xs font-bold text-slate-700 mb-1.5">
              <i class="fa-solid fa-school text-slate-400 mr-1" aria-hidden="true"></i>อาคารสถานที่ <span class="text-rose-500">*</span>
            </label>
            <input 
              type="text" 
              class="w-full px-4 py-3 rounded-2xl bg-slate-100 focus:bg-white text-slate-900 text-xs sm:text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all" 
              id="building" 
              name="building" 
              list="buildingOptions"
              value="<?= !empty($selectedAsset) ? htmlspecialchars($selectedAsset['building']) : '' ?>"
              placeholder="เช่น อาคาร 1 เฉลิมพระเกียรติ หรือ อาคารวิทยาศาสตร์" 
              required
            >
            <datalist id="buildingOptions">
              <option value="อาคาร 1 เฉลิมพระเกียรติ">
              <option value="อาคาร 2 วิทยาศาสตร์">
              <option value="อาคาร 3 มัธยมศึกษา">
              <option value="อาคารบริหารและธุรการ">
              <option value="อาคารอเนกประสงค์ และโรงอาหาร">
              <option value="หอประชุมใหญ่">
            </datalist>
          </div>

          <div>
            <label for="room" class="block text-xs font-bold text-slate-700 mb-1.5">
              <i class="fa-solid fa-door-open text-slate-400 mr-1" aria-hidden="true"></i>ห้อง หรือบริเวณจุดติดตั้ง <span class="text-rose-500">*</span>
            </label>
            <input 
              type="text" 
              class="w-full px-4 py-3 rounded-2xl bg-slate-100 focus:bg-white text-slate-900 text-xs sm:text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all" 
              id="room" 
              name="room" 
              value="<?= !empty($selectedAsset) ? htmlspecialchars($selectedAsset['room']) : '' ?>"
              placeholder="เช่น ห้องเรียน 204 ชั้น 2 หรือ ห้องปฏิบัติการเคมี" 
              required
            >
          </div>
        </div>

        <!-- Equipment Type & Priority -->
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
          <div class="sm:col-span-7">
            <label for="equipment_type_id" class="block text-xs font-bold text-slate-700 mb-1.5">
              <i class="fa-solid fa-wrench text-slate-400 mr-1" aria-hidden="true"></i>หมวดหมู่อุปกรณ์ หรือประเภทปัญหา <span class="text-rose-500">*</span>
            </label>
            <select 
              class="w-full px-4 py-3 rounded-2xl bg-slate-100 focus:bg-white text-slate-900 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all" 
              id="equipment_type_id" 
              name="equipment_type_id" 
              required
            >
              <option value="" disabled <?= empty($selectedAsset) ? 'selected' : '' ?>>เลือกประเภทอุปกรณ์หรือระบบที่ชำรุด</option>
              <?php foreach ($equipmentTypes as $eq): ?>
                <?php $isEqSelected = !empty($selectedAsset) && (int)$selectedAsset['equipment_type_id'] === (int)$eq->id; ?>
                <option value="<?= $eq->id ?>" <?= $isEqSelected ? 'selected' : '' ?>>
                  <?= htmlspecialchars($eq->name) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="sm:col-span-5">
            <label for="priority" class="block text-xs font-bold text-slate-700 mb-1.5">
              <i class="fa-solid fa-flag text-slate-400 mr-1" aria-hidden="true"></i>ระดับความเร่งด่วน <span class="text-rose-500">*</span>
            </label>
            <select 
              class="w-full px-4 py-3 rounded-2xl bg-slate-100 focus:bg-white text-slate-900 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all" 
              id="priority" 
              name="priority" 
              required
            >
              <option value="normal" selected>ปกติ - ทั่วไปตามลำดับคิว</option>
              <option value="high">ด่วน - มีผลต่อการเรียนการสอน</option>
              <option value="urgent">ด่วนที่สุด - อันตรายหรือระบบหยุดชะงัก</option>
              <option value="low">ไม่เร่งด่วน - งานปรับปรุงซ่อมแซมล่วงหน้า</option>
            </select>
          </div>
        </div>

        <!-- Problem Description -->
        <div>
          <label for="description" class="block text-xs font-bold text-slate-700 mb-1.5">
            <i class="fa-solid fa-file-lines text-slate-400 mr-1" aria-hidden="true"></i>รายละเอียดอาการชำรุดและข้อสังเกตเพิ่มเติม <span class="text-rose-500">*</span>
          </label>
          <textarea 
            class="w-full px-4 py-3 rounded-2xl bg-slate-100 focus:bg-white text-slate-900 text-xs sm:text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all" 
            id="description" 
            name="description" 
            rows="4" 
            placeholder="ระบุอาการชำรุดอย่างละเอียด เช่น เปิดแล้วไม่มีเสียง หลอดไฟกระพริบ มีกลิ่นไหม้ หรือเริ่มเป็นมาตั้งแต่เมื่อไหร่" 
            required
          ></textarea>
        </div>

        <!-- Before Image Upload Box -->
        <div class="p-6 rounded-3xl bg-slate-100">
          <label for="before_image_input" class="block text-xs font-bold text-slate-800 mb-1 cursor-pointer">
            <i class="fa-solid fa-camera text-violet-600 mr-1.5" aria-hidden="true"></i>แนบภาพถ่ายหลักฐานก่อนการซ่อม
          </label>
          <p class="text-[11px] text-slate-500 mb-3">
            ถ่ายภาพจุดที่ชำรุด ตัวเครื่อง หรืออุปกรณ์ที่มีปัญหา รองรับไฟล์ JPG PNG WEBP ขนาดไม่เกิน 5MB
          </p>
          <input 
            type="file" 
            class="block w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-violet-100 file:text-violet-700 hover:file:bg-violet-200 transition-colors cursor-pointer focus:outline-none" 
            id="before_image_input" 
            name="before_image" 
            accept="image/jpeg,image/png,image/webp,image/gif"
          >
          <div id="before_image_preview" class="mt-3"></div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
          <a 
            href="index.php?page=tickets" 
            class="inline-flex items-center justify-center gap-2 px-5 py-3 min-h-[44px] rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs sm:text-sm font-bold transition-all text-center"
          >
            <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i>
            <span>ยกเลิก</span>
          </a>
          <button 
            type="submit" 
            class="inline-flex items-center justify-center gap-2 px-7 py-3 min-h-[44px] rounded-2xl bg-violet-600 hover:bg-violet-700 text-white text-xs sm:text-sm font-bold transition-all active:scale-95 group text-center"
          >
            <span>ส่งคำขอแจ้งซ่อม</span>
            <i class="fa-solid fa-paper-plane text-xs transition-transform group-hover:translate-x-0.5" aria-hidden="true"></i>
          </button>
        </div>

      </form>
    </div>
  </div>

</div>

<!-- Quick QR Token Modal -->
<div id="qrTokenModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm" role="dialog" aria-modal="true">
  <div class="bg-white w-full max-w-md rounded-3xl overflow-hidden shadow-2xl transition-all">
    <div class="px-6 py-5 bg-slate-50 flex items-center justify-between">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-violet-100 text-violet-700 flex items-center justify-center text-sm font-bold">
          <i class="fa-solid fa-qrcode" aria-hidden="true"></i>
        </div>
        <h2 class="text-base font-black text-slate-900">ค้นหาครุภัณฑ์ด้วย QR Token</h2>
      </div>
      <button type="button" onclick="closeQrTokenModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl">
        <i class="fa-solid fa-xmark text-sm" aria-hidden="true"></i>
      </button>
    </div>

    <form onsubmit="handleQrTokenSubmit(event)" class="p-6 space-y-4">
      <div>
        <label for="qrInputToken" class="block text-xs font-bold text-slate-700 mb-1">
          กรอก QR Token หรือรหัสครุภัณฑ์
        </label>
        <input 
          type="text" 
          id="qrInputToken" 
          required 
          placeholder="เช่น ASSET-TOKEN-001 หรือ QR-XXXX"
          class="w-full px-4 py-2.5 rounded-2xl bg-slate-100 focus:bg-white text-xs font-mono font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all"
        >
        <p class="text-[11px] text-slate-400 mt-1">สามารถคัดลอก Token จาก QR Code หรือพิมพ์รหัสที่ระบุบนป้ายครุภัณฑ์</p>
      </div>

      <div class="flex items-center justify-end gap-2 pt-2">
        <button 
          type="button" 
          onclick="closeQrTokenModal()"
          class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors"
        >
          ยกเลิก
        </button>
        <button 
          type="submit" 
          class="px-5 py-2.5 rounded-2xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold transition-all active:scale-95"
        >
          ดึงข้อมูลครุภัณฑ์
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function handleAssetSelect(select) {
  const opt = select.options[select.selectedIndex];
  if (opt && opt.value) {
    const building = opt.getAttribute('data-building');
    const room = opt.getAttribute('data-room');
    const eq = opt.getAttribute('data-eq');
    const name = opt.getAttribute('data-name');

    if (building) document.getElementById('building').value = building;
    if (room) document.getElementById('room').value = room;
    if (eq) document.getElementById('equipment_type_id').value = eq;
    if (name && !document.getElementById('title').value) {
      document.getElementById('title').value = 'ซ่อมบำรุง ' + name;
    }
  }
}

function openQrTokenModal() {
  document.getElementById('qrTokenModal').classList.remove('hidden');
  document.getElementById('qrInputToken').focus();
}

function closeQrTokenModal() {
  document.getElementById('qrTokenModal').classList.add('hidden');
}

function handleQrTokenSubmit(e) {
  e.preventDefault();
  const token = document.getElementById('qrInputToken').value.trim();
  if (token) {
    window.location.href = 'index.php?page=tickets-create&asset_token=' + encodeURIComponent(token);
  }
}
</script>

<?php
require_once __DIR__ . '/../layout/footer.php';
?>
