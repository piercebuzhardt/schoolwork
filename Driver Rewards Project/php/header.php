<?php

# Makes sure PHP reports all errors (AWS webserver does not actually do this by default!!)
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

# Always properly include, suppress whichever one of these doesn't work
@include_once('../php/sqlconnect.php');
@include_once('./php/sqlconnect.php');

# Protect form data from SQL injection
safeguard();

echo "<title>";
if(isset($title))
{
	echo $title;
}
else{
	echo "Driver Rewards";
}

echo "</title> ";

?>

<link rel="stylesheet" type="text/css" href="../styles/layout.php"/>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<?php 
# Login Redirect
$pagename = basename($_SERVER['PHP_SELF']);
$no_login_required = array("home.php", "login.php", "register.php", "password_reset.php", "about.php",
                           "contact.php", "support.php", "privacy.php", "terms.php");
if($logged_in == false && !in_array($pagename, $no_login_required)){ redirect('../pages/login.php'); }


// Force password reset if necessary
if($logged_in) {
  $endless_redirect = $db->query("SELECT * FROM password_resets WHERE account_id = $_SESSION[user_id] AND reset_complete IS NULL AND force_reset IS NOT NULL");
  $nrows = mysqli_num_rows($endless_redirect);
  $redirect_lockout = false;
  for($i = 0; $i < $nrows && !$redirect_lockout; $i += 1) {
    $rows = $endless_redirect->fetch_assoc();
    if($rows['reset_complete'] == NULL) {
      $redirect_lockout = true;
    }
  }
  if($redirect_lockout && ($pagename != "password_reset.php" && $pagename != "logout.php")) { redirect('../pages/password_reset.php'); }
}
?>

<body>
<div id="wrapper">
<article id = "testing"><?php displayLogin($logged_in,$login_name) ?></article>
<header><div id="header"><?php if(isset($title)){echo $title;}else{echo "Driver Rewards";} ?></div></header>
<hr>
<nav>
	<a href="../index.php">Home</a>
	
	<?php
	if(!$logged_in) {
		echo "<a href='../pages/login.php'>Log In</a>
			<a href='../pages/register.php'>Register</a>";
	}
	else {
		if($role == 'driver')
		{
			echo "<div class='dropdown'>
			<a class='dropbtn' href='../pages/profile.php'>MyProfile</a>
			<div class='dropdown-content'>
			<a href='../pages/orders.php'>Orders</a>	
			<a href='../pages/phistory.php'>Points</a>
			</div></div>
			<div class='dropdown'>
			<a class='dropbtn' href='../pages/catalog.php'>Shopping</a>
			<div class='dropdown-content'>
			<a href='../pages/catalog.php";
			if(isset($_GET['org_id'])) echo "?org_id=$_GET[org_id]";
			if(isset($_GET['driver_id'])) {
				if(isset($_GET['org_id'])) echo "&"; else echo "?";
				echo "driver_id=$_GET[driver_id]";
			}
			echo "'>Sponsor Catalog</a>
			<a href='../pages/cart.php";
			if(isset($_GET['org_id'])) echo "?org_id=$_GET[org_id]";
			if(isset($_GET['driver_id'])) {
				if(isset($_GET['org_id'])) echo "&"; else echo "?";
				echo "driver_id=$_GET[driver_id]";
			}
			echo "'>My Cart</a>
			<a href='../pages/checkout.php";
			if(isset($_GET['org_id'])) echo "?org_id=$_GET[org_id]";
			if(isset($_GET['driver_id'])) {
				if(isset($_GET['org_id'])) echo "&"; else echo "?";
				echo "driver_id=$_GET[driver_id]";
			}
			echo "'>Checkout</a>
			</div></div>
			<div class='dropdown'>
			Sponsorship
			<div class='dropdown-content'>
			<a href='../pages/sponsorApply.php'>Apply for Sponsor</a>
			<a href='../pages/sponsorApplications.php'>My Applications</a>
			</div></div>";
		}
		else if($role == 'admin')
		{
			echo "
			<a href='../pages/profile.php'>MyProfile</a>
			<div class='dropdown'>
			<a class='dropbtn' href='../pages/admincontrol.php'>Tools</a>
			<div class='dropdown-content'>
			<a href='../pages/orders.php'>Manage Orders</a>
			<a href='../pages/suspend_login.php'>Suspend Logins</a>
			<a href='../pages/force_update.php'>Force Update</a>
			<a href='../pages/sponsorApply.php'>Edit Application Info</a>
			<a href='../pages/sponsorApplications.php'>Adjust Application Status</a>
			<a href='../pages/catalog.php'>Browse catalogs</a>
			<a href='../pages/cart.php";
			if(isset($_GET['org_id'])) echo "?org_id=$_GET[org_id]";
			if(isset($_GET['driver_id'])) {
				if(isset($_GET['org_id'])) echo "&"; else echo "?";
				echo "driver_id=$_GET[driver_id]";
			}
			echo "'>My Cart</a>
			<a href='../pages/checkout.php";
			if(isset($_GET['org_id'])) echo "?org_id=$_GET[org_id]";
			if(isset($_GET['driver_id'])) {
				if(isset($_GET['org_id'])) echo "&"; else echo "?";
				echo "driver_id=$_GET[driver_id]";
			}
			echo "'>Checkout</a>";
			echo "</div></div><div class='dropdown'>";
			echo "<a class='dropbtn' href='../pages/adminreport.php'>Management</a>
			<div class='dropdown-content'>
			<a href='../pages/pointoverview.php'>Point Overview</a>
			<a href='../pages/orderoverview.php'>Order Overview</a>
			<a href='../pages/org_changes.php'>Org Changes</a>
			<a href='../pages/org_statuses.php'>Org Statuses</a>
			<a href='../pages/productorders.php'>Product Orders</a>
			<a href='../pages/userlist.php'>User list</a>
			<a href='../pages/create_account.php'>Create An Account</a>
			<a href='../pages/orgcontrol.php'>Create/Delete Org</a>
			</div></div>";
		}
		else if($role == 'sponsor')
		{
			$sql = "SELECT sponsor_org_id FROM driver_in_org WHERE driver_account_id = $_SESSION[user_id]";
			$result = $db->query($sql);
			if(mysqli_num_rows($result) != 0) {	
			echo "
			<div class='dropdown'>
			<a class='dropbtn' href='../pages/profile.php'>MyProfile</a>
			<div class='dropdown-content'>
			<a href='../pages/view_org.php'>Organization</a>
			</div></div>
			<div class='dropdown'>
			<a class='dropbtn' href='../pages/sponsorTools.php'>Management</a>
			<div class='dropdown-content'>
			<a href='../pages/sponsorpointoverview.php'>Point Overview</a>
			<a href='../pages/sponsororderoverview.php'>Order History</a>
			<a href='../pages/orders.php'>Manage Orders</a>
			<a href='../pages/force_update.php'>Force Update</a>
			<a href='../pages/sponsorApply.php'>Edit Application Info</a>
			<a href='../pages/sponsorApplications.php'>Adjust Application Status</a>
			<a href='../pages/userlist.php'>User List</a>
			</div></div><div class='dropdown'>
			<a class='dropbtn' href='../pages/sponsorCatalog.php";
			if(isset($_GET['org_id'])) echo "?org_id=$_GET[org_id]";
			if(isset($_GET['driver_id'])) {
				if(isset($_GET['org_id'])) echo "&"; else echo "?";	
				echo "driver_id=$_GET[driver_id]";
			}
			echo "'>Catalog</a>
			<div class='dropdown-content'>
			<a href='../pages/manage_catalog.php'>Browse Ebay Catalog</a>
			<a href='../pages/catalog.php";
			if(isset($_GET['org_id'])) echo "?org_id=$_GET[org_id]";
			if(isset($_GET['driver_id'])) {
				if(isset($_GET['org_id'])) echo "&"; else echo "?";	
				echo "driver_id=$_GET[driver_id]";
			}
			echo "'>Browse Your Catalog</a>
			<a href='../pages/cart.php";
			if(isset($_GET['org_id'])) echo "?org_id=$_GET[org_id]";
			if(isset($_GET['driver_id'])) {
				if(isset($_GET['org_id'])) echo "&"; else echo "?";
				echo "driver_id=$_GET[driver_id]";
			}
			echo "'>My Cart</a>
			<a href='../pages/checkout.php";
			if(isset($_GET['driver_id'])) {
				if(isset($_GET['org_id'])) echo "&"; else echo "?";
				echo "driver_id=$_GET[driver_id]";
			}
			echo "'>Checkout</a></div></div>";
			}
			else {
			echo "<a href='../pages/profile.php'>MyProfile</a>
			<div class=dropdown><a class='dropbtn' href='../pages/sponsorTools.php'>Applications</a>
			<div class='dropdown-content'>
			<a href='../pages/sponsorApply.php'>Make an Application</a>
			<a href='../pages/sponsorApplications.php'>My Applications</a></div></div>";	
			}
		}
	}
	?>
	
</nav>
<hr>

