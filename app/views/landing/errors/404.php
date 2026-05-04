<?php
// Pure HTML/CSS error view — intentionally simple and standalone.
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: #334155;
        }
        .error-container {
            text-align: center;
            background: #ffffff;
            padding: 50px 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            max-width: 500px;
            width: 90%;
            border-top: 5px solid #1e40af;
        }
        .error-code {
            font-size: 80px;
            font-weight: 800;
            color: #1e40af;
            margin: 0;
            line-height: 1;
        }
        .error-title {
            font-size: 24px;
            font-weight: 700;
            margin: 20px 0 10px;
            color: #1e293b;
        }
        .error-message {
            font-size: 15px;
            color: #64748b;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .btn-group {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-block;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.2s;
            border: none;
        }
        .btn-home {
            background-color: #1e293b;
            color: #ffffff;
        }
        .btn-home:hover {
            background-color: #0f172a;
            transform: translateY(-2px);
        }
        .btn-back {
            background-color: #e2e8f0;
            color: #334155;
        }
        .btn-back:hover {
            background-color: #cbd5e1;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="error-code">404</h1>
        <h2 class="error-title">Halaman Tidak Ditemukan</h2>
        <p class="error-message">
            Maaf, halaman yang Anda cari tidak tersedia atau telah dipindahkan.
            Silakan periksa kembali URL yang Anda masukkan.
        </p>
        <div class="btn-group">
            <a href="<?= rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\') ?>/home" class="btn btn-home">Kembali ke Dashboard</a>
            <button onclick="history.back()" class="btn btn-back">Kembali ke Halaman Sebelumnya</button>
        </div>
    </div>
</body>
</html>
