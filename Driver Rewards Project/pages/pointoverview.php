<?php session_start(); ?>
<!DOCTYPE html>
<html>
<?php $title = 'Point Overview';
include('../php/header.php'); ?>
<main>
    <?php
    //admin only page
    authenticate_admin($db);
    ?>

    <!-- Search bar -->
    <form name='search' method='POST' action='../pages/pointoverview.php'>
        <table>
            <tr>
                <td><label for='reggie'>Username Like:</label><input name='reggie'
                                                                     type='text' <?php if (isset($_POST['reggie'])) echo "value='$_POST[reggie]'"; ?>>
                <td>
            </tr>
            <tr>
                <td><label for='reggiespon'>Sponsor Org Like:</label><input name='reggiespon'
                                                                            type='text' <?php if (isset($_POST['reggiespon'])) echo "value='$_POST[reggiespon]'"; ?>>
                </td>
            </tr>
            <tr>
                <td><label for='authreggie'>Authorization Account Like:</label><input name='authreggie'
                                                                                      type='text' <?php if (isset($_POST['authreggie'])) echo "value='$_POST[authreggie]'"; ?>>
                    <input class='button' type='submit'></td>
            </tr>
        </table>
    </form>

    <table border='1px'>
        <?php
        // Clearable searches
        if (isset($_POST['search']) && $_POST['reggie'] == '') unset($_POST['reggie']);
        if (isset($_POST['search']) && $_POST['reggiespon'] == '') unset($_POST['reggiespon']);
        if (isset($_POST['search']) && $_POST['authreggie'] == '') unset($_POST['authreggie']);

        // Select general SQL or limited SQL based on search (if applicable)
        $sql = "SELECT * FROM `point_transactions` RIGHT OUTER JOIN `account` ON `account`.`account_id` = `point_transactions`.`driver_account_id` INNER JOIN `sponsor_org` ON `point_transactions`.`sponsor_org_id` = `sponsor_org`.`sponsor_org_id`";
        if (isset($_POST['reggie']) && $_POST['reggie'] != '' && isset($_POST['reggiespon']) && $_POST['reggiespon'] != '') {
            $sql .= " WHERE `username` RLIKE '$_POST[reggie]' && `org_name` RLIKE '$_POST[reggiespon]'";
        } elseif (isset($_POST['reggie']) && $_POST['reggie'] != '') {
            $sql .= " WHERE `username` RLIKE '$_POST[reggie]'";
        } elseif (isset($_POST['reggiespon']) && $_POST['reggiespon'] != '') {
            $sql .= " WHERE `org_name` RLIKE '$_POST[reggiespon]'";
        } else {
            $sql .= " WHERE 1";
        }
	$sql .= " ORDER BY point_transactions.change_time DESC";
        // Do the search
        $result = $db->query($sql);
        if (($nrows = mysqli_num_rows($result)) != 0) {
            echo "<tr><td>Username</td><td>Authorization Account</td><td>Authorization Role</td><td>Sponsor Org</td><td>Change amount</td><td>Change Time</td><td>Change Reason</td></tr>";
            for ($i = 0; $i < $nrows; $i += 1) {
                $row = mysqli_fetch_array($result);
                $xtemp = $row['auth_account_id'];
                $query = "SELECT `username`,`role` FROM `account` WHERE `account_id` = $xtemp";
                $xrow = $db->query($query);
                $autho = mysqli_fetch_array($xrow);
                if (isset($_POST['authreggie']) && $_POST['authreggie'] != '') {
                    if (strpos(strtolower($autho['username']), strtolower($_POST['authreggie'])) !== false) {
                        echo "<tr><td><a href='../pages/viewaccount.php?viewid=" . $row['driver_account_id'] . "'>$row[username]</a></td><td><a href='../pages/viewaccount.php?viewid=" . $row['auth_account_id'] . "'>$autho[username]</a></td><td>$autho[role]</td>
	      <td>$row[org_name]</td><td>$row[point_change_amt]</td>
	      <td>$row[change_time]</td><td>$row[change_reason]</td></tr>";
                    }
                } else {
                    echo "<tr><td><a href='../pages/viewaccount.php?viewid=" . $row['driver_account_id'] . "'>$row[username]</a></td><td><a href='../pages/viewaccount.php?viewid=" . $row['auth_account_id'] . "'>$autho[username]</a></td><td>$autho[role]</td>
		<td>$row[org_name]</td><td>$row[point_change_amt]</td>
		<td>$row[change_time]</td><td>$row[change_reason]</td></tr>";
                }
            }
        }
        ?>
    </table>
</main>
<?php include('../php/footer.php'); ?>
</html>
