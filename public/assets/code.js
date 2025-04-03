/**
 * Gestionnaire principal - s'exécute quand le DOM est chargé
 */
document.addEventListener("DOMContentLoaded", function () {
    // Initialisation de toutes les fonctionnalités
    initBurgerMenu();
    initUserDropdown();
    initBackToTop();
    initCookieBanner();
    initFormToggles();
    initFileUpload();
    initFormReset();
});

/**
 * Initialise le menu burger pour mobile
 */
function initBurgerMenu() {
    const burgerIcon = document.getElementById("burger-icon");
    const sidebar = document.getElementById("sidebar");

    if (!burgerIcon || !sidebar) return;

    // Toggle sidebar
    burgerIcon.addEventListener("click", function(e) {
        e.stopPropagation();
        sidebar.classList.toggle("active");
    });

    // Fermer quand on clique ailleurs
    document.addEventListener("click", function() {
        sidebar.classList.remove("active");
    });
}

/**
 * Initialise le dropdown du profil utilisateur
 */
function initUserDropdown() {
    const userDropdown = document.querySelector(".user-dropdown");
    const dropdownContent = document.querySelector(".dropdown-content");

    if (!userDropdown || !dropdownContent) return;

    userDropdown.addEventListener("click", function(e) {
        e.stopPropagation();
        dropdownContent.style.display = 
            dropdownContent.style.display === "block" ? "none" : "block";
    });

    document.addEventListener("click", function() {
        dropdownContent.style.display = "none";
    });
}

/**
 * Initialise le bouton "Retour en haut"
 */
function initBackToTop() {
    const topButton = document.getElementById("topButton");
    if (!topButton) return;

    window.addEventListener("scroll", function() {
        topButton.style.display = 
            (window.scrollY > 200) ? "block" : "none";
    });

    topButton.addEventListener("click", function(e) {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: "smooth" });
    });
}

/**
 * Initialise le bandeau de cookies
 */
function initCookieBanner() {
    const cookieBanner = document.getElementById("cookieBanner");
    if (!cookieBanner) return;

    const acceptBtn = document.getElementById("acceptCookies");
    const rejectBtn = document.getElementById("rejectCookies");

    if (acceptBtn) {
        acceptBtn.addEventListener("click", function() {
            setCookie("cookies_accepted", "true", 365);
            cookieBanner.style.display = "none";
        });
    }

    if (rejectBtn) {
        rejectBtn.addEventListener("click", function() {
            setCookie("cookies_accepted", "false", 365);
            cookieBanner.style.display = "none";
        });
    }
}

/**
 * Initialise la bascule entre login/signup
 */
function initFormToggles() {
    const loginForm = document.querySelector(".login");
    const signupForm = document.querySelector(".signup");
    const showSignupBtn = document.querySelector("#showSignup");
    const showLoginBtn = document.querySelector("#showLogin");

    if (!loginForm || !signupForm || !showSignupBtn || !showLoginBtn) return;

    showSignupBtn.addEventListener("click", function() {
        toggleForms(loginForm, signupForm);
    });

    showLoginBtn.addEventListener("click", function() {
        toggleForms(signupForm, loginForm);
    });
}

/**
 * Initialise l'affichage du nom de fichier uploadé
 */
function initFileUpload() {
    const fileUpload = document.getElementById("file-upload");
    const fileNameDisplay = document.getElementById("file-name-display");

    if (!fileUpload || !fileNameDisplay) return;

    fileUpload.addEventListener("change", function() {
        if (this.files.length > 0) {
            fileNameDisplay.textContent = this.files[0].name;
            fileNameDisplay.classList.add("has-file");
        } else {
            fileNameDisplay.textContent = "Aucun fichier sélectionné";
            fileNameDisplay.classList.remove("has-file");
        }
    });
}

/**
 * Initialise la réinitialisation du formulaire
 */
function initFormReset() {
    const resetButton = document.getElementById("reset-button");
    if (!resetButton) return;

    resetButton.addEventListener("click", function() {
        const form = this.closest("form");
        if (form) form.reset();
        
        const fileNameDisplay = document.getElementById("file-name-display");
        if (fileNameDisplay) {
            fileNameDisplay.textContent = "Aucun fichier sélectionné";
            fileNameDisplay.classList.remove("has-file");
        }
    });
}

/**
 * Helper pour basculer entre deux formulaires avec animation
 */
function toggleForms(hideForm, showForm) {
    hideForm.style.opacity = "0";
    setTimeout(() => {
        hideForm.style.display = "none";
        showForm.style.display = "block";
        setTimeout(() => {
            showForm.style.opacity = "1";
        }, 50);
    }, 300);
}

/**
 * Helper pour définir un cookie
 */
function setCookie(name, value, days) {
    const date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    const expires = "expires=" + date.toUTCString();
    document.cookie = name + "=" + value + ";" + expires + ";path=/";
}