<?php session_start(); ?>
<!DOCTYPE html>
<html>
<?php $title = 'Admin Tools';
include('../php/header.php'); ?>
<main>
    <?php
    if (isset($role)) {
        if ($role == "admin") {
            echo "<a href='../pages/orders.php'>Manage Orders</a><br>
			<a href='../pages/suspend_login.php'>Suspend Logins</a><br>
			<a href='../pages/force_update.php'>Force Update</a><br>
			<a href='../pages/sponsorApply.php'>Edit Application Information</a><br>
			<a href='../pages/sponsorApplications.php'>Adjust Application Status</a><br>
			<a href='../pages/catalog.php'>Browse Catalogs</a><br>
			<a href='../pages/cart.php";
            if (isset($_GET['org_id'])) echo "?org_id=$_GET[org_id]";
            if (isset($_GET['driver_id'])) {
                if (isset($_GET['org_id'])) echo "&"; else echo "?";
                echo "driver_id=$_GET[driver_id]";
            }
            echo "'>My Cart</a><br>
			<a href='../pages/checkout.php";
            if (isset($_GET['org_id'])) echo "?org_id=$_GET[org_id]";
            if (isset($_GET['driver_id'])) {
                if (isset($_GET['org_id'])) echo "&"; else echo "?";
                echo "driver_id=$_GET[driver_id]";
            }
            echo "'>Checkout</a><br>";
        } else {
            echo "Get out, you don't belong here.";
        }
    } else {
        echo "Log in as an Admin to view";
    }

    ?>

    <?php include('../php/footer.php'); ?>
</html>
