<?php session_start(); ?>
<!DOCTYPE html>
<html>
<?php $title = "Orders";
include('../php/header.php'); ?>
<main>
    <?php
    // Build redirect link for sponsors/admins
    if (isset($_GET['viewid'])) {
        $rd_link = '../pages/orders.php?viewid=' . $_GET['viewid'];
    } else {
        $rd_link = '../pages/orders.php';
    }

    // Form processing
    if ($role != 'driver') {
      if($role == 'sponsor') {
        // Make sure they are in an org
        $org = getSponsorOrgId($db, $_SESSION['user_id']);
        $sql = "SELECT account_id, username FROM account WHERE role='driver' AND account_id IN (SELECT driver_account_id FROM driver_in_org WHERE sponsor_org_id IN (SELECT sponsor_org_id FROM driver_in_org WHERE driver_account_id = $_SESSION[user_id])) AND live_account = 1 AND account_id IN (SELECT driver_account_id FROM order_transactions)";
      }
      else {
        $sql = "SELECT account_id, username FROM account WHERE role='driver' AND live_account = 1 AND account_id IN (SELECT driver_account_id FROM order_transactions)";
      }
      $result = $db->query($sql);
      if(mysqli_num_rows($result) == 0) {
      footdie("No drivers have made any orders!");
      }
      else {
      echo "<form method=GET action='$rd_link'><select name=viewid>\n";
      while ($row = $result->fetch_assoc()) {
        if (!isset($_GET['viewid'])) $_GET['viewid'] = $row['account_id'];
        echo "\t<option name=driver value=$row[account_id]";
        if ((!isset($_GET['viewid']) && $selected_driver = $row['account_id']) || $_GET['viewid'] == $row['account_id']) {
          echo " selected";
          if (isset($_GET['viewid'])) $selected_driver = $_GET['viewid'];
        }
        echo ">$row[username]</option>\n";
      }
      echo "</select>\n";
      echo "<input type=submit class=button value=\"Change Driver\"></form><br>";
      }
    }

    if (isset($_POST['change_shipping'])) {
      $sql = "UPDATE order_transactions SET shipping_address = \"$_POST[newShipping]\" WHERE order_id = $_POST[order_id]";
      if(!$db->query($sql)) echo "<p class=error>An error occurred while changing your shipping address</p>";
    }
    if (isset($_POST['submitComplaint'])) {
      $sql = "UPDATE order_transactions SET complain = \"$_POST[complaint]\" WHERE order_id = $_POST[order_id]";
      if(!$db->query($sql)) echo "<p class=error>An error occurred while submitting your complaint, please try again</p>";
      else echo "Successfully filed your complaint";
    }
    if (isset($_POST['complain'])) {
      echo "We're sorry that you had a poor experience, please let us know what we could improve for you:<br>\n";
      ?>
	<form method=POST action='../pages/orders.php'>
	<textarea rows=4 cols=80 name=complaint required autofocus style="resize: none;"></textarea><br><br>
	<input type=submit class=button name=submitComplaint value="Submit Complaint">
	<input type=hidden name=order_id value=<?php echo "$_POST[order_id]"; ?>>
	<a class=button href='../pages/orders.php'>Cancel</a>
	</form><br>
            <?php
            $sql = "SELECT creation_time, org_name, shipping_address, TIMESTAMPDIFF(Day, NOW(), creation_time) as remain_time, complain FROM order_transactions INNER JOIN sponsor_org ON order_transactions.sponsor_org_id = sponsor_org.sponsor_org_id WHERE order_id = $_POST[order_id]";
            $result = $db->query($sql);
            $row = $result->fetch_assoc();
            ?>
        <table border=1px>
            <tr>
                <td>Placed On</td>
                <td>Sponsor</td>
                <td>Shipping Address</td>
            </tr>
            <?php
            echo "<tr><td>$row[creation_time]</td><td>$row[org_name]</td><td>$row[shipping_address]</td></tr>\n";
            $complaint = $row['complain'];
            echo "</table>\n<br>";

            // Items in order
            echo "<table border=1px><tr><td>Points Spent</td><td>Item</td><td>Quantity</td></tr>\n";
            $sql = "SELECT point_change_amt, point_change_id, quantity_change, url, product_name
FROM item_transactions INNER JOIN point_transactions ON item_transactions.point_transaction_id = point_transactions.point_change_id
INNER JOIN catalog_items ON  item_transactions.item_id = catalog_items.catalog_id
WHERE item_transactions.order_id = $_POST[order_id]";
            $result = $db->query($sql);
            while ($row = $result->fetch_assoc()) {
                echo "<tr><td>$row[point_change_amt]</td><td><a href='$row[url]'>$row[product_name]</a></td><td>$row[quantity_change]</td></tr>\n";
            }
            ?>
        </table><br>
    <?php
      if(isset($complaint)) echo "Complaint on File:<br>$complaint";
      else echo "No complaints currently filed";
      echo "<br><br><a class=button href='../pages/orders.php'>Return to all orders</a>";
      footdie("");
    }

    if (isset($_POST['cancel'])) {
        ?>
        <table border=1px>
            <?php
            $sql = "SELECT creation_time, org_name, shipping_address, TIMESTAMPDIFF(Day, NOW(), creation_time) as remain_time FROM order_transactions INNER JOIN sponsor_org ON order_transactions.sponsor_org_id = sponsor_org.sponsor_org_id WHERE order_id = $_POST[order_id]";
            $result = $db->query($sql);
            $row = $result->fetch_assoc();
            ?>
            <tr>
                <td>Placed On</td>
                <td>Sponsor</td>
                <td>Shipping Address</td>
		<?php if($row['remain_time'] > -1) echo "<td>Change Shipping Address</td>"; ?>
            </tr>
            <?php echo "<tr><td>$row[creation_time]</td><td>$row[org_name]</td><td>";
            if($row['remain_time'] > -1) {
              echo "<input type=hidden name=cancel value=1><input type=hidden name=order_id value=$_POST[order_id]><input type=text name=newShipping value=\"";
            }
            echo $row['shipping_address'];
            if($row['remain_time'] > -1) echo "\"></td><td><input type=submit name=change_shipping value=Change class=button>";
            echo "</td></tr><tr></tr>\n";
            echo "</table>\n";

            // Cancel items in order
            if (isset($_POST['cancelItem'])) {
                $sql = "DELETE FROM point_transactions WHERE point_change_id = $_POST[point_id]";
                if (!$result = $db->query($sql)) mysqli_error($db->db_connect_id);
                // See if this cascades to delete the entire order (cascades out items)
                $sql = "SELECT point_transaction_id FROM item_transactions WHERE order_id = $_POST[order_id]";
                $result = $db->query($sql);
                if (mysqli_num_rows($result) == 0) {
                    $sql = "DELETE FROM order_transactions WHERE order_id = $_POST[order_id]";
                    $db->query($sql);
                    redirect($rd_link);
                }
            }

            echo "<table border=1px><tr><td>Point Refund Available</td><td>Item</td><td>Quantity</td><td>Cancel Just Item</td></tr>\n";
            $sql = "SELECT point_change_amt, point_change_id, quantity_change, url, product_name
FROM item_transactions INNER JOIN point_transactions ON item_transactions.point_transaction_id = point_transactions.point_change_id
INNER JOIN catalog_items ON  item_transactions.item_id = catalog_items.catalog_id
WHERE item_transactions.order_id = $_POST[order_id]";
            $result = $db->query($sql);
            while ($row = $result->fetch_assoc()) {
                echo "<tr><td>$row[point_change_amt]</td><td><a href='$row[url]'>$row[product_name]</a></td><td>$row[quantity_change]</td>";
                echo "<td><form method=POST action=$rd_link><input type=hidden name=order_id value=$_POST[order_id]>";
                echo "<input type=hidden name=point_id value=$row[point_change_id]><input type=hidden name=cancel value=1>";
                echo "<input type=submit class=button name=cancelItem value='Cancel All of this Item'></form></td></tr>\n";
            }
            ?>
        </table><br>
        Really cancel this order?
        <form method=POST action='<?php echo "$rd_link"; ?>'>
            <input type=hidden name=order_id value=<?php echo "$_POST[order_id]"; ?>>
            <input type=submit name=rlyCancel value="Yes, cancel the entire order" class=button>
            <input type=submit name=nope value="Do not cancel my order" class=button>
        </form>
        <?php
        footdie("");
    }
    if (isset($_POST['rlyCancel'])) {
        $sql = "DELETE FROM point_transactions WHERE point_change_id IN (SELECT point_transaction_id FROM item_transactions WHERE order_id = $_POST[order_id])";
        $db->query($sql);
        $sql = "DELETE FROM order_transactions WHERE order_id = $_POST[order_id]";
        $db->query($sql);
        redirect($rd_link);
    }
    ?>
    <h3>Past orders</h3>
    <?php
    if (isset($_SESSION['user_id'])) {
        $id = $_SESSION['user_id'];
        if (isset($_GET['viewid'])) $id = $_GET['viewid'];

        if ($role == "sponsor") {
            $me = $_SESSION['user_id'];
            $query = "SELECT `sponsor_org_id` FROM `driver_in_org` WHERE `driver_account_id` = $me";
            $result = $db->query($query);
            $result_row = mysqli_fetch_array($result);
            $my_org = $result_row['sponsor_org_id'];
            $query = "SELECT * FROM `driver_in_org` WHERE `sponsor_org_id` = $my_org && `driver_account_id` = $id";
            $result = $db->query($query);
            if (!$result || mysqli_num_rows($result) == 0) $correct_sponsor = false;
            else $correct_sponsor = true;
        }

        if ($role == "admin" || $_SESSION['user_id'] == $id || $correct_sponsor) {

            $query = "SELECT `order_transactions`.`order_id`, `order_transactions`.`auth_account_id`, `order_transactions`.`shipping_address`, `order_transactions`.`creation_time`,  `order_transactions`.`fulfill_time`, `sponsor_org`.`org_name`, `order_transactions`.`problem`, TIMESTAMPDIFF(DAY, NOW(), `order_transactions`.`creation_time`) AS `remaining_days` FROM `order_transactions` INNER JOIN `sponsor_org` ON `sponsor_org`.`sponsor_org_id` = `order_transactions`.`sponsor_org_id` WHERE `order_transactions`.`driver_account_id` = $id ORDER BY `order_transactions`.`creation_time` DESC";
            $result = $db->query($query);
            if (!$result || mysqli_num_rows($result) == 0) {
                echo("<p class=\"errorText\">No Past Orders Made</p>");
            } else {
                $count = 0;
                $return_string = "<table border =\"1\">";
                $return_string .= "<tr><td>Placed on</td><td>Fulfilled on</td><td>Sponsor catalog</td><td>Shipping address</td><td>Edit Order</td><td>View Details</td><td>File a Complaint</td></tr>\n";
                while ($count != mysqli_num_rows($result)) {
                    $result_row = mysqli_fetch_array($result);
                    $address = $result_row['shipping_address'];
                    $creation_date = $result_row['creation_time'];
                    $org_name = $result_row['org_name'];
                    $fulfill_date = $result_row['fulfill_time'];
                    if ($result_row['problem'] != NULL) $fulfill_date = $result_row['problem'];
                    if ($fulfill_date == NULL) $fulfill_date = "To be delivered";
                    $return_string .= "<tr><td>$creation_date</td><td>$fulfill_date</td><td>$org_name</td><td>$address</td>";
                    if ($result_row['remaining_days'] > -2) {
                        $return_string .= "\n<td><form method=POST action='$rd_link'><input type=submit name=cancel value=Edit class=button onclick=\"cancelWarning();\"><input type=hidden name=order_id value=$result_row[order_id]></form></td>";
                    } else $return_string .= "<td>N/A</td>";
                    $return_string .= "<td><a href='../pages/order_details.php?order_id=$result_row[order_id]'>View</a></td>\n";
                    $return_string .= "<td>";
                    if($result_row['remaining_days'] > -14 && $result_row['remaining_days'] <= -3) {
                      $return_string .= "<form method=POST action='$rd_link'><input type=submit name=complain value=Complaint class=button>\n";
                      $return_string .= "<input type=hidden name=order_id value=$result_row[order_id]></form>";
                    }
                    else $return_string .= "N/A";
                    $return_string .= "</td></tr>\n";
                    $count = $count + 1;
                }
                $return_string .= "</table><br/>";
                echo "$return_string";
            }
        }
    }
    ?>
</main>
<?php include('../php/footer.php'); ?>
</html>
