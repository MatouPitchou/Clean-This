/**
 *  @author Amélie
 *
 */

if (window.location.search.includes("crudAction=index")) {
  console.log("Ceci est l'index");
}

if (window.location.search.includes("crudAction=edit")) {
  console.log("Ceci est la page d'édition du CRUD Operation");

  document.addEventListener("DOMContentLoaded", function () {
    // formulaire
    const form = document.getElementById("edit-Operations-form");
    // champ du statut
    const statusField = document.getElementById("Operations_status");

    // Gestionnaire d'événements pour détecter les changements de statut
    statusField.addEventListener("change", function () {
      // Récupère la valeur sélectionnée à l'intérieur du champ
      const selectedStatus = statusField.value;

      if (selectedStatus === "Annulée") {
        // Gestion de l'événement submit du formulaire : appel au contrôleur qui génère et envoie la facture
        form.addEventListener("submit", function (event) {
            window.confirm("Êtes-vous sûr de vouloir annuler cette opération ?")
        });
      }
    });
  });
}
