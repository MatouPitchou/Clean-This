/**
 *  @author Jérémy <jeremydecreton@live.fr>
 * 
 */

console.log('Ceci est l\'index');

$(document).on('click', '.action-assignOperationTo', function(e) {
    e.preventDefault();

    const href = $(this).attr('href');
    const urlParams = new URLSearchParams(href);
    entityId = urlParams.get('entityId');

    console.log('ID de l\'opération :', entityId);

    showAssignOperationPopup();
});

var occupiedId;
var attributeBtn;
var entityId;
var popup;

function showAssignOperationPopup() {
    popup = document.getElementById('modal-attribute');
    var xhr = new XMLHttpRequest();

    xhr.open('GET', '/eligible-user');
    xhr.responseType = 'json';

    xhr.onload = function () {
        var status = xhr.status;
        occupiedId = xhr.response.occupiedId
        var userLimit = xhr.response['limit/ongoing'];

        if (status == 200) {
            var userChoices = xhr.response.userchoices;
            console.log(xhr.response);
            var userChoicesContainer = document.getElementById('userChoicesContainer');

            
            // Construire le HTML pour les options
            var optionsHtml = '<select id="user-select" name="user" class="select">';
            for (var name in userChoices) 
            {
                var userId = userChoices[name];
                var userLimitInfo = userLimit[userId];
                optionsHtml += '<option value="' + userChoices[name] + '">' + name + ' (' + userLimitInfo[1] + ' / ' + userLimitInfo[0] + ')</option>';
            }
            optionsHtml += '</select>';
            
            // Mettre à jour le contenu de l'élément avec les options
            userChoicesContainer.innerHTML = optionsHtml;
            console.log(occupiedId.length);
            attributeBtn = document.getElementById('attriBtn');
            attributeBtn.addEventListener('click', function (e)
            {
                
                e.preventDefault()
                const select = document.getElementById("user-select");
                const selectedId = select.value;
                if (isOccupied(selectedId)) {
                    console.log('Employé occupé');
                    askConfirmation(selectedId);
                }
                else
                {
                    callPost(selectedId);
                }
            });

        } else {
            console.error('Erreur lors de la récupération des données:', xhr.response);
        }
    };

    xhr.send();

    popup.style.display = "block";
}

function isOccupied(id) { 

    for (var i = 0; i < occupiedId.length; i++)
    {   

        if (id == occupiedId[i])
        {
            console.log("Oui")
            return true;
            
        }    
   }
   return false;

}

function askConfirmation(selectedId) {
    if(confirm("l'employé est déjà occupé. Voulez-vous continuer ? "))
    {
        callPost(selectedId);
        console.log("Continuer l'opération...");
    }
    else
    {
        console.log("Opération annulée.");
        popup.style.display = "none";
    }
}

function callPost(selectedId) 
{
    console.log(entityId)
    var data = {
        entityId: entityId, 
        selectedId: selectedId
    };
   $.ajax({
    type:'POST',
    url:"/assignto",
    data: data,
    success :  function(response)
    {
        if (response.success) 
        {
            alert(response.message);
            popup.style.display = "none";
            hideassignedOp(entityId);

            
        }
        else
        {
            alert(response.message);
            popup.style.display = "none";
        }
        console.log(response);
    },
    error: function(error)
    {
        console.error(error);
    }
   })
}

function hideassignedOp(entityId) {
    var assignedOp = document.querySelector('tr[data-id="' + entityId + '"]');
    if (assignedOp)
    {
        assignedOp.style.display ="none";
    }
}

if (window.location.search.includes('crudAction=edit')) {

    console.log('Ceci est la page d\'édition du CRUD Operation');

    document.addEventListener('DOMContentLoaded', function() {
        const customServiceField = document.getElementById('Operations_services');
        const priceField = document.getElementById('Operations_price');

        // Fonction pour afficher ou masquer le champ prix en fonction de la sélection du type de service
        function togglePriceField() {
            console.log("Fonction Appelée")
            var labelElement = document.querySelector('label[for="Operations_price"]');
            if (customServiceField.value === '4') { // Si le type est custom
                priceField.closest('.form-group').style.display = 'block'; 
                priceField.required = true; 
                labelElement.classList.add('required');
            } else {
                priceField.closest('.form-group').style.display = 'none';
                priceField.required = false; 
                labelElement.classList.remove('required');
            }
        }

        // Ajouter un gestionnaire d'événements pour détecter les changements dans le champ de sélection du type de service
        if (customServiceField) {
            customServiceField.addEventListener('change', function() {
                togglePriceField(); // Appel de la fonction pour afficher ou masquer le champ prix
            });

            // Appel initial de la fonction pour afficher ou masquer le champ prix au chargement de la page
            togglePriceField();
        }
    });
}
