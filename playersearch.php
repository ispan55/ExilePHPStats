<?php

$PageTitle = "Exile Player Search:";
$path = dirname($_SERVER['PHP_SELF']);
include 'includes/header.php';
include 'includes/config.php';
include 'includes/functions.php';

if ((!isset($_POST['searchfield']) || $_POST['searchfield'] == '') && !isset($_GET['searchfield']))
{
    echo '<div>
    <div>

        <h2>Player Search:</h2>
     
        <form method="post" action="playersearch.php" name="myform">
            
            <p style="width:100%;padding-top:5px;padding-bottom:5px">
                <b>Search By:</b><br>
				<select name="server">                 
                  <option value="altis" selected>Altis</option>
				  <option value="chernarus">Chernarus</option>
                </select>
                <select name="searchtype">                 
                  <option value="name" selected>Name</option>
				  <option value="uid">Steam64id</option>
                </select>
                <input type="text" name="searchfield" size=45 id="textfield"></input>
                <input type="hidden" name="submitok" value="true">
                <input type="submit">
                <a href="'.$path.'/playersearch.php" style="color:#fff;">Reset</a>
            </p>
        </form>

    </div>
</div>

<script language="Javascript"  type="text/javascript"><!--
    document.myform.searchfield.focus();
    //-->
</script>';
}
else
{
    echo '<div class="content">
    <div id="login" class="remove" style="padding:30px;padding-left:110px;padding-right:120px">

        <h2>Player Search:</h2>
     
        <form method="post" action="playersearch.php" name="myform">
            
            <p style="width:100%;padding-top:5px;padding-bottom:5px">
                <b>Search By:</b><br>
				<select name="server">                 
                  <option value="altis" selected>Altis</option>
				  <option value="chernarus">Chernarus</option>
                </select>
                <select name="searchtype">                 
                  <option value="name" selected>Name</option>
				  <option value="uid">Steam64id</option>
                </select>
                <input type="text" name="searchfield" size=45 id="textfield"></input>
                <input type="hidden" name="submitok" value="true">
                <input type="submit">
                <a href="/exilestats/playersearch.php" style="color:#fff;">Reset</a>
            </p>
        </form>

    </div>
</div>

<script language="Javascript"  type="text/javascript"><!--
    document.myform.searchfield.focus();
    //-->
</script>';

    if (isset($_GET['searchfield']))
    {
        $Server = strtolower($_GET['server']);
        $Searchfield = $_GET['searchfield'];
        $SearchType = $_GET['searchtype'];
    }
    else
    {
        $Server = $_POST['server'];
        $Searchfield = $_POST['searchfield'];
        $SearchType = $_POST['searchtype'];
    }


    if ($SearchType == 'name')
    {
        $Searchfield = strtolower($Searchfield);
        $sql = "SELECT * FROM account WHERE LOWER(name) LIKE '%$Searchfield%'";
    }
    elseif ($SearchType == 'uid')
    {
        $Searchfield = strtolower($Searchfield);
        $Searchfield = str_replace(' ', '', $Searchfield);
        $sql = "SELECT * FROM account WHERE LOWER(uid) LIKE '%$Searchfield%'";
    }
    else
    {
        $sql = "SELECT * FROM account WHERE $SearchType = '$Searchfield'";
    }

    foreach ($ServerList as $ServerToCheck)
    {
        $ServerDetails = explode("|", $ServerToCheck);
        $dbhost = $ServerDetails[0];
        $dbname = $ServerDetails[1];
        $dbuser = $ServerDetails[2];
        $dbpass = $ServerDetails[3];
        $servername = ucwords($ServerDetails[4]);

        if (strtolower($servername) == $Server)
        {
            $db_local = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
        }
    }

    $result = mysqli_query($db_local, $sql);
    $align1 = ' align=right style="padding:8px;" ';

    echo '<hr><h2>Results for: ' . $SearchType . ' = "' . $Searchfield . '" on Server: '.ucwords($Server).'</h2><hr><table border="1" cellspacing=1 width=100%>';
    echo "<tr>
					<td width=150 $align1>steam64id</td>
					<td width=150 $align1>name</td>
					<td width=150 $align1>pop&nbsp;tabs</td>
					<td width=150 $align1>Respect</td>
					<td width=150 $align1>Kills</td>
					<td width=150 $align1>Deaths</td>
					<td width=150 $align1>First&nbsp;Connected</td>
					<td width=150 $align1>Last&nbsp;Connected</td>
					<td width=150 $align1>Last&nbsp;Disconnect</td>
					<td width=150 $align1>Connections</td>
				</tr>";


    while ($row = mysqli_fetch_object($result))
    {
        if (isset($row->uid) && $row->uid <> '')
        {

            // Display Account
            $uid = $row->uid;
            $steam64id = '<a href="http://steamcommunity.com/profiles/' . $uid . '" target=_blank>' . $uid . '</a> ';
            $name = $row->name;
            $poptabs = $row->money;
            $respect = $row->score;
            $kills = $row->kills;
            $deaths = $row->deaths;
            $first_connect_at = $row->first_connect_at;
            $last_connect_at = $row->last_connect_at;
            $last_disconnect_at = $row->last_disconnect_at;
            $total_connections = $row->total_connections;

            echo "<tr>
			<td width=150 $align1>$steam64id</td>
			<td width=150 $align1>$name</td>
			<td width=150 $align1>$poptabs</td>
			<td width=150 $align1>$respect</td>
			<td width=150 $align1>$kills</td>
			<td width=150 $align1>$deaths</td>
			<td width=250 $align1>$first_connect_at</td>
			<td width=250 $align1>$last_connect_at</td>
			<td width=250 $align1>$last_disconnect_at</td>
			<td width=150 $align1>$total_connections</td>
			</tr></table>";

            // Display associated territories	
            $sql3 = "SELECT territory.name, territory.position_x, territory.position_y, territory.radius, territory.level,
					account.name as owner_name, account.uid, territory.build_rights, territory.moderators, territory.created_at, territory.last_payed_at 

					FROM territory, account 
					WHERE territory.owner_uid = account.uid 
					AND account.uid = '$uid'
					ORDER BY territory.name";
            $result3 = mysqli_query($db_local, $sql3);


            if (mysqli_num_rows($result3) > 0)
            {
                echo "<hr><h2>Territories for $name</h2><hr>";
                echo '
				<table class="tftable" border="1"">
				<tr>
				<td style="width:300px;">TerritoryName</td>'
                . '<td>Coords</td>'
                . '<td>Radius</td>'
                . '<td>Level</td>'
                . '<td>Owner</td>'
                . '<td>Moderators</td>'
                . '<td style="width:200px;">Created_at</td>'
                . '<td style="width:200px;">Last_paid_at</td>'
                . '<td>BuildRights</td>'
                . '</tr>';
            }
            else
            {
                echo "<hr><h2>This player has no territories</h2><hr>";
            }


            while ($row3 = mysqli_fetch_object($result3))
            {
                $steam64id = $row3->uid;
                $territoryName = $row3->name;
                $position_x = $row3->position_x;
                $position_x = sprintf('%05d', $position_x);
                $position_y = $row3->position_y;
                $position_y = sprintf('%05d', $position_y);
                $inGameCoords = substr($position_x, 0, 3) . substr($position_y, 0, 3);
                $radius = $row3->radius;
                $level = $row3->level;
                $owner_name = $row3->owner_name;

                $moderators = $row3->moderators;
                $moderators = str_replace('[', "", $moderators);
                $moderators = str_replace(']', "", $moderators);
                $moderators = str_replace('"', "", $moderators);
                $moderators = explode(",", $moderators);
                $territoryModerators = "";

                foreach ($moderators as $moderator)
                {
                    if ($moderator <> "")
                    {
                        $sql4 = "SELECT name FROM account WHERE uid = '$moderator'";
                        //echo "<hr>$sql2<hr>";
                        $result4 = mysqli_query($db_local, $sql4);
                        $row4 = mysqli_fetch_object($result4);

                        $ModeratorName = $row4->name;
                        $ModeratorName = html_entity_decode(utf8_decode($ModeratorName));
                        $territoryModerators .= '<a href="http://steamcommunity.com/profiles/' . $moderator . '" target=_blank>' . $ModeratorName . '</a> ';
                    }
                }

                $created_at = $row3->created_at;
                $last_payed_at = $row3->last_payed_at;

                $build_rights = $row3->build_rights;
                $build_rights = str_replace('[', "", $build_rights);
                $build_rights = str_replace(']', "", $build_rights);
                $build_rights = str_replace('"', "", $build_rights);
                $buildRights = explode(",", $build_rights);
                $territoryBuilders = "";
                foreach ($buildRights as $builder)
                {
                    if ($builder <> "")
                    {
                        $sql4 = "SELECT name FROM account WHERE uid = '$builder'";
                        //echo "<hr>$sql4<hr>";
                        $result4 = mysqli_query($db_local, $sql4);
                        $row4 = mysqli_fetch_object($result4);

                        $BuilderName = $row4->name;
                        $BuilderName = html_entity_decode(utf8_decode($BuilderName));
                        $territoryBuilders .= '<a href="playersearch.php?server=' . $Server . '&searchtype=uid&searchfield=' . $builder . '" target=_blank>' . $BuilderName . '</a> ';
                    }
                }
                $territoryBuilders = rtrim($territoryBuilders);
                echo '<tr>'
                . '<td>' . $territoryName . '</td>'
                . '<td>' . $inGameCoords . '</td>'
                . '<td>' . $radius . '</td>'
                . '<td>' . $level . '</td>'
                . '<td><a href="http://steamcommunity.com/profiles/' . $steam64id . '" target=_blank>' . $owner_name . '</a></td>'
                . '<td>' . $territoryModerators . '</td>'
                . '<td>' . $created_at . '</td>'
                . '<td>' . $last_payed_at . '</td>'
                . '<td>' . $territoryBuilders . '</td>'
                . '</tr>';
            }
            echo "</table>";



            // Display Containers
            $sql2 = "SELECT * FROM container WHERE account_uid = '$uid'";
            $result2 = mysqli_query($db_local, $sql2);

            if (mysqli_num_rows($result2) > 0)
            {
                echo "<hr><h2>Containers owned by $name</h2><hr>";
                echo '
				<table class="tftable" border="1"">
				<tr>
				<td style="width:300px;">Container</td>'
                . '<td>Coords</td>'
                . '<td>PIN</td>'
                . '<td style="width:600px;">Contents</td>'
                . '<td style="width:250px;">Spawned at</td>'
                . '<td style="width:250px;">Last Used</td>'
                . '</tr>';
            }
            else
            {
                echo "<hr><h2>This player has no containers</h2><hr>";
            }

            while ($row2 = mysqli_fetch_object($result2))
            {
                $vehicle = $row2->class;
                $position_x = $row2->position_x;
                $position_x = sprintf('%05d', $position_x);
                $position_y = $row2->position_y;
                $position_y = sprintf('%05d', $position_y);
                $inGameCoords = substr($position_x, 0, 3) . substr($position_y, 0, 3);
                $pin_code = $row2->pin_code;
                $spawned_at = $row2->spawned_at;
                $last_updated = $row2->last_accessed;
                $contents = "Items: " . $row2->cargo_items . "<hr>";
                $contents .= "Magazines: " . $row2->cargo_magazines . "<hr>";
                $contents .= "Weapons: " . $row2->cargo_weapons . "<hr>";
                echo '<tr>'
                . '<td valign=top>' . $vehicle . '</td>'
                . '<td valign=top>' . $inGameCoords . '</td>'
                . '<td valign=top>' . $pin_code . '</td>'
                . '<td valign=top style="width:600px;">' . $contents . '</td>'
                . '<td valign=top>' . $spawned_at . '</td>'
                . '<td valign=top>' . $last_updated . '</td>'
                . '</tr>';
            }
            echo "</table>";



            // Display Vehicles
            $sql2 = "SELECT * FROM vehicle WHERE account_uid = '$uid'";
            $result2 = mysqli_query($db_local, $sql2);

            if (mysqli_num_rows($result2) > 0)
            {
                echo "<hr><h2>Vehicles owned by $name</h2><hr>";
                echo '
				<table class="tftable" border="1"">
				<tr>
				<td style="width:300px;">Vehicle</td>'
                . '<td>Coords</td>'
                . '<td>PIN</td>'
                . '<td style="width:600px;">Contents</td>'
                . '<td style="width:250px;">Spawned at</td>'
                . '<td style="width:250px;">Last Used</td>'
                . '</tr>';
            }
            else
            {
                echo "<hr><h2>This player has no vehicles</h2><hr>";
            }

            while ($row2 = mysqli_fetch_object($result2))
            {
                $vehicle = $row2->class;
                $position_x = $row2->position_x;
                $position_x = sprintf('%05d', $position_x);
                $position_y = $row2->position_y;
                $position_y = sprintf('%05d', $position_y);
                $inGameCoords = substr($position_x, 0, 3) . substr($position_y, 0, 3);
                $pin_code = $row2->pin_code;
                $spawned_at = $row2->spawned_at;
				if(!isset($row2->last_updated))
				{
					$last_updated = "n/a";
				}
				else
				{
					$last_updated = $row2->last_updated;
				}       
                $contents = "Items: " . $row2->cargo_items . "<hr>";
                $contents .= "Magazines: " . $row2->cargo_magazines . "<hr>";
                $contents .= "Weapons: " . $row2->cargo_weapons . "<hr>";
                echo '<tr>'
                . '<td valign=top>' . $vehicle . '</td>'
                . '<td valign=top>' . $inGameCoords . '</td>'
                . '<td valign=top>' . $pin_code . '</td>'
                . '<td valign=top style="width:600px;">' . $contents . '</td>'
                . '<td valign=top>' . $spawned_at . '</td>'
                . '<td valign=top>' . $last_updated . '</td>'
                . '</tr>';
            }
            echo "</table>";
        }
        else
        {
            echo "<hr><h1>Invalid. reset the form to continue</h1><hr>";
        }
    }
}

include 'includes/footer.php';
?>