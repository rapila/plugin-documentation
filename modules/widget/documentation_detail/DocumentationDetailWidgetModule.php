<?php
/**
 * @package modules.widget
 */
class DocumentationDetailWidgetModule extends PersistentWidgetModule {

	private $iDocumentationId = null;
	
	public function __construct($sSessionId) {
		parent::__construct($sSessionId);
		$oRichtext = WidgetModule::getWidget('rich_text', null, null, 'documentation');
		$oRichtext->setTemplate(PagePeer::getRootPage()->getTemplate());
		$this->setSetting('richtext_session', $oRichtext->getSessionKey());
	}

	public function setDocumentationId($iDocumentationId) {
		$this->iDocumentationId = $iDocumentationId;
	}
	
	public function loadData() {
		$oDocumentation = DocumentationQuery::create()->findPk($this->iDocumentationId);
		$aResult = $oDocumentation->toArray();
		$aResult['CreatedInfo'] = Util::formatCreatedInfo($oDocumentation);
		$aResult['UpdatedInfo'] = Util::formatUpdatedInfo($oDocumentation);
		$sDescription = '';
		if(is_resource($oDocumentation->getDescription())) {
			$sDescription = RichtextUtil::parseStorageForBackendOutput(stream_get_contents($oDocumentation->getDescription()))->render();
		} else {
			$sDescription = $oDocumentation->getDescription();
		}
		$aResult['Description'] = $sDescription;
		return $aResult;
	}

	private function validate($aDocumentationData) {
		$oFlash = Flash::getFlash();
		$oFlash->setArrayToCheck($aDocumentationData);
		$oFlash->checkForValue('name', 'name_required');
		$oFlash->checkForValue('version', 'version_required');
		$oFlash->finishReporting();
	}
	
	public function saveData($aDocumentationData) {
		if($this->iDocumentationId === null) {
			$oDocumentation = new Documentation();
		} else {
			$oDocumentation = DocumentationQuery::create()->findPk($this->iDocumentationId);
		}
		$oDocumentation->fromArray($aDocumentationData, BasePeer::TYPE_FIELDNAME);
		$oDocumentation->setDescription(RichtextUtil::parseInputFromEditorForStorage($aDocumentationData['description']));
		$this->validate($aDocumentationData);
		if(!Flash::noErrors()) {
			throw new ValidationException();
		}
		ErrorHandler::log('DocumentationData', $aDocumentationData, 'DocumentArray', $oDocumentation->toArray());
		
		return $oDocumentation->save();
	}
}