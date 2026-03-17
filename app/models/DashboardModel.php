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
}
