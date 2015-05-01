<?php $start = microtime(1); ?><?php
 zalupa 
?><?php
$finish = microtime(1);
$memory = memory_get_peak_usage();

file_put_contents('/home/roman/www/console/resources/config/../../bin/exec_mem', $memory);
file_put_contents('/home/roman/www/console/resources/config/../../bin/exec_time', $finish - $start);
?>