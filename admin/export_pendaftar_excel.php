<?php
require_once dirname(dirname(__FILE__)) . '/config.php';
require_login();

$totalRows = count_pendaftar();
log_activity('export_pendaftar_excel', 'Ekspor Excel data pendaftar sebanyak ' . $totalRows . ' data');

$filename = 'data-pendaftar-' . date('Ymd-His') . '.xls';

header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

echo "\xEF\xBB\xBF";

$columns = [
    'No',
    'No Pendaftaran',
    'Nama Pendaftar',
    'Jenis Kelamin',
    'Tempat Lahir',
    'Tanggal Lahir',
    'NIK',
    'KK',
    'Alamat',
    'Status Keluarga',
    'Anak Ke',
    'Jumlah Saudara',
    'Asal TK/RA',
    'Nama Ayah',
    'Pekerjaan Ayah',
    'Nama Ibu',
    'Pekerjaan Ibu',
    'Nama Wali',
    'Pekerjaan Wali',
    'Email',
    'No HP',
    'KIP',
    'PKH',
    'Status Daftar',
    'Tanggal Daftar',
];
?>
<table border="1">
    <thead>
        <tr>
            <?php foreach ($columns as $column): ?>
                <th><?= esc($column); ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        if ($result = $mysqli->query('SELECT * FROM pendaftar ORDER BY created_at DESC')):
            while ($row = $result->fetch_assoc()):
        ?>
        <tr>
            <td><?= $no++; ?></td>
            <td><?= esc($row['no_pendaftaran']); ?></td>
            <td><?= esc($row['nama_lengkap']); ?></td>
            <td><?= esc($row['jenis_kelamin']); ?></td>
            <td><?= esc($row['tempat_lahir']); ?></td>
            <td><?= esc($row['tanggal_lahir']); ?></td>
            <td style="mso-number-format:'\@';"><?= esc($row['nik']); ?></td>
            <td style="mso-number-format:'\@';"><?= esc($row['kk']); ?></td>
            <td><?= esc($row['alamat']); ?></td>
            <td><?= esc($row['status_keluarga']); ?></td>
            <td><?= esc((string)$row['anak_ke']); ?></td>
            <td><?= esc((string)$row['jumlah_saudara']); ?></td>
            <td><?= esc($row['asal_tk']); ?></td>
            <td><?= esc($row['nama_ayah']); ?></td>
            <td><?= esc($row['pekerjaan_ayah']); ?></td>
            <td><?= esc($row['nama_ibu']); ?></td>
            <td><?= esc($row['pekerjaan_ibu']); ?></td>
            <td><?= esc($row['nama_wali']); ?></td>
            <td><?= esc($row['pekerjaan_wali']); ?></td>
            <td><?= esc($row['email']); ?></td>
            <td style="mso-number-format:'\@';"><?= esc($row['hp']); ?></td>
            <td><?= esc($row['kip']); ?></td>
            <td><?= esc($row['pkh']); ?></td>
            <td><?= esc($row['status_daftar']); ?></td>
            <td><?= esc($row['created_at']); ?></td>
        </tr>
        <?php
            endwhile;
            $result->free();
        endif;
        ?>
    </tbody>
</table>
