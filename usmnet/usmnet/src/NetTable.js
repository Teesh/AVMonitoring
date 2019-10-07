import React from 'react';
import MuiThemeProvider from 'material-ui/styles/MuiThemeProvider'
import {
  Table,
  TableBody,
  TableHeader,
  TableHeaderColumn,
  TableRow,
  TableRowColumn,
} from 'material-ui/Table';
import data from './NetEquipment.json'
console.log(data);

export class NetTable extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      error:null,
      isLoaded: false,
      items: []
    };
  }

  componentDidMount() {
      fetch
  }

  render() {
    const rows = data.map((netCell) =>
      <TableRow key={netCell.id}>
        <TableRowColumn>{netCell.loc}</TableRowColumn>
        <TableRowColumn>{netCell.dev}</TableRowColumn>
        <TableRowColumn>{netCell.mac}</TableRowColumn>
        <TableRowColumn></TableRowColumn>
        <TableRowColumn></TableRowColumn>
        <TableRowColumn></TableRowColumn>
        <TableRowColumn></TableRowColumn>
        <TableRowColumn></TableRowColumn>
        <TableRowColumn></TableRowColumn>
      </TableRow>
    )
    return (
      <MuiThemeProvider>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHeaderColumn>Location</TableHeaderColumn>
              <TableHeaderColumn>Device</TableHeaderColumn>
              <TableHeaderColumn>MAC Address</TableHeaderColumn>
              <TableHeaderColumn>IP Address</TableHeaderColumn>
              <TableHeaderColumn>Subnet</TableHeaderColumn>
              <TableHeaderColumn>Switch</TableHeaderColumn>
              <TableHeaderColumn>Port</TableHeaderColumn>
              <TableHeaderColumn>Jack</TableHeaderColumn>
              <TableHeaderColumn>Status</TableHeaderColumn>
            </TableRow>
          </TableHeader>
          <TableBody>
          {rows}
          </TableBody>
        </Table>
      </MuiThemeProvider>
    )
  }
};
