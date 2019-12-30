<?php session_start(); ?>
<!DOCTYPE html>
<html>
<?php $title = 'In Progress Order Overview';
include('../php/header.php'); ?>
<main>
    <?php
    //admin only page
    authenticate_admin($db);
    ?>

    <!-- Search bar -->
    <form name='search' method='POST' action='../pages/inprogressorderoverview.php'>
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
        if (isset($_POST['searchspon']) && $_POST['reggiespon'] == '') unset($_POST['reggiespon']);
        if (isset($_POST['authreggie']) && $_POST['authreggie'] == '') unset($_POST['authreggie']);


        // Select general SQL or limited SQL based on search (if applicable)
        $sql = "SELECT * FROM `order_transactions` RIGHT OUTER JOIN `account` ON `account`.`account_id` = `order_transactions`.`driver_account_id` INNER JOIN `sponsor_org` ON `order_transactions`.`sponsor_org_id` = `sponsor_org`.`sponsor_org_id`";
        if (isset($_POST['reggie']) && $_POST['reggie'] != '' && isset($_POST['reggiespon']) && $_POST['reggiespon'] != '') {
            $sql .= " WHERE `username` RLIKE '$_POST[reggie]' && `org_name` RLIKE '$_POST[reggiespon]' && `fulfill_time` IS NULL";
        } elseif (isset($_POST['reggie']) && $_POST['reggie'] != '') {
            $sql .= " WHERE `username` RLIKE '$_POST[reggie]' && `fulfill_time` IS NULL";
        } elseif (isset($_POST['reggiespon']) && $_POST['reggiespon'] != '') {
            $sql .= " WHERE `org_name` RLIKE '$_POST[reggiespon]' && `fulfill_time` IS NULL";
        } else {
            $sql .= " WHERE `fulfill_time` IS NULL";
        }
        // Do the search
        $result = $db->query($sql);
        if (($nrows = mysqli_num_rows($result)) != 0) {
            echo "<tr><td><b>Username</b></td><td><b>Sponsor Org</b></td><td><b>Authorization Account</b></td><td><b>Creation Time</b></td><td><b>Shipping address</b></td><td><b>View Details</b></td></tr>";
            for ($i = 0; $i < $nrows; $i += 1) {
                $row = mysqli_fetch_array($result);
                $xtemp = $row['auth_account_id'];
                $query = "SELECT `username` FROM `account` WHERE `account_id` = $xtemp";
                $xrow = $db->query($query);
                $autho = mysqli_fetch_array($xrow);
                if (isset($_POST['authreggie']) && $_POST['authreggie'] != '') {
                    if (strpos(strtolower($autho['username']), strtolower($_POST['authreggie'])) !== false) {
                        echo "<tr><td><a href='../pages/viewaccount.php?viewid=" . $row['driver_account_id'] . "'>$row[username]</a></td><td>$row[org_name]</td><td><a href='../pages/viewaccount.php?viewid=" . $row['auth_account_id'] . "'>$autho[username]</a></td>
		  <td>$row[creation_time]</td><td>$row[shipping_address]</td><td><a href='../pages/order_details.php?order_id=".$row['order_id']."'>View</a></td></tr>";
                    }
                } else {
                    echo "<tr><td><a href='../pages/viewaccount.php?viewid=" . $row['driver_account_id'] . "'>$row[username]</a></td><td>$row[org_name]</td><td><a href='../pages/viewaccount.php?viewid=" . $row['auth_account_id'] . "'>$autho[username]</a></td>
		<td>$row[creation_time]</td><td>$row[shipping_address]</td><td><a href='../pages/order_details.php?order_id=".$row['order_id']."'>View</a></td></tr>";
                }
            }
        }
        ?>
    </table>
</main>
<?php include('../php/footer.php'); ?>
</html>
