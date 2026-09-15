<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Daftar Hadir Murid</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Toast container -->
<div id="toast-container" class="toast-container"></div>

<div class="container">
    <h2>📋 Daftar Hadir Murid</h2>

    <!-- Filter (header) -->
    <form method="get" class="header-form" id="filter-form">
        <input type="hidden" name="action" value="index">
        <label>📘 Mapel
            <select name="mapel_id" onchange="document.getElementById('filter-form').submit()">
                <?php foreach ($mapel as $m): ?>
                    <option value="<?= $m['id'] ?>" <?= $m['id'] == $mapel_id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($m['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>🏫 Kelas
            <select name="kelas_id" onchange="document.getElementById('filter-form').submit()">
                <?php foreach ($kelas_list as $k): ?>
                    <option value="<?= $k['id'] ?>" <?= $k['id'] == $kelas_display ? 'selected' : '' ?>>
                        <?= htmlspecialchars($k['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>🗓️ Bulan
            <select name="bulan" onchange="document.getElementById('filter-form').submit()">
                <?php for ($i = 1; $i <= 12; $i++): 
                    $val = str_pad($i, 2, '0', STR_PAD_LEFT);
                ?>
                    <option value="<?= $val ?>" <?= $bulan == $val ? 'selected' : '' ?>>
                        <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                    </option>
                <?php endfor; ?>
            </select>
        </label>
        <label>📅 Tahun
            <input type="number" name="tahun" value="<?= $tahun ?>" style="width:90px"
                   onchange="document.getElementById('filter-form').submit()">
        </label>
    </form>

    <!-- Form utama simpan presensi -->
    <form method="post" action="index.php?action=simpan" id="form-simpan">
        <input type="hidden" name="bulan" value="<?= $bulan ?>">
        <input type="hidden" name="tahun" value="<?= $tahun ?>">
        <input type="hidden" name="mapel_id" value="<?= $mapel_id ?>">
        <input type="hidden" name="kelas_id" value="<?= $kelas_display ?>">

        <div class="info-bar">
            <label>👨‍🏫 Guru Mapel:
                <select name="guru_id">
                    <option value="">-- Pilih Guru --</option>
                    <?php foreach ($guru_list as $g): ?>
                        <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>

        <div class="table-wrapper">
            <?php if ($jml_siswa == 0): ?>
                <p class="empty-message">Tidak ada siswa terdaftar di kelas ini. Silakan tambahkan siswa.</p>
            <?php elseif ($tampil_sampai == 0): ?>
                <p class="empty-message">Belum ada hari yang bisa diisi.</p>
            <?php else: ?>
                <p class="progress-info">
                    📌 Mengisi kehadiran hingga tanggal <strong><?= $tampil_sampai ?></strong>.
                    <?= $tampil_sampai < $jml_hari ? 'Setelah semua siswa lengkap, tanggal berikutnya akan terbuka.' : 'Semua tanggal sudah lengkap.' ?>
                </p>
                <table>
                    <thead>
                        <tr>
                            <th class="col-no">No</th>
                            <th class="col-nama">Nama Siswa</th>
                            <?php for ($tgl = 1; $tgl <= $tampil_sampai; $tgl++): 
                                $hari = date('D', strtotime("$tahun-$bulan-$tgl"));
                            ?>
                                <th><?= $tgl ?><br><small><?= $hari ?></small></th>
                            <?php endfor; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($siswa as $s): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td class="col-nama"><?= htmlspecialchars($s['nama']) ?></td>
                                <?php for ($tgl = 1; $tgl <= $tampil_sampai; $tgl++): 
                                    $tgl_str = sprintf('%04d-%02d-%02d', $tahun, $bulan, $tgl);
                                    $presensi = $presensi_data[$s['id']][$tgl_str] ?? null;
                                    $status = $presensi ? $presensi['status'] : 'Masuk';
                                    $alasan = $presensi ? $presensi['alasan'] : '';
                                ?>
                                    <td>
                                        <select name="status[<?= $s['id'] ?>][<?= $tgl_str ?>]"
                                                onchange="toggleAlasan(this, 'alasan_<?= $s['id'] ?>_<?= $tgl_str ?>')">
                                            <option value="Masuk" <?= $status == 'Masuk' ? 'selected' : '' ?>>M</option>
                                            <option value="Izin"  <?= $status == 'Izin'  ? 'selected' : '' ?>>I</option>
                                            <option value="Alpha" <?= $status == 'Alpha' ? 'selected' : '' ?>>A</option>
                                        </select>
                                        <input type="text"
                                               name="alasan[<?= $s['id'] ?>][<?= $tgl_str ?>]"
                                               id="alasan_<?= $s['id'] ?>_<?= $tgl_str ?>"
                                               class="alasan-input <?= $status == 'Masuk' ? 'hidden' : '' ?>"
                                               placeholder="Alasan"
                                               value="<?= htmlspecialchars($alasan) ?>">
                                    </td>
                                <?php endfor; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Baris tombol -->
        <div class="action-buttons">
            <button type="submit" class="btn btn-primary" id="btn-simpan">💾 Simpan Kehadiran</button>
            <button type="button" class="btn btn-secondary" onclick="openModal()">➕ Kelola Data</button>
        </div>
    </form>
</div>

<!-- ======================= MODAL KELOLA DATA ======================= -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal-box">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h3>Kelola Data</h3>

        <!-- Grid form tambah -->
        <div class="kelola-grid">
            <!-- Tambah Kelas -->
            <form method="post" action="index.php?action=tambah_kelas" class="mini-form">
                <fieldset>
                    <legend>➕ Tambah Kelas</legend>
                    <label>Nama Kelas</label>
                    <input type="text" name="nama_kelas" placeholder="cth: 1A" required>
                    <input type="hidden" name="bulan" value="<?= $bulan ?>">
                    <input type="hidden" name="tahun" value="<?= $tahun ?>">
                    <input type="hidden" name="mapel_id" value="<?= $mapel_id ?>">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </fieldset>
            </form>

            <!-- Tambah Siswa -->
            <form method="post" action="index.php?action=tambah_siswa" class="mini-form">
                <fieldset>
                    <legend>➕ Tambah Siswa</legend>
                    <label>Nama Siswa</label>
                    <input type="text" name="nama_siswa" placeholder="Nama lengkap" required>
                    <label>Kelas</label>
                    <select name="kelas_id" required>
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach ($kelas_list as $k): ?>
                            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="bulan" value="<?= $bulan ?>">
                    <input type="hidden" name="tahun" value="<?= $tahun ?>">
                    <input type="hidden" name="mapel_id" value="<?= $mapel_id ?>">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </fieldset>
            </form>

            <!-- Tambah Guru & Mapel (gabungan) -->
            <form method="post" action="index.php?action=tambah_guru_mapel" class="mini-form">
                <fieldset>
                    <legend>👨‍🏫📘 Tambah Guru & Mapel</legend>
                    <label>Nama Guru</label>
                    <input type="text" name="nama_guru" placeholder="Nama guru (opsional)">
                    <label>Nama Mapel</label>
                    <input type="text" name="nama_mapel" placeholder="Nama mapel (opsional)">
                    <input type="hidden" name="bulan" value="<?= $bulan ?>">
                    <input type="hidden" name="tahun" value="<?= $tahun ?>">
                    <input type="hidden" name="mapel_id" value="<?= $mapel_id ?>">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </fieldset>
            </form>
        </div>

        <!-- Bagian Hapus -->
        <fieldset class="delete-section">
            <legend>🗑️ Hapus Data</legend>
            <div class="delete-container">
                <!-- Hapus Siswa -->
                <form class="delete-form" method="post" action="index.php?action=delete_data" data-confirm="Yakin ingin menghapus siswa ini?">
                    <input type="hidden" name="bulan" value="<?= $bulan ?>">
                    <input type="hidden" name="tahun" value="<?= $tahun ?>">
                    <input type="hidden" name="mapel_id" value="<?= $mapel_id ?>">
                    <input type="hidden" name="kelas_id" value="<?= $kelas_display ?>">
                    <input type="hidden" name="delete_type" value="siswa">
                    <label>Hapus Siswa</label>
                    <select name="siswa_id">
                        <option value="">-- Pilih Siswa --</option>
                        <?php foreach ($siswa as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>

                <!-- Hapus Guru -->
                <form class="delete-form" method="post" action="index.php?action=delete_data" data-confirm="Yakin ingin menghapus guru ini?">
                    <input type="hidden" name="bulan" value="<?= $bulan ?>">
                    <input type="hidden" name="tahun" value="<?= $tahun ?>">
                    <input type="hidden" name="mapel_id" value="<?= $mapel_id ?>">
                    <input type="hidden" name="kelas_id" value="<?= $kelas_display ?>">
                    <input type="hidden" name="delete_type" value="guru">
                    <label>Hapus Guru</label>
                    <select name="guru_id">
                        <option value="">-- Pilih Guru --</option>
                        <?php foreach ($all_guru as $g): ?>
                            <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>

                <!-- Hapus Mapel -->
                <form class="delete-form" method="post" action="index.php?action=delete_data" data-confirm="Yakin ingin menghapus mapel ini?">
                    <input type="hidden" name="bulan" value="<?= $bulan ?>">
                    <input type="hidden" name="tahun" value="<?= $tahun ?>">
                    <input type="hidden" name="mapel_id" value="<?= $mapel_id ?>">
                    <input type="hidden" name="kelas_id" value="<?= $kelas_display ?>">
                    <input type="hidden" name="delete_type" value="mapel">
                    <label>Hapus Mapel</label>
                    <select name="mapel_id_delete">
                        <option value="">-- Pilih Mapel --</option>
                        <?php foreach ($mapel as $m): ?>
                            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>

                <!-- Hapus Kelas -->
                <form class="delete-form" method="post" action="index.php?action=delete_data" data-confirm="Yakin ingin menghapus kelas ini beserta siswanya?">
                    <input type="hidden" name="bulan" value="<?= $bulan ?>">
                    <input type="hidden" name="tahun" value="<?= $tahun ?>">
                    <input type="hidden" name="mapel_id" value="<?= $mapel_id ?>">
                    <input type="hidden" name="kelas_id" value="<?= $kelas_display ?>">
                    <input type="hidden" name="delete_type" value="kelas">
                    <label>Hapus Kelas</label>
                    <select name="kelas_id_delete">
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach ($kelas_list as $k): ?>
                            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </fieldset>
    </div>
</div>

<script>
function showToast(type, message) {
    const container = document.getElementById('toast-container');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    container.appendChild(toast);
    requestAnimationFrame(() => toast.classList.add('show'));
    setTimeout(() => {
        toast.classList.remove('show');
        toast.classList.add('hide');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

function toggleAlasan(selectEl, alasanId) {
    const input = document.getElementById(alasanId);
    if (!input) return;
    if (selectEl.value === 'Masuk') {
        input.classList.add('hidden');
        input.value = '';
    } else {
        input.classList.remove('hidden');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('select[name^="status"]').forEach(function(sel) {
        const match = sel.name.match(/status\[(\d+)\]\[(.+?)\]/);
        if (match) {
            const siswaId = match[1];
            const tglStr = match[2];
            const alasanId = 'alasan_' + siswaId + '_' + tglStr;
            toggleAlasan(sel, alasanId);
        }
    });

    <?php if (!empty($_SESSION['flash_error'])): ?>
        showToast('error', '<?= htmlspecialchars($_SESSION['flash_error'], ENT_QUOTES) ?>');
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_success'])): ?>
        showToast('success', '<?= htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES) ?>');
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>
});

const overlay = document.getElementById('modalOverlay');
function openModal() {
    // Reset semua form di dalam modal agar kosong
    document.querySelectorAll('#modalOverlay form').forEach(form => form.reset());
    overlay.style.display = 'flex';
    overlay.classList.add('modal-visible');
}
function closeModal() {
    overlay.classList.remove('modal-visible');
    overlay.classList.add('modal-hiding');
    setTimeout(() => {
        overlay.style.display = 'none';
        overlay.classList.remove('modal-hiding');
    }, 200);
}
window.onclick = function (event) {
    if (event.target === overlay) closeModal();
};

const formSimpan = document.getElementById('form-simpan');
if (formSimpan) {
    formSimpan.addEventListener('submit', function() {
        const btn = document.getElementById('btn-simpan');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '⏳ Menyimpan...';
        }
    });
}

document.querySelectorAll('.delete-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const msg = this.getAttribute('data-confirm') || 'Yakin ingin menghapus data ini?';
        if (!confirm(msg)) {
            e.preventDefault();
        }
    });
});
</script>
</body>
</html>