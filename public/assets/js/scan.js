document.addEventListener('DOMContentLoaded', function () {
  const readerId = 'reader';
  const readerEl = document.getElementById(readerId);
  if (!readerEl) return;

  const scannerConfig = {
    fps: 10,
    qrbox: { width: 250, height: 250 },
    // prefer back camera if available
    videoConstraints: { facingMode: { ideal: 'environment' } },
    experimentalFeatures: { useBarCodeDetectorIfSupported: true }
  };

  // Create scanner UI
  let scanner = null;

  const renderScanner = () => {
    try {
      scanner = new Html5QrcodeScanner(readerId, scannerConfig, false);

      const onScanSuccess = (decodedText, decodedResult) => {
        // clear scanner UI and show result
        try {
          scanner.clear().then(() => {
            console.log('Scanner cleared after success');
          }).catch(() => console.warn('Scanner clear failed'));
        } catch (e) {
          console.warn('Error clearing scanner', e);
        }

        console.log('Decoded:', decodedText);
        alert('Barcode terdeteksi: ' + decodedText);
      };

      const onScanFailure = (error) => {
        // ignore non-fatal scan errors
        // console.debug('scan failure', error);
      };

      scanner.render(onScanSuccess, onScanFailure);

    } catch (err) {
      console.error('Failed to initialize scanner', err);
      readerEl.innerHTML = '<div class="text-danger p-3">Kamera diblokir atau halaman tidak diakses via HTTPS/localhost. Pastikan memberikan izin akses kamera.</div>';
    }
  };

  // Try to proactively request permission (may be blocked by browser policies)
  if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
    navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } } })
      .then(stream => {
        // immediately stop the test stream; Html5QrcodeScanner will open its own stream
        stream.getTracks().forEach(t => t.stop());
        renderScanner();
      })
      .catch(err => {
        // Provide a manual permission request button and clearer guidance
        console.warn('getUserMedia test failed:', err);
        const msg = document.createElement('div');
        msg.className = 'p-3';
        const short = (err && err.name) ? err.name : 'Kamera tidak tersedia';
        msg.innerHTML = `
          <div class="text-warning">${short}: Tidak dapat mengakses kamera secara otomatis.</div>
          <div class="small text-muted">Jika Anda sudah memberi izin, coba muat ulang halaman atau periksa pengaturan izin browser.</div>
          <button id="request-camera" class="btn btn-primary btn-sm mt-2">Izinkan Kamera</button>
          <div class="mt-2 small text-muted">Jika masih gagal, pastikan membuka halaman lewat <strong>http://localhost</strong> atau <strong>HTTPS</strong>, dan periksa pengaturan privasi browser.</div>
        `;
        // clear reader and show the message
        readerEl.innerHTML = '';
        readerEl.appendChild(msg);

        const btn = document.getElementById('request-camera');
        if (btn) {
          btn.addEventListener('click', function () {
            // Try again to get permission when user explicitly clicks
            navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } } })
              .then(stream => {
                stream.getTracks().forEach(t => t.stop());
                renderScanner();
              })
              .catch(e => {
                console.error('Manual getUserMedia failed:', e);
                msg.innerHTML = `<div class="text-danger">Izin kamera gagal: ${e && e.message ? e.message : e}</div>` + msg.innerHTML;
              });
          });
        }
      });
  } else {
    // No media support
    readerEl.innerHTML = '<div class="text-danger p-3">Perangkat Anda tidak mendukung akses kamera lewat browser.</div>';
  }

  // Ensure back button stops camera before navigating
  const backBtn = document.getElementById('btn-back');
  if (backBtn) {
    backBtn.addEventListener('click', function (e) {
      e.preventDefault();
      const href = this.getAttribute('href');
      if (scanner && typeof scanner.clear === 'function') {
        scanner.clear().finally(() => { window.location.href = href; });
      } else {
        window.location.href = href;
      }
    });
  }
});
