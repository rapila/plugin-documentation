<?php

/**
 * @package    propel.generator.model
 */
class DocumentationPartPeer extends BaseDocumentationPartPeer {
	
	public static function addSearchToCriteria($sSearch, $oCriteria) {
		$oSearchCriterion = $oCriteria->getNewCriterion(self::NAME, "%$sSearch%", Criteria::LIKE);
		$oSearchCriterion->addOr($oCriteria->getNewCriterion(self::TITLE, "%$sSearch%", Criteria::LIKE));
		$oSearchCriterion->addOr($oCriteria->getNewCriterion(self::BODY, "%$sSearch%", Criteria::LIKE));
		$oCriteria->add($oSearchCriterion);
	}

}

