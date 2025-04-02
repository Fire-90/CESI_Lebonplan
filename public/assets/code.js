document.addEventListener("DOMContentLoaded", function () {
    // Gestion du menu burger
    const burgerIcon = document.getElementById('burger-icon');
    const burgerMenu = document.querySelector('.burger .bar');

    if (burgerIcon && burgerMenu) {
        burgerIcon.addEventListener('click', function() {
            burgerMenu.classList.toggle('active');
        });

        // Fermer le menu burger lorsqu'on clique à l'extérieur
        document.addEventListener('click', function(event) {
            if (!burgerIcon.contains(event.target) && !burgerMenu.contains(event.target)) {
                burgerMenu.classList.remove('active');
            }
        });
    }

    // Gestion du menu dropdown utilisateur
    const userDropdown = document.querySelector('.user-dropdown');
    const dropdownContent = document.querySelector('.dropdown-content');

    if (userDropdown && dropdownContent) {
        function showDropdown() {
            dropdownContent.style.display = 'block';
            dropdownContent.style.opacity = '1';
        }

        function hideDropdown() {
            setTimeout(() => {
                if (!userDropdown.matches(':hover') && !dropdownContent.matches(':hover')) {
                    dropdownContent.style.opacity = '0';
                    setTimeout(() => {
                        dropdownContent.style.display = 'none';
                    }, 300);
                }
            }, 200);
        }

        userDropdown.addEventListener('mouseenter', showDropdown);
        dropdownContent.addEventListener('mouseenter', showDropdown);
        userDropdown.addEventListener('mouseleave', hideDropdown);
        dropdownContent.addEventListener('mouseleave', hideDropdown);
    }

    // Gestion du bouton retour en haut de page
    const topButton = document.getElementById("topButton");

    if (topButton) {
        window.addEventListener("scroll", function () {
            if (window.scrollY > 200) {
                topButton.style.display = "block";
            } else {
                topButton.style.display = "none";
            }
        });

        topButton.addEventListener("click", function () {
            window.scrollTo({ top: 0, behavior: "smooth" });
        });
    }

    // Gestion de la bascule entre login et signup
    const loginForm = document.querySelector(".login");
    const signupForm = document.querySelector(".signup");
    const showSignupBtn = document.querySelector("#showSignup");
    const showLoginBtn = document.querySelector("#showLogin");

    if (loginForm && signupForm && showSignupBtn && showLoginBtn) {
        function showSignup() {
            loginForm.style.opacity = "0";
            setTimeout(() => {
                loginForm.style.display = "none";
                signupForm.style.display = "block";
                setTimeout(() => {
                    signupForm.style.opacity = "1";
                }, 50);
            }, 300);
        }

        function showLogin() {
            signupForm.style.opacity = "0";
            setTimeout(() => {
                signupForm.style.display = "none";
                loginForm.style.display = "block";
                setTimeout(() => {
                    loginForm.style.opacity = "1";
                }, 50);
            }, 300);
        }

        showSignupBtn.addEventListener("click", showSignup);
        showLoginBtn.addEventListener("click", showLogin);
    }
});

// Sélectionner les éléments nécessaires
const burgerIcon = document.getElementById("burger-icon");
const sidebar = document.getElementById("sidebar");

// Fonction pour afficher la sidebar
function toggleSidebar() {
    sidebar.classList.toggle("active");
}

// Ajouter un écouteur d'événement pour ouvrir/fermer la sidebar lors du clic sur le burger
burgerIcon.addEventListener("click", toggleSidebar);

// Ajouter un écouteur d'événement pour fermer la sidebar en dehors
document.addEventListener("click", function (event) {
    if (!sidebar.contains(event.target) && !burgerIcon.contains(event.target)) {
        sidebar.classList.remove("active");
    }
});

// Sélectionner l'élément du bouton
const topButton = document.getElementById("topButton");

// Ajouter un événement pour détecter le défilement de la page
window.onscroll = function() {
    // Vérifier si l'utilisateur a fait défiler la page vers le bas
    if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
        topButton.style.display = "block";  // Afficher le bouton
    } else {
        topButton.style.display = "none";  // Cacher le bouton
    }
};

// Ajouter un événement pour faire défiler la page vers le haut lorsqu'on clique sur le bouton
topButton.addEventListener('click', function(e) {
    e.preventDefault();
    window.scrollTo({
        top: 0,
        behavior: "smooth" // Faire défiler en douceur
    });
});
