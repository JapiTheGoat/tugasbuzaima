<?php
class SiswaModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT s.id, s.nama, s.kelas_id, k.nama as kelas_nama 
                                  FROM siswa s LEFT JOIN kelas k ON s.kelas_id = k.id 
                                  ORDER BY s.id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByKelas($kelas_id) {
        $stmt = $this->db->prepare("SELECT s.id, s.nama, s.kelas_id, k.nama as kelas_nama 
                                    FROM siswa s 
                                    LEFT JOIN kelas k ON s.kelas_id = k.id 
                                    WHERE s.kelas_id = ? 
                                    ORDER BY s.id");
        $stmt->execute([$kelas_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add($nama, $kelas_id) {
        $stmt = $this->db->prepare("INSERT INTO siswa (nama, kelas_id) VALUES (?, ?)");
        $stmt->execute([$nama, $kelas_id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM siswa WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function getIdsByKelas($kelas_id) {
        $stmt = $this->db->prepare("SELECT id FROM siswa WHERE kelas_id = ?");
        $stmt->execute([$kelas_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}