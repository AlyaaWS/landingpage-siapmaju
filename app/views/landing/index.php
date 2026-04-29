<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">

<title><?= $judul ?? 'SIAP MAJU'; ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha384-iw3OoTErCYJJB9mCa8LNS2hbsQ7M3C0EpIsO/H5+EGAkPGc6rk+V8i04oW/K5xq0" crossorigin="anonymous">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="<?= base_url('assets/css/landing.css?v=' . time()) ?>">

<style>
/* small smooth animation for modal appearance */
.modal.fade .modal-dialog{
	transform: translateY(-10px);
	transition: transform .18s ease-out, opacity .18s ease-out;
}
.modal.show .modal-dialog{
	transform: translateY(0);
}
</style>

</head>
<body>


<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
	<div class="container">
		<a class="navbar-brand d-flex align-items-center gap-2" href="<?= base_url() ?>">
			<img src="<?= asset_url('img/logo.png') ?>" alt="Logo SIAP MAJU" class="logo-img">
			<span class="fw-semibold">DISHUB KABUPATEN SLEMAN</span>
		</a>

		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
			<span class="navbar-toggler-icon"></span>
		</button>

		<div class="collapse navbar-collapse justify-content-end" id="menu">
			<ul class="navbar-nav align-items-lg-center">
				<li class="nav-item"><a class="nav-link active" href="#">Beranda</a></li>
				<li class="nav-item"><a class="nav-link" href="#alur">Buat Laporan</a></li>
				<li class="nav-item"><a class="nav-link" href="#cek-status">Cek Status</a></li>
			</ul>
		</div>
	</div>
</nav>

<!-- spacer to account for fixed navbar -->
<div class="pt-5"></div>

<!-- HERO -->
<?php $heroImg = asset_url('img/hero.png'); ?>
<section class="hero-section d-flex align-items-center py-4 py-md-5" style="background-image: linear-gradient(to right, rgba(63,81,181,.25), rgba(26,35,126,.25)), url('<?= $heroImg ?>'); background-size: cover; background-position: center;">
	<div class="container">
		<div class="row align-items-center">
			<div class="col-lg-6 text-center text-md-start">
				<h1 class="display-5 display-md-3 fw-bold mb-3">SIAP MAJU</h1>
				<p class="lead text-white-75 mb-4">Portal resmi aduan PJU Dishub Sleman. Laporkan tiang atau lampu jalan rusak di lingkungan Anda dengan mudah dan pantau statusnya.</p>
				<div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-start">
					<a href="<?= base_url('scan') ?>" class="btn btn-primary px-4"><i class="fa-solid fa-qrcode me-1"></i> Scan Barcode</a>
					<button type="button" class="btn btn-outline-light px-4" data-bs-toggle="modal" data-bs-target="#modalInputPju"><i class="fa-solid fa-keyboard me-1"></i> Input ID PJU</button>
				</div>
			</div>
		</div>
	</div>
</section>



<!-- ALUR -->
<section id="alur" class="py-5 py-md-6">
	<div class="container">
		<div class="text-center mb-3 mb-md-4">
			<h2 class="section-title">Alur Pelaporan</h2>
		</div>

		<div class="row g-4">
				<div class="col-lg-3 col-md-6 mb-3 mb-md-0">
					<div class="card-alur h-100 d-flex flex-column p-4 text-center">
						<div class="card-head d-flex flex-column align-items-center">
							<div class="icon-box mb-3">
								<i class="fa-solid fa-qrcode fa-lg"></i>
							</div>
							<h6 class="fw-semibold">1. Cari Barcode</h6>
						</div>
						<p class="card-desc text-muted small mt-3 mt-md-2 mt-auto">Temukan stiker barcode SIAP MAJU yang menempel pada tiang PJU yang bermasalah.</p>
					</div>
				</div>

				<div class="col-lg-3 col-md-6 mb-3 mb-md-0">
					<div class="card-alur h-100 d-flex flex-column p-4 text-center">
						<div class="card-head d-flex flex-column align-items-center">
							<div class="icon-box mb-3"><i class="fa-solid fa-mobile-screen fa-lg"></i></div>
							<h6 class="fw-semibold">2. Scan atau Input ID</h6>
						</div>
						<p class="card-desc text-muted small mt-3 mt-md-2 mt-auto">Pindai barcode menggunakan kamera HP Anda, atau masukkan ID PJU secara manual jika barcode tidak tersedia.</p>
					</div>
				</div>

				<div class="col-lg-3 col-md-6 mb-3 mb-md-0">
					<div class="card-alur h-100 d-flex flex-column p-4 text-center">
						<div class="card-head d-flex flex-column align-items-center">
							<div class="icon-box mb-3"><i class="fa-solid fa-file-signature fa-lg"></i></div>
							<h6 class="fw-semibold">3. Isi Laporan</h6>
						</div>
						<p class="card-desc text-muted small mt-3 mt-md-2 mt-auto">Ceritakan detail kerusakan dan unggah foto lokasi.</p>
					</div>
				</div>

				<div class="col-lg-3 col-md-6 mb-3 mb-md-0">
					<div class="card-alur h-100 d-flex flex-column p-4 text-center">
						<div class="card-head d-flex flex-column align-items-center">
							<div class="icon-box mb-3"><i class="fa-solid fa-paper-plane fa-lg"></i></div>
							<h6 class="fw-semibold">4. Kirim & Pantau</h6>
						</div>
						<p class="card-desc text-muted small mt-3 mt-md-2 mt-auto">Kirim laporan dan simpan nomor tiket untuk mengecek status.</p>
					</div>
				</div>
			</div>
</section>

<!-- TRANSPARANSI -->
<section class="section-gray py-4 py-md-5">
	<div class="container">
		<div class="text-center mb-3 mb-md-4">
			<h2 class="section-title">Transparansi Kinerja PJU</h2>
		</div>

		<div class="row g-4 mb-4">
			<div class="col-md-4 mb-3 mb-md-0">
				<div class="card-stat h-100 d-flex flex-column justify-content-center text-center p-4">
					<h6 class="text-muted mb-2">Total Laporan</h6>
					<h2 class="display-6 mb-1"><?= htmlspecialchars($totalLaporan ?? 0) ?></h2>
					<p class="small text-muted">Tahun Ini</p>
				</div>
			</div>

			<div class="col-md-4 mb-3 mb-md-0">
				<div class="card-stat h-100 d-flex flex-column justify-content-center text-center p-4">
					<h6 class="text-muted mb-2">Sedang Diperbaiki</h6>
					<h2 class="display-6 mb-1"><?= htmlspecialchars($sedangDiperbaiki ?? 0) ?></h2>
				</div>
			</div>

			<div class="col-md-4 mb-3 mb-md-0">
				<div class="card-stat h-100 d-flex flex-column justify-content-center text-center p-4">
					<h6 class="text-muted mb-2">Berhasil Diperbaiki</h6>
					<h2 class="display-6 mb-1"><?= htmlspecialchars($berhasilDiperbaiki ?? 0) ?></h2>
					<p class="small text-muted"><?= htmlspecialchars($persenSukses ?? 0) ?>% Sukses</p>
				</div>
			</div>
		</div>

		<div class="row g-4">
			<div class="col-md-4">
				<div class="chart-card h-100 d-flex flex-column p-4">
					<div class="flex-grow-1 d-flex align-items-center justify-content-center">
						<canvas id="chartDoughnut"></canvas>
					</div>
					<div class="mt-4 text-center fw-bold text-secondary">
						Distribusi LPJU Berdasarkan Daya
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="chart-card h-100 d-flex flex-column p-4">
					<div class="flex-grow-1 d-flex align-items-center justify-content-center">
						<canvas id="chartBar"></canvas>
					</div>
					<div class="mt-4 text-center fw-bold text-secondary">
						Rekapitulasi Total Aset & Daya
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="chart-card h-100 d-flex flex-column p-4">
					<div class="flex-grow-1 d-flex align-items-center justify-content-center">
						<canvas id="chartPie"></canvas>
					</div>
					<div class="mt-4 text-center fw-bold text-secondary">
						Distribusi LPJU per Nama
					</div>
				</div>
			</div>
		</div>
	</div>
</section>



<!-- CEK STATUS -->
<section id="cek-status" class="py-4 py-md-5">
	<div class="container">
		<div class="text-center mb-3 mb-md-4">
			<h2 class="section-title">Cek Status Laporan</h2>
			<p class="text-muted mb-0">Sudah lapor? cek perkembangan laporan anda di sini.</p>
		</div>

		<div class="row justify-content-center mt-4">
			<div class="col-lg-8">
				<form class="cek-box">
					<!-- Responsive input-group: stacked on small screens, inline on md+ -->
					<div class="input-group flex-column flex-md-row flex-md-nowrap align-items-center w-100">
						<input id="nomor_tiket" name="nomor_tiket" type="text" class="form-control rounded-3 mb-2 mb-md-0 me-md-2 w-100 flex-grow-1" placeholder="Masukan nomer tiket anda ..." aria-label="Nomor tiket atau WhatsApp">
						<button id="btn_cek_status" type="submit" class="btn btn-primary rounded-3 w-auto text-nowrap">Cek Status</button>
					</div>
					<div id="hasil_status" class="mt-3"></div>
				</form>
			</div>
		</div>
	</div>
</section>



<!-- FOOTER -->
<footer>

<div class="footer-top">

<div class="container">

<div class="row align-items-center">

<div class="col-md-2 text-center text-md-start mb-3 mb-md-0">
<img src="<?= asset_url('img/sleman-logo.png') ?>" width="120">
</div>

<div class="col-md-7 text-center text-md-start mb-3 mb-md-0">

<h5>DISHUB SLEMAN</h5>

<p>
Address: Dinas Perhubungan Sleman, Yogyakarta
</p>

<p>Tel: 0821 7826 7737</p>

<p>Email: dishub@sleman.go.id</p>

</div>

<div class="col-md-3 text-center text-md-start mb-3 mb-md-0">

<h6>Media</h6>

<div class="social">

<a href="#"><i class="fa-brands fa-instagram"></i> IG</a>

<a href="#"><i class="fa-brands fa-facebook"></i> FB</a>

<a href="#"><i class="fa-brands fa-twitter"></i> Twitter</a>

</div>

</div>

</div>

</div>

</div>

<div class="footer-bottom">
Copyright © 2026 Dinas Perhubungan Kabupaten Sleman.
</div>

</footer>

<!-- Modal Input ID PJU -->
<div id="modalInputPju" class="modal fade" tabindex="-1" aria-labelledby="modalInputPjuLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header" style="background:#1a237e;color:#fff;">
				<h5 class="modal-title" id="modalInputPjuLabel"><i class="fa-solid fa-keyboard me-1"></i> Input Data PJU</h5>
				<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<p class="text-muted small mb-3">Masukkan ID PJU yang tertera pada tiang lampu untuk melihat detail dan membuat laporan.</p>
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
				<!-- PJU Detail -->
				<div id="pju-detail-card" class="border rounded-3 p-3 mb-3" style="display:none;">
					<h6 class="fw-bold mb-2" style="color:#1a237e;"><i class="fa-solid fa-lightbulb me-1"></i> Detail PJU</h6>
					<div class="row g-2 mb-2" id="pju-info-rows"></div>
					<div id="kwh-section" style="display:none;">
						<hr class="my-2">
						<h6 class="fw-bold mb-2" style="color:#1a237e;"><i class="fa-solid fa-bolt me-1"></i> Detail KWH</h6>
						<div class="row g-2" id="kwh-info-rows"></div>
					</div>
				</div>
				<!-- Action -->
				<div id="pju-action-area" class="text-center" style="display:none;">
					<a id="btn-laporkan" href="#" class="btn btn-primary px-4" target="_blank">
						<i class="fa-solid fa-file-signature me-1"></i> Laporkan Kerusakan
					</a>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Modal untuk menampilkan hasil cek status -->
<div id="modal_hasil_status" class="modal fade" tabindex="-1" aria-labelledby="modalHasilLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalHasilLabel">Hasil Cek Status</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body" id="modal_hasil_status_body"></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
			</div>
		</div>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>

<script>
    // Pass PHP data to global JS object safely using the exact variables returned by DashboardModel
    window.__DASHBOARD_DATA__ = {
        summary: {
            total_lpju: <?= $barData[0] ?? 0 ?>,
            total_daya: <?= $barData[1] ?? 0 ?> 
        },
        doughnutLabels: <?= json_encode($doughnutLabels ?? []) ?>,
        doughnutData: <?= json_encode($doughnutData ?? []) ?>,
        pieLabels: <?= json_encode($pieLabels ?? []) ?>,
        pieData: <?= json_encode($pieData ?? []) ?>,
        // FIXED: Using rtrim to ensure a clean, absolute base path without double slashes
        apiCekStatusUrl: '<?= rtrim(base_url(), '/') ?>/api/cek-status'
    };

    window.__INPUT_PJU_CONFIG__ = {
        lookupUrl: '<?= rtrim(base_url(), '/') ?>/api/lookup-pju',
        adminApiBase: '<?= ADMIN_API_BASE ?>'
    };
</script>

<script src="<?= base_url('assets/js/landing.js?v=' . time()) ?>"></script>
<script src="<?= base_url('assets/js/input-pju.js?v=' . time()) ?>"></script>

</body>
</html>
