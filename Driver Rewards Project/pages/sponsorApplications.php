<?php session_start(); ?>
<!DOCTYPE html>
<html>
<?php $title = "Application Status";
include('../php/header.php'); ?>
<main>
    <?php
    $sql = "SELECT sponsor_org_id FROM driver_in_org WHERE driver_account_id = $_SESSION[user_id]";
    if (!($result = $db->query($sql))) footdie("ERROR: Could not fetch your org at this time!");
     
    if ($role!='admin' && ($role == "driver" || mysqli_num_rows($result) == 0)) $viewType='driver';
    else $viewType=$role;

    // Form handling
    if (isset($_POST['driver'])) {
        $sql = "UPDATE open_sponsor_applications SET status='$_POST[setStatus]', auth_account_id = $_SESSION[user_id]
            WHERE driver_account_id = $_POST[driver] AND sponsor_org_id = $_POST[sponsor]";
        if (!$db->query($sql)) echo "Could not complete the request at this time!";
        // NOTE: Bug where this can multiple insert records
        if ($_POST['setStatus'] == 'accepted') {
            // Validate that user isn't already in org
            $sql = "SELECT driver_account_id FROM driver_in_org WHERE driver_account_id = $_POST[driver] AND sponsor_org_id = $_POST[sponsor]";
            if (!($result = $db->query($sql))) echo "Could not complete the request at this time 2!";
            else if (mysqli_num_rows($result) == 1) echo "Success (no db action required)";
            else {
                $sql = "INSERT INTO driver_in_org VALUES ($_POST[driver], $_POST[sponsor])";
                if (!$db->query($sql)) echo "Could not complete the request at this time!";
                // Notification
                $sql = "SELECT org_name FROM sponsor_org WHERE sponsor_org_id = $_POST[sponsor]";
                $result = $db->query($sql);
                $orgName = $result->fetch_assoc();
                $orgName = $orgName['org_name'];
                $sql = "SELECT username, email_address FROM account WHERE account_id = $_POST[driver]";
                $result = $db->query($sql);
                $row = $result->fetch_assoc();
                $subj = "You have been added to $orgName";
                $headers = 'From: WholesaleCrocodile@gmail.com' . "\r\n" . "Reply-To:WholesaleCrocodile@gmail.com" . "\r\n" . "X-Mailer: PHP/" . phpversion();
                // Compose message
                $mess = "Dear $row[username],\r\n\r\nYou have been added to the $orgName organization on Wholesale Crocodile Driver Rewards! Congratulations!";
                // Fix long lines in case they exist
                $message = wordwrap($mess, 70, "\r\n");
                mail($row['email_address'], $subj, $message, $headers, '-f WholesaleCrocodile@gmail.com');
                #echo "$message";
            }
        }
    } else if (isset($_POST['uName'])) {
        // Validate that user and sponsor exist
        // If adding, validate that user isn't already in org
        // If removing, validate that user is in org
        $sql = "SELECT account.username, account.account_id, sponsor_org.org_name, sponsor_org.sponsor_org_id FROM account, sponsor_org
WHERE account.username = \"$_POST[uName]\" AND sponsor_org.org_name = \"$_POST[sName]\"";
        if (!($result = $db->query($sql))) echo "Could not complete the request at this time 1!";
        else {
            if (mysqli_num_rows($result) == 0) {
                echo "User or Sponsor Org does not exist";
            } else {
                $row = $result->fetch_assoc();
                $sql = "SELECT driver_account_id FROM driver_in_org WHERE driver_account_id = $row[account_id] AND sponsor_org_id = $row[sponsor_org_id]";
                if (!($result = $db->query($sql))) echo "Could not complete the request at this time 2!";
                else if (mysqli_num_rows($result) == ($_POST['act'] == 'add')) echo "Success (no db action required)";
                else {
                    // success
                    if ($_POST['act'] == 'add') {
                        $sql = "INSERT INTO driver_in_org VALUES ($row[account_id], $row[sponsor_org_id])";
                        if (!$db->query($sql)) echo "Could not complete the request at this time 3!";
                    } else {
                        $sql = "DELETE FROM driver_in_org WHERE driver_account_id = $row[account_id] AND sponsor_org_id = $row[sponsor_org_id]";
                        if (!$db->query($sql)) echo "Could not complete the request at this time 3.5!";
                    }
                    $join = ($_POST['act'] == 'add') ? 1 : 'NULL';
                    $sql = "INSERT INTO driver_changes_org VALUES ($row[account_id], $row[sponsor_org_id], $join, NULL)";
                    if (!$db->query($sql)) echo "Could not complete the request at this time 4!";
                    if ($join == 1) {
                        $sql = "INSERT INTO open_sponsor_applications VALUES ($row[account_id], $row[sponsor_org_id], $_SESSION[user_id], 'accepted', NULL)";
                        if (!$db->query($sql)) echo "Could not complete the request at this time 5!";
                        // Notification
                        $sql = "SELECT org_name FROM sponsor_org WHERE sponsor_org_id = $row[sponsor_org_id]";
                        $result = $db->query($sql);
                        $orgName = $result->fetch_assoc();
                        $orgName = $orgName['org_name'];
                        $sql = "SELECT username, email_address FROM account WHERE account_id = $row[account_id]";
                        $result = $db->query($sql);
                        $row = $result->fetch_assoc();
                        $subj = "You have been added to $orgName!";
                        $headers = 'From: WholesaleCrocodile@gmail.com' . "\r\n" . "Reply-To:WholesaleCrocodile@gmail.com" . "\r\n" . "X-Mailer: PHP/" . phpversion();
                        // Compose message
                        $mess = "Dear $row[username],\r\n\r\nYou have been added to the $orgName organization on Wholesale Crocodile Driver Rewards! Congratulations!";
                        // Fix long lines in case they exist
                        $message = wordwrap($mess, 70, "\r\n");
                        mail($row['email_address'], $subj, $message, $headers, '-f WholesaleCrocodile@gmail.com');
                    } else {
                        $sql = "DELETE FROM open_sponsor_applications WHERE driver_account_id = $row[account_id] AND sponsor_org_id = $row[sponsor_org_id]";
                        if (!$db->query($sql)) echo "Could not complete the request at this time 6!";
                        // Notification
                        $sql = "SELECT org_name FROM sponsor_org WHERE sponsor_org_id = $row[sponsor_org_id]";
                        $result = $db->query($sql);
                        $orgName = $result->fetch_assoc();
                        $orgName = $orgName['org_name'];
                        $sql = "SELECT username, email_address FROM account WHERE account_id = $row[account_id]";
                        $result = $db->query($sql);
                        $row = $result->fetch_assoc();
                        $subj = "You have been removed from $orgName";
                        $headers = 'From: WholesaleCrocodile@gmail.com' . "\r\n" . "Reply-To:WholesaleCrocodile@gmail.com" . "\r\n" . "X-Mailer: PHP/" . phpversion();
                        // Compose message
                        $mess = "Dear $row[username],\r\n\r\nYou have been removed from the $orgName organization on Wholesale Crocodile Driver Rewards.";
                        // Fix long lines in case they exist
                        $message = wordwrap($mess, 70, "\r\n");
                        mail($row['email_address'], $subj, $message, $headers, '-f WholesaleCrocodile@gmail.com');
                    }
                }
            }
        }
    }
    // Form creation
    if ($viewType == "driver") {
        $sql = "SELECT open_sponsor_applications.*, sponsor_org.org_name
FROM open_sponsor_applications
INNER JOIN sponsor_org ON sponsor_org.sponsor_org_id = open_sponsor_applications.sponsor_org_id
WHERE driver_account_id = $_SESSION[user_id] && sponsor_org.live_org = 1";
    } else if ($role == "sponsor") {
        $sql = "SELECT open_sponsor_applications.*, a.username as driver_username, a.role,  a2.username as auth_username, sponsor_org.org_name
FROM open_sponsor_applications
INNER JOIN account a ON open_sponsor_applications.driver_account_id = a.account_id
LEFT OUTER JOIN account a2 ON open_sponsor_applications.auth_account_id = a2.account_id
INNER JOIN sponsor_org ON open_sponsor_applications.sponsor_org_id = sponsor_org.sponsor_org_id
WHERE open_sponsor_applications.sponsor_org_id IN (SELECT sponsor_org_id FROM driver_in_org WHERE driver_account_id = $_SESSION[user_id]) AND a.live_account = 1";
    } else {
        $sql = "SELECT open_sponsor_applications.*, a.username as driver_username, a.role, a2.username as auth_username, sponsor_org.org_name
FROM open_sponsor_applications
INNER JOIN account a ON open_sponsor_applications.driver_account_id = a.account_id
INNER JOIN sponsor_org ON open_sponsor_applications.sponsor_org_id = sponsor_org.sponsor_org_id
LEFT OUTER JOIN account a2 ON a2.account_id = open_sponsor_applications.auth_account_id WHERE a.live_account = 1";
        // Admins also get a form to just punch a driver into an org
        ?>
        <label for=joinOrg>Manually Insert User into or Remove User From Org</label>
        <form name=joinOrg method=POST action='../pages/sponsorApplications.php'>
            <label for=uName>Username</label>
            <input name=uName type=text required>
            <label for=sName>Sponsor</label>
            <input name=sName type=text required>
            <label for=act>Action</label>
            <select name='act'>
                <option value='add' selected>Add to Org</option>
                <option value='rm'>Remove from Org</option>
            </select>
            <input class='button' name=join type=submit value='Update'>
        </form><br>
        <?php
    }
    $sql .= " ORDER BY open_sponsor_applications.status ASC, open_sponsor_applications.creation_time DESC";
    ?>
    <table border=1px>
        <tr><?php if ($viewType != "driver") echo "<td>Username</td><td>Type</td>"; ?>
            <td>Sponsor Org</td>
            <?php if ($viewType != "driver") echo "<td>Authorizing Account</td>"; ?>
            <td>Status</td>
            <td>Applied</td>
            <?php if ($viewType != "driver") echo "<td>Action</td>"; ?></tr>
        <?php
        $result = $db->query($sql);
        $nrows = mysqli_num_rows($result);
        for ($i = 0; $i < $nrows; $i += 1) {
            $row = $result->fetch_assoc();
            ?>
            <tr><?php if ($viewType != "driver") { echo "<td>$row[driver_username]</td><td>";
                if($row['role'] == 'sponsor') echo "<b>SPONSOR</b></td>"; else echo "Driver</td>";
                }
                echo "<td>$row[org_name]</td>";
                if ($viewType != "driver") {
                    echo "<td>$row[auth_username]</td>
            <td><form method=POST action='../pages/sponsorApplications.php'>
            <select name='setStatus'>
            <option value='pending'";
                    if ($row['status'] == 'pending') echo "selected";
                    echo ">Pending</option>
            <option value='accepted'";
                    if ($row['status'] == 'accepted') echo "selected";
                    echo ">Accepted</option>
            <option value='rejected'";
                    if ($row['status'] == 'rejected') echo "selected";
                    echo ">Rejected</option>
            </select>
            </td>";
                } else echo "<td>$row[status]</td>";
                echo "<td>$row[creation_time]</td>";
                if ($viewType != "driver") echo "<td><input class=button type=submit value='Update'>
            <input type=hidden name='driver' value='$row[driver_account_id]'>
            <input type=hidden name='sponsor' value='$row[sponsor_org_id]'>
            </form></td>"; ?></tr>
            <?php
        }
        ?>
    </table>
</main>
<?php include('../php/footer.php'); ?>
</html>
