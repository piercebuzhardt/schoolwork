<?php session_start(); ?>
<!DOCTYPE html>
<html>
<?php $title = 'Home';
include('../php/header.php'); ?>
<main>
    <!-- Enter your page code inside here -->
    <h3>Alerts</h3>
    <hr>
    <?php
    $query = "SELECT `username`, `sent_time`, `message_contents` FROM `admin_messages`, `account` WHERE account.account_id = sender_id ORDER BY `sent_time` DESC";
    $result = $db->query($query);
    if (!$result || mysqli_num_rows($result) == 0) {
        die ("<p class=\"errorText\">No Admin Alerts Available</p>");
    } else {
        $count = 0;
        $return_string = "";
        while ($count != mysqli_num_rows($result) && $count < 10) {
            for ($i = 0; $i < 4 && $result_row = mysqli_fetch_array($result); $i = $i + 1) {
                $time = $result_row['sent_time'];
                $poster = $result_row['username'];
                $message = $result_row['message_contents'];
                $return_string .= "<b>$poster posted at $time</b><p>&nbsp $message<hr>";
            }
            $count = $count + 1;
        }
        echo "$return_string";
    }
    if ($role == "admin") {
        echo "<h3>Make a new alert</h3>";
        echo "<form name='alert' method='POST' action='$self'>";
        echo "<input type='hidden' name='userid' value='$id'>";
        echo "<input type='text' name='Message' placeholder='Message contents' required>";
        echo "<input class='button' type='submit' name='Alert' value='Post'>";

        if (isset($_POST['Alert'])) {
            $message = $_POST['Message'];
            $userid = $_POST['userid'];
            $query = "INSERT INTO `admin_messages` (`sender_id`, `message_contents`) VALUES ('$userid', '$message')";
            $db->query($query);
            header('Location: home.php');
        }
    }

    ?>
</main>
<?php include('../php/footer.php'); ?>
</html>
