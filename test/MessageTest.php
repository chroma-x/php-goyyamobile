<?php

namespace GoyyaMobile;

use CommonException;

/**
 * Class MessageTest
 *
 * @package GoyyaMobile
 */
class MessageTest extends \PHPUnit_Framework_TestCase
{

	public function testSetReceiverInvalid1()
	{
		$this->setExpectedException(get_class(new \InvalidArgumentException()));
		$message = new Message();
		$message->setReceiver('this-is-invalid');
	}

	public function testSetReceiverInvalid2()
	{
		$this->setExpectedException(get_class(new \InvalidArgumentException()));
		$message = new Message();
		$message->setReceiver('00-this-is-invalid');
	}

	public function testSetReceiverValid()
	{
		$message = new Message();
		$message->setReceiver('+4915112345678');
		$this->assertEquals('004915112345678', $message->getReceiver());
		$message->setReceiver('004915112345678');
		$this->assertEquals('004915112345678', $message->getReceiver());
	}

	public function testSetSenderInvalid1()
	{
		$this->setExpectedException(get_class(new \InvalidArgumentException()));
		$message = new Message();
		// Phone number too long
		$message->setSender('0049151123456789012');
	}

	public function testSetSenderInvalid2()
	{
		$this->setExpectedException(get_class(new \InvalidArgumentException()));
		$message = new Message();
		// String too long
		$message->setSender('thisstringislongerthanelevencharacters');
	}

	public function testSetSenderInvalid3()
	{
		$this->setExpectedException(get_class(new \InvalidArgumentException()));
		$message = new Message();
		// String contains invalid characters
		$message->setSender('äöü');
	}

	public function testSetSenderValid()
	{
		$message = new Message();
		$message->setSender('+4915112345678');
		$this->assertEquals('004915112345678', $message->getSender());
		$message->setSender('Sendername');
		$this->assertEquals('Sendername', $message->getSender());
	}

	public function testSetMessageInvalid()
	{
		$this->setExpectedException(get_class(new \InvalidArgumentException()));
		$testMessage = str_pad('', 161, '0');
		$message = new Message();
		$message->setMessage($testMessage);
	}

	public function testSetMessageValid()
	{
		$shortTestMessage = str_pad('', 160, '0');
		$longTestMessage = str_pad('', 161, '0');
		$message = new Message();
		$message->setMessage($shortTestMessage);
		$this->assertEquals($shortTestMessage, $message->getMessage());
		$message
			->setMessageType(Message::MESSAGE_TYPE_OVERLONG_SMS)
			->setMessage($longTestMessage);
		$this->assertEquals($longTestMessage, $message->getMessage());
	}

	public function testConfigure()
	{
		$message = new Message();
		$message
			->setAccountId('YOUR_ACCOUNT_ID')
			->setAccountPassword('YOUR_ACCOUNT_PASSWORD')
			->setDebugMode(false)
			->setDelayedSubmission(true)
			->setSubmissionDate(strtotime('2020-01-01 12:00:00'))
			->setMessageType($message::MESSAGE_TYPE_OVERLONG_SMS)
			->setMessage('Curabitur blandit tempus porttitor. ÄÖÜß~')
			->setSubmissionPlan($message::PLAN_QUALITY)
			->setReceiver('+49151123456789')
			->setSender('Test');
		$this->assertEquals(Message::PLAN_QUALITY, $message->getSubmissionPlan());
		$this->assertEquals(true, $message->hasDelayedSubmission());
		$this->assertEquals(strtotime('2020-01-01 12:00:00'), $message->getSubmissionDate());
	}

	public function testSubmitSuccess()
	{
		$performTest = getenv('PERFORM_SUBMISSION_TEST');
		if ((int)$performTest !== 1) {
			$this->markTestSkipped('Submission test was skipped by environment.');
		}
		$goyyaAccountId = getenv('GOYYA_MOBILE_ACCOUNT_ID');
		$goyyaPassword = getenv('GOYYA_MOBILE_PASSWORD');
		if ($goyyaAccountId === false || $goyyaPassword === false) {
			$this->markTestSkipped('Submission test was skipped. No Goyya credentials found.');
		}
		$message = new Message();
		$message
			->setAccountId($goyyaAccountId)
			->setAccountPassword($goyyaPassword)
			->setDebugMode(true)
			->setDelayedSubmission(false)
			->setMessageType($message::MESSAGE_TYPE_TEXT_SMS)
			->setMessage('Curabitur blandit tempus porttitor. ÄÖÜß~')
			->setSubmissionPlan($message::PLAN_QUALITY)
			->setReceiver('+49151123456789')
			->setSender('Test')
			->submit();
	}

	public function testSubmitFailed()
	{
		$this->setExpectedException(get_class(new CommonException\ApiException\Base\ApiException()));
		$message = new Message();
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
	}

}
