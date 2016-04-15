<?php

namespace GoyyaMobile;

use GoyyaMobile\Exception\GoyyaException;
use GoyyaMobile\Exception\NetworkException;

/**
 * Class GoyyaMobile
 *
 * @package GoyyaMobile
 */
class Message
{

	/**
	 * The Goyya Mobile base URL
	 */
	const GOYYA_BASE_URL = 'https://gate1.goyyamobile.com/sms/sendsms.asp';

	/**
	 * Goyya Mobile plans
	 */
	const PLAN_BASIC = 'OA';
	const PLAN_ECONOMY = 'MA';
	const PLAN_QUALITY = 'PM';

	/**
	 * Message types
	 */
	const MESSAGE_TYPE_TEXT_SMS = 't';
	const MESSAGE_TYPE_OVERLONG_SMS = 'c';
	const MESSAGE_TYPE_UTF8_SMS = 'utf8';

	/**
	 * The receivers mobile number
	 *
	 * @var string
	 */
	private $receiver;

	/**
	 * The senders mobile number or name
	 *
	 * Maximum 16 numeric digits or 11 alphanumeric characters from [a-z,A-Z,0-9]
	 *
	 * @var string
	 */
	private $sender;

	/**
	 * The messages text
	 *
	 * Maximum 160 bytes of GSM standard alphabet characters
	 *
	 * @var string
	 */
	private $message;

	/**
	 * The message type
	 *
	 * @var string
	 */
	private $messageType = self::MESSAGE_TYPE_TEXT_SMS;

	/**
	 * The plan the SMS submission should use. Has only effect if the `Kombitarif` is booked.
	 *
	 * @var string
	 */
	private $submissionPlan = self::PLAN_BASIC;

	/**
	 * The account ID
	 *
	 * @var string
	 */
	private $accountId;

	/**
	 * The account password
	 *
	 * @var string
	 */
	private $accountPassword;

	/**
	 * Whether the submission should get delayed.
	 *
	 * See `$plannedSubmissionTime`
	 *
	 * @var bool
	 */
	private $delayedSubmission = false;

	/**
	 * The timestamp representing the planned submission date time.
	 *
	 * @var int
	 */
	private $submissionDate = 0;

	/**
	 * In debug mode message will not get submitted through Goyya Mobile.
	 *
	 * @var bool
	 */
	private $debugMode = false;

	/**
	 * The Goyya Mobile id of the submitted message.
	 *
	 * @var int
	 */
	private $messageId;

	/**
	 * The number of SMS that were submitted
	 *
	 * @var int
	 */
	private $messageCount;

	/**
	 * @return string
	 */
	public function getReceiver()
	{
		return $this->receiver;
	}

	/**
	 * @param string $receiver
	 * @return $this
	 * @throws \InvalidArgumentException
	 */
	public function setReceiver($receiver)
	{
		$receiver = str_replace('+', '00', $receiver);
		if (strpos($receiver, '00') !== 0) {
			throw new \InvalidArgumentException('Receiver is invalid', 10);
		}
		if (!ctype_digit($receiver)) {
			throw new \InvalidArgumentException('Receiver is invalid', 11);
		}
		$this->receiver = $receiver;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSender()
	{
		return $this->sender;
	}

	/**
	 * @param string $sender
	 * @return $this
	 * @throws \InvalidArgumentException
	 */
	public function setSender($sender)
	{
		if (strpos($sender, '+') === 0) {
			$sender = '00' . substr($sender, 1);
		}
		if (preg_match("/^[a-zA-Z0-9]+$/", $sender) !== 1) {
			throw new \InvalidArgumentException('Sender contains invalid characters', 20);
		}
		if (ctype_digit($sender)) {
			if (strlen($sender) > 16) {
				throw new \InvalidArgumentException('Sender longer than 16 numeric digits', 21);
			}
		} else if (strlen($sender) > 11) {
			throw new \InvalidArgumentException('Sender longer than 11 alphanumeric characters', 22);
		}
		$this->sender = $sender;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * @param string $message
	 * @return $this
	 * @throws \InvalidArgumentException
	 */
	public function setMessage($message)
	{
		if ($this->getMessageType() == self::MESSAGE_TYPE_TEXT_SMS && strlen($message) > 160) {
			throw new \InvalidArgumentException('Message too long for type text SMS', 30);
		}
		$this->message = $message;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getMessageType()
	{
		return $this->messageType;
	}

	/**
	 * @param string $messageType
	 * @return $this
	 */
	public function setMessageType($messageType)
	{
		$this->messageType = $messageType;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSubmissionPlan()
	{
		return $this->submissionPlan;
	}

	/**
	 * @param string $submissionPlan
	 * @return $this
	 */
	public function setSubmissionPlan($submissionPlan)
	{
		$this->submissionPlan = $submissionPlan;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getAccountId()
	{
		return $this->accountId;
	}

	/**
	 * @param string $accountId
	 * @return $this
	 */
	public function setAccountId($accountId)
	{
		$this->accountId = $accountId;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getAccountPassword()
	{
		return $this->accountPassword;
	}

	/**
	 * @param string $accountPassword
	 * @return $this
	 */
	public function setAccountPassword($accountPassword)
	{
		$this->accountPassword = $accountPassword;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function hasDelayedSubmission()
	{
		return $this->delayedSubmission;
	}

	/**
	 * @param boolean $delayedSubmission
	 * @return $this
	 */
	public function setDelayedSubmission($delayedSubmission)
	{
		$this->delayedSubmission = $delayedSubmission;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getSubmissionDate()
	{
		return $this->submissionDate;
	}

	/**
	 * @return int
	 */
	private function getFormattedSubmissionDate()
	{
		if (!$this->hasDelayedSubmission()) {
			return 0;
		}
		$submissionDate = strtotime($this->submissionDate);
		$formattedDate = date('H', $submissionDate);
		$formattedDate .= date('i', $submissionDate);
		$formattedDate .= date('d', $submissionDate);
		$formattedDate .= date('m', $submissionDate);
		$formattedDate .= date('Y', $submissionDate);
		return $formattedDate;
	}

	/**
	 * @param int $submissionDate
	 * @return $this
	 */
	public function setSubmissionDate($submissionDate)
	{
		$this->submissionDate = $submissionDate;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function isDebugMode()
	{
		return $this->debugMode;
	}

	/**
	 * @param boolean $debugMode
	 * @return $this
	 */
	public function setDebugMode($debugMode)
	{
		$this->debugMode = $debugMode;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getMessageId()
	{
		return $this->messageId;
	}

	/**
	 * @return int
	 */
	public function getMessageCount()
	{
		return $this->messageCount;
	}

	/**
	 * Submits the message
	 */
	public function submit()
	{
		// Add some time to prevent timeouts
		set_time_limit(5);

		// Init curl
		$curl = $this->getCurl();

		// Execute request and parse response
		$responseBody = curl_exec($curl);
		$curlErrorCode = curl_errno($curl);
		$curlError = curl_error($curl);
		$responseStatusCode = intval(curl_getinfo($curl, CURLINFO_HTTP_CODE));
		$responseBody = $this->parseHttpResponse($responseBody);

		// Close connection
		curl_close($curl);

		// Check for errors and throw exception
		if ($curlErrorCode > 0) {
			throw new NetworkException('Goyya request with curl error ' . $curlError, 40);
		}
		if ($responseStatusCode < 200 || $responseStatusCode >= 300) {
			throw new NetworkException('Goyya request failed with HTTP status code ' . $responseStatusCode, 41);
		}
		if (strpos($responseBody, 'OK') !== 0) {
			throw new GoyyaException('Goyya request failed with response ' . $responseBody, 42);
		}

		// Extract information from response body
		$responseBody = ltrim($responseBody, ' OK');
		$responseBody = trim($responseBody, ' ()');
		$responseBodyParts = explode(',', $responseBody);

		// Check response
		if (count($responseBodyParts) < 2) {
			throw new GoyyaException('Goyya responsed with unexpected body ' . $responseBody, 43);
		}

		// Set properties from the response body
		$this->messageId = (int)trim($responseBodyParts[0]);
		$this->messageCount = (int)trim($responseBodyParts[1]);
	}

	/**
	 * @return resource
	 */
	private function getCurl()
	{
		// Setup curl
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, true);
		curl_setopt($curl, CURLINFO_HEADER_OUT, true);
		curl_setopt($curl, CURLOPT_USERAGENT, 'PhpGoyyaMobile');
		curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

		// Setup request GET params
		$requestParams = array(
			'receiver' => $this->getReceiver(),
			'sender' => $this->getSender(),
			'msg' => utf8_decode($this->getMessage()),
			'id' => $this->getAccountId(),
			'pw' => $this->getAccountPassword(),
			'time' => $this->getFormattedSubmissionDate(),
			'msgtype' => $this->getMessageType(),
			'getId' => 1,
			'countMsg' => 1,
			'test' => ($this->isDebugMode()) ? 1 : 0,
		);
		$requestQuery = http_build_query($requestParams);
		$queryCombineChar = '?';
		if (strpos(self::GOYYA_BASE_URL, '?') !== false) {
			$queryCombineChar = '&';
		}
		$url = self::GOYYA_BASE_URL . $queryCombineChar . $requestQuery;
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPGET, true);

		// Setup request header fields
		$requestHeaders = array(
			'Accept: */*',
			'Content-Type: */*',
		);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $requestHeaders);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		return $curl;
	}

	/**
	 * @param string $responseBody
	 * @return string
	 */
	private function parseHttpResponse($responseBody)
	{
		// Parse response
		$responseHeader = array();
		if (strpos($responseBody, "\r\n\r\n") !== false) {
			do {
				list($responseHeader, $responseBody) = explode("\r\n\r\n", $responseBody, 2);
				$responseHeaderLines = explode("\r\n", $responseHeader);
				$responseStatus = $responseHeaderLines[0];
				$responseStatusCode = (int)substr(
					trim($responseStatus),
					strpos($responseStatus, ' ') + 1,
					3
				);
			} while (
				strpos($responseBody, "\r\n\r\n") !== false
				&& (
					!($responseStatusCode >= 200 && $responseStatusCode < 300)
					|| !$responseStatusCode >= 400
				)
			);
			$responseHeader = preg_split('/\r\n/', $responseHeader, null, PREG_SPLIT_NO_EMPTY);
		}
		return $responseBody;
	}

}
