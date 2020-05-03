<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Untitled Document</title>
</head>

<body>
<?php
 
error_reporting(0); //prevent error when server can not be reached
 
$host = $_GET['ip']; //server.php?ip=IP-OR-HOST
if ($host=="") {
$host = $_SERVER['REMOTE_ADDR'];
}
$port = 2559;
 
if (substr_count($host , ".") != 4) { //If not an IP, resolve host
$host = gethostbyname($host);
}
 
//connect to server
 
echo "checking $host on port $port...<br>";
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
$timeout = array(sec=>3,usec=>0);
socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, $timeout);
socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, $timeout);
 
$connected = socket_connect($socket, $host, $port);
 
if (!$connected) { //If not connected: die
die("Server offline");
}
 
if ($connected) {
    $ping_start = microtime(true);
    socket_send($socket, "\xFE", 1, 0);
    $data = "";
    $result = socket_recv($socket, $data, 150, 0);$ping_end = microtime(true);
    socket_close($socket);
 
    if ($result != false && substr($data, 0, 1) == "\xFF") { //get values
        $info = explode("\xA7", mb_convert_encoding(substr($data,1), "iso-8859-1", "utf-16be"));
        $serverName = substr($info[0], 1);
        $playersOnline = $info[1];
        $playersMax = $info[2];
        $ping = round(($ping_end - $ping_start) * 1000);
//echo values
        echo  "Server: $serverName<br/>
                Address: $host<br/>
                Port: $port<br/>
                Players Online: $playersOnline/$playersMax <br/>
                Ping: $ping ms<br/>";
    } else {
        echo "Failed to receive data";
    }
} else {
    echo "Failed to connect";
}
?>
</body>
</html>