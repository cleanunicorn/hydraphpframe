<?php

error_reporting( E_ALL );

/* MySQL */
$_database_type = 'mysqli';
$_database_host = 'localhost';
$_database_user = 'esmus_db';
$_database_pass = '123456';
$_database_name = 'esmus';

$db = mysql_connect( $_database_host, $_database_user, $_database_pass );
mysql_select_db( $_database_name );
if ( !$db )
{
    die( 'Could not connect to database' );
}

function local_query( $String )
{
    return mysql_query( $String );
}

function transformQueryToArray($query)
{
    $ret = array();
    $res = local_query($query);
    if ($res)
    {
        if (mysql_num_rows($res) > 0)
        {
            while ($r = mysql_fetch_assoc($res))
            {
                $ret[] = $r;
            }
        }
    }
    return $ret;
}

function byState($a, $b)
{
    return strcmp($a['State'], $b['State']);
}

function byTime($a, $b)
{
    return $b['Time'] - $a['Time'];
}

function byIn_use($a, $b)
{
    return $b['In_use'] - $a['In_use'];
}

$mysqlStatus = array();
$blockingQueries = array();
$blockedQueries = array();

$mysqlStatus = transformQueryToArray("SHOW FULL PROCESSLIST");

foreach ($mysqlStatus as $r)
{
    if ((!empty($r['State'])) && ($r['State'] != 'Locked'))
    {
        $blockingQueries[] = $r;
    }
    if ($r['State'] == 'Locked')
    {
        $blockedQueries[] = $r;
    }
}

usort($mysqlStatus, "byState");
usort($blockingQueries, "byState");
usort($blockingQueries, "byTime");

function drawTable($array)
{
    $i = 0;
    ?>
    <table border="1" width="100%">
    <?php
    foreach ($array as $r)
    {
        $i++;
       ?>
       <tr bgcolor="<?php if ( ($i % 2) == 0) { echo '#ffffff'; } else { echo '#eeeebb'; } ?>">
            <?php
            foreach ($r as $td)
            {
                ?>
                <td>
                    <?php echo $td ?>
                </td>
                <?php
            }
        ?>
       </tr>
       <?php

    }
    ?>
    </table>
    <?php

}
echo '<h1>active queries ('.count($blockingQueries).')</h1>';
drawTable($blockingQueries);

echo '<h1>blocked queries ('.count($blockedQueries).')</h1>';
drawTable($blockedQueries);


echo '<h1>open tables</h1>';
$openTables = transformQueryToArray("SHOW OPEN TABLES");
usort($openTables, "byIn_use");
drawTable($openTables);

echo '<h1>all queries ('.count($mysqlStatus).')</h1>';
drawTable($mysqlStatus);

echo '<h1>STATUS</h1>';
$status = transformQueryToArray("SHOW STATUS");
drawTable($status);

echo '<h1>VARIABLES</h1>';
$variables = transformQueryToArray("SHOW VARIABLES");
drawTable($variables);
