<?php

/**
 * @package    propel.generator.model
 */
class DocumentationQuery extends BaseDocumentationQuery {

	public function active() {
		return $this->filterByIsPublished(true);
	}

	public function withTutorialOrParts() {
		return $this->filterByYoutubeUrl(null, Criteria::ISNOTNULL)->_or()->useDocumentationPartQuery()->filterByIsPublished(true)->endUse();
	}

	public function orderByRand() {
		return $this->addAscendingOrderByColumn('RAND()');
	}

}

