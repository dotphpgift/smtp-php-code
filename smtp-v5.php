<?php
require_once 'swiftmailer/lib/swift_required.php';

if(!count($_POST)) {
    echo "Bad request";
    exit(1);
}

$emailContentMessage = ($_POST["message"]);
$serverAddress = $_POST["serverAddress"];
$portNumber = $_POST["portnumber"];
$securityType = $_POST["securitytype"];
$emailAddressSet = $_POST["emailAddressSet"];
$password = base64_decode($_POST["password"]);


$emailMessage = \Swift_Message::newInstance()
    ->setSubject($emailContentMessage['subject'])
    ->setFrom($emailContentMessage['fromAddress'], $emailContentMessage['fromName'])
    ->setTo($emailContentMessage['toAddress'])
    ->setBody($emailContentMessage['body'],'text/html')
;

if(isset($emailContentMessage['ccAddress'])) {
    $emailMessage->setCc($emailContentMessage['ccAddress']);
}

if(isset($emailContentMessage['bccAddress'])) {
    $emailMessage->setBcc($emailContentMessage['bccAddress']);
}

if(isset($emailContentMessage['attachments']) && count($emailContentMessage['attachments'])) {
    foreach($emailContentMessage['attachments'] as $aaset) {
        $attachment = new \Swift_Attachment(base64_decode($aaset['content']), $aaset['filename'], $aaset['contentType']);
        $emailMessage->attach($attachment);
    }
}

if($emailMessage->getId()) {
    $emailMessageId = '<'.$emailMessage->getId().'>';
    $transport = \Swift_SmtpTransport::newInstance($serverAddress, $portNumber);
    $transport->setUsername($emailAddressSet)->setPassword($password);
    if (strtolower($securityType == 'tls') || strtolower($securityType == 'ssl')) {
        $transport->setEncryption($securityType);
    }
    $arrayLogger = new \Swift_Plugins_Loggers_ArrayLogger();
    $logger = new \Swift_Plugins_LoggerPlugin($arrayLogger);
    $transport->registerPlugin($logger);

    try {
        # Check if connection started
        if($transport->isStarted() == false) {
            $transport->start();
        }

        if($transport->isStarted() == true) {
            $mailer = \Swift_Mailer::newInstance($transport);
            try {
                $success = $mailer->send($emailMessage);
                echo json_encode(array("status"=>"success", "messageId" => $emailMessage->getId()));

            } catch(Swift_TransportException $e) {
                echo json_encode(array("status" => "failedSending", "error" => $e->getMessage(),"messageId" => $emailMessage->getId(),
                    "loggerMessage" => explode(PHP_EOL, $logger->dump())));
            }
        }
    } catch (Swift_TransportException $e) {
        echo json_encode(array("status" => "failedLogin", "error" => $e->getMessage(),"messageId" => $emailMessage->getId(),
            "loggerMessage" => explode(PHP_EOL, $logger->dump())));
    }
}
