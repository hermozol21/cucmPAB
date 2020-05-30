<?php
/*Czytanie prywatnej ksiazki PAB danego uzytkownika*/
/*Read Personal Address Book of given user*/


//Usage#php blfRead.php -h 192.168.0.10 -l apilogin -p password -u username

$options = getopt("h:l:p:u:");

$host=$options["h"];
$login=$options["l"];
$password=$options["p"];
$userid=$options["u"];


$numtype = array('11'=>"Work",'1'=>"Home",'21'=>"Mobile");

$context = stream_context_create(array('ssl'=>array('allow_self_signed'=>true,'verify_peer'=> false,'verify_peer_name'=> false)));

   $client = new SoapClient("AXLAPI.wsdl",
                array('trace'=>true,
               'exceptions'=>true,
               'location'=>"https://".$host.":8443/axl",
               'login'=>$login,
               'password'=>$password,
               'stream_context'=>$context
            ));

//$queryPAB="SELECT * from personalphonebook";
//$queryPAB="SELECT * from personaladdressbook where fkenduser = (SELECT pkid from enduser where userid = '$userid')";

$queryPAB="select pab.nickname,pab.firstname,pab.lastname,ppb.tkpersonalphonenumber,ppb.phonenumber from personalphonebook as ppb left join personaladdressbook as pab on ppb.fkpersonaladdressbook = pab.pkid where pab.fkenduser = (SELECT pkid from enduser where userid = '$userid')";

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

//print_r($response);

foreach($response->return->row as $pab){
    echo($pab->nickname.",");
    echo($pab->firstname.",");
    echo($pab->lastname.",");
    echo($numtype[$pab->tkpersonalphonenumber].",");
    echo($pab->phonenumber."\n");
    }


?>