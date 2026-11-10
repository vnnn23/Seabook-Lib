function openTab(evt, tabName) {
    // 1. Sembunyikan semua konten tab
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tab-content");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }

    // 2. Hapus class 'active' dari semua tombol tab
    tablinks = document.getElementsByClassName("tab-link");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }

    // 3. Tampilkan tab yang dipilih dan tambahkan class 'active' ke tombolnya
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.className += " active";
}

// Modal Upload Foto
document.addEventListener('DOMContentLoaded', function() {
    const btnUploadFoto = document.getElementById('btnUploadFoto');
    const modalUploadFoto = document.getElementById('modalUploadFoto');
    const closeUploadFoto = document.getElementById('closeUploadFoto');

    if (btnUploadFoto && modalUploadFoto) {
        btnUploadFoto.addEventListener('click', function() {
            modalUploadFoto.classList.add('show');
            modalUploadFoto.classList.remove('hidden');
        });

        if (closeUploadFoto) {
            closeUploadFoto.addEventListener('click', function() {
                modalUploadFoto.classList.remove('show');
                modalUploadFoto.classList.add('hidden');
            });
        }

        window.addEventListener('click', function(e) {
            if (e.target === modalUploadFoto) {
                modalUploadFoto.classList.remove('show');
                modalUploadFoto.classList.add('hidden');
            }
        });
    }

    // Modal Logout
    const btnLogout = document.getElementById('btnLogout');
    const modalLogout = document.getElementById('modalLogout');
    const closeLogout = document.getElementById('closeLogout');
    const confirmLogout = document.getElementById('confirmLogout');
    const cancelLogout = document.getElementById('cancelLogout');

    if (btnLogout && modalLogout) {
        btnLogout.addEventListener('click', function(e) {
            e.preventDefault();
            modalLogout.classList.add('show');
            modalLogout.classList.remove('hidden');
        });

        if (closeLogout) {
            closeLogout.addEventListener('click', function() {
                modalLogout.classList.remove('show');
                modalLogout.classList.add('hidden');
            });
        }

        if (cancelLogout) {
            cancelLogout.addEventListener('click', function() {
                modalLogout.classList.remove('show');
                modalLogout.classList.add('hidden');
            });
        }

        if (confirmLogout) {
            confirmLogout.addEventListener('click', function() {
                // Submit form logout
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = logoutUrl;
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = csrfToken;
                form.appendChild(csrf);
                document.body.appendChild(form);
                form.submit();
            });
        }

        window.addEventListener('click', function(e) {
            if (e.target === modalLogout) {
                modalLogout.classList.remove('show');
                modalLogout.classList.add('hidden');
            }
        });
    }
});