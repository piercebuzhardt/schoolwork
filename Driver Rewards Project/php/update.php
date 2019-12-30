<?php
# Makes sure PHP reports all errors (AWS webserver does not actually do this by default!!)
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

# Always properly include, suppress whichever one of these doesn't work
@include_once('../php/sqlconnect.php');
@include_once('./php/sqlconnect.php');

$sql = "SELECT order_transactions.order_id, order_transactions.driver_account_id, email_preferences.order_problem
FROM order_transactions INNER JOIN email_preferences ON order_transactions.driver_account_id = email_preferences.driver_account_id
WHERE order_transactions.fulfill_time IS NULL AND order_transactions.problem IS NULL AND TIMESTAMPDIFF(DAY, NOW(), order_transactions.creation_time) < -2";
// ^ 3 day shipping is the < -2 part
$result = $db->query($sql);
if(mysqli_num_rows($result)) {
  while($row = $result->fetch_assoc()) {
    #echo "Process Order #$row[order_id] FOR $row[driver_account_id] with email = $row[order_problem]\n";
    if(rand() > getrandmax()/2) {
      $action = "FULFILL";
      $sql = "UPDATE order_transactions SET fulfill_time = NOW() WHERE order_id = $row[order_id]";
      #echo "FULFILL THIS ORDER\n";
    }
    else {
      $action = "PROBLEMIZE";
      $sql = "UPDATE order_transactions SET problem=\"Houston, we have a problem\" WHERE order_id = $row[order_id]";
      #echo "PROBLEM THIS ORDER\n";
    }
    $db->query($sql);
    if($action == "PROBLEMIZE" && $row['order_problem'] == 1) {
      $failed_order = $row['order_id'];
      $sql = "SELECT username, email_address FROM account WHERE account_id = $row[driver_account_id]";
      $Aresult = $db->query($sql);
      $row = $Aresult->fetch_assoc();
      $email = $row['email_address'];
      $username = $row['username'];
      $subj = 'Order No Longer Deliverable From Wholesale Crocodile';
      $headers = 'From: WholesaleCrocodile@gmail.com'."\r\n"."Reply-To:WholesaleCrocodile@gmail.com"."\r\n"."X-Mailer: PHP/".phpversion();
      // Compose message
      $mess = "Dear $username,\r\n\r\nWe regret to inform you that the order #$failed_order can no longer be completed, which contained the following:\n";
      $sql = "SELECT item_transactions.quantity_change, catalog_items.product_name FROM item_transactions INNER JOIN catalog_items ON item_transactions.item_id = catalog_items.catalog_id WHERE item_transactions.order_id = $failed_order";
      $Bresult = $db->query($sql);
      while($row = $Bresult->fetch_assoc()) $mess .= "$row[product_name] x$row[quantity_change]\n";
      // Fix long lines in case they exist
      $message = wordwrap($mess, 70, "\r\n");
      #echo "$email\n$subj\n$message\n$headers\n";
      mail($email, $subj, $message, $headers);
    }
  }
}
?>
