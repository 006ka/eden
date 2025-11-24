// LIGHTBOX
const images = document.querySelectorAll(".galerie-grid img");
const lightbox = document.getElementById("lightbox");
const lightboxImg = document.getElementById("lightbox-img");

// ouvrir image
images.forEach(img => {
    img.addEventListener("click", () => {
        lightbox.classList.add("active");
        lightboxImg.src = img.src;
    });
});

// fermer en cliquant sur le fond
lightbox.addEventListener("click", () => {
    lightbox.classList.remove("active");
});
