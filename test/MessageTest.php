<?php

namespace GoyyaMobile;

require_once(__DIR__ . '/../vendor/autoload.php');

$message = new Message();
$message
	->setAccountId('1627101')
	->setAccountPassword('pimmel77')
	->setDebugMode(false)
	->setDelayedSubmission(false)
	->setMessageType($message::MESSAGE_TYPE_OVERLONG_SMS)
	->setMessage('Curabitur blandit tempus porttitor. ÄÖÜß~')
	->setSubmissionPlan($message::PLAN_QUALITY)
	->setReceiver('+4915123540849')
	->setSender('Test')
	->submit();