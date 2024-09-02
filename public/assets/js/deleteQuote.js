$(document).ready(function(){
    // Utilisez une délégation d'événement pour traiter les formulaires de suppression dynamiques
    $(document).on("submit", "[id^='deleteForm_']", function(e){
        e.preventDefault();

        var form = $(this);
        var operationId = form.find("[id^='operation_id_']").val();
        var csrfToken = $(this).data('csrf-token');

        // Add a confirmation prompt
        var confirmation = confirm('êtes-vous sûr de vouloir annuler cette commande ?');

        if (confirmation) {
            $.ajax({
                url: '/operations/' + operationId,
                type: 'DELETE',
                dataType: 'json',
                data: {
                    operationId: operationId,
                    csrf_token_delete: csrfToken
                },
                success: function(response){		
                    console.log(response);
                    alert("La commande " + operationId + " a bien été annulée" );

                    // Masquer la ligne de l'utilisateur supprimé
                    form.closest("tr").hide();
                },
                error: function(response){
                    console.log('error');
                    alert("Erreur lors de la suppression de l'opération " + operationId);
                }
            });
        }
    });
});
