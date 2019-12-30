<?php session_start(); ?>
<!DOCTYPE html>
<html>
<?php $title = 'Sponsor Management Reports & Tools';
include('../php/header.php'); ?>
<main>
    <?php
    if ($role == "sponsor") {
          $sql = "SELECT sponsor_org_id FROM driver_in_org WHERE driver_account_id = $_SESSION[user_id]";
          $result = $db->query($sql);
          if(mysqli_num_rows($result) != 0) {
	?>
		<h3>Reports</h3>
		<a href='../pages/sponsorpointoverview.php'>Point Overview</a><br>
		<a href='../pages/sponsororderoverview.php'>Order Overview</a>
		<br><hr>
		<h3>Tools</h3>
		<a href='../pages/orders.php'>Manage Orders</a><br>
		<a href='../pages/force_update.php'>Force Update</a><br>
		<a href='../pages/sponsorApply.php'>Edit Application Information</a><br>
		<a href='../pages/sponsorApplications.php'>Adjust Application Status</a><br>
		<a href='../pages/userlist.php'>User List</a><br>
	<?php
        } else {
        ?>
		<a href='../pages/sponsorApply.php'>Apply to Organization</a><br>
		<a href='../pages/sponsorApplications.php'>My Applications</a>
        <?php
        }
    } else {
        echo "Log in as a Sponsor to view";
    }
    ?>
    <?php include('../php/footer.php'); ?>
</html>
