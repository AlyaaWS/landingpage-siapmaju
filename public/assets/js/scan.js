// ─── Safety check: ensure HTML5-QRCode library is loaded ───
if (typeof Html5Qrcode === 'undefined') {
  console.error('CRITICAL ERROR: HTML5-QRCode library is not loaded. Please check the script tags in your HTML.');
  document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('reader');
    if (el) el.innerHTML = '<div class="alert alert-danger">Sistem pemindai gagal dimuat. Library HTML5-QRCode tidak ditemukan. Muat ulang halaman atau periksa koneksi internet.</div>';
  });
  alert('Sistem pemindai gagal dimuat. Muat ulang halaman.');
}

document.addEventListener('DOMContentLoaded', function () {
  const readerId = 'reader';
  const readerEl = document.getElementById(readerId);
  if (!readerEl) return;

  // Abort early if library never loaded
  if (typeof Html5Qrcode === 'undefined') return;

  const uploadFallback = document.getElementById('upload-fallback');

  // ─── Console-only logger (no DOM output) ───
  function logToScreen(message, level) {
    level = level || 'info';
    if (level === 'error') console.error(message);
    else if (level === 'warn') console.warn(message);
    else console.log(message);
  }

  // ─── Show the upload fallback UI ───
  function showUploadFallback() {
    if (uploadFallback) uploadFallback.style.display = 'block';
  }

  // ─── Friendly error description for common DOMException names ───
  function describeError(err) {
    var name = (err && err.name) ? err.name : 'Unknown';
    var msg = (err && err.message) ? err.message : String(err);
    var hint = '';
    if (name === 'NotAllowedError') {
      hint = 'Browser menolak akses kamera. Periksa izin kamera di pengaturan browser & pastikan halaman dibuka lewat HTTPS atau localhost.';
    } else if (name === 'NotFoundError') {
      hint = 'Tidak ditemukan perangkat kamera. Pastikan perangkat memiliki kamera yang aktif.';
    } else if (name === 'NotReadableError') {
      hint = 'Kamera sedang digunakan aplikasi lain atau hardware error. Tutup aplikasi kamera lain dan coba lagi.';
    } else if (name === 'OverconstrainedError') {
      hint = 'Constraint kamera tidak didukung perangkat ini (misal: facingMode environment). Akan dicoba fallback.';
    } else if (name === 'AbortError') {
      hint = 'Permintaan kamera dibatalkan. Coba muat ulang halaman.';
    } else if (name === 'TypeError') {
      hint = 'Kemungkinan halaman tidak diakses via HTTPS/localhost sehingga navigator.mediaDevices tidak tersedia.';
    }
    return name + ': ' + msg + (hint ? ' — ' + hint : '');
  }

  // ─── Scanner instance ───
  var scanner = null;

  // ─── WhatsApp URL interceptor ───
  // Intercepts wa.me links, extracts the ID, and redirects to local detail page.
  function handleDecodedText(decodedText) {
    logToScreen('QR Terdeteksi: ' + decodedText, 'success');

    if (decodedText.includes('wa.me')) {
      try {
        var url = new URL(decodedText);
        var textParam = url.searchParams.get('text'); // e.g. "ID PJU 36"

        if (textParam) {
          var baseUrl = '';
          var currentHost = window.location.hostname;

          if (currentHost === 'localhost' || currentHost === '127.0.0.1' || currentHost.startsWith('192.168') || currentHost.includes('ngrok-free.app')) {
            baseUrl = window.location.origin + '/lpju-sleman-test/public';
          } else {
            baseUrl = 'https://adminpju.dishubsleman.id';
          }

          var extractedText = textParam.toUpperCase();

          if (extractedText.includes('PJU')) {
            var extractedId = textParam.replace(/ID PJU /i, '').trim();
            logToScreen('WhatsApp URL terdeteksi — PJU ID: ' + extractedId + ' (dari: ' + textParam + ')', 'success');
            window.location.href = baseUrl + '/pju/detail?id=' + encodeURIComponent(extractedId);
            return;
          } else if (extractedText.includes('KWH')) {
            var extractedId = textParam.replace(/ID KWH /i, '').trim();
            logToScreen('WhatsApp URL terdeteksi — KWH ID: ' + extractedId + ' (dari: ' + textParam + ')', 'success');
            window.location.href = baseUrl + '/kwh/detail?id=' + encodeURIComponent(extractedId);
            return;
          }
        }
      } catch (e) {
        logToScreen('Gagal parse WA URL: ' + e, 'error');
      }
    }

    // Fallback for non-WA links or if parsing failed
    alert('Scanned: ' + decodedText);
  }

  function onScanSuccess(decodedText) {
    if (scanner) {
      try {
        scanner.stop().then(function () { scanner.clear(); }).catch(function () {});
      } catch (_) {}
    }
    handleDecodedText(decodedText);
  }

  // ─── Camera-based scanner using Html5Qrcode (lower-level API for more control) ───
  function startScannerWithCameraId(cameraId) {
    logToScreen('Memulai scanner dengan kamera: ' + cameraId, 'info');
    readerEl.innerHTML = '';
    scanner = new Html5Qrcode(readerId);
    scanner.start(
      cameraId,
      { fps: 10, qrbox: { width: 250, height: 250 } },
      onScanSuccess,
      function () { /* ignore per-frame misses */ }
    ).then(function () {
      logToScreen('Kamera berhasil dibuka ✓', 'success');
    }).catch(function (err) {
      logToScreen('Gagal start kamera (' + cameraId + '): ' + describeError(err), 'error');
      showUploadFallback();
    });
  }

  // ─── Attempt camera with progressive fallback ───
  // Strategy:
  //   1. Try rear camera via facingMode constraint
  //   2. If that fails, enumerate devices and pick any camera
  //   3. If everything fails, show upload fallback
  function attemptCamera() {
    logToScreen('Memeriksa dukungan kamera browser…', 'info');

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      logToScreen('navigator.mediaDevices TIDAK tersedia. Halaman harus diakses via HTTPS atau localhost.', 'error');
      readerEl.innerHTML = '<div class="text-danger p-3">Browser tidak mendukung akses kamera pada origin ini. Gunakan HTTPS atau localhost.</div>';
      showUploadFallback();
      return;
    }

    logToScreen('Mencoba akses kamera belakang (environment)…', 'info');

    // Step 1: try environment (rear) camera
    navigator.mediaDevices.getUserMedia({ video: { facingMode: { exact: 'environment' } } })
      .then(function (stream) {
        stream.getTracks().forEach(function (t) { t.stop(); });
        logToScreen('Kamera belakang tersedia, mengambil daftar perangkat…', 'info');
        return pickAndStart('environment');
      })
      .catch(function (err1) {
        logToScreen('Kamera belakang gagal: ' + describeError(err1), 'warn');
        logToScreen('Fallback: mencoba kamera APA SAJA…', 'info');

        // Step 2: try ANY camera
        navigator.mediaDevices.getUserMedia({ video: true })
          .then(function (stream) {
            stream.getTracks().forEach(function (t) { t.stop(); });
            logToScreen('Kamera generik tersedia.', 'info');
            return pickAndStart(null);
          })
          .catch(function (err2) {
            logToScreen('Semua upaya kamera gagal: ' + describeError(err2), 'error');
            readerEl.innerHTML =
              '<div class="text-danger p-3">' +
                '<strong>Kamera tidak dapat diakses.</strong><br>' +
                '<span class="small">' + describeError(err2) + '</span>' +
              '</div>' +
              '<button id="retry-camera" class="btn btn-primary btn-sm mt-2">Coba Lagi</button>';
            showUploadFallback();

            var retry = document.getElementById('retry-camera');
            if (retry) {
              retry.addEventListener('click', function () {
                readerEl.innerHTML = '<div class="text-muted p-3">Mencoba ulang…</div>';
                attemptCamera();
              });
            }
          });
      });
  }

  // ─── Enumerate cameras and start scanning ───
  function pickAndStart(preferredFacing) {
    Html5Qrcode.getCameras().then(function (cameras) {
      if (!cameras || cameras.length === 0) {
        logToScreen('Tidak ditemukan perangkat kamera.', 'error');
        showUploadFallback();
        return;
      }

      logToScreen('Ditemukan ' + cameras.length + ' kamera: ' + cameras.map(function (c) { return c.label || c.id; }).join(', '), 'info');

      var chosen = cameras[0]; // default: first camera

      if (preferredFacing === 'environment' && cameras.length > 1) {
        // Heuristic: pick the camera whose label contains "back", "rear", "environment", or "belakang"
        for (var i = 0; i < cameras.length; i++) {
          var label = (cameras[i].label || '').toLowerCase();
          if (/(back|rear|environment|belakang)/.test(label)) {
            chosen = cameras[i];
            break;
          }
        }
        // If no label matched, on mobile the last camera is often the rear one
        if (chosen === cameras[0] && cameras.length >= 2) {
          chosen = cameras[cameras.length - 1];
        }
      }

      logToScreen('Menggunakan kamera: ' + (chosen.label || chosen.id), 'info');
      startScannerWithCameraId(chosen.id);
    }).catch(function (err) {
      logToScreen('getCameras() gagal: ' + describeError(err), 'error');
      showUploadFallback();
    });
  }

  // ─── File upload fallback: decode QR from an image ───
  var fileInput = document.getElementById('qr-input-file');
  var uploadResult = document.getElementById('upload-result');
  if (fileInput) {
    fileInput.addEventListener('change', function (e) {
      var file = e.target.files && e.target.files[0];
      if (!file) return;

      logToScreen('Memproses gambar: ' + file.name, 'info');
      if (uploadResult) uploadResult.innerHTML = '<div class="text-muted small">Memproses gambar…</div>';

      var html5Qr = new Html5Qrcode('reader-file-temp');
      // We need a temporary hidden element for the library
      var tempDiv = document.getElementById('reader-file-temp');
      if (!tempDiv) {
        tempDiv = document.createElement('div');
        tempDiv.id = 'reader-file-temp';
        tempDiv.style.display = 'none';
        document.body.appendChild(tempDiv);
      }

      html5Qr.scanFile(file, true)
        .then(function (decodedText) {
          logToScreen('QR dari gambar: ' + decodedText, 'success');
          if (uploadResult) {
            uploadResult.innerHTML =
              '<div class="alert alert-success py-2">' +
                '<strong>Hasil:</strong> ' + decodedText.replace(/</g, '&lt;').replace(/>/g, '&gt;') +
              '</div>';
          }
          fileInput.value = '';
          handleDecodedText(decodedText);
        })
        .catch(function (err) {
          logToScreen('QR Code tidak terdeteksi pada gambar. Pastikan gambar jelas dan tidak terpotong.', 'error');
          if (uploadResult) {
            uploadResult.innerHTML =
              '<div class="alert alert-warning py-2">QR Code tidak terdeteksi pada gambar. Pastikan gambar jelas dan tidak terpotong.</div>';
          }
          fileInput.value = '';
        });
    });
  }

  // ─── Kick off camera attempt ───
  attemptCamera();

  // ─── Back button: stop camera before navigating ───
  var backBtn = document.getElementById('btn-back');
  if (backBtn) {
    backBtn.addEventListener('click', function (e) {
      e.preventDefault();
      var href = this.getAttribute('href');
      if (scanner && typeof scanner.stop === 'function') {
        scanner.stop().then(function () { scanner.clear(); }).finally(function () { window.location.href = href; });
      } else {
        window.location.href = href;
      }
    });
  }
});
