<?php
class cms_pages_class
{
    public $TABLE_NAME = 'CMS_pages';
    public $TABLE_COLUMNS = array(
                                'id'        => 'CMS_pages_id',
                                'content'   => 'Content',
                                'url'       => 'Url',
                                'title'     => 'Title',
                                'keywords'  => 'Keywords'
                                );

    function __construct( $dblink )
    {
        if ( $dblink == NULL || !$dblink )
        {
            echo 'You need to specify a database valid link';
            return NULL;
        }
        $this->DB_LINK = $dblink;

        $create_table_sql = sprintf( "
            CREATE TABLE IF NOT EXISTS `%s` (
              `CMS_pages_id` int(11) NOT NULL AUTO_INCREMENT,
              `Content` text NOT NULL,
              `Url` text NOT NULL,
              `Title` text NOT NULL,
              `Keywords` text NOT NULL,
              PRIMARY KEY (`CMS_pages_id`),
              UNIQUE KEY `Url_UNIQUE` (`Url`(200))
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8
            ",
                $this->TABLE_NAME
                );
        $this->DB_LINK->Execute( $create_table_sql );
    }

    function find_by( $Columns, $Values, $Limit = NULL, $Offset = NULL, $Newest = false )
    {
        $select_sql = sprintf( "
            SELECT
                *
            FROM
                `%s`
            WHERE
               ",
                $this->TABLE_NAME
                );
        $error = false;

        if ( is_array( $Columns ) && is_array( $Values ) )
        {
            if ( ( $condition_number = count( $Columns ) ) && ( count( $Columns ) == count( $Values ) ) )
            {
                for( $i = 0 ; $i < $condition_number ; $i++ )
                {
                    if ( $i > 0 )
                    {
                        $select_sql .= " AND ";
                    }
                    $select_sql .= sprintf(
                            " `%s`=?  ",
                            $this->TABLE_COLUMNS[$Columns[$i]]
                            );
                }
                if ( $Newest )
                {
                    $select_sql .= sprintf( "ORDER BY `%s` DESC", $this->TABLE_COLUMNS['id'] );
                }
                if ( $Limit > 0 )
                {
                    if ( $Offset > 0 )
                    {
                        $select_sql .= sprintf( " LIMIT %d,%d", $Offset, $Limit );
                    }
                    else
                    {
                        $select_sql .= sprintf( " LIMIT %d", $Limit );
                    }
                }
                $select_result = $this->DB_LINK->Execute( $select_sql, $Values );
            }
            else
            {
                $error = true;
            }

        }
        elseif ( !is_array( $Columns ) && !is_array( $Values ) )
        {
            $select_sql = sprintf( "
                SELECT
                    *
                FROM
                    `%s`
                WHERE
                    `%s`=? ",
                    $this->TABLE_NAME,
                    $this->TABLE_COLUMNS[ $Columns ]
                    );
            if ( $Newest )
            {
                $select_sql .= sprintf( "ORDER BY `%s` DESC", $this->TABLE_COLUMNS['id'] );
            }
            if ( $Limit > 0 )
            {
                if ( $Offset > 0 )
                {
                    $select_sql .= sprintf( " LIMIT %d,%d", $Offset, $Limit );
                }
                else
                {
                    $select_sql .= sprintf( " LIMIT %d", $Limit );
                }
            }
            $select_result = $this->DB_LINK->Execute( $select_sql, array( $Values ) );
        }
        else
        {
            $error = true;
        }

        if ( $select_result && ( $error == false ) )
        {
            return $select_result->GetRows();
        }
        else
        {
            return array();
        }
    }

    function get_all()
    {
        $get_all_sql = sprintf( "SELECT * FROM `%s`", $this->TABLE_NAME );
        $get_all_result = $this->DB_LINK->GetAll( $get_all_sql );
        if ( $get_all_result )
        {
            return $get_all_result;
        }
        else
        {
            return false;
        }
    }

    function create( $Content, $Url = null, $Title = null, $Keywords = null )
    {
        $cols = '';
        $vals =  '';

        $insert_page_query = sprintf( "
            INSERT INTO
                `%s`
            (
                %%s
            )
            VALUES
            (
                %%s
            )
            ",
                $this->TABLE_NAME );

        $cols .= '`'. $this->TABLE_COLUMNS['content'] .'`';
        $vals .= "'". $Content ."'";
        if ( !empty( $Url ) )
        {
            $cols .= ', `'. $this->TABLE_COLUMNS['url'] .'`';
            $vals .= ", '". $Url ."'";
        }
        if ( !empty( $Title ) )
        {
            $cols .= ', `'. $this->TABLE_COLUMNS['title'] .'`';
            $vals .= ", '". $Title ."'";
        }
        if ( !empty( $Keywords ) )
        {
            $cols .= ', `'. $this->TABLE_COLUMNS['keywords'] .'`';
            $vals .= ", '". $Keywords ."'";
        }

        $insert_page_query = sprintf( $insert_page_query,
                $cols,
                $vals
                );

        $insert_page_result = $this->DB_LINK->Execute( $insert_page_query );
        if ( $insert_page_result )
        {
            return $this->DB_LINK->Insert_ID();
        }
        else
        {
            return $insert_page_result;
        }
    }

    function update( $Id, $Content = null, $Url = null, $Title = null, $Keywords = null )
    {
        if ( $Id <= 0 )
        {
            return false;
        }

        $update_page_query = sprintf( "
            UPDATE
                `%s`
            SET
                %%s
            WHERE
                `%s`= ?
            ",
                $this->TABLE_NAME,
                $this->TABLE_COLUMNS['id']
                );

        $vals = array();

        if ( !empty( $Content ) )
        {
            $vals[ $this->TABLE_COLUMNS['content'] ] = $Content;
        }
        if ( !empty( $Url ) )
        {
            $vals[ $this->TABLE_COLUMNS['url'] ] = $Url;
        }
        if ( !empty( $Title ) )
        {
            $vals[ $this->TABLE_COLUMNS['title'] ] = $Title;
        }
        if ( !empty( $Keywords ) )
        {
            $vals[ $this->TABLE_COLUMNS['keywords'] ] = $Keywords;
        }

        $update_page_query_generated = $this->DB_LINK->AutoExecute(
                    $this->TABLE_NAME,
                    $vals,
                    'UPDATE',
                    ' `'. $this->TABLE_COLUMNS['id'] .'`='. (int)$Id
                );
        return $update_page_query_generated;
    }

    function read( $Url = null, $Id = null )
    {
        if ( empty( $Url ) && $Id <= 0 )
        {
            return false;
        }
        else
        {
            if ( !empty( $Url ) )
            {
                $selector_col = $this->TABLE_COLUMNS['url'];
                $selector_val = $Url;
            }
            elseif ( (int)$Id > 0 )
            {
                $selector_col = $this->TABLE_COLUMNS['id'];
                $selector_val = $Id;
            }
            else
            {
                return false;
            }
        }

        $read_page_query = sprintf( "
            SELECT
                *
            FROM
                `%s`
            WHERE
                `%s`=?
            ",
                $this->TABLE_NAME,
                $selector_col
                );
        $read_page_result = $this->DB_LINK->GetRow( $read_page_query, array( $selector_val ) );
        if ( count( $read_page_result ) > 0 )
        {
            return $read_page_result;
        }
        else
        {
            return false;
        }
    }

    function delete( $Url = null, $Id = null )
    {
        if ( empty( $Url ) && $Id <= 0 )
        {
            return false;
        }
        else
        {
            if ( !empty( $Url ) )
            {
                $selector_col = $this->TABLE_COLUMNS['url'];
                $selector_val = $Url;
            }
            elseif ( (int)$Id > 0 )
            {
                $selector_col = $this->TABLE_COLUMNS['id'];
                $selector_val = $Id;
            }
            else
            {
                return false;
            }
        }

        $delete_page_query = sprintf( "
            DELETE FROM
                `%s`
            WHERE
                `%s`=?
            ",
                $this->TABLE_NAME,
                $selector_col
                );
        $delete_page_result = $this->DB_LINK->Execute( $delete_page_query, array( $selector_val ) );
        return $delete_page_result;
    }


}
?>