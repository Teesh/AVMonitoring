import React from 'react';
import AppBar from 'material-ui/AppBar';
import MuiThemeProvider from 'material-ui/styles/MuiThemeProvider'

export const USMMenu = () => (
<MuiThemeProvider>
  <AppBar
    title="USMnet"
    iconClassNameRight="muidocs-icon-navigation-expand-more"
  />
</MuiThemeProvider>
);
