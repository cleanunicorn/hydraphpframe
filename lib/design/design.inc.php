<?php

/*

    You can create the tables for this class with

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `Design_init` (
    `Vizitator_id` int(11) NOT NULL,
    `Design_id` int(11) default NULL,
    `Actiune_id` int(11) default NULL,
    `Data` datetime default NULL,
    KEY `Design_id_INDEX` (`Design_id`),
    KEY `Vizitator_id_INDEX` (`Vizitator_id`),
    KEY `Data_INDEX` (`Data`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `Design_vizitatori` (
    `Vizitator_id` int(11) NOT NULL auto_increment,
    `IP` varchar(15) default NULL,
    `User_id` int(10) unsigned default NULL,
    `Data` datetime default NULL,
    PRIMARY KEY  (`Vizitator_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;



*/

class Design_monitor
{

    private $DB = null;

    public $Vizitator_id = null;

    private $DESIGN_VIZITATORI_TABLE_NAME = 'Design_vizitatori';
    private $DESIGN_VIZITATORI_COL = array(
                                                'id' 			=> 'Vizitator_id',
                                                'ip' 			=> 'IP',
                                                'data' 			=> 'Data',
                                                'user_id'		=> 'User_id'
                                        );

    private $DESIGN_INIT_TABLE_NAME = 'Design_init';
    private $DESIGN_INIT_COL = array(
                                        'id' 			=>	'Vizitator_id',
                                        'design_id'		=>	'Design_id',
                                        'data'			=>	'Data',
                                        'actiune_id'	=>	'Actiune_id'
                                );

    function Design_monitor( $Dblink = null, $Vizitator_id = null, $User_id = null )
    {
        if ( $Dblink )
        {
                $this->DB = $Dblink;

                // Daca vizitatorul e cunoscut
                if ( $Vizitator_id )
                {
                        $this->Vizitator_id = $Vizitator_id;
                }
                else
                {
                        $v_id = $this->vizitator_create( $User_id );
                        if ( $v_id == false )
                        {
                                return false;
                        }
                        $this->Vizitator_id = $v_id;
                }
        }
        else
        {
                return false;
        }
    }

    function vizitator_create( $User_id = null )
    {
        $adauga_vizitator_query = sprintf( " INSERT INTO `%s` ( `%s`, `%s`, `%s` ) VALUES ( '%s', '%d', NOW() ) ",
                                                                                        $this->DESIGN_VIZITATORI_TABLE_NAME,
                                                                                        $this->DESIGN_VIZITATORI_COL['ip'],
                                                                                        $this->DESIGN_VIZITATORI_COL['user_id'],
                                                                                        $this->DESIGN_VIZITATORI_COL['data'],

                                                                                        client_ip(),
                                                                                        $User_id
                                                                                        );

        $adauga_vizitator_result = $this->DB->db_query( $adauga_vizitator_query );
        
        if ( empty( $adauga_vizitator_result['error'] ) )
        {
            if ( $adauga_vizitator_result['insert_id'] )
            {
                    return $adauga_vizitator_result['insert_id'];
            }
        }
        return false;
    }

    function init_design( $Design_id )
    {
        if ( !$this->DB )
        {
                return false;
        }

        if ( !$this->Vizitator_id )
        {
                return false;
        }

        $adauga_design_query = sprintf( "INSERT INTO `%s`
                                                    (
                                                            `%s`,
                                                            `%s`,
                                                            `%s`
                                                    )
                                            VALUES
                                                    (
                                                            '%d',
                                                            '%d',
                                                            NOW()
                                                    )",
                    $this->DESIGN_INIT_TABLE_NAME,
                    $this->DESIGN_INIT_COL['design_id'],
                    $this->DESIGN_INIT_COL['id'],
                    $this->DESIGN_INIT_COL['data'],
                    $Design_id,
                    $this->Vizitator_id
                     );

        $adauga_design_result = $this->DB->db_query( $adauga_design_query );
        if ( !empty( $adauga_design_result['error'] ) )
        {
                return false;
        }
        else
        {
                return true;
        }
    }

    function adauga_actiune( $Actiune_id, $Design_id = null )
    {
        if ( !$this->DB )
        {
                return false;
        }

        if ( !$this->Vizitator_id )
        {
                return false;
        }

        $update_actiune_query = sprintf( "UPDATE
                                                `%s`
                                        SET
                                                `%s`='%d'
                                        WHERE
                                                `%s`.`%s` IS NULL AND
                                                `%s`.`%s`='%d'
                                                ",
                                                $this->DESIGN_INIT_TABLE_NAME,

                                                $this->DESIGN_INIT_COL['actiune_id'],
                                                $Actiune_id,

                                                $this->DESIGN_INIT_TABLE_NAME,
                                                $this->DESIGN_INIT_COL['actiune_id'],

                                                $this->DESIGN_INIT_TABLE_NAME,
                                                $this->DESIGN_INIT_COL['id'],
                                                $this->Vizitator_id
                                );

        if ( $Design_id )
        {
                $update_actiune_query = sprintf( "%s AND
                                                    `%s`.`%s`='%d'",
                                                    $update_actiune_query,
                                                    $this->DESIGN_INIT_TABLE_NAME,
                                                    $this->DESIGN_INIT_COL['design_id'],
                                                    $Design_id
                                                     );
        }

        $update_actiune_query .= sprintf( " ORDER BY `%s` DESC LIMIT 1 ",
                                            $this->DESIGN_INIT_COL['data']
                                    );

        $update_actiune_result = $this->DB->db_query( $update_actiune_query );
        if ( $update_actiune_result['affected_rows'] )
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}

?>
