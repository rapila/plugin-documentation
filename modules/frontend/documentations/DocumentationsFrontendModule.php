<?php
/**
 * @package modules.frontend
 */

class DocumentationsFrontendModule extends FrontendModule {
	
	public static $DISPLAY_MODES = array('detail', 'list', 'most_recent_teaser');
	
	public $sVersion = null;
	
	public static $DOCUMENTATION = null;
	
	public $oPage = null;
	
	const MODE_SELECT_KEY = 'display_mode';
	const DEFAULT_RAPILA_VERSION = '1.0';
	
	public function renderFrontend() {		
		$aOptions = $this->widgetData();
		if(!isset($aOptions[self::MODE_SELECT_KEY])) {
			return null;
		}
		$this->sVersion = isset($aOptions['version']) ? $aOptions['version'] : self::DEFAULT_RAPILA_VERSION;
		
		if(self::$DOCUMENTATION !== null) {
			return $this->renderDetail(self::$DOCUMENTATION);
		} 
		switch($aOptions[self::MODE_SELECT_KEY]) {
			case 'most_recent_teaser' : return $this->renderMostRecentTeaser();
			case 'list' : return $this->renderList();
		}
		// Detail is configured but no documentation_id
		if($aOptions[self::MODE_SELECT_KEY] === 'detail' && !isset($aOptions['documentation_id'])) {
			return;
		}
		// Detail is displayed if exists
		return $this->renderDetail(DocumentationQuery::create()->findPk($iDocumentationId));
	}
	
	private function setLinkPage() {
		$this->oPage = FrontendManager::$CURRENT_PAGE;
		if($this->oPage->getIdentifier() !== 'documentation-page') {
			$this->oPage = PageQuery::create()->filterByIdentifier('documentation-page')->active()->findOne();
		}
	}
	
	public function renderList() {
		$aDocumentations = self::listQuery()->find();
		if(count($aDocumentations) === 0) {
			return;
		}
		$this->setLinkPage();
		$oTemplate = $this->constructTemplate('list');
		$oItemPrototype = $this->constructTemplate('list_item');
		foreach($aDocumentations as $oDocumentation) {
			$oItemTemplate = clone $oItemPrototype;
			$oItemTemplate->replaceIdentifier('detail_link', LinkUtil::link($this->oPage->getFullPathArray(array($oDocumentation->getKey()))));
			$oItemTemplate->replaceIdentifier('title', $oDocumentation->getTitle());
			$oItemTemplate->replaceIdentifier('title_or_name', $oDocumentation->getTitle() != null ? $oDocumentation->getTitle() : $oDocumentation->getName());
			$oItemTemplate->replaceIdentifier('name', $oDocumentation->getName());
			$oTemplate->replaceIdentifierMultiple('list_item', $oItemTemplate);
		}
		return $oTemplate;
	}
	
	public static function listQuery() {
		return DocumentationQuery::create()->active()->filterByLanguageId(Session::language())->orderByName();
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
			$this->embedVideo($oTemplate, $oDocumentation);
		}		
		$oLink = TagWriter::quickTag('a', array('rel' => 'internal', 'href' => LinkUtil::link($this->oPage->getFullPathArray(array($oDocumentation->getKey()))), 'class' => 'read_more'), StringPeer::getString('wns.read_more'));
		$oTemplate->replaceIdentifier('more_link', $oLink);
		return $oTemplate;
	}
	
	public function embedVideo($oTemplate, $oDocumentation) {
		$oVideoTempl = $this->constructTemplate('iframe');
		$oVideoTempl->replaceIdentifier('src', $oDocumentation->getYoutubeUrl());
		$oVideoTempl->replaceIdentifier('width', 620);
		$oVideoTempl->replaceIdentifier('height', 400);
		$oTemplate->replaceIdentifier('youtube_video', $oVideoTempl);
	}

	public function renderDetail($oDocumentation, $bToPdf = false) {
		if($oDocumentation === null) {
			return null;
		}
		$aDocumentationParts = DocumentationPartQuery::create()->filterByDocumentationId($oDocumentation->getId())->filterByIsPublished(true)->orderBySort()->find();
		$oTemplate = $this->constructTemplate('documentation'.($bToPdf ? '_pdf' : ''));
		
		// render video if exists
		if($oDocumentation->getYoutubeUrl() != null && $bToPdf === false) {
			$this->embedVideo($oTemplate, $oDocumentation);
		}
		$oTemplate->replaceIdentifier('documentation_name', $oDocumentation->getName());
		$sDescription = RichtextUtil::parseStorageForFrontendOutput(stream_get_contents($oDocumentation->getDescription()));
		$oTemplate->replaceIdentifier('description', $sDescription);
		if($bToPdf === false) {
			$oTemplate->replaceIdentifier('pdf_link', TagWriter::quickTag('a', array('href' => LinkUtil::link(array('export_pdf', $oDocumentation->getId()), 'FileManager'), 'rel' => 'internal', 'class' => 'not_to_be_printed download'), 'PDF runterladen'));
		}
		
		// render parts
		if($bToPdf) {
			$oPartTmpl = $this->constructTemplate('part');
		} else {
			$oPartTmpl = $this->constructTemplate('part');
		}
		$sLinkToSelf = LinkUtil::linkToSelf();
		$i = 1;
		$bRequiresQuicklinks = count($aDocumentationParts) > 1;
		foreach($aDocumentationParts as $oPart) {
		  if($bRequiresQuicklinks) {
				$aParams = array('href' => $sLinkToSelf.'#'.$oPart->getNameNormalized());
				if($oPart->getTitle()) {
					$aParams = array_merge($aParams, array('title' => $oPart->getTitle()));
				}
  			$oTemplate->replaceIdentifierMultiple('part_links', TagWriter::quickTag('a', $aParams, $oPart->getName()), null, Template::NO_NEW_CONTEXT);
		  }
			$oPartTemplate = clone $oPartTmpl;
			$oPartTemplate->replaceIdentifier('name', '«'.$oPart->getName().'»');
			$oPartTemplate->replaceIdentifier('anchor', $oPart->getNameNormalized());
			if($oPart->getId() == 5) {
				// Util::dumpAll($oPartTemplate);
			}
			if($oPart->getDocument()) {
				$sSrc = !$oPart->getIsOverview() ? $oPart->getDocument()->getDisplayUrl(array('max_width' => 200)) : $oPart->getDocument()->getDisplayUrl(array('max_width' => 656));
				if(RichtextUtil::$USE_ABSOLUTE_LINKS) {
					$sSrc = LinkUtil::absoluteLink($sSrc);
				}
				$oPartTemplate->replaceIdentifier('image', TagWriter::quickTag('img', array('class' => (!$oPart->getIsOverview() ? 'image_float' : "image_fullwidth"), 'src' => $sSrc, 'alt' => 'Bildschirmfoto von '.$oPart->getName())));
				$oPartTemplate->replaceIdentifier('margin_left_class', $oPart->getIsOverview() ? '' : ' margin_left_class');
			}
			$oPartTemplate->replaceIdentifier('content', RichtextUtil::parseStorageForFrontendOutput(stream_get_contents($oPart->getBody())));
			
			$oTemplate->replaceIdentifierMultiple('part', $oPartTemplate);
			$i++;
		}
		return $oTemplate;
	}
	
	public function renderBackend() {
		$oTemplate = $this->constructTemplate('config');

		// display option
		$aDisplayOptions = array();
		foreach(self::$DISPLAY_MODES as $sDisplayMode) {
			$aDisplayOptions[$sDisplayMode] = StringPeer::getString('documentation.display_option.'.$sDisplayMode, null, StringUtil::makeReadableName($sDisplayMode));
		}
		$oTemplate->replaceIdentifier('display_options', TagWriter::optionsFromArray($aDisplayOptions, null, null, null));
		
		// documentation options
		$aDocumentationOptions = array();
		foreach(DocumentationQuery::create()->orderByName()->select(array('Id', 'Name'))->find() as $aParams) {
			$aDocumentationOptions[$aParams['Id']] = $aParams['Name'];
		}
		$oTemplate->replaceIdentifier('documentation_options', TagWriter::optionsFromArray($aDocumentationOptions, null, null, array('' => StringPeer::getString('wns.documentation_option.choose'))));
		return $oTemplate;
	}
}
