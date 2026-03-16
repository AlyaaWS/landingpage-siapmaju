<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?= $judul ?? 'SIAP MAJU'; ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="<?= base_url('assets/css/landing.css') ?>">

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
				<a href="#alur" class="btn btn-primary px-4">Scan Barcode Sekarang</a>
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
							<h6 class="fw-semibold">2. Scan dengan HP</h6>
						</div>
						<p class="card-desc text-muted small mt-3 mt-md-2 mt-auto">Klik tombol "Scan Barcode Sekarang" pada web ini, lalu pindai barcode menggunakan kamera HP Anda.</p>
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
					<h2 class="display-6 mb-1">1500</h2>
					<p class="small text-muted">Tahun Ini</p>
				</div>
			</div>

			<div class="col-md-4 mb-3 mb-md-0">
				<div class="card-stat h-100 d-flex flex-column justify-content-center text-center p-4">
					<h6 class="text-muted mb-2">Sedang Diperbaiki</h6>
					<h2 class="display-6 mb-1">75</h2>
				</div>
			</div>

			<div class="col-md-4 mb-3 mb-md-0">
				<div class="card-stat h-100 d-flex flex-column justify-content-center text-center p-4">
					<h6 class="text-muted mb-2">Berhasil Diperbaiki</h6>
					<h2 class="display-6 mb-1">1500</h2>
					<p class="small text-muted">20% Sukses</p>
				</div>
			</div>
		</div>

		<div class="row g-4">
			<div class="col-md-4">
				<div class="chart-card h-100 p-4">
					<canvas id="chartDoughnut"></canvas>
				</div>
			</div>
			<div class="col-md-4">
				<div class="chart-card h-100 p-4">
					<canvas id="chartBar"></canvas>
				</div>
			</div>
			<div class="col-md-4">
				<div class="chart-card h-100 p-4">
					<canvas id="chartPie"></canvas>
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
						<input type="text" class="form-control rounded-3 mb-2 mb-md-0 me-md-2 w-100 flex-grow-1" placeholder="Masukan nomer tiket atau no whatsapp anda ..." aria-label="Nomor tiket atau WhatsApp">
						<button type="submit" class="btn btn-primary rounded-3 w-auto text-nowrap">Cek Status</button>
					</div>
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
Copyright © 2024 Dinas Perhubungan Kabupaten Sleman.
</div>

</footer>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="<?= base_url('assets/js/landing.js') ?>"></script>

</body>
</html>
