<?php
class PresensiModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getPresensiByMapelBulanSiswa($mapel_id, $tahun, $bulan, $siswa_ids = []) {
        $start = "$tahun-$bulan-01";
        $end = "$tahun-$bulan-" . cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

        if (empty($siswa_ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($siswa_ids), '?'));
        $sql = "SELECT * FROM presensi 
                WHERE mapel_id = ? AND tanggal BETWEEN ? AND ? 
                AND siswa_id IN ($placeholders)";
        $stmt = $this->db->prepare($sql);
        $params = array_merge([$mapel_id, $start, $end], $siswa_ids);
        $stmt->execute($params);

        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[$row['siswa_id']][$row['tanggal']] = $row;
        }
        return $data;
    }

    public function saveOrUpdate($siswa_id, $mapel_id, $guru_id, $tanggal, $status, $alasan) {
        $stmt = $this->db->prepare("SELECT id FROM presensi WHERE siswa_id = ? AND mapel_id = ? AND tanggal = ?");
        $stmt->execute([$siswa_id, $mapel_id, $tanggal]);
        if ($stmt->fetch()) {
            $upd = $this->db->prepare("UPDATE presensi SET status = ?, alasan = ?, guru_id = ? WHERE siswa_id = ? AND mapel_id = ? AND tanggal = ?");
            $upd->execute([$status, $status == 'Masuk' ? '' : $alasan, $guru_id, $siswa_id, $mapel_id, $tanggal]);
        } else {
            $ins = $this->db->prepare("INSERT INTO presensi (siswa_id, mapel_id, guru_id, tanggal, status, alasan) VALUES (?, ?, ?, ?, ?, ?)");
            $ins->execute([$siswa_id, $mapel_id, $guru_id, $tanggal, $status, $status == 'Masuk' ? '' : $alasan]);
        }
    }

    public function countByTanggal($mapel_id, $tanggal, $siswa_ids = []) {
        if (empty($siswa_ids)) return 0;
        $placeholders = implode(',', array_fill(0, count($siswa_ids), '?'));
        $sql = "SELECT COUNT(*) FROM presensi WHERE mapel_id = ? AND tanggal = ? AND siswa_id IN ($placeholders)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge([$mapel_id, $tanggal], $siswa_ids));
        return $stmt->fetchColumn();
    }

    public function countBySiswa($siswa_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM presensi WHERE siswa_id = ?");
        $stmt->execute([$siswa_id]);
        return $stmt->fetchColumn();
    }

    public function countByGuru($guru_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM presensi WHERE guru_id = ?");
        $stmt->execute([$guru_id]);
        return $stmt->fetchColumn();
    }

    public function countByMapel($mapel_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM presensi WHERE mapel_id = ?");
        $stmt->execute([$mapel_id]);
        return $stmt->fetchColumn();
    }
}