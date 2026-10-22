<?php
require_once __DIR__ . '/../layout/header.php';
use App\Core\Session;

$totalTickets = array_sum(array_column($types, 'ticket_count'));
$totalAssets = array_sum(array_column($types, 'asset_count'));
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

  <!-- Header -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <div class="flex items-center gap-2 text-xs font-bold text-violet-600 uppercase tracking-wider mb-1">
        <i class="fa-solid fa-shapes" aria-hidden="true"></i>
        <span>การตั้งค่าระบบ</span>
      </div>
      <h1 class="text-2xl font-black text-slate-900 tracking-tight font-display">จัดการหมวดหมู่อุปกรณ์และงานซ่อม</h1>
      <p class="text-xs text-slate-500 mt-0.5">กำหนดประเภทของอุปกรณ์ ไอคอนแสดงผล และตรวจสอบปริมาณงานซ่อมตามหมวดหมู่</p>
    </div>
    <button 
      type="button" 
      onclick="openTypeModal()"
      class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-violet-600/30 active:scale-95"
    >
      <i class="fa-solid fa-plus" aria-hidden="true"></i>
      <span>เพิ่มหมวดหมู่ใหม่</span>
    </button>
  </div>

  <!-- Summary Tiles -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="bg-white rounded-3xl p-5 flex items-center justify-between">
      <div>
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">หมวดหมู่ทั้งหมด</span>
        <div class="text-3xl font-black text-slate-900 mt-2 font-display tabular-nums"><?= count($types) ?> <span class="text-xs font-normal text-slate-400">หมวดหมู่</span></div>
      </div>
      <div class="w-12 h-12 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center text-lg">
        <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
      </div>
    </div>

    <div class="bg-white rounded-3xl p-5 flex items-center justify-between">
      <div>
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">ใบแจ้งซ่อมที่เกี่ยวข้อง</span>
        <div class="text-3xl font-black text-slate-900 mt-2 font-display tabular-nums"><?= $totalTickets ?> <span class="text-xs font-normal text-slate-400">รายการ</span></div>
      </div>
      <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center text-lg">
        <i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i>
      </div>
    </div>

    <div class="bg-white rounded-3xl p-5 flex items-center justify-between">
      <div>
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">ครุภัณฑ์ในระบบ</span>
        <div class="text-3xl font-black text-slate-900 mt-2 font-display tabular-nums"><?= $totalAssets ?> <span class="text-xs font-normal text-slate-400">รายการ</span></div>
      </div>
      <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center text-lg">
        <i class="fa-solid fa-boxes-stacked" aria-hidden="true"></i>
      </div>
    </div>
  </div>

  <!-- Types Table Card -->
  <div class="bg-white rounded-3xl overflow-hidden">
    <div class="px-6 py-4 flex items-center justify-between bg-slate-50/60">
      <div class="flex items-center gap-2">
        <span class="text-xs font-black text-slate-800">รายชื่อหมวดหมู่อุปกรณ์</span>
        <span class="px-2 py-0.5 rounded-full bg-violet-100 text-violet-700 text-[11px] font-bold tabular-nums"><?= count($types) ?> รายการ</span>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-100">
        <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-left">
          <tr>
            <th class="px-5 py-3.5">ไอคอน</th>
            <th class="px-5 py-3.5">ชื่อหมวดหมู่</th>
            <th class="px-5 py-3.5">รายละเอียดคำอธิบาย</th>
            <th class="px-5 py-3.5 text-center">ใบแจ้งซ่อม</th>
            <th class="px-5 py-3.5 text-center">ครุภัณฑ์</th>
            <th class="px-5 py-3.5 text-center">อะไหล่</th>
            <th class="px-5 py-3.5 text-right">การจัดการ</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-xs">
          <?php foreach ($types as $t): ?>
            <?php 
              $isDeletable = ((int)$t['ticket_count'] === 0 && (int)$t['asset_count'] === 0);
            ?>
            <tr class="hover:bg-slate-50/70 transition-colors">
              <td class="px-5 py-4 whitespace-nowrap">
                <div class="w-10 h-10 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center text-base">
                  <i class="<?= htmlspecialchars($t['icon']) ?>" aria-hidden="true"></i>
                </div>
              </td>
              <td class="px-5 py-4 whitespace-nowrap">
                <strong class="text-slate-900 block font-bold text-sm"><?= htmlspecialchars($t['name']) ?></strong>
                <span class="text-[11px] font-mono text-slate-400">Class: <?= htmlspecialchars($t['icon']) ?></span>
              </td>
              <td class="px-5 py-4 text-slate-600 max-w-xs">
                <?= htmlspecialchars($t['description'] ?? '-') ?>
              </td>
              <td class="px-5 py-4 whitespace-nowrap text-center">
                <span class="inline-flex items-center px-2.5 py-1 rounded-xl bg-slate-100 text-slate-700 font-bold tabular-nums">
                  <?= number_format((int)$t['ticket_count']) ?>
                </span>
              </td>
              <td class="px-5 py-4 whitespace-nowrap text-center">
                <span class="inline-flex items-center px-2.5 py-1 rounded-xl bg-slate-100 text-slate-700 font-bold tabular-nums">
                  <?= number_format((int)$t['asset_count']) ?>
                </span>
              </td>
              <td class="px-5 py-4 whitespace-nowrap text-center">
                <span class="inline-flex items-center px-2.5 py-1 rounded-xl bg-slate-100 text-slate-700 font-bold tabular-nums">
                  <?= number_format((int)$t['part_count']) ?>
                </span>
              </td>
              <td class="px-5 py-4 whitespace-nowrap text-right space-x-1">
                <button 
                  type="button" 
                  onclick="editType(<?= htmlspecialchars(json_encode($t, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)) ?>)"
                  class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors"
                >
                  <i class="fa-solid fa-pen text-[10px] mr-1" aria-hidden="true"></i>แก้ไข
                </button>

                <?php if ($isDeletable): ?>
                  <form action="index.php?page=equipment-types-delete" method="POST" class="inline" onsubmit="return confirm('ยืนยันการลบหมวดหมู่อุปกรณ์ \'<?= htmlspecialchars(addslashes($t['name'])) ?>\' ?');">
                    <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-xs transition-colors">
                      <i class="fa-solid fa-trash-can text-[10px] mr-1" aria-hidden="true"></i>ลบ
                    </button>
                  </form>
                <?php else: ?>
                  <span class="px-2 py-1 text-[10px] text-slate-400 font-medium" title="ไม่สามารถลบได้เนื่องจากมีข้อมูลใบแจ้งซ่อมหรือครุภัณฑ์ใช้งานอยู่">
                    ใช้งานอยู่
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

<!-- Create / Edit Type Modal -->
<div id="typeModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm" role="dialog" aria-modal="true">
  <div class="bg-white w-full max-w-lg rounded-3xl overflow-hidden shadow-2xl transition-all">
    <div class="px-6 py-5 bg-slate-50 flex items-center justify-between">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-violet-100 text-violet-700 flex items-center justify-center text-sm font-bold">
          <i class="fa-solid fa-shapes" aria-hidden="true"></i>
        </div>
        <h2 id="modalTitle" class="text-base font-black text-slate-900">เพิ่มหมวดหมู่อุปกรณ์</h2>
      </div>
      <button type="button" onclick="closeTypeModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl">
        <i class="fa-solid fa-xmark text-sm" aria-hidden="true"></i>
      </button>
    </div>

    <form action="index.php?page=equipment-types-store" method="POST" class="p-6 space-y-4">
      <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
      <input type="hidden" id="formTypeId" name="id" value="">

      <div>
        <label for="typeName" class="block text-xs font-bold text-slate-700 mb-1">
          ชื่อหมวดหมู่อุปกรณ์ <span class="text-rose-500">*</span>
        </label>
        <input 
          type="text" 
          id="typeName" 
          name="name" 
          required 
          placeholder="เช่น เครื่องปรับอากาศและพัดลมระบายอากาศ"
          class="w-full px-4 py-2.5 rounded-2xl bg-slate-100 focus:bg-white text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-violet-500/20 font-bold transition-all"
        >
      </div>

      <div>
        <label for="typeIcon" class="block text-xs font-bold text-slate-700 mb-1">
          Font Awesome Icon Class <span class="text-rose-500">*</span>
        </label>
        <div class="flex items-center gap-3">
          <input 
            type="text" 
            id="typeIcon" 
            name="icon" 
            required 
            placeholder="เช่น fa-solid fa-fan"
            oninput="updateIconPreview(this.value)"
            class="flex-1 px-4 py-2.5 rounded-2xl bg-slate-100 focus:bg-white text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-violet-500/20 font-mono transition-all"
          >
          <div id="iconPreviewBox" class="w-10 h-10 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center text-base flex-shrink-0">
            <i id="iconPreview" class="fa-solid fa-wrench"></i>
          </div>
        </div>
        <div class="mt-2 flex flex-wrap gap-1.5">
          <span class="text-[10px] text-slate-400 w-full mb-0.5">ไอคอนแนะนำที่พบบ่อย (คลิกเพื่อเลือก):</span>
          <button type="button" onclick="selectQuickIcon('fa-solid fa-desktop')" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-violet-100 hover:text-violet-700 text-[11px] font-mono"><i class="fa-solid fa-desktop mr-1"></i>คอม</button>
          <button type="button" onclick="selectQuickIcon('fa-solid fa-bolt')" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-violet-100 hover:text-violet-700 text-[11px] font-mono"><i class="fa-solid fa-bolt mr-1"></i>ไฟฟ้า</button>
          <button type="button" onclick="selectQuickIcon('fa-solid fa-fan')" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-violet-100 hover:text-violet-700 text-[11px] font-mono"><i class="fa-solid fa-fan mr-1"></i>แอร์/พัดลม</button>
          <button type="button" onclick="selectQuickIcon('fa-solid fa-faucet-drip')" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-violet-100 hover:text-violet-700 text-[11px] font-mono"><i class="fa-solid fa-faucet-drip mr-1"></i>ประปา</button>
          <button type="button" onclick="selectQuickIcon('fa-solid fa-building')" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-violet-100 hover:text-violet-700 text-[11px] font-mono"><i class="fa-solid fa-building mr-1"></i>อาคาร</button>
          <button type="button" onclick="selectQuickIcon('fa-solid fa-microphone-lines')" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-violet-100 hover:text-violet-700 text-[11px] font-mono"><i class="fa-solid fa-microphone-lines mr-1"></i>โสตทัศน์</button>
          <button type="button" onclick="selectQuickIcon('fa-solid fa-network-wired')" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-violet-100 hover:text-violet-700 text-[11px] font-mono"><i class="fa-solid fa-network-wired mr-1"></i>เครือข่าย</button>
        </div>
      </div>

      <div>
        <label for="typeDesc" class="block text-xs font-bold text-slate-700 mb-1">รายละเอียดและคำอธิบาย</label>
        <textarea 
          id="typeDesc" 
          name="description" 
          rows="2" 
          placeholder="คำอธิบายเพิ่มเติมเกี่ยวกับหมวดหมู่นี้"
          class="w-full px-4 py-2.5 rounded-2xl bg-slate-100 focus:bg-white text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all resize-none"
        ></textarea>
      </div>

      <div class="flex items-center justify-end gap-2 pt-2">
        <button 
          type="button" 
          onclick="closeTypeModal()"
          class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors"
        >
          ยกเลิก
        </button>
        <button 
          type="submit" 
          class="px-5 py-2.5 rounded-2xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold transition-all active:scale-95"
        >
          บันทึกข้อมูล
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  function openTypeModal() {
    document.getElementById('modalTitle').innerText = 'เพิ่มหมวดหมู่อุปกรณ์';
    document.getElementById('formTypeId').value = '';
    document.getElementById('typeName').value = '';
    document.getElementById('typeIcon').value = 'fa-solid fa-wrench';
    document.getElementById('typeDesc').value = '';
    updateIconPreview('fa-solid fa-wrench');
    document.getElementById('typeModal').classList.remove('hidden');
  }

  function editType(type) {
    document.getElementById('modalTitle').innerText = 'แก้ไขหมวดหมู่อุปกรณ์';
    document.getElementById('formTypeId').value = type.id;
    document.getElementById('typeName').value = type.name;
    document.getElementById('typeIcon').value = type.icon || 'fa-solid fa-wrench';
    document.getElementById('typeDesc').value = type.description || '';
    updateIconPreview(type.icon || 'fa-solid fa-wrench');
    document.getElementById('typeModal').classList.remove('hidden');
  }

  function closeTypeModal() {
    document.getElementById('typeModal').classList.add('hidden');
  }

  function updateIconPreview(cls) {
    const el = document.getElementById('iconPreview');
    if (el) el.className = cls || 'fa-solid fa-wrench';
  }

  function selectQuickIcon(cls) {
    document.getElementById('typeIcon').value = cls;
    updateIconPreview(cls);
  }
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
