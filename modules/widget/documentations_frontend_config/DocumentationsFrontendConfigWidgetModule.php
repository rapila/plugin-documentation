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
		ErrorHandler::log('listDocumentationParts', $aData);
		$oDocumentationPartQuery = DocumentationPartQuery::create()->active();
		if($aData['documentation'] !== null) {
			$oDocumentationPartQuery->filterByDocumentationId($aData['documentation']);
		}
		return $oDocumentationPartQuery->orderByDocumentationId()->orderBySort()->find()->toKeyValue('Id', 'Name');
	}	
	
	private function getDisplayOptions() {
		$aResult = array();
		foreach(DocumentationsFrontendModule::$DISPLAY_MODES as $sDisplayMode) {
			$aResult[$sDisplayMode] = StringPeer::getString('documentation.display_option.'.$sDisplayMode, null, $sDisplayMode);
		}
		return $aResult;
	}

	private function getDocumentationOptions() {
		return array_merge(array('' => StringPeer::getString('wns.documentation_option.choose')), DocumentationQuery::create()->orderByName()->find()->toKeyValue('Id', 'Name'));
	}
}