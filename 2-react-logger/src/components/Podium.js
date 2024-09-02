import React, { useEffect, useState } from "react";
import EmployesTable from "./EmployesTable";
import trophy from "../assets/trophy.png";
import "../styles/Podium.css";
import RolesFilter from "./RolesFilter"; 

function Podium() {
  const [topEmployees, setTopEmployees] = useState([]);
  const [activeRole, setActiveRole] = useState('');


  useEffect(() => {
    const fetchLogs = async () => {
      const response = await fetch("http://localhost:3000/api/logs");
      const data = await response.json();

      // Filtrer les logs pour ne garder que ceux avec un loggerName spécifique
      const filteredLogs = data.filter(
        (log) => log.loggerName === "finishedOP"
      );

      // Compter le nombre d'occurrences de chaque employé (userName)
      const userCounts = filteredLogs.reduce((counts, log) => {
        // Vérifier si log.data est défini et si log.data.userName est défini
        if (log.data && log.data.userName) {
          const userName = log.data.userName;
          counts[userName] = (counts[userName] || 0) + 1;
        }
        return counts;
      }, {});

      // Trier les employés par nombre d'occurrences
      const topEmployees = Object.keys(userCounts).sort(
        (a, b) => userCounts[b] - userCounts[a]
      );

      // Récupérer les rôles des employés
      const roles = {};
      filteredLogs.forEach((log) => {
        const userName = log.data.userName;
        if (!roles[userName]) {
          roles[userName] = log.data.userRole; // Supposons que le rôle soit stocké dans log.data.userRole
        }
      });

      // Créer un tableau d'objets avec le nom de l'employé et le nombre d'opérations
      const topEmployeesWithOperations = topEmployees.map((employee) => ({
        name: employee,
        operations: userCounts[employee],
        role: roles[employee][0] || "Unknown",
      }));
      console.log(topEmployeesWithOperations);

      // Définir les données mises à jour dans l'état
      setTopEmployees(topEmployeesWithOperations);
    };
    fetchLogs();
  }, []);

   // Fonction de gestion des événements pour mettre à jour le rôle actif
   const handleRoleChange = (selectedRole) => {
    setActiveRole(selectedRole);
  };

  // Filtrer les employés en fonction du rôle sélectionné
  const filteredEmployees = activeRole
    ? topEmployees.filter((employee) => employee.role === activeRole)
    : topEmployees;

  return (
    <div className="pageWrapper">
      <h2>Best Employees</h2>
      <h3>Employees who have completed the most operations</h3>
      <RolesFilter
        setActiveRole={handleRoleChange}
        roles={["ROLE_ADMIN", "ROLE_SENIOR", "ROLE_APPRENTI"]}
        activeRole={activeRole}
        handleReset={() => setActiveRole('')}
      />
      <div className="podium-container">
        <div className="podium">
          <div className="podium__front podium__left">
            <div className="best-employees">{filteredEmployees[1]?.name || "?"}</div>
            <div className="podium__image">
              <img src={trophy} alt="" />
            </div>
          </div>
          <div className="podium__front podium__center">
            <div className="best-employees">{filteredEmployees[0]?.name || "?"}</div>
            <div className="podium__image">
              <img src={trophy} alt="" />
            </div>
          </div>
          <div className="podium__front podium__right">
            <div className="best-employees">{filteredEmployees[2]?.name || "?"}</div>
            <div className="podium__image">
              <img src={trophy} alt="" />
            </div>
          </div>
        </div>
      </div>
      <div className="employeesTable">
      <EmployesTable topEmployees={filteredEmployees} />
      </div>
    </div>
  );
}



export default Podium;
