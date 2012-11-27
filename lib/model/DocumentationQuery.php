<?php

/**
 * @package    propel.generator.model
 */
class DocumentationQuery extends BaseDocumentationQuery {

	public function active() {
		return $this->filterByIsPublished(true);
	}
	
	public function orderByRand() {
		return $this->addAscendingOrderByColumn('RAND()');
	}
	
}

