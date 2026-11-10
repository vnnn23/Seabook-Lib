document.addEventListener("DOMContentLoaded", function() {
    // Logic untuk Accordion FAQ
    var acc = document.getElementsByClassName("accordion");
    var i;

    for (i = 0; i < acc.length; i++) {
        acc[i].addEventListener("click", function() {
            this.classList.toggle("active");
            var panel = this.nextElementSibling;
            if (panel.style.maxHeight) {
                panel.style.maxHeight = null;
                panel.style.paddingTop = null;
                panel.style.paddingBottom = null;
            } else {
                panel.style.maxHeight = panel.scrollHeight + "px";
                panel.style.paddingTop = "10px";
                panel.style.paddingBottom = "10px";
            }
        });
    }

    // Hamburger menu toggle
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('nav-menu');

    hamburger.addEventListener('click', function() {
        navMenu.classList.toggle('show');
    });

    console.log("SmartLib UI Loaded");
});

function openModal() {
        document.getElementById("authModal").style.display = "flex";
    }

    // Fungsi menutup modal
    function closeModal() {
        document.getElementById("authModal").style.display = "none";
    }

    // Fungsi helper untuk pindah tab
    function switchTab(tabId) {
        // Sembunyikan semua tab
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        // Tampilkan tab yang dipilih
        document.getElementById(tabId).classList.add('active');
    }

    // Logika tombol sesuai HTML Anda
    function showEmailLogin() {
        switchTab('email-login');
    }

    function showRegister() {
        switchTab('register-tab');
    }

    function showMethodSelection() {
        switchTab('method-selection');
    }

    // Menutup modal jika user klik di area gelap (luar box)
    window.onclick = function(event) {
        var modal = document.getElementById("authModal");
        if (event.target == modal) {
            closeModal();
        }
    }
    