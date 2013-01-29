<?php
class DocumentationFilterModule extends FilterModule {
	const ITEM_TYPE = 'documentation';
	const PARENT_PAGE_IDENTIFIER = 'documentation-page';
	
	public function onNavigationItemChildrenRequested(NavigationItem $oNavigationItem) {
		if(!($oNavigationItem instanceof PageNavigationItem && $oNavigationItem->getIdentifier() === self::PARENT_PAGE_IDENTIFIER)) {
			return;
		}
		
		$oObject = LanguageObjectQuery::create()->filterByLanguageId(Session::language())->joinContentObject()->useQuery('ContentObject')->filterByPageId($oNavigationItem->getMe()->getId())->filterByContainerName('content')->filterByObjectType('documentations')->endUse()->findOne();
		if(!$oObject) {
			return;
		}
		
		foreach(DocumentationsFrontendModule::listQuery()->select(array('Key', 'Name', 'Title'))->orderByName()->find() as $aParams) {
			$sTitle = $aParams['Title'] != null ? $aParams['Title'] : $aParams['Name'];
			$oNavItem = new VirtualNavigationItem(self::ITEM_TYPE, $aParams['Key'], $sTitle, null, null);
			$oNavigationItem->addChild($oNavItem);
		}
	}
	
	public function onPageHasBeenSet($oPage, $bIsNotFound, $oNavigationItem) {
		if($bIsNotFound || !($oNavigationItem instanceof VirtualNavigationItem) || $oNavigationItem->getType() !== self::ITEM_TYPE) {
				return;
		}
		if($oNavigationItem instanceof VirtualNavigationItem && $oNavigationItem->getType() === self::ITEM_TYPE) {
			DocumentationsFrontendModule::$DOCUMENTATION = DocumentationQuery::create()->active()->filterByKey($oNavigationItem->getName())->findOne();
		}
		ErrorHandler::log($oNavigationItem->getName());
	}
}
