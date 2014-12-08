<?php
/**
 * @package modules.frontend
 */

class DocumentationsFrontendModule extends FrontendModule {

	public static $DISPLAY_MODES = array('detail', 'list', 'extended_list', 'most_recent_teaser', 'tutorial');

	public $sVersion = null;

	public static $DOCUMENTATION = null;
	public static $DOCUMENTATION_PARTS = null;

	public $oPage = null;

	const MODE_SELECT_KEY = 'display_mode';
	const DEFAULT_RAPILA_VERSION = '1.0';

	public function renderFrontend() {
		$aOptions = $this->widgetData();
		if(!isset($aOptions[self::MODE_SELECT_KEY])) {
			return null;
		}
		$this->sVersion = isset($aOptions['version']) ? $aOptions['version'] : self::DEFAULT_RAPILA_VERSION;
		if($aOptions[self::MODE_SELECT_KEY] === 'tutorial' && self::$DOCUMENTATION) {
			return $this->renderTutorial(self::$DOCUMENTATION);
		}
		if(self::$DOCUMENTATION !== null || self::$DOCUMENTATION_PARTS !== null) {
			return $this->renderDetail(self::$DOCUMENTATION);
		}
		switch($aOptions[self::MODE_SELECT_KEY]) {
			case 'most_recent_teaser' : return $this->renderMostRecentTeaser();
			case 'list' : return $this->renderList();
			case 'extended_list' : return $this->renderList(true);
		}
		// Detail if configured
		if($aOptions[self::MODE_SELECT_KEY] === 'detail' && isset($aOptions['documentation_id'])) {
			return $this->renderDetail(DocumentationQuery::create()->findPk($aOptions['documentation_id']));
		}
	}

	private function setLinkPage() {
		$this->oPage = FrontendManager::$CURRENT_PAGE;
		if($this->oPage->getIdentifier() !== 'documentation-page') {
			$this->oPage = PageQuery::create()->filterByIdentifier('documentation-page')->active()->findOne();
			if($this->oPage === null) {
				throw new Exception('Error in '.__METHOD__.': page with page-identifier «documentation-page» required');
			}
		}
	}

	public function renderList($bExtendedList = false) {
		$aDocumentations = self::listQuery()->find();
		if(count($aDocumentations) === 0) {
			return;
		}
		$this->setLinkPage();
		$oTemplate = $this->constructTemplate('list');
		$oItemPrototype = $this->constructTemplate($bExtendedList ? 'list_extended_item' : 'list_item');
		$oPartLinkPrototype = $this->constructTemplate('part_link');

		$sHasVideoString = StringPeer::getString('wns.documentation.with_video_tutorial');
		foreach($aDocumentations as $oDocumentation) {
			$oItemTemplate = clone $oItemPrototype;
			if($oDocumentation->getTitle()) {
				$oItemTemplate->replaceIdentifier('title_or_name', $oDocumentation->getTitle());
				$oItemTemplate->replaceIdentifier('title', $oDocumentation->getTitle());
			} else {
				$oItemTemplate->replaceIdentifier('title_or_name', $oDocumentation->getName());
			}
			$oItemTemplate->replaceIdentifier('name', $oDocumentation->getName());
			$oItemTemplate->replaceIdentifier('detail_link', LinkUtil::link($this->oPage->getFullPathArray(array($oDocumentation->getKey()))));
			if($oDocumentation->getYoutubeUrl() != null) {
				$oItemTemplate->replaceIdentifier('has_video_tutorial', $sHasVideoString);
			}
			if($bExtendedList) {
				$aDocumentationParts = $oDocumentation->getDocumentationPartsOrdered();
				foreach($aDocumentationParts as $oPart) {
					$oPartLink = clone $oPartLinkPrototype;
					$oPartLink->replaceIdentifier('href', LinkUtil::link($this->oPage->getFullPathArray(array($oDocumentation->getKey()))).'#'.$oPart->getKey());
					$oPartLink->replaceIdentifier('link_text', $oPart->getName());
					if($oPart->getTitle()) {
						$oPartLink->replaceIdentifier('title', $oPart->getTitle());
					}
	  			$oItemTemplate->replaceIdentifierMultiple('part_links', $oPartLink, null, Template::NO_NEW_CONTEXT);
				}
			}
			$oTemplate->replaceIdentifierMultiple('list_item', $oItemTemplate);
		}
		return $oTemplate;
	}

	public static function listQuery() {
		return DocumentationQuery::create()->active()->filterByLanguageId(Session::language())->orderBySort();
	}

	public function renderMostRecentTeaser() {
		$oDocumentation = DocumentationQuery::create()->active()->filterByLanguageId(Session::language())->filterByYoutubeUrl(null, Criteria::ISNOTNULL)->orderByCreatedAt(Criteria::DESC)->findOne();
		if($oDocumentation === null) {
			return null;
		}
		$this->setLinkPage();

		$oTemplate = $this->constructTemplate('teaser');
		$oTemplate->replaceIdentifier('title', $oDocumentation->getTitle());
		$oTemplate->replaceIdentifier('name', $oDocumentation->getName());
		if($oDocumentation->getYoutubeUrl() != null) {
			$this->embedVideo($oTemplate, $oDocumentation->getYoutubeUrl());
		}
		$oLink = TagWriter::quickTag('a', array('rel' => 'internal', 'href' => LinkUtil::link($this->oPage->getFullPathArray(array($oDocumentation->getKey()))), 'class' => 'read_more'), StringPeer::getString('wns.read_more'));
		$oTemplate->replaceIdentifier('link_to_detail', $oLink);
		return $oTemplate;
	}

	public function embedVideo($oTemplate, $sLocation) {
		$oVideoTempl = $this->constructTemplate('iframe');
		$oVideoTempl->replaceIdentifier('src', $sLocation);
		$oVideoTempl->replaceIdentifier('width', 620);
		$oVideoTempl->replaceIdentifier('height', 400);
		$oTemplate->replaceIdentifier('youtube_video', $oVideoTempl);
	}

	public function renderTutorial(Documentation $oDocumentation = null) {
		$oTemplate = $this->constructTemplate('documentation');
		$this->embedVideo($oTemplate, $oDocumentation->getYoutubeUrl());
		return $oTemplate;
	}

	public function renderDetail(Documentation $oDocumentation = null) {
		if(self::$DOCUMENTATION_PARTS == null) {
			self::$DOCUMENTATION_PARTS = DocumentationPartQuery::create()->filterByDocumentationId($oDocumentation->getId())->filterByIsPublished(true)->orderBySort()->find();
		}

		if($oDocumentation) {
			$sName = $oDocumentation->getName();
			$sEmbedUrl = $oDocumentation->getYoutubeUrl();
			$sDescription = RichtextUtil::parseStorageForFrontendOutput(stream_get_contents($oDocumentation->getDescription()));
		} else {
			$sName = StringPeer::getString('documentations.uncategorized');
			$sEmbedUrl = null;
			$sDescription = null;
		}
		$oTemplate = $this->constructTemplate('documentation');

		// render video if exists
		if($sEmbedUrl != null) {
			$this->embedVideo($oTemplate, $sEmbedUrl);
		}
		$oTemplate->replaceIdentifier('documentation_name', $sName);
		$oTemplate->replaceIdentifier('description', $sDescription);
		// render parts
		$oPartTmpl = $this->constructTemplate('part');
		$sLinkToSelf = LinkUtil::linkToSelf();

		$bRequiresQuicklinks = count(self::$DOCUMENTATION_PARTS) > 1;
		$oPartLinkPrototype = $this->constructTemplate('part_link');

		foreach(self::$DOCUMENTATION_PARTS as $sKey => $mPart) {
			if($mPart === true) {
				$mPart = DocumentationPartQuery::create()->filterByKey($sKey)->findOne();
			}
			$bIsOverview = false;
			if($mPart instanceof DocumentationPart) { //Internal documentation
				$sBody = RichtextUtil::parseStorageForFrontendOutput(stream_get_contents($mPart->getBody()));
				$sLinkText = $mPart->getName();
				$sTitle = $mPart->getTitle();
				$sImageUrl = null;
				if($mPart->getDocument()) {
					$sImageUrl = $mPart->getDocument()->getDisplayUrl();
					if(RichtextUtil::$USE_ABSOLUTE_LINKS) {
						$sImageUrl = LinkUtil::absoluteLink($sImageUrl);
					}
				}
				$sKey = $mPart->getKey();
				$bIsOverview = $mPart->getIsOverview();
				$sExternalLink = null;
			} else { //External documentation
				$aData = DocumentationProviderTypeModule::dataForPart($sKey, Session::language());
				$sBody = new Template($aData['content'], null, true);
				$sLinkText = $aData['title'];
				$sTitle = null;
				$sImageUrl = null;
				$sExternalLink = $aData['url'];
			}
			// Add quick links
		  if($bRequiresQuicklinks) {
				$oPartLink = clone $oPartLinkPrototype;
				$oPartLink->replaceIdentifier('href', $sLinkToSelf.'#'.$sKey);

				$oPartLink->replaceIdentifier('link_text', $sLinkText);
				if($sTitle != null) {
					$oPartLink->replaceIdentifier('title', $sTitle);
				}
  			$oTemplate->replaceIdentifierMultiple('part_links', $oPartLink, null, Template::NO_NEW_CONTEXT);
		  }
			// Add documentation part
			$oPartTemplate = clone $oPartTmpl;
			$oPartTemplate->replaceIdentifier('name', $sLinkText);
			$oPartTemplate->replaceIdentifier('anchor', $sKey);
			$oPartTemplate->replaceIdentifier('href_top', $sLinkToSelf."#top_of_page");

			$oPartTemplate->replaceIdentifier('external_link', $sExternalLink);
			if($sImageUrl) {
				$oPartTemplate->replaceIdentifier('image', TagWriter::quickTag('img', array('class' => (!$bIsOverview ? 'image_float' : "image_fullwidth"), 'src' => $sImageUrl, 'alt' => 'Bildschirmfoto von '.$sLinkText)));
				$oPartTemplate->replaceIdentifier('margin_left_class', $bIsOverview ? '' : ' margin_left_class');
			}
			$oPartTemplate->replaceIdentifier('content', $sBody);
			$oTemplate->replaceIdentifierMultiple('part', $oPartTemplate);
		}
		return $oTemplate;
	}
}
