<?php
////Input text file format: Nickname, Firstname, Lastname, Type(Work,Home,Mobile), Number, (ew.e-mail)
/*Insert Personal Address Book of given user*/


//Usage#php pabInsert.php -h 192.168.0.10 -l apilogin -p password -u apiusername -f filename.txt

$options = getopt("h:l:p:u:f:");

$host=$options["h"];
$login=$options["l"];
$password=$options["p"];
$userid=$options["u"];
$myfile=$options["f"];
$email='';


$numtype = array('Work'=>"11",'Home'=>"1",'Mobile'=>"21");
$lastnickname = "";
$context = stream_context_create(array('ssl'=>array('allow_self_signed'=>true,'verify_peer'=> false,'verify_peer_name'=> false)));

   $client = new SoapClient("AXLAPI.wsdl",
                array('trace'=>true,
               'exceptions'=>true,
               'location'=>"https://".$host.":8443/axl",
               'login'=>$login,
               'password'=>$password,
               'stream_context'=>$context
            ));
//Czytanie pliku
$handler = fopen($myfile, "r") or die("Unable to open file!");
//Glowna petla
    while(!feof($handler)) {
        $line=str_replace(array("\r", "\n"), '', fgets($handler));
        if($line){
            $lineTab=explode(",",$line);
	$nickname = $lineTab[0];
	$firstname = $lineTab[1];
	$lastname = $lineTab[2];
	$numtyp = $numtype[$lineTab[3]];
	$number = $lineTab[4];
//	if($lineTab[5]){$email = $lineTab[5];}nie dziala
	}

if ($nickname != $lastnickname){


$queryPAB = "INSERT INTO personaladdressbook (pkid,fkenduser,nickname,firstname,lastname,email) VALUES (newid(),(SELECT pkid from enduser where userid = '$userid'),'$nickname','$firstname','$lastname','$email')";

$sqlQueryPAB['sql'] = $queryPAB;

     try {
           $response = $client->executeSQLUpdate($sqlQueryPAB);
    }
    catch (SoapFault $sf) {
        echo "SoapFault: " . $sf . "\n";
    }
    catch (Exception $e) {
        echo "Exception: ". $e ."\n";
    }
$lastnickname = $nickname;
}

$queryPAB="SELECT pkid from personaladdressbook where nickname = '$nickname' and fkenduser = (SELECT pkid from enduser where userid = '$userid')";
$sqlQueryPAB['sql'] = $queryPAB;

     try {
           $response = $client->executeSQLQuery($sqlQueryPAB);
    }
    catch (SoapFault $sf) {
        echo "SoapFault: " . $sf . "\n";
    }
    catch (Exception $e) {
        echo "Exception: ". $e ."\n";
    }

$addressbookid = $response->return->row->pkid;

$queryPAB = "INSERT INTO personalphonebook (pkid,fkenduser,fkpersonaladdressbook,tkpersonalphonenumber,phonenumber,personalfastdialindex) VALUES (newid(),(SELECT pkid from enduser where userid = '$userid'),'$addressbookid',$numtyp,$number,'0')";

$sqlQueryPAB['sql'] = $queryPAB;

     try {
           $response = $client->executeSQLUpdate($sqlQueryPAB);
    }
    catch (SoapFault $sf) {
        echo "SoapFault: " . $sf . "\n";
    }
    catch (Exception $e) {
        echo "Exception: ". $e ."\n";
    }
//koniec petli while otwierania pliku
}
?>