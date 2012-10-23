<?php
/**
 * @package modules.frontend
 */

class DocumentationsFrontendModule extends FrontendModule {
	
	public static $DISPLAY_MODES = array('detail', 'list');
	public $sVersion = null;
	
	const MODE_SELECT_KEY = 'display_mode';
	
	public function renderFrontend() {		
		$aOptions = $this->widgetData();
		if(!isset($aOptions[self::MODE_SELECT_KEY])) {
			return null;
		}
		$this->sVersion = isset($aOptions['version']) ? $aOptions['version'] : '1.0';

		$iDocumentationId = isset($aOptions['documentation_id']) && $aOptions['documentation_id'] != null ? $aOptions['documentation_id'] : null;
		$oDocumentation = DocumentationQuery::create()->findPk($iDocumentationId);
		if($oDocumentation === null) {
			return $this->renderList();
		}
		return $this->renderDetail($oDocumentation);
	}
	
	public function renderList() {
		$aDocumentations = $this->listQuery()->find();
		if(count($aDocumentations) === 0) {
			return;
		}
		$oPage = FrontendManager::$CURRENT_PAGE;
		$oTemplate = $this->constructTemplate('list');
		$oItemPrototype = $this->constructTemplate('list_item');
		foreach($aDocumentations as $oDocumentation) {
			$oItemTemplate = clone $oItemPrototype;
			$oItemTemplate->replaceIdentifier('detail_link', LinkUtil::link($oPage->getFullPathArray(array($oDocumentation->getSlug()))));
			$oItemTemplate->replaceIdentifier('name', $oDocumentation->getName());
			$oTemplate->replaceIdentifierMultiple('list_item', $oItemTemplate);
		}
		return $oTemplate;
	}
	
	public function listQuery() {
		return DocumentationQuery::create()->filterByVersion($this->sVersion)->orderByName();
	}

	public function renderDetail($oDocumentation, $bToPdf = false) {
		if($oDocumentation === null) {
			return null;
		}
		$aDocumentationParts = DocumentationPartQuery::create()->filterByDocumentationId($oDocumentation->getId())->filterByIsInactive(false)->orderBySort()->find();
		$oTemplate = $this->constructTemplate('documentation'.($bToPdf ? '_pdf' : ''));
		
		// render video if exists
		if($oDocumentation->getYoutubeUrl() != null && $bToPdf === false) {
			$oVideoTempl = $this->constructTemplate('iframe');
			$oVideoTempl->replaceIdentifier('src', $oDocumentation->getYoutubeUrl());
			$oVideoTempl->replaceIdentifier('width', 560);
			$oVideoTempl->replaceIdentifier('height', 315);
			$oTemplate->replaceIdentifier('youtube_video', $oVideoTempl);
			$oTemplate->replaceIdentifier('tutorial_name', $oDocumentation->getName());
		}
		// $sCss = file_get_contents(SITE_DIR.'/web/css/site.css')."\n";
		$oTemplate->replaceIdentifier('documentation_title', $oDocumentation->getTitle());
		$oTemplate->replaceIdentifier('description', RichtextUtil::parseStorageForFrontendOutput(stream_get_contents($oDocumentation->getDescription())));
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
  			$oTemplate->replaceIdentifierMultiple('part_links', TagWriter::quickTag('a', array('href' => $sLinkToSelf.'#'.$oPart->getNameNormalized()), $oPart->getName()), null, Template::NO_NEW_CONTEXT);
		  }
			$oPartTemplate = clone $oPartTmpl;
			$oPartTemplate->replaceIdentifier('name', '«'.$oPart->getName().'»');
			$oPartTemplate->replaceIdentifier('anchor', $oPart->getNameNormalized());
			if($oPart->getDocument()) {
				$sSrc = !$oPart->getIsOverview() ? $oPart->getDocument()->getDisplayUrl(array('max_width' => 200)) : $oPart->getDocument()->getDisplayUrl();
				if(RichtextUtil::$USE_ABSOLUTE_LINKS) {
					$sSrc = LinkUtil::absoluteLink($sSrc);
				}
				$oPartTemplate->replaceIdentifier('image', TagWriter::quickTag('img', array('class' => (!$oPart->getIsOverview() ? 'image_float' : "image_fullwidth"), 'src' => $sSrc, 'alt' => 'Bildschirmfoto von '.$oPart->getName())));
			}
			$oPartTemplate->replaceIdentifier('content', RichtextUtil::parseStorageForFrontendOutput(stream_get_contents($oPart->getBody())));
			$oPartTemplate->replaceIdentifier('margin_left_class', $oPart->getIsOverview() ? '' : ' margin_left_class');
			
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
