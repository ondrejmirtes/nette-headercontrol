<?php

/**
 * HeaderControl<br />
 * This renderable component is ultimate solution for valid and complete HTML headers.
 *
 * @author Ondřej Mirtes
 * @copyright (c) Ondřej Mirtes 2009, 2010
 * @license MIT
 * @package HeaderControl
 */
class HeaderControl extends Control {

	/**
	 * doctypes
	 */
	const HTML_4 = self::HTML_4_STRICT; //backwards compatibility
	const HTML_4_STRICT = 'html4_strict';
	const HTML_4_TRANSITIONAL = 'html4_transitional';
	const HTML_4_FRAMESET = 'html4_frameset';

	const HTML_5 = 'html5';

	const XHTML_1 = self::XHTML_1_STRICT; //backwards compatibility
	const XHTML_1_STRICT = 'xhtml1_strict';
	const XHTML_1_TRANSITIONAL = 'xhtml1_transitional';
	const XHTML_1_FRAMESET = 'xhtml1_frameset';

	/**
	 * languages
	 */
	const CZECH = 'cs';
	const SLOVAK = 'sk';
	const ENGLISH = 'en';
	const GERMAN = 'de';

	/**
	 * content types
	 */
	const TEXT_HTML = 'text/html';
	const APPLICATION_XHTML = 'application/xhtml+xml';

	/** @var string doctype */
	private $docType;

	/** @var bool whether doctype is XML compatible or not */
	private $xml;

	/** @var string document language */
	private $language;

	/** @var string document title */
	private $title;

	/** @var string title separator */
	private $titleSeparator;

	/** @var bool whether title should be rendered in reverse order or not */
	private $titlesReverseOrder = true;

	/** @var array document hierarchical titles */
	private $titles = array();

	/** @var array site rss channels */
	private $rssChannels = array();

	/** @var array header meta tags */
	private $metaTags = array();

	/** @var string document content type */
	private $contentType;

	/** @var bool whether XML content type should be forced or not */
	private $forceContentType;

	/** @var string path to favicon (without $basePath) */
	private $favicon;

	public function __construct($docType, $language, $title) {
		$this->setDocType($docType);
		$this->setLanguage($language);
		$this->setTitle($title);

		$this->setContentType(self::TEXT_HTML);

		try {
			$this->setFavicon('favicon.ico');
		} catch (FileNotFoundException $e) {

		}
	}

	protected function createComponentCss() {
		return new CssLoader;
	}

	protected function createComponentJs() {
		return new JavaScriptLoader;
	}

	public function setDocType($docType) {
		if ($docType == self::HTML_4_STRICT || $docType == self::HTML_4_TRANSITIONAL ||
				$docType == self::HTML_4_FRAMESET || $docType == self::HTML_5 ||
				$docType == self::XHTML_1_STRICT || $docType == self::XHTML_1_TRANSITIONAL ||
				$docType == self::XHTML_1_FRAMESET) {
			$this->docType = $docType;
			$this->xml = Html::$xhtml = ($docType == self::XHTML_1_STRICT ||
							$docType == self::XHTML_1_TRANSITIONAL ||
							$docType == self::XHTML_1_FRAMESET);
		} else {
			throw new InvalidArgumentException("Doctype $docType is not supported.");
		}

		return $this; //fluent interface
	}

	public function getDocType() {
		return $this->docType;
	}

	public function isXml() {
		return $this->xml;
	}

	public function setLanguage($language) {
		$this->language = $language;

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
		if ($contentType == self::APPLICATION_XHTML &&
				$this->docType != self::XHTML_1_STRICT && $this->docType != self::XHTML_1_TRANSITIONAL &&
				$this->docType != self::XHTML_1_FRAMESET) {
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
		if (file_exists(WWW_DIR . '/' . $filename)) {
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
		} else if (is_string($keywords)) {
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

	public function render() {
		$this->renderBegin();
		$this->renderRss();
		$this->renderCss();
		$this->renderJs();
		$this->renderEnd();
	}

	public function renderBegin() {
		$response = Environment::getHttpResponse();
		if ($this->docType == self::XHTML_1_STRICT &&
				$this->contentType == self::APPLICATION_XHTML &&
				($this->forceContentType || $this->isClientXhtmlCompatible())) {
			$contentType = self::APPLICATION_XHTML;
			$response->setHeader('Vary', 'Accept');
			echo "<?xml version='1.0' encoding='utf-8'?>\n";
		} else {
			$contentType = self::TEXT_HTML;
			Environment::getHttpResponse()->setContentType($contentType, 'utf-8');
		}

		$response->setContentType($contentType, 'utf-8');

		echo $this->getDocTypeString() . "\n";

		echo '<html' . ($this->xml ? ' xmlns="http://www.w3.org/1999/xhtml" xml:lang="'
				. $this->language . '" lang="' . $this->language . '"' : '') . ">\n";

		echo "<head>\n";

		$metaLanguage = Html::el('meta')->content($this->language);
		$metaLanguage->attrs['http-equiv'] = 'Content-Language';
		echo $metaLanguage . "\n";

		$metaContentType = Html::el('meta')->content($contentType);
		$metaContentType->attrs['http-equiv'] = 'Content-Type';
		echo $metaContentType . "\n";

		echo Html::el('title', $this->getTitleString()) . "\n";

		if ($this->favicon != '') {
			echo Html::el('link')->rel('shortcut icon')
					->href(Environment::getVariable('baseUri') . $this->favicon) . "\n";
		}

		foreach ($this->metaTags as $name=>$content) {
			echo Html::el('meta')->name($name)->content($content) . "\n";
		}
	}

	public function renderEnd() {
		echo "</head>\n";
	}

	public function renderRss($channels=null) {
		if ($channels !== null) {
			$this->rssChannels = array();

			foreach ($channels as $title => $link) {
				$this->addRssChannel($title, $link);
			}
		}

		foreach ($this->rssChannels as $channel) {
			echo Html::el('link')->rel('alternate')->type('application/rss+xml')
					->title($channel['title'])
					->href(Environment::getApplication()->getPresenter()->link($channel['link'])) . "\n";
		}
	}

	public function renderCss() {
		$css = $this['css'];
		if (func_num_args() > 0) {
			$css->addFiles(func_get_args());
		}

		$css->render();
		
		echo "\n";
	}

	public function renderJs() {
		$js = $this['js'];
		if (func_num_args() > 0) {
			$js->addFiles(func_get_args());
		}

		$js->render();
		
		echo "\n";
	}

	private function getDocTypeString($docType=null) {
		if ($docType == null) {
			$docType = $this->docType;
		}

		switch ($docType) {
			case self::HTML_4_STRICT:
				return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
			break;
			case self::HTML_4_TRANSITIONAL:
				return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
			break;
			case self::HTML_4_FRAMESET:
				return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">';
			break;
			case self::HTML_5:
				return '<!DOCTYPE html>';
			break;
			case self::XHTML_1_STRICT:
				return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
			break;
			case self::XHTML_1_TRANSITIONAL:
				return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
			break;
			case self::XHTML_1_FRAMESET:
				return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">';
			break;
			default:
				throw new InvalidStateException("Doctype $docType is not supported.");
		}
	}

	private function isClientXhtmlCompatible() {
		$req = Environment::getHttpRequest();
		return stristr($req->getHeader('Accept'), 'application/xhtml+xml') ||
				$req->getHeader('Accept') == '*/*';
	}

}
