<?php session_start(); ?>
<!DOCTYPE html>
<html>
<?php $title = 'View Org';
include('../php/header.php'); ?>
<main>
    <?php
    if (isset($_SESSION["vieworgid"])) {
        $view_org = $_SESSION["vieworgid"];
        //unset($_SESSION["vieworgid"]);
    } elseif (isset($_GET['view_org']) && isset($role)) {
        $temp = $_GET['view_org'];
        if ($role != "admin") {
            $query = "SELECT * FROM `driver_in_org` WHERE `driver_account_id` = $id && `sponsor_org_id` = $temp";
            $result = $db->query($query);
            if (!$result || mysqli_num_rows($result) == 0) {
                echo "You have no connected sponsor org";
            } else {
                $result_row = mysqli_fetch_array($result);
                $view_org = $result_row['sponsor_org_id'];
            }
        } else {
            $view_org = $temp;
            $_SESSION["vieworgid"] = $view_org;
        }
    } elseif (isset($role) && $role == "sponsor") {
        $query = "SELECT * FROM `driver_in_org` WHERE `driver_account_id` = $id";
        $result = $db->query($query);
        if (!$result || mysqli_num_rows($result) == 0) {
            echo "You have no connected sponsor org";
        } else {
            $result_row = mysqli_fetch_array($result);
            $view_org = $result_row['sponsor_org_id'];
        }
    }
    if (isset($view_org)) {
        if (isset($role)) {
            if ($role == "admin") {
                $_SESSION["sponsor_identifier"] = $view_org;
                $query = "SELECT sponsor_org.*, event_time as creation_time FROM `sponsor_org` INNER JOIN sponsor_org_creation_deletion ON sponsor_org.sponsor_org_id = sponsor_org_creation_deletion.sponsor_org_id WHERE sponsor_org.`sponsor_org_id` = $view_org";
                $result = $db->query($query);
                if (!$result || mysqli_num_rows($result) == 0) {
                    echo("<p class=\"errorText\">Not an organization in database</p>");
                } else {
                    $result_row = mysqli_fetch_array($result);
                    $name = $result_row['org_name'];
                    $email = $result_row['org_email_address'];
                    $bio = $result_row['org_bio'];
                    $url = $result_row['org_url'];
                    $created = $result_row['creation_time'];
                    $live = $result_row['live_org'];
                    echo "<h1>$name</h1><hr>";
                    if ($live == 1) echo "Active organization";
                    else echo "Dead organization";
                    echo "<br>Email: " . $email . "<br>Website: " . $url . "<br>Created: " . $created . "<h2>Description</h2>" . $bio . "<br>";
                    echo "<h2>Drivers</h2>\n";

                    $query = "SELECT `account`.`username`, `account`.`account_id`, `account`.`role` FROM `account` INNER JOIN `driver_in_org` ON `account`.`account_id` = `driver_in_org`.`driver_account_id` WHERE `driver_in_org`.`sponsor_org_id` = $view_org && `account`.`role` = 'driver'";

                    $result = $db->query($query);
                    if (!$result || mysqli_num_rows($result) == 0) {
                        echo("<p>No drivers</p>");
                    } else {
                        $return_string = "<table border =\"1\"><tr><td>Driver</td><td>Points</td>";
			$q2 = "SELECT live_org FROM sponsor_org WHERE sponsor_org_id = $view_org";
			$q2r = $db->query($q2);
			$q2row = $q2r->fetch_assoc();
			if($q2row['live_org'] == 1) $return_string .= "<td colspan=3>Add or Remove Points</td>";
			echo $return_string."</tr>";
                        $count = 0;
			if (isset($_POST['pointsubmit'])) {
                        $newPRatio = $_POST['newratio'];
                        $sql = "UPDATE `sponsor_org` SET `points_to_dollars` = $newPRatio WHERE $view_org = `sponsor_org_id`";
                        $db->query($sql);
                        $_SESSION["vieworgid"] = $view_org;
                        redirect("Location: view_org.php?view_org=" . $_SESSION["vieworgid"]);
                    }
//code to update points
                        if (isset($_POST['pointval']) && isset($_POST['pointbutt'])) {
                            $pointval = $_POST['pointval'];
                            $usertarget = $_POST['usertarget'];
                            if (isset($_POST['reason'])) {
                                $reason = $_POST['reason'];
                                $sql = "INSERT INTO `point_transactions`(`driver_account_id`, `auth_account_id`, `sponsor_org_id`, `point_change_amt`, `change_reason`) VALUES ('$usertarget', '$id', '$view_org', '$pointval', '$reason')";
                            } else {
                                $sql = "INSERT INTO `point_transactions`(`driver_account_id`, `auth_account_id`, `sponsor_org_id`, `point_change_amt`) VALUES ('$usertarget', '$id', '$view_org', '$pointval')";
                            }
                            $db->query($sql);
                            // Notification
                            $sql = "SELECT points_changed FROM email_preferences WHERE driver_account_id = $usertarget && points_changed IS NOT NULL";
                            $result = $db->query($sql);
                            if (mysqli_num_rows($result) != 0) {
				$orgName = $name;
                                $sql = "SELECT username, email_address FROM account WHERE account_id = $usertarget";
                                $result = $db->query($sql);
                                $row = $result->fetch_assoc();
                                $subj = 'You earned Wholesale Crocodile Points!';
                                $headers = 'From: WholesaleCrocodile@gmail.com' . "\r\n" . "Reply-To:WholesaleCrocodile@gmail.com" . "\r\n" . "X-Mailer: PHP/" . phpversion();
                                // Compose message
                                $mess = "Dear $row[username],\r\n\r\nYou have received $pointval points from an admin for $orgName! Congratulations!";
                                if (isset($_POST['reason'])) $mess .= "\r\nThe sponsor wrote this message: $reason";
                                // Fix long lines in case they exist
                                $message = wordwrap($mess, 70, "\r\n");
                                mail($row['email_address'], $subj, $message, $headers, '-f WholesaleCrocodile@gmail.com');
                                #echo "$message";
                            }
                            $_SESSION["vieworgid"] = $view_org;
                            redirect("../pages/view_org.php?view_org=$_SESSION[vieworgid]");
                        }
                        while ($count != mysqli_num_rows($result)) {
                            for ($i = 0; $i < 3 && $result_row = mysqli_fetch_array($result); $i = $i + 1) {
                                if ("driver" == $result_row['role']) {
                                    $return_string = "<tr>";
                                    $count++;
                                    $drivers = $result_row['username'];
                                    $driver_id = $result_row['account_id'];
                                    $return_string .= "<td><a href='../pages/viewaccount.php?viewid=" . $driver_id . "'>$drivers</a></td>";

                                    $xquery = "SELECT SUM(point_change_amt) FROM point_transactions WHERE driver_account_id = $driver_id AND sponsor_org_id = $view_org";
                                    $xresult = $db->query($xquery);
                                    if (!$xresult || mysqli_num_rows($xresult) == 0) {
                                        $return_string .= "<td>!error</td><td colspan=3>";
                                    } else {
                                        $xresult_row = mysqli_fetch_array($xresult);
                                        $points = "$xresult_row[0]";
					if($points == NULL) $points = 0;
                                        $return_string .= "<td>$points</td>";
                                    }
                                    echo $return_string."\n";
				if($q2row['live_org'] == 1) {
				echo "<td colspan=3>";
                                    ?>
				<form name="points" method="POST" action="../pages/view_org.php">
				<input type='hidden' name='usertarget' value='<?php echo $driver_id; ?>'>
				<table><tr>
					<td><input type='text' name='reason' placeholder='Optional reason for change'></td>
					<td colspan=2>
					<input type='number' name='pointval' required>
					<input class='button' type='submit' name='pointbutt' value='Confirm change'>
					</td>
					</tr></table></form>
<?php
                                    $return_string = "</td></tr>\n";
                                }
				}
                            }
                        }
                        echo "</table><br/>";
                    }
                    $query = "SELECT `points_to_dollars` FROM `sponsor_org` WHERE `sponsor_org_id` = $view_org";
                    $res = $db->query($query);
                    $res_row = mysqli_fetch_array($res);
                    $ratio = $res_row['points_to_dollars'];

                    //edit point to dollar ratio

                    ?>
                    Points-to-Dollars ratio
                    <form name='pointratio' method='POST' action='../pages/view_org.php'>
                        <table class='tabledesign'>
                            <tr>
                                <td><input type='number' name='newratio' min='.01' step='.01'
                                           value='<?php echo "$ratio"; ?>'>points to equal one(1) dollar
                                </td>
                            </tr>
                            <tr>
                                <td><input class='button' type='submit' name='pointsubmit' value='Change ratio'></td>
                            </tr>
                        </table>
                    </form><br>
                    <?php
                }

                echo "<h2><a href='../pages/catalog.php'>Catalog</a></h2>";
            } elseif ($role == "driver") {
                $_SESSION["sponsor_identifier"] = $view_org;
                $query = "SELECT * FROM `sponsor_org` WHERE `sponsor_org_id` = $view_org";
                $result = $db->query($query);
                if (!$result || mysqli_num_rows($result) == 0) {
                    echo("<p class=\"errorText\">Not an organization in database</p>");
                } else {
                    $result_row = mysqli_fetch_array($result);
                    $name = $result_row['org_name'];
                    $email = $result_row['org_email_address'];
                    $bio = $result_row['org_bio'];
                    $url = $result_row['org_url'];
                    echo "<h1>$name</h1><hr>";
                    echo "<br>Email: " . $email . "<br>Website: " . $url . "<h2>Description</h2>" . $bio . "<br>";
                    echo "<h2><a href='../pages/catalog.php'>Catalog</a></h2>";
                }
            } elseif ($role == "sponsor") {
                $_SESSION["sponsor_identifier"] = $view_org;
                $query = "SELECT sponsor_org.*, event_time as creation_time FROM `sponsor_org` INNER JOIN sponsor_org_creation_deletion ON sponsor_org.sponsor_org_id = sponsor_org_creation_deletion.sponsor_org_id WHERE sponsor_org.`sponsor_org_id` = $view_org";
                $result = $db->query($query);
                if (!$result || mysqli_num_rows($result) == 0) {
                    echo("<p class=\"errorText\">Not an organization in database</p>");
                } else {
                    $result_row = mysqli_fetch_array($result);
                    $name = $result_row['org_name'];
                    $email = $result_row['org_email_address'];
                    $bio = $result_row['org_bio'];
                    $url = $result_row['org_url'];
                    $created = $result_row['creation_time'];
                    $live = $result_row['live_org'];
                    echo "<h1>$name</h1><hr>";
                    if ($live == 1) {
                        echo "Active organization";
                    } else
                        echo "Dead organization";
                    echo "<br>Email: " . $email . "<br>Website: " . $url . "<br>Created: " . $created . "<h2>Description</h2>" . $bio . "<br>";

                    echo "<h2>Drivers</h2>\n";

                    $query = "SELECT `account`.`username`, `account`.`account_id`, `account`.`role` FROM `account` INNER JOIN `driver_in_org` ON `account`.`account_id` = `driver_in_org`.`driver_account_id` WHERE `driver_in_org`.`sponsor_org_id` = $view_org && `account`.`role` = 'driver'";

                    $result = $db->query($query);
                    if (!$result || mysqli_num_rows($result) == 0) {
                        echo("<p>No drivers</p>");
                    } else {
                        echo "<table border =\"1\"><tr><td>Driver</td><td>Points</td>";
			$q2 = "SELECT live_org FROM sponsor_org WHERE sponsor_org_id = $view_org";
			$q2r = $db->query($q2);
			$q2row = $q2r->fetch_assoc();
			if($q2row['live_org'] == 1) echo "<td colspan=3>Add or Remove Points</td>";
			echo "</tr>";
                        $count = 0;
//code to update points
                        if (isset($_POST['pointval']) && isset($_POST['pointbutt'])) {
                            $pointval = $_POST['pointval'];
                            $usertarget = $_POST['usertarget'];
                            if (isset($_POST['reason'])) {
                                $reason = $_POST['reason'];
                                $sql = "INSERT INTO `point_transactions`(`driver_account_id`, `auth_account_id`, `sponsor_org_id`, `point_change_amt`, `change_reason`) VALUES ('$usertarget', '$id', '$view_org', '$pointval', '$reason')";
                            } else {
                                $sql = "INSERT INTO `point_transactions`(`driver_account_id`, `auth_account_id`, `sponsor_org_id`, `point_change_amt`) VALUES ('$usertarget', '$id', '$view_org', '$pointval')";
                            }
                            $db->query($sql);
                            // Notification
                            $sql = "SELECT points_changed FROM email_preferences WHERE driver_account_id = $usertarget AND points_changed IS NOT NULL";
                            $result = $db->query($sql);
                            if (mysqli_num_rows($result) != 0) {
                                $sql = "SELECT org_name FROM sponsor_org WHERE sponsor_org_id = $view_org";
                                $result = $db->query($sql);
                                $orgName = $result->fetch_assoc();
                                $orgName = $orgName['org_name'];
                                $sql = "SELECT username, email_address FROM account WHERE account_id = $usertarget";
                                $result = $db->query($sql);
                                $row = $result->fetch_assoc();
                                $subj = 'You earned Wholesale Crocodile Points!';
                                $headers = 'From: WholesaleCrocodile@gmail.com' . "\r\n" . "Reply-To:WholesaleCrocodile@gmail.com" . "\r\n" . "X-Mailer: PHP/" . phpversion();
                                // Compose message
                                $mess = "Dear $row[username],\r\n\r\nYou have received $pointval points from an admin for $orgName! Congratulations!";
                                if (isset($_POST['reason'])) $mess .= "\r\nThe sponsor wrote this message: $reason";
                                // Fix long lines in case they exist
                                $message = wordwrap($mess, 70, "\r\n");
                                mail($row['email_address'], $subj, $message, $headers, '-f WholesaleCrocodile@gmail.com');
                                #echo "$message";
                            }
                            $_SESSION["vieworgid"] = $view_org;
                            redirect("../pages/view_org.php?view_org=$_SESSION[vieworgid]");
                        }
                        while ($count != mysqli_num_rows($result)) {
                            for ($i = 0; $i < 3 && $result_row = mysqli_fetch_array($result); $i = $i + 1) {
                                if ("driver" == $result_row['role']) {
                                    $return_string = "<tr>\n";
                                    $count++;
                                    $drivers = $result_row['username'];
                                    $driver_id = $result_row['account_id'];
                                    $return_string .= "<td><a href='../pages/viewaccount.php?viewid=" . $driver_id . "'>$drivers</a></td>";

                                    $xquery = "SELECT SUM(point_change_amt) FROM point_transactions WHERE driver_account_id = $driver_id AND sponsor_org_id = $view_org";
                                    $xresult = $db->query($xquery);
                                    if (!$xresult || mysqli_num_rows($xresult) == 0) {
                                        $return_string .= "<td>0</td>";
                                    } else {
                                        $xresult_row = mysqli_fetch_array($xresult);
                                        $points = $xresult_row[0];
					if($points == NULL) $points=0;
                                        $return_string .= "<td>$points</td>";
                                    }
                                    echo $return_string;
				if($q2row['live_org'] == 1) {
				    echo "\n<td colspan=3>";
                                    ?>
                                    <form name="points" method="POST" action="../pages/view_org.php">
                                        <input type='hidden' name='usertarget' value='<?php echo $driver_id; ?>'>
					<table><tr>
                                        <td><input type='text' name='reason' placeholder='Optional reason for change'></td>
                                        <td>
                                            <input type='number' name='pointval' required>
                                            <input class='button' type='submit' name='pointbutt' value='Confirm change'>
                                    </td></tr></table></form></td></tr>
                                    <?php
				}
				}
                            }
                        }
                        echo "</table><br/>";
                    }
                    $query = "SELECT `points_to_dollars` FROM `sponsor_org` WHERE `sponsor_org_id` = $view_org";
                    $res = $db->query($query);
                    $res_row = mysqli_fetch_array($res);
                    $ratio = $res_row['points_to_dollars'];

                    //edit point to dollar ratio
                    if (isset($_POST['pointsubmit'])) {
                        $newPRatio = $_POST['newratio'];
                        $sql = "UPDATE `sponsor_org` SET `points_to_dollars` = $newPRatio WHERE $view_org = `sponsor_org_id`";
                        $db->query($sql);
                        $_SESSION["vieworgid"] = $view_org;
                        header("Location: view_org.php?view_org=" . $_SESSION["vieworgid"]);
                    }
                    ?>
                    Points-to-Dollars ratio
                    <form name='pointratio' method='POST' action='../pages/view_org.php'>
                        <table class='tabledesign'>
                            <tr>
                                <td><input type='number' name='newratio' min='.01' step='.01'
                                           value='<?php echo "$ratio"; ?>'>points to equal one(1) dollar
                                </td>
                            </tr>
                            <tr>
                                <td><input class='button' type='submit' name='pointsubmit' value='Change ratio'></td>
                            </tr>
                        </table>
                    </form><br>
                    <?php
                }

                echo "<h2><a href='../pages/catalog.php'>Catalog</a></h2>";
                echo "<h2><a href='../pages/manage_catalog.php'>Manage Catalog</a></h2>";
            }
        }
    } else {
        echo "<br><p class=error>No organization selected</p>";
    }
    ?>
</main>
<?php include('../php/footer.php'); ?>
</html>
