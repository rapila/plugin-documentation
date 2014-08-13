<?php
class DocumentationFilterModule extends FilterModule {
	const ITEM_TYPE = 'documentation';
	const ITEM_TYPE_UNCATEGORIZED = 'documentation-uncategorized';
	const PARENT_PAGE_IDENTIFIER = 'documentation-page';

	public function onNavigationItemChildrenRequested(NavigationItem $oNavigationItem) {
		if(!($oNavigationItem instanceof PageNavigationItem && $oNavigationItem->getIdentifier() === self::PARENT_PAGE_IDENTIFIER)) {
			return;
		}

		$aDocumentationPartKeys = array();
		foreach(DocumentationProviderTypeModule::completeMetadata() as $sPart => $aLanguages) {
			if(isset($aLanguages[Session::language()])) {
				$aDocumentationPartKeys[$sPart] = false;
			}
		}
		foreach(DocumentationPartQuery::create()->filterByLanguageId(Session::language())->select('Key')->find() as $sPart) {
			$aDocumentationPartKeys[$sPart] = true;
		}
		ksort($aDocumentationPartKeys);
		$aDocumentations = DocumentationsFrontendModule::listQuery()->select(array('Key', 'Name', 'Title', 'NameSpace'))->orderByName()->find();
		foreach($aDocumentations as $aParams) {
			$aConfiguredParts = array();
			foreach($aDocumentationPartKeys as $sKey => $bIsInternal) {
				if(StringUtil::startsWith($sKey, $aParams['NameSpace'].'.')) {
					$aConfiguredParts[$sKey] = $bIsInternal;
					unset($aDocumentationPartKeys[$sKey]);
				} else if ($sKey > $aParams['NameSpace'].'.') {
					break;
				}
			}
			$sTitle = $aParams['Title'] != null ? $aParams['Title'] : $aParams['Name'];
			$oNavItem = new VirtualNavigationItem(self::ITEM_TYPE, $aParams['Key'], $sTitle, $aParams['Name'], $aConfiguredParts);
			$oNavigationItem->addChild($oNavItem);
		}
		// if(count($aDocumentationPartKeys) > 0) {
		// 	$oNavItem = new VirtualNavigationItem(self::ITEM_TYPE_UNCATEGORIZED, 'uncategorized', StringPeer::getString('documentations.uncategorized'), null, $aDocumentationPartKeys);
		// 	$oNavigationItem->addChild($oNavItem);
		// }
	}

	public function onPageHasBeenSet($oPage, $bIsNotFound, $oNavigationItem) {
		if($bIsNotFound || !($oNavigationItem instanceof VirtualNavigationItem) || ($oNavigationItem->getType() !== self::ITEM_TYPE && $oNavigationItem->getType() !== self::ITEM_TYPE_UNCATEGORIZED)) {
				return;
		}
		// IMPORTANT: documentation needs to be configured as content in the parent page, f.e. "show detail"
		if($oNavigationItem->getType() === self::ITEM_TYPE) {
			DocumentationsFrontendModule::$DOCUMENTATION = DocumentationQuery::create()->active()->filterByKey($oNavigationItem->getName())->findOne();
		}
		DocumentationsFrontendModule::$DOCUMENTATION_PARTS = $oNavigationItem->getData();
	}
}
