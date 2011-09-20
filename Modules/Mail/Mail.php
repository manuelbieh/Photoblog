<?php

class Modules_Mail_Mail extends Modules_Mail {

	public function send() {

		#$message = ($this->getType() == 'html') ? $this->getHTMLMessage() : $this->getMessage();
		switch($this->getType()) {
			case 'html':
				$message = $this->getHTMLMessage();
				break;
			case 'multipart':
				$message = $this->getMultipart();
				break;
			case 'text':
			case 'plain':
			default:
				$message = $this->getMessage();
				break;
		}

		foreach((array) $this->getRecipient() AS $recipient) {
			mail($recipient, $this->getSubject(), $message, $this->getHeader());
		}

	}

}