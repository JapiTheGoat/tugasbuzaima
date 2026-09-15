<?php
class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $dbFile = 'absensi.db';
        $this->pdo = new PDO("sqlite:$dbFile");
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->initTables();
        $this->migrate();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    private function initTables() {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS mapel (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nama TEXT NOT NULL
        )");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS guru (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nama TEXT NOT NULL,
            mapel_id INTEGER,
            FOREIGN KEY (mapel_id) REFERENCES mapel(id)
        )");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS kelas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nama TEXT NOT NULL UNIQUE
        )");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS siswa (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nama TEXT NOT NULL,
            kelas_id INTEGER,
            FOREIGN KEY (kelas_id) REFERENCES kelas(id)
        )");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS presensi (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            siswa_id INTEGER NOT NULL,
            mapel_id INTEGER NOT NULL,
            guru_id INTEGER,
            tanggal TEXT NOT NULL,
            status TEXT CHECK(status IN ('Masuk','Izin','Alpha')) NOT NULL DEFAULT 'Masuk',
            alasan TEXT,
            UNIQUE(siswa_id, mapel_id, tanggal)
        )");

        $cek = $this->pdo->query("SELECT COUNT(*) FROM mapel")->fetchColumn();
        if ($cek == 0) {
            $this->pdo->exec("INSERT INTO mapel (nama) VALUES ('Matematika'), ('Bahasa Indonesia'), ('IPA')");
            $this->pdo->exec("INSERT INTO guru (nama, mapel_id) VALUES ('Budi Santoso', 1), ('Ani Rahmawati', 2), ('Cici Nurhayati', 3)");

            $this->pdo->exec("INSERT INTO kelas (nama) VALUES ('1A')");
            $kelas_id = $this->pdo->lastInsertId();

            $this->pdo->exec("INSERT INTO siswa (nama, kelas_id) VALUES 
                ('Andi Prasetyo', $kelas_id),
                ('Bunga Lestari', $kelas_id),
                ('Cahyo Wibowo', $kelas_id),
                ('Dewi Anggraini', $kelas_id)
            ");
        }
    }

    private function migrate() {
        $columns = $this->getColumns('siswa');
        if (!in_array('kelas_id', $columns)) {
            if (in_array('kelas', $columns)) {
                $this->pdo->exec("ALTER TABLE siswa ADD COLUMN kelas_id INTEGER");
                $oldSiswa = $this->pdo->query("SELECT id, kelas FROM siswa WHERE kelas IS NOT NULL AND kelas != ''")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($oldSiswa as $s) {
                    $namaKelas = $s['kelas'];
                    $stmt = $this->pdo->prepare("SELECT id FROM kelas WHERE nama = ?");
                    $stmt->execute([$namaKelas]);
                    $kelasId = $stmt->fetchColumn();
                    if (!$kelasId) {
                        $stmt = $this->pdo->prepare("INSERT INTO kelas (nama) VALUES (?)");
                        $stmt->execute([$namaKelas]);
                        $kelasId = $this->pdo->lastInsertId();
                    }
                    $this->pdo->prepare("UPDATE siswa SET kelas_id = ? WHERE id = ?")->execute([$kelasId, $s['id']]);
                }
                try {
                    $this->pdo->exec("ALTER TABLE siswa DROP COLUMN kelas");
                } catch (Exception $e) {
                }
            } else {
                $this->pdo->exec("ALTER TABLE siswa ADD COLUMN kelas_id INTEGER");
            }
        }
    }

    private function getColumns($table) {
        $columns = [];
        $rows = $this->pdo->query("PRAGMA table_info($table)")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $columns[] = $row['name'];
        }
        return $columns;
    }
}