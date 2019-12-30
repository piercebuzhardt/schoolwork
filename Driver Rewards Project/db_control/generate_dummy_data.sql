-- Sample sponsors
-- Example Org is fully populated
-- Closed Org is minimally populated
INSERT INTO `sponsor_org` (`sponsor_org_id`, `org_name`, `org_email_address`, `org_bio`, `org_url`, `path_to_org_logo`, `points_to_dollars`, `application_open`, `live_org`) VALUES
(1, 'Example Org', 'Ex@mple.org', 'This is an example organization bio. It is optional, but this org has one', 'https://www.example.org', NULL, 1, 1, 1),
(2, 'Closed Org', 'options@closed.org', NULL, NULL, NULL, 50, 0, 1);

-- Org Logo Bans
INSERT INTO `org_logo_bans` (`banned_org_id`) VALUES
(2);

-- Sample users
-- 2 Drivers, passwords are Example\ User:'password' and banned:'t!ban'
-- 1 Admin, passwords are admin:'admin'
-- 2 Sponsors, passwords are Example\ Sponsor:'LOGIN' and Closed\ Sponsor\ Account:'sPeCi@1'
INSERT INTO `account` (`account_id`, `username`, `email_address`, `shipping_address`, `hashed_password`, `role`, `path_to_pfp`, `live_account`) VALUES
(1, 'Example User', 'eUse@ex.com', '123 Example Dr., Clemson, SC', '$2y$10$tk0rUeHgh1zYJ4XWsTYA5erz.7cbSmJYF/cj4KgN2ovuTl2akWiHS', 'driver', NULL, 1),
(2, 'admin', 'wholesalecrocadmin@gmail.com', '123 Admins Rule, Clemson, SC', '$2y$10$l6O98UNnRMj9cgAhm52b2u8tvv7P4DLRLAa12MnHYfpj6t3yLtv9S', 'admin', NULL, 1),
(3, 'banned user', 'bannerino@banned.com', 'Haha fools this shipping address is invalid', '$2y$10$SQDHYFVRE8beAskAp/T.de3G3WgQvV2.xFmtHp0a50/h9M3t/.I4q', 'driver', NULL, 1),
(4, 'Example Sponsor', 'sponsor.ex@mple.org', '123 Good Sponsor Way, Clemson, SC', '$2y$10$GHjM4sHCkZIi8BHgGJPAIuRrSyJQAseVOElQmqId96fTMqfI11jRu', 'sponsor', NULL, 1),
(5, 'Closed Sponsor Account', 'warden@closed.org', '123 Abc Xyz, Clemson, SC', '$2y$10$rIjnM9oguNSoqtkz0VKA3OXBDFn194oamea5J6bSPxIEDnp5xh5Vm', 'sponsor', NULL, 1),
(6, 'Thomas', 'tlranda@g.clemson.edu', '123 Clemson', '$2y$10$f9Z8O7/IBWrBbsfO.btqdOwFRxPx1Fu8wZMEPpN4JlSy6qh5tKHzW', 'admin', NULL, 1);

-- Banned Users
-- Automaticall initiated, but we have to update banned user's bans
-- banned is banned, what a surprise
UPDATE `account_bans` SET product_report_mute = 1, org_report_mute = 1, pfp_report_mute = 1, allowed_change_pfp = 1, login_suspended = ADDTIME(NOW(), SEC_TO_TIME(36000)) WHERE account_id = 3;

-- Password Resets
-- Example User has completed one already
-- Closed Sponsor Account is required to complete one by an administrator
INSERT INTO `DriverRewards4910`.`password_resets` (`account_id`, `reset_requested_by_id`, `temp_hashed_password`, `creation_time`, `reset_complete`, `force_reset`)
VALUES ('1', NULL, '$2y$10$xCuasL6S8m0XPAG.GBDW/OHdbWsg56OqZWUcWL.QmSghxz2yIbcg2', NULL, '1', NULL),
('5', '6', NULL, NULL, NULL, '1');

-- Profile Picture Flags
-- I mean, why do you think banned is banned?
INSERT INTO `profile_picture_flags` (`account_id`, `reporter_id`, `dismissed`) VALUES
(3, 1, 1);

-- Account Creation / Deletion is auto-populated by triggers. NO interaction required, just insert into `account` or switch the `live_account` bit on an account

-- Admin Messages
INSERT INTO `admin_messages` (`admin_message_id`, `sender_id`, `message_contents`, `sent_time`) VALUES
(1, 2, 'This is test of admin warning messages on the site.', NULL);

-- Catalog Items
-- Just two per sponsor for now
INSERT INTO `catalog_items` (`catalog_id`, `sponsor_org_id`, `api_reference`, `product_name`, `category`, `product_description`, `product_visible`, `product_deleted`, `gallery_pic`, `url`, `unit_price`) VALUES
(1, 1, 323758657638, "Military High Power Green Laser Pointer Lazer Pen", "Toy", NULL, 1, NULL, "http://thumbs3.ebaystatic.com/m/mbscIm-8TbC7AqVShs1tXRA/140.jpg", "http://www.ebay.com/itm/Military-High-Power-Green-Laser-Pointer-Lazer-Pen-Star-Cap-18650-Battery-USA-/323758657638", 7.5),
(2, 1, 123714280111, "Revoltech Yamaguchi No. 31  Robo Getter 1 Figure", "Toy", NULL, 1, NULL, "http://thumbs4.ebaystatic.com/m/mqlNYS0pIkjvWubNLNfXMew/140.jpg", "http://www.ebay.com/itm/Revoltech-Yamaguchi-No-31-Robo-Getter-1-Figure-/123714280111", 19.80);

-- Catalog Item Reviews
-- Only for the open sponsor's items
INSERT INTO `catalog_item_reviews` (`catalog_review_id`, `catalog_id`, `driver_account_id`, `rating`, `review`, `is_deleted`, `creation_time`) VALUES
(1, 1, 1, 3, 'I left a review!', 0, NULL),
(2, 2, 1, 1, 'This review should be deleted', 1, NULL);

-- Catalog Item Review Flags
-- Sample reports
INSERT INTO `catalog_item_review_flags` (`catalog_review_id`, `reporter_id`, `dismissed`) VALUES
(1, 2, 0),
(1, 4, 1);

-- Catalog Item Review Responses
-- Sample response
INSERT INTO `catalog_item_review_responses` (`respond_to_review_id`, `account_id`, `response`, `is_deleted`, `creation_time`) VALUES
(1, 4, 'Glad you reviewed the item! Enjoy!', 0, NULL);

-- Closed Sponsor Keys
-- Documenting the struggle of getting `banned` into `Closed Org`
-- Example has also joined the closed org
INSERT INTO `closed_sponsor_keys` (`sponsor_org_id`, `key_creator_id`, `key_redeemer_id`, `join_key`, `status`, `creation_time`) VALUES
(2, 5, 3, 'missedOpportunity', 'deactivated', NULL),
(2, 5, 3, 'JOINTHEORG', 'used', NULL),
(2, 5, 3, 'keyToBeDeactivated', 'pending', NULL),
(2, 5, 1, 'joinUs!', 'used', NULL);

-- Open Sponsor Applications
INSERT INTO `open_sponsor_applications` (`driver_account_id`, `sponsor_org_id`, `auth_account_id`, `status`, `creation_time`) VALUES
(1, 1, 4, 'accepted', NULL),
(3, 1, 4, 'rejected', NULL);

-- Open Sponsor Reviews
INSERT INTO `open_sponsor_reviews` (`sponsor_review_id`, `sponsor_org_id`, `driver_account_id`, `creation_time`, `rating`, `review`, `is_deleted`) VALUES
(1, 1, 1, NULL, 3, 'I like this org.', 0),
(2, 1, 1, NULL, 3, 'Whoops. This should have been deleted.', 1);

-- Open Sponsor Review Flags
INSERT INTO `open_sponsor_review_flags` (`sponsor_review_id`, `reporter_id`, `dismissed`) VALUES
(2, 1, 1);

-- Open Sponsor Review Replies
INSERT INTO `open_sponsor_review_replies` (`respond_to_review_id`, `account_id`, `reply_depth`, `creation_time`, `response`, `is_deleted`) VALUES
(2, 4, 1, NULL, 'Gotcha, deleted it!', 0);

-- Driver Cart
-- Example account has some stuff
INSERT INTO `driver_cart` (`driver_account_id`, `catalog_id`, `quantity`) VALUES
(1, 2, 3);

-- Driver Changes Org
-- Automatically tracked by DB

-- Driver in Org
-- Example has joined both orgs
-- banned used to be in closed, has since left
-- Sponsors are in their respective sponsor orgs
INSERT INTO `DriverRewards4910`.`driver_in_org` (`driver_account_id`, `sponsor_org_id`)
VALUES ('4', '1'),
('5', '2'),
('1', '1'),
('1', '2'),
('3', '2');
DELETE FROM `DriverRewards4910`.`driver_in_org` WHERE `driver_account_id` = 3 AND `sponsor_org_id` = 2;

-- Invalid Password Log
INSERT INTO `invalid_password_log` (`account_id`, `log_time`) VALUES
(1, NULL);

-- Orders
INSERT INTO `order_transactions` (`order_id`, `driver_account_id`, `auth_account_id`, `sponsor_org_id`, `shipping_address`, `creation_time`) VALUES
(1, 1, 1, 1, "123 Example Dr., Clemson, SC", "2019-01-03 03:03:03"),
(2, 1, 4, 1, "home", "2019-04-03 10:01:11"),
(3, 3, 3, 1, "invalid_address", "2019-04-02 00:00:00");
UPDATE `order_transactions` SET creation_time = "2019-04-03 03:03:03" WHERE order_id = 1;
UPDATE `order_transactions` SET creation_time = "2019-04-05 10:01:11" WHERE order_id = 2;
UPDATE `order_transactions` SET creation_time = "2019-04-02 00:00:00" WHERE order_id = 3;

-- Point Transactions
INSERT INTO `point_transactions` (`point_change_id`, `driver_account_id`, `auth_account_id`, `sponsor_org_id`, `point_change_amt`, `change_time`, `change_reason`) VALUES
(1, 1, 4, 1, 500, "2019-01-01 05:05:05", "Joining bonus"),
(2, 3, 4, 1, 500, "2019-01-01 05:05:10", "Joining bonus"),
(3, 1, 1, 1, -200, NOW(), "Order #1"),
(4, 1, 1, 1, -100, NOW(), "Order #1"),
(5, 1, 1, 1, -30, NOW(), "Order #2"),
(6, 3, 3, 1, -10, NOW(), "Order #3");

-- Item Transactions
-- Putting the above 2 together
INSERT INTO `item_transactions` (`order_id`, `point_transaction_id`, `item_id`, `quantity_change`) VALUES
(1, 3, 1, 20),
(1, 4, 2, 5),
(2, 5, 1, 3),
(3, 6, 1, 1);

-- Unrecognized Logins
-- Prompted Example User to reset their password
INSERT INTO `unrecognized_login` (`account_id`, `MAC Address`, `login_time`) VALUES
(1, 'evil', NULL);

-- Change Email Preferences for Banned User
UPDATE `email_preferences` SET `order_problem` = NULL, `order_placed` = NULL, `points_changed` = NULL WHERE `driver_account_id` = 3;

