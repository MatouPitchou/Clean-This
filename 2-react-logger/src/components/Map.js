import React, { useEffect, useState, useRef } from 'react';
import { MapContainer, TileLayer, Marker, Popup } from 'react-leaflet';
import './map.css';

function MyMap({ operations, mapRef }) {
  return (
    <MapContainer center={[50.6293, 2.93]} zoom={10} id='map' ref={mapRef}>
      <TileLayer
        attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" 
      />
      {Object.keys(operations).map(address => (
        <Marker key={address} position={[operations[address].lat, operations[address].long]}>
          <Popup>
            Adresse : {address}<br/>
            Description : {operations[address].description ? operations[address].description : 'Aucune description disponible'}
          </Popup>
        </Marker>
      ))}
    </MapContainer>
  );
}

function Sidebar({ operations, handleClick }) {
  return (
    <div className="sidebar">
      <h2>Liste des opérations</h2>
      <ul>
        {Object.keys(operations).map(address => (
          <li key={address} onClick={() => handleClick(address)}>
            {address}
          </li>
        ))}
      </ul>
    </div>
  );
}

function OpFinder() {
  const [operations, setOperations] = useState([]);
  const mapRef = useRef(null); // Initialise la référence mapRef à null

  useEffect(() => {
    const fetchLogs = async () => {
      const response = await fetch('http://localhost:3000/api/logs');
      const data = await response.json();
      //Trie les logs
      const filteredData = data.filter(log =>
        log.loggerName === 'crtOp' ||
        log.loggerName === 'finishedOp'
      );

      const objectArray = {};
      const promises = [];

      for (let index = 0; index < filteredData.length; index++) {
        let address = filteredData[index].OpAdress;

        const promise = fetch('https://nominatim.openstreetmap.org/search?q='+ address +'&format=json')
          .then(response => response.json())
          .then(data => {
            if (data.length > 0) {
              const latitude = data[0].lat;
              const longitude = data[0].lon;
              var desc = filteredData[index].OpDesc;
              objectArray[address] = { description: desc, lat: latitude, long: longitude}
            } else {
              console.log("Adresse non trouvée");
            }
          })
          .catch(error => {
            console.error("Erreur lors de la requête :", error);
          });
        promises.push(promise);
      }
      Promise.all(promises)
        .then(() => {
          console.log("Toutes les adresses ont été géocodées :", objectArray);
          setOperations(objectArray)
        });
    };
     
    fetchLogs();
    const intervalId = setInterval(fetchLogs, 5000);

    return () => clearInterval(intervalId);
  }, []);

  const handleMarkerClick = (address) => {
    if (mapRef.current) {
      mapRef.current.setView([operations[address].lat, operations[address].long], mapRef.current.getZoom(), {
        animate: true,
      });
      console.log("Marqueur Cliqué:", address);
    }
  };

  return (
    <>
    <div className="sidebar">
      <Sidebar operations={operations} handleClick={handleMarkerClick} />
    </div>
    <div className="container">
      <MyMap operations={operations} mapRef={mapRef} />
    </div></>
  );
}

export default OpFinder;
