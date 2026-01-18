<?php
$success_message = flash('success');
$error_message = flash('error');
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

    <script src="https://cdn.ckeditor.com/4.25.1-lts/standard/ckeditor.js"></script>

    <script>
        $(function () {
            var successMessage = <?= json_encode($success_message); ?>;
            var errorMessage = <?= json_encode($error_message); ?>;

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

            $('.datatable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        className: 'btn btn-sm btn-success',
                        title: 'Data'
                    },
                    {
                        extend: 'pdfHtml5',
                        className: 'btn btn-sm btn-danger',
                        title: 'Data'
                    }
                ]
            });

            if (typeof window.initEditors === 'function') {
                var tries = 0;
                var checkEditors = function () {
                    if (window.CKEDITOR) {
                        window.initEditors();
                        return;
                    }
                    if (tries < 20) {
                        tries++;
                        setTimeout(checkEditors, 500);
                    }
                };
                checkEditors();
            }
        });
    </script>
</body>

</html>
