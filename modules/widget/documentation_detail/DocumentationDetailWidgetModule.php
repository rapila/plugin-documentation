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
		$this->setSetting('international_option', LanguageInputWidgetModule::isMonolingual());
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

	private function validate($aDocumentationData, $oDocumentation) {
		$oFlash = Flash::getFlash();
		$oFlash->setArrayToCheck($aDocumentationData);
		$oFlash->checkForValue('name', 'name_required');
		$oFlash->checkForValue('key', 'key_required');
		if(!LanguageInputWidgetModule::isMonolingual()) {
			$oFlash->checkForValue('language_id', 'language_required');
		} else {
			$oLanguage = LanguageQuery::create()->findOne();
			$oDocumentation->setLanguageId($oLanguage->getId());
		}
		$oCheckDocumentation = DocumentationQuery::create()->filterByLanguageId($oDocumentation->getLanguageId())->filterByKey($aDocumentationData['key'])->findOne();
		if($oCheckDocumentation && !Util::equals($oDocumentation, $oCheckDocumentation)) {
			$oFlash->addMessage('documentation_unique_required');
		}
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
		if($oDocumentation->getYoutubeUrl() == null) {
			$oDocumentation->setYoutubeUrl(null);
		}
		$this->validate($aDocumentationData, $oDocumentation);
		if(!Flash::noErrors()) {
			throw new ValidationException();
		}		
		return $oDocumentation->save();
	}
}