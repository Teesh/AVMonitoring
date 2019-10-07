import React, { Component } from 'react';
import './App.css';
import { NetTable } from './NetTable';
import { USMMenu } from './USMMenu';

class App extends Component {
  render() {
    return (
      <div className="App">
        <USMMenu />
        <NetTable />
      </div>
    );
  }
}

export default App;
