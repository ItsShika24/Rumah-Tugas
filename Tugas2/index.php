<?php
include 'config.php';
$profile_id = 1;

$profile = $conn->query("SELECT * FROM informasi_pribadi WHERE id = $profile_id")->fetch_assoc();
$kontak = $conn->query("SELECT * FROM kontak WHERE informasi_pribadi_id = $profile_id")->fetch_assoc();
$pendidikan = $conn->query("SELECT * FROM pendidikan WHERE informasi_pribadi_id = $profile_id ORDER BY tahun_mulai DESC");
$pengalaman = $conn->query("SELECT * FROM pengalaman WHERE informasi_pribadi_id = $profile_id ORDER BY tahun_mulai DESC");
$keterampilan = $conn->query("SELECT * FROM keterampilan WHERE informasi_pribadi_id = $profile_id");
$penghargaan = $conn->query("SELECT * FROM penghargaan WHERE informasi_pribadi_id = $profile_id ORDER BY tahun DESC");

$foto_data = [];
$pengalaman_result = $conn->query("SELECT * FROM pengalaman WHERE informasi_pribadi_id = $profile_id");
while($pengalaman_row = $pengalaman_result->fetch_assoc()) {
    $fotos = $conn->query("SELECT * FROM media WHERE pengalaman_id = " . $pengalaman_row['id']);
    $foto_data[$pengalaman_row['id']] = $fotos->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - <?php echo $profile['nama']; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; line-height: 1.6; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; padding: 20px; }
        .card { background: white; padding: 30px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; }
        .profile-img { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #007bff; }
        .name { font-size: 2em; margin: 15px 0; color: #333; }
        .title { color: #666; margin-bottom: 20px; }
        .contact-info { display: flex; justify-content: center; gap: 20px; margin: 20px 0; flex-wrap: wrap; }
        .contact-item { display: flex; align-items: center; gap: 8px; }
        .section { margin: 30px 0; }
        .section h2 { color: #007bff; margin-bottom: 15px; border-bottom: 2px solid #007bff; padding-bottom: 5px; }
        .timeline-item { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #007bff; }
        .skills-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px; }
        .skill-category { background: #f8f9fa; padding: 15px; border-radius: 5px; }
        .photos-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; margin-top: 10px; }
        .photo-item { border-radius: 5px; overflow: hidden; }
        .photo-item img { width: 100%; height: 120px; object-fit: cover; }
        .admin-btn { position: fixed; bottom: 20px; right: 20px; background: #007bff; color: white; padding: 12px 20px; border-radius: 5px; text-decoration: none; }
        @media (max-width: 768px) { .container { padding: 10px; } .contact-info { flex-direction: column; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="card header">
            <?php
            $profile_photo = 'uploads/potoh gweh.webp';
            if (file_exists($profile_photo)): 
            ?>
                <img src="<?php echo $profile_photo; ?>" alt="Foto Profil" class="profile-img">
            <?php else: ?>
                <div style="width:150px;height:150px;border-radius:50%;background:#007bff;color:white;display:flex;align-items:center;justify-content:center;font-size:48px;margin:0 auto;">
                    <?php echo substr($profile['nama'], 0, 1); ?>
                </div>
            <?php endif; ?>
            
            <h1 class="name"><?php echo $profile['nama']; ?></h1>
            <div class="title"><?php echo $profile['status']; ?> - <?php echo $profile['bidang_studi']; ?></div>
            
            <div class="contact-info">
                <?php if($kontak['email']): ?>
                <div class="contact-item">📧 <?php echo $kontak['email']; ?></div>
                <?php endif; ?>
                <?php if($kontak['whatsapp']): ?>
                <div class="contact-item">📱 <?php echo $kontak['whatsapp']; ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tentang Saya & Keterampilan -->
        <div class="card">
            <h2>-_- Tentang Saya </h2>
            <p style="margin-bottom: 20px;"><?php echo nl2br($profile['deskripsi_diri']); ?></p>
            
            <div class="skills-grid">
                <?php
                $skills_by_type = [];
                while($skill = $keterampilan->fetch_assoc()) {
                    $skills_by_type[$skill['jenis']][] = $skill;
                }
                
                foreach($skills_by_type as $type => $skills): 
                ?>
                <div class="skill-category">
                    <h3><?php echo $type; ?></h3>
                    <ul>
                        <?php foreach($skills as $skill): ?>
                        <li><?php echo $skill['nama_keterampilan']; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Pendidikan -->
        <div class="card">
            <h2>🎓 Pendidikan</h2>
            <?php while($edu = $pendidikan->fetch_assoc()): ?>
            <div class="timeline-item">
                <strong><?php echo $edu['institusi']; ?></strong><br>
                <?php echo $edu['tingkat']; ?> - <?php echo $edu['jurusan']; ?><br>
                <?php echo $edu['tahun_mulai']; ?> - <?php echo $edu['tahun_selesai']; ?>
            </div>
            <?php endwhile; ?>
        </div>

        <!-- Pengalaman dengan Foto -->
        <!-- Pengalaman dengan Foto -->
<div class="card">
    <h2>💼 Pengalaman</h2>
    <?php 
    $pengalaman_result->data_seek(0); // Reset pointer
    while($exp = $pengalaman_result->fetch_assoc()): 
    ?>
    <div class="timeline-item">
        <strong><?php echo $exp['nama_kegiatan']; ?></strong><br>
        <?php echo $exp['posisi']; ?> | <?php echo $exp['tahun_mulai']; ?><br>
        <?php echo nl2br($exp['deskripsi_tugas']); ?>
        
        <!-- Foto Pengalaman -->
        <?php if(isset($foto_data[$exp['id']]) && !empty($foto_data[$exp['id']])): ?>
        <div style="margin-top: 15px;">
            <h4 style="margin-bottom: 10px;">📸 Dokumentasi:</h4>
            <div class="photos-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-top: 10px;">
                <?php foreach($foto_data[$exp['id']] as $foto): 
                    $file_path = 'uploads/' . $foto['nama_file'];
                    $file_exists = file_exists($file_path);
                ?>
                <div class="photo-item" style="border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); background: white;">
                    <?php if($file_exists): ?>
                        <img src="<?php echo $file_path; ?>" 
                             alt="<?php echo $foto['deskripsi']; ?>" 
                             style="width: 100%; height: 180px; object-fit: cover; display: block;">
                    <?php else: ?>
                        <div style="background:#f8f9fa;height:180px;display:flex;align-items:center;justify-content:center;color:#666;flex-direction:column;padding:10px;">
                            <div style="font-size:24px;margin-bottom:5px;">❌</div>
                            <small style="text-align:center;">Foto Tidak Ditemukan</small>
                        </div>
                    <?php endif; ?>
                    <div style="padding: 8px 10px; background: white; font-size: 12px; color: #666; text-align: center; border-top: 1px solid #f0f0f0;">
                        <?php echo $foto['deskripsi'] ?: 'Dokumentasi ' . $exp['nama_kegiatan']; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endwhile; ?>
</div>

        <!-- Penghargaan -->
        <div class="card">
            <h2>🏆 Penghargaan</h2>
            <?php while($award = $penghargaan->fetch_assoc()): ?>
            <div class="timeline-item">
                <strong><?php echo $award['nama_penghargaan']; ?></strong><br>
                <?php echo $award['tahun']; ?> | <?php echo $award['tingkat']; ?>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <a href="kelola.php" class="admin-btn">✏️ Kelola Profil</a>
</body>
</html>