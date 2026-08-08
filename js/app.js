// App JS - Inventory Management System BTN
(function () {
    'use strict';

    // Sidebar toggle
    var toggle = document.getElementById('sidebarToggle');
    var sidebar = document.getElementById('sidebar');
    if (toggle && sidebar) {
        toggle.addEventListener('click', function () {
            sidebar.classList.toggle('collapsed');
            var collapsed = sidebar.classList.contains('collapsed');
            document.cookie = 'sidebar_collapsed=' + (collapsed ? '1' : '0') + ';path=/;max-age=31536000';
        });
    }

    // DataTables default
    if (window.jQuery && window.DataTable) {
        $.extend(true, $.fn.dataTable.defaults, {
            language: {
                sEmptyTable: 'Tidak ada data tersedia',
                sInfo: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                sInfoEmpty: 'Menampilkan 0 data',
                sInfoFiltered: '(disaring dari _MAX_ data)',
                sLengthMenu: 'Tampilkan _MENU_ data',
                sLoadingRecords: 'Memuat...',
                sProcessing: 'Memproses...',
                sSearch: 'Cari:',
                sZeroRecords: 'Data tidak ditemukan',
                oPaginate: {
                    sFirst: 'Pertama', sLast: 'Terakhir', sNext: 'Selanjutnya', sPrevious: 'Sebelumnya'
                }
            },
            pageLength: 10,
            responsive: true,
            autoWidth: false
        });
    }

    // Global toast helper
    window.showToast = function (icon, title) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: icon,
            title: title,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    };

    // Confirm delete helper
    window.confirmDelete = function (url, opts) {
        opts = opts || {};
        Swal.fire({
            title: opts.title || 'Hapus data ini?',
            text: opts.text || 'Data yang dihapus tidak dapat dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
        return false;
    };

    // Loading overlay
    window.showLoading = function () {
        var ov = document.getElementById('loadingOverlay');
        if (ov) ov.classList.add('show');
    };
    window.hideLoading = function () {
        var ov = document.getElementById('loadingOverlay');
        if (ov) ov.classList.remove('show');
    };

    // Auto logout warning
    var warnAt = 25 * 60 * 1000;
    setTimeout(function () {
        if (document.cookie.indexOf('IMS_BTN_SESSION') !== -1) {
            showToast('info', 'Sesi akan berakhir segera. Simpan pekerjaan Anda.');
        }
    }, warnAt);

    // Form submit loading
    document.addEventListener('submit', function (e) {
        var f = e.target;
        if (f.dataset.loading !== 'off') {
            showLoading();
        }
    });

    // ===== Dark Mode =====
    var darkToggle = document.getElementById('darkModeToggle');
    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        if (darkToggle) {
            darkToggle.innerHTML = theme === 'dark' ? '<i class="bi bi-sun"></i>' : '<i class="bi bi-moon-stars"></i>';
        }
    }
    var savedTheme = localStorage.getItem('ims_theme') || 'light';
    applyTheme(savedTheme);
    if (darkToggle) {
        darkToggle.addEventListener('click', function () {
            var current = document.documentElement.getAttribute('data-theme');
            var next = current === 'dark' ? 'light' : 'dark';
            localStorage.setItem('ims_theme', next);
            applyTheme(next);
            showToast('success', next === 'dark' ? 'Mode gelap aktif' : 'Mode terang aktif');
        });
    }

    // ===== Keyboard Shortcuts =====
    document.addEventListener('keydown', function (e) {
        if (e.altKey && !e.ctrlKey && !e.metaKey) {
            switch (e.key.toLowerCase()) {
                case 'n':
                    e.preventDefault();
                    window.location.href = BASE_URL + '/pages/barang-form.php';
                    break;
                case 's':
                    e.preventDefault();
                    var search = document.getElementById('globalSearchInput');
                    if (search) { search.focus(); search.select(); }
                    break;
                case 'd':
                    e.preventDefault();
                    if (darkToggle) darkToggle.click();
                    break;
            }
        }
    });

    // ===== Dashboard Customize =====
    var customizeBtn = document.getElementById('customizeDashboardBtn');
    var customizePanel = document.getElementById('customizePanel');
    if (customizeBtn && customizePanel) {
        customizeBtn.addEventListener('click', function () {
            customizePanel.classList.toggle('d-none');
        });
        document.querySelectorAll('.dash-card-toggle').forEach(function (cb) {
            var cardId = cb.getAttribute('data-card');
            var saved = localStorage.getItem('dash_card_' + cardId);
            if (saved === 'hidden') {
                var card = document.getElementById(cardId);
                if (card) card.classList.add('dashboard-card-hidden');
                cb.checked = false;
            }
            cb.addEventListener('change', function () {
                var card = document.getElementById(cardId);
                if (!card) return;
                if (cb.checked) {
                    card.classList.remove('dashboard-card-hidden');
                    localStorage.removeItem('dash_card_' + cardId);
                } else {
                    card.classList.add('dashboard-card-hidden');
                    localStorage.setItem('dash_card_' + cardId, 'hidden');
                }
            });
        });
    }

})();
