<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $judul ?? 'Scan Barcode'; ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha384-iw3OoTErCYJJB9mCa8LNS2hbsQ7M3C0EpIsO/H5+EGAkPGc6rk+V8i04oW/K5xq0" crossorigin="anonymous">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url('assets/css/landing.css') ?>">
<style>
  /* small overrides for scanner card */
  .scanner-card {
    max-width: 720px;
    width: 100%;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(15,23,42,0.08);
    background: #fff;
  }
  .scanner-wrapper {
    min-height: 60vh;
  }
  #reader {
    width: 100%;
    max-width: 500px; /* avoid huge camera view on desktop */
    min-height: 320px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border-radius: 8px;
    background: #f8fafc;
  }

  /* Mobile adjustments: make the scanner card and reader smaller so it doesn't fill entire screen */
  @media (max-width: 767.98px) {
    .scanner-card {
      max-width: 380px;
      padding: 1rem;
    }
    .scanner-wrapper {
      min-height: 40vh;
    }
    #reader {
      max-width: 320px;
      min-height: 200px;
    }
    .scan-info {
      font-size: 0.95rem;
    }
  }
  .scan-info {
    color: #1a237e; /* text-dark-blue theme */
  }

  .qr-alert-overlay {
    position: fixed;
    inset: 0;
    z-index: 1055;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    background: rgba(3, 12, 30, .62);
    backdrop-filter: blur(8px);
  }

  .qr-alert-overlay.is-open {
    display: flex;
  }

  .qr-alert-modal {
    width: 100%;
    max-width: 390px;
    border-radius: 32px;
    background: linear-gradient(180deg, #ffffff 0%, #f7faff 100%);
    box-shadow: 0 28px 80px rgba(0, 0, 0, .5), 0 0 0 4px rgba(255, 255, 255, .08);
    border: 1.5px solid rgba(0, 70, 199, .55);
    padding: 34px 24px 24px;
    text-align: center;
  }

  .qr-alert-icon {
    width: 92px;
    height: 92px;
    margin: 0 auto 24px;
    border-radius: 28px;
    display: grid;
    place-items: center;
    position: relative;
    background: linear-gradient(145deg, #0046c7, #062d91);
    box-shadow: 0 18px 38px rgba(0, 70, 199, .28);
    font-size: 2.5rem;
    color: #fff;
  }

  .qr-alert-icon::after {
    content: '!';
    position: absolute;
    right: -6px;
    bottom: -6px;
    width: 32px;
    height: 32px;
    display: grid;
    place-items: center;
    border-radius: 50%;
    background: #f7b500;
    color: #061a3d;
    border: 4px solid #fff;
    font-size: 1rem;
    font-weight: 800;
  }

  .qr-alert-title {
    margin: 0 0 12px;
    font-size: 27px;
    line-height: 1.2;
    font-weight: 900;
    letter-spacing: -.05em;
    color: #061a3d;
  }

  .qr-alert-title span {
    color: #0046c7;
  }

  .qr-alert-message {
    max-width: 290px;
    margin: 0 auto 22px;
    color: #334155;
    font-size: 16px;
    line-height: 1.55;
  }

  .qr-alert-info {
    margin-bottom: 20px;
    padding: 16px 18px;
    border-radius: 20px;
    background: #eaf2ff;
    border: 1px solid rgba(0, 70, 199, .16);
    color: #14346c;
  }

  .qr-alert-info p {
    margin: 0;
    font-size: 14px;
    line-height: 1.5;
    font-weight: 600;
  }

  .qr-alert-actions {
    display: flex;
    flex-direction: column;
    gap: .75rem;
  }

  .qr-alert-actions .btn {
    width: 100%;
    border-radius: 18px;
    padding: 16px;
    font-size: 16px;
    font-weight: 900;
  }

  .btn-qr-retry {
    color: #fff;
    border: 0;
    background: linear-gradient(135deg, #006cff, #0037b8);
    box-shadow: 0 14px 30px rgba(0, 70, 199, .28), inset 0 1px 0 rgba(255,255,255,.28);
  }

  .btn-qr-close {
    color: #334155;
    border: 1px solid #d7e1f4;
    background: #f8fafc;
  }

  @media (max-width: 575.98px) {
    .qr-alert-overlay {
      padding: 1rem;
    }
  }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= base_url() ?>">
      <img src="<?= asset_url('img/logo.png') ?>" alt="Logo" class="logo-img">
      <span class="fw-semibold">DISHUB KABUPATEN SLEMAN</span>
    </a>
  </div>
</nav>

<div class="pt-5"></div>

<section class="py-5">
  <div class="container d-flex justify-content-center align-items-center scanner-wrapper">
    <div class="scanner-card p-4">
      <div class="text-center mb-3">
        <h4 class="fw-bold scan-info">Scan Barcode</h4>
        <p class="text-muted small mb-0">Arahkan kamera ke QR / barcode. Kamera akan terbuka otomatis.</p>
      </div>

      <div id="reader"></div>

      <!-- File upload fallback when camera fails -->
      <div id="upload-fallback" class="text-center mt-3" style="display:none;">
        <p class="text-muted small mb-2">Kamera tidak tersedia? Upload foto QR code dari galeri:</p>
        <label for="qr-input-file" class="btn btn-outline-primary btn-sm">
          <i class="fas fa-image me-1"></i> Upload Gambar QR
        </label>
        <input type="file" id="qr-input-file" accept="image/*" capture="environment" style="display:none;">
        <div id="upload-result" class="mt-2"></div>
      </div>

      <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted small">Pastikan beri izin akses kamera. Hanya berfungsi di localhost atau HTTPS.</div>
        <div class="d-flex gap-2">
          <a href="<?= base_url('input-pju') ?>" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-keyboard me-1"></i> Input Manual</a>
          <a href="<?= base_url() ?>" id="btn-back" class="btn btn-outline-secondary btn-sm">Kembali</a>
        </div>
      </div>
    </div>
  </div>
</section>

<footer class="mt-4"></footer>

<div id="qr-unknown-modal" class="qr-alert-overlay" aria-hidden="true">
  <section class="qr-alert-modal" role="dialog" aria-modal="true" aria-labelledby="qr-unknown-title">
    <div class="qr-alert-icon"><i class="fa-solid fa-qrcode"></i></div>
    <h2 id="qr-unknown-title" class="qr-alert-title">Data PJU <span>tidak ditemukan</span></h2>
    <p class="qr-alert-message">QR Code yang Anda scan tidak sesuai dengan data PJU.</p>
    <div class="qr-alert-info">
      <p>Silahkan hubungi petugas Dishub untuk verifikasi lebih lanjut.</p>
    </div>
    <div class="qr-alert-actions">
      <button type="button" id="btn-qr-retry" class="btn btn-qr-retry">Scan Ulang</button>
      <button type="button" id="btn-qr-close" class="btn btn-qr-close">Tutup</button>
    </div>
  </section>
</div>

<!-- libs: primary CDN + fallback if primary fails -->
<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.7/html5-qrcode.min.js" integrity="sha384-hJMcc4vZxKbPwUvrjl/f7MWYnIQvANoP8ItXzz0nUy9i6D0ShaiIMj32mTZGb9kj" crossorigin="anonymous"></script>
<script>
  if (typeof Html5Qrcode === 'undefined') {
    document.write('<script src="https://unpkg.com/html5-qrcode@2.3.7/html5-qrcode.min.js" integrity="sha384-hJMcc4vZxKbPwUvrjl/f7MWYnIQvANoP8ItXzz0nUy9i6D0ShaiIMj32mTZGb9kj" crossorigin="anonymous"><\/script>');
  }
</script>
<script>
  window.__SCAN_CONFIG__ = {
    adminApiBase: '<?= ADMIN_API_BASE ?>'
  };
</script>
<script src="<?= base_url('assets/js/scan.js?v=' . time()) ?>"></script>

</body>
</html>
