<?php session_start(); ?>
<!DOCTYPE html>
<html>
<?php $title = "Catalog Management";
include('../php/header.php'); ?>
<main>

    <?php
    // Make sure sponsors who aren't associated can't try to break themselves
    if($role == 'sponsor') getSponsorOrgId($db, $_SESSION['user_id']);
    if (isset($_POST['additem'])) {

        $query = "SELECT sponsor_org_id FROM driver_in_org WHERE driver_account_id = '$id'";
        $sponsor_org_id;
        $result = $db->query($query);
        if (!$result || mysqli_num_rows($result) == 0) {
            echo("<p class=\"errorText\">No Matching User Data</p>");
        } else {
            $result_row = mysqli_fetch_array($result);
            $sponsor_org_id = $result_row['sponsor_org_id'];
        }

        $itemId = $_POST['additem'];
        $title = $_POST['title'];
        $pic = $_POST['pic'];
        $link = $_POST['link'];
        $primary_category = $_POST['primary_category'];
        $unit_price = $_POST['unit_price'];
        echo $sponsor_org_id;
        $query = "INSERT INTO catalog_items (sponsor_org_id,api_reference,product_name,product_visible,gallery_pic,url,unit_price,category) VALUES ('$sponsor_org_id','$itemId','$title','1','$pic','$link','$unit_price','$primary_category')";

        $db->query($query);


        redirect("../pages/manage_catalog.php");

    } else if (isset($_POST['submit'])) {
        $ebaySearch = $_POST['ebaySearch'];
        $resp = $ebayManager->findItems($ebaySearch);
        $respstring = $resp->asXML();
        $_SESSION['resp'] = $respstring;
    } else if (isset($_SESSION['resp'])) {
        $resp = simplexml_load_string($_SESSION['resp']);
    }


    ?>


    <table>
        <tr>
            <td>
                Search By Keyword:
            </td>
            <td>
                <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <input type="text" name="ebaySearch" required><br>
                    <input type="submit" name="submit" value="Submit Form"><br>
                </form>
            </td>
        <tr>
            <td>

                <?php
                if (isset($_SESSION['resp'])) {


                    $results = '';
                    // If the response was loaded, parse it and build links

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

                    echo "<tr><td>";
                    echo "<form method = 'post' action = '$self'><button class = 'button' type='submit' name ='previouspage' value='$page'>Previous Page</button></form>";
                    echo "</td><td><form method = 'post' action = '$self'><button class = 'button' type='submit' name ='nextpage' value='$page'>Next Page</button></form>";
                    $pagenum = $page + 1;
                    echo "</td><td>Current Page: $pagenum</td></tr>";

                    foreach ($resp->searchResult->item as $item) {

                        if ((int)floor($currItem / 5) == $page) {

                            //var_dump($item);

                            $pic = $item->galleryURL;
                            $link = $item->viewItemURL;
                            $title = $item->title;
                            $itemId = $item->itemId;
                            $unit_price = $item->sellingStatus->currentPrice;
                            $primary_category = $item->primaryCategory->categoryName;

                            $results .= "<tr><td><img src=\"$pic\"></td><td><a href=\"$link\">$title</a></td><td><form method = 'post' action='$self'><input type = 'hidden' id = 'link' name='link' value ='$link'><input type = 'hidden' id = 'pic' name='pic' value ='$pic'><input type = 'hidden' id = 'primary_category' name='primary_category' value ='$primary_category'><input type = 'hidden' id = 'unit_price' name='unit_price' value ='$unit_price'><input type = 'hidden' id = 'title' name='title' value ='$title'><button class='button' type = 'submit' name='additem' value='$itemId'>Add Item</button></form></td></tr>";
                        }
                        $currItem++;
                    }

                    echo $results;
                    /*
                    $resp = $ebayManager->findItemById('173827082726');

                    $pic = $resp->Item->GalleryURL;
                    $link = $resp->Item->ViewItemURLForNaturalSearch;
                    $title = $resp->Item->Title;

                    $results = "<tr><td><img src=\"$pic\"></td><td><a href=\"$link\">$title</a></td><td><input type = 'submit' name='additem' value='Add Item'></td></tr>";
                    echo $results;
                    */

                }
                ?>
            </td>
        </tr>
    </table>


</main>
<?php include('../php/footer.php'); ?>
</html>
