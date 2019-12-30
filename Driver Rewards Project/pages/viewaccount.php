<?php session_start(); ?>
<!DOCTYPE html>
<html>
<?php $title = 'View Account';
include('../php/header.php'); ?>
<main>
    <?php

    if (isset($_GET['viewid']) || isset($_SESSION["viewingid"])) {
        if (isset($_GET['viewid'])) {
            $viewaccount = $_GET['viewid'];
            $_SESSION["viewingid"] = $viewaccount;
        } else {
            $viewaccount = $_SESSION["viewingid"];
            unset($_SESSION["viewingid"]);
        }
        if (isset($role)) {
            $role_viewing = $role;
            if ($role_viewing == "admin") {
                echo "<h1>Viewing Profiles</h1><p>";
                $query = "SELECT `account_id`, `username`, `email_address`, `shipping_address`, `role`  FROM `account` WHERE $viewaccount = account_id";
                $result = $db->query($query);
                if (!$result || mysqli_num_rows($result) == 0) {
                    echo("<p class=\"errorText\">No Matching User Data</p>");
                } else {
                    $return_string = "<table border =\"1\">";
                    $result_row = mysqli_fetch_array($result);
                    $user_id = $result_row['account_id'];
                    $name = $result_row['username'];
                    $email = $result_row['email_address'];
                    $shipping_addr = $result_row['shipping_address'];
                    $role_user = $result_row['role'];
                    $return_string .= "$role_user Information<br><td>Username: $name</td><td>Email: $email</td><td>Shipping Address: $shipping_addr</td>";
                    $return_string .= "</tr></table><br/>";
                    echo "$return_string";

                    //viewing driver as admin code
                    if ("driver" == $role_user) {
                        echo "<hr><h3>Orders</h3><a href='../pages/orders.php?viewid=" . $viewaccount . "'>View orders</a><p><a href = '../pages/phistory.php?viewid=" . $viewaccount . "'>View point history</a>";
                        echo "<h3>Sponsors</h3>";
                        $query = "SELECT `sponsor_org`.`org_name`, `sponsor_org`.`sponsor_org_id` FROM `sponsor_org` INNER JOIN `driver_in_org` ON `sponsor_org`.`sponsor_org_id` = `driver_in_org`.`sponsor_org_id` && `driver_in_org`.`driver_account_id` = $viewaccount && `sponsor_org`.`live_org` = 1";
                        $result = $db->query($query);
                        if (!$result || mysqli_num_rows($result) == 0) {
                            echo("<p>No sponsors linked with account</p>");
                        } else {
                            $return_string = "<table border =\"1\">";
                            $return_string .= "<tr><td>Organization</td><td>Points</td><td>Delete Number</td></tr>";
                            $count = 0;
                            while ($count != mysqli_num_rows($result)) {
                                for ($i = 0; $i < 2 && $result_row = mysqli_fetch_array($result); $i = $i + 1) {
                                    $return_string .= "<tr valign=\"top\" style=\"padding-top: 25px; padding-bottom: 25px;\">";
                                    $count++;
                                    $org_name = $result_row['org_name'];
                                    $org_numb = $result_row['sponsor_org_id'];
                                    $return_string .= "<td><a href='../pages/view_org.php?view_org=" . $org_numb . "'>$org_name</a></td>";

                                    $xquery = "SELECT SUM(point_change_amt) FROM point_transactions WHERE driver_account_id = $viewaccount AND sponsor_org_id = $org_numb";
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

                            echo "<hr><h3>Leave a sponsor</h3><p>To unlink from a sponsor, type the number from above in the box<p>This does nothing if you are not in the sponsor organization typed.<p><form method = 'post'><div><input style='float:left;' type='text' name='UnlinkSponsor' maxlength='50'><input name='submitsponsor' type='submit' value='Remove Sponsor'></div></form><hr>";
                        }
                    } else {
                        //viewing sponsor as admin
                        if ("sponsor" == $role_user) {
                            echo "<hr><h3>Drivers</h3>";
                            $query = "SELECT `sponsor_org_id` FROM `driver_in_org` WHERE `driver_account_id` = $viewaccount";
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
                                                <form name="points" method="POST" action="../pages/viewaccount.php">
                                                    <input type='hidden' name='usertarget'
                                                           value='<?php echo $driver_id; ?>'>
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
                            }
                            $query = "SELECT `points_to_dollars` FROM `sponsor_org` WHERE `sponsor_org_id` = $spon_org_id";
                            $res = $db->query($query);
                            $res_row = mysqli_fetch_array($res);
                            $ratio = $res_row['points_to_dollars'];
                            ?>
                            Points-to-Dollars ratio
                            <form name='pointratio' method='POST' action='../pages/viewaccount.php'>
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
                        } else {
                            if ("admin" == $role_user) {
                                echo "<hr><h3><a href='../pages/userlist.php'>Users</a></h3><p>";
                            } else {
                                echo "Undefined user role.";
                            }
                        }
                    }
					if(isset($_POST['undelete'])){
		$query = "UPDATE `DriverRewards4910`.`account` SET `account`.`live_account` = 1 WHERE `account`.`account_id` = $viewaccount";
		$db->query($query);
		header("Location: userlist.php");
	}
    elseif (isset($_POST['submitdelete']) && isset($_POST['Confirm'])) {
        if ("delete" == $_POST['Confirm']) {
            $query = "UPDATE `DriverRewards4910`.`account` SET `account`.`live_account`= 2 WHERE `account`.`account_id` = $viewaccount";
            $db->query($query);
            if ($viewaccount == $_SESSION['user_id']) {
                unset($_SESSION['user_id']);
                header("Location: home.php");
            }
            header("Location: userlist.php");
        }
    } 

                    echo "<h3>Delete account</h3><p>Type 'delete' to confirm account deletion<p><form method = 'post' action = " . htmlspecialchars($_SERVER['PHP_SELF']) . "><div><input style='float:left;' type='text' name='Confirm' maxlength='10'><input name='submitdelete' type='submit' value='Delete Account'></div></form>";
                    echo "<form method = 'post' action = " . htmlspecialchars($_SERVER['PHP_SELF']) . "><div><input name='undelete' type='submit' value='Un-delete'></div></form>";
                }

                //viewing as sponsor
            } elseif ($role_viewing == "sponsor") {
                $query = "SELECT `sponsor_org_id` FROM `driver_in_org` WHERE `driver_account_id` = $id";
                $result = $db->query($query);

                $result_row = mysqli_fetch_array($result);
                $my_org = $result_row['sponsor_org_id'];
                $query = "SELECT * FROM `driver_in_org` WHERE `sponsor_org_id` = $my_org && `driver_account_id` = $viewaccount";
                $result = $db->query($query);
                if (!$result || mysqli_num_rows($result) == 0) {
                    echo("<p class=\"errorText\">That driver is not in your organization.</p>");
                } //driver is in sponsor's org
                else {
                    $query = "SELECT `account_id`, `username`, `email_address`, `role`, `shipping_address` FROM `account` WHERE `account_id` = $viewaccount";
                    $result = $db->query($query);
                    if (!$result || mysqli_num_rows($result) == 0) {
                        echo("Something went wrong.");
                    } else {
                        echo "<h1>Viewing Profiles</h1><p>";
                        $return_string = "<table border =\"1\">";
                        $result_row = mysqli_fetch_array($result);
                        $user_id = $result_row['account_id'];
                        $name = $result_row['username'];
                        $email = $result_row['email_address'];
                        $shipping_addr = $result_row['shipping_address'];
                        $role_user = $result_row['role'];
                        $return_string .= "$role_user Information<br><td>Username: $name</td><td>Email: $email</td><td>Shipping Address: $shipping_addr</td>";
                        $return_string .= "</tr></table><br/>";
                        echo "$return_string";
                    }
                    echo "<hr><h3>Orders</h3><a href='../pages/orders.php?viewid=" . $viewaccount . "'>View orders</a><p><a href = '../pages/phistory.php?viewid=" . $viewaccount . "'>View point history</a>";
                    //can prob add remove from the sponsor org here or something or in the sponsor page
                    ?>
                    <form name='removal' method='POST' action='../pages/viewaccount.php'>
                        <input class='button' type='submit' name='Removal' value='Remove <?php if($role_user == 'driver') echo "Driver From Org"; else echo "Sponsor From Org"; ?>'>
                    </form>
                    <hr>
                    <?php
                    if (isset($_POST['Removal'])) {
                        $query = "DELETE FROM `driver_in_org` WHERE `driver_account_id` = $viewaccount && `sponsor_org_id` = $my_org";
                        $db->query($query);
                        unset($_SESSION["viewingid"]);

                        // Notification
                        $sql = "SELECT org_name FROM sponsor_org WHERE sponsor_org_id = $my_org";
                        $result = $db->query($sql);
                        $orgName = $result->fetch_assoc();
                        $orgName = $orgName['org_name'];
                        $sql = "SELECT username, email_address FROM account WHERE account_id = $viewaccount";
                        $result = $db->query($sql);
                        $row = $result->fetch_assoc();
                        $subj = "You have been removed from $orgName";
                        $headers = 'From: WholesaleCrocodile@gmail.com' . "\r\n" . "Reply-To:WholesaleCrocodile@gmail.com" . "\r\n" . "X-Mailer: PHP/" . phpversion();
                        // Compose message
                        $mess = "Dear $row[username],\r\n\r\nYou have been removed from the $orgName organization on Wholesale Crocodile Driver Rewards.";
                        // Fix long lines in case they exist
                        $message = wordwrap($mess, 70, "\r\n");
                        mail($row['email_address'], $subj, $message, $headers, '-f WholesaleCrocodile@gmail.com');
                        header('Location: profile.php');
                    }
                }
            } else {
                echo "You lack permissions to view this page";
            }
        }
    } else {
        echo "You didn't select a user. Probably because you entered the page manually.";
    }
    ?>

    <?php
    //remove account $id

	if (isset($_POST['UnlinkSponsor']) && isset($_POST['submitsponsor'])) {
        //unlink from sponsor
        $org = $_POST['UnlinkSponsor'];
        $query = "DELETE FROM `driver_in_org` WHERE `driver_in_org`.`sponsor_org_id` = $org && `driver_in_org`.`driver_account_id` = $viewaccount";
        $db->query($query);
        $_SESSION["viewingid"] = $viewaccount;
        header("Location: viewaccount.php?viewid=" . $_SESSION["viewingid"]);
    }//edit points
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

        $_SESSION["viewingid"] = $viewaccount;
        header("Location: viewaccount.php?viewid=" . $_SESSION["viewingid"]);
    } //edit point to dollar ratio
    elseif (isset($_POST['pointsubmit'])) {
        $newPRatio = $_POST['newratio'];
        $sql = "UPDATE `sponsor_org` SET `points_to_dollars` = $newPRatio WHERE $spon_org_id = `sponsor_org_id`";
        $db->query($sql);
        $_SESSION["viewingid"] = $viewaccount;
        #header("Location: viewaccount.php?viewid=".$_SESSION["viewingid"]);

    }
    //need something for sponsor to remove driver
    ?>
</main>
<?php include('../php/footer.php'); ?>
</html>
