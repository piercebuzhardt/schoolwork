<?php session_start(); ?>
<!DOCTYPE html>
<html>
<?php $title = "Org Control";
include('../php/header.php'); ?>
<main>
    <?php
    authenticate_admin($db);
    // Form handling
    if (isset($_POST['create'])) {
        $sql = "INSERT INTO sponsor_org (org_name, org_email_address, points_to_dollars) VALUES ('$_POST[nOrgName]', '$_POST[nOrgEmail]', $_POST[points])";
        if (!$db->query($sql)) echo "Could not perform that action at this time!<br>";
    }
    if (isset($_POST['delete'])) {
        $sql = "UPDATE sponsor_org SET live_org = NULL WHERE sponsor_org_id = $_POST[orgId]";
        if (!$db->query($sql)) echo "Could not perform that action at this time!<br>";
    }
    if (isset($_POST['rez'])) {
        $sql = "UPDATE sponsor_org SET live_org = 1 WHERE sponsor_org_id = $_POST[orgId]";
        if (!$db->query($sql)) echo "Could not perform that action at this time!<br>";
    }
    ?>
    <label for=create_org>Create a new Org</label>
    <form name=create_org method=POST action='../pages/orgcontrol.php'>
        <label for=nOrgName>New Org Name</label>
        <input type=text name=nOrgName required><br>
        <label for=nOrgEmail>New Org Email</label>
        <input type=text name=nOrgEmail required><br>
        <label for=points>Points to Dollars</label>
        <input type=number step="0.01" name=points required><br>
        <input type=submit name=create value='Create' class='button'>
    </form>
    <br>

    <label for=delet_org>Existing Orgs</label>
    <table name=delet_org border=1px>
        <tr>
            <td>Org Name</td>
            <td>Delete?</td>
        </tr>
        <?php
        $sql = "SELECT * FROM sponsor_org WHERE live_org = 1";
        if (!($result = $db->query($sql))) echo "Could not populate this table at this time";
        else {
            $nrows = mysqli_num_rows($result);
            for ($i = 0; $i < $nrows; $i += 1) {
                $row = $result->fetch_assoc();
                echo "<tr><td>$row[org_name]</td><td><form name=delete_org method=POST action='../pages/orgcontrol.php'><input type=submit name=delete value='Delete' class=button><input type=hidden name=orgId value=$row[sponsor_org_id]></form></td></tr>";
            }
        }
        ?>
    </table>
    <br>
    <label for=rez_org>Return Org</label>
    <table name=rez_org border=1px>
        <tr>
            <td>Org Name</td>
            <td>Bring Back?</td>
        </tr>
        <?php
        $sql = "SELECT * FROM sponsor_org WHERE live_org IS NULL";
        if (!($result = $db->query($sql))) echo "Could not populate this table at this time";
        else {
            $nrows = mysqli_num_rows($result);
            for ($i = 0; $i < $nrows; $i += 1) {
                $row = $result->fetch_assoc();
                echo "<tr><td>$row[org_name]</td><td><form name=rest_org method=POST action='../pages/orgcontrol.php'><input type=submit name=rez value='Bring Back' class=button><input type=hidden name=orgId value=$row[sponsor_org_id]></form></td></tr>";
            }
        }
        ?>
    </table>
</main>
<?php include('../php/footer.php'); ?>
</html>
