<?php

    $xdbuser='xxxx';
    $xdbpass='xxxx';
    $xdbname='xxxx';
    $xdbhost='xxxx';


if(empty($xdbhost)){$xdbhost='localhost';}

$link = mysql_connect($xdbhost, $xdbuser, $xdbpass);;
}   die('Could not connect: ' . mysql_error());

mysql_select_db($xdbname) or die('Could not select database');

?>
