<?php
class GuruModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getGuruByMapel($mapel_id) {
        $stmt = $this->db->prepare("SELECT * FROM guru WHERE mapel_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$mapel_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllByMapel($mapel_id) {
        $stmt = $this->db->prepare("SELECT * FROM guru WHERE mapel_id = ? ORDER BY nama");
        $stmt->execute([$mapel_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM guru ORDER BY nama");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findOrCreate($nama, $mapel_id) {
        $stmt = $this->db->prepare("SELECT id FROM guru WHERE nama = ? AND mapel_id = ?");
        $stmt->execute([$nama, $mapel_id]);
        $id = $stmt->fetchColumn();
        if ($id) return $id;

        $stmt = $this->db->prepare("INSERT INTO guru (nama, mapel_id) VALUES (?, ?)");
        $stmt->execute([$nama, $mapel_id]);
        return $this->db->lastInsertId();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM guru WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function countByMapel($mapel_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM guru WHERE mapel_id = ?");
        $stmt->execute([$mapel_id]);
        return $stmt->fetchColumn();
    }
}