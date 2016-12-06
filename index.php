<?php
if (!defined('MODULE_FILE')) {
	die ("You can't access this file directly...");
}

require_once("mainfile.php");
$module_name = basename(dirname(__FILE__));
get_lang($module_name);
$userpage = 1;
global $module_name,$cookie,$user,$db,$mintareset;

cookiedecode($user);
//$uname=$cookie[1];
//echo "===$umane==";

// Quota
// When you want to enforce quota for your mailbox users set this to 'YES'.
$CONF['quota'] = 'YES';
// You can either use '1024000' or '1048576'
$CONF['quota_multiplier'] = '1024000';
$CONF['encrypt'] = 'md5crypt';
$CONF['database_type']='mysql';
$CONF['smtp_server']='172.17.1.29';
$CONF['smtp_port']='25';
$CONF['welcome_text'] = <<<EOM
Hi,

Welcome to your new account.

You can change your email password using Menu Settings -> Password on the top right of this screen.


*** This is an automatically generated email, please do not reply ***
-
Admin of Student Portal
Universiti Sultan Zainal Abidin
Kampus Gong Badak,
21300 Kuala Terengganu
EOM;



$fDomain='putra.unisza.edu.my';

$MAGIC = "$1$";
$ITOA64 = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
global $CONF,$MAGIC,$ITOA64;



if ($_SERVER['REQUEST_METHOD'] == "POST"){

    $fnokp = $_POST['fnokp'];
    $fnokp = escape_string ($fnokp);
    $fUsername = $_POST['fUsername'];
    if(empty($fnokp) || empty($fUsername)){
		$error = 1;
		$error_msg = "Sila penuhi semua maklumat yang diminta!";
    }

    //$mbox = escape_string ($_POST['fUsername']) . "@$fDomain";
    //$mbox = escape_string ($_POST['fUsername']) . "@$fDomain";
    //$mbox = strtolower($_POST['fUsername']);
    $mbox = trim(strtolower($_POST['fUsername']));
    $fDomain = escape_string ($fDomain);
    $fQuota = "5";
    $fActive = "1";
    $quota = $fQuota * $CONF['quota_multiplier'];
    $fPassword = generate_password();

    if($error != 1){
    	$sql = "select a006namapel from a006 where a006nopel='$cookie[1]' and a006kstatus='PA'";
    	//echo $sql;
    	$result=$db->sql_query($sql);
    	if($db->sql_numrows($result) != 1){
        	$error = 1;
        	$error_msg = "Tiada pelajar seperti No Pelajar yang anda berikan.";
    	}else{
    	   list($namapelajar) = $db->sql_fetchrow($result);
    	}
    }

    if($error != 1){
    	$sql2 = "select a006xemail from a006x where a006xnopel='$cookie[1]'";
    	//echo $sql2;
    	$result2=$db->sql_query($sql2);
    	if($db->sql_numrows($result2) == 1){
        	$error = 1;
        	list($tmpemail)=$db->sql_fetchrow($result2);
        	$error_msg = "Anda telah mendaftar sebagai $tmpemail.";
    	}
    }
    //mysql_close($link);

    include ("db_link.php");

    if($error != 1){
    	$sql = "SELECT * FROM alias WHERE address='$mbox'";
    	$result = mysql_query($sql);
    	if(mysql_num_rows($result)==1){
        	$error=1;
        	$error_msg="$mbox telah digunakan!";

      $sql_portal="insert into a006x set a006xemail='$mbox', a006xnopel='$cookie[1]'";
      $db->sql_query($sql_portal);


    	}
    }


                    $nopel=$cookie[1];
                    $studentInfo = getstudentinfo1($nopel);
                    $nokp = strtolower($studentInfo[0]);
                    $password = pacrypt ($nokp);


    if ($error != 1){
        echo "$fUsername,$nokp,$mbox,$fPassword,$quota, $fDomain,$fActive<br>";
        //$password = pacrypt ($fPassword);
	$password = pacrypt ($nokp);
       // echo "2: $password<br>";
        $maildir = $fDomain . "/" . $mbox . "/";
       // echo "3: $maildir<br>";
        $sql_alias="INSERT INTO alias (address,goto,domain,created,modified,active) VALUES ('$mbox','$mbox','$fDomain',NOW(),NOW(),'$fActive')";
	mysql_query($sql_alias);
        $namapelajar =  addslashes($namapelajar); // bagi kes nama pelajar ada " ' "
        $sql_mailbox = "INSERT INTO mailbox (username,password,name,maildir,quota,domain,created,modified,active) VALUES ('$mbox','$password','$namapelajar','$maildir','$quota','$fDomain',NOW(),NOW(),'$fActive')";
	mysql_query($sql_mailbox);

        $fTo = $mbox;
        //$fFrom = 'taipan@sentral.udm.edu.my';
        $fFrom = 'nik@unisza.edu.my';
        $fHeaders = "To: " . $mbox . "\n";
        $fHeaders .= "From: " . $fFrom . "\n";
	    $fHeaders .= "Subject: Welcome to your new account\n\n";
	    $fHeaders .= $CONF['welcome_text'];

        //smtp_mail ($fTo, $fFrom, $fHeaders);

	$fHeadersTo = $mbox;
	$fHeadersSubject = "Welcome to your new account\n\n";
	$fHeaderBody = $CONF['welcome_text'];

        emailcreate($fHeadersTo,$fHeadersSubject,$fHeaderBody,$fFrom);

        mysql_close($link);

	    $sql_portal="insert into a006x set a006xemail='$mbox', a006xnopel='$cookie[1]', a006xtdaftar=NOW()";
	    $db->sql_query($sql_portal);

    }


    if($error == 1){
		userForm($fUsername,$fnokp,$user);
    }else{
        include("header.php");
        OpenTable();
        echo "<h1>Pendaftaran Emel</h1>";
		echo "Pendaftaran anda telah berjaya. Akaun anda sekarang telah aktif. Berikut adalah maklumat mengenai akaun anda:<br>
	<ul><li><h2>Nama Anda : $namapelajar</h2>
	<li><b><h2>Email : $mbox</h2></b>
	<li><b><h2>Katalaluan : No KP / Passport </h2></b></ul></center>
	</ul><br>

	<br>
<!---
	<center><font color=red>Pastikan anda menyalin Katalaluan anda sekarang.<br>Mesej ini hanya dipaparkan <u>sekali sahaja</u>.<br>Terima kasih</font></center> //-->
	<br>
	<br>
	- <u>Capaian emel</u> dan <u>Pertukaran Katalaluan</u> boleh dibuat di laman emel Putra@UniSZA <a href=\"http://www.putra.unisza.edu.my\">http://www.putra.unisza.edu.my</a><br><br>
	- Sebarang pertanyaan, sila berjumpa dengan kakitangan Pusat Teknologi Maklumat di bangunan Blok A, Kampus Gong Badak. <br><br>";
	   CloseTable();
        include("footer.php");
        //OpenTable();

    }
}else{
    $sql= "select count(*) from a006x where a006xnopel='$cookie[1]'";
    //echo $sql;
    $result = $db->sql_query($sql);
    list($myemail)=$db->sql_fetchrow($sql);
    if($myemail==0){
       userForm();
   }else{
        Header("location:http://www.putra.unisza.edu.my");
    }
}

function userForm($xfUsername='',$xfnokp='',$user){
    global $error_msg,$cookie,$user,$mintareset;
    cookiedecode($user);
        include("header.php");
        OpenTable();
    echo "<h1>Pendaftaran Emel</h1>";

        $sql2 = "select a006xemail from a006x where a006xnopel='$cookie[1]'";
        //echo $sql2;
        $result2=$db->sql_query($sql2);
        if($db->sql_numrows($result2) == 1){
                $error = 1;
                list($tmpemail)=$db->sql_fetchrow($result2);
                $error_msg = "<p>Anda telah mendaftar sebagai pengguna emel Putra. <br><h2> Emel : $tmpemail</h2><br><br></p><p>Sila layari web emel Putra di : <br><br><a href='http://www.putra.unisza.edu.my/' target='_blank'><img src='http://www.putra.unisza.edu.my/skins/default/images/roundcube_logo.png' alt='Putra WebM@il'></a><h2><a href='http://www.putra.unisza.edu.my/' target='_blank'>http://www.putra.unisza.edu.my/</a></h2></p>";
                echo "<b><font color=black>$error_msg</font></b><br>";

                /*yan tambah kat sini .. */
		$putra = '@putra.unisza.edu.my';
                echo "<br> <hr /> <form action='$PHP_SELF' method='post'>";
                //echo "<table><tr><td><input type=\"hidden\" name=\"mintareset\" value=\"arahan_reset_diterima\">";
                echo "<table><tr><td><input type=\"submit\" name=\"mintareset\" value=\"Reset Password Emel\">";
                echo "<tr><td><input type=\"submit\" name=\"mintabaiki\" value=\"Baiki Masalah Emel\">";
                echo "</td></tr></table></form><br>";
                //echo "<input type='submit' value='Reset Password Emel'></td></tr><br>";
                //echo "<input type='submit' value='Baiki Emel'></td></tr></table></form><br>";


		if (isset($_POST['mintareset'])) {

                    $nopel=$cookie[1];
                    $studentInfo = getstudentinfo1($nopel);
                    $nokp = strtolower($studentInfo[0]);
                    $password = pacrypt ($nokp);

                    //echo "$nopel//$studentInfo//$nokp//$password//$tmpemail";
                    include ("db_link.php");
                     $sqlreset = "UPDATE mailbox SET password = '$password' WHERE username = '$tmpemail'";
                    //mysql_query($sql_mailbox);
                    mysql_query($sqlreset);
                    mysql_close($link);
                    echo "<p><h2>Kata laluan bagi $tmpemail telah ditukar kepada No KP / Passport anda... <br></h2></p>";


		} else if (isset($_POST['mintabaiki'])) {

		$sqltt = "delete alias, mailbox from alias, mailbox where alias.address='$tmpemail' and mailbox.username='$tmpemail'";
                mysql_query($sqltt);

		$sqltt1 = "delete from a006x where a006xemail = '$tmpemail'";
		$db->sql_query($sqltt1);
                    mysql_close($link);


				Header("location:https://www.sentral.unisza.edu.my/modules.php?name=Email_Register");
		} else {
				echo '';
		}
/*

                if ($mintareset == 'arahan_reset_diterima'){
                    $nopel=$cookie[1];
                    $studentInfo = getstudentinfo1($nopel);
                    $nokp = strtolower($studentInfo[0]);
                    $password = pacrypt ($nokp);

                    //echo "$nopel//$studentInfo//$nokp//$password//$tmpemail";
                    include ("db_link.php");
                     $sqlreset = "UPDATE mailbox SET password = '$password' WHERE username = '$tmpemail'";
                    //mysql_query($sql_mailbox);
                    mysql_query($sqlreset);
                    mysql_close($link);
                    echo "<p><h2>Kata laluan bagi $tmpemail telah ditukar kepada No KP / Passport anda... <br></h2></p>";
                }
*/

    	}else{

    if($error_msg){
            echo "<b><font color=red><blink>$error_msg</blink></font></b><br>";
    }
    $mymatrik = trim($cookie[1]);
    $fUsername="$mymatrik".'@putra.unisza.edu.my';
    echo "<form action='$PHP_SELF' method='post'>";
    echo "<table><tr><td><b>No. Pelajar</b></td><td>: <input name='fnokp' type='text' value='$cookie[1]' readonly></td></tr>";
    echo "<tr><td><b>Email ID</b></td><td>: <input name='fUsername' type='text' value='$fUsername' size=30 readonly></td></tr>";
    echo "<tr><td>&nbsp;</td><td><input type='submit' value='Daftar Emel'></td></tr></table></form><br>";
    echo "No pelajar akan digunakan sebagai email anda. Tekan butang Daftar Emel untuk meneruskan proses pendaftaran emel."; }
        CloseTable();
        include("footer.php");
}


//
// escape_string
// Action: Escape a string
// Call: escape_string (string string)
//
function escape_string ($string)
{
   global $CONF;
   if (get_magic_quotes_gpc () == 0)
   {
      if ($CONF['database_type'] == "mysql")  $escaped_string = mysql_real_escape_string ($string);
      if ($CONF['database_type'] == "mysqli")  $escaped_string = mysqli_real_escape_string ($string);
      if ($CONF['database_type'] == "pgsql")  $escaped_string = pg_escape_string ($string);
   }
   else
   {
      $escaped_string = $string;
   }
   return $escaped_string;
}
//functions
function generate_password ()
{
   $password = substr (md5 (mt_rand ()), 0, 8);
   return $password;
}

function pacrypt ($pw, $salt="")
{
        global $CONF;
        $password = "";

        if ($CONF['encrypt'] == 'md5crypt')
        {
                $password = md5crypt ($pw, $salt);
        }

        if ($CONF['encrypt'] == 'system')
        {
                $password = crypt ($pw, $salt);
        }

        if ($CONF['encrypt'] == 'cleartext')
        {
                $password = $pw;
        }

        return $password;
}

function md5crypt ($pw, $salt="", $magic="")
{
        global $MAGIC;
        if ($magic == "") $magic = $MAGIC;
        if ($salt == "") $salt = create_salt();
        $slist = explode ("$", $salt);
        if ($slist[0] == "1") $salt = $slist[1];
        $salt = substr ($salt, 0, 8);
        $ctx = $pw . $magic . $salt;
        $final = postfixadminhex2bin (md5 ($pw . $salt . $pw));
        for ($i=strlen ($pw); $i>0; $i-=16) {
                if ($i > 16)
                        $ctx .= substr ($final,0,16);
                else
                        $ctx .= substr ($final,0,$i);
        }
        $i = strlen ($pw);
        while ($i > 0) {
                if ($i & 1) $ctx .= chr (0);
                else $ctx .= $pw[0];
                $i = $i >> 1;
        }
        $final = postfixadminhex2bin (md5 ($ctx));
        for ($i=0;$i<1000;$i++) {
                $ctx1 = "";
                if ($i & 1) $ctx1 .= $pw;
                else $ctx1 .= substr ($final,0,16);
                if ($i % 3) $ctx1 .= $salt;
                if ($i % 7) $ctx1 .= $pw;
                if ($i & 1) $ctx1 .= substr ($final,0,16);
                else $ctx1 .= $pw;
                $final = postfixadminhex2bin (md5 ($ctx1));
        }
        $passwd = "";
        $passwd .= to64 ( ( (ord ($final[0]) << 16) | (ord ($final[6]) << 8) | (ord ($final[12])) ), 4);
        $passwd .= to64 ( ( (ord ($final[1]) << 16) | (ord ($final[7]) << 8) | (ord ($final[13])) ), 4);
        $passwd .= to64 ( ( (ord ($final[2]) << 16) | (ord ($final[8]) << 8) | (ord ($final[14])) ), 4);
        $passwd .= to64 ( ( (ord ($final[3]) << 16) | (ord ($final[9]) << 8) | (ord ($final[15])) ), 4);
        $passwd .= to64 ( ( (ord ($final[4]) << 16) | (ord ($final[10]) << 8) | (ord ($final[5])) ), 4);
        $passwd .= to64 ( ord ($final[11]), 2);
        return "$magic$salt\$$passwd";
}

function create_salt ()
{
        srand ((double)microtime ()*1000000);
        $salt = substr (md5 (rand (0,9999999)), 0, 8);
        return $salt;
}

function postfixadminhex2bin ($str)
{
        $len = strlen ($str);
        $nstr = "";
        for ($i=0;$i<$len;$i+=2) {
                $num = sscanf (substr ($str,$i,2), "%x");
                $nstr.=chr ($num[0]);
        }
        return $nstr;
}


function to64 ($v, $n)
{
        global $ITOA64;
        $ret = "";
        while (($n - 1) >= 0) {
                $n--;
                $ret .= $ITOA64[$v & 0x3f];
                $v = $v >> 6;
        }
        return $ret;
}


//
// smtp_mail
// Action: Sends email to new account.
// Call: smtp_mail (string To, string From, string Data)
//
function smtp_mail ($to, $from, $data)
{
   global $CONF;
   $smtp_server = $CONF['smtp_server'];
   $smtp_port = $CONF['smtp_port'];
   $errno = "0";
   $errstr = "0";
   $timeout = "30";

   $fh = @fsockopen ($smtp_server, $smtp_port, $errno, $errstr, $timeout);

   if (!$fh)
   {
      return false;
   }
   else
   {
      fputs ($fh, "EHLO $smtp_server\r\n");
      $res = fgets ($fh, 256);
      fputs ($fh, "MAIL FROM:<$from>\r\n");
      $res = fgets ($fh, 256);
      fputs ($fh, "RCPT TO:<$to>\r\n");
      $res = fgets ($fh, 256);
      fputs ($fh, "DATA\r\n");
      $res = fgets ($fh, 256);
      fputs ($fh, "$data\r\n.\r\n");
      $res = fgets ($fh, 256);
      fputs ($fh, "QUIT\r\n");
      $res = fgets ($fh, 256);
      fclose ($fh);
   }
   return true;
}

?>
