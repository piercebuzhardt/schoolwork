<?php session_start(); ?>
<!DOCTYPE html>
<html>
<?php
$title = "Create an Account";
include('../php/header.php');
authenticate_admin($db);
?>
<main>
    <form action="../pages/create_account.php" method="post">
        <table style="margin-left: auto; margin-right: auto; margin-top: auto; margin-bottom: auto; border-radius: 4px; padding: 8px;">
            <tr>
                <td>Username:</td>
                <td><input style="float:right;" type="text" name="username" maxlength="30" required autofocus></td>
            </tr>
            <tr>
                <td>Create Password:</td>
                <td><input style="float:right;" type="password" name="password1" maxlength="30" required></td>
            </tr>
            <tr>
                <td>Repeat Password:</td>
                <td><input style="float:right;" type="password" name="password2" maxlength="30" required></td>
            </tr>
            <tr>
                <td>Email Address:</td>
                <td><input style="float:right;" type="text" name="email" maxlength="40" required></td>
            </tr>
            <tr>
                <td>Shipping Address:</td>
                <td><input style="float:right;" type="text" name="shipping" maxlength="40" required></td>
            </tr>
            <tr>
                <td>Role:</td>
                <td><select name="role" required>
                        <option value="driver" selected>Driver</option>
                        <option value="sponsor">Sponsor</option>
                        <option value="admin">Admin</option>
                    </select></td>
            </tr>
            <tr>
                <td><input class='button' name="submit" type="submit" value="Register"></td>
                <td style="float:right;"><input class='button' name="cancel" type="reset" value="Reset"></td>
            </tr>
        </table>
    </form>
</main>

<?php

// Send to homepage once created
if (isset($_POST['submit'])) {
    // Confirm the user entered the same password twice
    if ($_POST['password1'] != $_POST['password2']) {
        footdie("<p class=\"error\">Passwords do not match</p>");
    }
    $password = password_hash("$_POST[password1]", PASSWORD_DEFAULT);
    // Uniqueness checks: Username and email address
    $username = mysqli_real_escape_string($db->db_connect_id, $_POST['username']);
    $email = mysqli_real_escape_string($db->db_connect_id, $_POST['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        footdie("<p class=\"error\">Not a vaild email address</p>");
    }
    $query = "SELECT * FROM account WHERE username='$username'";
    if (!($result = $db->query($query))) {
        footdie("<p class=\"error\">Could not verify availability of your username at this time</p>");
    }
    if (mysqli_num_rows($result) != 0) { // Username is unique if no rows
        footdie("<p class=\"error\">That username has already been taken</p>");
    }
    $query = "SELECT * FROM account WHERE email_address='$email'";
    if (!($result = $db->query($query))) {
        footdie("<p class=\"error\">Could not verify availability of your email at this time</p>");
    }
    if (mysqli_num_rows($result) != 0) { // Email is unique if no rows
        footdie("<p class=\"error\">That email address has already been registered. Did you <a href=\"../pages/password_reset.php\">forget your password</a>?</p>");
    }
    // Need to be added eventually: PFP support
    $shipping = mysqli_real_escape_string($db->db_connect_id, $_POST['shipping']);
    $role = $_POST['role'];
    $query = "INSERT INTO account (username, email_address, shipping_address, hashed_password, role) VALUES ('$username','$email', '$shipping', '$password', '$role')";
    // Valid registration constructed, insert into table
    if ($insert = $db->query($query)) {
        $query = "SELECT account_id FROM account WHERE username='$username'";
        if (!($result = $db->query($query))) {
            footdie("<p class=\"error\">Could not complete registration at this time</p>");
        }
    }
}
include('../php/footer.php'); ?>
</html>
