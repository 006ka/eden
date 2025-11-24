document.getElementById("inscriptionForm").addEventListener("submit", function(e) {
    e.preventDefault();

    let nom = document.getElementById("nom").value;
    let email = document.getElementById("email").value;
    let tel = document.getElementById("telephone").value;

    // Validation simple
    if (nom === "" || email === "" || tel === "") {
        alert("Veuillez remplir tous les champs obligatoires.");
        return;
    }

    // Afficher message de réussite
    let msg = document.getElementById("message");
    msg.textContent = "Votre inscription a été enregistrée avec succès !";
    msg.style.display = "block";

    // Réinitialiser le formulaire
    document.getElementById("inscriptionForm").reset();
});
