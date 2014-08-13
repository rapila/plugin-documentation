<?php
class DocumentationFileModule extends FileModule {

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
		foreach(DocumentationQuery::create()->active()->find() as $oDocumentation) {
			$oContainer = self::container($oDocumentation->getTitle(), $oDocumentation->getURL());
			$cAddToResult($oDocumentation->getLanguageId(), $oDocumentation->getKey(), $oContainer);
		}
		foreach(DocumentationPartQuery::create()->active()->find() as $oPart) {
			$oContainer = self::container($oPart->getDisplayTitle(), $oPart->getURL());
			$cAddToResult($oPart->getLanguageId(), $oPart->getFullKey(), $oContainer);
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
			return RichtextUtil::parseStorageForFrontendOutput($oDocumentation->getDescription())->render();
		}
	}
}