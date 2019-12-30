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
		<a href='../pages/manage_catalog.php'>Browse Ebay Catalog</a><br>
		<a href='../pages/catalog.php'>Browse Your Catalog</a><br>
		<a href='../pages/cart.php<?php
		if(isset($_GET['org_id'])) echo "?org_id=$_GET[org_id]";
		if(isset($_GET['driver_id'])) {
			if(isset($_GET['org_id'])) echo "&"; else echo "?";	
			echo "driver_id=$_GET[driver_id]";
		}
		?>'>My Cart</a><br>
		<a href='../pages/checkout.php<?php
		if(isset($_GET['org_id'])) echo "?org_id=$_GET[org_id]";
		if(isset($_GET['driver_id'])) {
			if(isset($_GET['org_id'])) echo "&"; else echo "?";	
			echo "driver_id=$_GET[driver_id]";
		}
		?>'>Checkout</a><br>
	<?php
        } else {
            echo "<p class=error>You are not associated with any sponsor organization!</p>";
        }
    } else {
        echo "Log in as a Sponsor to view";
    }

    ?>

    <?php include('../php/footer.php'); ?>
</html>
