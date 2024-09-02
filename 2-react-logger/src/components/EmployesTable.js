import React from 'react';
import Table from 'react-bootstrap/Table';
import { formatRole } from '../utils/formatRole'; 


function EmployesTable({ topEmployees }) {
  return (
    <Table striped bordered hover variant="dark">
      <thead>
        <tr>
          <th>#</th>
          <th>Employee Name</th>
          <th>Number of Operations</th>
          <th>Rôle</th>
        </tr>
      </thead>
      <tbody>
      {topEmployees.map((employee, index) => (
          <tr key={index}>
            <td>{index + 1}</td>
            <td>{employee.name}</td>
            <td>{employee.operations}</td>
            <td>{formatRole(employee.role)}</td>
          </tr>
        ))}
      </tbody>
    </Table>
  );
}


export default EmployesTable;
