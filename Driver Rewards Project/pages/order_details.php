<!DOCTYPE HTML>
<?php session_start(); ?>
<html>
<?php $title = 'Order details';
include('../php/header.php'); ?>
<main>
  <?php
    if (isset($_GET['order_id'])) {
        $orderid = $_GET['order_id'];

        if ($role == "sponsor") {
            $me = $_SESSION['user_id'];
            $query = "SELECT `sponsor_org_id` FROM `driver_in_org` WHERE `driver_account_id` = $me";
            $result = $db->query($query);
            $result_row = mysqli_fetch_array($result);
            $my_org = $result_row['sponsor_org_id'];
            $query = "SELECT * FROM `order_transactions` WHERE `sponsor_org_id` = $my_org && `order_id` = $orderid";
            $result = $db->query($query);
            if (!$result || mysqli_num_rows($result) == 0) $correct_sponsor = false;
            else $correct_sponsor = true;
        }
        if ($role == "driver") {
            $me = $_SESSION['user_id'];
            $query = "SELECT * FROM `order_transactions` WHERE `driver_account_id` = $me && `order_id` = $orderid";
            $result = $db->query($query);
            if (!$result || mysqli_num_rows($result) == 0) $correct_user = false;
            else $correct_user = true;
        }

        if ($role == "admin" || $correct_sponsor || $correct_user) {
            $orderid = $_GET['order_id'];

            // Select general SQL or limited SQL based on search (if applicable)
            $sql = "SELECT item_transactions.order_id, item_transactions.point_transaction_id, item_transactions.item_id, item_transactions.quantity_change, 
  sponsor_org.org_name, 
  catalog_items.product_name, catalog_items.unit_price, catalog_items.url, 
  point_transactions.point_change_amt, 
  a.username as driver_username, a2.username as auth_username, 
  order_transactions.driver_account_id, order_transactions.auth_account_id, order_transactions.sponsor_org_id, order_transactions.shipping_address, order_transactions.creation_time, order_transactions.fulfill_time, order_transactions.problem, order_transactions.complain
  
  FROM item_transactions 
  INNER JOIN point_transactions ON item_transactions.point_transaction_id = point_transactions.point_change_id 
  INNER JOIN account a ON point_transactions.driver_account_id = a.account_id
  LEFT OUTER JOIN account a2 ON a2.account_id = point_transactions.auth_account_id
  INNER JOIN sponsor_org ON point_transactions.sponsor_org_id = sponsor_org.sponsor_org_id
  INNER JOIN order_transactions ON item_transactions.order_id = order_transactions.order_id
  INNER JOIN catalog_items ON item_transactions.item_id = catalog_items.catalog_id
  WHERE $orderid = item_transactions.order_id";


            // Do the search
            $result = $db->query($sql);
            if (($nrows = mysqli_num_rows($result)) != 0) {
                echo "<table border='1px'>";
                $row = mysqli_fetch_array($result);
                echo "<tr><td>Shipping Address</td><td>Order Creation Time</td><td>Order Fulfillment Time</td><td>Problem?</td><td colspan=2>Complaint?</td></tr>";
                echo "<tr><td>$row[shipping_address]</td><td>$row[creation_time]</td><td>";
		if($row['fulfill_time'] != NULL) echo $row['fulfill_time'];
		else echo "To be delivered";
		echo "</td><td>$row[problem]</td><td colspan=2>$row[complain]</td></tr></table><br>";
                echo "<table border=1px><tr><td>Username</td><td>Authorization Account</td><td>Sponsor Org</td><td>Product</td><td>Quantity</td><td>Point Change</td></tr>";
                for ($i = 0; $i < $nrows; $i += 1) {
                    echo "<tr><td><a href='../pages/viewaccount.php?viewid=" . $row['driver_account_id'] . "'>$row[driver_username]</a></td><td><a href='../pages/viewaccount.php?viewid=" . $row['auth_account_id'] . "'>$row[auth_username]</a></td><td><a href='../pages/view_org.php?view_org=" . $row['sponsor_org_id'] . "'>$row[org_name]</a></td><td><a href=$row[url]>$row[product_name]</a></td><td>$row[quantity_change]</td><td>$row[point_change_amt]</td></tr>\n";
                    $row = mysqli_fetch_array($result);
                }
                echo "</table>";
            }
        } else {
            echo "You do not have access to this information.";
        }
    } else {
        header("Location: home.php");
    }
    ?>
</main>
<?php include('../php/footer.php'); ?>
</html>
