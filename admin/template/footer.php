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
$tahun_ajaran = get_option('tahun_ajaran', date('Y') . '/' . (date('Y') + 1));
?>
            </div>

            <footer class="sticky-footer" style="background: linear-gradient(135deg, #065f46, #047857); padding: 1rem 0; display: flex; align-items: center;">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span style="color: #ffffff !important;">Sistem Penerimaan Peserta Didik Baru MI Sultan Fattah Sukosono @ <?= date('Y'); ?></span>
                        <span style="color: rgba(255,255,255,0.5) !important; font-size: 0.75rem; margin-left: 0.5rem;">v<?= app_version(); ?></span>
                    </div>
                </div>
            </footer>

        </div>

    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <form id="logoutForm" action="logout.php" method="post" style="display:none;"></form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
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
            var ACADEMIC_YEAR = <?= json_encode($tahun_ajaran); ?>;
            var ACADEMIC_YEAR_SLUG = (ACADEMIC_YEAR || '').replace(/[^\w-]+/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');

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

            var SIDEBAR_STATE_KEY = 'ppdb_admin_sidebar_state';

            var setSidebarCollapsed = function (collapsed) {
                var $body = $('body');
                var $sidebar = $('.sidebar');

                $body.toggleClass('sidebar-toggled', collapsed).removeClass('sidebar-open');
                $sidebar.toggleClass('toggled', collapsed);
            };

            var getStoredSidebarCollapsed = function () {
                try {
                    return window.localStorage.getItem(SIDEBAR_STATE_KEY) === 'collapsed';
                } catch (e) {
                    return false;
                }
            };

            var storeSidebarCollapsed = function (collapsed) {
                try {
                    window.localStorage.setItem(SIDEBAR_STATE_KEY, collapsed ? 'collapsed' : 'expanded');
                } catch (e) {}
            };

            var initMobileSidebar = function () {
                if ($(window).width() <= 768) {
                    setSidebarCollapsed(true);
                } else {
                    setSidebarCollapsed(getStoredSidebarCollapsed());
                }
            };

            var adjustAdminTables = function () {
                if (!$.fn.DataTable) return;
                setTimeout(function () {
                    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
                }, 260);
            };

            initMobileSidebar();
            $(window).on('resize', function () {
                initMobileSidebar();
                adjustAdminTables();
            });

            $('#sidebarToggleTop, #sidebarToggle').on('click', function () {
                var $body = $('body');
                setTimeout(function () {
                    if ($body.hasClass('sidebar-toggled')) {
                        $body.removeClass('sidebar-open');
                    } else {
                        $body.addClass('sidebar-open');
                    }
                    if ($(window).width() > 768) {
                        storeSidebarCollapsed($body.hasClass('sidebar-toggled'));
                    }
                }, 0);
                adjustAdminTables();
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
                        title: APP_NAME + ' - ' + MADRASAH_NAME + ' - TA ' + ACADEMIC_YEAR + ' - ' + TODAY,
                        filename: 'export_ta_' + (ACADEMIC_YEAR_SLUG || 'default'),
                        customize: function (xlsx) {
                            var sheet = xlsx.xl.worksheets['sheet1.xml'];
                            var pageSetup = $('<pageSetup/>').attr('paperSize', '14').attr('orientation', 'landscape');
                            $('worksheet', sheet).append(pageSetup);
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        className: 'btn btn-sm btn-danger',
                        title: APP_NAME + ' - ' + MADRASAH_NAME + ' - TA ' + ACADEMIC_YEAR + ' - ' + TODAY,
                        customize: function (doc) {
                            doc.pageSize = { width: 935, height: 609 };
                            doc.pageMargins = [20, 20, 20, 20];
                            doc.content.unshift({
                                text: APP_NAME + '\n' + MADRASAH_NAME + '\n' + 'TA: ' + ACADEMIC_YEAR + '\nTanggal: ' + TODAY,
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
                            title: APP_NAME + ' - ' + MADRASAH_NAME + ' - TA ' + ACADEMIC_YEAR + ' - ' + TODAY,
                            filename: 'data_pendaftar_ta_' + (ACADEMIC_YEAR_SLUG || 'default'),
                            exportOptions: {
                                columns: ':visible:not(:last-child)',
                                format: { body: function (data) { return (data || '').replace(/<[^>]*>/g, '').trim(); } }
                            },
                            customize: function (xlsx) {
                                var sheet = xlsx.xl.worksheets['sheet1.xml'];
                                var pageSetup = $('<pageSetup/>').attr('paperSize', '14').attr('orientation', 'landscape');
                                $('worksheet', sheet).append(pageSetup);
                            }
                        },
                        {
                            text: 'PDF',
                            className: 'btn btn-sm btn-danger',
                            action: function () {
                                var printUrl = 'laporan_pendaftar.php';
                                var hasStatusFilter = window.location.search.match(/[?&]status=([^&]+)/);
                                if (hasStatusFilter && hasStatusFilter[1]) {
                                    printUrl += '?status=' + encodeURIComponent(decodeURIComponent(hasStatusFilter[1]));
                                }
                                window.open(printUrl, '_blank');
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

                $(document).on('click', '.btn-edit-pendaftar', function () {
                    var raw = $(this).attr('data-pendaftar') || '{}';
                    var data = {};
                    try {
                        data = JSON.parse(raw);
                    } catch (e) {
                        data = {};
                    }
                    var $form = $('#formEditPendaftar');
                    $form[0].reset();
                    Object.keys(data).forEach(function (key) {
                        var $field = $form.find('[name="' + key + '"]');
                        if (!$field.length) return;
                        if ($field.attr('type') === 'checkbox') {
                            $field.prop('checked', data[key] === 'Ya');
                        } else {
                            $field.val(data[key]);
                        }
                    });
                    $form.find('[name="id"]').val(data.id || '');
                    $('#modalEditPendaftar').modal('show');
                });

                $('#modalTambahPendaftar').on('hidden.bs.modal', function () {
                    var form = $(this).find('form')[0];
                    if (form) form.reset();
                });

                $('#modalTambahPendaftar, #modalEditPendaftar').on('keypress', 'input[name="nik"], input[name="kk"], input[name="hp"]', function(e) {
                    if (e.which < 48 || e.which > 57) {
                        e.preventDefault();
                    }
                }).on('paste', 'input[name="nik"], input[name="kk"], input[name="hp"]', function(e) {
                    e.preventDefault();
                    var text = (e.originalEvent || e).clipboardData.getData('text/plain');
                    this.value = text.replace(/[^0-9]/g, '');
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
                var $span = $this.find('span.notif-text');
                
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

            // Mark All as Read
            $('#markAllReadBtn').on('click', function(e) {
                e.preventDefault();
                var $btn = $(this);
                $btn.prop('disabled', true);
                $.post('api/mark_all_read.php', function(response) {
                    // Update UI
                    $('.notif-item span.notif-text').removeClass('font-weight-bold');
                    $('.badge-counter').remove();
                    $('#alertsDropdown').dropdown('toggle');
                }, 'json').fail(function() {
                    $btn.prop('disabled', false);
                });
            });

            // Update Sistem
            $('#btnUpdateSistem').on('click', function(e) {
                e.preventDefault();
                $('#userDropdown').dropdown('toggle');

                Swal.fire({
                    title: 'Update Sistem?',
                    text: 'Sistem akan diperbarui ke versi terbaru.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-cloud-download-alt"></i> Ya, Update',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#1cc88a'
                }).then(function(result) {
                    if (!result.isConfirmed) return;

                    // Custom overlay — no SweetAlert2, no scroll
                    var overlay = document.getElementById('updateOverlay');
                    overlay.style.display = 'flex';

                    fetch('api/update_system.php', { method: 'POST' })
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            overlay.style.display = 'none';
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Update Berhasil!',
                                    text: (data.message || 'Sistem berhasil diperbarui.') + (data.version ? ' (v' + data.version + ')' : ''),
                                    confirmButtonText: 'Muat Ulang Halaman',
                                    confirmButtonColor: '#4e73df'
                                }).then(function() {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Update Gagal',
                                    text: data.message || 'Terjadi kesalahan.',
                                    confirmButtonText: 'Tutup'
                                });
                            }
                        })
                        .catch(function(err) {
                            overlay.style.display = 'none';
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Tidak dapat terhubung ke server: ' + err.message,
                                confirmButtonText: 'Tutup'
                            });
                        });
                });
            });
        });
    </script>

    <!-- Custom loading overlay for update — pure HTML, no scroll -->
    <div id="updateOverlay" style="display:none; position:fixed; inset:0; z-index:99999;
         background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:12px; padding:2rem 3rem; text-align:center;
                    box-shadow:0 8px 32px rgba(0,0,0,0.2);">
            <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
            <div style="font-weight:600; color:#333;">Memperbarui sistem...</div>
        </div>
    </div>
</body>

</html>
