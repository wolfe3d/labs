<?php
$mysqlConn= mysqli_connect('localhost', 'hari', 'rani', 'wolfescience');

/* check connection */
if ($mysqlConn->connect_errno) {
   printf("Connect failed: %s\n", $mysqlConn->connect_error);
   exit();
}
?>