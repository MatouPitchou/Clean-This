
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.status').forEach(function(element) {
        let statusText = element.textContent.trim();
        let container = element.closest('.info-badge');
        let icon = container.querySelector('.status-icon');

        switch(statusText) {
            case 'Disponible':
            case 'Available':
                container.style.backgroundColor = '#87dfd6'; // Couleur de fond pour "Disponible"
                element.style.color = '#024a51'; // Couleur du texte pour "Disponible"
                icon.classList.remove('fa-user', 'fa-circle-check', 'fa-ban', 'fa-hourglass-start', 'fa-pause');
                icon.classList.add('fa-circle-check'); // Icône FontAwesome pour "Disponible"
                break;
            case 'Terminée':
            case 'Validé':
            case 'Validated':
            case 'Completed':
                container.style.backgroundColor = '#c2ffc0'; // Couleur de fond pour "Terminée" ou "Validé"
                element.style.color = '#007419'; // Couleur du texte pour "Terminée" ou "Validé"
                icon.classList.remove('fa-user', 'fa-circle-check', 'fa-ban', 'fa-hourglass-start', 'fa-pause');
                icon.classList.add('fa-check'); // Icône FontAwesome pour "Terminée" ou "Validé"
                break;
            case 'Annulée':
            case 'Canceled':
            case 'Refusé':
            case 'Refused':
                container.style.backgroundColor = '#e83a3a'; // Couleur de fond pour "Annulée" ou "Refusé"
                element.style.color = 'white'; // Couleur du texte pour "Annulée" ou "Refusé"
                icon.classList.remove('fa-user', 'fa-circle-check', 'fa-check', 'fa-hourglass-start', 'fa-pause');
                icon.classList.add('fa-ban'); // Icône FontAwesome pour "Annulée" ou "Refusé"
                break;
            case 'En cours':
            case 'In Progress':
            case 'Estimation':
                container.style.backgroundColor = '#f39c11'; // Couleur de fond pour "En cours" ou "Estimation"
                element.style.color = '#945b00'; // Couleur du texte pour "En cours" ou "Estimation"
                icon.classList.remove('fa-user', 'fa-circle-check', 'fa-check', 'fa-ban', 'fa-pause');
                icon.classList.add('fa-hourglass-start'); // Icône FontAwesome pour "En cours" ou "Estimation"
                break;
            case 'Validation':
                container.style.backgroundColor = '#ffe853'; // Couleur de fond pour "En cours" ou "Validation"
                element.style.color = '#776700'; // Couleur du texte pour "En cours" ou "Estimation"
                icon.classList.remove('fa-user', 'fa-circle-check', 'fa-check', 'fa-ban', 'fa-hourglass-start');
                icon.classList.add('fa-pause'); // Icône FontAwesome pour "En cours" ou "Estimation"
                break;
            default: // Pour null ou vide
                container.style.backgroundColor = 'transparent'; // Réinitialisation du fond
                element.style.color = ''; // Réinitialisation de la couleur du texte
                icon.classList.remove('fa-circle-check', 'fa-check', 'fa-ban', 'fa-hourglass-start', 'fa-user', 'fa-pause');
        }
    });
});



document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.employee_badge_container').forEach(function(container) {
        var employeeName = container.querySelector('.employee_name').textContent.trim();
        if (employeeName === '- badge_aattribuer_label -') {
            container.querySelector('.employee_icon').style.display = 'none';
            container.style.backgroundColor = '#99fff3';
        }
    });
});