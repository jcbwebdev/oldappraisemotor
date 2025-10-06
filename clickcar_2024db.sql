/*
 Navicat MySQL Dump SQL

 Source Server         : UK Servers - 2021
 Source Server Type    : MySQL
 Source Server Version : 80042 (8.0.42)
 Source Host           : 94.229.69.34:3306
 Source Schema         : clickcar_2024db

 Target Server Type    : MySQL
 Target Server Version : 80042 (8.0.42)
 File Encoding         : 65001

 Date: 16/07/2025 15:59:34
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for AuctionBidHistory
-- ----------------------------
DROP TABLE IF EXISTS `AuctionBidHistory`;
CREATE TABLE `AuctionBidHistory` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `AuctionVehicleID` int DEFAULT NULL,
  `CustomerID` int DEFAULT NULL,
  `BidDateTime` datetime DEFAULT NULL,
  `BidActualAmount` varbinary(255) DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Records of AuctionBidHistory
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for AuctionBids
-- ----------------------------
DROP TABLE IF EXISTS `AuctionBids`;
CREATE TABLE `AuctionBids` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `AuctionVehicleID` int DEFAULT NULL,
  `BidDateTime` datetime DEFAULT NULL,
  `BidMaxAmount` varbinary(255) DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Records of AuctionBids
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for AuctionRooms
-- ----------------------------
DROP TABLE IF EXISTS `AuctionRooms`;
CREATE TABLE `AuctionRooms` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Title` varbinary(255) DEFAULT NULL,
  `Content` mediumblob,
  `ImgPath` varbinary(255) DEFAULT NULL,
  `ImgFIlename` varbinary(255) DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Records of AuctionRooms
-- ----------------------------
BEGIN;
INSERT INTO `AuctionRooms` (`ID`, `Title`, `Content`, `ImgPath`, `ImgFIlename`) VALUES (2, 0x3FBF056C13A214ACA864E96B42254B3D, 0x46529F799F27D1109DB63F722F87741A, 0x853B9A7572F8452A7E1F678DCA31C06E0193A93D2B8A2512846717F7294CC2C316FF5F3BAAC19547C61252E9CDA43A7B, 0x04BD626C1FEF9C78BBE2FC58AA88D9342947CBDA68D400F86B079D2804AABFF5);
INSERT INTO `AuctionRooms` (`ID`, `Title`, `Content`, `ImgPath`, `ImgFIlename`) VALUES (3, 0x3600C76FCF818A67EAD484C63A5B6B43, 0x5793ECF6A1A500D83DA18545AC11A278, 0x853B9A7572F8452A7E1F678DCA31C06E0193A93D2B8A2512846717F7294CC2C316FF5F3BAAC19547C61252E9CDA43A7B, 0x3C906434D251E370BFF23CADE0122F76B5DCF4B94AE3DC65BEAAEE162112E6EA);
INSERT INTO `AuctionRooms` (`ID`, `Title`, `Content`, `ImgPath`, `ImgFIlename`) VALUES (4, 0xBC543FB2D7DEA1D4C2C94A20A007BC74, 0x5793ECF6A1A500D83DA18545AC11A278, 0x853B9A7572F8452A7E1F678DCA31C06E0193A93D2B8A2512846717F7294CC2C316FF5F3BAAC19547C61252E9CDA43A7B, 0x6C91A9A7D04BE0DD14EC0F72557961B887D585E06ABDFBCADC05DA52B97A2728);
INSERT INTO `AuctionRooms` (`ID`, `Title`, `Content`, `ImgPath`, `ImgFIlename`) VALUES (5, 0xAA953D080FD57A07A98B86FEBB8B24D1, 0x5793ECF6A1A500D83DA18545AC11A278, 0x853B9A7572F8452A7E1F678DCA31C06E0193A93D2B8A2512846717F7294CC2C316FF5F3BAAC19547C61252E9CDA43A7B, 0xEA616EC31169B1D84C867E0BB90A42866145E7373251D53CD424C0052729243B);
COMMIT;

-- ----------------------------
-- Table structure for AuctionVehicles
-- ----------------------------
DROP TABLE IF EXISTS `AuctionVehicles`;
CREATE TABLE `AuctionVehicles` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `AuctionID` int DEFAULT NULL,
  `VehicleID` int DEFAULT NULL,
  `DisplayOrder` int DEFAULT NULL,
  `BuyItNow` varbinary(255) DEFAULT NULL,
  `BuyItNowPrice` varbinary(255) DEFAULT NULL,
  `BuyItNowPaid` varbinary(255) DEFAULT NULL,
  `OldBuyItNow` varbinary(255) DEFAULT NULL,
  `OldBuyItNowPrice` varbinary(255) DEFAULT NULL,
  `CurrentBidAmount` varbinary(100) DEFAULT NULL,
  `CurrentBidID` int DEFAULT NULL,
  `CurrentBidCustomerID` int DEFAULT NULL,
  `CustomerNotified` varbinary(255) DEFAULT NULL,
  `FinalBidAmount` varbinary(255) DEFAULT NULL,
  `FinalBidCustomerID` int DEFAULT NULL,
  `FinalBidCustomerDetails` mediumblob,
  `AuctionStatus` varbinary(255) DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Records of AuctionVehicles
-- ----------------------------
BEGIN;
INSERT INTO `AuctionVehicles` (`ID`, `AuctionID`, `VehicleID`, `DisplayOrder`, `BuyItNow`, `BuyItNowPrice`, `BuyItNowPaid`, `OldBuyItNow`, `OldBuyItNowPrice`, `CurrentBidAmount`, `CurrentBidID`, `CurrentBidCustomerID`, `CustomerNotified`, `FinalBidAmount`, `FinalBidCustomerID`, `FinalBidCustomerDetails`, `AuctionStatus`) VALUES (19, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `AuctionVehicles` (`ID`, `AuctionID`, `VehicleID`, `DisplayOrder`, `BuyItNow`, `BuyItNowPrice`, `BuyItNowPaid`, `OldBuyItNow`, `OldBuyItNowPrice`, `CurrentBidAmount`, `CurrentBidID`, `CurrentBidCustomerID`, `CustomerNotified`, `FinalBidAmount`, `FinalBidCustomerID`, `FinalBidCustomerDetails`, `AuctionStatus`) VALUES (20, 1, 15, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `AuctionVehicles` (`ID`, `AuctionID`, `VehicleID`, `DisplayOrder`, `BuyItNow`, `BuyItNowPrice`, `BuyItNowPaid`, `OldBuyItNow`, `OldBuyItNowPrice`, `CurrentBidAmount`, `CurrentBidID`, `CurrentBidCustomerID`, `CustomerNotified`, `FinalBidAmount`, `FinalBidCustomerID`, `FinalBidCustomerDetails`, `AuctionStatus`) VALUES (21, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `AuctionVehicles` (`ID`, `AuctionID`, `VehicleID`, `DisplayOrder`, `BuyItNow`, `BuyItNowPrice`, `BuyItNowPaid`, `OldBuyItNow`, `OldBuyItNowPrice`, `CurrentBidAmount`, `CurrentBidID`, `CurrentBidCustomerID`, `CustomerNotified`, `FinalBidAmount`, `FinalBidCustomerID`, `FinalBidCustomerDetails`, `AuctionStatus`) VALUES (22, 1, 16, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `AuctionVehicles` (`ID`, `AuctionID`, `VehicleID`, `DisplayOrder`, `BuyItNow`, `BuyItNowPrice`, `BuyItNowPaid`, `OldBuyItNow`, `OldBuyItNowPrice`, `CurrentBidAmount`, `CurrentBidID`, `CurrentBidCustomerID`, `CustomerNotified`, `FinalBidAmount`, `FinalBidCustomerID`, `FinalBidCustomerDetails`, `AuctionStatus`) VALUES (23, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `AuctionVehicles` (`ID`, `AuctionID`, `VehicleID`, `DisplayOrder`, `BuyItNow`, `BuyItNowPrice`, `BuyItNowPaid`, `OldBuyItNow`, `OldBuyItNowPrice`, `CurrentBidAmount`, `CurrentBidID`, `CurrentBidCustomerID`, `CustomerNotified`, `FinalBidAmount`, `FinalBidCustomerID`, `FinalBidCustomerDetails`, `AuctionStatus`) VALUES (24, 1, 17, 10000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
COMMIT;

-- ----------------------------
-- Table structure for AuctionWatchers
-- ----------------------------
DROP TABLE IF EXISTS `AuctionWatchers`;
CREATE TABLE `AuctionWatchers` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `AuctionVehicleID` int DEFAULT NULL,
  `CustomerID` int DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Records of AuctionWatchers
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for Auctions
-- ----------------------------
DROP TABLE IF EXISTS `Auctions`;
CREATE TABLE `Auctions` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `AuctionRoomID` int DEFAULT NULL,
  `AuctionStart` varbinary(255) DEFAULT NULL,
  `Seller_Percent` varbinary(255) DEFAULT NULL,
  `Seller_UptoMax` varbinary(255) DEFAULT NULL,
  `Seller_Fixed` varbinary(255) DEFAULT NULL,
  `Buyer_Percent` varbinary(255) DEFAULT NULL,
  `Buyer_UptoMax` varbinary(255) DEFAULT NULL,
  `Buyer_Fixed` varbinary(255) DEFAULT NULL,
  `BidExtensionTime` varbinary(255) DEFAULT NULL,
  `LotMinimumLength` varbinary(255) DEFAULT NULL,
  `LotBidIncrement` varbinary(255) DEFAULT NULL,
  `AuctionEnd` varbinary(255) DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Records of Auctions
-- ----------------------------
BEGIN;
INSERT INTO `Auctions` (`ID`, `AuctionRoomID`, `AuctionStart`, `Seller_Percent`, `Seller_UptoMax`, `Seller_Fixed`, `Buyer_Percent`, `Buyer_UptoMax`, `Buyer_Fixed`, `BidExtensionTime`, `LotMinimumLength`, `LotBidIncrement`, `AuctionEnd`) VALUES (1, 3, 0x190A13B8E4E2C24197E1DF4E39CBEF515793ECF6A1A500D83DA18545AC11A278, 0xB0187E7CD8D4E50DBDA8DDD329757ABE, 0xD58CC68AFABB0B3B645A2BC8F3920015, 0x5793ECF6A1A500D83DA18545AC11A278, 0xB0187E7CD8D4E50DBDA8DDD329757ABE, 0xD58CC68AFABB0B3B645A2BC8F3920015, 0x5793ECF6A1A500D83DA18545AC11A278, 0x0B950495698503AAC23700E76BD7D8AA, 0x5E8EF38D3853029F0852282CEA8A4D90, 0xD58CC68AFABB0B3B645A2BC8F3920015, NULL);
INSERT INTO `Auctions` (`ID`, `AuctionRoomID`, `AuctionStart`, `Seller_Percent`, `Seller_UptoMax`, `Seller_Fixed`, `Buyer_Percent`, `Buyer_UptoMax`, `Buyer_Fixed`, `BidExtensionTime`, `LotMinimumLength`, `LotBidIncrement`, `AuctionEnd`) VALUES (2, 2, 0x1925E2EAAE2C64198B9F6349339AEE565793ECF6A1A500D83DA18545AC11A278, 0xB0187E7CD8D4E50DBDA8DDD329757ABE, 0xD58CC68AFABB0B3B645A2BC8F3920015, 0x5793ECF6A1A500D83DA18545AC11A278, 0xB0187E7CD8D4E50DBDA8DDD329757ABE, 0xD58CC68AFABB0B3B645A2BC8F3920015, 0x5793ECF6A1A500D83DA18545AC11A278, 0x0B950495698503AAC23700E76BD7D8AA, 0x5E8EF38D3853029F0852282CEA8A4D90, 0xD58CC68AFABB0B3B645A2BC8F3920015, NULL);
INSERT INTO `Auctions` (`ID`, `AuctionRoomID`, `AuctionStart`, `Seller_Percent`, `Seller_UptoMax`, `Seller_Fixed`, `Buyer_Percent`, `Buyer_UptoMax`, `Buyer_Fixed`, `BidExtensionTime`, `LotMinimumLength`, `LotBidIncrement`, `AuctionEnd`) VALUES (3, 5, 0x0957C982E0A94737E4E80ADCFB0264825793ECF6A1A500D83DA18545AC11A278, 0xB0187E7CD8D4E50DBDA8DDD329757ABE, 0xD58CC68AFABB0B3B645A2BC8F3920015, 0x5793ECF6A1A500D83DA18545AC11A278, 0xB0187E7CD8D4E50DBDA8DDD329757ABE, 0xD58CC68AFABB0B3B645A2BC8F3920015, 0x5793ECF6A1A500D83DA18545AC11A278, 0x0B950495698503AAC23700E76BD7D8AA, 0x5E8EF38D3853029F0852282CEA8A4D90, 0xD58CC68AFABB0B3B645A2BC8F3920015, NULL);
COMMIT;

-- ----------------------------
-- Table structure for Carousel
-- ----------------------------
DROP TABLE IF EXISTS `Carousel`;
CREATE TABLE `Carousel` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `ImgPath` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `ImgFilename` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `Title` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `Content` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `CTALink` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `DisplayOrder` int DEFAULT NULL,
  `AuthorID` int DEFAULT NULL,
  `AuthorName` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `CTALabel` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `BGColour` varchar(8) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Records of Carousel
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for Content
-- ----------------------------
DROP TABLE IF EXISTS `Content`;
CREATE TABLE `Content` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `ParentID` int DEFAULT NULL,
  `Title` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '',
  `SubTitle` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `MenuTitle` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `Content` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `Col2Content` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `Col3Content` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `Link` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT '',
  `ImgFilename` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `ImgPath` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `DateDisplay` datetime DEFAULT NULL,
  `AuthorID` int DEFAULT NULL,
  `AuthorName` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT '',
  `DisplayOrder` int DEFAULT NULL,
  `URLText` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `MetaDesc` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `MetaKey` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `MetaTitle` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `SpecialContent` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Records of Content
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for ContentByType
-- ----------------------------
DROP TABLE IF EXISTS `ContentByType`;
CREATE TABLE `ContentByType` (
  `ID` bigint NOT NULL AUTO_INCREMENT,
  `ContentID` int NOT NULL DEFAULT '0',
  `ContentTypeID` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Records of ContentByType
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for ContentLibrary
-- ----------------------------
DROP TABLE IF EXISTS `ContentLibrary`;
CREATE TABLE `ContentLibrary` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `ContentID` int DEFAULT NULL,
  `ContentParentTable` varbinary(255) DEFAULT NULL,
  `MediaType` varbinary(255) DEFAULT NULL,
  `MediaPath` varbinary(255) DEFAULT NULL,
  `MediaFilename` varbinary(255) DEFAULT NULL,
  `MediaExtension` varbinary(255) DEFAULT NULL,
  `MediaMimeType` varbinary(255) DEFAULT NULL,
  `Caption` varbinary(255) DEFAULT NULL,
  `DisplayOrder` int DEFAULT NULL,
  `LastEdited` datetime DEFAULT NULL,
  `MediaThumb` varbinary(255) DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Records of ContentLibrary
-- ----------------------------
BEGIN;
INSERT INTO `ContentLibrary` (`ID`, `ContentID`, `ContentParentTable`, `MediaType`, `MediaPath`, `MediaFilename`, `MediaExtension`, `MediaMimeType`, `Caption`, `DisplayOrder`, `LastEdited`, `MediaThumb`) VALUES (15, 15, 0x4F9851CE18662F32BD4E6EB5840DC793, NULL, 0x853B9A7572F8452A7E1F678DCA31C06EBD9EA01DB4901BB879B83BF534459FE728CE964ACDE43EEBCB3DBA3C7C1E9FAB, 0x8F2E80F8C675CCF97CB85B9CCB557FD41B976246F05F0C7118DDF92B0D03C7C9, 0x1F99DDE0ED363DD4F6739E514893F94C, 0xF2BBE3A7C74B2D03E425E91AD22ADD19, 0x5793ECF6A1A500D83DA18545AC11A278, 100, '2024-06-20 05:55:42', 0xF3DAFD6E147539A84E9A3E11339D58AD);
INSERT INTO `ContentLibrary` (`ID`, `ContentID`, `ContentParentTable`, `MediaType`, `MediaPath`, `MediaFilename`, `MediaExtension`, `MediaMimeType`, `Caption`, `DisplayOrder`, `LastEdited`, `MediaThumb`) VALUES (16, 15, 0x4F9851CE18662F32BD4E6EB5840DC793, NULL, 0x853B9A7572F8452A7E1F678DCA31C06EBD9EA01DB4901BB879B83BF534459FE728CE964ACDE43EEBCB3DBA3C7C1E9FAB, 0x8F2E80F8C675CCF97CB85B9CCB557FD4811226688AE9854A172F2D46B8271211, 0x1F99DDE0ED363DD4F6739E514893F94C, 0xF2BBE3A7C74B2D03E425E91AD22ADD19, 0x5793ECF6A1A500D83DA18545AC11A278, 100, '2024-06-20 05:55:45', 0xF3DAFD6E147539A84E9A3E11339D58AD);
INSERT INTO `ContentLibrary` (`ID`, `ContentID`, `ContentParentTable`, `MediaType`, `MediaPath`, `MediaFilename`, `MediaExtension`, `MediaMimeType`, `Caption`, `DisplayOrder`, `LastEdited`, `MediaThumb`) VALUES (19, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-07-11 11:57:36', NULL);
INSERT INTO `ContentLibrary` (`ID`, `ContentID`, `ContentParentTable`, `MediaType`, `MediaPath`, `MediaFilename`, `MediaExtension`, `MediaMimeType`, `Caption`, `DisplayOrder`, `LastEdited`, `MediaThumb`) VALUES (20, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-07-11 11:59:40', NULL);
INSERT INTO `ContentLibrary` (`ID`, `ContentID`, `ContentParentTable`, `MediaType`, `MediaPath`, `MediaFilename`, `MediaExtension`, `MediaMimeType`, `Caption`, `DisplayOrder`, `LastEdited`, `MediaThumb`) VALUES (21, 16, 0x4F9851CE18662F32BD4E6EB5840DC793, NULL, 0x853B9A7572F8452A7E1F678DCA31C06EBD9EA01DB4901BB879B83BF534459FE728CE964ACDE43EEBCB3DBA3C7C1E9FAB, 0x620BFC6FEF1B484D1247B33947EE9C93CD91A74A2A22BB5DE3215745AB3AB015, 0x1F99DDE0ED363DD4F6739E514893F94C, 0xF2BBE3A7C74B2D03E425E91AD22ADD19, 0x5793ECF6A1A500D83DA18545AC11A278, 100, '2024-07-11 12:02:37', 0xF3DAFD6E147539A84E9A3E11339D58AD);
INSERT INTO `ContentLibrary` (`ID`, `ContentID`, `ContentParentTable`, `MediaType`, `MediaPath`, `MediaFilename`, `MediaExtension`, `MediaMimeType`, `Caption`, `DisplayOrder`, `LastEdited`, `MediaThumb`) VALUES (22, 16, 0x4F9851CE18662F32BD4E6EB5840DC793, NULL, 0x853B9A7572F8452A7E1F678DCA31C06EBD9EA01DB4901BB879B83BF534459FE728CE964ACDE43EEBCB3DBA3C7C1E9FAB, 0x620BFC6FEF1B484D1247B33947EE9C93FA3890E767195A3B05DFB151E6A35E3E, 0x1F99DDE0ED363DD4F6739E514893F94C, 0xF2BBE3A7C74B2D03E425E91AD22ADD19, 0x5793ECF6A1A500D83DA18545AC11A278, 100, '2024-07-11 12:02:41', 0xF3DAFD6E147539A84E9A3E11339D58AD);
INSERT INTO `ContentLibrary` (`ID`, `ContentID`, `ContentParentTable`, `MediaType`, `MediaPath`, `MediaFilename`, `MediaExtension`, `MediaMimeType`, `Caption`, `DisplayOrder`, `LastEdited`, `MediaThumb`) VALUES (23, 17, 0x4F9851CE18662F32BD4E6EB5840DC793, NULL, 0x853B9A7572F8452A7E1F678DCA31C06EBD9EA01DB4901BB879B83BF534459FE728CE964ACDE43EEBCB3DBA3C7C1E9FAB, 0xD49D370D539AFC95A9D3A9A6186DD5B564CBB5A07C933BD29EDEDCEE82741334, 0x1F99DDE0ED363DD4F6739E514893F94C, 0xF2BBE3A7C74B2D03E425E91AD22ADD19, 0x5793ECF6A1A500D83DA18545AC11A278, 2, '2024-07-11 15:51:36', 0xF3DAFD6E147539A84E9A3E11339D58AD);
INSERT INTO `ContentLibrary` (`ID`, `ContentID`, `ContentParentTable`, `MediaType`, `MediaPath`, `MediaFilename`, `MediaExtension`, `MediaMimeType`, `Caption`, `DisplayOrder`, `LastEdited`, `MediaThumb`) VALUES (24, 17, 0x4F9851CE18662F32BD4E6EB5840DC793, NULL, 0x853B9A7572F8452A7E1F678DCA31C06EBD9EA01DB4901BB879B83BF534459FE728CE964ACDE43EEBCB3DBA3C7C1E9FAB, 0xD49D370D539AFC95A9D3A9A6186DD5B5C3AE7BF269FE835D6F9EE6119379A061, 0x1F99DDE0ED363DD4F6739E514893F94C, 0xF2BBE3A7C74B2D03E425E91AD22ADD19, 0x5793ECF6A1A500D83DA18545AC11A278, 1, '2024-07-11 15:51:36', 0xF3DAFD6E147539A84E9A3E11339D58AD);
COMMIT;

-- ----------------------------
-- Table structure for ContentTypes
-- ----------------------------
DROP TABLE IF EXISTS `ContentTypes`;
CREATE TABLE `ContentTypes` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Title` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '',
  `DisplayOrder` int DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Records of ContentTypes
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for Customers
-- ----------------------------
DROP TABLE IF EXISTS `Customers`;
CREATE TABLE `Customers` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Company` varbinary(255) DEFAULT NULL,
  `Address1` varbinary(255) DEFAULT NULL,
  `Address2` varbinary(255) DEFAULT NULL,
  `Address3` varbinary(255) DEFAULT NULL,
  `Town` varbinary(255) DEFAULT NULL,
  `County` varbinary(255) DEFAULT NULL,
  `Postcode` varbinary(255) DEFAULT NULL,
  `Email` varbinary(255) DEFAULT NULL,
  `Tel` varbinary(255) DEFAULT NULL,
  `Mobile` varbinary(255) DEFAULT NULL,
  `LastEdited` datetime DEFAULT NULL,
  `LastEditedBy` varbinary(255) DEFAULT NULL,
  `DateRegistered` datetime DEFAULT NULL,
  `ImgFilename` varbinary(255) DEFAULT NULL,
  `ImgPath` varbinary(255) DEFAULT NULL,
  `Status` varbinary(255) DEFAULT NULL,
  `LocationInfo` mediumblob,
  `CSRFToken` varbinary(255) DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Records of Customers
-- ----------------------------
BEGIN;
INSERT INTO `Customers` (`ID`, `Company`, `Address1`, `Address2`, `Address3`, `Town`, `County`, `Postcode`, `Email`, `Tel`, `Mobile`, `LastEdited`, `LastEditedBy`, `DateRegistered`, `ImgFilename`, `ImgPath`, `Status`, `LocationInfo`, `CSRFToken`) VALUES (1, 0xEEFD66977C95112A56E353051EA233A9B7B328CC982388709B80F1833A53F17B80D691CF7B458EE8BCE251D4718B7C38, 0x85720A58CC451F24B35C8E9BF280AB370B8CDBFF61D612566512801A3FE87760, 0x5793ECF6A1A500D83DA18545AC11A278, 0x5793ECF6A1A500D83DA18545AC11A278, 0xB2E9AB8ADEDA955736637BC4E6D13C77, 0x4C76521954A47DB8E150BFD96AD56096, 0xE6B1B9490FC41B87B58E7C25BC871A28, 0x281E969F85B2031E88084E11C315A1E256FFF7766AE35592E3D3DEB6C2097F99, 0xAB202534E9E7CDFF49080465A68F1D26, 0x3ED2B1519BFEF11C02160CE80A42CB1A, '2024-06-04 05:03:27', 0xF2BB11F04C2C7C47E9C1F0052B0784D9, '2024-03-20 09:12:35', NULL, 0x853B9A7572F8452A7E1F678DCA31C06E79E8E13E52140434690D18BB4C450AAC27910348338D2A83FC253BEDC99F6B61, 0x34FF6B840E634E7B4F25B57BBC2E2B10, NULL, NULL);
INSERT INTO `Customers` (`ID`, `Company`, `Address1`, `Address2`, `Address3`, `Town`, `County`, `Postcode`, `Email`, `Tel`, `Mobile`, `LastEdited`, `LastEditedBy`, `DateRegistered`, `ImgFilename`, `ImgPath`, `Status`, `LocationInfo`, `CSRFToken`) VALUES (6, 0xE07B5DF15A4A6A0E51AB9295E3FEFAE2, 0x312B369B011FF555B53B326A8D6DC549, 0xDC64487C01A7ACB0F9078F4F60BCC585, 0x42B7F23711BDCEFBEE55171F4DAB65D2, 0x6E6AC57B54F62329893BF8571B85474C, 0x449EEC050014E35D7829D687D44D4B6E, 0x02DE4552D7FAD02C8E2A289FE936E31C, 0xAE7900D355F2B796633B165DC6757F0CC14B33F3C1061D0EBA671BEC879B8E2A, 0x56BE117A694F18B1E15AECFC4B2C0359, 0x3F9573C09B4FC1FF133AA12576D27C1D, '2024-03-23 09:00:43', NULL, '2024-03-23 14:00:43', NULL, 0x853B9A7572F8452A7E1F678DCA31C06E79E8E13E52140434690D18BB4C450AAC27910348338D2A83FC253BEDC99F6B61, 0x34FF6B840E634E7B4F25B57BBC2E2B10, NULL, NULL);
INSERT INTO `Customers` (`ID`, `Company`, `Address1`, `Address2`, `Address3`, `Town`, `County`, `Postcode`, `Email`, `Tel`, `Mobile`, `LastEdited`, `LastEditedBy`, `DateRegistered`, `ImgFilename`, `ImgPath`, `Status`, `LocationInfo`, `CSRFToken`) VALUES (7, 0x90D08D5F5617D79A615179518966F75B, 0x4945502270CE1AB2460586ECED50AB4D, 0xFB01AA6D8AE57FACCE92F2B1A3C82B65, 0x433BD9E53630A591F7872CE1E67D050547E42720DB3366798049DF5BD0479A4E, 0xB2E9AB8ADEDA955736637BC4E6D13C77, 0x4C76521954A47DB8E150BFD96AD56096, 0x0D6CAB43438714F58E889FBE8B4DDCF3, 0x208F1A155B99D130441CD1ADA32B9FCB065A6B6F0ECE7184DCFFA0EB19D593A8, 0xDB57FDDC143B441C6294A228E3B83C59, 0xBBECA4700D5579C873A482F0A0F20C93, '2024-06-21 13:27:22', NULL, '2024-06-21 13:27:22', NULL, 0x853B9A7572F8452A7E1F678DCA31C06E79E8E13E52140434690D18BB4C450AAC27910348338D2A83FC253BEDC99F6B61, 0x34FF6B840E634E7B4F25B57BBC2E2B10, NULL, NULL);
COMMIT;

-- ----------------------------
-- Table structure for CustomersByAuctionRoom
-- ----------------------------
DROP TABLE IF EXISTS `CustomersByAuctionRoom`;
CREATE TABLE `CustomersByAuctionRoom` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `CustomerID` int DEFAULT NULL,
  `AuctionRoomID` int DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Records of CustomersByAuctionRoom
-- ----------------------------
BEGIN;
INSERT INTO `CustomersByAuctionRoom` (`ID`, `CustomerID`, `AuctionRoomID`) VALUES (51, 1, 2);
INSERT INTO `CustomersByAuctionRoom` (`ID`, `CustomerID`, `AuctionRoomID`) VALUES (52, 1, 5);
INSERT INTO `CustomersByAuctionRoom` (`ID`, `CustomerID`, `AuctionRoomID`) VALUES (53, 1, 3);
INSERT INTO `CustomersByAuctionRoom` (`ID`, `CustomerID`, `AuctionRoomID`) VALUES (54, 1, 4);
INSERT INTO `CustomersByAuctionRoom` (`ID`, `CustomerID`, `AuctionRoomID`) VALUES (70, 6, 3);
INSERT INTO `CustomersByAuctionRoom` (`ID`, `CustomerID`, `AuctionRoomID`) VALUES (75, 1, 2);
INSERT INTO `CustomersByAuctionRoom` (`ID`, `CustomerID`, `AuctionRoomID`) VALUES (76, 1, 5);
INSERT INTO `CustomersByAuctionRoom` (`ID`, `CustomerID`, `AuctionRoomID`) VALUES (77, 1, 3);
INSERT INTO `CustomersByAuctionRoom` (`ID`, `CustomerID`, `AuctionRoomID`) VALUES (78, 1, 4);
INSERT INTO `CustomersByAuctionRoom` (`ID`, `CustomerID`, `AuctionRoomID`) VALUES (83, 7, 2);
INSERT INTO `CustomersByAuctionRoom` (`ID`, `CustomerID`, `AuctionRoomID`) VALUES (84, 7, 5);
INSERT INTO `CustomersByAuctionRoom` (`ID`, `CustomerID`, `AuctionRoomID`) VALUES (85, 7, 3);
INSERT INTO `CustomersByAuctionRoom` (`ID`, `CustomerID`, `AuctionRoomID`) VALUES (86, 7, 4);
COMMIT;

-- ----------------------------
-- Table structure for FAQs
-- ----------------------------
DROP TABLE IF EXISTS `FAQs`;
CREATE TABLE `FAQs` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `ContentID` int DEFAULT NULL,
  `Title` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `Content` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `DisplayOrder` int DEFAULT NULL,
  `AuthorID` int DEFAULT NULL,
  `AuthorName` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `DateEdited` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Records of FAQs
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for Files
-- ----------------------------
DROP TABLE IF EXISTS `Files`;
CREATE TABLE `Files` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `ContentID` int DEFAULT NULL,
  `ContentParentTable` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `Title` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `DateUploaded` datetime DEFAULT NULL,
  `Filename` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `Filesize` float DEFAULT NULL,
  `Filetype` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `Fileblob` mediumblob,
  `DisplayOrder` int DEFAULT NULL,
  `Caption` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `AuthorID` int DEFAULT NULL,
  `AuthorName` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Records of Files
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for HomeButtons
-- ----------------------------
DROP TABLE IF EXISTS `HomeButtons`;
CREATE TABLE `HomeButtons` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Title` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `LinkURL` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `Content` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `BGCol` varchar(6) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `DisplayOrder` int DEFAULT NULL,
  `AuthorID` int DEFAULT NULL,
  `AuthorName` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `NewWindow` enum('Y','N') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT 'N',
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Records of HomeButtons
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for HomePage
-- ----------------------------
DROP TABLE IF EXISTS `HomePage`;
CREATE TABLE `HomePage` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Title` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `SubTitle` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `Content` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `Col2Content` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `DateDisplay` datetime DEFAULT NULL,
  `AuthorID` int DEFAULT NULL,
  `AuthorName` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `MetaKey` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `MetaDesc` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `MetaTitle` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Records of HomePage
-- ----------------------------
BEGIN;
INSERT INTO `HomePage` (`ID`, `Title`, `SubTitle`, `Content`, `Col2Content`, `DateDisplay`, `AuthorID`, `AuthorName`, `MetaKey`, `MetaDesc`, `MetaTitle`) VALUES (1, 'Welcome to Click Car Auction', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
COMMIT;

-- ----------------------------
-- Table structure for HomePanels
-- ----------------------------
DROP TABLE IF EXISTS `HomePanels`;
CREATE TABLE `HomePanels` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Title` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `Content` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `LinkText` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `LinkURL` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `BGCol` varchar(7) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `ImgPath` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `ImgFilename` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `DisplayOrder` int DEFAULT NULL,
  `AuthorID` int DEFAULT NULL,
  `AuthorName` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Records of HomePanels
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for Images
-- ----------------------------
DROP TABLE IF EXISTS `Images`;
CREATE TABLE `Images` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `ContentID` int DEFAULT NULL,
  `ContentParentTable` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `ImgFilename` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `ImgPath` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `DisplayOrder` int DEFAULT NULL,
  `Caption` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Records of Images
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for Invoices
-- ----------------------------
DROP TABLE IF EXISTS `Invoices`;
CREATE TABLE `Invoices` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `InvoiceType` varbinary(255) DEFAULT NULL,
  `AuctionID` int DEFAULT NULL,
  `CustomerID` int DEFAULT NULL,
  `CustomerDetails` mediumblob,
  `Nett` varbinary(255) DEFAULT NULL,
  `Vat` varbinary(255) DEFAULT NULL,
  `Gross` varbinary(255) DEFAULT NULL,
  `Content` mediumblob,
  `InvoiceNumber` int DEFAULT NULL,
  `StripeInvoiceID` varbinary(255) DEFAULT NULL,
  `StripePaymentIntentID` varbinary(255) DEFAULT NULL,
  `Status` varbinary(255) DEFAULT NULL,
  `DateInvoiced` datetime DEFAULT NULL,
  `DatePaid` datetime DEFAULT NULL,
  `Email1Sent` datetime DEFAULT NULL,
  `Email2Sent` datetime DEFAULT NULL,
  `Email3Sent` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Records of Invoices
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for Log
-- ----------------------------
DROP TABLE IF EXISTS `Log`;
CREATE TABLE `Log` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Date` datetime DEFAULT NULL,
  `UserID` int DEFAULT NULL,
  `UserName` varbinary(255) DEFAULT NULL,
  `UserType` varbinary(255) DEFAULT NULL,
  `LogType` varbinary(255) DEFAULT NULL,
  `LogContent` mediumblob,
  `ContentID` int DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Records of Log
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for NextInvoiceNumber
-- ----------------------------
DROP TABLE IF EXISTS `NextInvoiceNumber`;
CREATE TABLE `NextInvoiceNumber` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `NextInvoiceNumber` int DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Records of NextInvoiceNumber
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for Notes
-- ----------------------------
DROP TABLE IF EXISTS `Notes`;
CREATE TABLE `Notes` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `ParentID` int DEFAULT NULL,
  `ParentTable` varbinary(255) DEFAULT NULL,
  `Content` mediumblob,
  `NoteBy` varbinary(255) DEFAULT NULL,
  `NoteByID` int DEFAULT NULL,
  `DateEdited` datetime DEFAULT NULL,
  PRIMARY KEY (`Id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Records of Notes
-- ----------------------------
BEGIN;
INSERT INTO `Notes` (`Id`, `ParentID`, `ParentTable`, `Content`, `NoteBy`, `NoteByID`, `DateEdited`) VALUES (7, 4, 0x478321639EC9A00FF522D54D8B4190EE, 0x0E3187DDB4F6FA2FF6A0FF979588135B, 0xF2BB11F04C2C7C47E9C1F0052B0784D9, 1, '2024-03-20 06:01:55');
INSERT INTO `Notes` (`Id`, `ParentID`, `ParentTable`, `Content`, `NoteBy`, `NoteByID`, `DateEdited`) VALUES (8, 4, 0x478321639EC9A00FF522D54D8B4190EE, 0x163D110341214C73D0EE6A83D7E49BDF, 0xF2BB11F04C2C7C47E9C1F0052B0784D9, 1, '2024-03-20 06:02:30');
INSERT INTO `Notes` (`Id`, `ParentID`, `ParentTable`, `Content`, `NoteBy`, `NoteByID`, `DateEdited`) VALUES (17, 1, 0x478321639EC9A00FF522D54D8B4190EE, 0xADD3002701DA898659A8101D75D097D3, 0xF2BB11F04C2C7C47E9C1F0052B0784D9, 1, '2024-03-20 14:30:06');
INSERT INTO `Notes` (`Id`, `ParentID`, `ParentTable`, `Content`, `NoteBy`, `NoteByID`, `DateEdited`) VALUES (18, 1, 0x478321639EC9A00FF522D54D8B4190EE, 0xC646D85D60CC22ACA192AD0C44200EB8, 0xF2BB11F04C2C7C47E9C1F0052B0784D9, 1, '2024-03-20 14:30:10');
INSERT INTO `Notes` (`Id`, `ParentID`, `ParentTable`, `Content`, `NoteBy`, `NoteByID`, `DateEdited`) VALUES (19, 4, 0xFEF8BD571B64E3A48689E4C773EB1038, 0x054FD1054CC9519607D1CFD02D3931C798D0D8E9252D1CE9E7A0446F0503531D330B1B31B92F2C1B80689D1E60D17A72, 0xF2BB11F04C2C7C47E9C1F0052B0784D9, 1, '2024-03-23 16:07:16');
INSERT INTO `Notes` (`Id`, `ParentID`, `ParentTable`, `Content`, `NoteBy`, `NoteByID`, `DateEdited`) VALUES (21, 1, 0xFEF8BD571B64E3A48689E4C773EB1038, 0x7007B1D08505883E53A66221DC534BF485DD89A2CB2A55CF5535E80CC9D59337, 0xF2BB11F04C2C7C47E9C1F0052B0784D9, 1, '2024-03-23 09:46:45');
COMMIT;

-- ----------------------------
-- Table structure for PasswordResets
-- ----------------------------
DROP TABLE IF EXISTS `PasswordResets`;
CREATE TABLE `PasswordResets` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `UserID` int DEFAULT NULL,
  `Token` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `Requested` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Records of PasswordResets
-- ----------------------------
BEGIN;
INSERT INTO `PasswordResets` (`ID`, `UserID`, `Token`, `Requested`) VALUES (1, 1, 'YIUSwFtn1Ou8TZrKvxXe9MTlQPeaiSOUuW3qxoUQPEvygq3pcO7lAEIXTff6voveabmrZX2BaVQl9oXzePbbZfvRslXVlhN0XSoj', '2024-03-23 10:06:25');
INSERT INTO `PasswordResets` (`ID`, `UserID`, `Token`, `Requested`) VALUES (2, 1, '4EBlaJ7PYJqnUOhife8tsfyTpIM24s0RksCY5gXsu9ILxywg7m7QLoXQjJbwCZ9liixpMA4WHYnvfvnbglHtcIRfhL4ZCZTcqp9F', '2024-03-23 10:07:09');
INSERT INTO `PasswordResets` (`ID`, `UserID`, `Token`, `Requested`) VALUES (3, 1, 'd639NgqzWX8Gj2EnQYaVhtc7rC83P6Xs09FA40gm2z0vCzCN9f53RsowK0i8BasdT7cmZCOZmRWpqN1CoaFO5O1gL1moGBbOQDAr', '2024-03-23 10:07:18');
INSERT INTO `PasswordResets` (`ID`, `UserID`, `Token`, `Requested`) VALUES (4, 1, 'ZCz2LTuhnM3xFpvz5SBW6zGIMa4HInR0RRUNG3YqY0VcuCRImnb6E2DTbIPMvxB3mXbb38jkSqM7Kmp6mW4yXP2TKXYBixASsg2c', '2024-03-23 10:10:56');
INSERT INTO `PasswordResets` (`ID`, `UserID`, `Token`, `Requested`) VALUES (5, 1, 'S9uB68PLb4v22ovjzIapMSdV4tGW6bxTfzyGbbj06Fwdda8WD81rbkQxrzJvDXC839j3c661YQ8JvDiDVYTAuvtYNoY0OVOPH2m1', '2024-03-23 10:16:19');
INSERT INTO `PasswordResets` (`ID`, `UserID`, `Token`, `Requested`) VALUES (6, 9, 'aiYjAiqqZUeKopYndJicVP8S1oVXyXBvAXqG03Y6n7h3KPtXWHeVZdZW99Zp8aY5do3iFz1EXDHThRR2f3duVFB77nIDSlLGEhZ0', '2024-06-05 06:00:24');
COMMIT;

-- ----------------------------
-- Table structure for Prices
-- ----------------------------
DROP TABLE IF EXISTS `Prices`;
CREATE TABLE `Prices` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `ShortCode` varbinary(255) DEFAULT NULL,
  `Title` varbinary(255) DEFAULT NULL,
  `Price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Records of Prices
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for SiteSettings
-- ----------------------------
DROP TABLE IF EXISTS `SiteSettings`;
CREATE TABLE `SiteSettings` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Title` varbinary(255) DEFAULT NULL,
  `FQDN` varbinary(255) DEFAULT NULL,
  `ImgPath` varbinary(255) DEFAULT NULL,
  `ImgFilename` varbinary(255) DEFAULT NULL,
  `PrimaryColour` varchar(8) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `SecondaryColour` varchar(8) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `Strapline` varbinary(255) DEFAULT NULL,
  `Telephone` varbinary(255) DEFAULT NULL,
  `Mobile` varbinary(255) DEFAULT NULL,
  `Email` varbinary(255) DEFAULT NULL,
  `Address1` varbinary(255) DEFAULT NULL,
  `Address2` varbinary(255) DEFAULT NULL,
  `Address3` varbinary(255) DEFAULT NULL,
  `Town` varbinary(255) DEFAULT NULL,
  `County` varbinary(255) DEFAULT NULL,
  `Postcode` varbinary(255) DEFAULT NULL,
  `RegNumber` varbinary(255) DEFAULT NULL,
  `RegAddress1` varbinary(255) DEFAULT NULL,
  `RegAddress2` varbinary(255) DEFAULT NULL,
  `RegAddress3` varbinary(255) DEFAULT NULL,
  `RegTown` varbinary(255) DEFAULT NULL,
  `RegCounty` varbinary(255) DEFAULT NULL,
  `RegPostcode` varbinary(255) DEFAULT NULL,
  `RegJurisdiction` varbinary(255) DEFAULT NULL,
  `Social_Facebook` varbinary(255) DEFAULT NULL,
  `Social_LinkedIn` varbinary(255) DEFAULT NULL,
  `Social_Twitter` varbinary(255) DEFAULT NULL,
  `Social_Pinterest` varbinary(255) DEFAULT NULL,
  `Social_Instagram` varbinary(255) DEFAULT NULL,
  `Social_Google` varbinary(255) DEFAULT NULL,
  `AddThisCode` varbinary(255) DEFAULT NULL,
  `EnableCTAPanels` enum('Y','N') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT 'N',
  `EnableTestimonials` enum('Y','N') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT 'N',
  `EnableNews` enum('Y','N') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT 'N',
  `EnableImageLibrary` enum('Y','N') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT 'N',
  `EnableMap` enum('Y','N') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT 'Y',
  `MapEmbed` blob,
  `DateSetup` datetime DEFAULT NULL,
  `DefaultMetaDesc` varbinary(255) DEFAULT NULL,
  `DefaultMetaKey` varbinary(255) DEFAULT NULL,
  `GA_Code` varbinary(255) DEFAULT NULL,
  `G_RecaptchaSite` varbinary(255) DEFAULT NULL,
  `G_RecaptchaSecret` varbinary(255) DEFAULT NULL,
  `Template` varbinary(255) DEFAULT NULL,
  `SMSEnabled` varbinary(12) DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Records of SiteSettings
-- ----------------------------
BEGIN;
INSERT INTO `SiteSettings` (`ID`, `Title`, `FQDN`, `ImgPath`, `ImgFilename`, `PrimaryColour`, `SecondaryColour`, `Strapline`, `Telephone`, `Mobile`, `Email`, `Address1`, `Address2`, `Address3`, `Town`, `County`, `Postcode`, `RegNumber`, `RegAddress1`, `RegAddress2`, `RegAddress3`, `RegTown`, `RegCounty`, `RegPostcode`, `RegJurisdiction`, `Social_Facebook`, `Social_LinkedIn`, `Social_Twitter`, `Social_Pinterest`, `Social_Instagram`, `Social_Google`, `AddThisCode`, `EnableCTAPanels`, `EnableTestimonials`, `EnableNews`, `EnableImageLibrary`, `EnableMap`, `MapEmbed`, `DateSetup`, `DefaultMetaDesc`, `DefaultMetaKey`, `GA_Code`, `G_RecaptchaSite`, `G_RecaptchaSecret`, `Template`, `SMSEnabled`) VALUES (1, 0x00ECA74EA96D0DBF62715B7EBFE8B1B9241A0A5EEE99F66835828D873102DF40, 0x21E91AC589B2FEC8B7719B7F902B58588EE9ABF4D415D077BD49C9BE67A6DBE143A692C356249CEDC39FFCBE79DD3DA0, 0x853B9A7572F8452A7E1F678DCA31C06E8E508DF25FA70E5225B77C132FF67F1D, NULL, NULL, NULL, 0x5793ECF6A1A500D83DA18545AC11A278, 0x5A0EFC85A48CCD2A899310FD865E228C, 0x5793ECF6A1A500D83DA18545AC11A278, 0xB81A148A50B1B6BFC68738D9D4A064E9D15688CE21ADF23E41A72A20511D3C7B, 0xC6AB13E4504EAA5A4ED426A77E58427685DD89A2CB2A55CF5535E80CC9D59337, 0x433BD9E53630A591F7872CE1E67D050547E42720DB3366798049DF5BD0479A4E, 0x5793ECF6A1A500D83DA18545AC11A278, 0xB2E9AB8ADEDA955736637BC4E6D13C77, 0x4C76521954A47DB8E150BFD96AD56096, 0xD476B2CE454DFBD68CFA66BED69AABBF, 0x5793ECF6A1A500D83DA18545AC11A278, 0x5793ECF6A1A500D83DA18545AC11A278, 0x5793ECF6A1A500D83DA18545AC11A278, 0x5793ECF6A1A500D83DA18545AC11A278, 0x5793ECF6A1A500D83DA18545AC11A278, 0x5793ECF6A1A500D83DA18545AC11A278, 0x5793ECF6A1A500D83DA18545AC11A278, 0xDF715CFD1EA6A604C9FC2B12F2A682703E8EA4ED2CE19C509CB0CA448CDE9180, 0x5793ECF6A1A500D83DA18545AC11A278, 0x5793ECF6A1A500D83DA18545AC11A278, 0x5793ECF6A1A500D83DA18545AC11A278, 0x5793ECF6A1A500D83DA18545AC11A278, 0x5793ECF6A1A500D83DA18545AC11A278, 0x5793ECF6A1A500D83DA18545AC11A278, 0x5793ECF6A1A500D83DA18545AC11A278, 'N', 'N', 'N', 'N', NULL, 0x5793ECF6A1A500D83DA18545AC11A278, NULL, 0x5793ECF6A1A500D83DA18545AC11A278, 0x5793ECF6A1A500D83DA18545AC11A278, 0x82EA531A0B4A66868451AEE3A5B81BD4, 0xFD99CA6D8126E675B8990C11443EC307C353DEB744759FDE7FF93FBD23BF58B518BF05D590A6A9D860F7F3CC556E8C2A, 0x85F404D4BD39AD4F5FB4B7DED26337FFDDB7C5DF8392140116697E52E89CCE5A36F28FADCB7C2573FC7DAA056B8071E1, NULL, NULL);
COMMIT;

-- ----------------------------
-- Table structure for Stats
-- ----------------------------
DROP TABLE IF EXISTS `Stats`;
CREATE TABLE `Stats` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `TotalAuctionsComplete` varbinary(255) DEFAULT NULL,
  `TotalVehiclesSold` varbinary(255) DEFAULT NULL,
  `TotalSoldValue` varbinary(255) DEFAULT NULL,
  `LastUpdated` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Records of Stats
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for StripeLog
-- ----------------------------
DROP TABLE IF EXISTS `StripeLog`;
CREATE TABLE `StripeLog` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Date` datetime DEFAULT NULL,
  `StripeEventID` varbinary(255) DEFAULT NULL,
  `StripeObjectType` varbinary(255) DEFAULT NULL,
  `StripeObjectID` varbinary(255) DEFAULT NULL,
  `TVAType` varbinary(255) DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Records of StripeLog
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for TopPicks
-- ----------------------------
DROP TABLE IF EXISTS `TopPicks`;
CREATE TABLE `TopPicks` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `VehicleID` int DEFAULT NULL,
  `AuctionID` int DEFAULT NULL,
  `TopPickExpires` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Records of TopPicks
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for Users
-- ----------------------------
DROP TABLE IF EXISTS `Users`;
CREATE TABLE `Users` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `CustomerID` int DEFAULT NULL,
  `Title` varbinary(255) DEFAULT NULL,
  `Firstname` varbinary(255) DEFAULT NULL,
  `Surname` varbinary(255) DEFAULT NULL,
  `Mobile` varbinary(255) DEFAULT NULL,
  `Email` varbinary(255) DEFAULT NULL,
  `Password` varbinary(255) DEFAULT NULL,
  `LastEdited` datetime DEFAULT NULL,
  `LastEditedBy` varbinary(255) DEFAULT NULL,
  `LastLoggedIn` datetime DEFAULT NULL,
  `AdminLevel` varbinary(255) DEFAULT NULL,
  `Status` varbinary(255) DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Records of Users
-- ----------------------------
BEGIN;
INSERT INTO `Users` (`ID`, `CustomerID`, `Title`, `Firstname`, `Surname`, `Mobile`, `Email`, `Password`, `LastEdited`, `LastEditedBy`, `LastLoggedIn`, `AdminLevel`, `Status`) VALUES (1, 1, 0xFFB96013D96E2FF5FCBFB17AD549C99B, 0xD2E35EF5B535C5D03EBAFA92BFBC650E, 0x8EF282A0BBC92DFEEF055EA83C540F15, 0xAB202534E9E7CDFF49080465A68F1D26, 0x360C8C6FFFE16D50A06E997D0441982456FFF7766AE35592E3D3DEB6C2097F99, 0x6538663839323335383064643039636237383733633934336630376537666233, '2024-06-05 05:57:42', NULL, '2024-07-11 15:34:07', 0x413D63690AEE55EDBDB62E7AF770116D, 0x34FF6B840E634E7B4F25B57BBC2E2B10);
INSERT INTO `Users` (`ID`, `CustomerID`, `Title`, `Firstname`, `Surname`, `Mobile`, `Email`, `Password`, `LastEdited`, `LastEditedBy`, `LastLoggedIn`, `AdminLevel`, `Status`) VALUES (4, 1, 0x7991B4A5544EFCCC294832F038AD4232, 0x2C5FC6AF4F3B3F9A7D42FDA9B4764ACF, 0xE5A7E333ACB8684A7EF92B3055839874, 0x3ED2B1519BFEF11C02160CE80A42CB1A, 0xB45088A34A0F885E8BD985126DD92DDC56FFF7766AE35592E3D3DEB6C2097F99, 0x6338356163616230396436393261383236663366633662613339663664346665, '2024-07-11 11:52:14', NULL, '2024-07-11 11:52:25', 0x5793ECF6A1A500D83DA18545AC11A278, 0x34FF6B840E634E7B4F25B57BBC2E2B10);
INSERT INTO `Users` (`ID`, `CustomerID`, `Title`, `Firstname`, `Surname`, `Mobile`, `Email`, `Password`, `LastEdited`, `LastEditedBy`, `LastLoggedIn`, `AdminLevel`, `Status`) VALUES (6, 6, 0xFFB96013D96E2FF5FCBFB17AD549C99B, 0x2C5FC6AF4F3B3F9A7D42FDA9B4764ACF, 0xE5A7E333ACB8684A7EF92B3055839874, 0x3F9573C09B4FC1FF133AA12576D27C1D, 0xAE7900D355F2B796633B165DC6757F0CC14B33F3C1061D0EBA671BEC879B8E2A, 0x3061353662323161326163643737383561336435386366646462336430646337, '2024-07-11 11:49:24', NULL, NULL, 0x5793ECF6A1A500D83DA18545AC11A278, 0x34FF6B840E634E7B4F25B57BBC2E2B10);
INSERT INTO `Users` (`ID`, `CustomerID`, `Title`, `Firstname`, `Surname`, `Mobile`, `Email`, `Password`, `LastEdited`, `LastEditedBy`, `LastLoggedIn`, `AdminLevel`, `Status`) VALUES (7, 7, 0xFFB96013D96E2FF5FCBFB17AD549C99B, 0xF8CE008BFDDC34E70BA291F3CDD5F624, 0x6BB8FAAABCF2F2808538D25DDF0D2096, 0xBBECA4700D5579C873A482F0A0F20C93, 0x208F1A155B99D130441CD1ADA32B9FCB065A6B6F0ECE7184DCFFA0EB19D593A8, 0x6537616264316232623030383363346236616633663136666132303663636361, '2024-07-11 18:40:37', NULL, '2024-07-11 18:41:03', 0x413D63690AEE55EDBDB62E7AF770116D, 0x34FF6B840E634E7B4F25B57BBC2E2B10);
COMMIT;

-- ----------------------------
-- Table structure for VehicleAppraisal
-- ----------------------------
DROP TABLE IF EXISTS `VehicleAppraisal`;
CREATE TABLE `VehicleAppraisal` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `VehicleID` int DEFAULT NULL,
  `Code` varbinary(255) DEFAULT NULL,
  `Title` varbinary(255) DEFAULT NULL,
  `LocX` varbinary(255) DEFAULT NULL,
  `LocY` varbinary(255) DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Records of VehicleAppraisal
-- ----------------------------
BEGIN;
INSERT INTO `VehicleAppraisal` (`ID`, `VehicleID`, `Code`, `Title`, `LocX`, `LocY`) VALUES (1, 15, 0x87A0668F857ACA3237D53351FA206EE7, 0x5E1D1472D03CE560ABF5C33B7F5FEB9F, 0x948AEBD6685BA56AB99CC829191D65E1, 0x9C66716551740AA22E42FA11A1738A03);
INSERT INTO `VehicleAppraisal` (`ID`, `VehicleID`, `Code`, `Title`, `LocX`, `LocY`) VALUES (4, 15, 0x15A705B476CC75844556EE752BEC589C, 0x207067C4631F1C0EA566DAD2087A6F6C, 0xBD5C46EE422E0188CEA535109BB95650, 0x3A935A2B6341F4E99AD0BDAE43A29199);
INSERT INTO `VehicleAppraisal` (`ID`, `VehicleID`, `Code`, `Title`, `LocX`, `LocY`) VALUES (5, 15, 0x8DEF91A65DB693F19366FE4EC64D1D23, 0x71AA46CC58DF3437F7BF2610B7A97EE7, 0x18CBCB4DD21BF2D9B2B44A33E0503107, 0x631C51776232D6A0D4B19F2D7893C95B);
INSERT INTO `VehicleAppraisal` (`ID`, `VehicleID`, `Code`, `Title`, `LocX`, `LocY`) VALUES (6, 15, 0xB7C0DD969BBE814D0BF2817161A675ED, 0x7C1354D2AFA38A755B7E2485B52EABBD, 0x2415DD3C1C9FEF98CCE7ED3638098A35, 0x1BEDFCCE818EE961BDE3FA112810FC25);
INSERT INTO `VehicleAppraisal` (`ID`, `VehicleID`, `Code`, `Title`, `LocX`, `LocY`) VALUES (7, 15, 0x7FB516458B8119FF07C53C70651DE311, 0xF569FFED58406DBBD43CE4A5AD08ABB6, 0xAD492C0222ED854B569A6220B7E7AF7F, 0xB425D1C2688561D2467615D104B717BC);
INSERT INTO `VehicleAppraisal` (`ID`, `VehicleID`, `Code`, `Title`, `LocX`, `LocY`) VALUES (8, 15, 0xB7C0DD969BBE814D0BF2817161A675ED, 0x7C1354D2AFA38A755B7E2485B52EABBD, 0x3E4C9865CC7D3B6B9F72F6C892D7F2C0, 0x835A0250A85D269A2249A86C52BE193C);
INSERT INTO `VehicleAppraisal` (`ID`, `VehicleID`, `Code`, `Title`, `LocX`, `LocY`) VALUES (9, 17, 0xB7DDD48A11925A565BC9849D89E4D75B, 0x009B87DBB36C262D5C7386ACA0B0E9AA, 0xBD5C46EE422E0188CEA535109BB95650, 0x6C38A01584AACFF1A207B14C8701734D);
INSERT INTO `VehicleAppraisal` (`ID`, `VehicleID`, `Code`, `Title`, `LocX`, `LocY`) VALUES (10, 17, 0x8DEF91A65DB693F19366FE4EC64D1D23, 0x71AA46CC58DF3437F7BF2610B7A97EE7, 0xE0350B95210BC63F72E40180CA5C981A, 0xCF340EB68011A74F93F3958E5788CAFD);
INSERT INTO `VehicleAppraisal` (`ID`, `VehicleID`, `Code`, `Title`, `LocX`, `LocY`) VALUES (11, 17, 0x6D11086C1A4EFD1132407693E6DEA6C2, 0xDFB040A4124663AA6D21C096F7B81AF5, 0x9BB1928A326634A550C782C722C7AA5A, 0xC01DA78518BFDE504D8EFD7B4FABC785);
INSERT INTO `VehicleAppraisal` (`ID`, `VehicleID`, `Code`, `Title`, `LocX`, `LocY`) VALUES (12, 17, 0xFAFB6EAA10FE4896CBD8665F4483E206, 0xEDF610AC9ABFA017A069E6A9D17E0E1C, 0x663A1DD6BEE17DD51F46E51127BAC6AA, 0x0D927F499EEEF75193CDF8E89CEFB105);
COMMIT;

-- ----------------------------
-- Table structure for VehicleFeatures
-- ----------------------------
DROP TABLE IF EXISTS `VehicleFeatures`;
CREATE TABLE `VehicleFeatures` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `VehicleID` int DEFAULT NULL,
  `Title` varbinary(255) DEFAULT NULL,
  `DisplayOrder` int DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ----------------------------
-- Records of VehicleFeatures
-- ----------------------------
BEGIN;
INSERT INTO `VehicleFeatures` (`ID`, `VehicleID`, `Title`, `DisplayOrder`) VALUES (2, 15, 0x392116C6F4A1DBE2FF0C5EEA67F15A04, 2);
INSERT INTO `VehicleFeatures` (`ID`, `VehicleID`, `Title`, `DisplayOrder`) VALUES (3, 15, 0xCB60859FACE32C8614359170C21D8226, 3);
INSERT INTO `VehicleFeatures` (`ID`, `VehicleID`, `Title`, `DisplayOrder`) VALUES (4, 15, 0xAE4BD10E5E5482D3CE59FE5599D18656, 1);
INSERT INTO `VehicleFeatures` (`ID`, `VehicleID`, `Title`, `DisplayOrder`) VALUES (5, 17, 0x21AC0B22B3DA18E89CF6BC5976407A215793ECF6A1A500D83DA18545AC11A278, 2);
INSERT INTO `VehicleFeatures` (`ID`, `VehicleID`, `Title`, `DisplayOrder`) VALUES (6, 17, 0x75D8652A7675D1CE71CC6A17EE14BA5F, 3);
INSERT INTO `VehicleFeatures` (`ID`, `VehicleID`, `Title`, `DisplayOrder`) VALUES (8, 17, 0xB46961064191FDEB93FA2D4E8B5DFF5EB1AEBBF1F690067435221483660073B4, 1);
INSERT INTO `VehicleFeatures` (`ID`, `VehicleID`, `Title`, `DisplayOrder`) VALUES (9, 17, 0xF0ADEB6EDA9E039389A000FA387D85DD, 10000);
INSERT INTO `VehicleFeatures` (`ID`, `VehicleID`, `Title`, `DisplayOrder`) VALUES (10, 17, 0x64560052886565BE82E2A7E5EC81AC355793ECF6A1A500D83DA18545AC11A278, 10000);
INSERT INTO `VehicleFeatures` (`ID`, `VehicleID`, `Title`, `DisplayOrder`) VALUES (11, 17, 0x86D18B406363DF983CAED1DCED8AB7B8, 10000);
COMMIT;

-- ----------------------------
-- Table structure for VehicleMakes
-- ----------------------------
DROP TABLE IF EXISTS `VehicleMakes`;
CREATE TABLE `VehicleMakes` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Title` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Records of VehicleMakes
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for VehicleService
-- ----------------------------
DROP TABLE IF EXISTS `VehicleService`;
CREATE TABLE `VehicleService` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `VehicleID` int DEFAULT NULL,
  `ServiceDate` date DEFAULT NULL,
  `Mileage` varbinary(255) DEFAULT NULL,
  `Type` varbinary(255) DEFAULT NULL,
  `Comments` varbinary(255) DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Records of VehicleService
-- ----------------------------
BEGIN;
INSERT INTO `VehicleService` (`ID`, `VehicleID`, `ServiceDate`, `Mileage`, `Type`, `Comments`) VALUES (6, 15, '2024-06-30', 0x0D7EEF96E4BC9BFE8398521390287232, 0x73DFAC4B0B72A0043A265E37930834BF, 0x2911E0B33013B692B4E62504D85208D6A727EA482189BFC071BB6F54EDA0790C);
INSERT INTO `VehicleService` (`ID`, `VehicleID`, `ServiceDate`, `Mileage`, `Type`, `Comments`) VALUES (7, 15, '2023-06-25', 0x01C3C171FC61D76882F26F140746DEDA, 0xE6F1CF9132219818DCC4121ACAA7B4455793ECF6A1A500D83DA18545AC11A278, 0xEB090EBD70501E9F3B377866EAD5783D);
INSERT INTO `VehicleService` (`ID`, `VehicleID`, `ServiceDate`, `Mileage`, `Type`, `Comments`) VALUES (8, 17, '2024-07-01', 0x21F4036B115E9DF07E416AF453831383, 0xE6F1CF9132219818DCC4121ACAA7B4455793ECF6A1A500D83DA18545AC11A278, 0x5793ECF6A1A500D83DA18545AC11A278);
INSERT INTO `VehicleService` (`ID`, `VehicleID`, `ServiceDate`, `Mileage`, `Type`, `Comments`) VALUES (9, 17, '2024-05-01', 0x9FCB3D9277335C0DA986AB0593F57976, 0xE6F1CF9132219818DCC4121ACAA7B4455793ECF6A1A500D83DA18545AC11A278, 0x5793ECF6A1A500D83DA18545AC11A278);
COMMIT;

-- ----------------------------
-- Table structure for Vehicles
-- ----------------------------
DROP TABLE IF EXISTS `Vehicles`;
CREATE TABLE `Vehicles` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `CustomerID` int DEFAULT NULL,
  `Make` varbinary(255) DEFAULT NULL,
  `Model` varbinary(255) DEFAULT NULL,
  `VehicleType` varbinary(255) DEFAULT NULL,
  `Reg` varbinary(255) DEFAULT NULL,
  `ShortDesc` mediumblob,
  `DateOfFirstReg` date DEFAULT NULL,
  `Mileage` bigint DEFAULT NULL,
  `ManufacturerColour` varbinary(255) DEFAULT NULL,
  `FinishType` varbinary(255) DEFAULT NULL,
  `TrimColour` varbinary(255) DEFAULT NULL,
  `TrimType` varbinary(255) DEFAULT NULL,
  `Transmission` varbinary(255) DEFAULT NULL,
  `Fuel` varbinary(255) DEFAULT NULL,
  `NoOfDoors` varbinary(255) DEFAULT NULL,
  `NoOfKeys` varbinary(255) DEFAULT NULL,
  `NoOfOwners` varbinary(255) DEFAULT NULL,
  `EngineSize` varbinary(255) DEFAULT NULL,
  `WheelSize` varbinary(255) DEFAULT NULL,
  `AlloySpec` varbinary(255) DEFAULT NULL,
  `MOTExpires` date DEFAULT NULL,
  `V5Present` varbinary(255) DEFAULT NULL,
  `ServiceHistory` varbinary(255) DEFAULT NULL,
  `Description` mediumblob,
  `TyreFOS` varbinary(255) DEFAULT NULL,
  `TyreFNS` varbinary(255) DEFAULT NULL,
  `TyreROS` varbinary(255) DEFAULT NULL,
  `TyreRNS` varbinary(255) DEFAULT NULL,
  `Updates` mediumblob,
  `DateAdded` datetime DEFAULT NULL,
  `DateUpdated` datetime DEFAULT NULL,
  `RecordComplete` varbinary(100) DEFAULT NULL,
  `OverrideAuctionFees` varbinary(255) DEFAULT NULL,
  `Seller_Percent` varbinary(255) DEFAULT NULL,
  `Seller_UptoMax` varbinary(255) DEFAULT NULL,
  `Seller_Fixed` varbinary(255) DEFAULT NULL,
  `Buyer_Percent` varbinary(255) DEFAULT NULL,
  `Buyer_UptoMax` varbinary(255) DEFAULT NULL,
  `Buyer_Fixed` varbinary(255) DEFAULT NULL,
  `ReservePrice` varbinary(255) DEFAULT NULL,
  `StartingBid` varbinary(255) DEFAULT NULL,
  `BuyItNow` varbinary(255) DEFAULT NULL,
  `BuyItNowPrice` varbinary(255) DEFAULT NULL,
  `VehicleStatus` varbinary(255) DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb3;

-- ----------------------------
-- Records of Vehicles
-- ----------------------------
BEGIN;
INSERT INTO `Vehicles` (`ID`, `CustomerID`, `Make`, `Model`, `VehicleType`, `Reg`, `ShortDesc`, `DateOfFirstReg`, `Mileage`, `ManufacturerColour`, `FinishType`, `TrimColour`, `TrimType`, `Transmission`, `Fuel`, `NoOfDoors`, `NoOfKeys`, `NoOfOwners`, `EngineSize`, `WheelSize`, `AlloySpec`, `MOTExpires`, `V5Present`, `ServiceHistory`, `Description`, `TyreFOS`, `TyreFNS`, `TyreROS`, `TyreRNS`, `Updates`, `DateAdded`, `DateUpdated`, `RecordComplete`, `OverrideAuctionFees`, `Seller_Percent`, `Seller_UptoMax`, `Seller_Fixed`, `Buyer_Percent`, `Buyer_UptoMax`, `Buyer_Fixed`, `ReservePrice`, `StartingBid`, `BuyItNow`, `BuyItNowPrice`, `VehicleStatus`) VALUES (15, 1, 0xF6BBD0FEF6E8BD97E30E0492473614D3, 0x871474E6E1960FD8CAF475E8281F9FC977100BED38D8D6B7108AB6B1C320D679, 0x2335CDBE185F94DE001B49520B4DEA11, 0x4B086C11DC9C3F8E5814302850F35D3D, 0x3B00F105D12F14217D819F038BAC02FA327A041CB903B6E348737E6A195B4200, '2023-06-29', 10742, 0x93743F5E2B439F3A9FCD75F1AAD3016E, 0xA7B9044817C949B7794E4F9C4F4F668D, 0xEB324C1CF26527E0982E00796ABAE3F7, 0xDCBF454C47A5F648C01127C6541A4FF1146B39C57E7F0A0AB1022509474025F6, 0x639F9388130E9794FED7EC29E4E13E03, 0xAC56B522FC7CD38F3EC95503A2D454A2, 0xA52BEF535A50D3FDF8E1AB720EEC02D3, 0x1E80DC79EEC0E16C6B06F69C537FA906, 0x7EB19E22A5D535D6AF5899D613BD03A1, 0x3EF1A0178C5FB410F0E859F3181B453E, 0xF973B04DCEE8324C6471571B8065F399, 0x817FC171008BCA684BBC5193D8350832, '2024-06-30', 0xF3DAFD6E147539A84E9A3E11339D58AD, 0x73DFAC4B0B72A0043A265E37930834BF, NULL, 0x4980ACBFC46002EE9EF051C3ECB850D2, 0x4980ACBFC46002EE9EF051C3ECB850D2, 0x4980ACBFC46002EE9EF051C3ECB850D2, 0x4980ACBFC46002EE9EF051C3ECB850D2, NULL, '2024-04-27 10:28:50', '2024-07-11 15:41:09', NULL, 0xF3DAFD6E147539A84E9A3E11339D58AD, 0xA52BEF535A50D3FDF8E1AB720EEC02D3, 0x5793ECF6A1A500D83DA18545AC11A278, 0x5793ECF6A1A500D83DA18545AC11A278, 0xA52BEF535A50D3FDF8E1AB720EEC02D3, 0xC713D82DE4ADB00C6E6B65D1DD4E81AC, NULL, NULL, NULL, NULL, NULL, 0x7B9EC2BFE98DD61AA6ADD844693E724A);
INSERT INTO `Vehicles` (`ID`, `CustomerID`, `Make`, `Model`, `VehicleType`, `Reg`, `ShortDesc`, `DateOfFirstReg`, `Mileage`, `ManufacturerColour`, `FinishType`, `TrimColour`, `TrimType`, `Transmission`, `Fuel`, `NoOfDoors`, `NoOfKeys`, `NoOfOwners`, `EngineSize`, `WheelSize`, `AlloySpec`, `MOTExpires`, `V5Present`, `ServiceHistory`, `Description`, `TyreFOS`, `TyreFNS`, `TyreROS`, `TyreRNS`, `Updates`, `DateAdded`, `DateUpdated`, `RecordComplete`, `OverrideAuctionFees`, `Seller_Percent`, `Seller_UptoMax`, `Seller_Fixed`, `Buyer_Percent`, `Buyer_UptoMax`, `Buyer_Fixed`, `ReservePrice`, `StartingBid`, `BuyItNow`, `BuyItNowPrice`, `VehicleStatus`) VALUES (16, 1, 0x8E836A32B35431702D92BD930F0107B1, 0x3C6B39BB6E04B5714857EC70B022EE05A26C6EE53A12CAA9A19C90BE7619485A, 0x6F5CAC70EEFB4BBDE598C5B940AF573D, 0xCBC702923076207847C4D49C27C9B3E5, 0x4F611BB4F24E5BDD3E419306294B9DF183215C7B868DC23C55406BE07354021A3D87EC0E09DA53337AA402DB93A4B00212FA043B728250E2A63795FAAB5CEC512AEEB78380EC24088FCE780AC84C67D32A8443900888BFF46781AEE625A40EDB6F258BC3C5E37C83D56AFC0DB94320C9E90D9EB03E29637A7FB5E5BDB5BF1FEE4CD0E8532DE0F8339D3DB29E3033DC22364DFBB4339F9A06F9966441647D1EDC07ECE27A51A58BB28486F4CC4F35B13A90772FA51586AEB81DD553B5AC301156B93B0160048336C10968A12AC335A97C793A22F15967140547312CA28F5F5392F00433C34B87A7468D71700C0136D3AC78369B298FFC168ACFC4FFBEA64B8B1D88BC5C63521F31CFF88CF4F7473ADA3CC5898675099A1909F26F69BE858F2C8D193F3AA3DA9B470D6BEB93A28070DF2590F6E13FCC5AC5A5B5396DF0D0FCF0B59DFAA4621A83DC2AF2AC6A837D9007EA45EC563B29A258185E321910AA333D6006276B956BA046F4B7CB8D4144EB0763A78844C59D134122FE4ACB7D099BA02A0B9F9BAD3FD9315CB8820D24E2DCBE08ECCCE278E8E5AD9505A4363D8ED8A7C6F2FC871EF3D8253C1A127D0B1D8C0B712644629595C395682C35E2299C4CE80DEB7D902756F523A54C40884A40160296DDBCE33D338CB80BEDFEAB49AE25EEB616ADDA5BAA616FAD7C887C927F24F712, '2015-07-17', 123456, 0xE3094F8998BE10C103E44FA15102A830, 0xA7B9044817C949B7794E4F9C4F4F668D, 0x3BBFD6A6E42ECE4DF33A0E0C898217D8, 0x88C563D6075E2C838C0DC2DA0A0587D2, 0x79EA90ACD48CEEFDB4D870D3D85688AB, 0xEBAB3402173B0FACD7B7D4DEB32F2343, 0xA52BEF535A50D3FDF8E1AB720EEC02D3, 0x1E80DC79EEC0E16C6B06F69C537FA906, 0x1E80DC79EEC0E16C6B06F69C537FA906, 0xE2957EBA728F650D04EBAB2AA42B0A91, 0x29E4728E6EEA27E1B22698327D242C05, 0xD0AB4967D7851C02756EC31CE19D25DF, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-06-20 05:39:57', '2024-07-11 15:41:11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0x7B9EC2BFE98DD61AA6ADD844693E724A);
INSERT INTO `Vehicles` (`ID`, `CustomerID`, `Make`, `Model`, `VehicleType`, `Reg`, `ShortDesc`, `DateOfFirstReg`, `Mileage`, `ManufacturerColour`, `FinishType`, `TrimColour`, `TrimType`, `Transmission`, `Fuel`, `NoOfDoors`, `NoOfKeys`, `NoOfOwners`, `EngineSize`, `WheelSize`, `AlloySpec`, `MOTExpires`, `V5Present`, `ServiceHistory`, `Description`, `TyreFOS`, `TyreFNS`, `TyreROS`, `TyreRNS`, `Updates`, `DateAdded`, `DateUpdated`, `RecordComplete`, `OverrideAuctionFees`, `Seller_Percent`, `Seller_UptoMax`, `Seller_Fixed`, `Buyer_Percent`, `Buyer_UptoMax`, `Buyer_Fixed`, `ReservePrice`, `StartingBid`, `BuyItNow`, `BuyItNowPrice`, `VehicleStatus`) VALUES (17, 7, 0x5763D53983329A23B2972A6355121E89, 0xA60A935BBA1FBC3ED890D06201F425323ED5857BE132E4ED2093F366C30776E0, 0x2335CDBE185F94DE001B49520B4DEA11, 0xD7CAC6653AD5FD00E289E1684096243E, 0xE8A72DF405D31A7DC09C342A2BAA662843A692C356249CEDC39FFCBE79DD3DA0, '2013-03-11', 54654, 0xBC941CCD9E8635A50085176570375617, 0x3E14374570B9A785F01497DFE60CDFC0, NULL, 0xC9E0A27E6624AF2AA2CD29A025738691, 0x639F9388130E9794FED7EC29E4E13E03, 0xB146A21762CE48F4A02F6DAF7D4CB0A2, 0x5793ECF6A1A500D83DA18545AC11A278, NULL, 0x7EB19E22A5D535D6AF5899D613BD03A1, 0x2782651D76FE555FDCE7E88BB8CF0EF8, NULL, NULL, '2024-07-11', 0xF3DAFD6E147539A84E9A3E11339D58AD, 0xE6F1CF9132219818DCC4121ACAA7B4455793ECF6A1A500D83DA18545AC11A278, NULL, 0x4980ACBFC46002EE9EF051C3ECB850D2, 0xA52BEF535A50D3FDF8E1AB720EEC02D3, 0xA52BEF535A50D3FDF8E1AB720EEC02D3, 0xA52BEF535A50D3FDF8E1AB720EEC02D3, NULL, '2024-07-11 15:44:38', '2024-07-11 15:54:54', NULL, 0xF3DAFD6E147539A84E9A3E11339D58AD, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0x7B9EC2BFE98DD61AA6ADD844693E724A);
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
