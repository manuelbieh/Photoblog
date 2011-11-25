<?php

class Modules_RSS {

	protected $XMLDoc;
	protected $channel;

	public function __construct() {

		if(class_exists('Modules_XML')) {
			$this->XMLDoc = new Modules_XML();
		} else {
			$this->XMLDoc = new DOMDocument();
		}

		$this->rss = $this->XMLDoc->createElement('rss');
		$this->rss->setAttribute('version', '2.0');		

	}

	public function channel($data) {

		$requiredElements = array('title','link','description');
		$optionalElements = array('language','copyright','managingEditor','webMaster','pubDate','lastBuildDate','category','generator','docs','cloud','ttl','image','rating','textInput','skipHours','skipDays');

		if(is_array($data)) {

			$this->channel = $this->XMLDoc->createElement('channel');

			foreach($data AS $key => $value) {

				if(in_array($key, $optionalElements) || in_array($key, $requiredElements)) {
					$channelItem = $this->XMLDoc->createElement($key, $value);
					$this->channel->appendChild($channelItem);
				}

			}

			$this->rss->appendChild($this->channel);

		}

		return $this;

	}


	public function item($data) {

		$elements = array('title','link','description','author','category','comments','enclosure','guid','pubDate','source');

		if(is_array($data) && $this->channel instanceof DOMNode) {

			$feedItem = $this->XMLDoc->createElement('item');

			foreach($data AS $key => $value) {

				if(in_array($key, $elements)) {
					$itemElement = $this->XMLDoc->createElement($key, $value);
					$feedItem->appendChild($itemElement);
				}

			}

			$this->channel->appendChild($feedItem);

		} else {

			throw new Exception('Item contains no data or channel element not found.');

		}

		return $this;

	}

	public function render() {

		$this->XMLDoc->appendChild($this->rss);
		return $this->XMLDoc->saveXML();

	}

}