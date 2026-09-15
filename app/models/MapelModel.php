<?php
class MapelModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM mapel");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findOrCreate($nama) {
        $stmt = $this->db->prepare("SELECT id FROM mapel WHERE nama = ?");
        $stmt->execute([$nama]);
        $id = $stmt->fetchColumn();
        if ($id) return $id;

        $stmt = $this->db->prepare("INSERT INTO mapel (nama) VALUES (?)");
        $stmt->execute([$nama]);
        return $this->db->lastInsertId();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM mapel WHERE id = ?");
        $stmt->execute([$id]);
    }
}