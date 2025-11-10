<?php
include 'config.php';
$profile_id = 1;

if($_POST) {
    $table = $_POST['table'];
    $id = $_POST['id'] ?? 0;
    
    if($table == 'pendidikan') {
        if($_POST['action'] == 'tambah') {
            $sql = "INSERT INTO pendidikan (informasi_pribadi_id, tingkat, institusi, tahun_mulai, tahun_selesai, jurusan) VALUES (?,?,?,?,?,?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isssss", $profile_id, $_POST['tingkat'], $_POST['institusi'], $_POST['tahun_mulai'], $_POST['tahun_selesai'], $_POST['jurusan']);
        } else {
            $sql = "UPDATE pendidikan SET tingkat=?, institusi=?, tahun_mulai=?, tahun_selesai=?, jurusan=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $_POST['tingkat'], $_POST['institusi'], $_POST['tahun_mulai'], $_POST['tahun_selesai'], $_POST['jurusan'], $id);
        }
        $stmt->execute();
    }
    
    elseif($table == 'pengalaman') {
        if($_POST['action'] == 'tambah') {
            $sql = "INSERT INTO pengalaman (informasi_pribadi_id, nama_kegiatan, tahun_mulai, posisi, deskripsi_tugas) VALUES (?,?,?,?,?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isiss", $profile_id, $_POST['nama_kegiatan'], $_POST['tahun_mulai'], $_POST['posisi'], $_POST['deskripsi_tugas']);
        } else {
            $sql = "UPDATE pengalaman SET nama_kegiatan=?, tahun_mulai=?, posisi=?, deskripsi_tugas=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sissi", $_POST['nama_kegiatan'], $_POST['tahun_mulai'], $_POST['posisi'], $_POST['deskripsi_tugas'], $id);
        }
        $stmt->execute();
    }
    
    elseif($table == 'foto' && $_POST['action'] == 'tambah') {
        if(isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
            $new_file_name = uniqid() . '_' . $_FILES['foto']['name'];
            $upload_path = 'uploads/' . $new_file_name;
            
            if(move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                $sql = "INSERT INTO media (informasi_pribadi_id, pengalaman_id, nama_file, deskripsi) VALUES (?,?,?,?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iiss", $profile_id, $_POST['pengalaman_id'], $new_file_name, $_POST['deskripsi']);
                $stmt->execute();
            }
        }
    }
    
    elseif($table == 'penghargaan') {
        if($_POST['action'] == 'tambah') {
            $sql = "INSERT INTO penghargaan (informasi_pribadi_id, nama_penghargaan, tahun, tingkat) VALUES (?,?,?,?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isis", $profile_id, $_POST['nama_penghargaan'], $_POST['tahun'], $_POST['tingkat']);
        } else {
            $sql = "UPDATE penghargaan SET nama_penghargaan=?, tahun=?, tingkat=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sisi", $_POST['nama_penghargaan'], $_POST['tahun'], $_POST['tingkat'], $id);
        }
        $stmt->execute();
    }
    
    elseif($table == 'keterampilan') {
        if($_POST['action'] == 'tambah') {
            $sql = "INSERT INTO keterampilan (informasi_pribadi_id, jenis, nama_keterampilan) VALUES (?,?,?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $profile_id, $_POST['jenis'], $_POST['nama_keterampilan']);
        } else {
            $sql = "UPDATE keterampilan SET jenis=?, nama_keterampilan=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $_POST['jenis'], $_POST['nama_keterampilan'], $id);
        }
        $stmt->execute();
    }
    
    if(isset($_POST['hapus'])) {
        if($_POST['table'] == 'foto') {
            $file_path = 'uploads/' . $_POST['nama_file'];
            if(file_exists($file_path)) unlink($file_path);
        }
        $conn->query("DELETE FROM ".$_POST['table']." WHERE id=".$_POST['id']);
    }
    
    header("Location: dashboard.php");
    exit();
}

$data = [
    'pendidikan' => $conn->query("SELECT * FROM pendidikan WHERE informasi_pribadi_id = $profile_id"),
    'pengalaman' => $conn->query("SELECT * FROM pengalaman WHERE informasi_pribadi_id = $profile_id"),
    'foto' => $conn->query("SELECT m.*, p.nama_kegiatan FROM media m JOIN pengalaman p ON m.pengalaman_id = p.id WHERE m.informasi_pribadi_id = $profile_id"),
    'penghargaan' => $conn->query("SELECT * FROM penghargaan WHERE informasi_pribadi_id = $profile_id"),
    'keterampilan' => $conn->query("SELECT * FROM keterampilan WHERE informasi_pribadi_id = $profile_id")
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Profil</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial; background: #f0f0f0; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .section { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; }
        h2 { color: #007bff; margin-bottom: 15px; }
        .form { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 15px; }
        .input { width: 100%; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px; }
        .btn { background: #007bff; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; margin: 2px; }
        .btn.edit { background: #28a745; }
        .btn.hapus { background: #dc3545; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background: #f8f9fa; }
        .foto-preview { width: 60px; height: 45px; object-fit: cover; border-radius: 3px; }
        .back { display: inline-block; margin-bottom: 15px; color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back">← Kembali ke Profil</a>
        
        <div class="section">
            <h1>Kelola Profil</h1>
        </div>

        <!-- Pendidikan -->
        <div class="section">
            <h2>🎓 Pendidikan</h2>
            <div class="form">
                <form method="POST">
                    <input type="hidden" name="table" value="pendidikan">
                    <input type="hidden" name="id" id="pendidikanId">
                    <input type="hidden" name="action" id="pendidikanAction" value="tambah">
                    
                    <input type="text" name="institusi" placeholder="Nama Institusi" class="input" required>
                    <select name="tingkat" class="input" required>
                        <option value="">Pilih Tingkat</option>
                        <option value="SD">SD</option><option value="SMP">SMP</option><option value="SMA">SMA</option>
                        <option value="S1">S1</option><option value="S2">S2</option><option value="S3">S3</option>
                    </select>
                    <input type="number" name="tahun_mulai" placeholder="Tahun Mulai" class="input" required>
                    <input type="text" name="tahun_selesai" placeholder="Tahun Selesai" class="input">
                    <input type="text" name="jurusan" placeholder="Jurusan" class="input">
                    
                    <button type="submit" class="btn" id="pendidikanBtn">Tambah</button>
                    <button type="button" class="btn hapus" onclick="resetForm('pendidikan')" style="display:none;" id="pendidikanBatal">Batal</button>
                </form>
            </div>

            <table>
                <tr><th>Institusi</th><th>Tingkat</th><th>Tahun</th><th>Aksi</th></tr>
                <?php while($row = $data['pendidikan']->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['institusi'] ?></td>
                    <td><?= $row['tingkat'] ?></td>
                    <td><?= $row['tahun_mulai'] ?> - <?= $row['tahun_selesai'] ?></td>
                    <td>
                        <button class="btn edit" onclick="editPendidikan(<?= $row['id'] ?>, '<?= $row['tingkat'] ?>', '<?= $row['institusi'] ?>', '<?= $row['tahun_mulai'] ?>', '<?= $row['tahun_selesai'] ?>', '<?= $row['jurusan'] ?>')">Edit</button>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="table" value="pendidikan">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="hapus" value="1">
                            <button class="btn hapus" onclick="return confirm('Yakin hapus?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <!-- Pengalaman -->
        <div class="section">
            <h2>💼 Pengalaman</h2>
            <div class="form">
                <form method="POST">
                    <input type="hidden" name="table" value="pengalaman">
                    <input type="hidden" name="id" id="pengalamanId">
                    <input type="hidden" name="action" id="pengalamanAction" value="tambah">
                    
                    <input type="text" name="nama_kegiatan" placeholder="Nama Kegiatan" class="input" required>
                    <input type="number" name="tahun_mulai" placeholder="Tahun" class="input" required>
                    <input type="text" name="posisi" placeholder="Posisi" class="input">
                    <textarea name="deskripsi_tugas" placeholder="Deskripsi Tugas" class="input" rows="3"></textarea>
                    
                    <button type="submit" class="btn" id="pengalamanBtn">Tambah</button>
                    <button type="button" class="btn hapus" onclick="resetForm('pengalaman')" style="display:none;" id="pengalamanBatal">Batal</button>
                </form>
            </div>

            <table>
                <tr><th>Kegiatan</th><th>Tahun</th><th>Posisi</th><th>Aksi</th></tr>
                <?php while($row = $data['pengalaman']->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['nama_kegiatan'] ?></td>
                    <td><?= $row['tahun_mulai'] ?></td>
                    <td><?= $row['posisi'] ?></td>
                    <td>
                        <button class="btn edit" onclick="editPengalaman(<?= $row['id'] ?>, '<?= addslashes($row['nama_kegiatan']) ?>', '<?= $row['tahun_mulai'] ?>', '<?= addslashes($row['posisi']) ?>', `<?= addslashes($row['deskripsi_tugas']) ?>`)">Edit</button>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="table" value="pengalaman">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="hapus" value="1">
                            <button class="btn hapus" onclick="return confirm('Yakin hapus?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <!-- Foto -->
        <div class="section">
            <h2>📸 Foto</h2>
            <div class="form">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="table" value="foto">
                    <input type="hidden" name="action" value="tambah">
                    
                    <select name="pengalaman_id" class="input" required>
                        <option value="">Pilih Pengalaman</option>
                        <?php 
                        $pengalaman = $conn->query("SELECT * FROM pengalaman WHERE informasi_pribadi_id = $profile_id");
                        while($p = $pengalaman->fetch_assoc()): 
                        ?>
                        <option value="<?= $p['id'] ?>"><?= $p['nama_kegiatan'] ?></option>
                        <?php endwhile; ?>
                    </select>
                    <input type="file" name="foto" class="input" accept="image/*" required>
                    <input type="text" name="deskripsi" placeholder="Deskripsi Foto" class="input">
                    
                    <button type="submit" class="btn">Upload</button>
                </form>
            </div>

            <table>
                <tr><th>Preview</th><th>Pengalaman</th><th>Deskripsi</th><th>Aksi</th></tr>
                <?php while($row = $data['foto']->fetch_assoc()): 
                    $file_path = 'uploads/' . $row['nama_file'];
                ?>
                <tr>
                    <td>
                        <?php if(file_exists($file_path)): ?>
                            <img src="<?= $file_path ?>" class="foto-preview">
                        <?php else: ?>
                            <div style="width:60px;height:45px;background:#eee;display:flex;align-items:center;justify-content:center;font-size:12px;">❌</div>
                        <?php endif; ?>
                    </td>
                    <td><?= $row['nama_kegiatan'] ?></td>
                    <td><?= $row['deskripsi'] ?></td>
                    <td>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="table" value="foto">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="nama_file" value="<?= $row['nama_file'] ?>">
                            <input type="hidden" name="hapus" value="1">
                            <button class="btn hapus" onclick="return confirm('Yakin hapus foto?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <!-- Penghargaan -->
        <div class="section">
            <h2>🏆 Penghargaan</h2>
            <div class="form">
                <form method="POST">
                    <input type="hidden" name="table" value="penghargaan">
                    <input type="hidden" name="id" id="penghargaanId">
                    <input type="hidden" name="action" id="penghargaanAction" value="tambah">
                    
                    <input type="text" name="nama_penghargaan" placeholder="Nama Penghargaan" class="input" required>
                    <input type="number" name="tahun" placeholder="Tahun" class="input" required>
                    <select name="tingkat" class="input" required>
                        <option value="Lokal">Lokal</option>
                        <option value="Regional">Regional</option>
                        <option value="Nasional">Nasional</option>
                        <option value="Internasional">Internasional</option>
                    </select>
                    
                    <button type="submit" class="btn" id="penghargaanBtn">Tambah</button>
                    <button type="button" class="btn hapus" onclick="resetForm('penghargaan')" style="display:none;" id="penghargaanBatal">Batal</button>
                </form>
            </div>

            <table>
                <tr><th>Penghargaan</th><th>Tahun</th><th>Tingkat</th><th>Aksi</th></tr>
                <?php while($row = $data['penghargaan']->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['nama_penghargaan'] ?></td>
                    <td><?= $row['tahun'] ?></td>
                    <td><?= $row['tingkat'] ?></td>
                    <td>
                        <button class="btn edit" onclick="editPenghargaan(<?= $row['id'] ?>, '<?= addslashes($row['nama_penghargaan']) ?>', '<?= $row['tahun'] ?>', '<?= $row['tingkat'] ?>')">Edit</button>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="table" value="penghargaan">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="hapus" value="1">
                            <button class="btn hapus" onclick="return confirm('Yakin hapus?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <!-- Keterampilan -->
        <div class="section">
            <h2>🛠️ Keterampilan</h2>
            <div class="form">
                <form method="POST">
                    <input type="hidden" name="table" value="keterampilan">
                    <input type="hidden" name="id" id="keterampilanId">
                    <input type="hidden" name="action" id="keterampilanAction" value="tambah">
                    
                    <select name="jenis" class="input" required>
                        <option value="Soft Skill">Soft Skill</option>
                        <option value="Hard Skill">Hard Skill</option>
                        <option value="Minat">Minat</option>
                    </select>
                    <input type="text" name="nama_keterampilan" placeholder="Nama Keterampilan" class="input" required>
                    
                    <button type="submit" class="btn" id="keterampilanBtn">Tambah</button>
                    <button type="button" class="btn hapus" onclick="resetForm('keterampilan')" style="display:none;" id="keterampilanBatal">Batal</button>
                </form>
            </div>

            <table>
                <tr><th>Jenis</th><th>Keterampilan</th><th>Aksi</th></tr>
                <?php while($row = $data['keterampilan']->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['jenis'] ?></td>
                    <td><?= $row['nama_keterampilan'] ?></td>
                    <td>
                        <button class="btn edit" onclick="editKeterampilan(<?= $row['id'] ?>, '<?= $row['jenis'] ?>', '<?= addslashes($row['nama_keterampilan']) ?>')">Edit</button>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="table" value="keterampilan">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="hapus" value="1">
                            <button class="btn hapus" onclick="return confirm('Yakin hapus?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>

    <script>
        // Fungsi edit untuk semua form
        function editPendidikan(id, tingkat, institusi, tahunMulai, tahunSelesai, jurusan) {
            document.getElementById('pendidikanId').value = id;
            document.querySelector('[name="tingkat"]').value = tingkat;
            document.querySelector('[name="institusi"]').value = institusi;
            document.querySelector('[name="tahun_mulai"]').value = tahunMulai;
            document.querySelector('[name="tahun_selesai"]').value = tahunSelesai;
            document.querySelector('[name="jurusan"]').value = jurusan;
            
            document.getElementById('pendidikanAction').value = 'edit';
            document.getElementById('pendidikanBtn').textContent = 'Update';
            document.getElementById('pendidikanBatal').style.display = 'inline-block';
        }

        function editPengalaman(id, nama, tahun, posisi, deskripsi) {
            document.getElementById('pengalamanId').value = id;
            document.querySelector('[name="nama_kegiatan"]').value = nama;
            document.querySelector('[name="tahun_mulai"]').value = tahun;
            document.querySelector('[name="posisi"]').value = posisi;
            document.querySelector('[name="deskripsi_tugas"]').value = deskripsi;
            
            document.getElementById('pengalamanAction').value = 'edit';
            document.getElementById('pengalamanBtn').textContent = 'Update';
            document.getElementById('pengalamanBatal').style.display = 'inline-block';
        }

        function editPenghargaan(id, nama, tahun, tingkat) {
            document.getElementById('penghargaanId').value = id;
            document.querySelector('[name="nama_penghargaan"]').value = nama;
            document.querySelector('[name="tahun"]').value = tahun;
            document.querySelector('[name="tingkat"]').value = tingkat;
            
            document.getElementById('penghargaanAction').value = 'edit';
            document.getElementById('penghargaanBtn').textContent = 'Update';
            document.getElementById('penghargaanBatal').style.display = 'inline-block';
        }

        function editKeterampilan(id, jenis, nama) {
            document.getElementById('keterampilanId').value = id;
            document.querySelector('[name="jenis"]').value = jenis;
            document.querySelector('[name="nama_keterampilan"]').value = nama;
            
            document.getElementById('keterampilanAction').value = 'edit';
            document.getElementById('keterampilanBtn').textContent = 'Update';
            document.getElementById('keterampilanBatal').style.display = 'inline-block';
        }

        function resetForm(table) {
            document.getElementById(table + 'Id').value = '';
            document.getElementById(table + 'Action').value = 'tambah';
            document.getElementById(table + 'Btn').textContent = 'Tambah';
            document.getElementById(table + 'Batal').style.display = 'none';
            document.querySelector('form input[name="table"][value="' + table + '"]').closest('form').reset();
        }
    </script>
</body>
</html>