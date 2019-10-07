<?php
/*
  Test file, cannot be reached by UI
*/
  echo 'Start';
  $stream = popen('/usr/bin/nmap -p 1-65535 -T5 192.17.117.37', 'r');

  while (!feof($stream)) {
      //Make sure you use semicolon at the end of command
      $buffer = fread($stream, 1024);
      echo $buffer, PHP_EOL;
  }

  pclose($stream);
?>
