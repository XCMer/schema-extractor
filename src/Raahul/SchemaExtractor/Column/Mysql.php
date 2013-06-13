<?php namespace Raahul\SchemaExtractor\Column;

use Raahul\SchemaExtractor\Column\Column;

class Mysql extends Column
{
    /**
     * Parse all the details of the given column
     */
    protected function parse()
    {
        // Parse the column name
        $this->parseName();

        // Parse the column type
        $this->parseType();

        // Parse column type parameters
        $this->parseParameters();

        // Parse if column is null
        $this->parseNull();

        // Parse if column is unsigned
        $this->parseUnsigned();

        // Parse column default value
        $this->parseDefaultValue();

        // Parse column index
        $this->parseIndex();
    }


    /**
     * Parse the name of the column
     */
    protected function parseName()
    {
        $this->name = $this->column->Field;
    }


    /**
     * Parse the type of the column without any additional parameters
     */
    protected function parseType()
    {
        // Find everything till the end or the start of the first parenthesis
        preg_match('/(^[^(]+)/', $this->column->Type, $matches);

        // We should have a match, and the type should be in $matches[1]
        $this->type = $matches[1];
    }


    /**
     * Parse the additional parameters to a column, which can be the length, precision,
     * or values in case of enums
     */
    protected function parseParameters()
    {
        // Find everything inside the parentheses
        preg_match('/\((.*)\)/', $this->column->Type, $matches);

        // We may or may not a match in $matches[1]
        if ( !isset($matches[1]) )
        {
            $this->parameters = null;
            return;
        }

        // From now on, we have parameters
        // If the field type is int or varchar, the parameter is a single one as
        // an integer
        if ( in_array($this->type, array('int', 'varchar')) )
        {
            $this->parameters = (int)$matches[1];
            return;
        }

        // Else if the field is of type decimal or enu,, the parameter is an array
        // of values, integers for decimal, and strings for enum
        if ( in_array($this->type, array('decimal', 'enum')) )
        {
            $param = str_getcsv($matches[1], ',', "'");

            // Parameters are integers in case of decimals
            if ('decimal' == $this->type)
            {
                $param = array_map('intval', $param);
            }

            $this->parameters = $param;
        }
    }


    /**
     * Parse whether the column is null
     */
    protected function parseNull()
    {
        $this->null = ($this->column->Null == 'YES');
    }


    /**
     * Parse whether the column is unsigned
     */
    protected function parseUnsigned()
    {
        // See if unsigned is the last word in the column type
        if (preg_match('/ unsigned$/', $this->column->Type))
        {
            $this->unsigned = true;
        }
        else
        {
            $this->unsigned = false;
        }
    }


    /**
     * Parse the default value of the column, which is null if there is no default
     */
    protected function parseDefaultValue()
    {
        if ($this->column->Default != 'NULL')
        {
            $this->defaultValue = $this->column->Default;
        }
        else
        {
            $this->defaultValue = null;
        }
    }


    /**
     * Parse which index the column has, false if no index present
     */
    protected function parseIndex()
    {
        switch ($this->column->Key)
        {
            case 'PRI':
                $this->index = 'primary';
                break;

            case 'UNI':
                $this->index = 'unique';
                break;

            case 'MUL':
                $this->index = 'multicolumn';
                break;

            default:
                $this->index = false;
        }
    }
}