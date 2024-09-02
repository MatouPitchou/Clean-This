document.addEventListener('DOMContentLoaded', function() {
    // Sélectionnez tous les éléments avec la classe message-flash
    var flashMessages = document.querySelectorAll('.flash');
    var alertMessages = document.querySelectorAll('.alert');

    
    flashMessages.forEach(function(flashMessage) {
        setTimeout(function() {
            flashMessage.classList.add('fade-out');
        }, 2000);
    });

    alertMessages.forEach(function(alertMessage) {
        setTimeout(function() {
            alertMessage.classList.add('fade-out');
        }, 2000);
    });
});