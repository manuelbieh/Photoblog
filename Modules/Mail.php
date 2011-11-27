<?php

/**
 * Abstract module to send emails
 * 
 * @author Manuel Bieh
 *
 */
abstract class Modules_Mail {

	protected $Encoding = 'UTF-8';
	protected $From;
	protected $Header;
	protected $HTMLMessage;
	protected $Message;
	protected $Recipient;
	protected $Subject;
	protected $Type = 'text';

	public function __get($member) {

		$method = 'get'.ucfirst($member);
		if(method_exists($this, $method)) {
			return $this->$method();
		}

	}

	public function __set($member, $value) {

		$method = 'set'.ucfirst($member);
		if(method_exists($this, $method)) {
			$this->$method($value);
		}

	}

	public function setRecipient($email, $name=NULL) {
		$this->Recipient[] = "$name <$email>";
		return $this;
	}

	public function addRecipient($email, $name=NULL) {
		$this->Recipient[] = "$name <$email>";
		return $this;
	}

	public function setType($type) {
		$this->Type = (in_array(strtolower($type), array('multipart', 'html', 'text', 'plain'))) ? $type : 'text';
	}

	public function setEncoding($encoding) {
		$this->Encoding = $encoding;
	}

	public function setSubject($subject) {
		$this->Subject = $subject;
		return $this;
	}

	public function setMessage($message) {
		$this->Message = $message;
		return $this;
	}

	public function setHTMLMessage($message) {
		$header  = 'MIME-Version: 1.0' . "\r\n";
		$header .= 'Content-type: text/html; charset=' . $this->getEncoding() . "\r\n";
		$this->HTMLMessage = $message;
		return $this;
	}

	public function setHeader($header) {
		$this->Header[] = $header;
		return $this;
	}

	public function setCC($email, $name) {
		$this->setHeader("Cc: $name <$email>");
	}

	public function setBCC($email, $name='') {
		$this->setHeader("Bcc: $name <$email>");
	}

	public function setFrom($email, $name='') {
		$this->setHeader("From: $name <$email>");
	}

	public function getType() {
		return strtolower($this->Type);
	}

	public function getEncoding() {
		return $this->Encoding;
	}

	public function getRecipient() {
		return $this->Recipient;
	}

	public function getSubject() {
		return $this->Subject;
	}

	public function getMessage() {
		return $this->Message;
	}

	public function getHTMLMessage() {
		return $this->HTMLMessage;
	}

	public function getMultipart() {

		$boundary = md5(time() . mt_rand(10000, 99999999));
		$this->setHeader('Content-Type: multipart/alternative; boundary="' . $boundary . '"');
		$this->setHeader("MIME-Version: 1.0");

		return '
MIME-Version: 1.0
Content-Type: multipart/alternative;
	boundary="' . $boundary . '"

This is a multi-part message in MIME format.

--' . $boundary . '
Content-Type: text/plain;
	charset="' . $this->getEncoding() . '"
Content-Transfer-Encoding: quoted-printable

' . $this->getMessage() . '
--' . $boundary . '
Content-Type: text/html;
	charset="' . $this->getEncoding() . '"
Content-Transfer-Encoding: quoted-printable

' . $this->getHTMLMessage() . '

--' . $boundary . '--
';

	}

	public function getHeader() {
		return join("\r\n", (array) $this->Header);
	}

	public abstract function send();

}