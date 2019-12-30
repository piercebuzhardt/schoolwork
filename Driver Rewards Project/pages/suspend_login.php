<?php session_start(); ?>
<!DOCTYPE html>
<html>
<?php $title = 'Suspend Login';
include('../php/header.php'); ?>
<main>
    <?php
    // ADMIN ONLY PAGE
    authenticate_admin($db);

    // Handle updates
    if (isset($_POST['ban'])) {
        // Generates new time or sets to NULL
        $bonus = $_POST['bonus'] * -3600;
        $sql = "SELECT account.account_id, IF(login_suspended, IF(SUBTIME(login_suspended, SEC_TO_TIME($bonus)) > NOW(), SUBTIME(login_suspended, SEC_TO_TIME($bonus)), NULL), IF(SUBTIME(NOW(), SEC_TO_TIME($bonus)) > NOW(), SUBTIME(NOW(), SEC_TO_TIME($bonus)), NULL)) AS time FROM account_bans RIGHT OUTER JOIN account ON account_bans.account_id = account.account_id WHERE account.account_id = $_POST[usertarget]";
        $result = $db->query($sql);
        $newval = $result->fetch_assoc();
        if ($newval['time'] == NULL) $newval['time'] = "NULL";
        else $newval['time'] = "'$newval[time]'";
        $sql = "UPDATE `account_bans` SET `login_suspended` = $newval[time] WHERE `account_id` = $_POST[usertarget]";
        $db->query($sql);
    }
    ?>
    <!-- Search bar -->
    <form name='search' method='POST' action='../pages/suspend_login.php'>
        <label for='reggie'>Username Like:</label><input name='reggie'
                                                         type='text' <?php if (isset($_POST['reggie'])) echo "value='$_POST[reggie]'"; ?>>
        <input class='button' type='submit'>
    </form>

    <!-- Table of users to ban/unban -->
    <table border='1px'>
        <?php
        // Clearable searches
        if (isset($_POST['search']) && $_POST['reggie'] == '') unset($_POST['reggie']);

        // Select general SQL or limited SQL based on search (if applicable)
        $sql = "SELECT `account`.`account_id`, `username`, `login_suspended`, `product_report_mute`, `org_report_mute`, `pfp_report_mute`, `allowed_change_pfp`, TIMEDIFF(`login_suspended`, NOW()) AS `remain` FROM `account_bans` RIGHT OUTER JOIN `account` ON `account`.`account_id` = `account_bans`.`account_id` WHERE `account`.`live_account` = 1";
        if (isset($_POST['reggie']) && $_POST['reggie'] != '') {
            $sql .= " AND `username` RLIKE '$_POST[reggie]'";
        }
        // Do the search
        $result = $db->query($sql);
        if (($nrows = mysqli_num_rows($result)) != 0) {
            echo "<tr><td>Username</td><td>Login Suspended For</td><td>Suspension Adjustment (+/- Hours)</td></tr>";
            for ($i = 0; $i < $nrows; $i += 1) {
                $row = $result->fetch_assoc();
                echo "<tr><td>$row[username]</td><td>$row[remain]</td><td>";
                ?>
                <form name="hammer" method="POST" action="../pages/suspend_login.php">
                    <input type='hidden' name='usertarget' value='<?php echo $row['account_id']; ?>'>
                    <?php if (isset($_POST['reggie'])) echo "<input type='hidden' name='reggie' value='$_POST[reggie]'>"; ?>
                    <input type='number' name='bonus' required>
                    <input class='button' type='submit' name='ban' value='BAN'>
                </form>
                <?php
                echo "</td></tr>";
            }
        }
        ?>
    </table>
</main>
<?php include('../php/footer.php'); ?>
</html>
