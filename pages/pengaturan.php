<?php
$page_title = 'Pengaturan';
$active_menu = 'pengaturan';
require_once __DIR__ . '/../includes/header.php';

$admin = current_admin();
clear_old();
?>

<div class="page-title-bar fade-in">
    <h4>Pengaturan</h4>
    <nav class="breadcrumb">
        <a class="breadcrumb-item" href="<?= BASE_URL ?>/pages/dashboard.php">Dashboard</a>
        <span class="breadcrumb-item active">Pengaturan</span>
    </nav>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header"><span><i class="bi bi-person text-primary me-2"></i>Edit Profil Admin</span></div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>/actions/profil-save.php" method="post" enctype="multipart/form-data">
                    <div class="text-center mb-3">
                        <?php $foto_src = !empty($admin['foto']) ? BASE_URL . '/uploads/' . e($admin['foto']) : BASE_URL . '/images/avatar-default.png'; ?>
                        <img src="<?= $foto_src ?>" class="rounded-circle" width="80" height="80" style="object-fit:cover" alt="" id="fotoPreview">
                        <div class="mt-2">
                            <label class="btn btn-sm btn-soft cursor-pointer">
                                <i class="bi bi-camera me-1"></i> Ganti Foto
                                <input type="file" name="foto" class="d-none" accept="image/jpeg,image/png,image/gif,image/webp" onchange="previewFoto(this)">
                            </label>
                        </div>
                        <div class="form-text small">Format: JPG, PNG, GIF, WebP. Maks 2MB.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap <span class="required">*</span></label>
                        <input type="text" name="nama" class="form-control" value="<?= e($admin['nama']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= e($admin['email']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. Telepon</label>
                        <input type="text" name="no_telepon" class="form-control" value="<?= e($admin['no_telepon']) ?>">
                    </div>
                    <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan Profil</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card" id="password">
            <div class="card-header"><span><i class="bi bi-key text-primary me-2"></i>Ganti Password</span></div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>/actions/password-save.php" method="post">
                    <div class="mb-3">
                        <label class="form-label">Password Lama <span class="required">*</span></label>
                        <input type="password" name="password_lama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru <span class="required">*</span></label>
                        <input type="password" name="password_baru" class="form-control" required minlength="6">
                        <div class="form-text">Minimal 6 karakter.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password <span class="required">*</span></label>
                        <input type="password" name="password_konfirmasi" class="form-control" required>
                    </div>
                    <button class="btn btn-primary"><i class="bi bi-shield-check me-1"></i> Ubah Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><span><i class="bi bi-database text-primary me-2"></i>Backup & Restore Database</span></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <h6>Backup Database</h6>
                        <p class="text-muted small">Unduh file SQL cadangan dari seluruh database.</p>
                        <a href="<?= BASE_URL ?>/actions/backup.php" class="btn btn-success"><i class="bi bi-download me-1"></i> Backup Sekarang</a>
                    </div>
                    <div class="col-md-6">
                        <h6>Restore Database</h6>
                        <p class="text-muted small">Unggah file SQL untuk memulihkan database. <strong>Peringatan:</strong> akan menimpa data saat ini.</p>
                        <form action="<?= BASE_URL ?>/actions/restore.php" method="post" enctype="multipart/form-data" onsubmit="return confirmRestore()">
                            <input type="file" name="sql_file" class="form-control mb-2" accept=".sql" required>
                            <button class="btn btn-warning"><i class="bi bi-upload me-1"></i> Restore</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$extra_js = "
function confirmRestore() {
    Swal.fire({
        title: 'Restore database?',
        text: 'Data saat ini akan ditimpa. Pastikan sudah backup terlebih dahulu.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#F59E0B',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Ya, restore',
        cancelButtonText: 'Batal'
    }).then(function(r) { if (!r.isConfirmed) { window.event.preventDefault(); } });
    return true;
}
function previewFoto(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) { document.getElementById('fotoPreview').src = e.target.result; };
        reader.readAsDataURL(input.files[0]);
    }
}
";
require_once __DIR__ . '/../includes/footer.php';
?>
