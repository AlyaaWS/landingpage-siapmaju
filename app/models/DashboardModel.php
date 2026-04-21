<?php

class DashboardModel extends Model
{
    /**
     * KPI metrics from perbaikan_pju for the current year.
     */
    public function getPerbaikanMetrics(): array
    {
        $year = date('Y');

        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*) AS total_laporan,
                SUM(CASE WHEN status_perbaikan = 'Belum dikerjakan' THEN 1 ELSE 0 END) AS sedang_diperbaiki,
                SUM(CASE WHEN status_perbaikan = 'Sukses' THEN 1 ELSE 0 END) AS berhasil_diperbaiki
             FROM perbaikan_pju
             WHERE YEAR(created_at) = ?"
        );
        $stmt->bind_param('i', $year);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $total   = (int) ($row['total_laporan'] ?? 0);
        $pending = (int) ($row['sedang_diperbaiki'] ?? 0);
        $success = (int) ($row['berhasil_diperbaiki'] ?? 0);

        return [
            'totalLaporan'       => $total,
            'sedangDiperbaiki'   => $pending,
            'berhasilDiperbaiki' => $success,
            'persenSukses'       => $total > 0 ? round(($success / $total) * 100) : 0,
        ];
    }

    /**
     * Chart data aggregated from aset_lpju.
     */
    public function getAsetMetrics(): array
    {
        // Doughnut: group by daya_lpju
        $doughnutLabels = [];
        $doughnutData   = [];
        $res = $this->db->query(
            "SELECT daya_lpju, COUNT(*) AS total FROM aset_lpju GROUP BY daya_lpju ORDER BY daya_lpju ASC"
        );
        while ($row = $res->fetch_assoc()) {
            $doughnutLabels[] = $row['daya_lpju'] . ' W';
            $doughnutData[]   = (int) $row['total'];
        }
        $res->free();

        // Bar: total count + total daya
        $res = $this->db->query(
            "SELECT COUNT(id) AS total_lpju, COALESCE(SUM(daya_lpju), 0) AS total_daya FROM aset_lpju"
        );
        $barRow  = $res->fetch_assoc();
        $barData = [(int) $barRow['total_lpju'], (int) $barRow['total_daya']];
        $res->free();

        // Pie: group by nama_lpju
        $pieLabels = [];
        $pieData   = [];
        $res = $this->db->query(
            "SELECT nama_lpju, COUNT(*) AS total FROM aset_lpju GROUP BY nama_lpju ORDER BY total DESC"
        );
        while ($row = $res->fetch_assoc()) {
            $pieLabels[] = $row['nama_lpju'];
            $pieData[]   = (int) $row['total'];
        }
        $res->free();

        return [
            'doughnutLabels' => $doughnutLabels,
            'doughnutData'   => $doughnutData,
            'barData'        => $barData,
            'pieLabels'      => $pieLabels,
            'pieData'        => $pieData,
        ];
    }

    /**
     * Look up PJU asset + related KWH data by id_pju.
     * Returns null if not found.
     */
    public function lookupPju(string $idPju): ?array
    {
        // Look up PJU asset
        $stmt = $this->db->prepare(
            "SELECT * FROM aset_lpju WHERE id_pju = ? LIMIT 1"
        );
        $stmt->bind_param('s', $idPju);
        $stmt->execute();
        $pju = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$pju) {
            return null;
        }

        // Compute secure_id (encrypted DB primary id) for admin redirects
        try {
            require_once __DIR__ . '/../helpers/SecurityHelper.php';
            if (!empty($pju['id'])) {
                $pju['secure_id'] = SecurityHelper::encrypt((string) $pju['id']);
            }
        } catch (\Throwable $e) {
            error_log('lookupPju: failed to compute secure_id: ' . $e->getMessage());
        }

        // Look up related KWH data if available (table may not exist)
        $kwh = null;
        try {
            $stmtKwh = $this->db->prepare(
                "SELECT * FROM aset_kwh WHERE id_pju = ? LIMIT 1"
            );
            if ($stmtKwh) {
                $stmtKwh->bind_param('s', $idPju);
                $stmtKwh->execute();
                $kwh = $stmtKwh->get_result()->fetch_assoc();
                $stmtKwh->close();
            }
        } catch (\Throwable $e) {
            // aset_kwh table might not exist — continue without KWH data
            error_log('lookupPju KWH query failed: ' . $e->getMessage());
        }

        return [
            'pju' => $pju,
            'kwh' => $kwh,
        ];
    }

    /**
     * Get tanggal_perbaikan for a given ticket number.
     */
    public function getTanggalPerbaikan(string $nomorTiket): ?string
    {
        $stmt = $this->db->prepare(
            'SELECT tanggal_perbaikan FROM perbaikan_pju WHERE nomor_tiket = ? LIMIT 1'
        );
        $stmt->bind_param('s', $nomorTiket);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['tanggal_perbaikan'] ?? null;
    }

    /**
     * Retrieve scheduling fields for a ticket: report_date, scheduled_date, actual_date.
     * Returns associative array with keys 'report_date', 'scheduled_date', 'actual_date' (values may be null).
     */
    public function getSchedulingData(string $nomorTiket): array
    {
        $stmt = $this->db->prepare('SELECT * FROM perbaikan_pju WHERE nomor_tiket = ? LIMIT 1');
        $stmt->bind_param('s', $nomorTiket);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if (!$row) return ['report_date' => null, 'scheduled_date' => null, 'actual_date' => null];

        // Helper to pick the first existing column from candidates
        $pick = function(array $candidates) use ($row) {
            foreach ($candidates as $c) {
                if (array_key_exists($c, $row)) {
                    $v = trim((string)($row[$c] ?? ''));
                    if ($v === '' || $v === '0000-00-00' || $v === '0000-00-00 00:00:00') return null;
                    return $v;
                }
            }
            return null;
        };

        $reportKeys = ['tanggal', 'tanggal_lapor', 'report_date', 'created_at'];
        $scheduledKeys = ['tanggal_penjadwalan', 'tanggal_jadwal', 'tanggal_terjadwal', 'scheduled_date', 'tanggal_rencana'];
        $actualKeys = ['tanggal_perbaikan', 'tanggal_selesai', 'actual_date'];

        $reportDate = $pick($reportKeys);
        $scheduledDate = $pick($scheduledKeys);
        $actualDate = $pick($actualKeys);

        return [
            'report_date'    => $reportDate,
            'scheduled_date' => $scheduledDate,
            'actual_date'    => $actualDate,
        ];
    }

    /**
     * Retrieve repair start and finish times for a ticket.
     * Returns ['repair_date' => ..., 'finish_time' => ...] where values may be null.
     */
    public function getRepairTimes(string $nomorTiket): array
    {
        $stmt = $this->db->prepare('SELECT * FROM perbaikan_pju WHERE nomor_tiket = ? LIMIT 1');
        $stmt->bind_param('s', $nomorTiket);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if (!$row) return ['repair_date' => null, 'finish_time' => null];

        $pick = function(array $candidates) use ($row) {
            foreach ($candidates as $c) {
                if (array_key_exists($c, $row)) {
                    $v = trim((string)($row[$c] ?? ''));
                    if ($v === '' || $v === '0000-00-00' || $v === '0000-00-00 00:00:00') return null;
                    return $v;
                }
            }
            return null;
        };

        $repairKeys = ['repair_date', 'tanggal_mulai', 'started_at', 'waktu_mulai', 'start_time', 'tanggal_perbaikan'];
        $finishKeys = ['finish_time', 'waktu_selesai', 'tanggal_selesai', 'actual_date', 'selesai_time', 'finished_at'];

        $repairDate = $pick($repairKeys);
        $finishTime = $pick($finishKeys);

        return [
            'repair_date' => $repairDate,
            'finish_time' => $finishTime,
        ];
    }

    /**
     * Compute duration in minutes using DB TIMESTAMPDIFF between tanggal_perbaikan and waktu_selesai.
     * Returns integer minutes or null if either field is NULL.
     */
    public function getDurasiMenit(string $nomorTiket): ?int
    {
        $stmt = $this->db->prepare(
            "SELECT
                p.tanggal,
                p.waktu_selesai,
                CASE
                    WHEN p.tanggal IS NULL OR p.waktu_selesai IS NULL THEN NULL
                    WHEN p.tanggal = '' OR p.waktu_selesai = '' THEN NULL
                    ELSE TIMESTAMPDIFF(MINUTE, p.tanggal, p.waktu_selesai)
                END AS durasi_menit
             FROM perbaikan_pju p
             WHERE p.nomor_tiket = ?
             LIMIT 1"
        );
        if (!$stmt) return null;
        $stmt->bind_param('s', $nomorTiket);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        if (!$row) return null;
        return isset($row['durasi_menit']) && $row['durasi_menit'] !== null ? (int)$row['durasi_menit'] : null;
    }
}
