<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = isset($_POST['aksi']) ? $_POST['aksi'] : '';

    if ($aksi === 'tambah') {
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? (string)$_POST['password'] : '';

        if ($username === '' || $password === '') {
            flash('error', 'Username dan password wajib diisi.');
            header('Location: ' . base_url('admin/index.php?page=pengguna'));
            exit;
        }

        $fotoName = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $tmp = $_FILES['foto']['tmp_name'];
            $name = $_FILES['foto']['name'];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png'];
            if (in_array($ext, $allowed, true)) {
                $uploadDir = __DIR__ . '/../../uploads';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $fotoName = 'user-' . date('YmdHis') . '.' . $ext;
                move_uploaded_file($tmp, $uploadDir . '/' . $fotoName);
            }
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $mysqli->prepare('INSERT INTO users (foto, username, password) VALUES (?, ?, ?)');
        if ($stmt) {
            $stmt->bind_param('sss', $fotoName, $username, $hash);
            if ($stmt->execute()) {
                flash('success', 'Pengguna baru berhasil ditambahkan.');
                log_activity('create_user', 'Tambah pengguna ' . $username);
            } else {
                flash('error', 'Gagal menambahkan pengguna.');
            }
            $stmt->close();
        } else {
            flash('error', 'Gagal menambahkan pengguna.');
        }
        echo '<script>window.location.href="' . esc(base_url('admin/index.php?page=pengguna')) . '";</script>';
        exit;
    }

    if ($aksi === 'edit') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? (string)$_POST['password'] : '';
        $fotoName = null;

        $current = null;
        if ($id > 0) {
            $stmt = $mysqli->prepare('SELECT id, username, foto, password FROM users WHERE id = ? LIMIT 1');
            if ($stmt) {
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $current = $result->fetch_assoc();
                $stmt->close();
            }
        }
        if (!$current && $username !== '') {
            $stmt = $mysqli->prepare('SELECT id, username, foto, password FROM users WHERE username = ? LIMIT 1');
            if ($stmt) {
                $stmt->bind_param('s', $username);
                $stmt->execute();
                $result = $stmt->get_result();
                $current = $result->fetch_assoc();
                $stmt->close();
                if ($current) {
                    $id = (int)$current['id'];
                }
            }
        }

        if (!$current) {
            flash('error', 'Pengguna tidak ditemukan.');
            echo '<script>window.location.href="' . esc(base_url('admin/index.php?page=pengguna')) . '";</script>';
            exit;
        }

        if ($username === '') {
            $username = (string)$current['username'];
        }

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $tmp = $_FILES['foto']['tmp_name'];
            $name = $_FILES['foto']['name'];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png'];
            if (in_array($ext, $allowed, true)) {
                $uploadDir = __DIR__ . '/../../uploads';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $fotoName = 'user-' . date('YmdHis') . '.' . $ext;
                move_uploaded_file($tmp, $uploadDir . '/' . $fotoName);
            }
        }

        $newFoto = $fotoName !== null ? $fotoName : (string)$current['foto'];
        $newPass = $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : (string)$current['password'];

        $stmt = $mysqli->prepare('UPDATE users SET foto = ?, username = ?, password = ? WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('sssi', $newFoto, $username, $newPass, $id);
            if ($stmt->execute()) {
                flash('success', 'Pengguna berhasil diperbarui.');
                log_activity('update_user', 'Perbarui pengguna ID ' . $id . ' (' . $username . ')');
            } else {
                flash('error', 'Gagal memperbarui pengguna.');
            }
            $stmt->close();
        } else {
            flash('error', 'Gagal memperbarui pengguna.');
        }
        echo '<script>window.location.href="' . esc(base_url('admin/index.php?page=pengguna')) . '";</script>';
        exit;
    }

    if ($aksi === 'hapus' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $username = '';
        $user_data = null;
        
        if ($st = $mysqli->prepare('SELECT id, username FROM users WHERE id=? LIMIT 1')) {
            $st->bind_param('i', $id);
            $st->execute();
            $result = $st->get_result();
            $user_data = $result->fetch_assoc();
            $st->close();
        }
        
        if ($user_data) {
            $username = $user_data['username'];
            
            // Check if this is the first user (primary admin) or the 'admin' user
            $first_user_result = $mysqli->query('SELECT id FROM users ORDER BY id ASC LIMIT 1');
            $first_user = $first_user_result ? $first_user_result->fetch_assoc() : null;
            
            if ($first_user && $id == $first_user['id']) {
                flash('error', 'Pengguna utama (admin pertama) tidak dapat dihapus.');
            } else if ($username === 'admin') {
                flash('error', 'Pengguna admin tidak dapat dihapus.');
            } else {
                $stmt = $mysqli->prepare('DELETE FROM users WHERE id=?');
                if ($stmt) {
                    $stmt->bind_param('i', $id);
                    if ($stmt->execute()) {
                        flash('success', 'Pengguna berhasil dihapus.');
                        log_activity('delete_user', 'Hapus pengguna ' . $username);
                    } else {
                        flash('error', 'Gagal menghapus pengguna.');
                    }
                    $stmt->close();
                }
            }
        } else {
            flash('error', 'Pengguna tidak ditemukan.');
        }
        header('Location: ' . base_url('admin/index.php?page=pengguna'));
        exit;
    }
}

$users = [];
$first_user_result = $mysqli->query('SELECT id FROM users ORDER BY id ASC LIMIT 1');
$first_user_id = null;

if ($first_user_result) {
    $first_row = $first_user_result->fetch_assoc();
    $first_user_id = $first_row['id'];
    $first_user_result->free();
}

if ($result = $mysqli->query('SELECT * FROM users ORDER BY id ASC')) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $result->free();
}
?>
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Pengguna</h1>
        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalPengguna">
            <i class="fas fa-user-plus"></i> Tambah Pengguna
        </button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Pengguna</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered datatable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Foto</th>
                            <th>Username</th>
                            <th>Password</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($users as $user): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td>
                                <?php if (!empty($user['foto'])): ?>
                                <img src="<?= esc(base_url('uploads/' . $user['foto'])); ?>" alt="Foto"
                                    class="img-profile rounded-circle" width="40" height="40">
                                <?php else: ?>
                                <img src="https://via.placeholder.com/40" alt="Foto"
                                    class="img-profile rounded-circle">
                                <?php endif; ?>
                            </td>
                            <td><?= esc($user['username']); ?></td>
                            <td>********</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-warning btn-edit-pengguna"
                                    data-id="<?= (int)$user['id']; ?>"
                                    data-username="<?= esc($user['username']); ?>"
                                    data-toggle="modal" data-target="#modalEditPengguna">
                                    Edit
                                </button>
                                <?php if ($user['username'] !== 'admin' && $user['id'] != $first_user_id): ?>
                                <form method="post" class="d-inline form-hapus-pengguna">
                                    <input type="hidden" name="aksi" value="hapus">
                                    <input type="hidden" name="id" value="<?= (int)$user['id']; ?>">
                                    <button type="button" class="btn btn-sm btn-danger btn-hapus-pengguna">
                                        Hapus
                                    </button>
                                </form>
                                <?php elseif ($user['id'] == $first_user_id): ?>
                                <button type="button" class="btn btn-sm btn-danger" disabled title="Akun utama tidak dapat dihapus">
                                    Hapus
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="modalEditPengguna" tabindex="-1" role="dialog" aria-labelledby="modalEditPenggunaLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="modalEditPenggunaLabel">Edit Pengguna</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="aksi" value="edit">
                    <input type="hidden" name="id" id="editUserId" value="">
                    <div class="form-group">
                        <label>Foto</label>
                        <input type="file" name="foto" class="form-control-file">
                    </div>
                    <div class="form-group">
                        <label>Username *</label>
                        <input type="text" name="username" id="editUsername" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Password (kosongkan jika tidak diubah)</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
    </div>
 
<div class="modal fade" id="modalPengguna" tabindex="-1" role="dialog" aria-labelledby="modalPenggunaLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalPenggunaLabel">Tambah Pengguna</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="aksi" value="tambah">
                    <div class="form-group">
                        <label>Foto</label>
                        <input type="file" name="foto" class="form-control-file">
                    </div>
                    <div class="form-group">
                        <label>Username *</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Password *</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(function () {
        $('.btn-hapus-pengguna').on('click', function () {
            var form = $(this).closest('form');
            Swal.fire({
                title: 'Hapus Pengguna?',
                text: 'Data pengguna yang dihapus tidak dapat dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
        $(document).on('click', '.btn-edit-pengguna', function () {
            var id = $(this).data('id');
            var username = $(this).data('username');
            $('#editUserId').val(id);
            $('#editUsername').val(username);
            $('#modalEditPengguna').modal('show');
        });
    });
</script>
