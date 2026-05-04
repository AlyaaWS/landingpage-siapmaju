<?php
// Pure HTML/CSS error view — intentionally simple and standalone.
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Terjadi Kesalahan</title>
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
            border-top: 5px solid #ef4444;
        }
        .error-code {
            font-size: 80px;
            font-weight: 800;
            color: #ef4444;
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
        .btn-home {
            display: inline-block;
            background-color: #1e293b;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: background-color 0.2s, transform 0.2s;
        }
        .btn-home:hover {
            background-color: #0f172a;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="error-code">500</h1>
        <h2 class="error-title">Oops! Terjadi Kesalahan Sistem</h2>
        <p class="error-message">
            Mohon maaf, server kami sedang mengalami gangguan atau memproses sesuatu yang tidak terduga. Tim teknis kami telah mencatat masalah ini dan akan segera memperbaikinya.
        </p>
    </div>
</body>
</html>
