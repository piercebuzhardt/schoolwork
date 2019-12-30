<?php session_start(); ?>
<!DOCTYPE html>
<html>
<?php $title = "Checkout";
include('../php/header.php'); ?>
<main>
    <?php
    // Build Selectors
    if($role != 'sponsor') {
	$sql = "SELECT COUNT(*) as count FROM sponsor_org WHERE live_org = 1";
	$result = $db->query($sql);
	$row = $result->fetch_assoc();
	if($row['count'] == 0) footdie("There are no organizations with active catalogs at this time!");
    ?>
    <table border=1px>
        <tr>
            <td colspan=2>Select <?php if ($role == 'admin') echo "Sponsor & Driver"; else echo "Sponsor"; ?> </td>
        </tr>
        <?php
        if ($role == 'driver') {
            $sql = "SELECT * FROM sponsor_org WHERE sponsor_org_id IN (SELECT sponsor_org_id FROM driver_in_org WHERE driver_account_id = $_SESSION[user_id])";
        } else { // admin
            $sql = "SELECT DISTINCT sponsor_org.sponsor_org_id, org_name FROM sponsor_org INNER JOIN driver_in_org ON driver_in_org.sponsor_org_id = sponsor_org.sponsor_org_id WHERE sponsor_org.live_org = 1 HAVING 0 < (SELECT COUNT(*) FROM driver_in_org INNER JOIN account ON account.account_id = driver_in_org.driver_account_id WHERE account.role = 'driver')";
        }
        if (!($result = $db->query($sql))) footdie("</table>Unable to complete request at this time!");
        echo "<form method=GET action='../pages/checkout.php'><tr><td><select name=org_id>\n";
        while ($row = $result->fetch_assoc()) {
            echo "\t<option name=org value=$row[sponsor_org_id]";
            if ((!isset($_GET['org_id']) && !isset($selected_org)) || (isset($_GET['org_id']) && $_GET['org_id'] == $row['sponsor_org_id'])) {
                echo " selected";
                if (isset($_GET['org_id'])) $selected_org = $_GET['org_id'];
                else if (!isset($selected_org)) $selected_org = $row['sponsor_org_id'];
            }
            echo ">$row[org_name]</option>\n";
        }
        echo "</select></td>\n";
        echo "<td><input type=submit class=button value=\"Change Sponsor Org\"></td></tr></form>\n";

        // Admin/Sponsor User Selector
        if ($role != 'driver') {
            $sql = "SELECT account_id, username FROM account WHERE role='driver' AND account_id IN (SELECT driver_account_id FROM driver_in_org WHERE sponsor_org_id = $selected_org) AND live_account = 1";
            $result = $db->query($sql);
            echo "<form method=GET action='../pages/checkout.php'><tr><td><select name=driver_id>\n";
            while ($row = $result->fetch_assoc()) {
                if (!isset($_GET['driver_id'])) $selected_driver = $row['account_id'];
                echo "\t<option name=driver value=$row[account_id]";
                if ((!isset($_GET['driver_id']) && $selected_driver = $row['account_id']) || $_GET['driver_id'] == $row['account_id']) {
                    echo " selected";
                    if (isset($_GET['driver_id'])) $selected_driver = $_GET['driver_id'];
                }
                echo ">$row[username]</option>\n";
            }
            echo "</select></td>\n";
            echo "<td><input type=hidden name=org_id value=$selected_org><input type=submit class=button value=\"Change Driver\"></td></tr></form>";
        } else {
            $selected_driver = $_SESSION['user_id'];
        }

        if (!isset($_POST['order'])) {
            $sql = "SELECT SUM(point_change_amt) AS points FROM point_transactions WHERE sponsor_org_id = $selected_org AND driver_account_id = $selected_driver";
            $result = $db->query($sql);
            $row = $result->fetch_assoc();
            if (($pointsMax = (float)$row['points']) == NULL) $pointsMax = 0;
            echo "<tr><td>Available Points:</td><td>$pointsMax</td></tr>";
        }
        ?>
    </table>
    <br>
    <?php
    }
    else { // Set sponsor selected org
    $sql = "SELECT d.sponsor_org_id FROM driver_in_org as d INNER JOIN sponsor_org as s ON d.sponsor_org_id = s.sponsor_org_id WHERE driver_account_id = $_SESSION[user_id] AND live_org = 1";
    if (!($result = $db->query($sql))) footdie("ERROR: Could not fetch your org at this time!");
    if (mysqli_num_rows($result) == 0) footdie("<p class=error>You are not associated with any active sponsor organization!</p>");
    $row = $result->fetch_assoc();
    $selected_org = $row['sponsor_org_id'];
    ?>
    <form method=GET action='../pages/checkout.php'>
        <table border=1px>
            <tr>
                <td colspan=2>Select Driver</td>
            </tr>
            <?php
            $sql = "SELECT account_id, username FROM account WHERE role='driver' AND account_id IN (SELECT driver_account_id FROM driver_in_org WHERE sponsor_org_id = $selected_org)";
            $result = $db->query($sql);
            if(mysqli_num_rows($result) == 0 ) {
              echo "<tr><td>There are no drivers in your organization at this time!</td></tr></table><br>";
              $pointsMax = 0;
            }
            else {
            echo "<tr><td><select name=driver_id>\n";
            while ($row = $result->fetch_assoc()) {
                if (!isset($_GET['driver_id'])) $selected_driver = $row['account_id'];
                echo "\t<option name=driver value=$row[account_id]";
                if ((!isset($_GET['driver_id']) && $selected_driver = $row['account_id']) || $_GET['driver_id'] == $row['account_id']) {
                    echo " selected";
                    if (isset($_GET['driver_id'])) $selected_driver = $_GET['driver_id'];
                }
                echo ">$row[username]</option>\n";
            }
            echo "</select></td>\n";
            echo "<td><input type=submit class=button value=\"Change Driver\"></td></tr>";

            $sql = "SELECT SUM(point_change_amt) AS points FROM point_transactions WHERE sponsor_org_id = $selected_org AND driver_account_id = $selected_driver";
            $result = $db->query($sql);
            $row = $result->fetch_assoc();
            if (($pointsMax = $row['points']) == NULL) $pointsMax = 0;
            echo "<tr><td>Available Points:</td><td>$pointsMax</td></tr></table><br>";
            }
            }
            $sql = "SELECT points_to_dollars FROM sponsor_org WHERE sponsor_org_id = $selected_org";
            $result = $db->query($sql);
            $pointConversion = $result->fetch_assoc();
            $pointConversion = $pointConversion['points_to_dollars'];
            $sum = 0;

            // Process order
            if (isset($_POST['order'])) {
                // Build Order
                $sql = "INSERT INTO order_transactions (driver_account_id, auth_account_id, sponsor_org_id, shipping_address) VALUES ($selected_driver, $_SESSION[user_id], $selected_org, \"$_POST[address]\")";
                $db->query($sql);
                // Retrieve new order id
                $sql = "SELECT order_id FROM order_transactions WHERE driver_account_id = $selected_driver AND auth_account_id = $_SESSION[user_id] AND sponsor_org_id = $selected_org AND shipping_address = \"$_POST[address]\" ORDER BY creation_time DESC LIMIT 1";
                $result = $db->query($sql);
                $row = $result->fetch_assoc();
                $newOrder = $row['order_id'];
                // Build point transactions
                $sql = "SELECT driver_cart.quantity, catalog_items.unit_price FROM driver_cart INNER JOIN catalog_items ON driver_cart.catalog_id = catalog_items.catalog_id WHERE driver_account_id = $_SESSION[user_id]";
                $result = $db->query($sql);
                $sql = "INSERT INTO point_transactions (driver_account_id, auth_account_id, sponsor_org_id, point_change_amt, change_reason) VALUES ";
                while ($row = $result->fetch_assoc()) {
                    $amt = $row['quantity'] * $row['unit_price'] * -1 * $pointConversion;
                    $sql .= "($selected_driver, $_SESSION[user_id], $selected_org, $amt, \"Order #$newOrder\"), ";
                }
                $db->query(rtrim($sql, ", "));
                // Build item transactions
                $sql = "SELECT point_change_id FROM point_transactions WHERE change_reason = \"Order #$newOrder\"";
                $result1 = $db->query($sql);
                $pts = array();
                while ($row1 = $result1->fetch_assoc()) array_push($pts, $row1['point_change_id']);
                $sql = "SELECT driver_cart.quantity, catalog_items.catalog_id
FROM driver_cart INNER JOIN catalog_items ON driver_cart.catalog_id = catalog_items.catalog_id
WHERE driver_cart.driver_account_id = $_SESSION[user_id]";
                $result2 = $db->query($sql);
                $sql2 = "INSERT INTO item_transactions (order_id, point_transaction_id, item_id, quantity_change) VALUES ";
                $index = 0;
                while ($row2 = $result2->fetch_assoc()) {
                    $sql2 .= "($newOrder, " . $pts[$index] . ", $row2[catalog_id], $row2[quantity]), ";
                    $index += 1;
                }
                $db->query(rtrim($sql2, ", "));
                // Empty cart
                $sql = "DELETE FROM driver_cart WHERE driver_account_id = $_SESSION[user_id]";
                $db->query($sql);
            }

            $sql = "SELECT * FROM driver_cart WHERE driver_account_id = $_SESSION[user_id] AND catalog_id IN (SELECT catalog_id FROM catalog_items WHERE sponsor_org_id = $selected_org)";
            if (!($result = $db->query($sql))) {
                footdie("Could not fetch your cart at this time!");
            }
            if (mysqli_num_rows($result)) {
            // Build the cart table
            ?>
            The following items are in your cart, ready to be checked out:
            <br><br>
            <table border=1px>
                <tr>
                    <td>Item Name</td>
                    <td>Item Quantity</td>
                    <td>Points</td>
                </tr>
                <?php
                while ($row = $result->fetch_assoc()) {
                    $sql = "SELECT product_name, url, product_visible, product_deleted, unit_price FROM catalog_items WHERE catalog_id = $row[catalog_id]";
                    $nameResult = $db->query($sql);
                    $nameRow = $nameResult->fetch_assoc();
                    $rowPrice = $nameRow['unit_price'] * $row['quantity'] * $pointConversion;
                    $sum += $rowPrice;
                    if ($nameRow['product_visible'] != 1 || $nameRow['product_deleted'] == 1) continue;
                    echo "<tr><td><a href='$nameRow[url]'>$nameRow[product_name]</a></td><td>x$row[quantity]</td><td>$rowPrice</td></tr>\n";
                }
                echo "<tr><td>SUM TOTAL</td><td colspan=2 style=\"text-align: center;\">$sum points</td></tr>"
                ?>
            </table>
    </form>
    <br>If you need to make changes,
    <a class=button href=
        <?php
        echo "'../pages/cart.php";
        if (isset($_GET['org_id'])) echo "?org_id=$_GET[org_id]";
        if (isset($_GET['driver_id'])) {
            if (isset($_GET['org_id'])) echo "&"; else echo "?";
            echo "driver_id=$_GET[driver_id]";
        }
        echo "'"; ?>>Return to your cart</a>
    or <a class=button href=
        <?php
        echo "'../pages/catalog.php";
        if (isset($_GET['org_id'])) echo "?org_id=$_GET[org_id]";
        if (isset($_GET['driver_id'])) {
            if (isset($_GET['org_id'])) echo "&"; else echo "?";
            echo "driver_id=$_GET[driver_id]";
        }
        echo "'"; ?>>Return to the catalog</a>
    <br><br>
    <!-- CHECKOUT FORM -->
    <?php
    if($sum <= $pointsMax) {
    ?>
    <form method=POST action='../pages/checkout.php<?php
    if (isset($_GET['org_id'])) echo "?org_id=$_GET[org_id]";
    if (isset($_GET['driver_id'])) {
        if (isset($_GET['org_id'])) echo "&"; else echo "?";
        echo "driver_id=$_GET[driver_id]";
    }
    ?>'>
        <table border=1px>
            <tr>
                <td>Shipping Address</td>
            </tr>
            <?php
            $sql = "SELECT shipping_address FROM account WHERE account_id = $selected_driver";
            if (!($result = $db->query($sql))) footdie("</table></form>Missing your shipping address<br>");
            $row = $result->fetch_assoc();
            ?>
            <tr>
                <td><input type=text name=address value="<?php echo "$row[shipping_address]"; ?>"></td>
            <tr>
                <td><input type=submit class=button name=order value="Place order"></td>
            </tr>
        </table>
    </form>
    <?php
    }
    else {
        echo "You don't have enough points to check out with this cart!";
    }
    }
    else { // No items to checkout
        if (isset($_POST['order'])) {
            $_POST['redirect'] = "true";
            redirect("../pages/checkout.php");
        }
        if (isset($_POST['redirect'])) {
            echo "Your order was successfully placed, and your cart is now empty.";
            $sql = "SELECT SUM(point_change_amt) AS points, username, order_placed FROM point_transactions INNER JOIN account ON point_transactions.driver_account_id = account_id INNER JOIN email_preferences ON point_transactions.driver_account_id = email_preferences.driver_account_id WHERE sponsor_org_id = $selected_org AND point_transactions.driver_account_id = $selected_driver";
            $result = $db->query($sql);
            $row = $result->fetch_assoc();
            if (($pointsMax = (float)$row['points']) == NULL) $pointsMax = 0;
            if ($role != 'driver') echo "<br>$row[username] now has $pointsMax points.<br>";
            else echo "<br>You now have $pointsMax points.<br>";
            // Trigger the email if necessary
            if ($row['order_placed'] != NULL) {
                $sql = "SELECT username, email_address FROM account WHERE account_id = $selected_driver";
                $Aresult = $db->query($sql);
                $row = $Aresult->fetch_assoc();
                $email = $row['email_address'];
                $username = $row['username'];
                $subj = 'Your Order Has Been Placed';
                $headers = 'From: WholesaleCrocodile@gmail.com' . "\r\n" . "Reply-To:WholesaleCrocodile@gmail.com" . "\r\n" . "X-Mailer: PHP/" . phpversion();
                // Compose message
                $mess = "Dear $username,\r\n\r\nOrder #$newOrder has been successfully placed and will contain the following items:\n";
                $sql = "SELECT item_transactions.quantity_change, catalog_items.product_name FROM item_transactions INNER JOIN catalog_items ON item_transactions.item_id = catalog_items.catalog_id WHERE item_transactions.order_id = $newOrder";
                $Bresult = $db->query($sql);
                while ($row = $Bresult->fetch_assoc()) $mess .= "$row[product_name] x$row[quantity_change]\n";
                // Fix long lines in case they exist
                $message = wordwrap($mess, 70, "\r\n");
                #echo "$email\n$subj\n$message\n$headers\n";
                mail($email, $subj, $message, $headers, '-f WholesaleCrocodile@gmail.com');
            }
        } else echo "Your cart is empty, there is nothing to check out!";
        echo "<br><br><a href='../pages/catalog.php";
        if (isset($_GET['org_id'])) echo "?org_id=$_GET[org_id]";
        if (isset($_GET['driver_id'])) {
            if (isset($_GET['org_id'])) echo "&"; else echo "?";
            echo "driver_id=$_GET[driver_id]";
        }
        echo "' class=button>Return to the catalog</a>";
    }
    ?>
</main>
<?php include('../php/footer.php'); ?>
</html>
