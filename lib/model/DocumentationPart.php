<?php

/**
 * @package    propel.generator.model
 */
class DocumentationPart extends BaseDocumentationPart {

	public function setName($sName) {
		$this->setNameNormalized(StringUtil::truncate(StringUtil::normalizePath($sName, '-', '-'), 50, '', 0));
		parent::setName($sName);
	}
	
	public function getDisplayTitle() {
		if(parent::getTitle()) {
			return parent::getTitle();
		}
		return $this->getName();
	}
	
	public function getDocumentationName() {
		return $this->getDocumentation()->getName();
	}
	
	public function getBodyTruncated($iLength = 70) {
		$sText = '';
		if(is_resource($this->getBody())) {
			$sText = RichtextUtil::parseStorageForBackendOutput(stream_get_contents($this->getBody()))->render();
			$sText = strip_tags($sText);
 		}
		if($iLength) {
			return StringUtil::truncate($sText, $iLength);
		}
		return $sText;
	}
	
	public function getHasImage() {
		return $this->getImageId() !== null;
	}

	public function getIsActive() {
		return !$this->getIsInactive();
	}
	
	public function getLink() {
		$oPage = PageQuery::create()->filterByIdentifier(DocumentationFilterModule::PARENT_PAGE_IDENTIFIER)->findOne();
		$aParams = $oPage->getLink();
		$aParams[] = $this->getDocumentation()->getKey();
		return LinkUtil::link($aParams, 'FrontendManager').'#'.$this->getKey();
	}

	public function delete(PropelPDO $oConnection = null) {
		$oImage = $this->getDocument();
		if($oImage) {
			$oImage->delete();
		}
		return parent::delete($oConnection);
	}
}

