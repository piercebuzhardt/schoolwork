<?php session_start(); ?>
<!DOCTYPE html>
<html>
<?php $title = "My Cart";
include('../php/header.php'); ?>
<main>
    <?php
    // Build Selectors
    if ($role != 'sponsor') {
	$sql = "SELECT COUNT(*) as count FROM sponsor_org WHERE live_org = 1";
	$result = $db->query($sql);
	$row = $result->fetch_assoc();
	if($row['count'] == 0) footdie("There are no organizations with active catalogs at this time!");
    ?>
    <table border=1px>
        <tr>
            <td colspan=2>Select <?php if ($role == 'admin') echo "Sponsor & Driver"; else echo "Sponsor"; ?></td>
        </tr>
        <?php
        if ($role == 'driver') {
            $sql = "SELECT * FROM sponsor_org WHERE sponsor_org.live_org = 1 && sponsor_org_id IN (SELECT sponsor_org_id FROM driver_in_org WHERE driver_account_id = $_SESSION[user_id])";
        } else { // admin
            $sql = "SELECT DISTINCT sponsor_org.sponsor_org_id, org_name FROM sponsor_org INNER JOIN driver_in_org ON driver_in_org.sponsor_org_id = sponsor_org.sponsor_org_id WHERE sponsor_org.live_org = 1 HAVING 0 < (SELECT COUNT(*) FROM driver_in_org INNER JOIN account ON account.account_id = driver_in_org.driver_account_id WHERE account.role = 'driver')";
        }
        if (!($result = $db->query($sql))) footdie("</table>Unable to complete request at this time!");
        echo "<form method=GET action='../pages/cart.php'><tr><td><select name=org_id>\n";
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
            echo "<form method=GET action='../pages/cart.php'><tr><td><select name=driver_id>\n";
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

        $sql = "SELECT SUM(point_change_amt) AS points FROM point_transactions WHERE sponsor_org_id = $selected_org AND driver_account_id = $selected_driver";
        $result = $db->query($sql);
        if(!$result) echo "YEAH THIS ONE '$selected_driver'<br>";
        $row = $result->fetch_assoc();
        echo "</table><br>";
        }
        else { // Set sponsor selected org
        $sql = "SELECT d.sponsor_org_id FROM driver_in_org as d INNER JOIN sponsor_org as s ON d.sponsor_org_id = s.sponsor_org_id WHERE driver_account_id = $_SESSION[user_id] AND live_org = 1";
        if (!($result = $db->query($sql))) footdie("ERROR: Could not fetch your org at this time!");
        if(mysqli_num_rows($result) == 0) footdie("<p class=error>You are not associated with any active sponsor organization!</p>");
        $row = $result->fetch_assoc();
        $selected_org = $row['sponsor_org_id'];
        ?>
        <form method=GET action='../pages/cart.php'>
            <table border=1px>
                <tr>
                    <td colspan=2>Select Driver</td>
                </tr>
                <?php
                $sql = "SELECT account_id, username FROM account WHERE role='driver' AND account_id IN (SELECT driver_account_id FROM driver_in_org WHERE sponsor_org_id = $selected_org)";
                $result = $db->query($sql);
                if(mysqli_num_rows($result) == 0) {
                  echo "<tr><td>There are no drivers in your organization at this time!</td></tr></table><br>";
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
                echo "<td><input type=submit class=button value=\"Change Driver\"></td></tr></table><br>";
                }
                }
                // Cart buttons
                // REMOVE ALL CART (dumpCart)
                if (isset($_POST['dumpCart'])) {
                    $sql = "DELETE FROM driver_cart WHERE driver_account_id = $_SESSION[user_id] AND catalog_id IN (SELECT catalog_id FROM catalog_items WHERE sponsor_org_id = $selected_org)";
                    if (!$db->query($sql)) echo "Could not perform that action at this time!<br>";
                }
                // Update Quantity (updateQty, change, cat_id)
                if (isset($_POST['updateQty'])) {
                    $sql = "UPDATE driver_cart SET quantity = GREATEST(1,($_POST[change] + quantity)) WHERE driver_account_id = $_SESSION[user_id] AND catalog_id = $_POST[cat_id]";
                    if (!$db->query($sql)) echo "Could not update that quanity right now!<br>";
                }
                // Remove Item (removeItem, cat_id)
                if (isset($_POST['removeItem'])) {
                    $sql = "DELETE FROM driver_cart WHERE driver_account_id = $_SESSION[user_id] AND catalog_id = $_POST[cat_id]";
                    if (!$db->query($sql)) echo "Could not remove that item at this moment!<br>";
                }

                // Cart portion
                $sql = "SELECT * FROM driver_cart WHERE driver_account_id = $_SESSION[user_id] AND catalog_id IN (SELECT catalog_id FROM catalog_items WHERE sponsor_org_id = $selected_org)";
                if (!($result = $db->query($sql))) {
                    footdie("Could not fetch your cart at this time!");
                }
                if (mysqli_num_rows($result)) {
                    // Build the cart table
                    ?>
                    <form method=POST action='../pages/cart.php<?php
                    if (isset($_GET['org_id'])) echo "?org_id=$_GET[org_id]";
                    if (isset($_GET['driver_id'])) {
                        if (isset($_GET['org_id'])) echo "&"; else echo "?";
                        echo "driver_id=$_GET[driver_id]";
                    }
                    ?>'><input type=submit class=button name=dumpCart value="Remove all items from cart"></form>
                    <br>
                    <table border=1px>
                        <tr>
                            <td>Item Name</td>
                            <td>Item Quantity</td>
                            <td>Change Quantity</td>
                            <td>Remove from Cart</td>
                        </tr>
                        <?php
                        while ($row = $result->fetch_assoc()) {
                            $sql = "SELECT product_name, url, product_visible, product_deleted FROM catalog_items WHERE catalog_id = $row[catalog_id]";
                            $nameResult = $db->query($sql);
                            $nameRow = $nameResult->fetch_assoc();
                            if ($nameRow['product_visible'] != 1 || $nameRow['product_deleted'] == 1) continue;
                            echo "<tr><td><a href='$nameRow[url]'>$nameRow[product_name]</a></td><td>$row[quantity]</td>\n";
                            echo "<td><form method=POST action='../pages/cart.php";
                            if (isset($_GET['org_id'])) echo "?org_id=$_GET[org_id]";
                            if (isset($_GET['driver_id'])) {
                                if (isset($_GET['org_id'])) echo "&"; else echo "?";
                                echo "driver_id=$_GET[driver_id]";
                            }
                            echo "'><input type=hidden name=cat_id value=$row[catalog_id]><input type=number name=change step='1' value=1 style=\"width: 65px;\"><input type=submit class=button name=updateQty value=\"Update Amount\"></form></td>\n";
                            echo "<td><form method=POST action='../pages/cart.php";
                            if (isset($_GET['org_id'])) echo "?org_id=$_GET[org_id]";
                            if (isset($_GET['driver_id'])) {
                                if (isset($_GET['org_id'])) echo "&"; else echo "?";
                                echo "driver_id=$_GET[driver_id]";
                            }
                            echo "'><input type=hidden name=cat_id value=$row[catalog_id]><input type=submit class=button name=removeItem value=\"REMOVE ALL\"></form></td></tr>";
                        }
                        ?>
                    </table>
                    <br>
                    <a href='../pages/checkout.php<?php
                    if (isset($_GET['org_id'])) echo "?org_id=$_GET[org_id]";
                    if (isset($_GET['driver_id'])) {
                        if (isset($_GET['org_id'])) echo "&"; else echo "?";
                        echo "driver_id=$_GET[driver_id]";
                    }
                    ?>' class=button>Proceed to checkout with all items in cart</a>
                    <?php
                } else {
                    echo "Your cart is empty. You can add items to it in the catalog<br><br>";
                    echo "<a class=button href='../pages/catalog.php";
                    if (isset($_GET['org_id'])) echo "?org_id=$_GET[org_id]";
                    if (isset($_GET['driver_id'])) {
                        if (isset($_GET['org_id'])) echo "&"; else echo "?";
                        echo "driver_id=$_GET[driver_id]";
                    }
                    echo "'>Return to catalog</a>";
                }
                ?>
</main>
<?php include('../php/footer.php'); ?>
</html>
