<?php

/**
 * @package    propel.generator.model
 */
class DocumentationPartQuery extends BaseDocumentationPartQuery {

	public function active() {
		return $this->filterByIsPublished(true)->useDocumentationQuery()->active()->endUse();
	}

	public function filterByKeys($sDocumentationKey, $sDocumentationPartKey) {
		return $this->useDocumentationQuery()->filterByKey($sDocumentationKey)->endUse()->filterByKey($sDocumentationPartKey);
	}

	public function filterByFullKey($sFullKey) {
		$aFullKey = explode('/', $sFullKey);
		$sDocumentationKey = array_shift($aFullKey);
		$sDocumentationPartKey = implode('/', $aFullKey);
		return $this->filterByKeys($sDocumentationKey, $sDocumentationPartKey);
	}
}

