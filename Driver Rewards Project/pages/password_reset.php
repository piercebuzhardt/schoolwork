<?php session_start(); ?>
<!DOCTYPE html>
<html>
<?php $title = 'Password Reset';
include('../php/header.php'); ?>
<main>
    <?php
    // Makes data super safe yay
    function test_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    // Used to create random passwords
    // Pulled from: https://stackoverflow.com/a/31107425
    function generateRandomString($length = 20, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        $pieces = [];
        $max = strlen($keyspace) - 1;
        for ($i = 0; $i < $length; ++$i) {
            // NOTE: random_int() is PHP 7.x compatible and cryptographically secure
            // It is not available for PHP 5.x, and rand() is NOT cryptographically secure
            $pieces [] = $keyspace[rand(0, $max)];
        }
        return implode('', $pieces);
    }

    // Page appears for logged out user, allowing them to send a password reset request
    if (!isset($_SESSION['user_id'])) {
        ?>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="container">
                <label for="email"><b>Email Address</b></label>
                <input type="text" placeholder="Enter Registered Email" name="email" required>
                <button class='button' type="submit">Reset My Password</button>
            </div>
        </form>

        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            # Make input safe
            $account_email = test_input($_POST["email"]);
            # Sanity check: email is an email
            if (!filter_var($account_email, FILTER_VALIDATE_EMAIL)) {
                die("<p class=\"error\">'$account_email' is not a vaild email address</p>");
            }
            # Sanity check: there is an account with this email registered to it
            $query = "SELECT * FROM account WHERE email_address='$account_email'";
            if (!($result = $db->query($query))) {
                die("<p class=\"error\">Could not reset your password at this time</p>");
            }
            if (mysqli_num_rows($result) == 0) { // Email does not exist
                die("<p class=\"error\">'$account_email' has not been registered with us</p>");
            } else { // Email exists! Reset its password to a random value
                $row = $result->fetch_assoc();
                $newpass = generateRandomString();
                $newencrypt = password_hash($newpass, PASSWORD_DEFAULT);
                $query = "INSERT INTO password_resets (account_id, temp_hashed_password) VALUES ('$row[account_id]','$newencrypt')";
                if (!($result = $db->query($query))) {
                    die("<p class=\"error\">Could not reset your password at this time</p>");
                }
                $subj = 'Password Reset';
                $headers = 'From: WholesaleCrocodile@gmail.com' . "\r\n" . "Reply-To: WholesaleCrocodile@gmail.com" . "\r\n" . "X-Mailer: PHP/" . phpversion();
                // Compose message
                $mess = "Dear $row[username],\r\n\r\nYou recently requested to reset your account password for the Wholesale Crocodile Driver Rewards System.\r\n\r\nFor the next two hours, you may log in using the following temporary password: $newpass\r\n\r\nIf you did not request to make this change, please ignore this email or notify the admins by replying to this email.\r\n\r\nUnderstand that your password DOES NOT CHANGE until you create a new password.";
                // Fix long lines in case they exist
                $message = wordwrap($mess, 70, "\r\n");
                mail($account_email, $subj, $message, $headers, '-f WholesaleCrocodile@gmail.com');
            }
            # Complete success
            echo "<p>A recovery password has been sent to $account_email</p>";
        }
    } // Page appears for a logged in user allowing them to change their password immediately
    else {
        $reset_info = $db->query("SELECT * FROM password_resets WHERE account_id = $_SESSION[user_id] AND reset_complete IS NULL");
        if (mysqli_num_rows($reset_info) != 0) {
            ?>
            <p class="warning">You MUST reset your password to continue to use the system normally. This action was
                requested by
                <?php
                $rirrbi = $reset_info->fetch_assoc();
                $rip = $db->query("SELECT role FROM account WHERE account_id = $rirrbi[reset_requested_by_id]");
                $ripi = $rip->fetch_assoc();
                if ($ripi['role'] == 'admin') {
                    echo "an administrator";
                } else {
                    echo "your sponsor";
                }
                ?>.</p>
            <?php
        } else {
            ?>
            <p class="warning">Your password has NOT been changed yet, but the temporary password just used is now
                disabled. Please reset your password now so that you may continue to log in to the system normally.</p>
            <?php
        }
        ?>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="container">
                <label for="p1"><b>New Password</b></label>
                <input type="password" placeholder="Enter New Password" name="p1" required>
                <label for="p2"><b>Confirm New Password</b></label>
                <input type="password" placeholder="Confirm Password" name="p2" required>

                <button class='button' type="submit">Set New Password</button>
            </div>
        </form>

        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Safely make sure passwords match
            $p1 = test_input($_POST["p1"]);
            $p2 = test_input($_POST["p2"]);
            if ($p1 != $p2) {
                die("<p class='error'>Passwords do not match!</p>");
            } else {
                // Reset password, then go home
                $newhash = password_hash($p1, PASSWORD_DEFAULT);
                $setPass = $db->query("UPDATE account SET hashed_password = '$newhash' WHERE account_id = '$_SESSION[user_id]'");
                // Free forcibly reset users from their torture
                if (isset($reset_info)) {
                    $setComplete = $db->query("UPDATE password_resets SET reset_complete = 1 WHERE account_id = '$_SESSION[user_id]' AND reset_complete IS NULL");
                }
                if (!$setPass || (isset($setComplete) && !$setComplete)) {
                    die("<p class='error'>Could not set your new password at this time!</p>");
                } else {
                    redirect('../index.php');
                }
            }
        }
    }
    ?>

</main>
<?php include('../php/footer.php'); ?>
</html>
