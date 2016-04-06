<?php

require_once(__DIR__ . '/../vendor/autoload.php');

try {
	$message = new GoyyaMobile\Message();
	$message
		->setAccountId('YOUR_ACCOUNT_ID')
		->setAccountPassword('YOUR_ACCOUNT_PASSWORD')
		->setDebugMode(false)
		->setDelayedSubmission(false)
		->setSubmissionDate(strtotime('+6 hours'))
		->setMessageType($message::MESSAGE_TYPE_OVERLONG_SMS)
		->setMessage('Curabitur blandit tempus porttitor. ÄÖÜß~')
		->setSubmissionPlan($message::PLAN_QUALITY)
		->setReceiver('+49151123456789')
		->setSender('Test')
		->submit();
	echo 'Message ID ' . $message->getMessageId() . PHP_EOL;
	echo 'Message count ' . $message->getMessageCount() . PHP_EOL;
} catch (GoyyaMobile\Exception\InvalidArgumentException $exception) {
	echo $exception->getMessage() . PHP_EOL;
	echo $exception->getCode() . PHP_EOL;
} catch (GoyyaMobile\Exception\NetworkException $exception) {
	echo $exception->getMessage() . PHP_EOL;
	echo $exception->getCode() . PHP_EOL;
} catch (GoyyaMobile\Exception\GoyyaException $exception) {
	echo $exception->getMessage() . PHP_EOL;
	echo $exception->getCode() . PHP_EOL;
}

