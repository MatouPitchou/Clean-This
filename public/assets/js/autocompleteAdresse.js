document.addEventListener('DOMContentLoaded', function() {
    var streetInput = document.getElementById('Users_street');
    var zipcodeInput = document.getElementById('Users_zipcode');
    var cityInput = document.getElementById('Users_city');
    var passwordInput = document.getElementById('User_password');

    var suggestionsList = document.createElement('ul');
    suggestionsList.style.listStyle = 'none';
    suggestionsList.style.marginTop = '5px';
    suggestionsList.style.padding = '0';
    suggestionsList.style.position = 'absolute';
    suggestionsList.style.backgroundColor = '#fff';
    suggestionsList.style.width = streetInput.offsetWidth + 'px';
    streetInput.parentNode.appendChild(suggestionsList);

    function fillAddressFields(address) {
        zipcodeInput.value = address.postcode;
        cityInput.value = address.city;
    }

    function clearSuggestions() {
        suggestionsList.innerHTML = '';
    }

    function addSuggestion(address) {
        var li = document.createElement('li');
        li.textContent = address.properties.label;
        li.style.padding = '5px';
        li.style.cursor = 'pointer';
        li.onmouseover = function() {
            li.style.backgroundColor = '#eee';
        };
        li.onmouseout = function() {
            li.style.backgroundColor = '#fff';
        };
        li.onclick = function() {
            streetInput.value = address.properties.name;
            fillAddressFields(address.properties);
            clearSuggestions();
        };
        suggestionsList.appendChild(li);
    }

    streetInput.addEventListener('input', function() {
        var query = streetInput.value;

        if (query.length < 3) {
            clearSuggestions();
            return;
        }

        fetch(`https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(query)}&limit=5`)
            .then(response => response.json())
            .then(data => {
                clearSuggestions();
                data.features.forEach(addSuggestion);
            }).catch(error => {
                console.error('Error fetching address data:', error);
            });
    });

    document.addEventListener('click', function(event) {
        if (event.target !== streetInput) {
            clearSuggestions();
        }
    });
});
