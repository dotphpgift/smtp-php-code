<?php
require_once 'swiftmailer/lib/swift_required.php';

if($_POST) 
{

$message = unserialize($_POST["message"]);
$serveraddress = $_POST["serverAddress"];
$portnumber = $_POST["portnumber"];
$securitytype = $_POST["securitytype"];
$emailAddressSet = $_POST["emailAddressSet"];
$password = base64_decode($_POST["password"]);


	
    $transport = \Swift_SmtpTransport::newInstance($serveraddress, $portnumber);
    $transport->setUsername($emailAddressSet)->setPassword($password);
    
    if (strtolower($securitytype == 'tls') || strtolower($securitytype == 'ssl')) {
            $transport->setEncryption($securitytype);
        }

    $transport->setLocalDomain($serveraddress);
    $arrayLogger = new Swift_Plugins_Loggers_ArrayLogger();
    $logger = new Swift_Plugins_LoggerPlugin($arrayLogger);
    $transport->registerPlugin($logger);
   
   
	try {
		# Check if connection started
        if($transport->isStarted == false) {
            $transport->start();
        }

		if($transport->isStarted == true) {
            $mailer = \Swift_Mailer::newInstance($transport);

            # Sending email after checked credentials
            try {
                $success = $mailer->send($message);
                echo json_encode(array("status"=>"success"));

			} catch(Swift_TransportException $e) {
                   echo json_encode(array("status" => "failedSending", "error" => $e->getMessage(),
                       "loggerMessage" => explode(PHP_EOL, $logger->dump())));
			}
		}
	} catch (Swift_TransportException $e) {
		echo json_encode(array("status" => "failedLogin", "error" => $e->getMessage(),
            "loggerMessage" => explode(PHP_EOL, $logger->dump())));
	}
}    
?>
