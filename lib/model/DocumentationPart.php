<?php

/**
 * @package    propel.generator.model
 */
class DocumentationPart extends BaseDocumentationPart {

	public function setName($sName) {
		$this->setNameNormalized(StringUtil::normalize($sName));
		parent::setName($sName);
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

	public function delete(PropelPDO $oConnection = null) {
		$oImage = $this->getDocument();
		if($oImage) {
			$oImage->delete();
		}
		return parent::delete($oConnection);
	}
}

