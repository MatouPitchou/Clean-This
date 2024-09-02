import React, { useEffect, useState } from 'react';
import "../styles/LogsTable.css";
import { format } from 'date-fns';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faSignInAlt, faSignOutAlt, faUserPlus } from '@fortawesome/free-solid-svg-icons';
import { formatDuration } from '../utils/formatDuration';

function getIconByLoggerName(loggerName) {
  switch (loggerName) {
    case 'cnxApp':
      return <td className="sign-in-icon"><FontAwesomeIcon icon={faSignInAlt} /></td>;
    case 'uncxApp':
      return <td className="sign-out-icon"><FontAwesomeIcon icon={faSignOutAlt} /></td>;
    case 'rgstApp':
      return <td className="register-icon"><FontAwesomeIcon icon={faUserPlus} /></td>;
    default:
      return <td className="null-icon"></td>;
  }
}

function LogsTable() {
  const [logs, setLogs] = useState([]);

  useEffect(() => {
    const fetchLogs = async () => {
      const response = await fetch('http://localhost:3000/api/logs');
      const data = await response.json();
      // Filtre les logs pour ne garder que ceux avec un loggerName spécifique
      const filteredData = data.filter(log =>
        log.loggerName === 'cnxApp' ||
        log.loggerName === 'uncxApp' ||
        log.loggerName === 'rgstApp'
      );
      // Trie les données filtrées
      const sortedData = filteredData.sort((a, b) => new Date(b.EventTime) - new Date(a.EventTime));
      setLogs(sortedData);
    };

    fetchLogs();
    const intervalId = setInterval(fetchLogs, 5000);

    return () => clearInterval(intervalId);
  }, []);

  return (
    <div className="pageWrapper">
      <h2>Authentification Logs</h2>
      <table className="log-table">
        <thead>
        </thead>
        <tbody className="table-body">
          {logs.map(log => (
            <tr key={log._id} className="table-row">
            {getIconByLoggerName(log.loggerName)}
            <td>
              <div className='date'>{format(new Date(log.EventTime), 'PPP')}</div>
              <div className='hour'>{format(new Date(log.EventTime), 'p')}</div>
            </td>
              <td>{log.email}</td>
              <td className="log-message">{log.Message}</td>
              <td className='duration'>
                <div className='duration-libele'>{log.loggerName === 'uncxApp' ? 'Stayed on for:' : ''}</div>
                <div className='duration-time'>{log.loggerName === 'uncxApp' ? `${formatDuration(log.Data?.sessionDuration) || 'N/A'}` : ''}</div>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

export default LogsTable;
