<?php
class base_class
{
    public $TABLE_NAME = '';
    public $TABLE_COLUMNS = array(

                                );

    function __construct( $dblink)
    {
        if ( $dblink == NULL || !$dblink )
        {
            echo 'You need to specify a database valid link';
            return NULL;
        }
        $this->DB_LINK = $dblink;
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

        if ( @$select_result && ( $error == false ) )
        {
            $all_rows = $select_result->GetRows();
            
            return $all_rows;
        }
        else
        {
            return array();
        }
    }

    function modify_data_for_id( $Id, $Data )
    {
        if ( $Id <= 0 )
        {
            return false;
        }

        $modify_user_data_sql = sprintf( "UPDATE
                                            `%s`
                                          SET
                                          ", $this->TABLE_NAME );

        $values = array();
        foreach( $Data as $key=>$c )
        {
            $values[] = $c;
            $modify_user_data_sql .= sprintf( " `%s` = ? , ", $this->TABLE_COLUMNS[ $key ] );
        }
        $modify_user_data_sql = substr( $modify_user_data_sql, 0, -2 );
        $modify_user_data_sql .= sprintf( "WHERE
                                            `%s`= ? ", $this->TABLE_COLUMNS[ 'id' ]
            );
        $values[] = $Id;

        $modify_result = $this->DB_LINK->Execute( $modify_user_data_sql, $values );
        if ( !$modify_result )
        {
            return $this->DB_LINK->ErrorMsg();
        }
        else
        {
            return TRUE;
        }

        return TRUE;
    }

    function get_all( $Union_Table = null, $Union_Columns = null, $Where = null, $Order_by = null, $Limit = null ) 
    {
        if ( $Union_Table != null && $Union_Columns != null )
        {
            $get_all_sql = sprintf(
                "SELECT * FROM 
                    `%s` 
                LEFT JOIN 
                        `%s` 
                    ON
                        `%s`.`%s`=`%s`.`%s`", 
                    $this->TABLE_NAME,
                    $Union_Table,
                    $this->TABLE_NAME,
                    $Union_Columns[0],
                    $Union_Table,
                    $Union_Columns[1]
            );
        }
        else
        {
            $get_all_sql = sprintf("SELECT * FROM `%s`", $this->TABLE_NAME);
        }
        
        if ( $Order_by )
        {
            $get_all_sql .= "ORDER BY ";
            foreach( $Order_by as $col=>$direction )
            {
                $get_all_sql .= sprintf( 
                        "`%s` %s , ", 
                        $this->TABLE_COLUMNS[$col], 
                        ( strtoupper( $direction ) == 'ASC' ) ? 'ASC' : 'DESC'
                        );
            }
            $get_all_sql = substr( $get_all_sql, 0, -2 );
        }
        
        
        
        $values = array();
        
        if ( $Where != null  )
        {
            $get_all_sql .= "WHERE ";
            foreach( $Where as $col=>$val )
            {
                $get_all_sql .= sprintf( "
                    `%s`=? AND",
                        $this->TABLE_COLUMNS[$col]
                        );
                $values[] = $val;
            }
            $get_all_sql = substr( $get_all_sql, 0, -3 );
        }
		
		if ( $Limit != null )
		{
			$limit = '';
			if ( is_array( $Limit ) )
			{
				$limit = sprintf( "%d, %d", $Limit[0], $Limit[1] );
			}
			else
			{
				$limit = (int)$Limit;
			}
			$get_all_sql .= "LIMIT ". $limit;
		}
        
        $get_all_result = $this->DB_LINK->GetAll($get_all_sql, $values);
        if ($get_all_result) 
        {
            return $get_all_result;
        } 
        else 
        {
            return false;
        }
    }
    
    function add( $Data )
    {
        $insert_sql = sprintf( "
            INSERT INTO
                `%s`
            (
                %%s
            )
            VALUES
            (
                %%s
            )", $this->TABLE_NAME );
        
        $columns_string = '';
        $values = array();
        $values_string = '';
        
        foreach( $Data as $k=>$d )
        {
            $columns_string .= sprintf( "`%s`, ", $this->TABLE_COLUMNS[$k] );
            $values[] = $d;
            $values_string .= sprintf( "?, " );
        }
        
        $columns_string = substr( $columns_string, 0, -2 );
        $values_string = substr( $values_string, 0, -2 );
        
        $insert_sql = sprintf( $insert_sql, $columns_string, $values_string );
        
        $insert_result = $this->DB_LINK->Execute( $insert_sql, $values );
		$error = $this->DB_LINK->ErrorMsg();
        
        if ( $this->DB_LINK->Insert_ID() )
        {
            return $this->DB_LINK->Insert_ID();
        }
        else
        {
            return $error;
        }
            
    }
	
	function count( $Selectors = null )
	{
        $count_sql = sprintf( "SELECT COUNT(*) FROM `%s` ", $this->TABLE_NAME );
        $values = array();
		
		if ( $Selectors != null )
		{
			$count_sql .= " WHERE ";
			
			foreach( $Selectors as $col=>$val )
			{
				$count_sql .= sprintf( 
					"`%s`=? AND",  
						$this->TABLE_COLUMNS[$col]
						);
				$values[] = $val;
			}
			$count_sql = substr( $count_sql, 0, -3 );
		}
        
		$count = $this->DB_LINK->GetOne( $count_sql, $values );
		return $count;
	}
    
    function delete( $Selectors )
    {
        $delete_sql = sprintf( "DELETE FROM `%s` WHERE ", $this->TABLE_NAME );
        
        $values = array();
        foreach( $Selectors as $col=>$val )
        {
            $delete_sql .= sprintf( 
                "`%s`=? AND",  
                    $this->TABLE_COLUMNS[$col]
                    );
            $values[] = $val;
        }
        $delete_sql = substr( $delete_sql, 0, -3 );
        
        $delete_result = $this->DB_LINK->Execute( $delete_sql, $values );
        if ( $this->DB_LINK->Affected_Rows() )
        {
            return $this->DB_LINK->Affected_Rows();
        }
        return 0;
    }
        
}

?>