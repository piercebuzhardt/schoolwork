<?php session_start(); ?>
<!DOCTYPE html>
<?php $title = 'Login';
include '../php/header.php'; ?>
<main>
    <?php
    $uname = $psw = $conpsw = "";
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $uname = test_input($_POST["uname"]);
        $psw = test_input($_POST["psw"]);
    }

    function test_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    ?>

    <form method="post" action= <?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>>
        <div class="container">
            <table style="margin-left: auto; margin-right: auto; margin-top: auto; margin-bottom: auto; border-radius: 4px; padding: 8px;">
                <tr>
                    <td>
                        <label for="uname"><b>Username</b></label>
                        <input type="text" placeholder="Enter Username" name="uname" required>
                    </td>
                </tr>

                <tr>
                    <td>
                        <label for="psw"><b>Password</b></label>
                        <input type="password" placeholder="Enter Password" name="psw" required>
                    </td>
                </tr>

                <tr>
                    <td>
                        <button class='button' type="submit">Login</button>
                        <a class='button' href="../pages/password_reset.php">Forgot Password</a>
                        <a class='button' href="../pages/register.php">Register</a>
                    </td>
                </tr>
        </div>
    </form>
    </br>

    <?php
    /* Login invalidations:
       1) 4 invalid passwords within the last 2 hours
       2) No such user
       3) Admin has specified a login suspension via account ban
       4) Password does not match real or temporary password
    */
    // Validate (1)
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $sql = "SELECT * FROM invalid_password_log RIGHT OUTER JOIN account ON account.account_id = invalid_password_log.account_id WHERE account.username = \"$uname\" AND TIMEDIFF(NOW(), invalid_password_log.log_time) < '02:00:00' AND account.live_account = 1";
        $result = $db->query($sql);
        if (mysqli_num_rows($result) > 3) {
            echo "<p class='error'>This account has been temporarily disabled due to frequent unsuccessful login attempts</p>";
        } else {
            // Rest of login checks (2-4)
            $sql = "SELECT account.hashed_password, account.account_id, account.username, account.email_address, IF(account_bans.login_suspended IS NOT NULL AND account_bans.login_suspended > NOW(), 1, 0) AS login_suspended FROM account LEFT OUTER JOIN account_bans ON account.account_id = account_bans.account_id WHERE account.username = \"$uname\" AND account.live_account = 1";
            $result = $db->query($sql);
            if (mysqli_num_rows($result) != 0) {
                $row = $result->fetch_assoc();

                if ($row["login_suspended"] == 1) {
                    echo "<p class='error'>Unable to log in at this time</p>";
                } else if (password_verify($psw, $row["hashed_password"])) {
                    $_SESSION["user_id"] = $row["account_id"];
                    redirect('../index.php');
                } else {
                    // See if a temporary password exists, fetch most recent living one (expires after 2 hours)
                    $sql = "SELECT account.account_id, password_resets.temp_hashed_password, TIMEDIFF(NOW(), password_resets.creation_time) as time_remaining, password_resets.reset_complete FROM password_resets RIGHT OUTER JOIN account ON account.account_id = password_resets.account_id WHERE TIMEDIFF(NOW(), password_resets.creation_time) < '02:00:00' AND account.live_account = 1 ORDER BY TIMEDIFF(NOW(), password_resets.creation_time) ASC";
                    $result = $db->query($sql);
                    if (mysqli_num_rows($result) != 0) { // Temporary password exists
                        $tpassrow = $result->fetch_assoc();
                        // Only allowed to use once
                        if ($tpassrow["reset_complete"] == 0 && password_verify($psw, $tpassrow["temp_hashed_password"])) {
                            $_SESSION["user_id"] = $tpassrow["account_id"];
                            // Mark this reset attempt as used to prevent another use of it
                            $sql = "UPDATE password_resets SET reset_complete = 1 WHERE account_id = '$tpassrow[account_id]' AND temp_hashed_password = '$tpassrow[temp_hashed_password]'";
                            $db->query($sql);
                            // Prompt user to immediately change their real password
                            redirect('../pages/password_reset.php');
                        }
                        // Invalid password log in DB
                        $db->query("INSERT INTO invalid_password_log VALUES ($tpassrow[account_id], NULL)");
                        // Get relevant information to see if email is necessary (5x invalid in last 24 hours
                        $sql = "SELECT COUNT(account_id) AS count FROM invalid_password_log WHERE account_id = $tpassrow[account_id] AND TIMEDIFF(NOW(), log_time) < '24';";
                        $result = $db->query($sql);
                        if (mysqli_num_rows($result) != 0) {
                            $invalid_pass_row = $result->fetch_assoc();
                            if ($invalid_pass_row['count'] == 5) {
                                $subj = 'Security Alert from Wholesale Crocodile';
                                $headers = 'From: WholesaleCrocodile@gmail.com' . "\r\n" . "Reply-To: WholesaleCrocodile@gmail.com" . "\r\n" . "X-Mailer: PHP/" . phpversion();
                                // Compose message
                                $mess = "Dear $row[username],\r\n\r\nSeveral failed attempts to log into your account were detected by our system within a short period of time.\r\nIf you did not fail to log in to your account within the last 24 hours, we would recommend that you change your password.";
                                // Fix long lines in case they exist
                                $message = wordwrap($mess, 70, "\r\n");
                                mail($row['email_address'], $subj, $message, $headers, '-f WholesaleCrocodile@gmail.com');
                            }
                        }
                    }
                    echo "<p class='error'>Unable to log in, please make sure your username and password are entered correctly</p>";
                    // Invalid password log in DB
                    $db->query("INSERT INTO invalid_password_log VALUES ($row[account_id], NULL)");
                    // Get relevant information to see if email is necessary (5x invalid in last 24 hours
                    $sql = "SELECT COUNT(account_id) AS count FROM invalid_password_log WHERE account_id = $row[account_id] AND TIMEDIFF(NOW(), log_time) < '24';";
                    $result = $db->query($sql);
                    if (mysqli_num_rows($result) != 0) {
                        $invalid_pass_row = $result->fetch_assoc();
                        if ($invalid_pass_row['count'] == 5) {
                            $subj = 'Security Alert from Wholesale Crocodile';
                            $headers = 'From: WholesaleCrocodile@gmail.com' . "\r\n" . "Reply-To: WholesaleCrocodile@gmail.com" . "\r\n" . "X-Mailer: PHP/" . phpversion();
                            // Compose message
                            $mess = "Dear $row[username],\r\n\r\nSeveral failed attempts to log into your account were detected by our system within a short period of time.\r\nIf you did not fail to log in to your account within the last 24 hours, we would recommend that you change your password.";
                            // Fix long lines in case they exist
                            $message = wordwrap($mess, 70, "\r\n");
                            mail($row['email_address'], $subj, $message, $headers, '-f WholesaleCrocodile@gmail.com');
                        }
                    }
                }
            } else {
                echo "<p class='error'>That account may not exist</p>";
            }
        }
    }
    ?>

</main>
<?php include('../php/footer.php'); ?>
</html>
