<?php
session_start();
?>
<!DOCTYPE html>
<html>
<?php $title = "Force Update";
include('../php/header.php'); ?>
<main>
    <?php
    // MUST BE Admin or Sponsor to view this page!!
    if (!authenticate_admin($db, 0) && !authenticate_sponsor($db, 0)) {
        footdie("<p class='error'>YOU MUST BE AN ADMIN OR SPONSOR TO VIEW THIS PAGE</p> </main>");
    }
    ?>
    <!-- Search bar -->
    <form name='search' method='POST' action='../pages/force_update.php'>
        <label for='reggie'>Username Like:</label>
	<input name='reggie' type='text' <?php if (isset($_POST['reggie'])) echo "value='$_POST[reggie]'"; ?>>
        <input class='button' type='submit'>
    </form>
    <?php
    // Form to select from appropriate users
    $sql = "SELECT * FROM account";
    if (authenticate_sponsor($db, 0)) {
        // Get Sponsor's Drivers
        $org = getSponsorOrgId($db, $_SESSION['user_id']);
        $sql .= " INNER JOIN driver_in_org ON driver_in_org.driver_account_id = account.account_id WHERE driver_in_org.sponsor_org_id = $org";
        // Check for RLIKE update
        if (isset($_POST['reggie']) && $_POST['reggie'] != '') $sql .= " AND account.username RLIKE '$_POST[reggie]'";
    } else if (isset($_POST['reggie']) && $_POST['reggie'] != '') $sql .= " WHERE username RLIKE '$_POST[reggie]'";

    // Perform necessary updates before tables are re-rendered
    if (isset($_POST['commit'])) {
        /*echo "New Username: $_POST[username]<br>";
        echo "New Email: $_POST[email]<br>";
        echo "New Address: $_POST[ship]<br>";
        echo "Account ID: $_POST[usertarget]<br>";
        if (isset($_POST['resetPassword'])) {
            echo "Reset Password? YES<br>";
        } else {
            echo "Reset Password? NO<br>";
        }
	if (isset($_POST['deleteUser'])) {
	    echo "Delete User? YES<br>";
	} else {
	    echo "Delete User? NO<br>";
        }
	*/
	// Username and Email still have to remain unique
        if (mysqli_num_rows($db->query("SELECT username FROM account WHERE username='$_POST[username]' AND account_id != $_POST[usertarget]")) ||
            mysqli_num_rows($db->query("SELECT email_address FROM account WHERE email_address='$_POST[email]' AND account_id != $_POST[usertarget]")) ||
            !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            echo "<p class='error'>Unable to complete that update (make sure username and email are unique and email is valid)</p>";
        } else { // go ahead and do the update
            $db->query("UPDATE account SET username='$_POST[username]', email_address='$_POST[email]', shipping_address='$_POST[ship]' WHERE account_id=$_POST[usertarget]");
            // See if password reset is requested
            if (isset($_POST['resetPassword'])) {
                $db->query("INSERT INTO password_resets VALUES ('$_POST[usertarget]', '$_SESSION[user_id]', NULL, NULL, NULL, 1)");
            }
	    // See if deletion is going to happen
	    if (isset($_POST['deleteUser'])) {
	      if($_POST['direction'] != 2) {
	        $db->query("UPDATE `account` SET `account`.`live_account`= 2 WHERE `account`.`account_id` = $_POST[usertarget]");
	      } else {
	        $db->query("UPDATE account SET live_account = 1 WHERE account_id = $_POST[usertarget]");
	      }
	    }
        }
    }
    ?>
    <!-- Table of users -->
    <table border=1px>
        <?php
        $result = $db->query($sql);
        if (($nrows = mysqli_num_rows($result)) != 0) {
            echo "<tr><td>Username</td><td>Email</td><td>Shipping Address</td><td>Force Password Reset</td><td>Delete(d) User</td><td>Commit Changes</td></tr>";
            for ($i = 0; $i < $nrows; $i += 1) {
                $row = $result->fetch_assoc();
                ?>
		<tr><td>
                <form name="update" method="POST" action="../pages/force_update.php">
                    <input type='hidden' name='usertarget' value='<?php echo $row['account_id']; ?>'>
                    <input type='text' name='username' value='<?php echo $row['username']; ?>'></td><td>
                    <input type='text' name='email' value='<?php echo $row['email_address']; ?>'></td><td>
                    <input type='text' name='ship' value='<?php echo $row['shipping_address']; ?>'></td><td>
                    <input type='checkbox' name='resetPassword' value='doit'>Reset Password</td><td>
		    <input type='checkbox' name='deleteUser' value='do'><?php if($row['live_account'] == 1) echo "Delete User"; else echo "Restore User";  ?></td><td>
		    <input type=hidden name=direction value=<?php if($row['live_account'] == 2) echo "2"; else echo "0"; ?>>
                    <input class='button' type='submit' value='Commit' name='commit'></td>
                </form></tr>
                <?php
            }
        }
        ?>
    </table>

</main>
<?php include('../php/footer.php'); ?>
</html>
