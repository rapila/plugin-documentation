<?php
/**
* @package modules.filter
*/
class DocumentationFilterModule extends FilterModule {
	
	const ITEM_TYPE = 'documentation';
	
	public function onNavigationItemChildrenRequested(NavigationItem $oCurrent) {
		if(!($oCurrent instanceof PageNavigationItem && $oCurrent->getIdentifier() === 'documentations')) {
			return;
		}
		
		$oObject = LanguageObjectQuery::create()->filterByLanguageId(Session::language())->joinContentObject()->useQuery('ContentObject')->filterByPageId($oCurrent->getMe()->getId())->filterByContainerName('content')->filterByObjectType('documentations')->endUse()->findOne();
		Util::dumpAll($oObject);
		if(!$oObject) {
			return;
		}
		
		$oModule = new DocumentationsFrontendModule($oObject);
		$aOptions = $oModule->widgetData();
		$oModule->iProjectCategoryId = @$aOptions[ProjectsFrontendModule::PROJECT_CATEGORY_ID];
		foreach($oModule->listQuery()->select(array('Slug', 'Name'))->orderByName()->find() as $aParams) {
			$oNavItem = new VirtualNavigationItem(self::ITEM_TYPE, $aParams['Slug'], $aParams['Name'], null, null);
			$oNavigationItem->addChild($oNavItem);
		}
	}
	
	public function onPageHasBeenSet($oPage, $bIsNotFound, $oNavigationItem) {
		if($bIsNotFound || !($oNavigationItem instanceof VirtualNavigationItem) || $oNavigationItem->getType() !== get_class()) {
			return;
		}
		$_REQUEST['documentation_id'] = $oNavigationItem->getData();
	}
}