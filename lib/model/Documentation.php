<?php

/**
 * @package    propel.generator.model
 */
class Documentation extends BaseDocumentation {
	
	
	public function getLinkArray($oPage = null) {
		if($oPage === null) {
			$oPage = PageQuery::create()->filterByIdentifier(DocumentationFilterModule::PARENT_PAGE_IDENTIFIER)->active()->findOne();
		}
		if($oPage === null) {
			return null;
		}
		return $oPage->getFullPathArray(array($this->getKey()));
	}
	
	/**
	 * @deprecated use getLinkArray
	*/
	public function getLink($oPage = null) {
		return $this->getLinkArray($oPage);
	}
	
	public function getURL() {
		return LinkUtil::link($this->getLinkArray(), 'FrontendManager');
	}
	
	public function getFullName() {
		return '['.$this->getLanguageId().'] '.$this->getName().(!$this->getIsPublished() ? '' : ' ✔');
	}
	
	public function getDocumentationPartsOrdered() {
		return $this->getDocumentationParts(DocumentationPartQuery::create()->active()->orderBySort());
	}
}

