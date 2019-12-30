<?php

@include_once('./mysqlClass.php');
@include_once('../php/mysqlClass.php');
@include_once('../php/ebayManager.php');

$session_name = "user";
$id = NULL;
$logged_in = false;
$login_name = "";
$role = "user";
if (!isset($_SESSION["user_id"])) {
} else {
    $id = $_SESSION["user_id"];
    $sql = "SELECT username,role FROM account WHERE account_id = '$id'";
    $result = $db->query($sql);
    if ($row = $result->fetch_assoc()) {
        $logged_in = true;
        $login_name = $row["username"];
        $role = $row["role"];
    }
}

//Should probably be condensed into one function which I will do sometime
function displayLogin($logged_in, $login_name)
{
    if ($logged_in == true) {
        echo "Logged in as " . $login_name . "<a class='button' href='../pages/logout.php'> Logout</a>";
    } else {
        echo "<a class='button' href=\"./login.php\">Login</a>";
        echo "	<a class = 'button' href=\"./register.php\">Register</a>";
    }
}

function logout()
{
    if (isset($_SESSION['user_id'])) {
        session_unset();
        session_destroy();
        echo "<a class='button' href='../pages/login.php'>Login</a><a href='../pages/register.php'>Register</a>";
    }
    redirect('../index.php');
}

function redirectLogin($logged_in, $login_name)
{
    if ($logged_in == true) {
        echo "Logged in as " . $login_name . "<a class='button' href='../php/logout.php'> Logout</a>";
    } else {
        header("Location: ../pages/login.php");
        die();
    }
}

function redirect($url, $statusCode = 303)
{
    header('Location: ' . $url, true, $statusCode);
    die();
}

function authenticate_admin($data, $kill = 1)
{
    $lockout = isset($_SESSION['user_id']);
    if ($lockout) {
        $lockout = 0;
        $sql = "SELECT role FROM account WHERE account_id = '$_SESSION[user_id]'";
        $result = $data->query($sql);
        if (mysqli_num_rows($result) == 0) {
            $lockout = 1;
        } else {
            $role = $result->fetch_assoc();
            $lockout = ($role['role'] != "admin");
        }
    }
    // KILL THE PAGE AHORITA
    if ($lockout && $kill) {
        echo "<p class='error'>YOU MUST BE AN ADMIN TO VIEW THIS PAGE</p> </main>";
        include('../php/footer.php');
        echo "</html>";
        die();
    } else if ($lockout) {
        return 0;
    }
    return 1;
}

function authenticate_sponsor($data, $kill = 1)
{
    $lockout = isset($_SESSION['user_id']);
    if ($lockout) {
        $lockout = 0;
        $sql = "SELECT role FROM account WHERE account_id = '$_SESSION[user_id]'";
        $result = $data->query($sql);
        if (mysqli_num_rows($result) == 0) {
            $lockout = 1;
        } else {
            $role = $result->fetch_assoc();
            $lockout = ($role['role'] != "sponsor");
        }
    }
    // KILL THE PAGE AHORITA
    if ($lockout && $kill) {
        echo "<p class='error'>YOU MUST BE A SPONSOR TO VIEW THIS PAGE</p> </main>";
        include('../php/footer.php');
        echo "</html>";
        die();
    } else if ($lockout) {
        return 0;
    }
    return 1;
}

function getSponsorOrgId($database, $userid)
{
    $query = "SELECT sponsor_org_id FROM driver_in_org WHERE driver_account_id = $userid";
    $qresult = $database->query($query);
    if(mysqli_num_rows($qresult) != 0) {
      $row = $qresult->fetch_assoc();
      return $row['sponsor_org_id'];
    }
    footdie("<p class=error>You are not associated with any sponsor organization!</p>");
}

function footdie($string)
{
    echo "$string";
    include('../php/footer.php');
    die();
}

function safeguard()
{
    foreach ($_POST as $key => $value) {
        #echo "BEFORE: $key => $value\n<br>";
        $value = mysqli_real_escape_string($GLOBALS['db']->db_connect_id, $value);
        #echo "AFTER: $key => $value\n<br>";
    }
    foreach ($_GET as $key => $value) {
        #echo "BEFORE: $key => $value\n<br>";
        $value = mysqli_real_escape_string($GLOBALS['db']->db_connect_id, $value);
        #echo "AFTER: $key => $value\n<br>";
    }
}

?>
