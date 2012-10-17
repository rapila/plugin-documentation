<?php
/**
 * @package modules.widget
 */
class DocumentationPartListWidgetModule extends PersistentWidgetModule {

	private $oListWidget;
	public $oDelegateProxy;
	
	public function __construct() {
		$this->oDelegateProxy = new CriteriaListWidgetDelegate($this, 'DocumentationPart', 'sort');
		$this->oListWidget = WidgetModule::getWidget('list', null, $this->oDelegateProxy);
	}
	
	public function doWidget() {
		return $this->oListWidget->doWidget('documentation_part_list');
	}
	
	public function allowSort($sSortColumn) {
		return $this->oDelegateProxy->getDocumentationId() !== CriteriaListWidgetDelegate::SELECT_ALL;
	}
	
	public function doSort($sColumnIdentifier, $oPartToSort, $oRelatedPart, $sPosition = 'before') {
		$iNewPosition = $oRelatedPart->getSort() + ($sPosition === 'before' ? 0 : 1);
		if($oPartToSort->getSort() < $oRelatedPart->getSort()) {
			$iNewPosition--;
		}
		$oPartToSort->setSort($iNewPosition);
		$oPartToSort->save();
		$oQuery = $this->oDelegateProxy->getCriteria();
		$oQuery->filterById($oPartToSort->getId(), Criteria::NOT_EQUAL);
		$oQuery->orderBySort();
		$i = 1;
		foreach($oQuery->find() as $oPart) {
			if($i == $iNewPosition) {
				$i++;
			}
			$oPart->setSort($i);
			$oPart->save();
			$i++;
		}
	}

	public function getColumnIdentifiers() {
		return array('id', 'name', 'body_truncated', 'documentation_name', 'sort', 'has_image', 'is_overview', 'is_active', 'delete');
	}
	
	public function getMetadataForColumn($sColumnIdentifier) {
		$aResult = array('is_sortable' => true);
		switch($sColumnIdentifier) {
			case 'name':
				$aResult['heading'] = StringPeer::getString('wns.documentation_part.name');
				break;			
			case 'documentation_name':
				$aResult['heading'] = StringPeer::getString('wns.documentation_part.documentation');
				break;
			case 'body_truncated':
				$aResult['heading'] = StringPeer::getString('wns.documentation_part.body');
				$aResult['is_sortable'] = false;
				break;
			case 'sort':
				$aResult['heading'] = StringPeer::getString('wns.sort');
				$aResult['display_type'] = ListWidgetModule::DISPLAY_TYPE_REORDERABLE;
				break;
			case 'has_image':
				$aResult['heading'] = StringPeer::getString('wns.documentation_part.has_image');
				$aResult['is_sortable'] = false;
				break;
			case 'is_active':
				$aResult['heading'] = StringPeer::getString('wns.documentation_part.is_active');
				break;
			case 'is_overview':
				$aResult['heading'] = StringPeer::getString('wns.documentation_part.is_overview');
				break;
			case 'delete':
				$aResult['heading'] = ' ';
				$aResult['display_type'] = ListWidgetModule::DISPLAY_TYPE_ICON;
				$aResult['field_name'] = 'trash';
				$aResult['is_sortable'] = false;
				break;
		}
		return $aResult;
	}
	
	public function getDatabaseColumnForColumn($sColumnIdentifier) {
		if($sColumnIdentifier === 'documentation_name') {
			return DocumentationPartPeer::DOCUMENTATION_ID;
		}
		if($sColumnIdentifier === 'is_active') {
			return DocumentationPartPeer::IS_INACTIVE;
		}
		if($sColumnIdentifier === 'body_truncated') {
			return DocumentationPartPeer::BODY;
		}
		return null;
	}

	public function getDocumentationName() {
		$oDocumentation = DocumentationQuery::create()->findPk($this->oDelegateProxy->getDocumentationId());
		if($oDocumentation) {
			return $oDocumentation->getName();
		}
		if($this->oDelegateProxy->getDocumentationId() === CriteriaListWidgetDelegate::SELECT_WITHOUT) {
			return StringPeer::getString('wns.documentation_part.without_documentation');
		}
		return $this->oDelegateProxy->getDocumentationId();
	}

	public function getDocumentationHasParts($iDocumentationId) {
		return DocumentationPartQuery::create()->filterByDocumentationId($iDocumentationId)->count() > 0;
	}

	public function getFilterTypeForColumn($sColumnIdentifier) {
		if($sColumnIdentifier === 'documentation_id') {
			return CriteriaListWidgetDelegate::FILTER_TYPE_IS;
		}
		return null;
	}
	
	public function getCriteria() {
		return DocumentationPartQuery::create();
	}

}