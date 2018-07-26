<?php
require_once 'swiftmailer/lib/swift_required.php';
if($_POST)
{
	$serveraddress = $_POST["serverAddress"];
	$portnumber = $_POST["portnumber"];
	$securitytype = $_POST["securitytype"];
	$emailAddressSet = $_POST["emailAddressSet"];
	$password = base64_decode($_POST["password"]);
	$subject = $_POST["subject"];
	$bodyContent = $_POST["bodyContent"];
	$emailAddress = $_POST["emailAddress"];
	$clientName = $_POST["clientName"];    
	
	
	$transport = \Swift_SmtpTransport::newInstance($serveraddress, $portnumber, $securitytype)->setUsername($emailAddressSet)->setPassword($password);
    $transport->setLocalDomain($serveraddress);
    $arrayLogger = new Swift_Plugins_Loggers_ArrayLogger();
    $logger = new Swift_Plugins_LoggerPlugin($arrayLogger);
    $transport->registerPlugin($logger);
    
	$mailer = Swift_Mailer::newInstance($transport);
    
	$mEmail = \Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom(array($emailAddress => $clientName))
                ->setTo(array($emailAddress => $emailAddress))
                ->setReturnPath($emailAddress)
                ->setBody($bodyContent, 'text/html');

    
	try {
		$mails =$mailer->send($mEmail);
	        if($mails){
                echo json_encode(array("status"=>"success"));
                }
	}
	catch (\Swift_TransportException $e) {
		$error = $e->getMessage();
        echo json_encode(array("status" => "failed", "error" => $error,
            "loggerMessage" => explode(PHP_EOL, $logger->dump())));
    }
    
  }  
    
?>
