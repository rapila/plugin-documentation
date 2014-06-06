<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1402054834.
 * Generated on 2014-06-06 13:40:34 by jmg
 */
class PropelMigration_1402054834
{

    public function preUp($manager)
    {
        // add the pre-migration code here
    }

    public function postUp($manager)
    {
			require __DIR__.'/../../base/lib/inc.php';
			foreach(DocumentationPartQuery::create()->find() as $oDocumentationPart) {
				$aKey = explode('.', $oDocumentationPart->getKey());
				if(count($aKey) < 2) {
					continue;
				}
				array_shift($aKey);
				$oDocumentationPart->setKey(implode('.', $aKey));
				$oDocumentationPart->save();
			}
    }

    public function preDown($manager)
    {
        // add the pre-migration code here
    }

    public function postDown($manager)
    {
			require __DIR__.'/../../base/lib/inc.php';
			foreach(DocumentationPartQuery::create()->find() as $oDocumentationPart) {
				$aKey = explode('.', $oDocumentationPart->getKey());
				array_unshift($aKey, $oDocumentationPart->getDocumentation()->getKey());
				$oDocumentationPart->setKey(implode('.', $aKey));
				$oDocumentationPart->save();
			}
    }

    /**
     * Get the SQL statements for the Up migration
     *
     * @return array list of the SQL strings to execute for the Up migration
     *               the keys being the datasources
     */
    public function getUpSQL()
    {
        return array (
  'rapila' => '
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

CREATE UNIQUE INDEX `documentation_parts_U_1` ON `documentation_parts` (`key`,`language_id`,`documentation_id`);

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

    /**
     * Get the SQL statements for the Down migration
     *
     * @return array list of the SQL strings to execute for the Down migration
     *               the keys being the datasources
     */
    public function getDownSQL()
    {
        return array (
  'rapila' => '
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

DROP INDEX `documentation_parts_U_1` ON `documentation_parts`;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}