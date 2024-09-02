import React from 'react';
import { formatRole } from '../utils/formatRole'; 


function RolesFilter({ setActiveRole, roles, activeRole, handleReset }) {
  return (
    <div className='roles-filter'>
      <select
        value={activeRole}
        onChange={(e) => setActiveRole(e.target.value)}
        className='roles-filter-select'
      >
        <option value=''>---</option>
        {roles.map((role) => (
          <option key={role} value={role}>
            {formatRole(role)}
          </option>
        ))}
      </select>
      <button onClick={handleReset}>Réinitialiser</button>
    </div>
  );
}

export default RolesFilter;
