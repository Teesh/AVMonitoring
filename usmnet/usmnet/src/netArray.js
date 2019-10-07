import React from 'react';
var fs = require('fs');

export function readCSV() {
  return new Promise((resolve, reject) => {
    fs.readFile('../NetEquipment.csv','utf8',function(err,contents) {
      console.log(contents);
      return contents;
    });
  });
}
