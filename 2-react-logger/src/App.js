import React from "react";
import { BrowserRouter as Router, Routes, Route, useLocation } from "react-router-dom";
import Container from "react-bootstrap/Container";
import Nav from "react-bootstrap/Nav";
import Navbar from "react-bootstrap/Navbar";
import LogsTable from "./components/LogsTable";
import Podium from "./components/Podium";
import Home from "./components/Home";
import Map from "./components/Map"

import { Link } from "react-router-dom";
import "./styles/pageTransition.css";
import "bootstrap/dist/css/bootstrap.min.css";
import "./App.css";
import { CSSTransition, SwitchTransition } from 'react-transition-group';

function AppWrapper() {
  return (
    <Router>
      <App />
    </Router>
  );
}

function App() {
  const location = useLocation(); 

  return (
    <div className="app">
      <Navbar expand="lg" className="bg-body-tertiary">
        <Container>
          <Navbar.Brand as={Link} to="/">Clean This Logs</Navbar.Brand>
          <Navbar.Toggle aria-controls="basic-navbar-nav" />
          <Navbar.Collapse id="basic-navbar-nav">
            <Nav className="me-auto">
              <Nav.Link as={Link} to="/dylan">
                Dylan
              </Nav.Link>
              <Nav.Link as={Link} to="/amelie">
                Amélie
              </Nav.Link>
              <Nav.Link as={Link} to="/jeremy">
                Jéméry
              </Nav.Link>
            </Nav>
          </Navbar.Collapse>
        </Container>
      </Navbar>
      <SwitchTransition mode="out-in">
        <CSSTransition
          key={location.pathname}
          classNames="slide"
          timeout={600}
        >
          <Routes location={location}> 
            <Route path="/dylan" element={<LogsTable />} />
            <Route path="/amelie" element={<Podium />} />
            <Route path="/" element={<Home />} />
            <Route path="/jeremy" element={<Map />} />
          </Routes>
        </CSSTransition>
      </SwitchTransition>
    </div>
  );
}

export default AppWrapper;
