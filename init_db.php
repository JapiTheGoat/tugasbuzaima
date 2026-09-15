<?php
// init_db.php
$dbFile = 'absensi.db';
$pdo = new PDO("sqlite:$dbFile");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Buat tabel
$pdo->exec("CREATE TABLE IF NOT EXISTS mapel (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nama TEXT NOT NULL
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS guru (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nama TEXT NOT NULL,
    mapel_id INTEGER,
    FOREIGN KEY (mapel_id) REFERENCES mapel(id)
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS siswa (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nama TEXT NOT NULL,
    kelas TEXT
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS presensi (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    siswa_id INTEGER NOT NULL,
    mapel_id INTEGER NOT NULL,
    guru_id INTEGER,
    tanggal TEXT NOT NULL,
    status TEXT CHECK(status IN ('Masuk','Izin','Alpha')) NOT NULL DEFAULT 'Masuk',
    alasan TEXT,
    UNIQUE(siswa_id, mapel_id, tanggal)
)");

// Cek apakah data dummy sudah ada
$cek = $pdo->query("SELECT COUNT(*) FROM mapel")->fetchColumn();
if ($cek == 0) {
    // Data mapel
    $pdo->exec("INSERT INTO mapel (nama) VALUES ('Matematika'), ('Bahasa Indonesia'), ('IPA')");
    // Data guru
    $pdo->exec("INSERT INTO guru (nama, mapel_id) VALUES ('Budi Santoso', 1), ('Ani Rahmawati', 2), ('Cici Nurhayati', 3)");
    // Data siswa
    $pdo->exec("INSERT INTO siswa (nama, kelas) VALUES ('Andi Prasetyo', '1A'), ('Bunga Lestari', '1A'), ('Cahyo Wibowo', '1A'), ('Dewi Anggraini', '1A')");
} 
?>