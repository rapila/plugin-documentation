<?php



/**
 * This class defines the structure of the 'documentations' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 * @package    propel.generator.model.map
 */
class DocumentationTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'model.map.DocumentationTableMap';

    /**
     * Initialize the table attributes, columns and validators
     * Relations are not initialized by this method since they are lazy loaded
     *
     * @return void
     * @throws PropelException
     */
    public function initialize()
    {
        // attributes
        $this->setName('documentations');
        $this->setPhpName('Documentation');
        $this->setClassname('Documentation');
        $this->setPackage('model');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('ID', 'Id', 'INTEGER', true, null, null);
        $this->addColumn('NAME', 'Name', 'VARCHAR', true, 100, null);
        $this->addColumn('TITLE', 'Title', 'VARCHAR', true, 255, null);
        $this->addColumn('DESCRIPTION', 'Description', 'BLOB', true, null, null);
        $this->addColumn('YOUTUBE_URL', 'YoutubeUrl', 'VARCHAR', false, 200, null);
        $this->addColumn('KEY', 'Key', 'VARCHAR', false, 100, null);
        $this->addColumn('NAME_SPACE', 'NameSpace', 'VARCHAR', false, 60, null);
        $this->addColumn('VERSION', 'Version', 'VARCHAR', false, 20, null);
        $this->addForeignKey('LANGUAGE_ID', 'LanguageId', 'VARCHAR', 'languages', 'ID', true, 3, null);
        $this->addColumn('IS_PUBLISHED', 'IsPublished', 'BOOLEAN', false, 1, true);
        $this->addColumn('SORT', 'Sort', 'INTEGER', false, null, null);
        $this->addColumn('CREATED_AT', 'CreatedAt', 'TIMESTAMP', false, null, null);
        $this->addColumn('UPDATED_AT', 'UpdatedAt', 'TIMESTAMP', false, null, null);
        $this->addForeignKey('CREATED_BY', 'CreatedBy', 'INTEGER', 'users', 'ID', false, null, null);
        $this->addForeignKey('UPDATED_BY', 'UpdatedBy', 'INTEGER', 'users', 'ID', false, null, null);
        // validators
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('Language', 'Language', RelationMap::MANY_TO_ONE, array('language_id' => 'id', ), 'CASCADE', null);
        $this->addRelation('UserRelatedByCreatedBy', 'User', RelationMap::MANY_TO_ONE, array('created_by' => 'id', ), 'SET NULL', null);
        $this->addRelation('UserRelatedByUpdatedBy', 'User', RelationMap::MANY_TO_ONE, array('updated_by' => 'id', ), 'SET NULL', null);
        $this->addRelation('DocumentationPart', 'DocumentationPart', RelationMap::ONE_TO_MANY, array('id' => 'documentation_id', ), 'CASCADE', null, 'DocumentationParts');
    } // buildRelations()

    /**
     *
     * Gets the list of behaviors registered for this table
     *
     * @return array Associative array (name => parameters) of behaviors
     */
    public function getBehaviors()
    {
        return array(
            'denyable' => array('mode' => '', 'role_key' => 'documentations', 'owner_allowed' => '', ),
            'extended_timestampable' => array('create_column' => 'created_at', 'update_column' => 'updated_at', 'disable_updated_at' => 'false', ),
            'attributable' => array('create_column' => 'created_by', 'update_column' => 'updated_by', ),
            'extended_keyable' => array('key_separator' => '_', ),
        );
    } // getBehaviors()

} // DocumentationTableMap
