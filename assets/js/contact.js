document.getElementById("contactForm").addEventListener("submit", function(e) {
    e.preventDefault();

    let nom = document.getElementById("nom").value;
    let email = document.getElementById("email").value;
    let tel = document.getElementById("telephone").value;
    let message = document.getElementById("message").value;

    if (nom === "" || email === "" || tel === "" || message === "") {
        alert("Veuillez remplir tous les champs obligatoires.");
        return;
    }

    // Afficher message de confirmation
    let msg = document.getElementById("messageSuccess");
    msg.textContent = "Votre message a été envoyé avec succès !";
    msg.style.display = "block";

    // Réinitialiser le formulaire
    document.getElementById("contactForm").reset();
});
