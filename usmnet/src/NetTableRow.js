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

const netData = [{
  id: 1,
  loc: "Bevier 89",
  dev: "Mersive Solstice",
  mac: "58:FC:DB:40:FD:4E",
  ip: "192.17.117.37",
  sub: "0158-mobilestream-net",
  sw: "sw-teeshtest",
  port: "8",
  jack: "HAP2-42",
  stat: "UP"
}, {
  id: 2,
  loc: "Bevier 85",
  dev: "Mersive Solstice",
  mac: "58:FC:DB:40:FD:4E",
  ip: "192.17.117.37",
  sub: "0158-mobilestream-net",
  sw: "sw-teeshtest",
  port: "8",
  jack: "HAP2-42",
  stat: "UP"
}]

export class NetTableRow extends React.Component {
  render() {
    return ( netData.map((netCell) =>
        <TableRow key={netCell.id}>
          <TableRowColumn>{netCell.loc}</TableRowColumn>
          <TableRowColumn>{netCell.dev}</TableRowColumn>
          <TableRowColumn>{netCell.mac}</TableRowColumn>
          <TableRowColumn>{netCell.ip}</TableRowColumn>
          <TableRowColumn>{netCell.sub}</TableRowColumn>
          <TableRowColumn>{netCell.sw}</TableRowColumn>
          <TableRowColumn>{netCell.port}</TableRowColumn>
          <TableRowColumn>{netCell.jack}</TableRowColumn>
          <TableRowColumn>{netCell.stat}</TableRowColumn>
        </TableRow>
      )
    )
  }
}
