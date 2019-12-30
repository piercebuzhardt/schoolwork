<!DOCTYPE HTML>
<?php session_start(); ?>
<html>
<?php $title = 'User List';
include('../php/header.php'); ?>
<main>
    <?php
    if (isset($role)) {
        $role_viewing = $role;
        if ($role_viewing == "admin") {
            echo "<h3>Admins(";
            $query = "SELECT `username`, `account_id`, `email_address`, `live_account`, `path_to_pfp` FROM `account` WHERE role = 'admin' ORDER BY `username`";
            $result = $db->query($query);
            $acc_nums = mysqli_num_rows($result);
            echo "$acc_nums)</h3><p><hr>";
            if (!$result || mysqli_num_rows($result) == 0) {
                echo("<p class =\"errorText\">This shouldn't be possible. Notify support please if you see this.</p>");
            } else {
                $count = 0;
                $return_string = "<table border =\"1\"><tr><td><b>Admin</b></td><td><b>Email</b></td><td><b>Is active?</b></td></tr>";
                while ($count != mysqli_num_rows($result)) {
                    for ($i = 0; $i < 4 && $result_row = mysqli_fetch_array($result); $i = $i + 1) {
                        $return_string .= "<tr valign=\"top\" style=\"padding-top: 25px; padding-bottom: 25px;\">";
                        $count++;
                        $user = $result_row['username'];
                        $id = $result_row['account_id'];
                        $email = $result_row['email_address'];
                        $live = $result_row['live_account'];
                        $return_string .= "<td><a href=../pages/viewaccount.php?viewid=" . $id . ">$user</a></td><td>$email</td><td>";
                        if ($live == '1')
                            $return_string .= "active account</td>";
                        else
                            $return_string .= "inactive account</td>";
                        $return_string .= "</tr>";
                    }
                }
                $return_string .= "</table><br/>";
                echo "$return_string";
            }
            echo "<h3>Sponsors(";
            $query = "SELECT `username`, `account_id`, `email_address`, `live_account`, `path_to_pfp` FROM `account` WHERE role = 'sponsor' ORDER BY `username`";
            $result = $db->query($query);
            $acc_nums = mysqli_num_rows($result);
            echo "$acc_nums)</h3><p><hr>";
            if (!$result || mysqli_num_rows($result) == 0) {
                echo("<p class =\"errorText\">There are no sponsors to display.</p>");
            } else {
                $count = 0;
                $return_string = "<table border =\"1\"><tr><td><b>Sponsor</b></td><td><b>Email</b></td><td><b>Is active?</b></td></tr>";
                while ($count != mysqli_num_rows($result)) {
                    for ($i = 0; $i < 4 && $result_row = mysqli_fetch_array($result); $i = $i + 1) {
                        $return_string .= "<tr valign=\"top\" style=\"padding-top: 25px; padding-bottom: 25px;\">";
                        $count++;
                        $user = $result_row['username'];
                        $id = $result_row['account_id'];
                        $email = $result_row['email_address'];
                        $live = $result_row['live_account'];
                        $return_string .= "<td><a href=../pages/viewaccount.php?viewid=" . $id . ">$user</a></td><td>$email</td><td>";
                        if ($live == '1')
                            $return_string .= "active account</td>";
                        else
                            $return_string .= "inactive account</td>";
                        $return_string .= "</tr>";
                    }
                }
                $return_string .= "</table><br/>";
                echo "$return_string";
            }
            echo "<h3>Drivers(";
            $query = "SELECT `username`, `account_id`, `email_address`, `live_account`, `path_to_pfp` FROM `account` WHERE role = 'driver' ORDER BY `username`";
            $result = $db->query($query);
            $acc_nums = mysqli_num_rows($result);
            echo "$acc_nums)</h3><p><hr>";
            if (!$result || mysqli_num_rows($result) == 0) {
                echo("<p class =\"errorText\">There are no drivers to display.</p>");
            } else {
                $count = 0;
                $return_string = "<table border =\"1\"><tr><td><b>Driver</b></td><td><b>Email</b></td><td><b>Is active?</b></td></tr>";
                while ($count != mysqli_num_rows($result)) {
                    for ($i = 0; $i < 4 && $result_row = mysqli_fetch_array($result); $i = $i + 1) {
                        $return_string .= "<tr valign=\"top\" style=\"padding-top: 25px; padding-bottom: 25px;\">";
                        $count++;
                        $user = $result_row['username'];
                        $id = $result_row['account_id'];
                        $email = $result_row['email_address'];
                        $live = $result_row['live_account'];
                        $return_string .= "<td><a href=../pages/viewaccount.php?viewid=" . $id . ">$user</a></td><td>$email</td><td>";
                        if ($live == '1')
                            $return_string .= "active account</td>";
                        else
                            $return_string .= "inactive account</td>";
                        $return_string .= "</tr>";
                    }
                }
                $return_string .= "</table><br/>";
                echo "$return_string";
            }
            echo "<h3>Sponsor Organizations(";
            $query = "SELECT `org_name`, `path_to_org_logo`, `live_org`, `org_email_address`, `sponsor_org_id`, `application_open` FROM `sponsor_org` WHERE 1";
            $result = $db->query($query);
            $acc_nums = mysqli_num_rows($result);
            echo "$acc_nums)</h3><p><hr>";
            if (!$result || mysqli_num_rows($result) == 0) {
                echo("<p class =\"errorText\">There are no sponsor organizations to display.</p>");
            } else {
                $count = 0;
                $return_string = "<table border =\"1\"><tr><td><b>Organization</b></td><td><b>Email</b></td><td><b>Is active?</b></td><td><b>Is open?</b></td></tr>";
                while ($count != mysqli_num_rows($result)) {
                    for ($i = 0; $i < 6 && $result_row = mysqli_fetch_array($result); $i = $i + 1) {
                        $return_string .= "<tr valign=\"top\" style=\"padding-top: 25px; padding-bottom: 25px;\">";
                        $count++;
                        $org_name = $result_row['org_name'];
                        $alive = $result_row['live_org'];
                        $app_open = $result_row['application_open'];
                        $email = $result_row['org_email_address'];
                        $org_id = $result_row['sponsor_org_id'];

                        //////IMPORTANT: URL IS PLACEHOLDER UNTIL PAGE IS MADE
                        $return_string .= "<td><a href='../pages/view_org.php?view_org=" . $org_id . "'>$org_name</a></td><td>$email</td><td>";
                        if ($alive == '1') $return_string .= "active account</td><td>";
                        else $return_string .= "inactive account</td><td>";
                        if ($app_open == 1) $return_string .= "open organization</td>";
                        else $return_string .= "closed organization</td>";
                        $return_string .= "</tr>";
                    }
                }
                $return_string .= "</table><br/>";
                echo "$return_string";
            }
        } elseif ($role_viewing == "sponsor") {
            $query = "SELECT * FROM `driver_in_org` WHERE $id = `driver_account_id`";
            $result = $db->query($query);
            if (!$result || mysqli_num_rows($result) == 0) {
                echo("<p class =\"errorText\">You are not linked to a sponsor org.</p>");
            } else {
                echo "<table border='1px'>";
                $result_row = mysqli_fetch_array($result);
                $org_id = $result_row['sponsor_org_id'];
                $query = "SELECT * FROM `account` INNER JOIN `driver_in_org` ON `account`.`account_id` = `driver_in_org`.`driver_account_id` WHERE `driver_in_org`.`sponsor_org_id` = $org_id ORDER BY `account`.`role`,`account`.`username`";
                $result = $db->query($query);
                if (($nrows = mysqli_num_rows($result)) != 0) {
                    echo "<tr><td><b>Username</b></td><td><b>Email/b></td><td><b>Shipping address</b></td><td><b>Role</b></td></tr>";
                    for ($i = 0; $i < $nrows; $i += 1) {
                        $row = mysqli_fetch_array($result);
                        echo "<tr><td><a href='../pages/viewaccount.php?viewid=" . $row[account_id] . "'>$row[username]</a></td><td>$row[email_address]</td>
						<td>$row[shipping_address]</td><td>$row[role]</td></tr>";

                    }
                }
                echo "</table>";
            }
        } else {
            echo "You lack the permissions to view this page.";
        }
    } else {
        echo "You are not logged in.";
    }
    ?>
</main>
<?php include('../php/footer.php'); ?>
</html>
