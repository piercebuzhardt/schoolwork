-- SPONSOR ORGS
INSERT INTO `sponsor_org` (`sponsor_org_id`, `org_name`, `org_email_address`, `org_bio`, `org_url`, `path_to_org_logo`, `points_to_dollars`, `application_open`, `live_org`) VALUES
(1, 'Example Org', 'Ex@mple.org', 'This is an example organization bio, but not all orgs have to have one', 'https://www.example.org', NULL, 10, 1, 1),
(2, 'Progressive Org', 'flow@progressive.com', 'Switch to save today', 'https://www.progressive.com', NULL, 15, 1, 1),
(3, 'Geico', 'gecko@geicoinsurance.com', '15% could save you... 15 points', 'https://www.geico.com', NULL, 12.75, 1, 1),
(4, 'Allstate', 'mayhem@goodhands.com', 'Join us to cover you against Mayhem, like me', 'https://allstate.com', NULL, 100, 1, 1);

-- Org Logo Bans
INSERT INTO `org_logo_bans` (`banned_org_id`) VALUES
(2);

-- Sample users
-- 5 Admins, 10 Drivers, 8 Sponsors
INSERT INTO `account` (`account_id`, `username`, `email_address`, `shipping_address`, `hashed_password`, `role`, `path_to_pfp`, `live_account`) VALUES
-- ADMINS
(1, 'admin', 'wholesalecrocadmin@gmail.com', '123 Admins Rule, Clemson, SC', '$2y$10$l6O98UNnRMj9cgAhm52b2u8tvv7P4DLRLAa12MnHYfpj6t3yLtv9S', 'admin', NULL, 1),
-- Password: admin
(2, 'Thomas', 'tlranda@g.clemson.edu', '123 Clemson Drive', '$2y$10$bLK7juTjKZ.kucVIE5JVpO1KOFcyQy6TMyNl5io2w7Sc/jjhjfjju', 'admin', NULL, 1),
-- Password: Thomas
(3, 'Pierce', 'pbuzhar@g.clemson.edu', '477 Reports Way', '$2y$10$bkFGJw/AGewVdSx41O3bce99ouDsB1UF.9EuHjTCPVie/XNbQAnPa', 'admin', NULL, 1),
-- Password: Pierce
(4, 'Ryan', 'rneeves@g.clemson.edu', '321 EBAY AVE', '$2y$10$sxBu7V5aNUmckw2p5Sn9l.iwEn70wgK.X0So9Lc5Nf8yam0h41Kdm', 'admin', NULL, 1),
-- Password: Ryan
(5, 'James', 'jdhartl@g.clemson.edu', '13 Clemson Road', '$2y$10$1V4R02BuqEIugdc0j2ONSeHNkf4984E9xyNd/OPf7piAnjaMwGZee', 'admin', NULL, 1),
-- Password: James
-- DRIVERS
(6, 'Example User', 'eUse@ex.com', '123 Example Drive, Clemson, SC', '$2y$10$tk0rUeHgh1zYJ4XWsTYA5erz.7cbSmJYF/cj4KgN2ovuTl2akWiHS', 'driver', NULL, 1),
-- Password: password
(7, 'banned user', 'bannerino@banned.com', 'Haha fools this shipping address is invalid', '$2y$10$SQDHYFVRE8beAskAp/T.de3G3WgQvV2.xFmtHp0a50/h9M3t/.I4q', 'driver', NULL, 1),
-- Password: t!ban
(8, 'John Doe', 'john@doe.demo', '19 Doe Road, Clemson SC', '$2y$10$NmDjMDOuqNM9JPqzKMVWl.uLGdxPh6R/.zfqz8hXOxK3DYB78FlwO', 'driver', NULL, 1),
-- Password: example
(9, 'Sally Smith', 'sally@smith.gg', '11-A Clover Apts, Greenville, SC', '$2y$10$BFfPIaNjpEljvb4qZs7Q3u8euR4/hK6yR5kDCn974gahUEwv2f.2u', 'driver', NULL, 1),
-- Password: part
(10, 'David', 'lifeglitchartist@gmail.com', '55 Somewhere Lane, Nowhere, MI', '$2y$10$ZwXbDfeHZDcvpw2SQPL/JuYnck1Dzutq3s3/zaNgGCm0dyP7NwgFq', 'driver', NULL, 1),
-- Password: nowhere
(11, 'Speed Demon McGee', 'speedyShipping@you.soon', '321 GO, ANYWHERE, ANYTIME', '$2y$10$dB6h/23/uwoplCkqsr7RjOCPhRIvWeiFwXWErWQ0FGaZYyTrQJWey', 'driver', NULL, 1),
-- Password: ACCELerate
(12, 'Safe Not Sorry', 'tortiseWins@hare.race', '8831 Victory Lane, Myrtle Beach, SC', '$2y$10$ylxGxIZ7gas1qIyzElOjPewjfUZvwqpwY9ae1asOuNZTa2BKfVL2C', 'driver', NULL, 1),
-- Password: she11
(13, 'Mr. Bad Driver', 'apolgies@everyone.else', '13 Hospital Way, Sumter, SC', '$2y$10$xFY1xxM8k3cyv2gXSTMz0uQET.vt4sjFBekch2RTHha5tCQGSPqOa', 'driver', NULL, 1),
-- Password: eXtremeP@ssw0rd
(14, 'Benjamin Button', '100Years@sleep.demo', '1 Time Drive, Wonderland', '$2y$10$EJp5HgUTgtc7ZkLLk5E7WOobVhxfbdYIxY67ORKgP1bhox7JdarPq', 'driver', NULL, 1),
-- Password: 5zzzzz
(15, 'The New Guy', 'new@brandnew.justborn', '7 JIT Road, Demoland, CPSCville', '$2y$10$wXBrppfUgAzCnV9ocM7p1eQmmfAIKCr6odQOwyeYPp1QxvYJUdibK', 'driver', NULL, 1),
-- Password: demo
-- SPONSORS
(16, 'Example Sponsor', 'sponsor.ex@mple.org', '123 Good Sponsor Way, Clemson, SC', '$2y$10$GHjM4sHCkZIi8BHgGJPAIuRrSyJQAseVOElQmqId96fTMqfI11jRu', 'sponsor', NULL, 1),
-- Password: LOGIN
(17, 'Former Sponsor Account', 'warden@closed.org', '123 Abc Xyz, Clemson, SC', '$2y$10$rIjnM9oguNSoqtkz0VKA3OXBDFn194oamea5J6bSPxIEDnp5xh5Vm', 'sponsor', NULL, 1),
-- Password: sPeCi@1
(18, 'Best Sponsor', 'number1@sales.demo', '2 Corporate Ladder, Clemson, SC', '$2y$10$eeNtt2ona1fNYD3r4OJ/XuE1oiOv9s.Vo/o3UzVCkRgX3k4GZ4LOG', 'sponsor', NULL, 1),
-- Password: CLIMB
(19, 'Competitive Sponsor', 'become1@motivate.genie', 'n/a', '$2y$10$a6fL0r3uaLdRV0oqr61Kbu0yja1.WrAm4hlA2yGaD3b1VG2TuS1q2', 'sponsor', NULL, 1),
-- Password: xyz123
(20, 'Not Mayhem', 'actuallyMayhem@allstate.demo', 'Your Place of Interest', '$2y$10$kPPxtq3p.VsC0lXBCzc6aetREZk533mEwl2rwiMUzmixmZyOjFffS', 'sponsor', NULL, 1),
-- Password: likeME
(21, 'Gecko Ins.', 'stickytoes@geico.demo', 'n/a', '$2y$10$f6crSRHP/s4COeIHiH08e.oZ/sLraRXVFeB9BhMvXKeX/SGO.tS4a', 'sponsor', NULL, 1),
-- Password: 15%gecko
(22, 'Actually Flo', 'flo@progressive.demo', 'VR Dominion #3', '$2y$10$uo26XMkEZpBTrZ9TbsLlneU4yQ/clofxevG0W8Jgv0GicQvotfcmC', 'sponsor', NULL, 1),
-- Password: getINtheVAN
(23, 'Example Buddy', 'buddy.ex@mple.demo', '5 Demo Way, Clemson, SC', '$2y$10$1C4XcG2KTB8csETUlM2V8udL2JN91aoij5QRIhsbrXkHeOKGpHxxq', 'sponsor', NULL, 1);
-- Password: demo


-- Banned Users
-- Automatically initiated, but we have to update banned user's bans
-- banned is banned, what a surprise
UPDATE `account_bans` SET product_report_mute = 1, org_report_mute = 1, pfp_report_mute = 1, allowed_change_pfp = 1, login_suspended = ADDTIME(NOW(), SEC_TO_TIME(36000)) WHERE account_id = 7;

-- Password Resets
-- Example User has completed one already
-- Closed Sponsor Account is required to complete one by an administrator
INSERT INTO `DriverRewards4910`.`password_resets` (`account_id`, `reset_requested_by_id`, `temp_hashed_password`, `creation_time`, `reset_complete`, `force_reset`) VALUES
('6', NULL, '$2y$10$xCuasL6S8m0XPAG.GBDW/OHdbWsg56OqZWUcWL.QmSghxz2yIbcg2', NULL, '1', NULL),
('8', '16', NULL, NULL, NULL, '1');

-- Profile Picture Flags
-- I mean, why do you think banned is banned?
INSERT INTO `profile_picture_flags` (`account_id`, `reporter_id`, `dismissed`) VALUES
(7, 1, 1);

-- Account Creation / Deletion is auto-populated by triggers. NO interaction required, just insert into `account` or switch the `live_account` bit on an account

-- Admin Messages
INSERT INTO `admin_messages` (`admin_message_id`, `sender_id`, `message_contents`, `sent_time`) VALUES
(1, 3, 'This is test of admin warning messages on the site.', NULL);

-- Catalog Items
-- Just two per sponsor for now
INSERT INTO `catalog_items` (`catalog_id`, `sponsor_org_id`, `api_reference`, `product_name`, `category`, `product_description`, `product_visible`, `product_deleted`, `gallery_pic`, `url`, `unit_price`) VALUES
(1, 1, 323758657638, "Military High Power Green Laser Pointer Lazer Pen", "Toy", NULL, 1, NULL, "http://thumbs3.ebaystatic.com/m/mbscIm-8TbC7AqVShs1tXRA/140.jpg", "http://www.ebay.com/itm/Military-High-Power-Green-Laser-Pointer-Lazer-Pen-Star-Cap-18650-Battery-USA-/323758657638", 7.5),
(2, 1, 123714280111, "Revoltech Yamaguchi No. 31  Robo Getter 1 Figure", "Toy", NULL, 1, NULL, "http://thumbs4.ebaystatic.com/m/mqlNYS0pIkjvWubNLNfXMew/140.jpg", "http://www.ebay.com/itm/Revoltech-Yamaguchi-No-31-Robo-Getter-1-Figure-/123714280111", 19.80),
(3, 2, 382892077729, "[P..D..F]  The C++  Programming language", "Textbooks, Education", NULL, 1, NULL, "http://thumbs2.ebaystatic.com/m/mg4kztCNHIfZPlOTiAoj29g/140.jpg", "http://www.ebay.com/itm/P-D-F-C-Programming-language-/382892077729", 1.49),
(4, 2, 382859622331, "[P..D..F]  PHP MYSQL & JavaScript all-in-one for dummies", "Textbooks, Education", NULL, 1, NULL, "http://thumbs4.ebaystatic.com/m/mx6JLY1wFOSFwQWSIGYruyA/140.jpg", "http://www.ebay.com/itm/P-D-F-PHP-MYSQL-JavaScript-all-in-one-dummies-/382859622331", 1.49),
(5, 3, 143182074593, "61 Key Digital Music Electronic Keyboard 4 Kids", "Electronic Keyboards", NULL, 1, NULL, "http://thumbs2.ebaystatic.com/m/mqnfx95TrCDM--6uR73qsyQ/140.jpg", "http://www.ebay.com/itm/61-Key-Digital-Music-Electronic-Keyboard-Kids-Electric-Piano-Organ-w-Microphone-/143182074593", 22.99),
(6, 3, 173344518504, "mini Wireless Keyboard Touchpad", "Keyboards", NULL, 1, NULL, "http://thumbs1.ebaystatic.com/m/m7d1koei4YII50vxAvkfIWw/140.jpg", "http://www.ebay.com/itm/mini-i8-2-4GHZ-mini-Wireless-Keyboard-Touchpad-Smart-TV-Android-Box-PC-HTPC-/173344518504", 8.95),
(7, 4, 173576084693, "Strawberry Seeds", "Seeds & Bulbs", NULL, 1, NULL, "http://thumbs2.ebaystatic.com/m/mqxtvbxwsuWvoe0obw3fKbg/140.jpg", "http://www.ebay.com/itm/Strawberry-One-Time-Fragaria-Ananassa-25-Seeds-/173576084693", 1.89),
(8, 4, 233003612853, "Men's Running Shoes", "Athletic Shoes", NULL, 1, NULL, "http://thumbs2.ebaystatic.com/pict/233003612853404000000001_1.jpg", "http://www.ebay.com/itm/Mens-Fashion-Running-Breathable-Shoes-Sports-Casual-Walking-Athletic-Sneakers-/233003612853?var=532516946970", 19.99);

-- Catalog Item Reviews
-- Only for the open sponsor's items
INSERT INTO `catalog_item_reviews` (`catalog_review_id`, `catalog_id`, `driver_account_id`, `rating`, `review`, `is_deleted`, `creation_time`) VALUES
(1, 1, 6, 3, 'I left a review!', 0, NULL),
(2, 2, 8, 1, 'This review should be deleted', 1, NULL);

-- Catalog Item Review Flags
-- Sample reports
INSERT INTO `catalog_item_review_flags` (`catalog_review_id`, `reporter_id`, `dismissed`) VALUES
(1, 9, 0),
(1, 10, 1);

-- Catalog Item Review Responses
-- Sample response
INSERT INTO `catalog_item_review_responses` (`respond_to_review_id`, `account_id`, `response`, `is_deleted`, `creation_time`) VALUES
(1, 16, 'Glad you reviewed the item! Enjoy!', 0, NULL);

-- Closed Sponsor Keys
-- Documenting the struggle of getting `banned` into `Closed Org`
-- Example has also joined the closed org
INSERT INTO `closed_sponsor_keys` (`sponsor_org_id`, `key_creator_id`, `key_redeemer_id`, `join_key`, `status`, `creation_time`) VALUES
(2, 17, 9, 'missedOpportunity', 'deactivated', NULL),
(2, 17, 8, 'JOINTHEORG', 'used', NULL),
(2, 17, 7, 'keyToBeDeactivated', 'pending', NULL),
(2, 17, 6, 'joinUs!', 'used', NULL);

-- Open Sponsor Applications
INSERT INTO `open_sponsor_applications` (`driver_account_id`, `sponsor_org_id`, `auth_account_id`, `status`, `creation_time`) VALUES
(6, 1, 18, 'accepted', NULL),
(7, 1, 16, 'accepted', NULL),
(8, 1, 16, 'accepted', NULL),
(9, 1, 18, 'accepted', NULL),
(12, 1, 18, 'accepted', NULL),
(13, 1, 18, 'accepted', NULL),
(6, 2, 17, 'accepted', NULL),
(8, 2, 22, 'accepted', NULL),
(11, 2, 22, 'accepted', NULL),
(12, 2, 22, 'accepted', NULL),
(15, 2, 22, 'accepted', NULL),
(10, 3, 19, 'accepted', NULL),
(12, 3, 21, 'accepted', NULL),
(14, 3, 19, 'accepted', NULL),
(10, 4, 20, 'accepted', NULL),
(12, 4, 23, 'accepted', NULL),
(13, 4, 20, 'accepted', NULL),
(13, 2, 22, 'rejected', NULL),
(11, 3, 21, 'rejected', NULL),
(6, 3, 19, 'rejected', NULL),
(6, 4, 23, 'rejected', NULL),
(7, 2, 17, 'rejected', NULL),
(11, 1, NULL, 'pending', NULL),
(13, 1, NULL, 'pending', NULL),
(10, 2, NULL, 'pending', NULL),
(7, 3, NULL, 'pending', NULL),
(8, 3, NULL, 'pending', NULL),
(9, 4, NULL, 'pending', NULL),
(11, 4, NULL, 'pending', NULL);

-- Open Sponsor Reviews
INSERT INTO `open_sponsor_reviews` (`sponsor_review_id`, `sponsor_org_id`, `driver_account_id`, `creation_time`, `rating`, `review`, `is_deleted`) VALUES
(1, 1, 6, NULL, 3, 'I like this org.', 0),
(2, 1, 8, NULL, 3, 'Whoops. This should have been deleted.', 1);

-- Open Sponsor Review Flags
INSERT INTO `open_sponsor_review_flags` (`sponsor_review_id`, `reporter_id`, `dismissed`) VALUES
(2, 10, 1);

-- Open Sponsor Review Replies
INSERT INTO `open_sponsor_review_replies` (`respond_to_review_id`, `account_id`, `reply_depth`, `creation_time`, `response`, `is_deleted`) VALUES
(2, 18, 1, NULL, 'Gotcha, deleted it!', 0);

-- Driver Cart
-- Example account has some stuff
INSERT INTO `driver_cart` (`driver_account_id`, `catalog_id`, `quantity`) VALUES
(8, 2, 3);

-- Driver in Org
-- Example has joined both orgs
-- banned used to be in closed, has since left
-- Sponsors are in their respective sponsor orgs
INSERT INTO `DriverRewards4910`.`driver_in_org` (`driver_account_id`, `sponsor_org_id`) VALUES
-- SPONSORS INTO THEIR ORGS
(16, 1),
(17, 2),
(18, 1),
(19, 3),
(20, 4),
(21, 3),
(22, 2),
(23, 4),
-- DRIVERS INTO THEIR ORGS
(6, 1),
(6, 2),
(7, 1),
(8, 1),
(8, 2),
(9, 1),
(10, 3),
(10, 4),
(11, 2),
(12, 1),
(12, 2),
(12, 3),
(12, 4),
(13, 1),
(13, 2),
(13, 3),
(13, 4),
(14, 3),
(15, 2);
DELETE FROM `driver_in_org` WHERE `driver_account_id` = 7 AND `sponsor_org_id` = 1;
DELETE FROM `driver_in_org` WHERE `driver_account_id` = 13 AND `sponsor_org_id` != 4;

-- Invalid Password Log
INSERT INTO `invalid_password_log` (`account_id`, `log_time`) VALUES
(9, NULL);

-- Orders
INSERT INTO `order_transactions` (`order_id`, `driver_account_id`, `auth_account_id`, `sponsor_org_id`, `shipping_address`, `creation_time`) VALUES
(1, 6, 6, 1, "123 Example Dr., Clemson, SC", "2019-01-03 03:03:03"),
(2, 6, 16, 1, "home", "2019-04-03 10:01:11"),
(3, 6, 6, 1, "invalid_address", "2019-04-02 00:00:00"),
(4, 6, 16, 1, "123 Example Drive, Clemson, SC", "2019-01-03 03:03:03");
-- SET TIMES TO ACTUALLY DESIRED VALUES
UPDATE `order_transactions` SET creation_time = TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 day)), fulfill_time = TIMESTAMP(DATE_SUB(NOW(), INTERVAL 27 day)) WHERE order_id = 1;
UPDATE `order_transactions` SET creation_time = TIMESTAMP(DATE_SUB(NOW(), INTERVAL 10 day)), fulfill_time = TIMESTAMP(DATE_SUB(NOW(), INTERVAL 4 day)), complain = 'Not a complaint, thanks for making this order for me, Sponsor!' WHERE order_id = 2;
UPDATE `order_transactions` SET creation_time = TIMESTAMP(DATE_SUB(NOW(), INTERVAL 100 hour)), fulfill_time = TIMESTAMP(DATE_SUB(NOW(), INTERVAL 36 hour)) WHERE order_id = 3;
UPDATE `order_transactions` SET creation_time = TIMESTAMP(DATE_SUB(NOW(), INTERVAL 26 hour)) WHERE order_id = 4;

-- Point Transactions
INSERT INTO `point_transactions` (`point_change_id`, `driver_account_id`, `auth_account_id`, `sponsor_org_id`, `point_change_amt`, `change_time`, `change_reason`) VALUES
(1, 6, 16, 1, 500, "2019-01-01 05:05:05", "Joining bonus"),
(2, 7, 16, 1, 500, "2019-01-01 05:05:10", "Joining bonus"),
(3, 6, 6, 1, -200, TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 day)), "Order #1"),
(4, 6, 6, 1, -100, TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 day)), "Order #1"),
(5, 6, 16, 1, -30, TIMESTAMP(DATE_SUB(NOW(), INTERVAL 10 day)), "Order #2"),
(6, 6, 6, 1, -10, TIMESTAMP(DATE_SUB(NOW(), INTERVAL 100 hour)), "Order #3"),
(7, 6, 16, 1, -20, TIMESTAMP(DATE_SUB(NOW(), INTERVAL 26 hour)), "Order #4");

-- Item Transactions
-- Putting the above 2 together
INSERT INTO `item_transactions` (`order_id`, `point_transaction_id`, `item_id`, `quantity_change`) VALUES
(1, 3, 1, 20),
(1, 4, 2, 5),
(2, 5, 1, 3),
(3, 6, 1, 1),
(4, 7, 2, 3);

-- Unrecognized Logins
-- Prompted Example User to reset their password
INSERT INTO `unrecognized_login` (`account_id`, `MAC Address`, `login_time`) VALUES
(1, 'evil', NULL);

-- Change Email Preferences for Banned User
UPDATE `email_preferences` SET `order_problem` = NULL, `order_placed` = NULL, `points_changed` = NULL WHERE `driver_account_id` = 7;

