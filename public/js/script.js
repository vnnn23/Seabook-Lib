document.addEventListener('DOMContentLoaded', function() {
    console.log('Script loaded');
    const btnTambah = document.getElementById('btnTambahBuku');
    const modal = document.getElementById('modalTambahBuku');
    const btnClose = document.querySelector('.close-btn');

    console.log('btnTambah:', btnTambah);
    console.log('modal:', modal);

    if (btnTambah && modal) {
        // Buka Modal
        btnTambah.addEventListener('click', function() {
            console.log('Button clicked');
            modal.classList.add('show');
            modal.classList.remove('hidden');
        });

        // Tutup Modal (Tombol X)
        if (btnClose) {
            btnClose.addEventListener('click', function() {
                modal.classList.remove('show');
                modal.classList.add('hidden');
            });
        }

        // Tutup Modal (Klik di luar area modal)
        window.addEventListener('click', function(e) {
            if (e.target == modal) {
                modal.classList.remove('show');
                modal.classList.add('hidden');
            }
        });
    } else {
        console.log('Elements not found');
    }
});