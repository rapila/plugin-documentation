<?php
class DocumentationsFrontendConfigWidgetModule extends FrontendConfigWidgetModule {
	public function __construct($sSessionKey, $oFrontendModule) {
		parent::__construct($sSessionKey, $oFrontendModule);
	}

	public function options() {
		$aData['display_options'] = $this->getDisplayOptions();
		$aData['documentation_options'] = $this->getDocumentationOptions();
		return $aData;
	}

	public function listDocumentationParts($aData) {
		// how to display internal and external documentation(s parts)
		$oDocumentationPartQuery = DocumentationPartQuery::create()->active();
		if($aData['documentation'] !== null) {
			$oDocumentationPartQuery->filterByDocumentationId($aData['documentation']);
		}
		if($aData['display_mode'] == 'most_recent_teaser') {
			return $oDocumentationPartQuery->orderByUpdatedAt()->limit(1)->find()->toKeyValue('Id', 'Name');
		}
		if(strpos($aData['display_mode'], 'list') !== false) {
			return $oDocumentationPartQuery->orderByDocumentationId()->orderBySort()->select(array('Id', 'Name'))->find()->toKeyValue('Id', 'Name');
		}
		return null;
	}

	private function getDisplayOptions() {
		$aResult = array();
		foreach(DocumentationsFrontendModule::$DISPLAY_MODES as $sDisplayMode) {
			$aResult[$sDisplayMode] = TranslationPeer::getString('documentation.display_option.'.$sDisplayMode, null, $sDisplayMode);
		}
		return $aResult;
	}

	private function getDocumentationOptions() {
		return DocumentationQuery::create()->orderByName()->select(array('Id', 'Name'))->find()->toKeyValue('Id', 'Name');
	}
}
