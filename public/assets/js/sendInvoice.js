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
 /*    // Désactiver le champ de statut si le statut est "Terminée" dès le chargement de la page
    if (statusField.value === "Terminée") {
      statusField.style.display = 'none';
    } */

    // Gestionnaire d'événements pour détecter les changements de statut
    statusField.addEventListener("change", function () {
      // Récupère la valeur sélectionnée à l'intérieur du champ
      const selectedStatus = statusField.value;
      var operationId = document.getElementById("Operations_id");
      var operationIdValue = operationId.value;
      console.log(operationIdValue);

      if (selectedStatus === "Terminée") {
        // Gestion de l'événement submit du formulaire : appel au contrôleur qui génère et envoie la facture
        form.addEventListener("submit", function (event) {
          // event.preventDefault();
          if (
            window.confirm(
              "En enregistrant l'opération comme Terminée, une facture sera générée automatiquement et envoyée au client."
            )
          ) {
            fetch("/invoice/new", {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
              },
              body: JSON.stringify({ operationId: operationIdValue }),
            })
              .then((response) => response.json())
              .then((data) => {
                console.log(data.message);
                // Envoi du formulaire une fois la confirmation reçue
                // form.submit();
              })
              .catch((error) => {
                console.error("Error:", error);
              });
          }
        });
      }
    });
  });
}
