<!DOCTYPE HTML>
<?php
session_start();
include_once('../php/sqlconnect.php');
// Update own account information if necessary
$updateError = 0;
if (isset($_POST['update'])) {
    // Username and Email still have to remain unique
    if (mysqli_num_rows($db->query("SELECT username FROM account WHERE username='$_POST[user]' AND account_id != $_POST[usertarget]")) ||
        mysqli_num_rows($db->query("SELECT email_address FROM account WHERE email_address='$_POST[email]' AND account_id != $_POST[usertarget]")) ||
        !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $updateError = 1;
    } else { // go ahead and do the update
        $db->query("UPDATE account SET username='$_POST[user]', email_address='$_POST[email]', shipping_address='$_POST[ship]' WHERE account_id=$_POST[usertarget]");
        $login_name = "$_POST[user]";
    }
}
?>

<?php $title = 'My Profile';
include('../php/header.php'); ?>
<main>

    <?php
    if ($updateError) {
        echo "<p class='error'>Unable to complete that update (make sure username and email are unique and email is valid)</p>";
    }
    // Build page contents
    if (isset($_SESSION['user_id'])) {
        echo "<h3>Information</h3>";
        $id = $_SESSION['user_id'];
        $query = "SELECT `account_id`, `username`, `email_address`, `shipping_address`, `role`  FROM `account` WHERE $id = account_id";
        $result = $db->query($query);
        if (!$result || mysqli_num_rows($result) == 0) {
            echo("<p class=\"errorText\">No Matching User Data</p>");
        } else {
            $result_row = mysqli_fetch_array($result);
            $user_id = $result_row['account_id'];
            $name = $result_row['username'];
            $email = $result_row['email_address'];
            $shipping_addr = $result_row['shipping_address'];
            $role_user = $result_row['role'];
            ?>
            Account Information
            <form name='updateAcct' method='POST' action='../pages/profile.php'>
                <input type='hidden' name='usertarget' value='<?php echo "$user_id"; ?>'>
                <table class='tabledesign'>
                    <tr>
                        <td>Username</td>
                        <td><input type='text' name='user' value='<?php echo "$name"; ?>'></td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td><input type='text' name='email' value='<?php echo "$email"; ?>'></td>
                    </tr>
                    <tr>
                        <td>Shipping Address</td>
                        <td><input type='text' name='ship' value='<?php echo "$shipping_addr"; ?>'></td>
                    </tr>
                    <tr>
                        <td colspan='2'><input class='button' type='submit' name='update'
                                               value='Update Account Information'></td>
                    </tr>
                </table>
            </form><br>
            <a class='button' href='../pages/password_reset.php'>Change My Password</a>
            <?php
            //for driver code
            if ("driver" == $role_user) {
                echo "<h3>Sponsors</h3>";
                $query = "SELECT `sponsor_org`.`org_name`, `sponsor_org`.`sponsor_org_id` FROM `sponsor_org` INNER JOIN `driver_in_org` ON `sponsor_org`.`sponsor_org_id` = `driver_in_org`.`sponsor_org_id` && `driver_in_org`.`driver_account_id` = $id && `sponsor_org`.`live_org` = 1";
                $result = $db->query($query);
                if (!$result || mysqli_num_rows($result) == 0) {
                    echo("<p>No sponsors linked with account</p>");
                } else {
                    $return_string = "<table border =\"1\"><tr><td>Organzation</td><td>Points</td><td>Delete Number</td></tr>";
                    $count = 0;
                    while ($count != mysqli_num_rows($result)) {
                        for ($i = 0; $i < 2 && $result_row = mysqli_fetch_array($result); $i = $i + 1) {
                            $return_string .= "<tr valign=\"top\" style=\"padding-top: 25px; padding-bottom: 25px;\">";
                            $count++;
                            $org_name = $result_row['org_name'];
                            $org_numb = $result_row['sponsor_org_id'];

                            ///////IMPORTANT: NEEDS A PAGE FOR SPONSOR ORG PROFILE
                            $return_string .= "<td><a href='../pages/view_org.php?view_org=" . $org_numb . "'>$org_name</a></td>";

                            $xquery = "SELECT SUM(point_change_amt) FROM point_transactions WHERE driver_account_id = $id AND sponsor_org_id = $org_numb";
                            $xresult = $db->query($xquery);
                            if (!$xresult || mysqli_num_rows($xresult) == 0) {
                                $return_string .= "<td>0</td>";
                            } else {
                                $xresult_row = mysqli_fetch_array($xresult);
                                $points = "$xresult_row[0]";
                                $return_string .= "<td>$points</td>";
                            }
                            $return_string .= "<td>$org_numb</td>";

                            $return_string .= "</tr>";
                        }
                    }
                    $return_string .= "</table><br/>";
                    echo "$return_string";

                    echo "<hr><h3>Leave a sponsor</h3><p>To unlink from a sponsor, type the number from above in the box<p>This does nothing if you are not in the sponsor organization typed.<p><form method = 'post' action = " . htmlspecialchars($_SERVER['PHP_SELF']) . "><div><input style='float:left;' type='text' name='UnlinkSponsor' maxlength='50'><input class='button' name='submitsponsor' type='submit' value='Remove Sponsor'></div></form><hr>";
                }
                // Email preferences
                if (isset($_POST['alertUpdate'])) {
                    $sql = "UPDATE email_preferences SET order_problem = $_POST[problem], order_placed = $_POST[order], points_changed = $_POST[points] WHERE driver_account_id = $id";
                    if (!$db->query($sql)) echo "Could not update your email preferences at this time";
                }

                echo "<h3>Email Preferences</h3>";
                $sql = "SELECT * FROM email_preferences WHERE driver_account_id = $id";
                if (!($result = $db->query($sql))) echo "Could not obtain email preferences at this time!";
                else {
                    echo "\n<table border=1px><tr><td>Alert on Order Problem</td><td>Alert when Order Placed</td><td>Alert when Points Change</td><td>Update Alerts</td></tr>";
                    $row = $result->fetch_assoc();
                    echo "<form name=adjustEmail method=POST action='../pages/profile.php'>";
                    echo "\n<td><select name='problem'><option value=1";
                    if (!is_null($row['order_problem'])) echo " selected ";
                    echo ">YES</option><option value='NULL'";
                    if (is_null($row['order_problem'])) echo " selected ";
                    echo ">NO</option></select></td>";
                    echo "\n<td><select name='order'><option value=1";
                    if (!is_null($row['order_placed'])) echo " selected ";
                    echo ">YES</option><option value='NULL'";
                    if (is_null($row['order_placed'])) echo " selected ";
                    echo ">NO</option></select></td>";
                    echo "\n<td><select name='points'><option value=1";
                    if (!is_null($row['points_changed'])) echo " selected ";
                    echo ">YES</option><option value='NULL'";
                    if (is_null($row['points_changed'])) echo " selected ";
                    echo ">NO</option></select></td>";
                    echo "\n<td><input name='alertUpdate' type=submit class=button value='Update'></td></form>";
                    echo "</table>";
                }
                echo "<hr>";
            } else {
                //Sponsor stuff
                if ("sponsor" == $role_user) {
                    echo "<hr><h3>Drivers</h3>";
                    $query = "SELECT `sponsor_org_id` FROM `driver_in_org` WHERE `driver_account_id` = $id";
                    $result = $db->query($query);
                    if (!$result || mysqli_num_rows($result) == 0) {
                        echo("<p>No connected sponsor org</p>");
                    } else {
                        $count = 0;
                        while ($count != mysqli_num_rows($result)) {
                            for ($i = 0; $i < 1 && $result_row = mysqli_fetch_array($result); $i = $i + 1) {
                                $count++;
                                $spon_org_id = $result_row['sponsor_org_id'];
                            }
                        }
                        $query = "SELECT `account`.`username`, `account`.`account_id`, `account`.`role` FROM `account` INNER JOIN `driver_in_org` ON `account`.`account_id` = `driver_in_org`.`driver_account_id` WHERE `driver_in_org`.`sponsor_org_id` = $spon_org_id && `account`.`role` = 'driver'";

                        $result = $db->query($query);
                        if (!$result || mysqli_num_rows($result) == 0) {
                            echo("<p>No drivers</p>");
                        } else {
                            $return_string = "<table border =\"1\"><tr><td>Driver</td><td>Points</td><td>Reason</td><td>Add/Remove Points</td></tr>";
                            $count = 0;
                            while ($count != mysqli_num_rows($result)) {
                                for ($i = 0; $i < 3 && $result_row = mysqli_fetch_array($result); $i = $i + 1) {
                                    if ("driver" == $result_row['role']) {
                                        $return_string .= "<tr valign=\"top\" style=\"padding-top: 25px; padding-bottom: 25px;\">";
                                        $count++;
                                        $drivers = $result_row['username'];
                                        $driver_id = $result_row['account_id'];
                                        $return_string .= "<td><a href='../pages/viewaccount.php?viewid=" . $driver_id . "'>$drivers</a></td>";

                                        $xquery = "SELECT SUM(point_change_amt) FROM point_transactions WHERE driver_account_id = $driver_id AND sponsor_org_id = $spon_org_id";
                                        $xresult = $db->query($xquery);
                                        if (!$xresult || mysqli_num_rows($xresult) == 0) {
                                            $return_string .= "<td>0</td><td>";
                                        } else {
                                            $xresult_row = mysqli_fetch_array($xresult);
                                            $points = "$xresult_row[0]";
                                            $return_string .= "<td>$points</td><td>";
                                        }
                                        echo "$return_string";
                                        ?>
                                        <form name="points" method="POST" action="../pages/profile.php">
                                            <input type='hidden' name='usertarget' value='<?php echo $driver_id; ?>'>
                                            <input type='text' name='reason'
                                                   placeholder='Optional reason for change'></td>
                                            <td>
                                                <input type='number' name='pointval' required>
                                                <input class='button' type='submit' name='pointbutt'
                                                       value='Confirm change'>
                                        </form>
                                        <?php
                                        $return_string = "</td></tr>";
                                    }
                                }
                            }
                            $return_string .= "</table><br/>";
                            echo "$return_string";
                        }
                        $query = "SELECT `points_to_dollars` FROM `sponsor_org` WHERE `sponsor_org_id` = $spon_org_id";
                        $res = $db->query($query);
                        $res_row = mysqli_fetch_array($res);
                        $ratio = $res_row['points_to_dollars'];
                        ?>
                        Points-to-Dollars ratio
                        <form name='pointratio' method='POST' action='../pages/profile.php'>
                            <table class='tabledesign'>
                                <tr>
                                    <td><input type='number' name='newratio' min='.01' step='.01'
                                               value='<?php echo "$ratio"; ?>'>points to equal one(1) dollar
                                    </td>
                                </tr>
                                <tr>
                                    <td><input class='button' type='submit' name='pointsubmit' value='Change ratio'>
                                    </td>
                                </tr>
                            </table>
                        </form><br>


                        <?php
                    }
                } else {
                    //admins
                    if ("admin" == $role_user) {

                    } else {
                        echo "Undefined user role.";
                    }
                }
            }
            echo "<h3>Delete account</h3><p>Type 'delete' to confirm account deletion<p><form method = 'post' action = " . htmlspecialchars($_SERVER['PHP_SELF']) . "><div><input style='float:left;' type='text' name='Confirm' maxlength='10'><input class = 'button' name='submitdelete' type='submit' value='Delete Account'></div></form>";
        }
    } else {
        echo "You are not logged in.";
    }
    ?>

    <?php
    //remove account $id

    if (isset($_POST['submitdelete']) && isset($_POST['Confirm'])) {
        if ("delete" == $_POST['Confirm']) {
            $query = "UPDATE `account` SET `live_account`= 2 WHERE account_id = $id";
            $db->query($query);
            unset($_SESSION['user_id']);
            header('Location: home.php');

        }
    } //unlink from sponsor
    elseif (isset($_POST['UnlinkSponsor']) && isset($_POST['submitsponsor'])) {
        $id = $_SESSION['user_id'];
        $org = $_POST['UnlinkSponsor'];
        $query = "DELETE FROM `driver_in_org` WHERE `driver_in_org`.`sponsor_org_id` = $org && `driver_in_org`.`driver_account_id` = $id";
        $db->query($query);
        header('Location: profile.php');
    } //edit points
    elseif (isset($_POST['pointval']) && isset($_POST['pointbutt'])) {
        $pointval = $_POST['pointval'];
        $usertarget = $_POST['usertarget'];
        if (isset($_POST['reason'])) {
            $reason = $_POST['reason'];
            $sql = "INSERT INTO `point_transactions`(`driver_account_id`, `auth_account_id`, `sponsor_org_id`, `point_change_amt`, `change_reason`) VALUES ('$usertarget', '$id', '$spon_org_id', '$pointval', '$reason')";
        } else {
            $sql = "INSERT INTO `point_transactions`(`driver_account_id`, `auth_account_id`, `sponsor_org_id`, `point_change_amt`) VALUES ('$usertarget', '$id', '$spon_org_id', '$pointval')";
        }
        $db->query($sql);
        // Notification
        $sql = "SELECT points_changed FROM email_preferences WHERE driver_account_id = $usertarget AND points_changed IS NOT NULL";
        $result = $db->query($sql);
        if (mysqli_num_rows($result) != 0) {
            $sql = "SELECT org_name FROM sponsor_org WHERE sponsor_org_id = $spon_org_id";
            $result = $db->query($sql);
            $orgName = $result->fetch_assoc();
            $orgName = $orgName['org_name'];
            $sql = "SELECT username, email_address FROM account WHERE account_id = $usertarget";
            $result = $db->query($sql);
            $row = $result->fetch_assoc();
            $subj = 'You earned Wholesale Crocodile Points!';
            $headers = 'From: WholesaleCrocodile@gmail.com' . "\r\n" . "Reply-To:WholesaleCrocodile@gmail.com" . "\r\n" . "X-Mailer: PHP/" . phpversion();
            // Compose message
            $mess = "Dear $row[username],\r\n\r\nYou have received $pointval points from a sponsor at $orgName! Congratulations!";
            if (isset($_POST['reason'])) $mess .= "\r\nThe sponsor wrote this message: $reason";
            // Fix long lines in case they exist
            $message = wordwrap($mess, 70, "\r\n");
            mail($row['email_address'], $subj, $message, $headers, '-f WholesaleCrocodile@gmail.com');
        }
        header('Location: profile.php');
    } //edit point to dollar ratio
    elseif (isset($_POST['pointsubmit'])) {
        $newPRatio = $_POST['newratio'];
        $sql = "UPDATE `sponsor_org` SET `points_to_dollars` = $newPRatio WHERE $spon_org_id = `sponsor_org_id`";
        $db->query($sql);
        header('Location:profile.php');
    }
    ?>

</main>
<?php include('../php/footer.php'); ?>
</html>
