<!DOCTYPE HTML>
<?php session_start(); ?>
<html>
<?php $title = 'Product orders';
include('../php/header.php'); ?>
<main>
    <?php
    authenticate_admin($db);
    ?>

    <form name='search' method='POST' action='../pages/productorders.php'>
        <table>
            <tr>
                <td><label for='reggie'>Username Like:</label><input name='reggie'
                                                                     type='text' <?php if (isset($_POST['reggie'])) echo "value='$_POST[reggie]'"; ?>>
                <td>
            </tr>
            <tr>
                <td><label for='reggiespon'>Sponsor Org Like:</label><input name='reggiespon'
                                                                            type='text' <?php if (isset($_POST['reggiespon'])) echo "value='$_POST[reggiespon]'"; ?>>
                </td>
            </tr>
            <tr>
                <td><label for='authreggie'>Authorization Account Like:</label><input name='authreggie'
                                                                                      type='text' <?php if (isset($_POST['authreggie'])) echo "value='$_POST[authreggie]'"; ?>>
            <tr>
                <td><label for='itemreggie'>Item Like:</label><input name='itemreggie'
                                                                     type='text' <?php if (isset($_POST['itemreggie'])) echo "value='$_POST[itemreggie]'"; ?>>
                    <input class='button' type='submit'></td>
            </tr>
        </table>
    </form>


    <?php
    // Clearable searches
    if (isset($_POST['search']) && $_POST['reggie'] == '') unset($_POST['reggie']);
    if (isset($_POST['search']) && $_POST['reggiespon'] == '') unset($_POST['reggiespon']);
    if (isset($_POST['search']) && $_POST['authreggie'] == '') unset($_POST['authreggie']);
    if (isset($_POST['search']) && $_POST['itemreggie'] == '') unset($_POST['itemreggie']);

    // Select general SQL or limited SQL based on search (if applicable)
    $sql = "SELECT item_transactions.order_id, item_transactions.point_transaction_id, item_transactions.item_id, item_transactions.quantity_change, 
  sponsor_org.org_name, sponsor_org.points_to_dollars,
  catalog_items.product_name, catalog_items.unit_price, catalog_items.url, 
  point_transactions.point_change_amt, 
  a.username as driver_username, a2.username as auth_username, 
  order_transactions.driver_account_id, order_transactions.auth_account_id, order_transactions.sponsor_org_id, order_transactions.shipping_address, order_transactions.creation_time, order_transactions.fulfill_time, order_transactions.problem,
  catalog_items.unit_price * item_transactions.quantity_change as 'total',
  catalog_items.unit_price * item_transactions.quantity_change * .01 as 'ours'
  
  FROM item_transactions 
  INNER JOIN point_transactions ON item_transactions.point_transaction_id = point_transactions.point_change_id 
  INNER JOIN account a ON point_transactions.driver_account_id = a.account_id
  LEFT OUTER JOIN account a2 ON a2.account_id = point_transactions.auth_account_id
  INNER JOIN sponsor_org ON point_transactions.sponsor_org_id = sponsor_org.sponsor_org_id
  INNER JOIN order_transactions ON item_transactions.order_id = order_transactions.order_id
  INNER JOIN catalog_items ON item_transactions.item_id = catalog_items.catalog_id
  WHERE 1";

    if (isset($_POST['reggie']) && $_POST['reggie'] != '') {
        $sql .= " && a.username RLIKE '$_POST[reggie]'";
    }
    if (isset($_POST['reggiespon']) && $_POST['reggiespon'] != '') {
        $sql .= " && org_name RLIKE '$_POST[reggiespon]'";
    }
    if (isset($_POST['authreggie']) && $_POST['authreggie'] != '') {
        $sql .= " && a2.username RLIKE '$_POST[authreggie]'";
    }
    if (isset($_POST['itemreggie']) && $_POST['itemreggie'] != '') {
        $sql .= " && product_name RLIKE '$_POST[itemreggie]'";
    }
    $sql .= " ORDER BY order_transactions.creation_time DESC";
    // Do the search
    $result = $db->query($sql);
    if (($nrows = mysqli_num_rows($result)) != 0) {
        echo "<table border='1px'>";
        echo "<tr><td>Username</td><td>Authorization Account</td><td>Sponsor Org</td><td>Product</td><td>Quantity</td><td>Unit Price ($)</td><td>Admin cut (1%)</td><td>Point Change</td><td>Shipping Address</td><td>Order Creation Time</td><td>Order Fulfillment Time</td><td>Details</td></tr>\n";
		$our_total = 0;
		$total_cost = 0;
        for ($i = 0; $i < $nrows; $i += 1) {
            $row = mysqli_fetch_array($result);
			$unit = $row['unit_price'];
			$total_cost += number_format($row['total'], 2);
			$our_cut = number_format($row['ours'], 2);

            echo "<tr><td><a href='../pages/viewaccount.php?viewid=" . $row['driver_account_id'] . "'>$row[driver_username]</a></td><td><a href='../pages/viewaccount.php?viewid=" . $row['auth_account_id'] . "'>$row[auth_username]</a></td><td><a href='../pages/view_org.php?view_org=" . $row['sponsor_org_id'] . "'>$row[org_name]</a></td><td><a href=$row[url]>$row[product_name]</a></td><td>$row[quantity_change]</td><td>$unit</td><td>$our_cut</td><td>$row[point_change_amt]</td><td>$row[shipping_address]</td><td>$row[creation_time]</td><td>$row[fulfill_time]</td><td><a href='../pages/order_details.php?order_id=" . $row['order_id'] . "'>View</a></td></tr>\n";
        }
		$our_total = number_format(.01 * $total_cost, 2);
		echo "<tr><td colspan=3></td><td>Totals:</td><td colspan='2'>$$total_cost</td><td>$$our_total</td><td colspan=5></td></tr>\n";
        	echo "</table>";
    }
    ?>

</main>
<?php include('../php/footer.php'); ?>
</html>
