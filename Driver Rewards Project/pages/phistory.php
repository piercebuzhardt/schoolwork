<?php session_start(); ?>
<!DOCTYPE html>
<html>
<?php $title = "Point History";
include('../php/header.php'); ?>
<main>
    <?php
    # Until we migrate control under one roof, gotta import my class to do convenient SQL stuff
    include_once('../php/mysqlClass.php');
    # Bullshit the server about which account we're logged into
    $uid = $_SESSION['user_id'];
    if (isset($_GET['viewid'])) {
        $uid = $_GET['viewid'];
    }
    ##### NEEDS A CHECK ON WHO IS VIEWING THIS PAGE[ADMINS, CORRECT USER, USER'S ORG]

    if ($role == "sponsor") {
        $me = $_SESSION['user_id'];
        $query = "SELECT `sponsor_org_id` FROM `driver_in_org` WHERE `driver_account_id` = $me";
        $result = $db->query($query);
        $result_row = mysqli_fetch_array($result);
        $my_org = $result_row['sponsor_org_id'];
        $query = "SELECT * FROM `driver_in_org` WHERE `sponsor_org_id` = $my_org && `driver_account_id` = $id";
        if (!$result || mysqli_num_rows($result) == 0) {
            $correct_sponsor = false;
        } else
            $correct_sponsor = true;
    }

    if ($role == "admin" || $_SESSION['user_id'] == $id || $correct_sponsor) {
	$sql = "SELECT d.sponsor_org_id FROM driver_in_org as d INNER JOIN sponsor_org as s ON d.sponsor_org_id = s.sponsor_org_id WHERE driver_account_id = $uid AND live_org = 1";
        if (!($result = $db->query($sql))) footdie("ERROR: Could not fetch your org at this time!");
        if(mysqli_num_rows($result) == 0) footdie("<p class=error>You are not associated with any active sponsor organization!</p>");
        $row = $result->fetch_assoc();
        #$_SESSION['user_id'] = 1;
        $query = "SELECT * FROM `point_transactions` INNER JOIN `sponsor_org` ON `point_transactions`.`sponsor_org_id` = `sponsor_org`.`sponsor_org_id` WHERE driver_account_id = $uid && `sponsor_org`.`live_org` = 1";
        if (!($result = $db->query($query))) {
            die ("<p class=\"errorText\">Invalid server state, please log in</p>");
        } else {
            $count = 0;
            $return_string = "<table border =\"1\">";
            $return_string .= "<tr valign=\"top\" style=\"padding-top: 25px; padding-bottom: 25px;\"><td>Sponsor</td><td>Change</td><td>Reason</td><td>Time</td></tr>";
            while ($count != mysqli_num_rows($result)) { // fetch all results
                for ($i = 0; $i < 8 && $result_row = mysqli_fetch_array($result); $i = $i + 1) { // fetch a row of 8 items
                    $return_string .= "<tr valign=\"top\" style=\"padding-top: 25px; padding-bottom: 25px;\">";
                    $count++;
                    $point_change_id = $result_row['point_change_id'];
                    $auth_account_id = $result_row['auth_account_id'];
                    $sponsor_org_id = $result_row['sponsor_org_id'];
                    $sponsor = $result_row['org_name'];
                    $point_change = $result_row['point_change_amt'];
                    $change_time = $result_row['change_time'];
                    $reason = $result_row['change_reason'];
                    //SHOULD BE EXPANDED, BUT DUNNO HOW
                    if ($role == "sponsor") {
                        if ($sponsor_org_id == $my_org) {
                            $return_string .= "<td>$sponsor</td><td>$point_change</td><td>$reason</td><td>$change_time</td>";
                            $return_string .= "</tr>";
                        }
                    } else {
                        $return_string .= "<td>$sponsor</td><td>$point_change</td><td>$reason</td><td>$change_time</td>";
                        $return_string .= "</tr>";
                    }
                }
            }
            $return_string .= "</table><br/>";
            echo "$return_string";
        }
    }

    ?>

</main>
<?php include('../php/footer.php'); ?>
</html>
