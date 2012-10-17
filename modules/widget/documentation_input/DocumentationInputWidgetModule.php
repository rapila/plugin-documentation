<?php
/**
 * @package modules.widget
 */
class DocumentationInputWidgetModule extends WidgetModule {
	
	public function getDocumentations() {
		return WidgetJsonFileModule::jsonBaseObjects(DocumentationQuery::create()->orderByName()->find(), array('id', 'name'));
	}
}