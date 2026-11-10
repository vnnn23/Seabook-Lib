document.addEventListener('DOMContentLoaded', function() {
    console.log('Modal script loaded');
    const btnTambah = document.getElementById('btnTambahBuku');
    const modal = document.getElementById('modalTambahBuku');
    const btnClose = document.querySelector('.close-btn');

    const btnTambahKategori = document.getElementById('btnTambahKategori');
    const modalKategori = document.getElementById('modalTambahKategori');
    const btnCloseKategori = document.querySelector('.close-btn-kategori');

    console.log('btnTambah:', btnTambah);
    console.log('modal:', modal);
    console.log('btnTambahKategori:', btnTambahKategori);
    console.log('modalKategori:', modalKategori);

    // Modal Buku
    if (btnTambah) {
        btnTambah.addEventListener('click', function() {
            console.log('Button Buku clicked');
            if (modal) {
                modal.style.display = 'flex';
                modal.style.opacity = '1';
                modal.style.visibility = 'visible';
            }
        });
    }

    if (btnClose) {
        btnClose.addEventListener('click', function() {
            if (modal) {
                modal.style.display = 'none';
                modal.style.opacity = '0';
                modal.style.visibility = 'hidden';
            }
        });
    }

    // Modal Kategori
    if (btnTambahKategori) {
        btnTambahKategori.addEventListener('click', function() {
            console.log('Button Kategori clicked');
            if (modalKategori) {
                modalKategori.style.display = 'flex';
                modalKategori.style.opacity = '1';
                modalKategori.style.visibility = 'visible';
            }
        });
    }

    if (btnCloseKategori) {
        btnCloseKategori.addEventListener('click', function() {
            if (modalKategori) {
                modalKategori.style.display = 'none';
                modalKategori.style.opacity = '0';
                modalKategori.style.visibility = 'hidden';
            }
        });
    }

    // Tutup modal klik di luar
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
            modal.style.opacity = '0';
            modal.style.visibility = 'hidden';
        }
        if (e.target === modalKategori) {
            modalKategori.style.display = 'none';
            modalKategori.style.opacity = '0';
            modalKategori.style.visibility = 'hidden';
        }
    });
});