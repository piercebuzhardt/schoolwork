<?php session_start(); ?>
<!DOCTYPE html>
<html>
<?php $title = 'Organization Change Overview';
include('../php/header.php'); ?>
<main>
    <?php
    //admin only page
    authenticate_admin($db);
    ?>

    <!-- Search bar -->
    <form name='search' method='POST' action='../pages/org_changes.php'>
        <table>
            <tr>
                <td><label for='reggie'>Username Like:</label><input name='reggie'
                                                                     type='text' <?php if (isset($_POST['reggie'])) echo "value='$_POST[reggie]'"; ?>>
                <td>
            </tr>
            <tr>
                <td><label for='reggiespon'>Sponsor Org Like:</label><input name='reggiespon'
                                                                            type='text' <?php if (isset($_POST['reggiespon'])) echo "value='$_POST[reggiespon]'"; ?>>
                    <input class='button' type='submit'></td>
            </tr>
        </table>
    </form>

    <table border='1px'>
        <?php
        // Clearable searches
        if (isset($_POST['search']) && $_POST['reggie'] == '') unset($_POST['reggie']);
        if (isset($_POST['searchspon']) && $_POST['reggiespon'] == '') unset($_POST['reggiespon']);

        // Select general SQL or limited SQL based on search (if applicable)
        $sql = "SELECT * FROM `driver_changes_org` RIGHT OUTER JOIN `account` ON `account`.`account_id` = `driver_changes_org`.`driver_account_id` INNER JOIN `sponsor_org` ON `driver_changes_org`.`sponsor_org_id` = `sponsor_org`.`sponsor_org_id`";
        if (isset($_POST['reggie']) && $_POST['reggie'] != '' && isset($_POST['reggiespon']) && $_POST['reggiespon'] != '') {
            $sql .= " WHERE `username` RLIKE '$_POST[reggie]' && `org_name` RLIKE '$_POST[reggiespon]'";
        } elseif (isset($_POST['reggie']) && $_POST['reggie'] != '') {
            $sql .= " WHERE `username` RLIKE '$_POST[reggie]'";
        } elseif (isset($_POST['reggiespon']) && $_POST['reggiespon'] != '') {
            $sql .= " WHERE `org_name` RLIKE '$_POST[reggiespon]'";
        } else {
            $sql .= " WHERE 1";
        }
	$sql .= " ORDER BY `driver_changes_org`.`event_time` DESC";
        // Do the search
        $result = $db->query($sql);
        if (($nrows = mysqli_num_rows($result)) != 0) {


            echo "<tr><td>Username</td><td>Role</td><td>Sponsor Org</td><td>Change</td><td>Change Time</td></tr>";
            for ($i = 0; $i < $nrows; $i += 1) {
                $row = mysqli_fetch_array($result);
                echo "<tr><td><a href='../pages/viewaccount.php?viewid=" . $row['driver_account_id'] . "'>$row[username]</a></td><td>$row[role]</td>
		<td>$row[org_name]</td>";

                if ($row[joined_org])
                    echo "<td>Added</td>";
                else
                    echo "<td>Removed</td>";
                echo "<td>$row[event_time]</td></tr>";
            }
        }
        ?>
    </table>
</main>
<?php include('../php/footer.php'); ?>
</html>
