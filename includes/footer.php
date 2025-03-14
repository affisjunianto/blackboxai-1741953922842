</div> <!-- Close main-content div -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Initialize Select2
        $(document).ready(function() {
            $('.select2').select2();
            
            // Initialize DataTables
            $('.datatable').DataTable({
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search..."
                }
            });
            
            // Theme Toggle Functionality
            const themeToggle = document.getElementById('themeToggle');
            const darkIcon = document.getElementById('darkIcon');
            const lightIcon = document.getElementById('lightIcon');
            
            // Check for saved theme preference or default to 'light'
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.body.setAttribute('data-theme', savedTheme);
            updateThemeIcons(savedTheme);
            
            themeToggle.addEventListener('click', () => {
                const currentTheme = document.body.getAttribute('data-theme');
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                
                document.body.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                updateThemeIcons(newTheme);
            });
            
            function updateThemeIcons(theme) {
                if (theme === 'dark') {
                    darkIcon.classList.add('d-none');
                    lightIcon.classList.remove('d-none');
                } else {
                    darkIcon.classList.remove('d-none');
                    lightIcon.classList.add('d-none');
                }
            }
            
            // Toast Notification Function
            window.showNotification = function(message, type = 'info') {
                const toast = document.getElementById('notificationToast');
                const toastBody = toast.querySelector('.toast-body');
                toastBody.textContent = message;
                
                // Add appropriate background color based on type
                toast.className = `toast bg-${type} text-white`;
                
                const bsToast = new bootstrap.Toast(toast);
                bsToast.show();
            };
            
            // Form Validation
            $('form').on('submit', function(e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                $(this).addClass('was-validated');
            });
            
            // CSRF Token for AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '<?php echo generateCSRFToken(); ?>'
                }
            });
            
            // Confirmation Dialog
            window.confirmAction = function(message, callback) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, proceed!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        callback();
                    }
                });
            };
            
            // Success Message
            window.showSuccess = function(message) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: message,
                    timer: 2000,
                    showConfirmButton: false
                });
            };
            
            // Error Message
            window.showError = function(message) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: message
                });
            };
            
            // Format number with commas
            window.formatNumber = function(number) {
                return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            };
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>
</body>
</html>