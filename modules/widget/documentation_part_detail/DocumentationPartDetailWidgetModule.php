<?php
/**
 * @package modules.widget
 */
class DocumentationPartDetailWidgetModule extends PersistentWidgetModule {
	private $iDocumentationPartId = null;
	private $aUnsavedDocuments = array();

	public function __construct($sSessionId) {
		parent::__construct($sSessionId);
		$iDocumentationPartCategory = 2;
		if(DocumentCategoryQuery::create()->filterById($iDocumentationPartCategory)->count() === 0) {
			throw new Exception(__METHOD__.': Please setup the exernally managed document category for this module');
		}
		$this->setSetting('documentation_image_category_id', $iDocumentationPartCategory);
		$oRichtext = WidgetModule::getWidget('rich_text', null, null, 'documentation_part');
		$oRichtext->setTemplate(PagePeer::getRootPage()->getTemplate());
		$this->setSetting('richtext_session', $oRichtext->getSessionKey());
	}

	public function setDocumentationPartId($iDocumentationPartId) {
		$this->iDocumentationPartId = $iDocumentationPartId;
	}

	public function loadData() {
		$oDocumentationPart = DocumentationPartQuery::create()->findPk($this->iDocumentationPartId);
		if($oDocumentationPart === null) {
			return array();
		}
		$aResult = $oDocumentationPart->toArray();
		$aResult['CreatedInfo'] = Util::formatCreatedInfo($oDocumentationPart);
		$aResult['UpdatedInfo'] = Util::formatUpdatedInfo($oDocumentationPart);
    $sBody = '';
		if(is_resource($oDocumentationPart->getBody())) {
			$sBody = RichtextUtil::parseStorageForBackendOutput(stream_get_contents($oDocumentationPart->getBody()))->render();
		}
		$aResult['Body'] = $sBody;
		return $aResult;
	}

	private function validate($aDocumentationPartData) {
		$oFlash = Flash::getFlash();
		$oFlash->setArrayToCheck($aDocumentationPartData);
		$oFlash->checkForValue('name', 'documentation_part_name_required');
		$oFlash->checkForValue('documentation_id', 'documentation_required');
		if($aDocumentationPartData['is_published']) {
			$oFlash->checkForValue('body', 'documentation_part_body_required');
			$oFlash->checkForValue('key', 'key_required');
		}
		$oFlash->finishReporting();
	}

	public function saveData($aDocumentationPartData) {
		if($this->iDocumentationPartId === null) {
			$oDocumentationPart = new DocumentationPart();
		} else {
		  $oDocumentationPart = DocumentationPartQuery::create()->findPk($this->iDocumentationPartId);
		}

		$this->validate($aDocumentationPartData);
		$oDocumentationPart->setName($aDocumentationPartData['name']);
		$oDocumentationPart->setKey($aDocumentationPartData['key']);
		$oDocumentationPart->setIsOverview($aDocumentationPartData['is_overview']);
		$oDocumentationPart->setIsPublished($aDocumentationPartData['is_published']);
		$oDocumentationPart->setDocumentationId($aDocumentationPartData['documentation_id']);
		$oDocumentationPart->setLanguageId($oDocumentationPart->getDocumentation()->getLanguageId());
		$oDocumentationPart->setImageId($aDocumentationPartData['image_id'] != null ? $aDocumentationPartData['image_id'] : null);
		if($oDocumentationPart->getTitle() == null) {
			$oDocumentationPart->setTitle(null);
		}

		$oRichtextUtil = new RichtextUtil();
		$oRichtextUtil->setTrackReferences($oDocumentationPart);
		$oDocumentationPart->setBody($oRichtextUtil->parseInputFromEditor($aDocumentationPartData['body']));

		if($oDocumentationPart->isNew() && is_numeric($oDocumentationPart->getDocumentationId())) {
			$oDocumentationPart->setSort(DocumentationPartQuery::create()->filterByDocumentationId($oDocumentationPart->getDocumentationId())->count()+1);
		}
		if($aDocumentationPartData['image_id'] == null && $oDocumentationPart->getDocument()) {
			$oDocumentationPart->getDocument()->delete();
		}

		if(!Flash::noErrors()) {
			// Don't validate on file upload but set is_published to false if there are errors
			if($aDocumentationPartData['documentation_id'] != null && $aDocumentationPartData['is_file_upload']) {
				$oDocumentationPart->setIsPublished(false);
			} else {
				throw new ValidationException();
			}
		}
		$oDocumentationPart->save();
		return $oDocumentationPart->getId();
	}
}