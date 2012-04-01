<?php

include( 'handy_user_class.inc.php' );

class user_class_extended extends user_class 
{
    // Add your application specific functions here
	
    function modify_data_for_id( $User_id, $Data )
    {
        if ( $User_id <= 0 )
        {
            return 'INVALID_USERID';
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
        $values[] = $User_id;

        $modify_result = $this->DB_LINK->Execute( $modify_user_data_sql, $values );
        if ( !$modify_result )
        {
            return 'MySQL error: '. $this->DB_LINK->ErrorMsg();
        }
        else
        {
            /*
             *  Caution, use
             *      if ( $resource === TRUE ) { // Query was OK  }
             *  and not
             *      if ( $resource == TRUE ) { // Because there could be zero rows affected but the query still work }
             */
            return TRUE;
        }

        return TRUE;
    }

    function new_user( $Username, $Password, $Data = NULL )
    {
        if( $Data == NULL )
        {
            return FALSE;
        }
        $new_user_result = parent::new_user( $Username, $Password );
        if ( (int)$new_user_result > 0 )
        {
            $user_data_modify_result = $this->modify_data_for_id( $new_user_result, $Data );
            if ( $user_data_modify_result === TRUE )
            {
                return $new_user_result;
            }
            else
            {
                return $user_data_modify_result;
            }
        }
        else
        {
            return $new_user_result;
        }

    }

    function get_id()
    {
        return $this->USER_ID;
    }

    function get_value ( $column ) //returneaza valoarea dintr-o anumita coloana din db
    {
        if ( isset( $this->ROW_FROM_DATABASE[ $this->TABLE_COLUMNS[ $column ] ] ) )
        {
            return $this->ROW_FROM_DATABASE[ $this->TABLE_COLUMNS[ $column ] ];
        }
        else
        {
            if ( $this->get_database_row() )
            {
                return @$this->ROW_FROM_DATABASE[ $this->TABLE_COLUMNS[ $column ] ];
            }
            else
            {
                return FALSE;
            }
        }
    }

    function find_by( $Column, $Value, $Limit = NULL )
    {
        if ( empty( $this->TABLE_COLUMNS[ $Column ] ) )
        {
            return array();
        }
        $select_sql = sprintf( "
            SELECT
                *
            FROM
                `%s`
            WHERE
                `%s`=? ",
                $this->TABLE_NAME,
                $this->TABLE_COLUMNS[ $Column ]
                );
        if ( $Limit > 0 )
        {
            $select_sql .= sprintf( " LIMIT %d", $Limit );
        }
        $select_result = $this->DB_LINK->Execute( $select_sql, array( $Value ) );
        if ( $select_result )
        {
            return $select_result->GetRows();
        }
        else
        {
            return array();
        }
    }	
	
	
	
}

?>