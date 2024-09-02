var map = L.map('map').setView([50.69093322753906,3.1560921669006348], 13);

L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

L.marker([50.69093322753906, 3.1560921669006348]).addTo(map)
    .bindPopup('Clean This<br> 20 rue du Luxembourg <br> Roubaix')
    .openPopup();