<?php namespace Raahul\SchemaExtractor;

class SchemaExtractor
{
    /**
     * Return an array of Column objects with descriptions of each column
     * @param  string $type         The type of the database
     * @param  array  $descriptions An array of columns on which DESCRIBE has been run with PDO::FETCH_OBJ
     * @return array                An array of column objects
     */
    public function extract($type = 'mysql', $descriptions)
    {
        if (!in_array( $type, array('mysql') ))
        {
            throw new \Exception('Invalid database type selected.');
        }

        $columns = array();

        foreach ($descriptions as $d)
        {
            $columns[] = ColumnFactory::getInstance($type, $d);
        }

        return $columns;
    }
}