<?php

$logfile = fopen("logfile.txt", "a") or die("Unable to open file!");
fwrite($logfile, 'Like a dragon' . "\n");
