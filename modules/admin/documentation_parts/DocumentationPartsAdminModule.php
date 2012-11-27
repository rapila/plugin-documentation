<?php
/**
 * @package modules.admin
 */
class DocumentationPartsAdminModule extends AdminModule {
	
	private $oListWidget;
	private $oSidebarWidget;
	private $oInputWidget;
	
	public function __construct() {
		$this->oListWidget = new DocumentationPartListWidgetModule();

		$this->oSidebarWidget = new ListWidgetModule();
		$this->oSidebarWidget->setListTag(new TagWriter('ul'));
		$this->oSidebarWidget->setDelegate(new CriteriaListWidgetDelegate($this, 'Documentation', 'full_name'));
		$this->oSidebarWidget->setSetting('initial_selection', array('documentation_id' => $this->oListWidget->oDelegateProxy->getDocumentationId()));

		$this->oInputWidget = new SidebarInputWidgetModule();
	}
	
	public function mainContent() {
		return $this->oListWidget->doWidget();
	}
	
	public function sidebarContent() {
		return $this->oSidebarWidget->doWidget();
	}
	
	public function getColumnIdentifiers() {
		return array('documentation_id', 'full_name', 'magic_column');
	}
	
	public function getMetadataForColumn($sColumnIdentifier) {
		$aResult = array();
		switch($sColumnIdentifier) {
			case 'documentation_id':
				$aResult['display_type'] = ListWidgetModule::DISPLAY_TYPE_DATA;
				$aResult['field_name'] = 'id';
				break;
			case 'full_name':
				$aResult['heading'] = StringPeer::getString('wns.documentation.sidebar_heading');
				break;
			case 'magic_column':
				$aResult['display_type'] = ListWidgetModule::DISPLAY_TYPE_CLASSNAME;
				$aResult['has_data'] = false;
				break;
		}
		return $aResult;
	}
	
	public function getCustomListElements() {
		if(DocumentationQuery::create()->count() > 0) {
			return array(
				array('documentation_id' => CriteriaListWidgetDelegate::SELECT_ALL,
							'full_name' => StringPeer::getString('wns.sidebar.select_all'),
							'magic_column' => 'all'));
		}
		return array();
	}
	
	public function getDatabaseColumnForColumn($sColumnIdentifier) {
		if($sColumnIdentifier === 'full_name') {
			return DocumentationPeer::NAME;
		}
		return null;
	}

	public function usedWidgets() {
		return array($this->oListWidget, $this->oSidebarWidget, $this->oInputWidget);
	}
}
