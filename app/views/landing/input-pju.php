<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $judul ?? 'Input Data PJU'; ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha384-iw3OoTErCYJJB9mCa8LNS2hbsQ7M3C0EpIsO/H5+EGAkPGc6rk+V8i04oW/K5xq0" crossorigin="anonymous">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url('assets/css/landing.css') ?>">
<style>
  .input-pju-card {
    max-width: 620px;
    width: 100%;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(15,23,42,0.08);
    background: #fff;
  }
  .input-pju-wrapper {
    min-height: 60vh;
  }
  .info-label {
    font-weight: 600;
    color: #475569;
    font-size: 0.85rem;
  }
  .info-value {
    color: #1e293b;
  }
  #pju-detail-card, #pju-action-area {
    display: none;
  }
  @media (max-width: 767.98px) {
    .input-pju-card {
      max-width: 380px;
      padding: 1rem;
    }
    .input-pju-wrapper {
      min-height: 40vh;
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
  <div class="container d-flex justify-content-center align-items-start input-pju-wrapper">
    <div class="input-pju-card p-4">
      <div class="text-center mb-3">
        <h4 class="fw-bold" style="color: #1a237e;">Input Data PJU</h4>
        <p class="text-muted small mb-0">Masukkan ID PJU yang tertera pada tiang lampu untuk melihat detail dan membuat laporan.</p>
      </div>

      <!-- Search Form -->
      <form id="form-lookup-pju" class="mb-3">
        <div class="input-group">
          <span class="input-group-text"><i class="fa-solid fa-hashtag"></i></span>
          <input type="text" id="input-id-pju" class="form-control" placeholder="Contoh: PJU-001" required autocomplete="off">
          <button type="submit" class="btn btn-primary" id="btn-cari-pju">
            <i class="fa-solid fa-magnifying-glass me-1"></i> Cari
          </button>
        </div>
      </form>

      <!-- Loading -->
      <div id="pju-loading" class="text-center py-3" style="display:none;">
        <div class="spinner-border text-primary spinner-border-sm" role="status"></div>
        <span class="text-muted ms-2 small">Mencari data PJU...</span>
      </div>

      <!-- Error -->
      <div id="pju-error" class="alert alert-danger py-2" style="display:none;" role="alert"></div>

      <!-- PJU Detail Card -->
      <div id="pju-detail-card" class="border rounded-3 p-3 mb-3">
        <h6 class="fw-bold mb-3" style="color: #1a237e;"><i class="fa-solid fa-lightbulb me-1"></i> Detail PJU</h6>
        <div class="row g-2 mb-2" id="pju-info-rows"></div>

        <div id="kwh-section" style="display:none;">
          <hr class="my-2">
          <h6 class="fw-bold mb-3" style="color: #1a237e;"><i class="fa-solid fa-bolt me-1"></i> Detail KWH</h6>
          <div class="row g-2" id="kwh-info-rows"></div>
        </div>
      </div>

      <!-- Action: proceed to report (same as barcode flow) -->
      <div id="pju-action-area" class="text-center">
        <a id="btn-laporkan" href="#" class="btn btn-primary px-4">
          <i class="fa-solid fa-file-signature me-1"></i> Laporkan Kerusakan
        </a>
      </div>

      <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted small">ID PJU biasanya tertera pada stiker tiang lampu.</div>
        <div>
          <a href="<?= base_url() ?>" class="btn btn-outline-secondary btn-sm">Kembali ke Beranda</a>
        </div>
      </div>
    </div>
  </div>
</section>

<footer class="mt-4"></footer>

<script>
  window.__INPUT_PJU_CONFIG__ = {
    lookupUrl: '<?= rtrim(base_url(), '/') ?>/api/lookup-pju',
    adminApiBase: '<?= ADMIN_API_BASE ?>'
  };
</script>
<script src="<?= base_url('assets/js/input-pju.js?v=' . time()) ?>"></script>

</body>
</html>
