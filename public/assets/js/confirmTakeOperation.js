document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.action-takeOperation').forEach(function(link) {
        // Ajouter un écouteur d'événement au clic sur chaque lien
        link.addEventListener('click', function(event) {
            // Empêcher le comportement par défaut du lien (redirection)
            event.preventDefault();
            
            // Afficher une boîte de dialogue de confirmation
            if (confirm("Êtes-vous sûr de vouloir prendre cette opération ?")) {
                // Si l'utilisateur clique sur OK, suivre le lien
                window.location.href = link.getAttribute('href');
            } else {
                // Sinon, ne rien faire
                return false;
            }
        });
    });
});
