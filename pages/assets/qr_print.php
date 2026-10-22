<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>สติกเกอร์ QR Code - <?= htmlspecialchars($asset['asset_code']) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@700;800&family=Sarabun:wght@400;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

  <style>
    body { font-family: 'Sarabun', sans-serif; background-color: #f1f5f9; }
    .sticker-card {
      width: 90mm;
      min-height: 55mm;
      box-sizing: border-box;
      background: #ffffff;
      border: 2px dashed #cbd5e1;
    }
    @media print {
      body { background: transparent !important; padding: 0 !important; }
      .no-print { display: none !important; }
      .sticker-card { border: 2px solid #000000 !important; page-break-inside: avoid; }
    }
  </style>
</head>
<body class="p-6 flex flex-col items-center justify-center min-h-screen">

<div class="max-w-md w-full mb-6 no-print flex items-center justify-between bg-white p-4 rounded-3xl">
  <div class="flex items-center gap-2">
    <i class="fa-solid fa-qrcode text-violet-600 text-lg"></i>
    <span class="text-xs font-bold text-slate-800">พิมพ์สติกเกอร์ประจำครุภัณฑ์</span>
  </div>
  <div class="flex items-center gap-2">
    <button onclick="window.print()" class="px-4 py-2 rounded-2xl bg-violet-600 hover:bg-violet-700 text-white font-bold text-xs">
      <i class="fa-solid fa-print mr-1"></i> พิมพ์สติกเกอร์
    </button>
    <button onclick="window.close()" class="px-3 py-2 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs">
      ปิด
    </button>
  </div>
</div>

<?php
  // Generate QR Code URL
  $repairUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost:8090') . dirname($_SERVER['PHP_SELF']) . "/index.php?page=tickets-create&asset_token=" . urlencode($asset['qr_token']);
  $qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=" . urlencode($repairUrl);
?>

<!-- Sticker Card (Standard 90x55mm label) -->
<div class="sticker-card p-4 rounded-3xl flex items-center gap-4 text-slate-900 shadow-none">
  <!-- QR Code Image -->
  <div class="flex-shrink-0 text-center">
    <img src="<?= $qrApiUrl ?>" alt="QR Code" class="w-28 h-28 object-contain rounded-xl bg-white border border-slate-200 p-1" />
    <span class="text-[9px] font-bold text-violet-700 mt-1 block">สแกนแจ้งซ่อม</span>
  </div>

  <!-- Asset Details -->
  <div class="flex-1 min-w-0 space-y-1">
    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">ครุภัณฑ์โรงเรียน</div>
    <div class="text-xs font-black text-slate-900 leading-snug line-clamp-2"><?= htmlspecialchars($asset['name']) ?></div>
    
    <div class="pt-1">
      <span class="inline-block px-2 py-0.5 rounded-lg bg-slate-100 font-mono text-[10px] font-bold text-slate-800">
        <?= htmlspecialchars($asset['asset_code']) ?>
      </span>
    </div>

    <div class="text-[10px] text-slate-600 pt-0.5">
      <i class="fa-solid fa-location-dot text-[9px] text-slate-400 mr-1"></i>
      <?= htmlspecialchars($asset['building']) ?> · <?= htmlspecialchars($asset['room']) ?>
    </div>

    <?php if (!empty($asset['brand']) || !empty($asset['model'])): ?>
      <div class="text-[9px] text-slate-400 truncate">
        <?= htmlspecialchars(($asset['brand'] ?? '') . ' ' . ($asset['model'] ?? '')) ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<p class="text-[11px] text-slate-400 mt-4 text-center no-print">
  สามารถพิมพ์ลงบนกระดาษสติกเกอร์และนำไปติดที่ตัวเครื่องอุปกรณ์เพื่อความสะดวกในการสแกนแจ้งซ่อม
</p>

</body>
</html>
