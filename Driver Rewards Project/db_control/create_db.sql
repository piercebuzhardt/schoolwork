CREATE TABLE IF NOT EXISTS `account` (
  `account_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `email_address` varchar(100) NOT NULL,
  `shipping_address` varchar(150) NOT NULL,
  `hashed_password` varchar(255) NOT NULL,
  `role` enum('driver','sponsor','admin') NOT NULL,
  `path_to_pfp` varchar(256) DEFAULT NULL,
  `live_account` INT (1) NULL DEFAULT '1',
  PRIMARY KEY (`account_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email_address` (`email_address`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

DROP TRIGGER IF EXISTS `account_deleted_or_restored`;
DELIMITER //
CREATE TRIGGER `account_deleted_or_restored` AFTER UPDATE ON `account`
 FOR EACH ROW IF( OLD.live_account = '1' AND (NEW.live_account IS NULL OR NEW.live_account = '0')) THEN INSERT INTO `account_creation_deletion`
    VALUES (NEW.account_id, NULL , NULL , NULL);
    ELSEIF((OLD.live_account IS NULL OR OLD.live_account = '0') AND NEW.live_account = TRUE) THEN INSERT INTO `account_creation_deletion`
    VALUES (NEW.account_id, NULL , NULL , '1');
    END IF
//
DELIMITER ;
DROP TRIGGER IF EXISTS `init_new_account`;
DELIMITER //
CREATE TRIGGER `init_new_account` AFTER INSERT ON `account`
FOR EACH ROW BEGIN
INSERT INTO account_creation_deletion VALUES (NEW.account_id, NULL , '1');
INSERT INTO account_bans VALUES (NEW.account_id, NULL , '0', '0', '0', '0');
INSERT INTO email_preferences (driver_account_id) VALUES (NEW.account_id);
END
//
DELIMITER ;

CREATE TABLE IF NOT EXISTS `account_bans` (
  `account_id` int(11) NOT NULL,
  `login_suspended` datetime DEFAULT NULL,
  `product_report_mute` int(1) NULL,
  `org_report_mute` int(1) NULL,
  `pfp_report_mute` int(1) NULL,
  `allowed_change_pfp` int(1) NULL,
  KEY `account_id` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `account_creation_deletion` (
  `account_id` int(11) NOT NULL,
  `event_time` datetime DEFAULT NULL,
  `was_created` int(1) NULL DEFAULT '1',
  KEY `account_id` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TRIGGER IF EXISTS `setAccountCreationDeletionDateTime`;
DELIMITER //
CREATE TRIGGER `setAccountCreationDeletionDateTime` BEFORE INSERT ON `account_creation_deletion`
FOR EACH ROW
IF NEW.event_time IS NULL THEN SET NEW.event_time = NOW();
END IF
//
DELIMITER ;

CREATE TABLE IF NOT EXISTS `email_preferences` (
  `driver_account_id` int(11) NOT NULL,
  `order_problem` int(11) DEFAULT '1',
  `order_placed` int(11) DEFAULT '1',
  `points_changed` int(11) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `admin_messages` (
  `admin_message_id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `message_contents` varchar(500) NOT NULL,
  `sent_time` datetime DEFAULT NULL,
  PRIMARY KEY (`admin_message_id`),
  KEY `sender_id` (`sender_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

DROP TRIGGER IF EXISTS `setAdminMessageSendDateTime`;
DELIMITER //
CREATE TRIGGER `setAdminMessageSendDateTime` BEFORE INSERT ON `admin_messages`
FOR EACH ROW
IF NEW.sent_time IS NULL THEN SET NEW.sent_time = NOW();
END IF
//
DELIMITER ;

CREATE TABLE IF NOT EXISTS `catalog_items` (
  `catalog_id` int(11) NOT NULL AUTO_INCREMENT,
  `sponsor_org_id` int(11) NOT NULL,
  `api_reference` varchar(200) DEFAULT NULL,
  `product_name` varchar(50) NOT NULL,
  `category` varchar(50) NOT NULL,
  `product_description` varchar(500) DEFAULT NULL,
  `product_visible` int(1) NULL DEFAULT '1',
  `product_deleted` int(1) NULL DEFAULT NULL,
  `creation_time` datetime DEFAULT NULL,
  `gallery_pic` varchar(255) NULL,
  `url` varchar(255) NOT NULL,
  `unit_price` double NOT NULL,
  PRIMARY KEY (`catalog_id`),
  KEY `sponsor_org_id` (`sponsor_org_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

DROP TRIGGER IF EXISTS `setProductCreationDateTime`;
DELIMITER //
CREATE TRIGGER `setProductCreationDateTime` BEFORE INSERT ON `catalog_items`
FOR EACH ROW
IF NEW.creation_time IS NULL THEN SET NEW.creation_time = NOW();
END IF
//
DELIMITER ;

CREATE TABLE IF NOT EXISTS `catalog_item_reviews` (
  `catalog_review_id` int(11) NOT NULL AUTO_INCREMENT,
  `catalog_id` int(11) NOT NULL,
  `driver_account_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `review` varchar(500) DEFAULT NULL,
  `is_deleted` int(1) NULL DEFAULT NULL,
  `creation_time` datetime DEFAULT NULL,
  PRIMARY KEY (`catalog_review_id`),
  KEY `catalog_id` (`catalog_id`),
  KEY `driver_account_id` (`driver_account_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

DROP TRIGGER IF EXISTS `setCreationDateTime`;
DELIMITER //
CREATE TRIGGER `setCreationDateTime` BEFORE INSERT ON `catalog_item_reviews`
FOR EACH ROW
IF NEW.creation_time IS NULL THEN SET NEW.creation_time = NOW();
END IF
//
DELIMITER ;

CREATE TABLE IF NOT EXISTS `catalog_item_review_flags` (
  `catalog_review_id` int(11) NOT NULL,
  `reporter_id` int(11) NOT NULL,
  `dismissed` int(1) NULL DEFAULT NULL,
  KEY `catalog_review_id` (`catalog_review_id`),
  KEY `reporter_id` (`reporter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `catalog_item_review_responses` (
  `respond_to_review_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `response` varchar(500) NOT NULL,
  `is_deleted` int(1) NULL DEFAULT NULL,
  `creation_time` datetime DEFAULT NULL,
  KEY `respond_to_review_id` (`respond_to_review_id`),
  KEY `account_id` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TRIGGER IF EXISTS `setCatalogItemReveiwResponseCreationDateTime`;
DELIMITER //
CREATE TRIGGER `setCatalogItemReveiwResponseCreationDateTime` BEFORE INSERT ON `catalog_item_review_responses`
FOR EACH ROW
IF NEW.creation_time IS NULL THEN SET NEW.creation_time = NOW();
END IF
//
DELIMITER ;

CREATE TABLE IF NOT EXISTS `closed_sponsor_keys` (
  `sponsor_org_id` int(11) NOT NULL,
  `key_creator_id` int(11) NOT NULL,
  `key_redeemer_id` int(11) DEFAULT NULL,
  `join_key` varchar(50) NOT NULL,
  `status` enum('pending','used','deactivated') NOT NULL DEFAULT 'pending',
  `creation_time` datetime DEFAULT NULL,
  KEY `sponsor_org_id` (`sponsor_org_id`),
  KEY `key_creator_id` (`key_creator_id`),
  KEY `key_redeemer_id` (`key_redeemer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TRIGGER IF EXISTS `setKeyCreationDateTime`;
DELIMITER //
CREATE TRIGGER `setKeyCreationDateTime` BEFORE INSERT ON `closed_sponsor_keys`
FOR EACH ROW
IF NEW.creation_time IS NULL THEN SET NEW.creation_time = NOW();
END IF
//
DELIMITER ;

CREATE TABLE IF NOT EXISTS `driver_cart` (
  `driver_account_id` int(11) NOT NULL,
  `catalog_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  KEY `driver_account_id` (`driver_account_id`),
  KEY `catalog_id` (`catalog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `driver_changes_org` (
  `driver_account_id` int(11) NOT NULL,
  `sponsor_org_id` int(11) NOT NULL,
  `joined_org` int(1) NULL DEFAULT '1',
  `event_time` datetime DEFAULT NULL,
  KEY `driver_account_id` (`driver_account_id`),
  KEY `sponsor_org_id` (`sponsor_org_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TRIGGER IF EXISTS `setJoinLeaveDateTime`;
DELIMITER //
CREATE TRIGGER `setJoinLeaveDateTime` BEFORE INSERT ON `driver_changes_org`
FOR EACH ROW
IF NEW.event_time IS NULL THEN SET NEW.event_time = NOW();
END IF
//
DELIMITER ;

CREATE TABLE IF NOT EXISTS `driver_in_org` (
  `driver_account_id` int(11) NOT NULL,
  `sponsor_org_id` int(11) NOT NULL,
  KEY `driver_account_id` (`driver_account_id`),
  KEY `sponsor_org_id` (`sponsor_org_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TRIGGER IF EXISTS `driver_enters_org`;
DELIMITER //
CREATE TRIGGER `driver_enters_org` BEFORE INSERT ON `driver_in_org`
 FOR EACH ROW INSERT INTO `driver_changes_org` VALUES (NEW.driver_account_id, NEW.sponsor_org_id, 1, NOW())
//
DELIMITER ;
DROP TRIGGER IF EXISTS `driver_leaves_org`;
DELIMITER //
CREATE TRIGGER `driver_leaves_org` BEFORE DELETE ON `driver_in_org`
 FOR EACH ROW INSERT INTO `driver_changes_org` VALUES (OLD.driver_account_id, OLD.sponsor_org_id, 0, NOW())
//
DELIMITER ;

CREATE TABLE IF NOT EXISTS `driver_wish_list` (
  `driver_account_id` int(11) NOT NULL,
  `catalog_id` int(11) NOT NULL,
  KEY `catalog_id` (`catalog_id`),
  KEY `driver_account_id` (`driver_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `invalid_password_log` (
  `account_id` int(11) NOT NULL,
  `log_time` datetime DEFAULT NULL,
  KEY `account_id` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TRIGGER IF EXISTS `setInvalidPasswordDateTime`;
DELIMITER //
CREATE TRIGGER `setInvalidPasswordDateTime` BEFORE INSERT ON `invalid_password_log`
FOR EACH ROW
IF NEW.log_time IS NULL THEN SET NEW.log_time = NOW();
END IF
//
DELIMITER ;

CREATE TABLE IF NOT EXISTS `item_transactions` (
  `order_id` int(11) NOT NULL,
  `point_transaction_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity_change` int(11) NOT NULL,
  KEY `order_id` (`order_id`),
  KEY `point_transaction_id` (`point_transaction_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `open_sponsor_applications` (
  `driver_account_id` int(11) NOT NULL,
  `sponsor_org_id` int(11) NOT NULL,
  `auth_account_id` int(11) DEFAULT NULL,
  `status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  `creation_time` datetime DEFAULT NULL,
  KEY `driver_account_id` (`driver_account_id`),
  KEY `sponsor_org_id` (`sponsor_org_id`),
  KEY `auth_account_id` (`auth_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TRIGGER IF EXISTS `setApplicationSubmissionDateTime`;
DELIMITER //
CREATE TRIGGER `setApplicationSubmissionDateTime` BEFORE INSERT ON `open_sponsor_applications`
FOR EACH ROW
IF NEW.creation_time IS NULL THEN SET NEW.creation_time = NOW();
END IF
//
DELIMITER ;

CREATE TABLE IF NOT EXISTS `open_sponsor_reviews` (
  `sponsor_review_id` int(11) NOT NULL AUTO_INCREMENT,
  `sponsor_org_id` int(11) NOT NULL,
  `driver_account_id` int(11) NOT NULL,
  `creation_time` datetime DEFAULT NULL,
  `rating` int(11) NOT NULL,
  `review` varchar(500) DEFAULT NULL,
  `is_deleted` int(1) NULL DEFAULT NULL,
  PRIMARY KEY (`sponsor_review_id`),
  KEY `sponsor_org_id` (`sponsor_org_id`),
  KEY `driver_account_id` (`driver_account_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

DROP TRIGGER IF EXISTS `setOpenSponsorReviewDateTime`;
DELIMITER //
CREATE TRIGGER `setOpenSponsorReviewDateTime` BEFORE INSERT ON `open_sponsor_reviews`
FOR EACH ROW
IF NEW.creation_time IS NULL THEN SET NEW.creation_time = NOW();
END IF
//
DELIMITER ;

CREATE TABLE IF NOT EXISTS `open_sponsor_review_flags` (
  `sponsor_review_id` int(11) NOT NULL,
  `reporter_id` int(11) NOT NULL,
  `dismissed` int(1) NULL DEFAULT NULL,
  KEY `sponsor_review_id` (`sponsor_review_id`),
  KEY `reporter_id` (`reporter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `open_sponsor_review_replies` (
  `respond_to_review_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `reply_depth` int(11) NOT NULL,
  `creation_time` datetime DEFAULT NULL,
  `response` varchar(500) NOT NULL,
  `is_deleted` int(1) NULL DEFAULT NULL,
  KEY `respond_to_review_id` (`respond_to_review_id`),
  KEY `account_id` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TRIGGER IF EXISTS `setOpenSponsorReviewRepliesDateTime`;
DELIMITER //
CREATE TRIGGER `setOpenSponsorReviewRepliesDateTime` BEFORE INSERT ON `open_sponsor_review_replies`
FOR EACH ROW
IF NEW.creation_time IS NULL THEN SET NEW.creation_time = NOW();
END IF
//
DELIMITER ;

CREATE TABLE IF NOT EXISTS `order_transactions` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `driver_account_id` int(11) NOT NULL,
  `auth_account_id` int(11) NOT NULL,
  `sponsor_org_id` int(11) NOT NULL,
  `shipping_address` varchar(150) NOT NULL,
  `creation_time` datetime DEFAULT NULL,
  `fulfill_time` datetime DEFAULT NULL,
  `problem` varchar(250) DEFAULT NULL,
  `complain` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  KEY `driver_account_id` (`driver_account_id`),
  KEY `auth_account_id` (`auth_account_id`),
  KEY `sponsor_org_id` (`sponsor_org_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

DROP TRIGGER IF EXISTS `setOrderCreationDateTime`;
DELIMITER //
CREATE TRIGGER `setOrderCreationDateTime` BEFORE INSERT ON `order_transactions`
FOR EACH ROW
IF NEW.creation_time IS NULL THEN SET NEW.creation_time = NOW();
END IF
//
DELIMITER ;

CREATE TABLE IF NOT EXISTS `org_logo_bans` (
  `banned_org_id` int(11) NOT NULL,
  UNIQUE KEY `banned_org_id` (`banned_org_id`),
  KEY `banned_org_id_2` (`banned_org_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `password_resets` (
  `account_id` int(11) NOT NULL,
  `reset_requested_by_id` int(11) DEFAULT NULL,
  `temp_hashed_password` varchar(255) NULL,
  `creation_time` datetime DEFAULT NULL,
  `reset_complete` int(1) NULL DEFAULT NULL,
  `force_reset` int(1) NULL DEFAULT NULL,
  KEY `account_id` (`account_id`),
  KEY `reset_requested_by_id` (`reset_requested_by_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TRIGGER IF EXISTS `setPasswordResetDateTime`;
DELIMITER //
CREATE TRIGGER `setPasswordResetDateTime` BEFORE INSERT ON `password_resets`
FOR EACH ROW
IF NEW.creation_time IS NULL THEN SET NEW.creation_time = NOW();
END IF
//
DELIMITER ;

CREATE TABLE IF NOT EXISTS `point_transactions` (
  `point_change_id` int(11) NOT NULL AUTO_INCREMENT,
  `driver_account_id` int(11) NOT NULL,
  `auth_account_id` int(11) NOT NULL,
  `sponsor_org_id` int(11) NOT NULL,
  `point_change_amt` int(11) NOT NULL,
  `change_time` datetime DEFAULT NULL,
  `change_reason` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`point_change_id`),
  KEY `driver_account_id` (`driver_account_id`),
  KEY `auth_account_id` (`auth_account_id`),
  KEY `sponsor_org_id` (`sponsor_org_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

DROP TRIGGER IF EXISTS `setPointChangeDateTime`;
DELIMITER //
CREATE TRIGGER `setPointChangeDateTime` BEFORE INSERT ON `point_transactions`
FOR EACH ROW
IF NEW.change_time IS NULL THEN SET NEW.change_time = NOW();
END IF
//
DELIMITER ;

CREATE TABLE IF NOT EXISTS `profile_picture_flags` (
  `account_id` int(11) NOT NULL,
  `reporter_id` int(11) NOT NULL,
  `dismissed` int(1) NULL DEFAULT NULL,
  KEY `account_id` (`account_id`),
  KEY `reporter_id` (`reporter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `sponsor_org` (
  `sponsor_org_id` int(11) NOT NULL AUTO_INCREMENT,
  `org_name` varchar(50) NOT NULL,
  `org_email_address` varchar(100) NOT NULL,
  `org_bio` varchar(500) DEFAULT NULL,
  `org_url` varchar(256) DEFAULT NULL,
  `path_to_org_logo` varchar(256) DEFAULT NULL,
  `points_to_dollars` double NOT NULL,
  `application_open` int(1) NULL DEFAULT '1',
  `live_org` int(1) NULL DEFAULT '1',
  PRIMARY KEY (`sponsor_org_id`),
  UNIQUE KEY `org_name` (`org_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

DROP TRIGGER IF EXISTS `org_deleted_or_restored`;
DELIMITER //
CREATE TRIGGER `org_deleted_or_restored` AFTER UPDATE ON `sponsor_org`
 FOR EACH ROW IF(OLD.live_org IS NOT NULL AND (NEW.live_org IS NULL OR NEW.live_org = 0)) THEN INSERT INTO `sponsor_org_creation_deletion` (`sponsor_org_id`, `was_created`)
    VALUES (NEW.sponsor_org_id, 0);
    ELSEIF((OLD.live_org IS NULL OR OLD.live_org = '0') AND NEW.live_org IS NOT NULL) THEN INSERT INTO `sponsor_org_creation_deletion` (`sponsor_org_id`, `was_created`)
    VALUES (NEW.sponsor_org_id, 1);
    END IF
//
DELIMITER ;

DROP TRIGGER IF EXISTS `init_new_org`;
DELIMITER //
CREATE TRIGGER `init_new_org` AFTER INSERT ON `sponsor_org`
FOR EACH ROW BEGIN
INSERT INTO sponsor_org_creation_deletion VALUES (NEW.sponsor_org_id, NULL , '1');
END
//
DELIMITER ;

CREATE TABLE IF NOT EXISTS `sponsor_org_creation_deletion` (
  `sponsor_org_id` int(11) NOT NULL,
  `event_time` datetime DEFAULT NULL,
  `was_created` int(1) NULL DEFAULT '1',
  KEY `sponsor_org_id` (`sponsor_org_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TRIGGER IF EXISTS `setDateCreateOrg`;
DELIMITER //
CREATE TRIGGER `setDateCreateOrg` BEFORE INSERT ON `sponsor_org_creation_deletion`
 FOR EACH ROW SET NEW.event_time = NOW()
//
DELIMITER ;

CREATE TABLE IF NOT EXISTS `unrecognized_login` (
  `account_id` int(11) NOT NULL,
  `MAC Address` varchar(20) NOT NULL,
  `login_time` datetime DEFAULT NULL,
  KEY `account_id` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TRIGGER IF EXISTS `unrecognizedLoginDateTime`;
DELIMITER //
CREATE TRIGGER `unrecognizedLoginDateTime` BEFORE INSERT ON `unrecognized_login`
FOR EACH ROW
IF NEW.login_time IS NULL THEN SET NEW.login_time = NOW();
END IF
//
DELIMITER ;

ALTER TABLE `account_bans`
  ADD CONSTRAINT `account_bans_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `account_creation_deletion`
  ADD CONSTRAINT `account_creation_deletion_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `admin_messages`
  ADD CONSTRAINT `admin_messages_ibfk1` FOREIGN KEY (`sender_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `catalog_items`
  ADD CONSTRAINT `catalog_items_ibfk_1` FOREIGN KEY (`sponsor_org_id`) REFERENCES `sponsor_org` (`sponsor_org_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `catalog_item_reviews`
  ADD CONSTRAINT `catalog_item_reviews_ibfk_1` FOREIGN KEY (`catalog_id`) REFERENCES `catalog_items` (`catalog_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `catalog_item_reviews_ibfk_2` FOREIGN KEY (`driver_account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `catalog_item_review_flags`
  ADD CONSTRAINT `catalog_item_review_flags_ibfk_2` FOREIGN KEY (`reporter_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `catalog_item_review_flags_ibfk_1` FOREIGN KEY (`catalog_review_id`) REFERENCES `catalog_item_reviews` (`catalog_review_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `catalog_item_review_responses`
  ADD CONSTRAINT `catalog_item_review_responses_ibfk_1` FOREIGN KEY (`respond_to_review_id`) REFERENCES `catalog_item_reviews` (`catalog_review_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `catalog_item_review_responses_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `closed_sponsor_keys`
  ADD CONSTRAINT `closed_sponsor_keys_ibfk_1` FOREIGN KEY (`sponsor_org_id`) REFERENCES `sponsor_org` (`sponsor_org_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `closed_sponsor_keys_ibfk_2` FOREIGN KEY (`key_creator_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `closed_sponsor_keys_ibfk_3` FOREIGN KEY (`key_redeemer_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `driver_cart`
  ADD CONSTRAINT `driver_cart_ibfk_2` FOREIGN KEY (`catalog_id`) REFERENCES `catalog_items` (`catalog_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `driver_cart_ibfk_1` FOREIGN KEY (`driver_account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `driver_changes_org`
  ADD CONSTRAINT `driver_changes_org_ibfk_1` FOREIGN KEY (`driver_account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `driver_changes_org_ibfk_2` FOREIGN KEY (`sponsor_org_id`) REFERENCES `sponsor_org` (`sponsor_org_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `driver_in_org`
  ADD CONSTRAINT `driver_in_org_ibfk_2` FOREIGN KEY (`sponsor_org_id`) REFERENCES `sponsor_org` (`sponsor_org_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `driver_in_org_ibfk_1` FOREIGN KEY (`driver_account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `driver_wish_list`
  ADD CONSTRAINT `driver_wish_list_ibfk_2` FOREIGN KEY (`catalog_id`) REFERENCES `catalog_items` (`catalog_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `driver_wish_list_ibfk_1` FOREIGN KEY (`driver_account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `invalid_password_log`
  ADD CONSTRAINT `invalid_password_log_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `item_transactions`
  ADD CONSTRAINT `item_transactions_ibfk_3` FOREIGN KEY (`item_id`) REFERENCES `catalog_items` (`catalog_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_transactions_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `order_transactions` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_transactions_ibfk_2` FOREIGN KEY (`point_transaction_id`) REFERENCES `point_transactions` (`point_change_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `open_sponsor_applications`
  ADD CONSTRAINT `open_sponsor_applications_ibfk_1` FOREIGN KEY (`driver_account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `open_sponsor_applications_ibfk_2` FOREIGN KEY (`sponsor_org_id`) REFERENCES `sponsor_org` (`sponsor_org_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `open_sponsor_applications_ibfk_3` FOREIGN KEY (`auth_account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `open_sponsor_reviews`
  ADD CONSTRAINT `open_sponsor_reviews_ibfk_1` FOREIGN KEY (`sponsor_org_id`) REFERENCES `sponsor_org` (`sponsor_org_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `open_sponsor_reviews_ibfk_2` FOREIGN KEY (`driver_account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `open_sponsor_review_flags`
  ADD CONSTRAINT `open_sponsor_review_flags_ibfk_2` FOREIGN KEY (`reporter_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `open_sponsor_review_flags_ibfk_1` FOREIGN KEY (`sponsor_review_id`) REFERENCES `open_sponsor_reviews` (`sponsor_review_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `open_sponsor_review_replies`
  ADD CONSTRAINT `open_sponsor_review_replies_ibfk_1` FOREIGN KEY (`respond_to_review_id`) REFERENCES `open_sponsor_reviews` (`sponsor_review_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `open_sponsor_review_replies_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `order_transactions`
  ADD CONSTRAINT `order_transactions_ibfk_1` FOREIGN KEY (`driver_account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_transactions_ibfk_2` FOREIGN KEY (`auth_account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_transactions_ibfk_3` FOREIGN KEY (`sponsor_org_id`) REFERENCES `sponsor_org` (`sponsor_org_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `org_logo_bans`
  ADD CONSTRAINT `org_logo_bans_ibfk_1` FOREIGN KEY (`banned_org_id`) REFERENCES `sponsor_org` (`sponsor_org_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `password_resets_ibfk_2` FOREIGN KEY (`reset_requested_by_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `point_transactions`
  ADD CONSTRAINT `point_transactions_ibfk_1` FOREIGN KEY (`driver_account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `point_transactions_ibfk_2` FOREIGN KEY (`auth_account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `point_transactions_ibfk_3` FOREIGN KEY (`sponsor_org_id`) REFERENCES `sponsor_org` (`sponsor_org_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `profile_picture_flags`
  ADD CONSTRAINT `profile_picture_flags_ibfk_2` FOREIGN KEY (`reporter_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `profile_picture_flags_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `unrecognized_login`
  ADD CONSTRAINT `unrecognized_login_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `email_preferences`
  ADD CONSTRAINT `email_preferences_ibfk_1` FOREIGN KEY (`driver_account_id`) REFERENCES `account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `sponsor_org_creation_deletion`
  ADD CONSTRAINT `sponsor_org_c_d_ibfk_1` FOREIGN KEY (`sponsor_org_id`) REFERENCES `sponsor_org` (`sponsor_org_id`) ON DELETE CASCADE ON UPDATE CASCADE;

