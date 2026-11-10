document.addEventListener('DOMContentLoaded', function() {
    const btnTambah = document.getElementById('btnTambahPetugas');
    const modal = document.getElementById('modalRegisterPetugas');
    const btnClose = document.getElementById('closeRegisterPetugas');

    // Buka Modal
    if(btnTambah) {
        btnTambah.addEventListener('click', function() {
            modal.classList.add('show');
            modal.classList.remove('hidden');
        });
    }

    // Tutup Modal (Tombol X)
    if(btnClose) {
        btnClose.addEventListener('click', function() {
            closeModal();
        });
    }

    // Tutup Modal (Klik di luar area modal)
    window.addEventListener('click', function(e) {
        if (e.target == modal) {
            closeModal();
        }
    });

    function closeModal() {
        modal.classList.remove('show');
        // Delay sedikit agar animasi fade-out terlihat sebelum hidden
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
});