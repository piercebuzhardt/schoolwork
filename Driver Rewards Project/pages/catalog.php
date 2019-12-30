<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head><?php $title = "Catalog";
    include('../php/header.php'); ?></head>
<main>
    <?php
    // Build Sponsor Selector
    if ($role != 'sponsor') {
	$sql = "SELECT COUNT(*) as count FROM sponsor_org WHERE live_org = 1";
	$result = $db->query($sql);
	$row = $result->fetch_assoc();
	if($row['count'] == 0) footdie("There are no organizations with active catalogs at this time!");
        ?>
        <table border=1px>
            <tr>
                <td colspan=2>Select Sponsor</td>
            </tr>
            <?php
            if ($role == 'driver') {
                $sql = "SELECT * FROM sponsor_org WHERE sponsor_org.live_org = 1 && sponsor_org_id IN (SELECT sponsor_org_id FROM driver_in_org WHERE driver_account_id = $_SESSION[user_id])";
            } else { // admin
                $sql = "SELECT DISTINCT sponsor_org.sponsor_org_id, org_name FROM sponsor_org INNER JOIN driver_in_org ON driver_in_org.sponsor_org_id = sponsor_org.sponsor_org_id WHERE sponsor_org.live_org = 1 HAVING 0 < (SELECT COUNT(*) FROM driver_in_org INNER JOIN account ON account.account_id = driver_in_org.driver_account_id WHERE account.role = 'driver')";
            }
            if (!($result = $db->query($sql))) footdie("</table>Unable to complete request at this time!");
            echo "<form method=GET action='../pages/catalog.php'><tr><td><select name=org_id>\n";
            while ($row = $result->fetch_assoc()) {
                if (!isset($selected_org)) $selected_org = $row['sponsor_org_id'];
                echo "\t<option name=org value=$row[sponsor_org_id]";
                if (isset($_GET['org_id']) && $_GET['org_id'] == $row['sponsor_org_id']) {
                    echo " selected";
                    $selected_org = $_GET['org_id'];
                }
                echo ">$row[org_name]</option>\n";
            }
            echo "</select></td>\n";
            echo "<td><input type=submit class=button value=\"Change Sponsor Org\"></td></tr></form>";
            $sql = "SELECT SUM(point_change_amt) AS points FROM point_transactions WHERE sponsor_org_id = $selected_org AND driver_account_id = $_SESSION[user_id]";
            $result = $db->query($sql);
            $row = $result->fetch_assoc();
            if (($pointsMax = $row['points']) == NULL) $pointsMax = 0;
            echo "<tr><td>Available Points:</td><td>$pointsMax</td></tr>";
            // Get point-to-dollar ratio
            $sql = "SELECT points_to_dollars FROM sponsor_org WHERE sponsor_org_id = $selected_org";
            $result = $db->query($sql);
            $row = $result->fetch_assoc();
            $p2d = $row['points_to_dollars']; ?>
        </table>
        <br>
        <?php
    } else { // Set sponsor selected org
        $sql = "SELECT d.sponsor_org_id FROM driver_in_org as d INNER JOIN sponsor_org as s ON d.sponsor_org_id = s.sponsor_org_id WHERE driver_account_id = $_SESSION[user_id] AND live_org = 1";
        if (!($result = $db->query($sql))) footdie("ERROR: Could not fetch your org at this time!");
        if(mysqli_num_rows($result) == 0) footdie("<p class=error>You are not associated with any active sponsor organization!</p>");
        $row = $result->fetch_assoc();
        $selected_org = $row['sponsor_org_id'];
        // Get point-to-dollar ratio
        $sql = "SELECT points_to_dollars FROM sponsor_org WHERE sponsor_org_id = $selected_org";
        $result = $db->query($sql);
        $row = $result->fetch_assoc();
        $p2d = $row['points_to_dollars'];
    }
    ?>

    <table>
        <tr>
            <td>
                Search Catalog
            </td>
            <td>
                <form method="post" action="<?php echo $_SERVER['PHP_SELF'];
                if (isset($_GET['org_id'])) echo "?org_id=$_GET[org_id]";
                if (isset($_GET['driver_id'])) {
                    if (isset($_GET['org_id'])) echo "&"; else echo "?";
                    echo "driver_id=$_GET[driver_id]";
                }
                ?>">
                    <select name="search_type">
                        <option value="" disabled selected>Select Search Option</option>
                        <option value="item_name">Item Name</option>
                        <option value="min_price">Maximum Price</option>
                        <option value="category">Category</option>
                    </select>
                    <input type="text" name="value" placeholder="Search Value">
                    <select name="sort_type">
                        <option value="" disabled selected>Select Sort Type</option>
                        <option value="sort_price">Price</option>
                        <option value="sort_name">Name</option>
                    </select>
                    <select name="sort_value">
                        <option value="" disabled selected>Select Sort Order</option>
                        <option value="asc">Ascending</option>
                        <option value="descending">Descending</option>
                    </select><br>
                    <input type="submit" name="submit" value="Submit Form"><br>
                </form>
            </td>
        </tr>
    </table>

    <table>
        <?php
        // Button processing
        if (isset($_POST['removeitem'])) {
            $query = "DELETE FROM catalog_items WHERE api_reference = $_POST[removeitem];";
            $result = $db->query($query);
        }
        if (isset($_POST['cartify'])) {
            $query = "SELECT * FROM driver_cart WHERE driver_account_id = $_SESSION[user_id] AND catalog_id = $_POST[cat_id];";
            if (mysqli_num_rows($db->query($query))) {
                echo "You already have that item in your cart, go to <a href='../pages/cart.php";
                if (isset($_GET['org_id'])) echo "?org_id=$_GET[org_id]";
                if (isset($_GET['driver_id'])) {
                    if (isset($_GET['org_id'])) echo "&"; else echo "?";
                    echo "driver_id=$_GET[driver_id]";
                }
                echo "'>your cart</a> if you want to change the quantity!<br>";
            } else {
                $query = "INSERT INTO driver_cart VALUES ($_SESSION[user_id], $_POST[cat_id], 1);";
                if (!$db->query($query)) {
                    echo "Was not able to add that to your cart at this time<br>";
                } else {
                    echo "Successfully added to your cart<br>";
                }
            }
        }

        if (isset($_POST['search_type'])) {
            $search_type = $_POST['search_type'];
            if (isset($_POST['value'])) $value = $_POST['value'];

            $search_value = $_POST['value'];
            if ($_POST['search_type'] == 'item_name') {
                $query = "SELECT catalog_id,api_reference,product_name,product_visible,gallery_pic,url,unit_price FROM catalog_items WHERE sponsor_org_id = '$selected_org' AND product_name LIKE '%$search_value%'";
            } else if ($_POST['search_type'] == 'min_price') {
                $query = "SELECT catalog_id,api_reference,product_name,product_visible,gallery_pic,url,unit_price FROM catalog_items WHERE sponsor_org_id = '$selected_org' AND unit_price <= '$search_value'";
            } else if ($_POST['search_type'] == 'category') {
                $query = "SELECT catalog_id,api_reference,product_name,product_visible,gallery_pic,url,unit_price FROM catalog_items WHERE sponsor_org_id = '$selected_org' AND category LIKE '%$search_value%'";
            }
        } else {
            $query = "SELECT catalog_id,api_reference,product_name,product_visible,gallery_pic,url,unit_price FROM catalog_items WHERE sponsor_org_id = '$selected_org'";
        }

        if (isset($_POST['sort_type']) && isset($_POST['sort_value'])) {
            $sort_type = $_POST['sort_type'];
            $sort_value = $_POST['sort_value'];

            $query .= " ORDER BY ";
            if ($_POST['sort_type'] == 'sort_price') {
                $query .= "unit_price ";
            } else {
                $query .= "product_name ";
            }

            if ($_POST['sort_value'] == 'asc') {
                $query .= "ASC";
            } else {
                $query .= "DESC";
            }
        }

        $db->query($query);
        if (!$result = $db->query($query)) {
            echo "Error";
        } else {
            $self = $_SERVER['PHP_SELF'];
            $page = 0;
            if (isset($_POST['nextpage'])) {
                $page = $_POST['nextpage'];
                $page++;
            }
            $currItem = 0;

            if (isset($_POST['previouspage'])) {
                $page = $_POST['previouspage'];
                if ($page != 0) {
                    $page--;
                }
            }

            // Build catalog results row by row
            while ($row = $result->fetch_assoc()) {
                if ($row['product_visible'] != 0 && (int)floor($currItem / 5) == $page) {
                    $pic = $row['gallery_pic'];
                    $link = $row['url'];
                    $title = $row['product_name'];
                    $api = $row['api_reference'];
                    $price = $row['unit_price'] * $p2d;

                    $results = "<tr><td><img src=\"$pic\"></td>\n<td><a href=\"$link\">$title</a></td><td>$price points</td>\n";
                    $results .= "<td><form method=post action='$self";
                    if (isset($_GET['org_id'])) $results .= "?org_id=$_GET[org_id]";
                    if (isset($_GET['driver_id'])) {
                        if (isset($_GET['org_id'])) $results .= "&";
                        else $results .= "?";
                        $results .= "driver_id=$_GET[driver_id]";
                    }
                    $results .= "'><input class=button type=submit name=cartify value=\"Add to My Cart\">";
                    $results .= "<input type=hidden name=cat_id value='$row[catalog_id]'></form></td>\n";

                    if ($role == "sponsor" || $role == "admin") {
                        $results .= "<td><form method = 'post' action = '$self";
                        if (isset($_GET['org_id'])) $results .= "?org_id=$_GET[org_id]";
                        if (isset($_GET['driver_id'])) {
                            if (isset($_GET['org_id'])) $results .= "&";
                            else $results .= "?";
                            $results .= "driver_id=$_GET[driver_id]";
                        }
                        $results .= "'><button class='button' type = 'submit' name='removeitem' value='$api'>Remove Item</button></form></td>\n";

                        $results .= "<td xmlns=\"http://www.w3.org/1999/html\"><form method = 'post' action = './edit_catalog.php'>";
                        if($role == 'admin') {$results .= "<input type = 'hidden' name = 'sponsor_org_id' value = $selected_org>";}
                        $results .= "<button class = 'button' type = 'submit' name='itemToEdit' value = '$api'>Edit Item</button></form>";

                    }
                    $results .= "</tr>\n";
                    echo $results;
                    $results = "";
                }
                $currItem++;
            }

            // Build next/previous page buttons (keep search queries intact)
            if ($currItem >= 5) {
                echo "<tr><td>";
                echo "<form method = 'post' action = '$self";
                if (isset($_GET['org_id'])) echo "?org_id=$_GET[org_id]";
                if (isset($_GET['driver_id'])) {
                    if (isset($_GET['org_id'])) echo "&"; else echo "?";
                    echo "driver_id=$_GET[driver_id]";
                }
                echo "'>\n";
                if (isset($value)) {
                    echo "<input type = 'hidden' id = 'value' name='value' value ='$value'>";
                }
                if (isset($search_type)) {
                    echo "<input type = 'hidden' id = 'search_type' name='search_type' value ='$search_type'>";
                }
                if (isset($sort_type)) {
                    echo "<input type = 'hidden' id = 'sort_type' name='sort_type' value ='$sort_type'>";
                }
                if (isset($sort_value)) {
                    echo "<input type = 'hidden' id = 'sort_value' name='sort_value' value ='$sort_value'>";
                }
                echo "<button class = 'button' type='submit' name ='previouspage' value='$page'>Previous Page</button></form>\n";

                echo "</td><td><form method = 'post' action = '$self";
                if (isset($_GET['org_id'])) echo "?org_id=$_GET[org_id]";
                if (isset($_GET['driver_id'])) {
                    if (isset($_GET['org_id'])) echo "&"; else echo "?";
                    echo "driver_id=$_GET[driver_id]";
                }
                echo "'>";
                if (isset($value)) {
                    echo "<input type = 'hidden' id = 'value' name='value' value ='$value'>";
                }
                if (isset($search_type)) {
                    echo "<input type = 'hidden' id = 'search_type' name='search_type' value ='$search_type'>";
                }
                if (isset($sort_type)) {
                    echo "<input type = 'hidden' id = 'sort_type' name='sort_type' value ='$sort_type'>";
                }
                if (isset($sort_value)) {
                    echo "<input type = 'hidden' id = 'sort_value' name='sort_value' value ='$sort_value'>";
                }
                echo "<button class = 'button' type='submit' name ='nextpage' value='$page'>Next Page</button></form>\n";

                $pagenum = $page + 1;
                echo "</td><td>Current Page: $pagenum</td></tr>\n";
            }
        }

        /*
        $db->query($query);
        if(!$result = $db->query($query))
        {
            echo "Error";
        }
        else
        {
            while($row = $result->fetch_assoc()){

                if($row['product_visible'] != 0)
                {
                    $itemId = $row['api_reference'];
                    $resp = $ebayManager->findItemById($itemId);
                    if($resp != -1)
                    {
                        $pic = $resp->Item->GalleryURL;
                        $link = $resp->Item->ViewItemURLForNaturalSearch;
                        $title = $resp->Item->Title;

                        $results = "<tr><td><img src=\"$pic\"></td><td><a href=\"$link\">$title</a></td></tr>";
                        echo $results;
                        $results = "";
                    }
                }
            }

        }
        */

        ?>
    </table>

</main>
<?php include('../php/footer.php'); ?>
</html>

