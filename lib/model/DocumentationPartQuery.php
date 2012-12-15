<?php

/**
 * @package    propel.generator.model
 */
class DocumentationPartQuery extends BaseDocumentationPartQuery {

	public function active() {
		return $this->filterByIsPublished(true);
	}
}

