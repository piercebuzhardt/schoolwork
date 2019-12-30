<?php session_start(); ?>
<!DOCTYPE html>
<html>
<?php
if (isset($_SESSION["user_id"])) {
    @include_once('../php/sqlconnect.php');
    $sql = "SELECT sponsor_org_id FROM driver_in_org WHERE driver_account_id = $_SESSION[user_id]";
    if (!($result = $db->query($sql))) footdie("ERROR: Could not fetch your org at this time!");
     
    if ($role != "admin" && ($role == "driver" || mysqli_num_rows($result) == 0)) { $viewType='driver'; $title = "Apply for a Sponsor"; }
    else { $viewType=$role ; $title = "Edit Application Information"; }
}
@include('../php/header.php'); ?>
<main>
    <?php // Form processing
    if (isset($_POST['makeChange'])) {
        $sql = "UPDATE sponsor_org SET org_name = '$_POST[orgName]', org_email_address = '$_POST[orgEmail]', org_bio = \"$_POST[orgBio]\", org_url = '$_POST[orgUrl]' WHERE sponsor_org_id = $_POST[target]";
        if (!$db->query($sql)) {
            echo "Failed to update information at this time!";
        }
    } else if (isset($_POST['apply'])) {
        $sql = "INSERT INTO open_sponsor_applications (driver_account_id, sponsor_org_id) VALUES ($_SESSION[user_id], $_POST[target])";
        if (!$db->query($sql)) {
            echo "Failed to complete your application at this time!";
        }
    }
    ?>
    <?php
    if ($viewType == "driver") {
    $sql = "SELECT * FROM sponsor_org WHERE live_org = 1 AND application_open = 1 and sponsor_org_id NOT IN (SELECT sponsor_org_id FROM driver_in_org WHERE driver_account_id = $_SESSION[user_id]) AND sponsor_org_id NOT IN (SELECT sponsor_org_id FROM open_sponsor_applications WHERE driver_account_id = $_SESSION[user_id])";
    $result = $db->query($sql);
    $nrows = mysqli_num_rows($result);
    if ($nrows == 0)
    echo "There are no organizations to which you can apply at this time. We apologize for the inconvenience.";
    else {
    ?>
    <table border=1px>
        <tr>
            <td>Org Name</td>
            <td>Org Email</td>
            <td>Org Bio</td>
            <td>Org URL</td>
            <td>Apply</td>
        </tr>
        <?php
        for ($i = 0; $i < $nrows; $i += 1) {
            $row = $result->fetch_assoc();
            ?>
            <tr><?php echo "<td>$row[org_name]</td><td>$row[org_email_address]</td><td>$row[org_bio]</td><td>$row[org_url]</td><td>
      <form method=POST action='../pages/sponsorApply.php'><input name='target' type=hidden value='$row[sponsor_org_id]'><input name='apply' class='button' type=submit value='APPLY'></form></td>"; ?></tr>
            <?php
        }
	echo "</table>";
        }
        }
        else { // Sponsor or Admin views page
        // Sponsors just see their own org(s), admins see all live orgs
        if ($viewType == "sponsor") {
            $sql = "SELECT * FROM sponsor_org WHERE live_org = 1 AND sponsor_org_id IN (SELECT sponsor_org_id FROM driver_in_org WHERE driver_account_id = $_SESSION[user_id])";
        } else {
            $sql = "SELECT * FROM sponsor_org WHERE live_org = 1";
        }
        ?>
        <table border=1px>
            <tr>
                <td>Org Name</td>
                <td>Org Email</td>
                <td>Org Bio</td>
                <td>Org URL</td>
                <td>Submit Changes</td>
            </tr>
            <?php
            $result = $db->query($sql);
            $nrows = mysqli_num_rows($result);
            for ($i = 0; $i < $nrows; $i += 1) {
                $row = $result->fetch_assoc();
                ?>
                <tr>
                    <form method=POST action="../pages/sponsorApply.php">
                        <input name=target type=hidden value="<?php echo "$row[sponsor_org_id]"; ?>">
                        <td><input name=orgName type=text value="<?php echo "$row[org_name]"; ?>"></td>
                        <td><input name=orgEmail type=text value="<?php echo "$row[org_email_address]"; ?>"></td>
                        <td><input name=orgBio type=text value="<?php echo "$row[org_bio]"; ?>"></td>
                        <td><input name=orgUrl type=text value="<?php echo "$row[org_url]"; ?>"></td>
                        <td><input name=makeChange type=submit class=button value="Submit"></td>
                    </form>
                </tr>
                <?php
            }
            echo "</table>";
            }
            ?>
</main>
<?php include('../php/footer.php'); ?>
</html>
