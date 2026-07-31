(function() {
    // index.php config
    const landingCfg = document.getElementById('landingConfig');
    if (landingCfg) {
        window.__DASHBOARD_DATA__ = JSON.parse(landingCfg.getAttribute('data-dashboard') || '{}');
    }
    
    // input-pju.php & index.php config
    const inputCfg = document.getElementById('inputPjuConfig');
    if (inputCfg) {
        window.__INPUT_PJU_CONFIG__ = JSON.parse(inputCfg.getAttribute('data-inputconfig') || '{}');
    }

    // scan.php config
    const scanCfg = document.getElementById('scanConfig');
    if (scanCfg) {
        window.__SCAN_CONFIG__ = JSON.parse(scanCfg.getAttribute('data-scanconfig') || '{}');
    }
})();
