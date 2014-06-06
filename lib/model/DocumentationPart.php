<?php

/**
 * @package    propel.generator.model
 */
class DocumentationPart extends BaseDocumentationPart {
	
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
	
	public function getFullKey() {
		return implode('/', $this->getKeys());
	}
	
	public function getKeys() {
		return array($this->getDocumentation()->getKey(), $this->getKey());
	}
	
	/**
	 * @deprecated use getURL
	*/
	public function getLink() {
		return $this->getURL();
	}

	public function getLinkArray($oPage = null) {
		$aLink = $this->getDocumentation()->getLinkArray($oPage);
		$aLink[] = '#'.$this->getKey();
		return LinkUtil::link($aLink, 'FrontendManager');
	}
	
	public function getURL() {
		return LinkUtil::link($this->getLinkArray(), 'FrontendManager');
	}

	public function delete(PropelPDO $oConnection = null) {
		$oImage = $this->getDocument();
		if($oImage) {
			$oImage->delete();
		}
		return parent::delete($oConnection);
	}
}

