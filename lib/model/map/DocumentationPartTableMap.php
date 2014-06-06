<?php



/**
 * This class defines the structure of the 'documentation_parts' table.
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
class DocumentationPartTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'model.map.DocumentationPartTableMap';

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
        $this->setName('documentation_parts');
        $this->setPhpName('DocumentationPart');
        $this->setClassname('DocumentationPart');
        $this->setPackage('model');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addColumn('name', 'Name', 'VARCHAR', true, 100, null);
        $this->addColumn('title', 'Title', 'VARCHAR', false, 255, null);
        $this->addColumn('body', 'Body', 'BLOB', false, null, null);
        $this->addColumn('key', 'Key', 'VARCHAR', true, 100, null);
        $this->addForeignKey('language_id', 'LanguageId', 'VARCHAR', 'languages', 'id', true, 3, null);
        $this->addForeignKey('documentation_id', 'DocumentationId', 'INTEGER', 'documentations', 'id', true, null, null);
        $this->addForeignKey('image_id', 'ImageId', 'INTEGER', 'documents', 'id', false, null, null);
        $this->addColumn('sort', 'Sort', 'INTEGER', false, null, null);
        $this->addColumn('is_overview', 'IsOverview', 'BOOLEAN', false, 1, false);
        $this->addColumn('is_published', 'IsPublished', 'BOOLEAN', false, 1, true);
        $this->addColumn('created_at', 'CreatedAt', 'TIMESTAMP', false, null, null);
        $this->addColumn('updated_at', 'UpdatedAt', 'TIMESTAMP', false, null, null);
        $this->addForeignKey('created_by', 'CreatedBy', 'INTEGER', 'users', 'id', false, null, null);
        $this->addForeignKey('updated_by', 'UpdatedBy', 'INTEGER', 'users', 'id', false, null, null);
        // validators
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('Language', 'Language', RelationMap::MANY_TO_ONE, array('language_id' => 'id', ), 'CASCADE', null);
        $this->addRelation('Documentation', 'Documentation', RelationMap::MANY_TO_ONE, array('documentation_id' => 'id', ), 'CASCADE', null);
        $this->addRelation('Document', 'Document', RelationMap::MANY_TO_ONE, array('image_id' => 'id', ), 'SET NULL', null);
        $this->addRelation('UserRelatedByCreatedBy', 'User', RelationMap::MANY_TO_ONE, array('created_by' => 'id', ), 'SET NULL', null);
        $this->addRelation('UserRelatedByUpdatedBy', 'User', RelationMap::MANY_TO_ONE, array('updated_by' => 'id', ), 'SET NULL', null);
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
            'denyable' =>  array (
  'mode' => '',
  'role_key' => 'documentations',
  'owner_allowed' => '',
),
            'extended_timestampable' =>  array (
  'create_column' => 'created_at',
  'update_column' => 'updated_at',
  'disable_updated_at' => 'false',
),
            'attributable' =>  array (
  'create_column' => 'created_by',
  'update_column' => 'updated_by',
),
            'extended_keyable' =>  array (
  'key_separator' => '_',
),
        );
    } // getBehaviors()

} // DocumentationPartTableMap
