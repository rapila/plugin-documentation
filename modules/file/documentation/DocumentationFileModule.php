<?php
class DocumentationFileModule extends FileModule {

	private static $DOCUMENTATION_PAGE;
	public function renderFile() {
		$sRequestType = StringUtil::camelize(Manager::usePath().'_action');
		header("Content-Type: application/json;charset=utf-8");
		print json_encode($this->$sRequestType(), JSON_FORCE_OBJECT);
	}

	private static function container($sTitle, $sURL) {
		$oContainer = new stdClass();
		$oContainer->title = $sTitle;
		$oContainer->url = LinkUtil::absoluteLink($sURL, null, LinkUtil::isSSL());
		return $oContainer;
	}

	private function metadataAction() {
		$aResult = array();
		$cAddToResult = function ($sLanguageId, $sKey, $oContainer) use (&$aResult) {
			if(isset($aResult[$sKey])) {
				$aResult[$sKey] = array($sLanguageId => $oContainer);
			} else {
				$aResult[$sKey][$sLanguageId] = $oContainer;
			}
		};
		foreach(DocumentationQuery::create()->active()->orderByName()->find() as $oDocumentation) {
			$aParts = $oDocumentation->getDocumentationPartsOrdered();
			$oContainer = self::container($oDocumentation->getTitleForDocumentation(), $oDocumentation->getURL());
			$cAddToResult($oDocumentation->getLanguageId(), $oDocumentation->getKey(), $oContainer);
			if($oDocumentation->hasTutorial()) {
				$oContainer = self::container(StringPeer::getString('wns.documentation.video_tutorial', $oDocumentation->getLanguageId(), "Tutorial"), $oDocumentation->getURL());
				$cAddToResult($oDocumentation->getLanguageId(), $oDocumentation->getKey().'/_tutorial', $oContainer);
			}
			foreach($aParts as $oPart) {
				$oContainer = self::container($oPart->getName(), $oPart->getURL());
				$cAddToResult($oPart->getLanguageId(), $oPart->getFullKey(), $oContainer);
			}
		}
		return $aResult;
	}

	private function contentAction() {
		$sLanguageId = Manager::usePath();
		$sDocumentationKey = Manager::usePath();
		$sPartKey = Manager::usePath();
		RichtextUtil::$USE_ABSOLUTE_LINKS = LinkUtil::isSSL();
		if($sPartKey) {
			$oPart = DocumentationPartQuery::create()->filterByLanguageId($sLanguageId)->filterByKeys($sDocumentationKey, $sPartKey)->findOne();
			if(!$oPart) {
				return null;
			}
			return RichtextUtil::parseStorageForFrontendOutput($oPart->getBody())->render();
		} else {
			$oDocumentation = DocumentationQuery::create()->filterByLanguageId($sLanguageId)->filterByKey($sDocumentationKey)->findOne();
			if(!$oDocumentation) {
				return null;
			}
			$sHtmlOutput = RichtextUtil::parseStorageForFrontendOutput($oDocumentation->getDescription())->render();
			$aParts = $oDocumentation->getDocumentationPartsOrdered();
			if(count($aParts) > 0) {
				$sHtmlOutput .= '<ul class="documentation_parts">';
				foreach($aParts as $i => $oPart) {
					$sHtmlOutput .= TagWriter::quickTag('li', array(), self::addPartLink($oPart));
				}
				$sHtmlOutput .= '</ul>';
			}
			$bDisplayVideo = false;
			if($bDisplayVideo && $oDocumentation->getYoutubeUrl()) {
				$sHtmlOutput .= $this->embedVideo($oDocumentation->getYoutubeUrl());
			}
			return $sHtmlOutput;
		}
	}

	private function addPartLink($oPart) {
		if(!self::$DOCUMENTATION_PAGE) {
			self::$DOCUMENTATION_PAGE = PageQuery::create()->filterByIdentifier(DocumentationFilterModule::PARENT_PAGE_IDENTIFIER)->findOne();
		}
		if(!self::$DOCUMENTATION_PAGE) {
			return;
		}
		$sLink = LinkUtil::absoluteLink(LinkUtil::link(array_merge(self::$DOCUMENTATION_PAGE->getFullPathArray(), array($oPart->getDocumentation()->getKey())), 'FrontendManager'));
		return TagWriter::quickTag('a', array('target' => 'documentation', 'href' => $sLink.'#'.$oPart->getKey()), $oPart->getName());
	}

	private function embedVideo($sLocation) {
		$oTemplateLocation = ResourceFinder::create(array('modules', 'frontend', 'media_object', 'templates', 'text', 'html.tmpl'))->returnObjects()->find();
		$oVideoTempl = new Template($oTemplateLocation);
		$oVideoTempl->replaceIdentifier('src', $sLocation);
		$oVideoTempl->replaceIdentifier('width', 420);
		$oVideoTempl->replaceIdentifier('height', 250);
		return $oVideoTempl->render();
	}

}