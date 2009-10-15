<?php

class HeaderControl extends BaseControl {

	/**
	 * docTypes
	 */
	const HTML_4 = 'html4';
	const XHTML_1 = 'xhtml1';

	/**
	 * docType levels
	 */
	const STRICT = 'strict';
	const TRANSITIONAL = 'transitional';
	const FRAMESET = 'frameset';

	/**
	 * languages
	 */
	const CZECH = 'cs';
	const SLOVAK = 'sk';
	const ENGLISH = 'en';
	const GERMAN = 'de';

	/**
	 * contentTypes
	 */	
	const TEXT_HTML = 'text/html';
	const APPLICATION_XHTML = 'application/xhtml+xml';

	private $docType;
	private $docTypeLevel;

	private $language;

	private $title;
	private $titleSeparator;
	private $titlesReverseOrder = true;
	private $titles = array();

	private $rssChannels = array();

	private $metaTags = array();

	private $contentType;
	private $forceContentType;

	private $favicon;

	private $minifyCss = false;
	private $minifyJs = false;

	public function __construct($docType, $language, $title) {
		$this->setDocType($docType);
		$this->setLanguage($language);
		$this->setTitle($title);

		$this->setContentType(self::TEXT_HTML);

		try {
			$this->setFavicon('favicon.ico'); 
		} catch (FileNotFoundException $e) { }
	}

	protected function createComponentCss() {
		return new CssLoader;
	}

  protected function createComponentJs() {
		return new JavaScriptLoader;
	}

	public function setDocType($docType, $level=self::STRICT) {
		if ($docType == self::HTML_4 || $docType == self::XHTML_1) {
			$this->docType = $docType;
		} else {
			throw new InvalidArgumentException("Doctype $docType is not supported.");
		}

		if ($level == self::STRICT || $level == self::TRANSITIONAL || $level == self::FRAMESET) {
			$this->docTypeLevel = $level;
		} else {
			throw new InvalidArgumentException("Doctype level $level is not supported.");
		}

		return $this; //fluent interface
	}

	public function getDocType() {
		return $this->docType;
	}

	public function getDocTypeLevel() {
		return $this->docTypeLevel;
	}

	public function setLanguage($language) {
		if ($language == self::CZECH ||
				$language == self::SLOVAK ||
				$language == self::ENGLISH ||
				$language == self::GERMAN) {
			$this->language = $language;
		} else {
			throw new InvalidArgumentException("Language $language is not supported.");
		}

		return $this; //fluent interface
	}

	public function getLanguage() {
		return $this->language;
	}

	public function setTitle($title) {
		if ($title != null && $title != '') {
			$this->title = $title;
		} else {
			throw new InvalidArgumentException("Title must be non-empty string.");
		}

		return $this; //fluent interface
	}

	public function getTitle($index = 0) {
		if (count($this->titles) == 0) {
			return $this->title;
		} else if (count($this->titles)-1-$index < 0) {
			return $this->getTitle();
		} else {
			return $this->titles[count($this->titles)-1-$index];
		}
	}

	public function addTitle($title) {
		if ($this->titleSeparator) {
			$this->titles[] = $title;
		} else {
			throw new InvalidStateException('Title separator is not set.');
		}

		return $this;
	}

	public function getTitles() {
		return $this->titles;
	}

	public function setTitleSeparator($separator) {
		$this->titleSeparator = $separator;
		
		return $this; //fluent interface
	}

	public function getTitleSeparator() {
		return $this->titleSeparator;
	}

	public function setTitlesReverseOrder($reverseOrder) {
		$this->titlesReverseOrder = (bool) $reverseOrder;

		return $this; //fluent interface
	}

	public function isTitlesOrderReversed() {
		return $this->titlesReverseOrder;
	}
	
	public function getTitleString() {
		if ($this->titles) {
			if (!$this->titlesReverseOrder) {
				array_unshift($this->titles, $this->title);
			} else {
				$this->titles = array_reverse($this->titles);
				ksort($this->titles);
				array_push($this->titles, $this->title);
			}

			return implode($this->titleSeparator, $this->titles);

		} else {
			return $this->title;
		}
	}

	public function addRssChannel($title, $link) {
		$this->rssChannels[] = array(
			'title' => $title,
			'link' => $link,
		);

		return $this; //fluent interface
	}

	public function getRssChannels() {
		return $this->rssChannels;
	}

	public function setContentType($contentType, $force=false) {
		if ($contentType == self::APPLICATION_XHTML && $this->docType != self::XHTML_1) {
			throw new InvalidArgumentException("Cannot send $contentType type with non-XML doctype.");
		}

		if ($contentType == self::TEXT_HTML || $contentType == self::APPLICATION_XHTML) {
			$this->contentType = $contentType;
		} else {
			throw new InvalidArgumentException("Content type $contentType is not supported.");
		}

		$this->forceContentType = (bool) $force;

		return $this; //fluent interface
	}

	public function getContentType() {
		return $this->contentType;
	}

	public function isContentTypeForced() {
		return $this->forceContentType;
	}

	public function setFavicon($filename) {
		if (file_exists(WWW_DIR . Environment::getVariable('baseUri') . $filename)) {
			$this->favicon = $filename;
		} else {
			throw new FileNotFoundException('Favicon ' . WWW_DIR . Environment::getVariable('baseUri') . $filename . ' not found.');
		}

		return $this; //fluent interface
	}

	public function getFavicon() {
		return $this->favicon;
	}

	public function setMetaTag($name, $value) {
		$this->metaTags[$name] = $value;
		
		return $this; //fluent interface
	}

	public function getMetaTag($name) {
		return isset($this->metaTags[$name]) ? $this->metaTags[$name] : null;
	}

	public function getMetaTags() {
		return $this->metaTags;
	}

	public function setAuthor($author) {
		$this->setMetaTag('author', $author);
		
		return $this; //fluent interface
	}

	public function getAuthor() {
		return $this->getMetaTag('author');
	}

	public function setDescription($description) {
		$this->setMetaTag('description', $description);
		
		return $this; //fluent interface
	}

	public function getDescription() {
		return $this->getMetaTag('description');
	}

	public function addKeywords($keywords) {
		if (is_array($keywords)) {
			if ($this->keywords) {
				$this->setMetaTag('keywords', $this->getKeywords() . ', ' . implode(', ', $keywords));
			} else {
				$this->setMetaTag('keywords', implode(', ', $keywords));
			}
		} else if (is_string($keywords)){
			if ($this->keywords) {
				$this->setMetaTag('keywords', $this->getKeywords() . ', ' . $keywords);
			} else {
				$this->setMetaTag('keywords', $keywords);
			}
		} else {
			throw new InvalidArgumentException('Type of keywords argument is not supported.');
		}

		return $this; //fluent interface
	}

	public function getKeywords() {
		return $this->getMetaTag('keywords');
	}

	public function setRobots($robots) {
		$this->setMetaTag('robots', $robots);

		return $this; //fluent interface
	}

	public function getRobots() {
		return $this->getMetaTag('robots');
	}

	public function setMinifyCss($minifyCss) {
		$this->minifyCss = (bool) $minifyCss;
		
		return $this; //fluent interface
	}

	public function isCssMinified() {
		return $this->minifyCss;
	}

	public function setMinifyJs($minifyJs) {
		$this->minifyJs = (bool) $minifyJs;

		return $this; //fluent interface
	}

	public function isJsMinified() {
		return $this->minifyJs;
	}

	public function render() {
		$this->renderBegin();
		$this->renderRss();
		$this->renderCss();
		$this->renderJs();
		$this->renderEnd();
	}

	public function renderBegin() {
		$template = $this->createTemplate();
		$template->setFile(dirname(__FILE__) . '/HeaderBegin.phtml');

		$template->docType = $this->docType;
		$template->docTypeString = $this->getDocTypeString();

		if ($this->docType == self::XHTML_1 &&
				$this->contentType == self::APPLICATION_XHTML &&
				($this->forceContentType || $this->isClientXhtmlCompatible())) {
			$template->xmlProlog = "<?xml version='1.0' encoding='utf-8'?>";
			$template->contentType = self::APPLICATION_XHTML;

			$response = Environment::getHttpResponse();
			$response->setContentType(self::APPLICATION_XHTML, 'utf-8');
			$response->setHeader('Vary', 'Accept');
		} else {
			$template->contentType = self::TEXT_HTML;
			Environment::getHttpResponse()->setContentType(self::TEXT_HTML, 'utf-8');
		}

		$template->xml = $this->docType == self::XHTML_1;

		$template->language = $this->language;

		$template->title = $this->getTitleString();

		$template->favicon = $this->favicon;
		$template->metaTags = $this->metaTags;

		$template->render();
	}

	public function renderEnd() {
		$template = $this->createTemplate();
		$template->setFile(dirname(__FILE__) . '/HeaderEnd.phtml');
		
		$template->render();
	}

	public function renderRss($channels=null) {
		$template = $this->createTemplate();
		$template->setFile(dirname(__FILE__) . '/HeaderRss.phtml');

		if ($channels !== null) {
			$this->rssChannels = array();

			foreach($channels as $title => $link) {
				$this->addRssChannel($title, $link);
			}
		}

		$template->channels = $this->rssChannels;

		$template->render();
	}

	public function renderCss() {
		$css = $this['css'];
		if (func_num_args() > 0) {
			$css->addFiles(func_get_args());
		}

		$css->render();
	}

	public function renderJs() {
		$js = $this['js'];
		if (func_num_args() > 0) {
			$js->addFiles(func_get_args());
		}

		$js->render();
	}

	private function getDocTypeString($docType=null, $level=null) {
		if ($docType == null) {
			$docType = $this->docType;
		}

		if ($level == null) {
			$level = $this->docTypeLevel;
		}

		if ($docType == self::HTML_4) {
			if ($level == self::STRICT) {
				return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
			} else if ($level == self::TRANSITIONAL) {
				return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
			} else if ($level == self::FRAMESET) {
				return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">';
			}
		} else if ($docType == self::XHTML_1) {
			if ($level == self::STRICT) {
				return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
			} else if ($level == self::TRANSITIONAL) {
				return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
			} else if ($level == self::FRAMESET) {
				return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">';
			}
		} else {
			throw new InvalidStateException("Doctype $docType of level $level is not supported.");
		}
	}

	private function isClientXhtmlCompatible() {
		$req = Environment::getHttpRequest();
		return stristr($req->getHeader('Accept'), 'application/xhtml+xml') ||
										$req->getHeader('Accept') == '*/*';
	}

}