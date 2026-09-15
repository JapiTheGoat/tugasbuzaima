<?php
require_once __DIR__ . '/../models/MapelModel.php';
require_once __DIR__ . '/../models/GuruModel.php';
require_once __DIR__ . '/../models/SiswaModel.php';
require_once __DIR__ . '/../models/PresensiModel.php';
require_once __DIR__ . '/../models/KelasModel.php';

class HomeController {
    private $mapelModel;
    private $guruModel;
    private $siswaModel;
    private $presensiModel;
    private $kelasModel;

    public function __construct() {
        $this->mapelModel = new MapelModel();
        $this->guruModel = new GuruModel();
        $this->siswaModel = new SiswaModel();
        $this->presensiModel = new PresensiModel();
        $this->kelasModel = new KelasModel();
    }

    public function handleRequest($action) {
        switch ($action) {
            case 'simpan':
                $this->simpanPresensi();
                break;
            case 'tambah_kelas':
                $this->tambahKelas();
                break;
            case 'tambah_siswa':
                $this->tambahSiswa();
                break;
            case 'tambah_guru_mapel':
                $this->tambahGuruMapel();
                break;
            case 'delete_data':
                $this->deleteData();
                break;
            default:
                $this->index();
                break;
        }
    }

    public function index() {
        $bulan = $_GET['bulan'] ?? date('m');
        $tahun = $_GET['tahun'] ?? date('Y');
        $mapel_id = $_GET['mapel_id'] ?? 1;
        $kelas_id = $_GET['kelas_id'] ?? null;

        $kelas_list = $this->kelasModel->getAll();
        if (empty($kelas_list)) {
            die("Belum ada kelas. Silakan tambahkan kelas terlebih dahulu.");
        }

        if ($kelas_id === null || !in_array($kelas_id, array_column($kelas_list, 'id'))) {
            $kelas_id = $_SESSION['kelas_display'] ?? $kelas_list[0]['id'];
        }
        $_SESSION['kelas_display'] = $kelas_id;

        $mapel = $this->mapelModel->getAll();
        $mapel_ids = array_column($mapel, 'id');
        if (!in_array($mapel_id, $mapel_ids)) {
            $mapel_id = $mapel_ids[0] ?? 0;
        }

        $guru_list = $this->guruModel->getAllByMapel($mapel_id);
        $all_guru  = $this->guruModel->getAll();
        $siswa = $this->siswaModel->getByKelas($kelas_id);
        $jml_siswa = count($siswa);
        $jml_hari  = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

        $siswa_ids = array_column($siswa, 'id');
        $presensi_data = $this->presensiModel->getPresensiByMapelBulanSiswa($mapel_id, $tahun, $bulan, $siswa_ids);

        // Progres harian
        $tampil_sampai = 0;
        if ($jml_siswa > 0) {
            for ($tgl = 1; $tgl <= $jml_hari; $tgl++) {
                $tgl_str = sprintf('%04d-%02d-%02d', $tahun, $bulan, $tgl);
                $count = 0;
                foreach ($siswa_ids as $sid) {
                    if (isset($presensi_data[$sid][$tgl_str])) {
                        $count++;
                    }
                }
                if ($count < $jml_siswa) {
                    $tampil_sampai = $tgl;
                    break;
                }
            }
            if ($tampil_sampai == 0) $tampil_sampai = $jml_hari;
        }

        $kelas_display = $kelas_id;

        require_once __DIR__ . '/../views/home.php';
    }

    public function simpanPresensi() {
        $bulan = $_POST['bulan'] ?? date('m');
        $tahun = $_POST['tahun'] ?? date('Y');
        $mapel_id = $_POST['mapel_id'] ?? 1;
        $kelas_id = $_POST['kelas_id'] ?? '';
        $status_arr = $_POST['status'] ?? [];
        $alasan_arr = $_POST['alasan'] ?? [];
        $guru_id = $_POST['guru_id'] ?? null;

        if ($kelas_id !== '') {
            $_SESSION['kelas_display'] = $kelas_id;
        }

        foreach ($status_arr as $siswa_id => $tgl_status) {
            foreach ($tgl_status as $tanggal => $status) {
                $alasan = $alasan_arr[$siswa_id][$tanggal] ?? '';
                $this->presensiModel->saveOrUpdate($siswa_id, $mapel_id, $guru_id, $tanggal, $status, $alasan);
            }
        }

        header("Location: index.php?bulan=$bulan&tahun=$tahun&mapel_id=$mapel_id&kelas_id=$kelas_id");
        exit;
    }

    public function tambahKelas() {
        $nama = trim($_POST['nama_kelas'] ?? '');
        $bulan = $_POST['bulan'] ?? date('m');
        $tahun = $_POST['tahun'] ?? date('Y');
        $mapel_id = $_POST['mapel_id'] ?? 1;

        if ($nama !== '') {
            $kelas_id = $this->kelasModel->findOrCreate($nama);
            $_SESSION['flash_success'] = 'Kelas berhasil ditambahkan.';
            $_SESSION['kelas_display'] = $kelas_id;
            header("Location: index.php?bulan=$bulan&tahun=$tahun&mapel_id=$mapel_id&kelas_id=$kelas_id");
            exit;
        } else {
            $_SESSION['flash_error'] = 'Nama kelas tidak boleh kosong.';
            header("Location: index.php?bulan=$bulan&tahun=$tahun&mapel_id=$mapel_id");
            exit;
        }
    }

    public function tambahSiswa() {
        $nama = trim($_POST['nama_siswa'] ?? '');
        $kelas_id = $_POST['kelas_id'] ?? '';
        $bulan = $_POST['bulan'] ?? date('m');
        $tahun = $_POST['tahun'] ?? date('Y');
        $mapel_id = $_POST['mapel_id'] ?? 1;

        if ($nama !== '' && $kelas_id !== '') {
            $this->siswaModel->add($nama, $kelas_id);
            $_SESSION['flash_success'] = 'Siswa berhasil ditambahkan.';
        } else {
            $_SESSION['flash_error'] = 'Nama siswa dan kelas wajib diisi.';
        }

        header("Location: index.php?bulan=$bulan&tahun=$tahun&mapel_id=$mapel_id&kelas_id=$kelas_id");
        exit;
    }

    public function tambahGuruMapel() {
        $nama_guru  = trim($_POST['nama_guru'] ?? '');
        $nama_mapel = trim($_POST['nama_mapel'] ?? '');
        $mapel_id   = $_POST['mapel_id'] ?? 1;
        $bulan      = $_POST['bulan'] ?? date('m');
        $tahun      = $_POST['tahun'] ?? date('Y');

        if ($nama_guru !== '') {
            $this->guruModel->findOrCreate($nama_guru, $mapel_id);
        }
        if ($nama_mapel !== '') {
            $this->mapelModel->findOrCreate($nama_mapel);
        }
        if ($nama_guru !== '' || $nama_mapel !== '') {
            $_SESSION['flash_success'] = 'Data guru/mapel berhasil ditambahkan.';
        } else {
            $_SESSION['flash_error'] = 'Isi minimal salah satu field.';
        }

        header("Location: index.php?bulan=$bulan&tahun=$tahun&mapel_id=$mapel_id");
        exit;
    }

    public function deleteData() {
        $type     = $_POST['delete_type'] ?? '';
        $bulan    = $_POST['bulan'] ?? date('m');
        $tahun    = $_POST['tahun'] ?? date('Y');
        $mapel_id = $_POST['mapel_id'] ?? 1;
        $error    = '';
        $success  = '';

        try {
            switch ($type) {
                case 'siswa':
                    $id = $_POST['siswa_id'] ?? '';
                    if ($id !== '') {
                        if ($this->presensiModel->countBySiswa($id) > 0) {
                            $error = 'Siswa tidak dapat dihapus karena memiliki data presensi.';
                        } else {
                            $this->siswaModel->delete($id);
                            $success = 'Siswa berhasil dihapus.';
                        }
                    } else {
                        $error = 'Pilih siswa terlebih dahulu.';
                    }
                    break;
                case 'guru':
                    $id = $_POST['guru_id'] ?? '';
                    if ($id !== '') {
                        if ($this->presensiModel->countByGuru($id) > 0) {
                            $error = 'Guru tidak dapat dihapus karena memiliki data presensi.';
                        } else {
                            $this->guruModel->delete($id);
                            $success = 'Guru berhasil dihapus.';
                        }
                    } else {
                        $error = 'Pilih guru terlebih dahulu.';
                    }
                    break;
                case 'mapel':
                    $id = $_POST['mapel_id_delete'] ?? '';
                    if ($id !== '') {
                        if ($this->presensiModel->countByMapel($id) > 0 || $this->guruModel->countByMapel($id) > 0) {
                            $error = 'Mapel tidak dapat dihapus karena masih digunakan oleh presensi atau guru.';
                        } else {
                            $this->mapelModel->delete($id);
                            $success = 'Mapel berhasil dihapus.';
                        }
                    } else {
                        $error = 'Pilih mapel terlebih dahulu.';
                    }
                    break;
                case 'kelas':
                    $kelas_id = $_POST['kelas_id_delete'] ?? '';
                    if ($kelas_id !== '') {
                        $siswa_ids = $this->siswaModel->getIdsByKelas($kelas_id);
                        $has_presensi = false;
                        foreach ($siswa_ids as $sid) {
                            if ($this->presensiModel->countBySiswa($sid) > 0) {
                                $has_presensi = true;
                                break;
                            }
                        }
                        if ($has_presensi) {
                            $error = 'Kelas tidak dapat dihapus karena terdapat siswa dengan data presensi.';
                        } else {
                            foreach ($siswa_ids as $sid) {
                                $this->siswaModel->delete($sid);
                            }
                            $this->kelasModel->delete($kelas_id);
                            $success = 'Kelas beserta siswanya berhasil dihapus.';
                        }
                    } else {
                        $error = 'Pilih kelas terlebih dahulu.';
                    }
                    break;
                default:
                    $error = 'Tipe hapus tidak valid.';
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }

        $_SESSION['flash_error']   = $error;
        $_SESSION['flash_success'] = $success;

        header("Location: index.php?bulan=$bulan&tahun=$tahun&mapel_id=$mapel_id&kelas_id=$kelas_id");
        exit;
    }
}