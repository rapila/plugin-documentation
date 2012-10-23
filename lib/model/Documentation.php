<?php

/**
 * @package    propel.generator.model
 */
class Documentation extends BaseDocumentation {
	public function setName($sName) {
		if($this->isNew() || $this->getSlug() == null) {
			$this->setSlug(StringUtil::truncate(StringUtil::normalizePath($sName, '-', '-'), 50, '', 0));
		}
		parent::setName($sName);
	}
}

