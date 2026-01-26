// Handle contact form submission in modal
document.addEventListener("DOMContentLoaded", function() {
    // Handle main contact form in modal
    const handleFormSubmit = function(e) {
        const form = e.target;
        const isContactForm = form.querySelector('input[type="hidden"]') === null || 
                             form.querySelector('input[type="hidden"][name="feedback_form"]') === undefined;
        
        e.preventDefault();
        
        const formData = new FormData(form);
        
        fetch("", {
            method: "POST",
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            // Reload page to show success/error message
            location.reload();
        })
        .catch(error => {
            console.error("Erreur:", error);
            alert("⚠️ Une erreur est survenue lors de l'envoi du message de contact");
        });
    };
    
    // Wait a bit for modals to be rendered, then attach handlers
    setTimeout(() => {
        const contactForm = document.querySelector('#contactModal form');
        const feedbackForm = document.querySelector('#feedbackModal form');
        
        if (contactForm) {
            contactForm.addEventListener("submit", handleFormSubmit);
        }
        if (feedbackForm) {
            feedbackForm.addEventListener("submit", handleFormSubmit);
        }
    }, 100);
});
