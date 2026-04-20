/**
 * Input PJU – manual ID lookup and redirect to admin detail page.
 * Same flow as QR scan: after finding the PJU, opens the admin web detail/report page.
 */
document.addEventListener("DOMContentLoaded", function () {
  var config = window.__INPUT_PJU_CONFIG__ || {};
  var lookupUrl = config.lookupUrl || "";

  var form = document.getElementById("form-lookup-pju");
  var inputField = document.getElementById("input-id-pju");
  var btnCari = document.getElementById("btn-cari-pju");
  var loading = document.getElementById("pju-loading");
  var errorBox = document.getElementById("pju-error");
  var detailCard = document.getElementById("pju-detail-card");
  var actionArea = document.getElementById("pju-action-area");
  var pjuRows = document.getElementById("pju-info-rows");
  var kwhSection = document.getElementById("kwh-section");
  var kwhRows = document.getElementById("kwh-info-rows");
  var btnLaporkan = document.getElementById("btn-laporkan");

  // Fields to display for PJU (label → key)
  var pjuFields = [
    ["ID PJU", "id_pju"],
    ["Nama LPJU", "nama_lpju"],
    ["Daya", "daya_lpju"],
    ["Lokasi / Alamat", "alamat"],
    ["Kecamatan", "kecamatan"],
    ["Desa", "desa"],
    ["Koordinat", "latitude", "longitude"],
    ["Status", "status"],
  ];

  var kwhFields = [
    ["ID KWH", "id_kwh"],
    ["Nama KWH", "nama_kwh"],
    ["Daya KWH", "daya_kwh"],
    ["Lokasi KWH", "alamat_kwh"],
  ];

  function escapeHTML(str) {
    if (!str) return "-";
    var div = document.createElement("div");
    div.appendChild(document.createTextNode(String(str)));
    return div.innerHTML;
  }

  function showLoading(show) {
    loading.style.display = show ? "block" : "none";
  }

  function showError(msg) {
    if (msg) {
      errorBox.textContent = msg;
      errorBox.style.display = "block";
    } else {
      errorBox.style.display = "none";
    }
  }

  function hideResults() {
    detailCard.style.display = "none";
    actionArea.style.display = "none";
    showError("");
  }

  function renderInfoRows(container, fields, data) {
    container.innerHTML = "";
    fields.forEach(function (f) {
      var label = f[0];
      var value = "";
      if (f.length === 3) {
        // Composite field (e.g. latitude + longitude)
        var v1 = data[f[1]],
          v2 = data[f[2]];
        value = v1 && v2 ? v1 + ", " + v2 : "-";
      } else {
        var raw = data[f[1]];
        value =
          raw !== null && raw !== undefined && raw !== "" ? String(raw) : "-";
        if (f[1] === "daya_lpju" || f[1] === "daya_kwh") {
          value = value !== "-" ? value + " W" : "-";
        }
      }
      var col = document.createElement("div");
      col.className = "col-sm-6 mb-1";
      col.innerHTML =
        '<div class="info-label">' +
        escapeHTML(label) +
        "</div>" +
        '<div class="info-value">' +
        escapeHTML(value) +
        "</div>";
      container.appendChild(col);
    });
  }

  /**
   * Build the admin detail URL (same logic as scan.js handleDecodedText)
   */
  function buildAdminUrl(secureId, type) {
    var currentHost = window.location.hostname;
    var baseUrl = "https://adminpju.dishubsleman.id";

    if (
      currentHost === "localhost" ||
      currentHost === "127.0.0.1" ||
      currentHost.includes("ngrok")
    ) {
      baseUrl = window.location.origin + "/lpju-sleman-test/public";
    }

    if (type === "pju") {
      return baseUrl + "/pju/detail?id=" + encodeURIComponent(secureId);
    }
    return baseUrl + "/kwh/detail?id=" + encodeURIComponent(secureId);
  }

  function doLookup() {
    var idPju = (inputField.value || "").trim();
    if (!idPju) {
      showError("ID PJU harus diisi.");
      return;
    }

    hideResults();
    showLoading(true);
    btnCari.disabled = true;

    var url = lookupUrl + "?id_pju=" + encodeURIComponent(idPju);

    fetch(url, { method: "GET", cache: "no-store" })
      .then(function (resp) {
        var contentType = resp.headers.get("content-type") || "";
        var isJson = contentType.indexOf("application/json") !== -1;

        if (!resp.ok) {
          if (isJson) {
            return resp.json().then(function (body) {
              throw new Error(body.message || "Data tidak ditemukan.");
            });
          }
          throw new Error(
            resp.status === 404
              ? "API endpoint tidak ditemukan. Periksa konfigurasi server."
              : resp.status === 500
                ? "Terjadi kesalahan server. Silakan coba lagi nanti."
                : "Terjadi kesalahan (HTTP " + resp.status + ")",
          );
        }

        if (!isJson) {
          throw new Error(
            "Server mengembalikan respons yang tidak valid. Hubungi administrator.",
          );
        }

        return resp.json();
      })
      .then(function (json) {
        showLoading(false);
        btnCari.disabled = false;

        if (!json.status || !json.data || !json.data.pju) {
          showError(json.message || "Data PJU tidak ditemukan.");
          return;
        }

        var pju = json.data.pju;
        var kwh = json.data.kwh;

        // Render PJU details
        renderInfoRows(pjuRows, pjuFields, pju);

        // Render KWH details if present
        if (kwh) {
          renderInfoRows(kwhRows, kwhFields, kwh);
          kwhSection.style.display = "block";
        } else {
          kwhSection.style.display = "none";
        }

        detailCard.style.display = "block";
        actionArea.style.display = "block";

        // Set the report link to admin web detail page (same as QR scan flow)
        var secureId = pju.secure_id || pju.id_pju || idPju;
        btnLaporkan.href = buildAdminUrl(secureId, "pju");
      })
      .catch(function (err) {
        showLoading(false);
        btnCari.disabled = false;
        showError(err.message || "Terjadi kesalahan jaringan. Coba lagi.");
        console.error("lookupPju error:", err);
      });
  }

  if (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      doLookup();
    });
  }

  // Reset modal state when reopened
  var modal = document.getElementById("modalInputPju");
  if (modal) {
    modal.addEventListener("hidden.bs.modal", function () {
      inputField.value = "";
      hideResults();
      showLoading(false);
    });
  }
});
