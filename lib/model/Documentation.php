<?php

/**
 * @package    propel.generator.model
 */
class Documentation extends BaseDocumentation {
	public function getLink($oPage = null) {
		if($oPage === null) {
			$oPage = PageQuery::create()->filterByIdentifier('documentation-page')->active()->findOne();
		}
		if($oPage === null) {
			return null;
		}
		return $oPage->getFullPathArray(array($this->getKey()));
	}
	
	public function getFullName() {
		return '['.$this->getLanguageId().'] '.$this->getName().(!$this->getIsPublished() ? ' [!]' : '');
	}
}

