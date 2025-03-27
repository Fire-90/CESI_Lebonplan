document.addEventListener("DOMContentLoaded", function () {
    const nomInput = document.querySelector("input[name='lastname']");
    const emailInput = document.querySelector("input[name='email']");
    const fileInput = document.getElementById("file-upload");
    const submitButton = document.getElementById("submit");
    const messageInput = document.querySelector("textarea[name='feedbacks']");
    const prenomInput = document.querySelector("input[name='surname']");
    const alertContainer = document.getElementById("alert-messages");
    const mailContainer = document.getElementById("mail-messages");
    const fileContainer = document.getElementById("file-messages");
    const backToTopButton = document.getElementById("backToTop");
    const resetButton = document.getElementById("reset");
    const checkboxes = document.querySelectorAll("input[type='checkbox']");
    const radioButtons = document.querySelectorAll("input[type='radio']");

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    function displayError(message) {
        alertContainer.innerHTML = `<p class='error'>${message}</p>`;
        alertContainer.style.display = "flex";
    }

    function displaySuccess() {
        alertContainer.innerHTML = "<p class='success'>Votre candidature a été envoyée avec succès !</p>";
        alertContainer.style.display = "flex";
    }

    function displayMail(message) {
        mailContainer.innerHTML = `<p class='mail-error'>${message}</p>`;
        mailContainer.style.display = "flex";
    }

    function displayFile(message) {
        fileContainer.innerHTML = `<p class='file-error'>${message}</p>`;
        fileContainer.style.display = "flex";
    }

    // Mettre le nom en majuscule après saisie
    nomInput.addEventListener("blur", function () {
        nomInput.value = nomInput.value.toUpperCase();
    });

    // Vérification du format d'email
    emailInput.addEventListener("blur", function () {
        if (!emailRegex.test(emailInput.value)) {
            displayMail("Veuillez entrer une adresse email valide.");
        } else {
            mailContainer.innerHTML = "";
            mailContainer.style.display = "none";
        }
    });

    document.getElementById('upload-btn').addEventListener('click', function() {
        document.getElementById('file-upload').click();
    });
    
    document.getElementById('file-upload').addEventListener('change', function(event) {
        const fileNameDisplay = document.getElementById('file-name-display');
        fileNameDisplay.textContent = event.target.files.length ? event.target.files[0].name : '';
    });

    fileInput.addEventListener("change", function () {
        const allowedFormats = ["pdf", "doc", "docx", "odt", "rtf", "jpg", "png"];
        const file = fileInput.files[0];
        const fileNameDisplay = document.getElementById("file-name-display");
    
        if (file) {
            const fileSizeMB = file.size / (1024 * 1024); // Convertir la taille en Mo
            const fileExtension = file.name.split(".").pop().toLowerCase(); // Extraire l'extension
    
            // Vérification du format
            if (!allowedFormats.includes(fileExtension)) {
                displayFile("Format non valide. Formats acceptés: .pdf, .doc, .docx, .odt, .rtf, .jpg, .png");
                fileInput.value = ""; // Réinitialise l'input fichier
                fileNameDisplay.innerHTML = ""; // Efface l'affichage du fichier
            } 
            // Vérification de la taille du fichier
            else if (fileSizeMB > 2) {
                displayFile("La taille du fichier dépasse 2 Mo.");
                fileInput.value = ""; // Réinitialise l'input fichier
                fileNameDisplay.innerHTML = ""; // Efface l'affichage du fichier
            } 
            // Affichage du nom du fichier si tout est correct
            else {
                fileContainer.style.display = "none";
                fileContainer.innerHTML = "";
                fileNameDisplay.innerHTML = `<p class='file-success'>${file.name}</p>`;
            }
        } else {
            fileNameDisplay.innerHTML = ""; // Efface l'affichage si aucun fichier
        }
    });
    

    submitButton.addEventListener("click", function (event) {
        let errorMessage = "";
    
        // Vérifications des champs obligatoires
        if (!nomInput.value.trim()) {
            nomInput.classList.add("error-input");
            errorMessage += "Le nom est requis. ";
        } else {
            nomInput.classList.remove("error-input");
        }
        if (!prenomInput.value.trim()) {
            prenomInput.classList.add("error-input");
            errorMessage += "Le prénom est requis. ";
        } else {
            prenomInput.classList.remove("error-input");
        }
        if (!emailRegex.test(emailInput.value)) {
            emailInput.classList.add("error-input");
            errorMessage += "L'email est requis. ";
        } else {
            emailInput.classList.remove("error-input");
        }
        if (!messageInput.value.trim()) {
            messageInput.classList.add("error-input");
            errorMessage += "Le message est requis. ";
        } else {
            messageInput.classList.remove("error-input");
        }
        if (!fileInput.value.trim()) {
            errorMessage += "Le CV est requis. ";
        }
    
        // Si des erreurs sont trouvées
        if (errorMessage) {
            displayError(errorMessage);
        } else {
            // Si tout est valide, afficher un message de succès
            displaySuccess();
    
            // Afficher toutes les informations dans la console
            console.log("Informations envoyées :");
            console.log("Nom: " + nomInput.value);
            console.log("Prénom: " + prenomInput.value);
            console.log("Email: " + emailInput.value);
    
            // Affichage des checkboxes avec leur état (true ou false)
            checkboxes.forEach(checkbox => {
                console.log(`${checkbox.name}: ${checkbox.checked}`);
            });
    
            // Affichage des radio buttons sélectionnés
            radioButtons.forEach(radio => {
                if (radio.checked) {
                    console.log(`${radio.name}: ${radio.value}`);
                }
            });
    
            // Affichage des valeurs sélectionnées dans les <select>
            const selects = document.querySelectorAll("select");
            selects.forEach(select => {
                console.log(`${select.name}: ${select.value}`);
            });

            console.log("Message: " + messageInput.value);
            console.log("CV: " + fileInput.value);
        }
    });
    

    // Réinitialisation du formulaire
    resetButton.addEventListener("click", function () {
        alertContainer.style.display = "none";
        alertContainer.innerHTML = "";
        mailContainer.style.display = "none";
        mailContainer.innerHTML = "";
        fileContainer.style.display = "none";
        fileContainer.innerHTML = "";
        nomInput.value = "";
        prenomInput.value = "";
        emailInput.value = "";
        messageInput.value = "";
        fileInput.value = "";

        nomInput.classList.remove("error-input");
        prenomInput.classList.remove("error-input");
        messageInput.classList.remove("error-input");
        emailInput.classList.remove("error-input");

        checkboxes.forEach(checkbox => checkbox.checked = false);
        radioButtons.forEach(radio => radio.checked = false);
        if (radioButtons.length > 0) radioButtons[0].checked = true;
    });

    // Gestion du bouton retour en haut de page
    window.addEventListener("scroll", function () {
        if (window.scrollY > 200) {
            backToTopButton.style.display = "block";
        } else {
            backToTopButton.style.display = "none";
        }
    });

    backToTopButton.addEventListener("click", function () {
        window.scrollTo({ top: 0, behavior: "smooth" });
    });

});

// Function to toggle the navigation menu
function toggleBurger() {
	document.getElementById('burger').classList.toggle('expanded');
  }
  
  // Close the menu when the user scrolls
  window.addEventListener('scroll', function() {
	// If the menu is expanded and the user scrolls, collapse it
	if (document.getElementById('burger').classList.contains('expanded')) {
	  document.getElementById('burger').classList.remove('expanded');
	}
  });

