<?php
class DocumentationFileModule extends FileModule {

	public function renderFile() {
		$sRequestType = StringUtil::camelize(Manager::usePath().'_action');
		header("Content-Type: application/json;charset=utf-8");
		print json_encode($this->$sRequestType(), JSON_FORCE_OBJECT);
	}
	
	private function metadataAction() {
		$aResult = array();
		foreach(DocumentationPartQuery::create()->find() as $oPart) {
			$oPartContainer = new stdClass();
			$oPartContainer->title = $oPart->getDisplayTitle();
			$oPartContainer->url = LinkUtil::absoluteLink($oPart->getLink(), null, LinkUtil::isSSL());
			if(isset($aResult[$oPart->getKey()])) {
				$aResult[$oPart->getKey()] = array($oPart->getLanguageId() => $oPartContainer);
			} else {
				$aResult[$oPart->getKey()][$oPart->getLanguageId()] = $oPartContainer;
			}
		}
		return $aResult;
	}
	
	private function contentAction() {
		$sLanguageId = Manager::usePath();
		$sPartKey = Manager::usePath();
		$oPart = DocumentationPartQuery::create()->filterByLanguageId($sLanguageId)->filterByKey($sPartKey)->findOne();
		if(!$oPart) {
			return null;
		}
		RichtextUtil::$USE_ABSOLUTE_LINKS = LinkUtil::isSSL();
		return RichtextUtil::parseStorageForFrontendOutput($oPart->getBody())->render();
	}
}