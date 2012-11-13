<?php

/**
 * @package    propel.generator.model
 */
class DocumentationQuery extends BaseDocumentationQuery {

	public function active() {
		return $this->filterByIsInactive(false);
	}
	
	public function orderByRand() {
		return $this->addAscendingOrderByColumn('RAND()');
	}
	
}

