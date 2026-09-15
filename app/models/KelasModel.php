<?php
class KelasModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM kelas ORDER BY nama");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add($nama) {
        $stmt = $this->db->prepare("INSERT INTO kelas (nama) VALUES (?)");
        $stmt->execute([$nama]);
        return $this->db->lastInsertId();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM kelas WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function findOrCreate($nama) {
        $stmt = $this->db->prepare("SELECT id FROM kelas WHERE nama = ?");
        $stmt->execute([$nama]);
        $id = $stmt->fetchColumn();
        if ($id) return $id;
        return $this->add($nama);
    }

    public function countSiswa($kelas_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM siswa WHERE kelas_id = ?");
        $stmt->execute([$kelas_id]);
        return $stmt->fetchColumn();
    }
}