document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('form');
    var messageWrapper = document.querySelector('.flashWrapper');
    var checkboxDelivery = document.getElementById("checkboxDelivery");
    var deliveryAddressFields = document.getElementById("delivery-address-fields");
  
    checkboxDelivery.addEventListener("change", function () {
      deliveryAddressFields.style.display = checkboxDelivery.checked ? "block" : "none";
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault(); 

        var formData = new FormData(form); 
        fetch('/operations/new', {
            method: 'POST',
            body: formData,
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Réponse réseau non ok');
            }
            return response.json();
        })
        .then(data => {
            var flashDiv = document.createElement('div'); // Créez un élément div pour afficher le message flash
            flashDiv.classList.add('flash'); // Ajoutez la classe 'flash' à l'élément div

            if (data.status === 'success') {
                flashDiv.classList.add('flash-success-order'); // Ajoutez la classe 'flash-success-order' pour les messages de succès
            } else {
                flashDiv.classList.add('flash-error-order'); // Ajoutez la classe 'flash-error-order' pour les messages d'erreur
            }

            flashDiv.textContent = data.message; // Ajoutez le texte du message flash

            // Ajoutez l'élément div au wrapper des messages
            messageWrapper.appendChild(flashDiv);

            // Effacez le formulaire après soumission réussie
            if (data.status === 'success') {
                form.reset();
                deliveryAddressFields.style.display = "none";
            }

            // Ajouter la classe fade-out après 3 secondes
            setTimeout(function() {
                flashDiv.classList.add('fade-out');
            }, 3000);

            // Supprimer l'élément div après l'animation de fade-out
            setTimeout(function() {
                flashDiv.remove();
            }, 3800);
        })
        .catch(error => {
            console.error('Erreur lors de la requête AJAX:', error.message);
            // Créez un élément div pour afficher l'erreur
            var flashDiv = document.createElement('div');
            flashDiv.classList.add('flash', 'flash-error-order');
            flashDiv.textContent = 'Erreur lors de la requête AJAX: ' + error.message;
            messageWrapper.appendChild(flashDiv);

            // Ajouter la classe fade-out après 3 secondes
            setTimeout(function() {
                flashDiv.classList.add('fade-out');
            }, 2000);

            // Supprimer l'élément div après l'animation de fade-out
            setTimeout(function() {
                flashDiv.remove();
            }, 3800);
        });
    });
});