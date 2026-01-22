<?php
$success_message = flash('success');
$error_message = flash('error');
$madrasah_name = '';
if ($res = $mysqli->query('SELECT nama FROM madrasah LIMIT 1')) {
    if ($row = $res->fetch_assoc()) {
        $madrasah_name = $row['nama'];
    }
    $res->free();
}
?>
            </div>

            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Sistem Penerimaan Peserta Didik Baru MI Sultan Fattah Sukosono @ <?= date('Y'); ?></span>
                    </div>
                </div>
            </footer>

        </div>

    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <form id="logoutForm" action="logout.php" method="post" style="display:none;"></form>

    <script src="../assets/vendor/jquery/jquery.min.js"></script>
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../assets/js/sb-admin-2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <script>
        $(function () {
            var successMessage = <?= json_encode($success_message); ?>;
            var errorMessage = <?= json_encode($error_message); ?>;
            var APP_NAME = <?= json_encode('Sistem PPDB Online'); ?>;
            var MADRASAH_NAME = <?= json_encode($madrasah_name); ?>;
            var TODAY = <?= json_encode(date('d-m-Y')); ?>;

            (function initAdminClock() {
                var $clock = $('#adminClock');
                if ($clock.length === 0) return;
                var updateClock = function () {
                    var now = new Date();
                    var dateStr = now.toLocaleDateString('id-ID', {
                        weekday: 'long',
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric',
                        timeZone: 'Asia/Jakarta'
                    });
                    var timeStr = now.toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: false,
                        timeZone: 'Asia/Jakarta'
                    }).replace(/\./g, ':');
                    $clock.text(dateStr + ' ' + timeStr + ' WIB');
                };
                updateClock();
                setInterval(updateClock, 1000);
            })();

            var initMobileSidebar = function () {
                var $body = $('body');
                var $sidebar = $('.sidebar');
                if ($(window).width() <= 768) {
                    $body.addClass('sidebar-toggled').removeClass('sidebar-open');
                    $sidebar.addClass('toggled');
                } else {
                    $body.removeClass('sidebar-toggled sidebar-open');
                    $sidebar.removeClass('toggled');
                }
            };

            initMobileSidebar();
            $(window).on('resize', initMobileSidebar);

            $('#sidebarToggleTop, #sidebarToggle').on('click', function () {
                var $body = $('body');
                setTimeout(function () {
                    if ($body.hasClass('sidebar-toggled')) {
                        $body.removeClass('sidebar-open');
                    } else {
                        $body.addClass('sidebar-open');
                    }
                }, 0);
            });

            $(document).on('click', '.sidebar-overlay', function () {
                var $body = $('body');
                var $sidebar = $('.sidebar');
                if (!$body.hasClass('sidebar-toggled')) {
                    $body.addClass('sidebar-toggled').removeClass('sidebar-open');
                    $sidebar.addClass('toggled');
                }
            });

            if (successMessage) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: successMessage,
                    timer: 3500,
                    showConfirmButton: false
                });
            }

            if (errorMessage) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: errorMessage,
                    timer: 3500,
                    showConfirmButton: false
                });
            }

            $('#btnLogout').on('click', function (e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Logout?',
                    text: 'Anda yakin ingin keluar dari dashboard?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Logout',
                    cancelButtonText: 'Batal'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $('#logoutForm').submit();
                    }
                });
            });

            $('.datatable').not('#tablePendaftar').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        className: 'btn btn-sm btn-success',
                        title: APP_NAME + ' - ' + MADRASAH_NAME + ' - ' + TODAY,
                        filename: 'export',
                        customize: function (xlsx) {
                            var sheet = xlsx.xl.worksheets['sheet1.xml'];
                            var pageSetup = $('<pageSetup/>').attr('paperSize', '14').attr('orientation', 'landscape');
                            $('worksheet', sheet).append(pageSetup);
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        className: 'btn btn-sm btn-danger',
                        title: APP_NAME + ' - ' + MADRASAH_NAME + ' - ' + TODAY,
                        customize: function (doc) {
                            doc.pageSize = { width: 935, height: 609 };
                            doc.pageMargins = [20, 20, 20, 20];
                            doc.content.unshift({
                                text: APP_NAME + '\n' + MADRASAH_NAME + '\n' + 'Tanggal: ' + TODAY,
                                alignment: 'center',
                                margin: [0, 0, 0, 10],
                                fontSize: 10
                            });
                        }
                    }
                ]
            });

            if ($('#tablePendaftar').length) {
                $('#tablePendaftar').DataTable({
                    responsive: true,
                    dom: 'Bfrtip',
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            className: 'btn btn-sm btn-success',
                            title: APP_NAME + ' - ' + MADRASAH_NAME + ' - ' + TODAY,
                            filename: 'data_pendaftar',
                            exportOptions: {
                                columns: [1,2,3,4,5,6,7,8,9,10],
                                format: { body: function (data) { return (data || '').replace(/<[^>]*>/g, '').trim(); } }
                            },
                            customize: function (xlsx) {
                                var sheet = xlsx.xl.worksheets['sheet1.xml'];
                                var pageSetup = $('<pageSetup/>').attr('paperSize', '14').attr('orientation', 'landscape');
                                $('worksheet', sheet).append(pageSetup);
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            className: 'btn btn-sm btn-danger',
                            title: APP_NAME + ' - ' + MADRASAH_NAME + ' - ' + TODAY,
                            filename: 'data_pendaftar',
                            exportOptions: {
                                columns: [1,2,3,4,5,6,7,8,9,10],
                                format: { body: function (data) { return (data || '').replace(/<[^>]*>/g, '').trim(); } }
                            },
                            customize: function (doc) {
                                doc.pageSize = { width: 935, height: 609 };
                                doc.pageMargins = [20, 20, 20, 20];
                                doc.content.unshift({
                                    text: APP_NAME + '\n' + MADRASAH_NAME + '\n' + 'Tanggal: ' + TODAY,
                                    alignment: 'center',
                                    margin: [0, 0, 0, 10],
                                    fontSize: 10
                                });
                            }
                        }
                    ]
                });
            }

            if ($('.btn-hapus-backup').length) {
                $(document).on('click', '.btn-hapus-backup', function () {
                    var form = $(this).closest('form');
                    Swal.fire({
                        title: 'Hapus Backup?',
                        text: 'File backup yang dihapus tidak dapat dikembalikan.',
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
            }

            if ($('#formPendaftar').length) {
                $('#checkAll').on('change', function () {
                    $('.check-item').prop('checked', $(this).is(':checked'));
                });

                $('#btnResetTotal').on('click', function () {
                    Swal.fire({
                        title: 'Reset Total?',
                        text: 'Semua data pendaftar akan dihapus dan nomor direset.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, reset',
                        cancelButtonText: 'Batal'
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            $('#aksiGlobal').val('reset_total');
                            $('#formPendaftar').submit();
                        }
                    });
                });

                $(document).on('click', '.btn-email', function () {
                    var id = $(this).data('id');
                    Swal.fire({
                        title: 'Kirim Email?',
                        text: 'Kirim informasi status ke email pendaftar ini?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, kirim',
                        cancelButtonText: 'Batal'
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            $('#aksiGlobal').val('kirim_email');
                            $('#idGlobal').val(id);
                            $('#formPendaftar').submit();
                        }
                    });
                });

                $(document).on('click', '.btn-status', function () {
                    var id = $(this).data('id');
                    var status = $(this).data('status');
                    var text = status === 'diterima' ? 'menerima' : 'menolak';
                    Swal.fire({
                        title: 'Ubah Status?',
                        text: 'Anda yakin akan ' + text + ' pendaftar ini?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, lanjutkan',
                        cancelButtonText: 'Batal'
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            $('#aksiGlobal').val('ubah_status');
                            $('#idGlobal').val(id);
                            $('#statusGlobal').val(status);
                            $('#formPendaftar').submit();
                        }
                    });
                });

                $(document).on('click', '.btn-hapus', function () {
                    var id = $(this).data('id');
                    Swal.fire({
                        title: 'Hapus Data?',
                        text: 'Data yang dihapus tidak dapat dikembalikan.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, hapus',
                        cancelButtonText: 'Batal'
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            $('#aksiGlobal').val('hapus');
                            $('#idGlobal').val(id);
                            $('#formPendaftar').submit();
                        }
                    });
                });

                $('#btnHapusTerpilih').on('click', function () {
                    if ($('.check-item:checked').length === 0) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Tidak ada data',
                            text: 'Silakan pilih data yang akan dihapus.'
                        });
                        return;
                    }
                    Swal.fire({
                        title: 'Hapus Data Terpilih?',
                        text: 'Data yang dihapus tidak dapat dikembalikan.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, hapus',
                        cancelButtonText: 'Batal'
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            $('#aksiGlobal').val('hapus_terpilih');
                            $('#formPendaftar').submit();
                        }
                    });
                });
            }

            if (typeof window.initEditors === 'function') {
                window.initEditors();
            }

            if ($('#btnResetNomor').length) {
                $(document).on('click', '#btnResetNomor', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Reset Nomor Pendaftaran?',
                        text: 'Nomor berikutnya akan kembali ke 0001 untuk tahun berjalan.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, reset',
                        cancelButtonText: 'Batal'
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            $('#formResetNomor').submit();
                        }
                    });
                });
            }

            // Notification Handler
            $('.notif-item').on('click', function(e) {
                e.preventDefault();
                var $this = $(this);
                var id = $this.data('id');
                var href = $this.attr('href');
                var $span = $this.find('span');
                
                if ($span.hasClass('font-weight-bold')) {
                    $.post('api/mark_read.php', { id: id }, function(response) {
                        // Redirect regardless of success
                        if (href && href !== '#') {
                            window.location.href = href;
                        }
                    }, 'json').fail(function() {
                        if (href && href !== '#') {
                            window.location.href = href;
                        }
                    });
                } else {
                    if (href && href !== '#') {
                        window.location.href = href;
                    }
                }
            });
        });
    </script>
</body>

</html>
