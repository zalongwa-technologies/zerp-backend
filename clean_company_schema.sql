CREATE TABLE `accountgroups` (
  `groupname` char(30) NOT NULL DEFAULT '',
  `sectioninaccounts` int NOT NULL DEFAULT '0',
  `pandl` tinyint NOT NULL DEFAULT '1',
  `sequenceintb` smallint NOT NULL DEFAULT '0',
  `parentgroupname` varchar(30) NOT NULL,
  PRIMARY KEY (`groupname`),
  KEY `SequenceInTB` (`sequenceintb`),
  KEY `sectioninaccounts` (`sectioninaccounts`),
  KEY `parentgroupname` (`parentgroupname`),
  CONSTRAINT `accountgroups_ibfk_1` FOREIGN KEY (`sectioninaccounts`) REFERENCES `accountsection` (`sectionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `accountsection` (
  `sectionid` int NOT NULL DEFAULT '0',
  `sectionname` text NOT NULL,
  PRIMARY KEY (`sectionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `areas` (
  `areacode` char(3) NOT NULL,
  `areadescription` varchar(25) NOT NULL DEFAULT '',
  PRIMARY KEY (`areacode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `assetmanager` (
  `id` int NOT NULL AUTO_INCREMENT,
  `stockid` varchar(64) NOT NULL DEFAULT '',
  `serialno` varchar(30) NOT NULL DEFAULT '',
  `location` varchar(15) NOT NULL DEFAULT '',
  `cost` double NOT NULL DEFAULT '0',
  `depn` double NOT NULL DEFAULT '0',
  `datepurchased` date NOT NULL DEFAULT '1000-01-01',
  `disposalvalue` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `auditscripts` (
  `executiondate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `secondsrunning` decimal(10,5) NOT NULL DEFAULT 0.00000,
  `userid` varchar(20) NOT NULL DEFAULT '',
  `scripttitle` varchar(200) NOT NULL DEFAULT '',
  KEY `idx_auditscripts_userid` (`userid`),
  KEY `idx_auditscripts_executiondate` (`executiondate`),
  KEY `idx_auditscripts_scripttitle` (`scripttitle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `audittrail` (
  `transactiondate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `userid` varchar(20) NOT NULL DEFAULT '',
  `querystring` text DEFAULT NULL,
  KEY `UserID` (`userid`),
  KEY `transactiondate` (`transactiondate`),
  KEY `transactiondate_2` (`transactiondate`),
  KEY `transactiondate_3` (`transactiondate`),
  CONSTRAINT `audittrail_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `www_users` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `bankaccounts` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `currcode` char(3) NOT NULL,
  `invoice` smallint NOT NULL DEFAULT '0',
  `bankaccountcode` varchar(50) NOT NULL DEFAULT '',
  `bankaccountname` char(50) NOT NULL DEFAULT '',
  `bankaccountnumber` char(50) NOT NULL DEFAULT '',
  `bankaddress` char(50) DEFAULT NULL,
  `importformat` varchar(10) NOT NULL DEFAULT '''''',
  PRIMARY KEY (`accountcode`),
  KEY `currcode` (`currcode`),
  KEY `BankAccountName` (`bankaccountname`),
  KEY `BankAccountNumber` (`bankaccountnumber`),
  CONSTRAINT `bankaccounts_ibfk_1` FOREIGN KEY (`accountcode`) REFERENCES `chartmaster` (`accountcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `bankaccountusers` (
  `accountcode` varchar(20) NOT NULL COMMENT 'Bank account code',
  `userid` varchar(20) NOT NULL COMMENT 'User code'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `banktrans` (
  `banktransid` bigint NOT NULL AUTO_INCREMENT,
  `type` smallint NOT NULL DEFAULT '0',
  `transno` bigint NOT NULL DEFAULT '0',
  `bankact` varchar(20) NOT NULL DEFAULT '0',
  `ref` varchar(50) NOT NULL DEFAULT '',
  `amountcleared` double NOT NULL DEFAULT '0',
  `exrate` double NOT NULL DEFAULT '1' COMMENT 'From bank account currency to payment currency',
  `functionalexrate` double NOT NULL DEFAULT '1' COMMENT 'Account currency to functional currency',
  `transdate` date NOT NULL DEFAULT '1000-01-01',
  `banktranstype` varchar(30) NOT NULL DEFAULT '',
  `amount` double NOT NULL DEFAULT '0',
  `currcode` char(3) NOT NULL DEFAULT '',
  `chequeno` varchar(16) NOT NULL DEFAULT '',
  PRIMARY KEY (`banktransid`),
  KEY `BankAct` (`bankact`,`ref`),
  KEY `TransDate` (`transdate`),
  KEY `TransType` (`banktranstype`),
  KEY `Type` (`type`,`transno`),
  KEY `CurrCode` (`currcode`),
  KEY `ref` (`ref`),
  CONSTRAINT `banktrans_ibfk_1` FOREIGN KEY (`type`) REFERENCES `systypes` (`typeid`),
  CONSTRAINT `banktrans_ibfk_2` FOREIGN KEY (`bankact`) REFERENCES `bankaccounts` (`accountcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `bom` (
  `parent` varchar(64) NOT NULL DEFAULT '',
  `sequence` int NOT NULL DEFAULT '0',
  `component` varchar(64) NOT NULL DEFAULT '',
  `workcentreadded` char(5) NOT NULL DEFAULT '',
  `loccode` char(5) NOT NULL DEFAULT '',
  `effectiveafter` date NOT NULL DEFAULT '1000-01-01',
  `effectiveto` date NOT NULL DEFAULT '9999-12-31',
  `quantity` double NOT NULL DEFAULT '1',
  `autoissue` tinyint NOT NULL DEFAULT '0',
  `remark` varchar(500) NOT NULL DEFAULT '',
  `digitals` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`parent`,`component`,`workcentreadded`,`loccode`),
  KEY `Component` (`component`),
  KEY `EffectiveAfter` (`effectiveafter`),
  KEY `EffectiveTo` (`effectiveto`),
  KEY `LocCode` (`loccode`),
  KEY `Parent` (`parent`,`effectiveafter`,`effectiveto`,`loccode`),
  KEY `Parent_2` (`parent`),
  KEY `WorkCentreAdded` (`workcentreadded`),
  CONSTRAINT `bom_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `bom_ibfk_2` FOREIGN KEY (`component`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `bom_ibfk_3` FOREIGN KEY (`workcentreadded`) REFERENCES `workcentres` (`code`),
  CONSTRAINT `bom_ibfk_4` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `chartmaster` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `accountname` char(50) NOT NULL DEFAULT '',
  `group_` char(30) NOT NULL DEFAULT '',
  `cashflowsactivity` tinyint(1) NOT NULL DEFAULT '-1' COMMENT 'Cash flows activity',
  PRIMARY KEY (`accountcode`),
  KEY `AccountName` (`accountname`),
  KEY `Group_` (`group_`),
  CONSTRAINT `chartmaster_ibfk_1` FOREIGN KEY (`group_`) REFERENCES `accountgroups` (`groupname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `cogsglpostings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `area` char(3) NOT NULL DEFAULT '',
  `stkcat` varchar(6) NOT NULL DEFAULT '',
  `glcode` varchar(20) NOT NULL DEFAULT '0',
  `salestype` char(2) NOT NULL DEFAULT 'AN',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Area_StkCat` (`area`,`stkcat`,`salestype`),
  KEY `Area` (`area`),
  KEY `StkCat` (`stkcat`),
  KEY `GLCode` (`glcode`),
  KEY `SalesType` (`salestype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `companies` (
  `coycode` int NOT NULL DEFAULT '1',
  `coyname` varchar(50) NOT NULL DEFAULT '',
  `gstno` varchar(20) NOT NULL DEFAULT '',
  `companynumber` varchar(20) NOT NULL DEFAULT '0',
  `regoffice1` varchar(40) NOT NULL DEFAULT '',
  `regoffice2` varchar(40) NOT NULL DEFAULT '',
  `regoffice3` varchar(40) NOT NULL DEFAULT '',
  `regoffice4` varchar(40) NOT NULL DEFAULT '',
  `regoffice5` varchar(20) NOT NULL DEFAULT '',
  `regoffice6` varchar(15) NOT NULL DEFAULT '',
  `telephone` varchar(25) NOT NULL DEFAULT '',
  `fax` varchar(25) NOT NULL DEFAULT '',
  `email` varchar(55) NOT NULL DEFAULT '',
  `currencydefault` varchar(4) NOT NULL DEFAULT '',
  `debtorsact` varchar(20) NOT NULL DEFAULT '70000',
  `pytdiscountact` varchar(20) NOT NULL DEFAULT '55000',
  `creditorsact` varchar(20) NOT NULL DEFAULT '80000',
  `payrollact` varchar(20) NOT NULL DEFAULT '84000',
  `grnact` varchar(20) NOT NULL DEFAULT '72000',
  `commissionsact` varchar(20) NOT NULL DEFAULT '1',
  `salesexchangediffact` varchar(20) NOT NULL DEFAULT '65000',
  `purchasesexchangediffact` varchar(20) NOT NULL DEFAULT '0',
  `currencyexchangediffact` varchar(20) NOT NULL DEFAULT '65000',
  `unrealizedcurrencydiffact` varchar(20) NOT NULL DEFAULT '65000',
  `retainedearnings` varchar(20) NOT NULL DEFAULT '90000',
  `gllink_debtors` tinyint(1) DEFAULT '1',
  `gllink_creditors` tinyint(1) DEFAULT '1',
  `gllink_stock` tinyint(1) DEFAULT '1',
  `freightact` varchar(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`coycode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `config` (
  `confname` varchar(35) NOT NULL DEFAULT '',
  `confvalue` text NOT NULL,
  PRIMARY KEY (`confname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `contractbom` (
  `contractref` varchar(20) NOT NULL DEFAULT '0',
  `stockid` varchar(64) NOT NULL DEFAULT '',
  `workcentreadded` char(5) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '1',
  PRIMARY KEY (`contractref`,`stockid`,`workcentreadded`),
  KEY `Stockid` (`stockid`),
  KEY `ContractRef` (`contractref`),
  KEY `WorkCentreAdded` (`workcentreadded`),
  CONSTRAINT `contractbom_ibfk_1` FOREIGN KEY (`workcentreadded`) REFERENCES `workcentres` (`code`),
  CONSTRAINT `contractbom_ibfk_3` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `contractcharges` (
  `id` int NOT NULL AUTO_INCREMENT,
  `contractref` varchar(20) NOT NULL,
  `transtype` smallint NOT NULL DEFAULT '20',
  `transno` int NOT NULL DEFAULT '0',
  `amount` double NOT NULL DEFAULT '0',
  `narrative` text NOT NULL,
  `anticipated` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `contractref` (`contractref`,`transtype`,`transno`),
  KEY `contractcharges_ibfk_2` (`transtype`),
  CONSTRAINT `contractcharges_ibfk_1` FOREIGN KEY (`contractref`) REFERENCES `contracts` (`contractref`),
  CONSTRAINT `contractcharges_ibfk_2` FOREIGN KEY (`transtype`) REFERENCES `systypes` (`typeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `contractreqts` (
  `contractreqid` int NOT NULL AUTO_INCREMENT,
  `contractref` varchar(20) NOT NULL DEFAULT '0',
  `requirement` varchar(40) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '1',
  `costperunit` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`contractreqid`),
  KEY `ContractRef` (`contractref`),
  CONSTRAINT `contractreqts_ibfk_1` FOREIGN KEY (`contractref`) REFERENCES `contracts` (`contractref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `contracts` (
  `contractref` varchar(20) NOT NULL DEFAULT '',
  `contractdescription` text NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `status` tinyint NOT NULL DEFAULT '0',
  `categoryid` varchar(6) NOT NULL DEFAULT '',
  `orderno` int NOT NULL DEFAULT '0',
  `customerref` varchar(20) NOT NULL DEFAULT '',
  `margin` double NOT NULL DEFAULT '1',
  `wo` int NOT NULL DEFAULT '0',
  `requireddate` date NOT NULL DEFAULT '1000-01-01',
  `drawing` varchar(50) NOT NULL DEFAULT '',
  `exrate` double NOT NULL DEFAULT '1',
  PRIMARY KEY (`contractref`),
  KEY `OrderNo` (`orderno`),
  KEY `CategoryID` (`categoryid`),
  KEY `Status` (`status`),
  KEY `WO` (`wo`),
  KEY `loccode` (`loccode`),
  KEY `DebtorNo` (`debtorno`,`branchcode`),
  KEY `contracts_ibfk_1` (`branchcode`,`debtorno`),
  CONSTRAINT `contracts_ibfk_1` FOREIGN KEY (`branchcode`, `debtorno`) REFERENCES `custbranch` (`branchcode`, `debtorno`),
  CONSTRAINT `contracts_ibfk_2` FOREIGN KEY (`categoryid`) REFERENCES `stockcategory` (`categoryid`),
  CONSTRAINT `contracts_ibfk_3` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `currencies` (
  `currency` char(20) NOT NULL DEFAULT '',
  `currabrev` char(3) NOT NULL DEFAULT '',
  `country` char(50) NOT NULL DEFAULT '',
  `hundredsname` char(15) NOT NULL DEFAULT 'Cents',
  `decimalplaces` tinyint NOT NULL DEFAULT '2',
  `rate` double NOT NULL DEFAULT '1',
  `webcart` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'If 1 shown in weberp cart. if 0 no show',
  PRIMARY KEY (`currabrev`),
  KEY `Country` (`country`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `custallocns` (
  `id` int NOT NULL AUTO_INCREMENT,
  `amt` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `datealloc` date NOT NULL DEFAULT '1000-01-01',
  `transid_allocfrom` int NOT NULL DEFAULT '0',
  `transid_allocto` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `DateAlloc` (`datealloc`),
  KEY `TransID_AllocFrom` (`transid_allocfrom`),
  KEY `TransID_AllocTo` (`transid_allocto`),
  CONSTRAINT `custallocns_ibfk_1` FOREIGN KEY (`transid_allocfrom`) REFERENCES `debtortrans` (`id`),
  CONSTRAINT `custallocns_ibfk_2` FOREIGN KEY (`transid_allocto`) REFERENCES `debtortrans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `custbranch` (
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `brname` varchar(40) NOT NULL DEFAULT '',
  `braddress1` varchar(40) NOT NULL DEFAULT '',
  `braddress2` varchar(40) NOT NULL DEFAULT '',
  `braddress3` varchar(40) NOT NULL DEFAULT '',
  `braddress4` varchar(50) NOT NULL DEFAULT '',
  `braddress5` varchar(20) NOT NULL DEFAULT '',
  `braddress6` varchar(40) NOT NULL DEFAULT '',
  `lat` float(12,8) NOT NULL DEFAULT '0.00000000',
  `lng` float(12,8) NOT NULL DEFAULT '0.00000000',
  `estdeliverydays` smallint NOT NULL DEFAULT '1',
  `area` char(3) NOT NULL,
  `salesman` varchar(4) NOT NULL DEFAULT '',
  `fwddate` smallint NOT NULL DEFAULT '0',
  `phoneno` varchar(20) NOT NULL DEFAULT '',
  `faxno` varchar(20) NOT NULL DEFAULT '',
  `contactname` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(55) NOT NULL DEFAULT '',
  `defaultlocation` varchar(5) NOT NULL DEFAULT '',
  `taxgroupid` tinyint NOT NULL DEFAULT '1',
  `defaultshipvia` int NOT NULL DEFAULT '1',
  `deliverblind` tinyint(1) DEFAULT '1',
  `disabletrans` tinyint NOT NULL DEFAULT '0',
  `brpostaddr1` varchar(40) NOT NULL DEFAULT '',
  `brpostaddr2` varchar(40) NOT NULL DEFAULT '',
  `brpostaddr3` varchar(40) NOT NULL DEFAULT '',
  `brpostaddr4` varchar(50) NOT NULL DEFAULT '',
  `brpostaddr5` varchar(20) NOT NULL DEFAULT '',
  `brpostaddr6` varchar(40) NOT NULL DEFAULT '',
  `specialinstructions` text NOT NULL,
  `custbranchcode` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`branchcode`,`debtorno`),
  KEY `BrName` (`brname`),
  KEY `DebtorNo` (`debtorno`),
  KEY `Salesman` (`salesman`),
  KEY `Area` (`area`),
  KEY `DefaultLocation` (`defaultlocation`),
  KEY `DefaultShipVia` (`defaultshipvia`),
  KEY `taxgroupid` (`taxgroupid`),
  CONSTRAINT `custbranch_ibfk_1` FOREIGN KEY (`debtorno`) REFERENCES `debtorsmaster` (`debtorno`),
  CONSTRAINT `custbranch_ibfk_2` FOREIGN KEY (`area`) REFERENCES `areas` (`areacode`),
  CONSTRAINT `custbranch_ibfk_3` FOREIGN KEY (`salesman`) REFERENCES `salesman` (`salesmancode`),
  CONSTRAINT `custbranch_ibfk_4` FOREIGN KEY (`defaultlocation`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `custbranch_ibfk_6` FOREIGN KEY (`defaultshipvia`) REFERENCES `shippers` (`shipper_id`),
  CONSTRAINT `custbranch_ibfk_7` FOREIGN KEY (`taxgroupid`) REFERENCES `taxgroups` (`taxgroupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `custcontacts` (
  `contid` int NOT NULL AUTO_INCREMENT,
  `debtorno` varchar(10) NOT NULL,
  `contactname` varchar(40) NOT NULL,
  `role` varchar(40) NOT NULL,
  `phoneno` varchar(20) NOT NULL,
  `notes` varchar(255) NOT NULL,
  `email` varchar(55) NOT NULL,
  `statement` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`contid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `custitem` (
  `debtorno` char(10) NOT NULL DEFAULT '',
  `stockid` varchar(64) NOT NULL DEFAULT '',
  `cust_part` varchar(20) NOT NULL DEFAULT '',
  `cust_description` varchar(30) NOT NULL DEFAULT '',
  `customersuom` char(50) NOT NULL DEFAULT '',
  `conversionfactor` double NOT NULL DEFAULT '1',
  PRIMARY KEY (`debtorno`,`stockid`),
  KEY `StockID` (`stockid`),
  KEY `Debtorno` (`debtorno`),
  CONSTRAINT `custitem_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `custitem_ibfk_2` FOREIGN KEY (`debtorno`) REFERENCES `debtorsmaster` (`debtorno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `custnotes` (
  `noteid` int NOT NULL AUTO_INCREMENT,
  `debtorno` varchar(10) NOT NULL DEFAULT '0',
  `href` varchar(100) NOT NULL,
  `note` text NOT NULL,
  `date` date NOT NULL DEFAULT '1000-01-01',
  `priority` varchar(20) NOT NULL,
  PRIMARY KEY (`noteid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `dashboard_scripts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `scripts` varchar(78) NOT NULL,
  `pagesecurity` int NOT NULL DEFAULT '1',
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `dashboard_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userid` varchar(20) NOT NULL,
  `scripts` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `debtorsmaster` (
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `name` varchar(40) NOT NULL DEFAULT '',
  `address1` varchar(40) NOT NULL DEFAULT '',
  `address2` varchar(40) NOT NULL DEFAULT '',
  `address3` varchar(40) NOT NULL DEFAULT '',
  `address4` varchar(50) NOT NULL DEFAULT '',
  `address5` varchar(20) NOT NULL DEFAULT '',
  `address6` varchar(40) NOT NULL DEFAULT '',
  `currcode` char(3) NOT NULL DEFAULT '',
  `salestype` char(2) NOT NULL DEFAULT '',
  `clientsince` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `holdreason` smallint NOT NULL DEFAULT '0',
  `paymentterms` char(2) NOT NULL DEFAULT 'f',
  `discount` double NOT NULL DEFAULT '0',
  `pymtdiscount` double NOT NULL DEFAULT '0',
  `lastpaid` double NOT NULL DEFAULT '0',
  `lastpaiddate` datetime DEFAULT NULL,
  `creditlimit` double NOT NULL DEFAULT '1000',
  `invaddrbranch` tinyint NOT NULL DEFAULT '0',
  `discountcode` char(2) NOT NULL DEFAULT '',
  `ediinvoices` tinyint NOT NULL DEFAULT '0',
  `ediorders` tinyint NOT NULL DEFAULT '0',
  `edireference` varchar(20) NOT NULL DEFAULT '',
  `editransport` varchar(5) NOT NULL DEFAULT 'email',
  `ediaddress` varchar(50) NOT NULL DEFAULT '',
  `ediserveruser` varchar(20) NOT NULL DEFAULT '',
  `ediserverpwd` varchar(20) NOT NULL DEFAULT '',
  `taxref` varchar(20) NOT NULL DEFAULT '',
  `customerpoline` tinyint(1) NOT NULL DEFAULT '0',
  `typeid` tinyint NOT NULL DEFAULT '1',
  `language_id` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  PRIMARY KEY (`debtorno`),
  KEY `Currency` (`currcode`),
  KEY `HoldReason` (`holdreason`),
  KEY `Name` (`name`),
  KEY `PaymentTerms` (`paymentterms`),
  KEY `SalesType` (`salestype`),
  KEY `EDIInvoices` (`ediinvoices`),
  KEY `EDIOrders` (`ediorders`),
  KEY `debtorsmaster_ibfk_5` (`typeid`),
  CONSTRAINT `debtorsmaster_ibfk_1` FOREIGN KEY (`holdreason`) REFERENCES `holdreasons` (`reasoncode`),
  CONSTRAINT `debtorsmaster_ibfk_2` FOREIGN KEY (`currcode`) REFERENCES `currencies` (`currabrev`),
  CONSTRAINT `debtorsmaster_ibfk_3` FOREIGN KEY (`paymentterms`) REFERENCES `paymentterms` (`termsindicator`),
  CONSTRAINT `debtorsmaster_ibfk_4` FOREIGN KEY (`salestype`) REFERENCES `salestypes` (`typeabbrev`),
  CONSTRAINT `debtorsmaster_ibfk_5` FOREIGN KEY (`typeid`) REFERENCES `debtortype` (`typeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `debtortrans` (
  `id` int NOT NULL AUTO_INCREMENT,
  `transno` int NOT NULL DEFAULT '0',
  `type` smallint NOT NULL DEFAULT '0',
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `trandate` date NOT NULL DEFAULT '1000-01-01',
  `inputdate` datetime NOT NULL,
  `prd` smallint NOT NULL DEFAULT '0',
  `settled` tinyint NOT NULL DEFAULT '0',
  `reference` varchar(50) NOT NULL DEFAULT '',
  `tpe` char(2) NOT NULL DEFAULT '',
  `order_` int NOT NULL DEFAULT '0',
  `rate` double NOT NULL DEFAULT '0',
  `ovamount` double NOT NULL DEFAULT '0',
  `ovgst` double NOT NULL DEFAULT '0',
  `ovfreight` double NOT NULL DEFAULT '0',
  `ovdiscount` double NOT NULL DEFAULT '0',
  `diffonexch` double NOT NULL DEFAULT '0',
  `alloc` double NOT NULL DEFAULT '0',
  `invtext` text DEFAULT NULL,
  `shipvia` int NOT NULL DEFAULT '0',
  `edisent` tinyint NOT NULL DEFAULT '0',
  `consignment` varchar(20) NOT NULL DEFAULT '',
  `packages` int NOT NULL DEFAULT '1' COMMENT 'number of cartons',
  `salesperson` varchar(4) NOT NULL DEFAULT '',
  `balance` double GENERATED ALWAYS AS (((((`ovamount` + `ovgst`) + `ovfreight`) + `ovdiscount`) - `alloc`)) STORED,
  PRIMARY KEY (`id`),
  KEY `DebtorNo` (`debtorno`,`branchcode`),
  KEY `Order_` (`order_`),
  KEY `Prd` (`prd`),
  KEY `Tpe` (`tpe`),
  KEY `Type` (`type`),
  KEY `Settled` (`settled`),
  KEY `TranDate` (`trandate`),
  KEY `TransNo` (`transno`),
  KEY `Type_2` (`type`,`transno`),
  KEY `EDISent` (`edisent`),
  KEY `salesperson` (`salesperson`),
  CONSTRAINT `debtortrans_ibfk_2` FOREIGN KEY (`type`) REFERENCES `systypes` (`typeid`),
  CONSTRAINT `debtortrans_ibfk_3` FOREIGN KEY (`prd`) REFERENCES `periods` (`periodno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `debtortranstaxes` (
  `debtortransid` int NOT NULL DEFAULT '0',
  `taxauthid` tinyint NOT NULL DEFAULT '0',
  `taxamount` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`debtortransid`,`taxauthid`),
  KEY `taxauthid` (`taxauthid`),
  CONSTRAINT `debtortranstaxes_ibfk_1` FOREIGN KEY (`taxauthid`) REFERENCES `taxauthorities` (`taxid`),
  CONSTRAINT `debtortranstaxes_ibfk_2` FOREIGN KEY (`debtortransid`) REFERENCES `debtortrans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `debtortypenotes` (
  `noteid` tinyint NOT NULL AUTO_INCREMENT,
  `typeid` tinyint NOT NULL DEFAULT '0',
  `href` varchar(100) NOT NULL,
  `note` varchar(200) NOT NULL,
  `date` date NOT NULL DEFAULT '1000-01-01',
  `priority` varchar(20) NOT NULL,
  PRIMARY KEY (`noteid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `debtortype` (
  `typeid` tinyint NOT NULL AUTO_INCREMENT,
  `typename` varchar(100) NOT NULL,
  PRIMARY KEY (`typeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `departments` (
  `departmentid` int NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL DEFAULT '',
  `authoriser` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`departmentid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `discountmatrix` (
  `salestype` char(2) NOT NULL DEFAULT '',
  `discountcategory` char(2) NOT NULL DEFAULT '',
  `quantitybreak` int NOT NULL DEFAULT '1',
  `discountrate` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`salestype`,`discountcategory`,`quantitybreak`),
  KEY `QuantityBreak` (`quantitybreak`),
  KEY `DiscountCategory` (`discountcategory`),
  KEY `SalesType` (`salestype`),
  CONSTRAINT `discountmatrix_ibfk_1` FOREIGN KEY (`salestype`) REFERENCES `salestypes` (`typeabbrev`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `ediitemmapping` (
  `supporcust` varchar(4) NOT NULL DEFAULT '',
  `partnercode` varchar(10) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `partnerstockid` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`supporcust`,`partnercode`,`stockid`),
  KEY `PartnerCode` (`partnercode`),
  KEY `StockID` (`stockid`),
  KEY `PartnerStockID` (`partnerstockid`),
  KEY `SuppOrCust` (`supporcust`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `edimessageformat` (
  `id` int NOT NULL AUTO_INCREMENT,
  `partnercode` varchar(10) NOT NULL DEFAULT '',
  `messagetype` varchar(6) NOT NULL DEFAULT '',
  `section` varchar(7) NOT NULL DEFAULT '',
  `sequenceno` int NOT NULL DEFAULT '0',
  `linetext` varchar(70) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `PartnerCode` (`partnercode`,`messagetype`,`sequenceno`),
  KEY `Section` (`section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `edi_orders_seg_groups` (
  `seggroupno` tinyint NOT NULL DEFAULT '0',
  `maxoccur` int NOT NULL DEFAULT '0',
  `parentseggroup` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`seggroupno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `edi_orders_segs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `segtag` char(3) NOT NULL DEFAULT '',
  `seggroup` tinyint NOT NULL DEFAULT '0',
  `maxoccur` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `SegTag` (`segtag`),
  KEY `SegNo` (`seggroup`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `emailsettings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `host` varchar(30) NOT NULL,
  `port` char(5) NOT NULL,
  `heloaddress` varchar(20) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `timeout` int DEFAULT '5',
  `companyname` varchar(50) DEFAULT NULL,
  `auth` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `employees` (
  `id` int NOT NULL AUTO_INCREMENT,
  `surname` varchar(20) NOT NULL,
  `firstname` varchar(30) NOT NULL,
  `stockid` varchar(64) NOT NULL COMMENT 'FK with stockmaster - ',
  `manager` int DEFAULT NULL,
  `normalhours` double NOT NULL DEFAULT '40',
  `userid` varchar(20) NOT NULL,
  `email` varchar(55) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `surname` (`surname`),
  KEY `firstname` (`firstname`),
  KEY `stockid` (`stockid`),
  KEY `manager` (`manager`),
  KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `factorcompanies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `coyname` varchar(50) NOT NULL DEFAULT '',
  `address1` varchar(40) NOT NULL DEFAULT '',
  `address2` varchar(40) NOT NULL DEFAULT '',
  `address3` varchar(40) NOT NULL DEFAULT '',
  `address4` varchar(40) NOT NULL DEFAULT '',
  `address5` varchar(20) NOT NULL DEFAULT '',
  `address6` varchar(15) NOT NULL DEFAULT '',
  `contact` varchar(25) NOT NULL DEFAULT '',
  `telephone` varchar(25) NOT NULL DEFAULT '',
  `fax` varchar(25) NOT NULL DEFAULT '',
  `email` varchar(55) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `factor_name` (`coyname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `favourites` (
  `userid` varchar(20) NOT NULL DEFAULT '',
  `caption` varchar(50) NOT NULL DEFAULT '',
  `href` varchar(200) NOT NULL DEFAULT '#',
  PRIMARY KEY (`userid`,`caption`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `fixedassetcategories` (
  `categoryid` char(6) NOT NULL DEFAULT '',
  `categorydescription` char(20) NOT NULL DEFAULT '',
  `costact` varchar(20) NOT NULL DEFAULT '0',
  `depnact` varchar(20) NOT NULL DEFAULT '0',
  `disposalact` varchar(20) NOT NULL DEFAULT '80000',
  `accumdepnact` varchar(20) NOT NULL DEFAULT '0',
  `defaultdepnrate` double NOT NULL DEFAULT '0.2',
  `defaultdepntype` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`categoryid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `fixedassetlocations` (
  `locationid` char(6) NOT NULL DEFAULT '',
  `locationdescription` char(20) NOT NULL DEFAULT '',
  `parentlocationid` char(6) DEFAULT '',
  PRIMARY KEY (`locationid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `fixedassets` (
  `assetid` int NOT NULL AUTO_INCREMENT,
  `serialno` varchar(30) NOT NULL DEFAULT '',
  `barcode` varchar(20) NOT NULL,
  `assetlocation` varchar(6) NOT NULL DEFAULT '',
  `cost` double NOT NULL DEFAULT '0',
  `accumdepn` double NOT NULL DEFAULT '0',
  `datepurchased` date NOT NULL DEFAULT '1000-01-01',
  `disposalproceeds` double NOT NULL DEFAULT '0',
  `assetcategoryid` varchar(6) NOT NULL DEFAULT '',
  `description` varchar(50) NOT NULL DEFAULT '',
  `longdescription` text NOT NULL,
  `depntype` int NOT NULL DEFAULT '1',
  `depnrate` double NOT NULL,
  `disposaldate` date NOT NULL DEFAULT '1000-01-01',
  PRIMARY KEY (`assetid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `fixedassettasks` (
  `taskid` int NOT NULL AUTO_INCREMENT,
  `assetid` int NOT NULL,
  `taskdescription` text NOT NULL,
  `frequencydays` int NOT NULL DEFAULT '365',
  `lastcompleted` date NOT NULL,
  `userresponsible` varchar(20) NOT NULL,
  `manager` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`taskid`),
  KEY `assetid` (`assetid`),
  KEY `userresponsible` (`userresponsible`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `fixedassettrans` (
  `id` int NOT NULL AUTO_INCREMENT,
  `assetid` int NOT NULL,
  `transtype` tinyint NOT NULL,
  `transdate` date NOT NULL,
  `transno` int NOT NULL,
  `periodno` smallint NOT NULL,
  `inputdate` date NOT NULL,
  `fixedassettranstype` varchar(8) NOT NULL,
  `amount` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `assetid` (`assetid`,`transtype`,`transno`),
  KEY `inputdate` (`inputdate`),
  KEY `transdate` (`transdate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `freightcosts` (
  `shipcostfromid` int NOT NULL AUTO_INCREMENT,
  `locationfrom` varchar(5) NOT NULL DEFAULT '',
  `destinationcountry` varchar(40) NOT NULL,
  `destination` varchar(40) NOT NULL DEFAULT '',
  `shipperid` int NOT NULL DEFAULT '0',
  `cubrate` double NOT NULL DEFAULT '0',
  `kgrate` double NOT NULL DEFAULT '0',
  `maxkgs` double NOT NULL DEFAULT '999999',
  `maxcub` double NOT NULL DEFAULT '999999',
  `fixedprice` double NOT NULL DEFAULT '0',
  `minimumchg` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`shipcostfromid`),
  KEY `Destination` (`destination`),
  KEY `LocationFrom` (`locationfrom`),
  KEY `ShipperID` (`shipperid`),
  KEY `Destination_2` (`destination`,`locationfrom`,`shipperid`),
  CONSTRAINT `freightcosts_ibfk_1` FOREIGN KEY (`locationfrom`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `freightcosts_ibfk_2` FOREIGN KEY (`shipperid`) REFERENCES `shippers` (`shipper_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `geocode_param` (
  `geocodeid` tinyint NOT NULL AUTO_INCREMENT,
  `geocode_key` varchar(200) NOT NULL DEFAULT '',
  `center_long` varchar(20) NOT NULL DEFAULT '',
  `center_lat` varchar(20) NOT NULL DEFAULT '',
  `map_height` varchar(10) NOT NULL DEFAULT '',
  `map_width` varchar(10) NOT NULL DEFAULT '',
  `map_host` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`geocodeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `glaccountusers` (
  `accountcode` varchar(20) NOT NULL COMMENT 'GL account code from chartmaster',
  `userid` varchar(20) NOT NULL,
  `canview` tinyint NOT NULL DEFAULT '0',
  `canupd` tinyint NOT NULL DEFAULT '0',
  UNIQUE KEY `useraccount` (`userid`,`accountcode`),
  UNIQUE KEY `accountuser` (`accountcode`,`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `glbudgetdetails` (
  `id` int NOT NULL AUTO_INCREMENT,
  `headerid` int NOT NULL DEFAULT '0',
  `account` varchar(20) NOT NULL DEFAULT '',
  `period` smallint NOT NULL DEFAULT '0',
  `amount` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `account` (`account`),
  KEY `headerid` (`headerid`,`account`,`period`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `glbudgetheaders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `owner` varchar(20) NOT NULL DEFAULT '',
  `name` varchar(200) NOT NULL DEFAULT '',
  `description` text DEFAULT NULL,
  `startperiod` smallint NOT NULL DEFAULT '0',
  `endperiod` smallint NOT NULL DEFAULT '0',
  `current` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `gltags` (
  `counterindex` int NOT NULL DEFAULT '0',
  `tagref` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`counterindex`,`tagref`),
  KEY `tagref` (`tagref`),
  CONSTRAINT `gltags_ibfk_1` FOREIGN KEY (`counterindex`) REFERENCES `gltrans` (`counterindex`),
  CONSTRAINT `gltags_ibfk_2` FOREIGN KEY (`tagref`) REFERENCES `tags` (`tagref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `gltotals` (
  `account` varchar(20) NOT NULL DEFAULT '',
  `period` smallint NOT NULL DEFAULT '0',
  `amount` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`account`,`period`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `gltrans` (
  `counterindex` int NOT NULL AUTO_INCREMENT,
  `type` smallint NOT NULL DEFAULT '0',
  `typeno` bigint NOT NULL DEFAULT '1',
  `chequeno` int NOT NULL DEFAULT '0',
  `trandate` date NOT NULL DEFAULT '1000-01-01',
  `periodno` smallint NOT NULL DEFAULT '0',
  `account` varchar(20) NOT NULL DEFAULT '0',
  `narrative` varchar(200) NOT NULL DEFAULT '',
  `amount` double NOT NULL DEFAULT '0',
  `jobref` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`counterindex`),
  KEY `Account` (`account`),
  KEY `ChequeNo` (`chequeno`),
  KEY `PeriodNo` (`periodno`),
  KEY `TranDate` (`trandate`),
  KEY `TypeNo` (`typeno`),
  KEY `Type_and_Number` (`type`,`typeno`),
  KEY `JobRef` (`jobref`),
  CONSTRAINT `gltrans_ibfk_1` FOREIGN KEY (`account`) REFERENCES `chartmaster` (`accountcode`),
  CONSTRAINT `gltrans_ibfk_2` FOREIGN KEY (`type`) REFERENCES `systypes` (`typeid`),
  CONSTRAINT `gltrans_ibfk_3` FOREIGN KEY (`periodno`) REFERENCES `periods` (`periodno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `grns` (
  `grnbatch` smallint NOT NULL DEFAULT '0',
  `grnno` int NOT NULL AUTO_INCREMENT,
  `podetailitem` int NOT NULL DEFAULT '0',
  `itemcode` varchar(64) NOT NULL DEFAULT '',
  `deliverydate` date NOT NULL DEFAULT '1000-01-01',
  `itemdescription` varchar(100) NOT NULL DEFAULT '',
  `qtyrecd` double NOT NULL DEFAULT '0',
  `quantityinv` double NOT NULL DEFAULT '0',
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `stdcostunit` double NOT NULL DEFAULT '0',
  `supplierref` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`grnno`),
  KEY `DeliveryDate` (`deliverydate`),
  KEY `ItemCode` (`itemcode`),
  KEY `PODetailItem` (`podetailitem`),
  KEY `SupplierID` (`supplierid`),
  CONSTRAINT `grns_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`supplierid`),
  CONSTRAINT `grns_ibfk_2` FOREIGN KEY (`podetailitem`) REFERENCES `purchorderdetails` (`podetailitem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `holdreasons` (
  `reasoncode` smallint NOT NULL DEFAULT '1',
  `reasondescription` char(30) NOT NULL DEFAULT '',
  `dissallowinvoices` tinyint NOT NULL DEFAULT '-1',
  PRIMARY KEY (`reasoncode`),
  KEY `ReasonDescription` (`reasondescription`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `internalstockcatrole` (
  `categoryid` varchar(6) NOT NULL,
  `secroleid` int NOT NULL,
  PRIMARY KEY (`categoryid`,`secroleid`),
  KEY `internalstockcatrole_ibfk_1` (`categoryid`),
  KEY `internalstockcatrole_ibfk_2` (`secroleid`),
  CONSTRAINT `internalstockcatrole_ibfk_1` FOREIGN KEY (`categoryid`) REFERENCES `stockcategory` (`categoryid`),
  CONSTRAINT `internalstockcatrole_ibfk_2` FOREIGN KEY (`secroleid`) REFERENCES `securityroles` (`secroleid`),
  CONSTRAINT `internalstockcatrole_ibfk_3` FOREIGN KEY (`categoryid`) REFERENCES `stockcategory` (`categoryid`),
  CONSTRAINT `internalstockcatrole_ibfk_4` FOREIGN KEY (`secroleid`) REFERENCES `securityroles` (`secroleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `jnltmpldetails` (
  `linenumber` int NOT NULL DEFAULT '0',
  `templateid` int NOT NULL DEFAULT '0',
  `tags` varchar(50) NOT NULL DEFAULT '0',
  `accountcode` varchar(20) NOT NULL DEFAULT '1',
  `amount` double NOT NULL DEFAULT '0',
  `narrative` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`templateid`,`linenumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `jnltmplheader` (
  `templateid` int NOT NULL DEFAULT '0',
  `templatedescription` varchar(50) NOT NULL DEFAULT '',
  `journaltype` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`templateid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `labelfields` (
  `labelfieldid` int NOT NULL AUTO_INCREMENT,
  `labelid` tinyint NOT NULL,
  `fieldvalue` varchar(20) NOT NULL,
  `vpos` double NOT NULL DEFAULT '0',
  `hpos` double NOT NULL DEFAULT '0',
  `fontsize` tinyint NOT NULL,
  `barcode` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`labelfieldid`),
  KEY `labelid` (`labelid`),
  KEY `vpos` (`vpos`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `labels` (
  `labelid` tinyint NOT NULL AUTO_INCREMENT,
  `description` varchar(50) NOT NULL,
  `pagewidth` double NOT NULL DEFAULT '0',
  `pageheight` double NOT NULL DEFAULT '0',
  `height` double NOT NULL DEFAULT '0',
  `width` double NOT NULL DEFAULT '0',
  `topmargin` double NOT NULL DEFAULT '0',
  `leftmargin` double NOT NULL DEFAULT '0',
  `rowheight` double NOT NULL DEFAULT '0',
  `columnwidth` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`labelid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `lastcostrollup` (
  `stockid` varchar(64) NOT NULL DEFAULT '',
  `totalonhand` double NOT NULL DEFAULT '0',
  `matcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `labcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `oheadcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `categoryid` char(6) NOT NULL DEFAULT '',
  `stockact` varchar(20) NOT NULL DEFAULT '0',
  `adjglact` varchar(20) NOT NULL DEFAULT '0',
  `newmatcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `newlabcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `newoheadcost` decimal(20,4) NOT NULL DEFAULT '0.0000'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `levels` (
  `part` char(20) DEFAULT NULL,
  `level` int DEFAULT NULL,
  `leadtime` smallint NOT NULL DEFAULT '0',
  `pansize` double NOT NULL DEFAULT '0',
  `shrinkfactor` double NOT NULL DEFAULT '0',
  `eoq` double NOT NULL DEFAULT '0',
  KEY `part` (`part`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `locations` (
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `locationname` varchar(50) NOT NULL DEFAULT '',
  `deladd1` varchar(40) NOT NULL DEFAULT '',
  `deladd2` varchar(40) NOT NULL DEFAULT '',
  `deladd3` varchar(40) NOT NULL DEFAULT '',
  `deladd4` varchar(40) NOT NULL DEFAULT '',
  `deladd5` varchar(20) NOT NULL DEFAULT '',
  `deladd6` varchar(15) NOT NULL DEFAULT '',
  `tel` varchar(30) NOT NULL DEFAULT '',
  `fax` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(55) NOT NULL DEFAULT '',
  `contact` varchar(30) NOT NULL DEFAULT '',
  `taxprovinceid` tinyint NOT NULL DEFAULT '1',
  `cashsalecustomer` varchar(10) DEFAULT '',
  `managed` int DEFAULT '0',
  `cashsalebranch` varchar(10) DEFAULT '',
  `internalrequest` tinyint NOT NULL DEFAULT '1' COMMENT 'Allow (1) or not (0) internal request from this location',
  `usedforwo` tinyint NOT NULL DEFAULT '1',
  `glaccountcode` varchar(20) NOT NULL DEFAULT '' COMMENT 'GL account of the location',
  `allowinvoicing` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Allow invoicing of items at this location',
  PRIMARY KEY (`loccode`),
  UNIQUE KEY `locationname` (`locationname`),
  KEY `taxprovinceid` (`taxprovinceid`),
  CONSTRAINT `locations_ibfk_1` FOREIGN KEY (`taxprovinceid`) REFERENCES `taxprovinces` (`taxprovinceid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `locationusers` (
  `loccode` varchar(5) NOT NULL,
  `userid` varchar(20) NOT NULL,
  `canview` tinyint NOT NULL DEFAULT '0',
  `canupd` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`loccode`,`userid`),
  KEY `UserId` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `locstock` (
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `stockid` varchar(64) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '0',
  `reorderlevel` bigint NOT NULL DEFAULT '0',
  `bin` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`loccode`,`stockid`),
  KEY `StockID` (`stockid`),
  KEY `bin` (`bin`),
  CONSTRAINT `locstock_ibfk_1` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `locstock_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `loctransfercancellations` (
  `reference` int NOT NULL,
  `stockid` varchar(64) NOT NULL,
  `cancelqty` double NOT NULL,
  `canceldate` datetime NOT NULL,
  `canceluserid` varchar(20) NOT NULL,
  KEY `Index1` (`reference`,`stockid`),
  KEY `Index2` (`canceldate`,`reference`,`stockid`),
  KEY `refstockid` (`reference`,`stockid`),
  KEY `cancelrefstockid` (`canceldate`,`reference`,`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `loctransfers` (
  `reference` int NOT NULL DEFAULT '0',
  `stockid` varchar(64) NOT NULL DEFAULT '',
  `shipqty` double NOT NULL DEFAULT '0',
  `recqty` double NOT NULL DEFAULT '0',
  `shipdate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `recdate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `shiploc` varchar(7) NOT NULL DEFAULT '',
  `recloc` varchar(7) NOT NULL DEFAULT '',
  `pendingqty` double GENERATED ALWAYS AS ((`shipqty` - `recqty`)) STORED,
  KEY `Reference` (`reference`,`stockid`),
  KEY `ShipLoc` (`shiploc`),
  KEY `RecLoc` (`recloc`),
  KEY `StockID` (`stockid`),
  CONSTRAINT `loctransfers_ibfk_1` FOREIGN KEY (`shiploc`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `loctransfers_ibfk_2` FOREIGN KEY (`recloc`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `loctransfers_ibfk_3` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Stores Shipments To And From Locations';
CREATE TABLE `mailgroupdetails` (
  `groupname` varchar(100) NOT NULL,
  `userid` varchar(20) NOT NULL,
  KEY `userid` (`userid`),
  KEY `groupname` (`groupname`),
  CONSTRAINT `mailgroupdetails_ibfk_1` FOREIGN KEY (`groupname`) REFERENCES `mailgroups` (`groupname`),
  CONSTRAINT `mailgroupdetails_ibfk_2` FOREIGN KEY (`userid`) REFERENCES `www_users` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `mailgroups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `groupname` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `groupname` (`groupname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `manufacturers` (
  `manufacturers_id` int NOT NULL AUTO_INCREMENT,
  `manufacturers_name` varchar(32) NOT NULL,
  `manufacturers_url` varchar(50) NOT NULL DEFAULT '',
  `manufacturers_image` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`manufacturers_id`),
  KEY `manufacturers_name` (`manufacturers_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `menuitems` (
  `secroleid` int NOT NULL DEFAULT '8',
  `modulelink` varchar(10) NOT NULL DEFAULT '',
  `menusection` varchar(15) NOT NULL DEFAULT '',
  `caption` varchar(60) NOT NULL DEFAULT '',
  `url` varchar(60) NOT NULL DEFAULT '',
  `sequence` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`secroleid`,`modulelink`,`menusection`,`caption`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `modules` (
  `secroleid` int NOT NULL DEFAULT '8',
  `modulelink` varchar(10) NOT NULL DEFAULT '',
  `reportlink` varchar(4) NOT NULL DEFAULT '',
  `modulename` varchar(25) NOT NULL DEFAULT '',
  `sequence` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`secroleid`,`modulelink`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `mrpcalendar` (
  `calendardate` date NOT NULL,
  `daynumber` int NOT NULL,
  `manufacturingflag` smallint NOT NULL DEFAULT '1',
  PRIMARY KEY (`calendardate`),
  KEY `daynumber` (`daynumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `mrpdemands` (
  `demandid` int NOT NULL AUTO_INCREMENT,
  `stockid` varchar(64) NOT NULL DEFAULT '',
  `mrpdemandtype` varchar(6) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '0',
  `duedate` date NOT NULL DEFAULT '1000-01-01',
  PRIMARY KEY (`demandid`),
  KEY `StockID` (`stockid`),
  KEY `mrpdemands_ibfk_1` (`mrpdemandtype`),
  CONSTRAINT `mrpdemands_ibfk_1` FOREIGN KEY (`mrpdemandtype`) REFERENCES `mrpdemandtypes` (`mrpdemandtype`),
  CONSTRAINT `mrpdemands_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `mrpdemandtypes` (
  `mrpdemandtype` varchar(6) NOT NULL DEFAULT '',
  `description` char(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`mrpdemandtype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `mrpparameters` (
  `runtime` datetime DEFAULT NULL,
  `location` varchar(50) DEFAULT NULL,
  `pansizeflag` varchar(5) DEFAULT NULL,
  `shrinkageflag` varchar(5) DEFAULT NULL,
  `eoqflag` varchar(5) DEFAULT NULL,
  `usemrpdemands` varchar(5) DEFAULT NULL,
  `userldemands` varchar(5) DEFAULT NULL,
  `leeway` smallint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `mrpplannedorders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `part` char(20) DEFAULT NULL,
  `duedate` date DEFAULT NULL,
  `supplyquantity` double DEFAULT NULL,
  `ordertype` varchar(6) DEFAULT NULL,
  `orderno` int DEFAULT NULL,
  `mrpdate` date DEFAULT NULL,
  `updateflag` smallint DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `mrprequirements` (
  `part` char(20) DEFAULT NULL,
  `daterequired` date DEFAULT NULL,
  `quantity` double DEFAULT NULL,
  `mrpdemandtype` varchar(6) DEFAULT NULL,
  `orderno` int DEFAULT NULL,
  `directdemand` smallint DEFAULT NULL,
  `whererequired` char(20) DEFAULT NULL,
  KEY `part` (`part`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `mrpsupplies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `part` char(20) DEFAULT NULL,
  `duedate` date DEFAULT NULL,
  `supplyquantity` double DEFAULT NULL,
  `ordertype` varchar(6) DEFAULT NULL,
  `orderno` int DEFAULT NULL,
  `mrpdate` date DEFAULT NULL,
  `updateflag` smallint DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `part` (`part`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `offers` (
  `offerid` int NOT NULL AUTO_INCREMENT,
  `tenderid` int NOT NULL DEFAULT '0',
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `stockid` varchar(64) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '0',
  `uom` varchar(15) NOT NULL DEFAULT '',
  `price` double NOT NULL DEFAULT '0',
  `expirydate` date NOT NULL DEFAULT '1000-01-01',
  `currcode` char(3) NOT NULL DEFAULT '',
  PRIMARY KEY (`offerid`),
  KEY `offers_ibfk_1` (`supplierid`),
  KEY `offers_ibfk_2` (`stockid`),
  CONSTRAINT `offers_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`supplierid`),
  CONSTRAINT `offers_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `orderdeliverydifferenceslog` (
  `orderno` int NOT NULL DEFAULT '0',
  `invoiceno` int NOT NULL DEFAULT '0',
  `stockid` varchar(64) NOT NULL DEFAULT '',
  `quantitydiff` double NOT NULL DEFAULT '0',
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branch` varchar(10) NOT NULL DEFAULT '',
  `can_or_bo` char(3) NOT NULL DEFAULT 'CAN',
  KEY `StockID` (`stockid`),
  KEY `DebtorNo` (`debtorno`,`branch`),
  KEY `Can_or_BO` (`can_or_bo`),
  KEY `OrderNo` (`orderno`),
  KEY `orderdeliverydifferenceslog_ibfk_2` (`branch`,`debtorno`),
  CONSTRAINT `orderdeliverydifferenceslog_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `orderdeliverydifferenceslog_ibfk_2` FOREIGN KEY (`branch`, `debtorno`) REFERENCES `custbranch` (`branchcode`, `debtorno`),
  CONSTRAINT `orderdeliverydifferenceslog_ibfk_3` FOREIGN KEY (`orderno`) REFERENCES `salesorders` (`orderno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `paymentmethods` (
  `paymentid` tinyint NOT NULL AUTO_INCREMENT,
  `paymentname` varchar(15) NOT NULL DEFAULT '',
  `paymenttype` int NOT NULL DEFAULT '1',
  `receipttype` int NOT NULL DEFAULT '1',
  `usepreprintedstationery` tinyint NOT NULL DEFAULT '0',
  `opencashdrawer` tinyint NOT NULL DEFAULT '0',
  `percentdiscount` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`paymentid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `paymentterms` (
  `termsindicator` char(2) NOT NULL DEFAULT '',
  `terms` char(40) NOT NULL DEFAULT '',
  `daysbeforedue` smallint NOT NULL DEFAULT '0',
  `dayinfollowingmonth` smallint NOT NULL DEFAULT '0',
  PRIMARY KEY (`termsindicator`),
  KEY `DaysBeforeDue` (`daysbeforedue`),
  KEY `DayInFollowingMonth` (`dayinfollowingmonth`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `pcashdetails` (
  `counterindex` int NOT NULL AUTO_INCREMENT,
  `tabcode` varchar(20) NOT NULL,
  `date` date NOT NULL,
  `codeexpense` varchar(20) NOT NULL,
  `amount` double NOT NULL,
  `authorized` date NOT NULL COMMENT 'date cash assigment was revised and authorized by authorizer from tabs table',
  `posted` tinyint NOT NULL COMMENT 'has (or has not) been posted into gltrans',
  `purpose` text DEFAULT NULL,
  `notes` text NOT NULL,
  `receipt` text DEFAULT NULL COMMENT 'Column redundant. Replaced by receipt file upload. Nov 2017.',
  PRIMARY KEY (`counterindex`),
  UNIQUE KEY `tabcodedate` (`tabcode`,`date`,`codeexpense`,`counterindex`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `pcashdetailtaxes` (
  `counterindex` int NOT NULL AUTO_INCREMENT,
  `pccashdetail` int NOT NULL DEFAULT '0',
  `calculationorder` tinyint NOT NULL DEFAULT '0',
  `description` varchar(40) NOT NULL DEFAULT '',
  `taxauthid` tinyint NOT NULL DEFAULT '0',
  `purchtaxglaccount` varchar(20) NOT NULL DEFAULT '',
  `taxontax` tinyint NOT NULL DEFAULT '0',
  `taxrate` double NOT NULL DEFAULT '0',
  `amount` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`counterindex`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `pcexpenses` (
  `codeexpense` varchar(20) NOT NULL COMMENT 'code for the group',
  `description` varchar(50) NOT NULL COMMENT 'text description, e.g. meals, train tickets, fuel, etc',
  `glaccount` varchar(20) NOT NULL DEFAULT '0',
  `taxcatid` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`codeexpense`),
  KEY `glaccount` (`glaccount`),
  CONSTRAINT `pcexpenses_ibfk_1` FOREIGN KEY (`glaccount`) REFERENCES `chartmaster` (`accountcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `pcreceipts` (
  `counterindex` int NOT NULL AUTO_INCREMENT,
  `pccashdetail` int NOT NULL DEFAULT '0' COMMENT 'Expenses record identity',
  `hashfile` varchar(32) NOT NULL DEFAULT '' COMMENT 'MD5 hash of uploaded receipt file',
  `type` varchar(80) NOT NULL DEFAULT '' COMMENT 'Mime type of uploaded receipt file',
  `extension` varchar(4) NOT NULL DEFAULT '' COMMENT 'File extension of uploaded receipt',
  `size` int NOT NULL DEFAULT '0' COMMENT 'File size of uploaded receipt',
  PRIMARY KEY (`counterindex`),
  KEY `pcreceipts_ibfk_1` (`pccashdetail`),
  CONSTRAINT `pcreceipts_ibfk_1` FOREIGN KEY (`pccashdetail`) REFERENCES `pcashdetails` (`counterindex`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `pctabexpenses` (
  `typetabcode` varchar(20) NOT NULL,
  `codeexpense` varchar(20) NOT NULL,
  KEY `typetabcode` (`typetabcode`),
  KEY `codeexpense` (`codeexpense`),
  CONSTRAINT `pctabexpenses_ibfk_1` FOREIGN KEY (`typetabcode`) REFERENCES `pctypetabs` (`typetabcode`),
  CONSTRAINT `pctabexpenses_ibfk_2` FOREIGN KEY (`codeexpense`) REFERENCES `pcexpenses` (`codeexpense`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `pctabs` (
  `tabcode` varchar(20) NOT NULL,
  `usercode` varchar(20) NOT NULL COMMENT 'code of user employee from www_users',
  `typetabcode` varchar(20) NOT NULL,
  `currency` char(3) NOT NULL,
  `tablimit` double NOT NULL,
  `assigner` varchar(100) DEFAULT NULL,
  `authorizer` varchar(100) DEFAULT NULL,
  `authorizerexpenses` varchar(20) NOT NULL,
  `glaccountassignment` varchar(20) NOT NULL DEFAULT '0',
  `glaccountpcash` varchar(20) NOT NULL DEFAULT '0',
  `defaulttag` tinyint NOT NULL DEFAULT '0',
  `taxgroupid` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`tabcode`),
  KEY `usercode` (`usercode`),
  KEY `typetabcode` (`typetabcode`),
  KEY `currency` (`currency`),
  KEY `authorizer` (`authorizer`),
  KEY `glaccountassignment` (`glaccountassignment`),
  CONSTRAINT `pctabs_ibfk_1` FOREIGN KEY (`usercode`) REFERENCES `www_users` (`userid`),
  CONSTRAINT `pctabs_ibfk_2` FOREIGN KEY (`typetabcode`) REFERENCES `pctypetabs` (`typetabcode`),
  CONSTRAINT `pctabs_ibfk_3` FOREIGN KEY (`currency`) REFERENCES `currencies` (`currabrev`),
  CONSTRAINT `pctabs_ibfk_5` FOREIGN KEY (`glaccountassignment`) REFERENCES `chartmaster` (`accountcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `pctags` (
  `pccashdetail` int NOT NULL,
  `tag` int NOT NULL,
  PRIMARY KEY (`pccashdetail`,`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `pctypetabs` (
  `typetabcode` varchar(20) NOT NULL COMMENT 'code for the type of petty cash tab',
  `typetabdescription` varchar(50) NOT NULL COMMENT 'text description, e.g. tab for CEO',
  PRIMARY KEY (`typetabcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `periods` (
  `periodno` smallint NOT NULL DEFAULT '0',
  `lastdate_in_period` date NOT NULL DEFAULT '1000-01-01',
  PRIMARY KEY (`periodno`),
  KEY `LastDate_in_Period` (`lastdate_in_period`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `pickinglistdetails` (
  `pickinglistno` int NOT NULL DEFAULT '0',
  `pickinglistlineno` int NOT NULL DEFAULT '0',
  `orderlineno` int NOT NULL DEFAULT '0',
  `qtyexpected` double NOT NULL DEFAULT '0',
  `qtypicked` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`pickinglistno`,`pickinglistlineno`),
  CONSTRAINT `pickinglistdetails_ibfk_1` FOREIGN KEY (`pickinglistno`) REFERENCES `pickinglists` (`pickinglistno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `pickinglists` (
  `pickinglistno` int NOT NULL DEFAULT '0',
  `orderno` int NOT NULL DEFAULT '0',
  `pickinglistdate` date NOT NULL DEFAULT '1000-01-01',
  `dateprinted` date NOT NULL DEFAULT '1000-01-01',
  `deliverynotedate` date NOT NULL DEFAULT '1000-01-01',
  PRIMARY KEY (`pickinglistno`),
  KEY `pickinglists_ibfk_1` (`orderno`),
  CONSTRAINT `pickinglists_ibfk_1` FOREIGN KEY (`orderno`) REFERENCES `salesorders` (`orderno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `pickreqdetails` (
  `detailno` int NOT NULL AUTO_INCREMENT,
  `prid` int NOT NULL DEFAULT '1',
  `orderlineno` int NOT NULL DEFAULT '0',
  `stockid` varchar(64) NOT NULL DEFAULT '',
  `qtyexpected` double NOT NULL DEFAULT '0',
  `qtypicked` double NOT NULL DEFAULT '0',
  `invoicedqty` double NOT NULL DEFAULT '0',
  `shipqty` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`detailno`),
  KEY `prid` (`prid`),
  KEY `stockid` (`stockid`),
  CONSTRAINT `pickreqdetails_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `pickreqdetails_ibfk_2` FOREIGN KEY (`prid`) REFERENCES `pickreq` (`prid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `pickreq` (
  `prid` int NOT NULL AUTO_INCREMENT,
  `initiator` varchar(20) NOT NULL DEFAULT '',
  `shippedby` varchar(20) NOT NULL DEFAULT '',
  `initdate` date NOT NULL DEFAULT '1000-01-01',
  `requestdate` date NOT NULL DEFAULT '1000-01-01',
  `shipdate` date NOT NULL DEFAULT '1000-01-01',
  `status` varchar(12) NOT NULL DEFAULT '',
  `comments` text,
  `closed` tinyint NOT NULL DEFAULT '0',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `orderno` int NOT NULL DEFAULT '1',
  `consignment` varchar(15) NOT NULL DEFAULT '',
  `packages` int NOT NULL DEFAULT '1' COMMENT 'number of cartons',
  PRIMARY KEY (`prid`),
  KEY `orderno` (`orderno`),
  KEY `requestdate` (`requestdate`),
  KEY `shipdate` (`shipdate`),
  KEY `status` (`status`),
  KEY `closed` (`closed`),
  KEY `loccode` (`loccode`),
  CONSTRAINT `pickreq_ibfk_1` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `pickreq_ibfk_2` FOREIGN KEY (`orderno`) REFERENCES `salesorders` (`orderno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `pickserialdetails` (
  `serialmoveid` int NOT NULL AUTO_INCREMENT,
  `detailno` int NOT NULL DEFAULT '1',
  `stockid` varchar(64) NOT NULL DEFAULT '',
  `serialno` varchar(30) NOT NULL DEFAULT '',
  `moveqty` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`serialmoveid`),
  KEY `detailno` (`detailno`),
  KEY `stockid` (`stockid`,`serialno`),
  KEY `serialno` (`serialno`),
  CONSTRAINT `pickserialdetails_ibfk_1` FOREIGN KEY (`detailno`) REFERENCES `pickreqdetails` (`detailno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `pricematrix` (
  `salestype` char(2) NOT NULL DEFAULT '',
  `stockid` varchar(64) NOT NULL DEFAULT '',
  `quantitybreak` int NOT NULL DEFAULT '1',
  `price` double NOT NULL DEFAULT '0',
  `currabrev` char(3) NOT NULL DEFAULT '',
  `startdate` date NOT NULL DEFAULT '1000-01-01',
  `enddate` date NOT NULL DEFAULT '9999-12-31',
  PRIMARY KEY (`salestype`,`stockid`,`currabrev`,`quantitybreak`,`startdate`,`enddate`),
  KEY `SalesType` (`salestype`),
  KEY `currabrev` (`currabrev`),
  KEY `stockid` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `prices` (
  `stockid` varchar(64) NOT NULL DEFAULT '',
  `typeabbrev` char(2) NOT NULL DEFAULT '',
  `currabrev` char(3) NOT NULL DEFAULT '',
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `price` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `startdate` date NOT NULL DEFAULT '1000-01-01',
  `enddate` date NOT NULL DEFAULT '9999-12-31',
  PRIMARY KEY (`stockid`,`typeabbrev`,`currabrev`,`debtorno`,`branchcode`,`startdate`,`enddate`),
  KEY `CurrAbrev` (`currabrev`),
  KEY `DebtorNo` (`debtorno`),
  KEY `StockID` (`stockid`),
  KEY `TypeAbbrev` (`typeabbrev`),
  CONSTRAINT `prices_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `prices_ibfk_2` FOREIGN KEY (`currabrev`) REFERENCES `currencies` (`currabrev`),
  CONSTRAINT `prices_ibfk_3` FOREIGN KEY (`typeabbrev`) REFERENCES `salestypes` (`typeabbrev`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `prodspecgroups` (
  `groupid` smallint NOT NULL AUTO_INCREMENT,
  `groupname` char(50) DEFAULT NULL,
  `groupbyNo` int NOT NULL DEFAULT '1',
  `headertitle` varchar(100) DEFAULT NULL,
  `trailertext` varchar(240) DEFAULT NULL,
  `labels` varchar(240) NOT NULL,
  `numcols` tinyint(1) NOT NULL,
  PRIMARY KEY (`groupid`),
  UNIQUE KEY `groupname` (`groupname`),
  KEY `groupbyNo` (`groupbyNo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `prodspecs` (
  `keyval` varchar(25) NOT NULL,
  `testid` int NOT NULL,
  `defaultvalue` varchar(150) NOT NULL DEFAULT '',
  `targetvalue` varchar(30) NOT NULL DEFAULT '',
  `rangemin` float DEFAULT NULL,
  `rangemax` float DEFAULT NULL,
  `showoncert` tinyint NOT NULL DEFAULT '1',
  `showonspec` tinyint NOT NULL DEFAULT '1',
  `showontestplan` tinyint NOT NULL DEFAULT '1',
  `active` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`keyval`,`testid`),
  KEY `testid` (`testid`),
  CONSTRAINT `prodspecs_ibfk_1` FOREIGN KEY (`testid`) REFERENCES `qatests` (`testid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `purchdata` (
  `supplierno` char(10) NOT NULL DEFAULT '',
  `stockid` varchar(64) NOT NULL DEFAULT '',
  `price` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `suppliersuom` char(50) NOT NULL DEFAULT '',
  `conversionfactor` double NOT NULL DEFAULT '1',
  `supplierdescription` char(50) NOT NULL DEFAULT '',
  `leadtime` smallint NOT NULL DEFAULT '1',
  `preferred` tinyint NOT NULL DEFAULT '0',
  `effectivefrom` date NOT NULL,
  `suppliers_partno` varchar(50) NOT NULL DEFAULT '',
  `minorderqty` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`supplierno`,`stockid`,`effectivefrom`),
  KEY `StockID` (`stockid`),
  KEY `SupplierNo` (`supplierno`),
  KEY `Preferred` (`preferred`),
  CONSTRAINT `purchdata_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `purchdata_ibfk_2` FOREIGN KEY (`supplierno`) REFERENCES `suppliers` (`supplierid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `purchorderauth` (
  `userid` varchar(20) NOT NULL DEFAULT '',
  `currabrev` char(3) NOT NULL DEFAULT '',
  `cancreate` smallint NOT NULL DEFAULT '0',
  `authlevel` double NOT NULL DEFAULT '0',
  `offhold` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`userid`,`currabrev`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `purchorderdetails` (
  `podetailitem` int NOT NULL AUTO_INCREMENT,
  `orderno` int NOT NULL DEFAULT '0',
  `itemcode` varchar(64) NOT NULL DEFAULT '',
  `deliverydate` date NOT NULL DEFAULT '1000-01-01',
  `itemdescription` varchar(100) NOT NULL,
  `glcode` varchar(20) NOT NULL DEFAULT '0',
  `qtyinvoiced` double NOT NULL DEFAULT '0',
  `unitprice` double NOT NULL DEFAULT '0',
  `actprice` double NOT NULL DEFAULT '0',
  `stdcostunit` double NOT NULL DEFAULT '0',
  `quantityord` double NOT NULL DEFAULT '0',
  `quantityrecd` double NOT NULL DEFAULT '0',
  `shiptref` int NOT NULL DEFAULT '0',
  `jobref` varchar(20) NOT NULL DEFAULT '',
  `completed` tinyint NOT NULL DEFAULT '0',
  `suppliersunit` varchar(50) DEFAULT NULL,
  `suppliers_partno` varchar(50) NOT NULL DEFAULT '',
  `assetid` int NOT NULL DEFAULT '0',
  `conversionfactor` double NOT NULL DEFAULT '1',
  PRIMARY KEY (`podetailitem`),
  KEY `DeliveryDate` (`deliverydate`),
  KEY `GLCode` (`glcode`),
  KEY `ItemCode` (`itemcode`),
  KEY `JobRef` (`jobref`),
  KEY `OrderNo` (`orderno`),
  KEY `ShiptRef` (`shiptref`),
  KEY `Completed` (`completed`),
  CONSTRAINT `purchorderdetails_ibfk_1` FOREIGN KEY (`orderno`) REFERENCES `purchorders` (`orderno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `purchorders` (
  `orderno` int NOT NULL AUTO_INCREMENT,
  `supplierno` varchar(10) NOT NULL DEFAULT '',
  `comments` longblob DEFAULT NULL,
  `orddate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `rate` double NOT NULL DEFAULT '1',
  `dateprinted` datetime DEFAULT NULL,
  `allowprint` tinyint NOT NULL DEFAULT '1',
  `initiator` varchar(20) DEFAULT NULL,
  `requisitionno` varchar(15) DEFAULT NULL,
  `intostocklocation` varchar(5) NOT NULL DEFAULT '',
  `deladd1` varchar(40) NOT NULL DEFAULT '',
  `deladd2` varchar(40) NOT NULL DEFAULT '',
  `deladd3` varchar(40) NOT NULL DEFAULT '',
  `deladd4` varchar(40) NOT NULL DEFAULT '',
  `deladd5` varchar(20) NOT NULL DEFAULT '',
  `deladd6` varchar(15) NOT NULL DEFAULT '',
  `tel` varchar(30) NOT NULL DEFAULT '',
  `suppdeladdress1` varchar(40) NOT NULL DEFAULT '',
  `suppdeladdress2` varchar(40) NOT NULL DEFAULT '',
  `suppdeladdress3` varchar(40) NOT NULL DEFAULT '',
  `suppdeladdress4` varchar(40) NOT NULL DEFAULT '',
  `suppdeladdress5` varchar(20) NOT NULL DEFAULT '',
  `suppdeladdress6` varchar(15) NOT NULL DEFAULT '',
  `suppliercontact` varchar(30) NOT NULL DEFAULT '',
  `supptel` varchar(30) NOT NULL DEFAULT '',
  `contact` varchar(30) NOT NULL DEFAULT '',
  `version` decimal(5,2) NOT NULL DEFAULT '1.00',
  `revised` date NOT NULL DEFAULT '1000-01-01',
  `realorderno` varchar(16) NOT NULL DEFAULT '',
  `deliveryby` varchar(100) NOT NULL DEFAULT '',
  `deliverydate` date NOT NULL DEFAULT '1000-01-01',
  `status` varchar(12) NOT NULL DEFAULT '',
  `stat_comment` text NOT NULL,
  `paymentterms` char(2) NOT NULL DEFAULT '',
  `port` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`orderno`),
  KEY `OrdDate` (`orddate`),
  KEY `SupplierNo` (`supplierno`),
  KEY `IntoStockLocation` (`intostocklocation`),
  KEY `AllowPrintPO` (`allowprint`),
  CONSTRAINT `purchorders_ibfk_1` FOREIGN KEY (`supplierno`) REFERENCES `suppliers` (`supplierid`),
  CONSTRAINT `purchorders_ibfk_2` FOREIGN KEY (`intostocklocation`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `qasamples` (
  `sampleid` int NOT NULL AUTO_INCREMENT,
  `prodspeckey` varchar(25) NOT NULL DEFAULT '',
  `lotkey` varchar(25) NOT NULL DEFAULT '',
  `identifier` varchar(10) NOT NULL DEFAULT '',
  `createdby` varchar(15) NOT NULL DEFAULT '',
  `sampledate` date NOT NULL DEFAULT '1000-01-01',
  `comments` varchar(255) NOT NULL DEFAULT '',
  `cert` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`sampleid`),
  KEY `prodspeckey` (`prodspeckey`,`lotkey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `qatests` (
  `testid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `method` varchar(20) DEFAULT NULL,
  `groupby` varchar(20) DEFAULT NULL,
  `units` varchar(20) NOT NULL,
  `type` varchar(15) NOT NULL,
  `defaultvalue` varchar(150) NOT NULL DEFAULT '''''',
  `numericvalue` tinyint NOT NULL DEFAULT '0',
  `showoncert` int NOT NULL DEFAULT '1',
  `showonspec` int NOT NULL DEFAULT '1',
  `showontestplan` tinyint NOT NULL DEFAULT '1',
  `active` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`testid`),
  KEY `name` (`name`),
  KEY `groupname` (`groupby`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `recurringsalesorders` (
  `recurrorderno` int NOT NULL AUTO_INCREMENT,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `customerref` varchar(50) NOT NULL DEFAULT '',
  `buyername` varchar(50) DEFAULT NULL,
  `comments` longblob,
  `orddate` date NOT NULL DEFAULT '1000-01-01',
  `ordertype` char(2) NOT NULL DEFAULT '',
  `shipvia` int NOT NULL DEFAULT '0',
  `deladd1` varchar(40) NOT NULL DEFAULT '',
  `deladd2` varchar(40) NOT NULL DEFAULT '',
  `deladd3` varchar(40) NOT NULL DEFAULT '',
  `deladd4` varchar(40) DEFAULT NULL,
  `deladd5` varchar(20) NOT NULL DEFAULT '',
  `deladd6` varchar(15) NOT NULL DEFAULT '',
  `contactphone` varchar(25) DEFAULT NULL,
  `contactemail` varchar(25) DEFAULT NULL,
  `deliverto` varchar(40) NOT NULL DEFAULT '',
  `freightcost` double NOT NULL DEFAULT '0',
  `fromstkloc` varchar(5) NOT NULL DEFAULT '',
  `lastrecurrence` date NOT NULL DEFAULT '1000-01-01',
  `stopdate` date NOT NULL DEFAULT '1000-01-01',
  `frequency` tinyint NOT NULL DEFAULT '1',
  `autoinvoice` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`recurrorderno`),
  KEY `debtorno` (`debtorno`),
  KEY `orddate` (`orddate`),
  KEY `ordertype` (`ordertype`),
  KEY `locationindex` (`fromstkloc`),
  KEY `branchcode` (`branchcode`,`debtorno`),
  CONSTRAINT `recurringsalesorders_ibfk_1` FOREIGN KEY (`branchcode`, `debtorno`) REFERENCES `custbranch` (`branchcode`, `debtorno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `recurrsalesorderdetails` (
  `recurrorderno` int NOT NULL DEFAULT '0',
  `stkcode` varchar(64) NOT NULL DEFAULT '',
  `unitprice` double NOT NULL DEFAULT '0',
  `quantity` double NOT NULL DEFAULT '0',
  `discountpercent` double NOT NULL DEFAULT '0',
  `narrative` text NOT NULL,
  KEY `orderno` (`recurrorderno`),
  KEY `stkcode` (`stkcode`),
  CONSTRAINT `recurrsalesorderdetails_ibfk_1` FOREIGN KEY (`recurrorderno`) REFERENCES `recurringsalesorders` (`recurrorderno`),
  CONSTRAINT `recurrsalesorderdetails_ibfk_2` FOREIGN KEY (`stkcode`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `regularpayments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `frequency` char(1) NOT NULL DEFAULT 'M',
  `days` tinyint NOT NULL DEFAULT '0',
  `glcode` varchar(20) NOT NULL DEFAULT '1',
  `bankaccountcode` varchar(20) NOT NULL DEFAULT '0',
  `tag` varchar(255) NOT NULL DEFAULT '',
  `amount` double NOT NULL DEFAULT '0',
  `currabrev` char(3) NOT NULL DEFAULT '',
  `narrative` varchar(255) DEFAULT '',
  `firstpayment` date NOT NULL DEFAULT '1001-01-01',
  `finalpayment` date NOT NULL DEFAULT '1001-01-01',
  `nextpayment` date NOT NULL DEFAULT '1001-01-01',
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `relateditems` (
  `stockid` varchar(64) NOT NULL,
  `related` varchar(64) NOT NULL,
  PRIMARY KEY (`stockid`,`related`),
  UNIQUE KEY `Related` (`related`,`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `reportcolumns` (
  `reportid` smallint NOT NULL DEFAULT '0',
  `colno` smallint NOT NULL DEFAULT '0',
  `heading1` varchar(15) NOT NULL DEFAULT '',
  `heading2` varchar(15) DEFAULT NULL,
  `calculation` tinyint(1) NOT NULL DEFAULT '0',
  `periodfrom` smallint DEFAULT NULL,
  `periodto` smallint DEFAULT NULL,
  `datatype` varchar(15) DEFAULT NULL,
  `colnumerator` tinyint DEFAULT NULL,
  `coldenominator` tinyint DEFAULT NULL,
  `calcoperator` char(1) DEFAULT NULL,
  `budgetoractual` tinyint(1) NOT NULL DEFAULT '0',
  `valformat` char(1) NOT NULL DEFAULT 'N',
  `constant` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`reportid`,`colno`),
  CONSTRAINT `reportcolumns_ibfk_1` FOREIGN KEY (`reportid`) REFERENCES `reportheaders` (`reportid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `reportfields` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reportid` int NOT NULL DEFAULT '0',
  `entrytype` varchar(15) NOT NULL DEFAULT '',
  `seqnum` int NOT NULL DEFAULT '0',
  `fieldname` varchar(80) NOT NULL DEFAULT '',
  `displaydesc` varchar(25) NOT NULL DEFAULT '',
  `visible` enum('1','0') NOT NULL DEFAULT '1',
  `columnbreak` enum('1','0') NOT NULL DEFAULT '1',
  `params` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reportid` (`reportid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
CREATE TABLE `reportheaders` (
  `reportid` smallint NOT NULL AUTO_INCREMENT,
  `reportheading` varchar(80) NOT NULL DEFAULT '',
  `groupbydata1` varchar(15) NOT NULL DEFAULT '',
  `newpageafter1` tinyint(1) NOT NULL DEFAULT '0',
  `lower1` varchar(10) NOT NULL DEFAULT '',
  `upper1` varchar(10) NOT NULL DEFAULT '',
  `groupbydata2` varchar(15) DEFAULT NULL,
  `newpageafter2` tinyint(1) NOT NULL DEFAULT '0',
  `lower2` varchar(10) DEFAULT NULL,
  `upper2` varchar(10) DEFAULT NULL,
  `groupbydata3` varchar(15) DEFAULT NULL,
  `newpageafter3` tinyint(1) NOT NULL DEFAULT '0',
  `lower3` varchar(10) DEFAULT NULL,
  `upper3` varchar(10) DEFAULT NULL,
  `groupbydata4` varchar(15) NOT NULL DEFAULT '',
  `newpageafter4` tinyint(1) NOT NULL DEFAULT '0',
  `upper4` varchar(10) NOT NULL DEFAULT '',
  `lower4` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`reportid`),
  KEY `ReportHeading` (`reportheading`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `reportlinks` (
  `table1` varchar(25) NOT NULL DEFAULT '',
  `table2` varchar(25) NOT NULL DEFAULT '',
  `equation` varchar(75) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `reports` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reportname` varchar(30) NOT NULL DEFAULT '',
  `reporttype` char(3) NOT NULL DEFAULT 'rpt',
  `groupname` varchar(9) NOT NULL DEFAULT 'misc',
  `defaultreport` enum('1','0') NOT NULL DEFAULT '0',
  `papersize` varchar(15) NOT NULL DEFAULT 'A4,210,297',
  `paperorientation` enum('P','L') NOT NULL DEFAULT 'P',
  `margintop` int NOT NULL DEFAULT '10',
  `marginbottom` int NOT NULL DEFAULT '10',
  `marginleft` int NOT NULL DEFAULT '10',
  `marginright` int NOT NULL DEFAULT '10',
  `coynamefont` varchar(20) NOT NULL DEFAULT 'Helvetica',
  `coynamefontsize` int NOT NULL DEFAULT '12',
  `coynamefontcolor` varchar(11) NOT NULL DEFAULT '0,0,0',
  `coynamealign` enum('L','C','R') NOT NULL DEFAULT 'C',
  `coynameshow` enum('1','0') NOT NULL DEFAULT '1',
  `title1desc` varchar(50) NOT NULL DEFAULT '%reportname%',
  `title1font` varchar(20) NOT NULL DEFAULT 'Helvetica',
  `title1fontsize` int NOT NULL DEFAULT '10',
  `title1fontcolor` varchar(11) NOT NULL DEFAULT '0,0,0',
  `title1fontalign` enum('L','C','R') NOT NULL DEFAULT 'C',
  `title1show` enum('1','0') NOT NULL DEFAULT '1',
  `title2desc` varchar(50) NOT NULL DEFAULT 'Report Generated %date%',
  `title2font` varchar(20) NOT NULL DEFAULT 'Helvetica',
  `title2fontsize` int NOT NULL DEFAULT '10',
  `title2fontcolor` varchar(11) NOT NULL DEFAULT '0,0,0',
  `title2fontalign` enum('L','C','R') NOT NULL DEFAULT 'C',
  `title2show` enum('1','0') NOT NULL DEFAULT '1',
  `filterfont` varchar(10) NOT NULL DEFAULT 'Helvetica',
  `filterfontsize` int NOT NULL DEFAULT '8',
  `filterfontcolor` varchar(11) NOT NULL DEFAULT '0,0,0',
  `filterfontalign` enum('L','C','R') NOT NULL DEFAULT 'L',
  `datafont` varchar(10) NOT NULL DEFAULT 'Helvetica',
  `datafontsize` int NOT NULL DEFAULT '10',
  `datafontcolor` varchar(10) NOT NULL DEFAULT 'black',
  `datafontalign` enum('L','C','R') NOT NULL DEFAULT 'L',
  `totalsfont` varchar(10) NOT NULL DEFAULT 'Helvetica',
  `totalsfontsize` int NOT NULL DEFAULT '10',
  `totalsfontcolor` varchar(11) NOT NULL DEFAULT '0,0,0',
  `totalsfontalign` enum('L','C','R') NOT NULL DEFAULT 'L',
  `col1width` int NOT NULL DEFAULT '25',
  `col2width` int NOT NULL DEFAULT '25',
  `col3width` int NOT NULL DEFAULT '25',
  `col4width` int NOT NULL DEFAULT '25',
  `col5width` int NOT NULL DEFAULT '25',
  `col6width` int NOT NULL DEFAULT '25',
  `col7width` int NOT NULL DEFAULT '25',
  `col8width` int NOT NULL DEFAULT '25',
  `col9width` int NOT NULL DEFAULT '25',
  `col10width` int NOT NULL DEFAULT '25',
  `col11width` int NOT NULL DEFAULT '25',
  `col12width` int NOT NULL DEFAULT '25',
  `col13width` int NOT NULL DEFAULT '25',
  `col14width` int NOT NULL DEFAULT '25',
  `col15width` int NOT NULL DEFAULT '25',
  `col16width` int NOT NULL DEFAULT '25',
  `col17width` int NOT NULL DEFAULT '25',
  `col18width` int NOT NULL DEFAULT '25',
  `col19width` int NOT NULL DEFAULT '25',
  `col20width` int NOT NULL DEFAULT '25',
  `table1` varchar(25) NOT NULL DEFAULT '',
  `table2` varchar(25) DEFAULT NULL,
  `table2criteria` varchar(75) DEFAULT NULL,
  `table3` varchar(25) DEFAULT NULL,
  `table3criteria` varchar(75) DEFAULT NULL,
  `table4` varchar(25) DEFAULT NULL,
  `table4criteria` varchar(75) DEFAULT NULL,
  `table5` varchar(25) DEFAULT NULL,
  `table5criteria` varchar(75) DEFAULT NULL,
  `table6` varchar(25) DEFAULT NULL,
  `table6criteria` varchar(75) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`reportname`,`groupname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
CREATE TABLE `salesanalysis` (
  `typeabbrev` char(2) NOT NULL DEFAULT '',
  `periodno` smallint NOT NULL DEFAULT '0',
  `amt` double NOT NULL DEFAULT '0',
  `cost` double NOT NULL DEFAULT '0',
  `cust` varchar(10) NOT NULL DEFAULT '',
  `custbranch` varchar(10) NOT NULL DEFAULT '',
  `qty` double NOT NULL DEFAULT '0',
  `disc` double NOT NULL DEFAULT '0',
  `stockid` varchar(64) NOT NULL DEFAULT '',
  `area` varchar(3) NOT NULL,
  `budgetoractual` tinyint(1) NOT NULL DEFAULT '0',
  `salesperson` varchar(4) NOT NULL DEFAULT '',
  `stkcategory` varchar(6) NOT NULL DEFAULT '',
  `id` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  KEY `CustBranch` (`custbranch`),
  KEY `Cust` (`cust`),
  KEY `PeriodNo` (`periodno`),
  KEY `StkCategory` (`stkcategory`),
  KEY `StockID` (`stockid`),
  KEY `TypeAbbrev` (`typeabbrev`),
  KEY `Area` (`area`),
  KEY `BudgetOrActual` (`budgetoractual`),
  KEY `Salesperson` (`salesperson`),
  CONSTRAINT `salesanalysis_ibfk_1` FOREIGN KEY (`periodno`) REFERENCES `periods` (`periodno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `salescatprod` (
  `salescatid` tinyint NOT NULL DEFAULT '0',
  `stockid` varchar(64) NOT NULL DEFAULT '',
  `manufacturers_id` int NOT NULL,
  `featured` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`salescatid`,`stockid`),
  KEY `salescatid` (`salescatid`),
  KEY `stockid` (`stockid`),
  KEY `manufacturer_id` (`manufacturers_id`),
  CONSTRAINT `salescatprod_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `salescatprod_ibfk_2` FOREIGN KEY (`salescatid`) REFERENCES `salescat` (`salescatid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `salescat` (
  `salescatid` tinyint NOT NULL AUTO_INCREMENT,
  `parentcatid` tinyint DEFAULT NULL,
  `salescatname` varchar(50) DEFAULT NULL,
  `active` int NOT NULL DEFAULT '1' COMMENT '1 if active 0 if inactive',
  PRIMARY KEY (`salescatid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `salescattranslations` (
  `salescatid` tinyint NOT NULL DEFAULT '0',
  `language_id` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  `salescattranslation` varchar(40) NOT NULL,
  PRIMARY KEY (`salescatid`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `salescommissionrates` (
  `salespersoncode` varchar(4) NOT NULL DEFAULT '',
  `categoryid` char(6) NOT NULL DEFAULT '',
  `area` char(3) NOT NULL DEFAULT '',
  `startfrom` double NOT NULL DEFAULT '0',
  `daysactive` int NOT NULL DEFAULT '0',
  `rate` double NOT NULL DEFAULT '0',
  `currency` char(3) NOT NULL DEFAULT '',
  PRIMARY KEY (`salespersoncode`,`categoryid`,`startfrom`),
  KEY `salespersoncode` (`salespersoncode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `salescommissions` (
  `commissionno` int NOT NULL DEFAULT '0',
  `type` smallint NOT NULL DEFAULT '10',
  `transno` int NOT NULL DEFAULT '0',
  `stkmoveno` int NOT NULL DEFAULT '0',
  `salespersoncode` varchar(4) NOT NULL DEFAULT '',
  `paid` int NOT NULL DEFAULT '0',
  `amount` double NOT NULL DEFAULT '0',
  `currency` char(3) NOT NULL DEFAULT '',
  `exrate` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`type`,`transno`),
  KEY `salespersoncode` (`salespersoncode`),
  KEY `paid` (`paid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `salescommissiontypes` (
  `commissiontypeid` tinyint NOT NULL AUTO_INCREMENT,
  `commissiontypename` varchar(55) NOT NULL DEFAULT '',
  PRIMARY KEY (`commissiontypeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `salesglpostings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `area` varchar(3) NOT NULL,
  `stkcat` varchar(6) NOT NULL DEFAULT '',
  `discountglcode` varchar(20) NOT NULL DEFAULT '0',
  `salesglcode` varchar(20) NOT NULL DEFAULT '0',
  `salestype` char(2) NOT NULL DEFAULT 'AN',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Area_StkCat` (`area`,`stkcat`,`salestype`),
  KEY `Area` (`area`),
  KEY `StkCat` (`stkcat`),
  KEY `SalesType` (`salestype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `salesman` (
  `salesmancode` varchar(4) NOT NULL DEFAULT '',
  `salesmanname` char(30) NOT NULL DEFAULT '',
  `smantel` char(20) NOT NULL DEFAULT '',
  `smanfax` char(20) NOT NULL DEFAULT '',
  `current` tinyint NOT NULL COMMENT 'Salesman current (1) or not (0)',
  `commissionperiod` int NOT NULL DEFAULT '0',
  `commissiontypeid` tinyint NOT NULL DEFAULT '0',
  `glaccount` varchar(20) NOT NULL DEFAULT '1',
  PRIMARY KEY (`salesmancode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `salesorderdetails` (
  `orderlineno` int NOT NULL DEFAULT '0',
  `orderno` int NOT NULL DEFAULT '0',
  `stkcode` varchar(64) NOT NULL DEFAULT '',
  `qtyinvoiced` double NOT NULL DEFAULT '0',
  `unitprice` double NOT NULL DEFAULT '0',
  `quantity` double NOT NULL DEFAULT '0',
  `estimate` tinyint NOT NULL DEFAULT '0',
  `discountpercent` double NOT NULL DEFAULT '0',
  `actualdispatchdate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `narrative` text,
  `itemdue` date DEFAULT NULL COMMENT 'Due date for line item.  Some customers require \r\nacknowledgements with due dates by line item',
  `poline` varchar(10) DEFAULT NULL COMMENT 'Some Customers require acknowledgements with a PO line number for each sales line',
  `linenetprice` double GENERATED ALWAYS AS ((`qtyinvoiced` * (`unitprice` * (1 - `discountpercent`)))) STORED,
  PRIMARY KEY (`orderlineno`,`orderno`),
  KEY `OrderNo` (`orderno`),
  KEY `StkCode` (`stkcode`),
  KEY `Completed` (`completed`),
  CONSTRAINT `salesorderdetails_ibfk_1` FOREIGN KEY (`orderno`) REFERENCES `salesorders` (`orderno`),
  CONSTRAINT `salesorderdetails_ibfk_2` FOREIGN KEY (`stkcode`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `salesorders` (
  `orderno` int NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `customerref` varchar(50) NOT NULL DEFAULT '',
  `buyername` varchar(50) DEFAULT NULL,
  `comments` longblob DEFAULT NULL	,
  `orddate` date NOT NULL DEFAULT '1000-01-01',
  `ordertype` char(2) NOT NULL DEFAULT '',
  `shipvia` int NOT NULL DEFAULT '0',
  `deladd1` varchar(40) NOT NULL DEFAULT '',
  `deladd2` varchar(40) NOT NULL DEFAULT '',
  `deladd3` varchar(40) NOT NULL DEFAULT '',
  `deladd4` varchar(40) DEFAULT NULL,
  `deladd5` varchar(20) NOT NULL DEFAULT '',
  `deladd6` varchar(15) NOT NULL DEFAULT '',
  `contactphone` varchar(25) DEFAULT NULL,
  `contactemail` varchar(40) DEFAULT NULL,
  `deliverto` varchar(40) NOT NULL DEFAULT '',
  `deliverblind` tinyint(1) DEFAULT '1',
  `freightcost` double NOT NULL DEFAULT '0',
  `fromstkloc` varchar(5) NOT NULL DEFAULT '',
  `deliverydate` date NOT NULL DEFAULT '1000-01-01',
  `confirmeddate` date NOT NULL DEFAULT '1000-01-01',
  `printedpackingslip` tinyint NOT NULL DEFAULT '0',
  `datepackingslipprinted` date NOT NULL DEFAULT '1000-01-01',
  `quotation` tinyint NOT NULL DEFAULT '0',
  `quotedate` date NOT NULL DEFAULT '1000-01-01',
  `poplaced` tinyint NOT NULL DEFAULT '0',
  `salesperson` varchar(4) NOT NULL,
  `internalcomment` blob DEFAULT NULL,
  PRIMARY KEY (`orderno`),
  KEY `DebtorNo` (`debtorno`),
  KEY `OrdDate` (`orddate`),
  KEY `OrderType` (`ordertype`),
  KEY `LocationIndex` (`fromstkloc`),
  KEY `BranchCode` (`branchcode`,`debtorno`),
  KEY `ShipVia` (`shipvia`),
  KEY `quotation` (`quotation`),
  KEY `poplaced` (`poplaced`),
  KEY `salesperson` (`salesperson`),
  CONSTRAINT `salesorders_ibfk_1` FOREIGN KEY (`branchcode`, `debtorno`) REFERENCES `custbranch` (`branchcode`, `debtorno`),
  CONSTRAINT `salesorders_ibfk_2` FOREIGN KEY (`shipvia`) REFERENCES `shippers` (`shipper_id`),
  CONSTRAINT `salesorders_ibfk_3` FOREIGN KEY (`fromstkloc`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `salestypes` (
  `typeabbrev` char(2) NOT NULL DEFAULT '',
  `sales_type` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`typeabbrev`),
  KEY `Sales_Type` (`sales_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `sampleresults` (
  `resultid` bigint NOT NULL AUTO_INCREMENT,
  `sampleid` int NOT NULL,
  `testid` int NOT NULL,
  `defaultvalue` varchar(150) NOT NULL,
  `targetvalue` varchar(30) NOT NULL,
  `rangemin` float DEFAULT NULL,
  `rangemax` float DEFAULT NULL,
  `testvalue` varchar(30) NOT NULL DEFAULT '',
  `testdate` date NOT NULL DEFAULT '1000-01-01',
  `testedby` varchar(15) NOT NULL DEFAULT '',
  `comments` varchar(255) NOT NULL DEFAULT '',
  `isinspec` tinyint NOT NULL DEFAULT '0',
  `showoncert` tinyint NOT NULL DEFAULT '1',
  `showontestplan` tinyint NOT NULL DEFAULT '1',
  `manuallyadded` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`resultid`),
  KEY `sampleid` (`sampleid`),
  KEY `testid` (`testid`),
  CONSTRAINT `sampleresults_ibfk_1` FOREIGN KEY (`testid`) REFERENCES `qatests` (`testid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `scripts` (
  `script` varchar(78) NOT NULL DEFAULT '',
  `pagesecurity` int NOT NULL DEFAULT '1',
  `description` text NOT NULL,
  PRIMARY KEY (`script`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `securitygroups` (
  `secroleid` int NOT NULL DEFAULT '0',
  `tokenid` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`secroleid`,`tokenid`),
  KEY `secroleid` (`secroleid`),
  KEY `tokenid` (`tokenid`),
  CONSTRAINT `securitygroups_secroleid_fk` FOREIGN KEY (`secroleid`) REFERENCES `securityroles` (`secroleid`),
  CONSTRAINT `securitygroups_tokenid_fk` FOREIGN KEY (`tokenid`) REFERENCES `securitytokens` (`tokenid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `securityroles` (
  `secroleid` int NOT NULL AUTO_INCREMENT,
  `secrolename` text NOT NULL,
  PRIMARY KEY (`secroleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `securitytokens` (
  `tokenid` int NOT NULL DEFAULT '0',
  `tokenname` text NOT NULL,
  PRIMARY KEY (`tokenid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `sellthroughsupport` (
  `id` int NOT NULL AUTO_INCREMENT,
  `supplierno` varchar(10) NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `categoryid` char(6) NOT NULL DEFAULT '',
  `stockid` varchar(64) NOT NULL DEFAULT '',
  `narrative` varchar(20) NOT NULL DEFAULT '',
  `rebatepercent` double NOT NULL DEFAULT '0',
  `rebateamount` double NOT NULL DEFAULT '0',
  `effectivefrom` date NOT NULL,
  `effectiveto` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `supplierno` (`supplierno`),
  KEY `debtorno` (`debtorno`),
  KEY `effectivefrom` (`effectivefrom`),
  KEY `effectiveto` (`effectiveto`),
  KEY `stockid` (`stockid`),
  KEY `categoryid` (`categoryid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `session_data` (
  `userid` varchar(20) NOT NULL,
  `field` varchar(100) NOT NULL DEFAULT '',
  `value` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`userid`,`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `sessions` (
  `sessionid` char(32),
  `logintime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `userid` varchar(20),
  `script` varchar(100) NOT NULL DEFAULT '',
  `scripttime` TIMESTAMP NULL,
  PRIMARY KEY (`sessionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `shipmentcharges` (
  `shiptchgid` int NOT NULL AUTO_INCREMENT,
  `shiptref` int NOT NULL DEFAULT '0',
  `transtype` smallint NOT NULL DEFAULT '0',
  `transno` int NOT NULL DEFAULT '0',
  `stockid` varchar(64) NOT NULL DEFAULT '',
  `value` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`shiptchgid`),
  KEY `TransType` (`transtype`,`transno`),
  KEY `ShiptRef` (`shiptref`),
  KEY `StockID` (`stockid`),
  KEY `TransType_2` (`transtype`),
  CONSTRAINT `shipmentcharges_ibfk_1` FOREIGN KEY (`shiptref`) REFERENCES `shipments` (`shiptref`),
  CONSTRAINT `shipmentcharges_ibfk_2` FOREIGN KEY (`transtype`) REFERENCES `systypes` (`typeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `shipments` (
  `shiptref` int NOT NULL DEFAULT '0',
  `voyageref` varchar(20) NOT NULL DEFAULT '0',
  `vessel` varchar(50) NOT NULL DEFAULT '',
  `eta` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `accumvalue` double NOT NULL DEFAULT '0',
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `closed` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`shiptref`),
  KEY `ETA` (`eta`),
  KEY `SupplierID` (`supplierid`),
  KEY `ShipperRef` (`voyageref`),
  KEY `Vessel` (`vessel`),
  CONSTRAINT `shipments_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`supplierid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `shippers` (
  `shipper_id` int NOT NULL AUTO_INCREMENT,
  `shippername` char(40) NOT NULL DEFAULT '',
  `mincharge` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`shipper_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `stockcategory` (
  `categoryid` varchar(6) NOT NULL DEFAULT '',
  `categorydescription` char(20) NOT NULL DEFAULT '',
  `stocktype` char(1) NOT NULL DEFAULT 'F',
  `stockact` varchar(20) NOT NULL DEFAULT '0',
  `adjglact` varchar(20) NOT NULL DEFAULT '0',
  `issueglact` varchar(20) NOT NULL DEFAULT '0',
  `purchpricevaract` varchar(20) NOT NULL DEFAULT '80000',
  `materialuseagevarac` varchar(20) NOT NULL DEFAULT '80000',
  `wipact` varchar(20) NOT NULL DEFAULT '0',
  `defaulttaxcatid` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`categoryid`),
  KEY `CategoryDescription` (`categorydescription`),
  KEY `StockType` (`stocktype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `stockcatproperties` (
  `stkcatpropid` int NOT NULL AUTO_INCREMENT,
  `categoryid` varchar(6) NOT NULL,
  `label` text NOT NULL,
  `controltype` tinyint NOT NULL DEFAULT '0',
  `defaultvalue` varchar(100) NOT NULL DEFAULT '''''',
  `maximumvalue` double NOT NULL DEFAULT '999999999',
  `reqatsalesorder` tinyint NOT NULL DEFAULT '0',
  `minimumvalue` double NOT NULL DEFAULT '-999999999',
  `numericvalue` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`stkcatpropid`),
  KEY `categoryid` (`categoryid`),
  CONSTRAINT `stockcatproperties_ibfk_1` FOREIGN KEY (`categoryid`) REFERENCES `stockcategory` (`categoryid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `stockcheckfreeze` (
  `stockid` varchar(64) NOT NULL DEFAULT '',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `qoh` double NOT NULL DEFAULT '0',
  `stockcheckdate` date NOT NULL DEFAULT '1000-01-01',
  PRIMARY KEY (`stockid`,`loccode`),
  KEY `LocCode` (`loccode`),
  CONSTRAINT `stockcheckfreeze_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `stockcheckfreeze_ibfk_2` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `stockcounts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `stockid` varchar(64) NOT NULL DEFAULT '',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `qtycounted` double NOT NULL DEFAULT '0',
  `reference` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `StockID` (`stockid`),
  KEY `LocCode` (`loccode`),
  CONSTRAINT `stockcounts_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `stockcounts_ibfk_2` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `stockdescriptiontranslations` (
  `stockid` varchar(64) NOT NULL DEFAULT '',
  `language_id` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  `descriptiontranslation` varchar(50) DEFAULT NULL COMMENT 'Item''s short description',
  `longdescriptiontranslation` text DEFAULT NULL COMMENT 'Item''s long description',
  `needsrevision` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`stockid`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `stockitemnotes` (
  `noteid` int NOT NULL AUTO_INCREMENT,
  `stockid` varchar(64) NOT NULL DEFAULT '0',
  `note` text NOT NULL,
  `date` date NOT NULL DEFAULT '1000-01-01',
  PRIMARY KEY (`noteid`),
  KEY `stockitemnotes_ibfk_1` (`stockid`),
  CONSTRAINT `stockitemnotes_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
CREATE TABLE `stockitemproperties` (
  `stockid` varchar(64) NOT NULL,
  `stkcatpropid` int NOT NULL,
  `value` varchar(50) NOT NULL,
  PRIMARY KEY (`stockid`,`stkcatpropid`),
  KEY `stockid` (`stockid`),
  KEY `value` (`value`),
  KEY `stkcatpropid` (`stkcatpropid`),
  CONSTRAINT `stockitemproperties_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `stockitemproperties_ibfk_2` FOREIGN KEY (`stkcatpropid`) REFERENCES `stockcatproperties` (`stkcatpropid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `stockmaster` (
  `stockid` varchar(64) NOT NULL DEFAULT '',
  `categoryid` varchar(6) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `longdescription` text NOT NULL,
  `units` varchar(20) NOT NULL DEFAULT 'each',
  `mbflag` char(1) NOT NULL DEFAULT 'B',
  `lastcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `materialcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `labourcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `overheadcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `lowestlevel` smallint NOT NULL DEFAULT '0',
  `discontinued` tinyint NOT NULL DEFAULT '0',
  `controlled` tinyint NOT NULL DEFAULT '0',
  `eoq` double NOT NULL DEFAULT '0',
  `volume` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `grossweight` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `barcode` varchar(50) NOT NULL DEFAULT '',
  `discountcategory` char(2) NOT NULL DEFAULT '',
  `taxcatid` tinyint NOT NULL DEFAULT '1',
  `serialised` tinyint NOT NULL DEFAULT '0',
  `perishable` tinyint(1) NOT NULL DEFAULT '0',
  `decimalplaces` tinyint NOT NULL DEFAULT '0',
  `pansize` double NOT NULL DEFAULT '0',
  `shrinkfactor` double NOT NULL DEFAULT '0',
  `nextserialno` bigint NOT NULL DEFAULT '0',
  `netweight` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `lastcostupdate` date NOT NULL DEFAULT '1000-01-01',
  `actualcost` decimal(20,4) GENERATED ALWAYS AS (((`materialcost` + `labourcost`) + `overheadcost`)) STORED,
  PRIMARY KEY (`stockid`),
  KEY `CategoryID` (`categoryid`),
  KEY `Description` (`description`),
  KEY `MBflag` (`mbflag`),
  KEY `StockID` (`stockid`,`categoryid`),
  KEY `Controlled` (`controlled`),
  KEY `DiscountCategory` (`discountcategory`),
  KEY `taxcatid` (`taxcatid`),
  CONSTRAINT `stockmaster_ibfk_1` FOREIGN KEY (`categoryid`) REFERENCES `stockcategory` (`categoryid`),
  CONSTRAINT `stockmaster_ibfk_2` FOREIGN KEY (`taxcatid`) REFERENCES `taxcategories` (`taxcatid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `stockmoves` (
  `stkmoveno` int NOT NULL AUTO_INCREMENT,
  `stockid` varchar(64) NOT NULL DEFAULT '',
  `type` smallint NOT NULL DEFAULT '0',
  `transno` int NOT NULL DEFAULT '0',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `trandate` date NOT NULL DEFAULT '1000-01-01',
  `userid` varchar(20) NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `price` decimal(21,5) NOT NULL DEFAULT '0.00000',
  `prd` smallint NOT NULL DEFAULT '0',
  `reference` varchar(100) NOT NULL DEFAULT '',
  `qty` double NOT NULL DEFAULT '1',
  `discountpercent` double NOT NULL DEFAULT '0',
  `standardcost` double NOT NULL DEFAULT '0',
  `show_on_inv_crds` tinyint NOT NULL DEFAULT '1',
  `newqoh` double NOT NULL DEFAULT '0',
  `hidemovt` tinyint NOT NULL DEFAULT '0',
  `narrative` text DEFAULT NULL,
  PRIMARY KEY (`stkmoveno`),
  KEY `DebtorNo` (`debtorno`),
  KEY `LocCode` (`loccode`),
  KEY `Prd` (`prd`),
  KEY `StockID_2` (`stockid`),
  KEY `TranDate` (`trandate`),
  KEY `TransNo` (`transno`),
  KEY `Type` (`type`),
  KEY `Show_On_Inv_Crds` (`show_on_inv_crds`),
  KEY `Hide` (`hidemovt`),
  KEY `reference` (`reference`),
  CONSTRAINT `stockmoves_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `stockmoves_ibfk_2` FOREIGN KEY (`type`) REFERENCES `systypes` (`typeid`),
  CONSTRAINT `stockmoves_ibfk_3` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `stockmoves_ibfk_4` FOREIGN KEY (`prd`) REFERENCES `periods` (`periodno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `stockmovestaxes` (
  `stkmoveno` int NOT NULL DEFAULT '0',
  `taxauthid` tinyint NOT NULL DEFAULT '0',
  `taxrate` double NOT NULL DEFAULT '0',
  `taxontax` tinyint NOT NULL DEFAULT '0',
  `taxcalculationorder` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`stkmoveno`,`taxauthid`),
  KEY `taxauthid` (`taxauthid`),
  KEY `calculationorder` (`taxcalculationorder`),
  CONSTRAINT `stockmovestaxes_ibfk_1` FOREIGN KEY (`taxauthid`) REFERENCES `taxauthorities` (`taxid`),
  CONSTRAINT `stockmovestaxes_ibfk_2` FOREIGN KEY (`stkmoveno`) REFERENCES `stockmoves` (`stkmoveno`),
  CONSTRAINT `stockmovestaxes_ibfk_3` FOREIGN KEY (`stkmoveno`) REFERENCES `stockmoves` (`stkmoveno`),
  CONSTRAINT `stockmovestaxes_ibfk_4` FOREIGN KEY (`stkmoveno`) REFERENCES `stockmoves` (`stkmoveno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `stockrequestitems` (
  `dispatchitemsid` int NOT NULL DEFAULT '0',
  `dispatchid` int NOT NULL DEFAULT '0',
  `stockid` varchar(64) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '0',
  `qtydelivered` double NOT NULL DEFAULT '0',
  `decimalplaces` int NOT NULL DEFAULT '0',
  `uom` varchar(20) NOT NULL DEFAULT '',
  `completed` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`dispatchitemsid`,`dispatchid`),
  KEY `dispatchid` (`dispatchid`),
  KEY `stockid` (`stockid`),
  CONSTRAINT `stockrequestitems_ibfk_1` FOREIGN KEY (`dispatchid`) REFERENCES `stockrequest` (`dispatchid`),
  CONSTRAINT `stockrequestitems_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `stockrequest` (
  `dispatchid` int NOT NULL AUTO_INCREMENT,
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `departmentid` int NOT NULL DEFAULT '0',
  `despatchdate` date NOT NULL DEFAULT '1000-01-01',
  `authorised` tinyint NOT NULL DEFAULT '0',
  `closed` tinyint NOT NULL DEFAULT '0',
  `narrative` text NOT NULL,
  `initiator` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`dispatchid`),
  KEY `loccode` (`loccode`),
  KEY `departmentid` (`departmentid`),
  CONSTRAINT `stockrequest_ibfk_1` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `stockrequest_ibfk_2` FOREIGN KEY (`departmentid`) REFERENCES `departments` (`departmentid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `stockserialitems` (
  `stockid` varchar(64) NOT NULL DEFAULT '',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `serialno` varchar(30) NOT NULL DEFAULT '',
  `expirationdate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `quantity` double NOT NULL DEFAULT '0',
  `qualitytext` text NOT NULL,
  `createdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`stockid`,`serialno`,`loccode`),
  UNIQUE KEY `stockid_serialno` (`stockid`,`serialno`),  -- ⭐ NEW
  KEY `StockID` (`stockid`),
  KEY `LocCode` (`loccode`),
  KEY `serialno` (`serialno`),
  KEY `createdate` (`createdate`),
  CONSTRAINT `stockserialitems_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `stockserialitems_ibfk_2` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `stockserialmoves` (
  `stkitmmoveno` int NOT NULL AUTO_INCREMENT,
  `stockmoveno` int NOT NULL DEFAULT '0',
  `stockid` varchar(64) NOT NULL DEFAULT '',
  `serialno` varchar(30) NOT NULL DEFAULT '',
  `moveqty` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`stkitmmoveno`),
  KEY `StockMoveNo` (`stockmoveno`),
  KEY `StockID_SN` (`stockid`,`serialno`),
  KEY `serialno` (`serialno`),
  CONSTRAINT `stockserialmoves_ibfk_1` FOREIGN KEY (`stockmoveno`) REFERENCES `stockmoves` (`stkmoveno`),
  CONSTRAINT `stockserialmoves_ibfk_2` FOREIGN KEY (`stockid`, `serialno`) REFERENCES `stockserialitems` (`stockid`, `serialno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `suppallocs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `amt` double NOT NULL DEFAULT '0',
  `datealloc` date NOT NULL DEFAULT '1000-01-01',
  `transid_allocfrom` int NOT NULL DEFAULT '0',
  `transid_allocto` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `TransID_AllocFrom` (`transid_allocfrom`),
  KEY `TransID_AllocTo` (`transid_allocto`),
  KEY `DateAlloc` (`datealloc`),
  CONSTRAINT `suppallocs_ibfk_1` FOREIGN KEY (`transid_allocfrom`) REFERENCES `supptrans` (`id`),
  CONSTRAINT `suppallocs_ibfk_2` FOREIGN KEY (`transid_allocto`) REFERENCES `supptrans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `suppinvstogrn` (
  `suppinv` int NOT NULL,
  `grnno` int NOT NULL,
  PRIMARY KEY (`suppinv`,`grnno`),
  KEY `suppinvstogrn_ibfk_1` (`grnno`),
  CONSTRAINT `suppinvstogrn_ibfk_1` FOREIGN KEY (`grnno`) REFERENCES `grns` (`grnno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `suppliercontacts` (
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `contact` varchar(30) NOT NULL DEFAULT '',
  `position` varchar(30) NOT NULL DEFAULT '',
  `tel` varchar(30) NOT NULL DEFAULT '',
  `fax` varchar(30) NOT NULL DEFAULT '',
  `mobile` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(55) NOT NULL DEFAULT '',
  `ordercontact` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`supplierid`,`contact`),
  KEY `Contact` (`contact`),
  KEY `SupplierID` (`supplierid`),
  CONSTRAINT `suppliercontacts_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`supplierid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `supplierdiscounts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `supplierno` varchar(10) NOT NULL,
  `stockid` varchar(64) NOT NULL,
  `discountnarrative` varchar(20) NOT NULL,
  `discountpercent` double NOT NULL,
  `discountamount` double NOT NULL,
  `effectivefrom` date NOT NULL,
  `effectiveto` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `supplierno` (`supplierno`),
  KEY `effectivefrom` (`effectivefrom`),
  KEY `effectiveto` (`effectiveto`),
  KEY `stockid` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `suppliers` (
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `suppname` varchar(40) NOT NULL DEFAULT '',
  `address1` varchar(40) NOT NULL DEFAULT '',
  `address2` varchar(40) NOT NULL DEFAULT '',
  `address3` varchar(40) NOT NULL DEFAULT '',
  `address4` varchar(50) NOT NULL DEFAULT '',
  `address5` varchar(20) NOT NULL DEFAULT '',
  `address6` varchar(40) NOT NULL DEFAULT '',
  `supptype` tinyint NOT NULL DEFAULT '1',
  `lat` float(10,6) NOT NULL DEFAULT '0.000000',
  `lng` float(10,6) NOT NULL DEFAULT '0.000000',
  `currcode` char(3) NOT NULL DEFAULT '',
  `suppliersince` date NOT NULL DEFAULT '1000-01-01',
  `paymentterms` char(2) NOT NULL DEFAULT '',
  `lastpaid` double NOT NULL DEFAULT '0',
  `lastpaiddate` date DEFAULT NULL,
  `bankact` varchar(30) NOT NULL DEFAULT '',
  `bankref` varchar(12) NOT NULL DEFAULT '',
  `bankpartics` varchar(12) NOT NULL DEFAULT '',
  `remittance` tinyint NOT NULL DEFAULT '1',
  `taxgroupid` tinyint NOT NULL DEFAULT '1',
  `factorcompanyid` int NOT NULL DEFAULT '1',
  `salespersonid` varchar(4) NOT NULL DEFAULT '',
  `taxref` varchar(20) NOT NULL DEFAULT '',
  `phn` varchar(50) NOT NULL DEFAULT '',
  `port` varchar(200) NOT NULL DEFAULT '',
  `email` varchar(55) DEFAULT NULL,
  `fax` varchar(25) DEFAULT NULL,
  `telephone` varchar(25) DEFAULT NULL,
  `url` varchar(50) NOT NULL DEFAULT '',
  `defaultshipper` int NOT NULL DEFAULT '0',
  `defaultgl` varchar(20) NOT NULL DEFAULT '1',
  PRIMARY KEY (`supplierid`),
  KEY `CurrCode` (`currcode`),
  KEY `PaymentTerms` (`paymentterms`),
  KEY `SuppName` (`suppname`),
  KEY `taxgroupid` (`taxgroupid`),
  CONSTRAINT `suppliers_ibfk_1` FOREIGN KEY (`currcode`) REFERENCES `currencies` (`currabrev`),
  CONSTRAINT `suppliers_ibfk_2` FOREIGN KEY (`paymentterms`) REFERENCES `paymentterms` (`termsindicator`),
  CONSTRAINT `suppliers_ibfk_3` FOREIGN KEY (`taxgroupid`) REFERENCES `taxgroups` (`taxgroupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `suppliertype` (
  `typeid` tinyint NOT NULL AUTO_INCREMENT,
  `typename` varchar(100) NOT NULL,
  PRIMARY KEY (`typeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `supptrans` (
  `transno` int NOT NULL DEFAULT '0',
  `type` smallint NOT NULL DEFAULT '0',
  `supplierno` varchar(10) NOT NULL DEFAULT '',
  `suppreference` varchar(20) NOT NULL DEFAULT '',
  `trandate` date NOT NULL DEFAULT '1000-01-01',
  `duedate` date NOT NULL DEFAULT '1000-01-01',
  `inputdate` datetime NOT NULL,
  `settled` tinyint NOT NULL DEFAULT '0',
  `rate` double NOT NULL DEFAULT '1',
  `ovamount` double NOT NULL DEFAULT '0',
  `ovgst` double NOT NULL DEFAULT '0',
  `diffonexch` double NOT NULL DEFAULT '0',
  `alloc` double NOT NULL DEFAULT '0',
  `transtext` text DEFAULT NULL,
  `hold` tinyint NOT NULL DEFAULT '0',
  `chequeno` varchar(16) NOT NULL DEFAULT '',
  `void` tinyint(1) NOT NULL DEFAULT '0',
  `id` int NOT NULL AUTO_INCREMENT,
  `balance` double GENERATED ALWAYS AS (((`ovamount` + `ovgst`) - `alloc`)) STORED,
  PRIMARY KEY (`id`),
  KEY `DueDate` (`duedate`),
  KEY `Hold` (`hold`),
  KEY `SupplierNo` (`supplierno`),
  KEY `Settled` (`settled`),
  KEY `SupplierNo_2` (`supplierno`,`suppreference`),
  KEY `SuppReference` (`suppreference`),
  KEY `TranDate` (`trandate`),
  KEY `TransNo` (`transno`),
  KEY `Type` (`type`),
  KEY `TypeTransNo` (`transno`,`type`),
  CONSTRAINT `supptrans_ibfk_1` FOREIGN KEY (`type`) REFERENCES `systypes` (`typeid`),
  CONSTRAINT `supptrans_ibfk_2` FOREIGN KEY (`supplierno`) REFERENCES `suppliers` (`supplierid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `supptranstaxes` (
  `supptransid` int NOT NULL DEFAULT '0',
  `taxauthid` tinyint NOT NULL DEFAULT '0',
  `taxamount` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`supptransid`,`taxauthid`),
  KEY `taxauthid` (`taxauthid`),
  CONSTRAINT `supptranstaxes_ibfk_1` FOREIGN KEY (`taxauthid`) REFERENCES `taxauthorities` (`taxid`),
  CONSTRAINT `supptranstaxes_ibfk_2` FOREIGN KEY (`supptransid`) REFERENCES `supptrans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `systypes` (
  `typeid` smallint NOT NULL DEFAULT '0',
  `typename` char(50) NOT NULL DEFAULT '',
  `typeno` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`typeid`),
  KEY `TypeNo` (`typeno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `tags` (
  `tagref` int NOT NULL AUTO_INCREMENT,
  `tagdescription` varchar(50) NOT NULL,
  PRIMARY KEY (`tagref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `taxauthorities` (
  `taxid` tinyint NOT NULL AUTO_INCREMENT,
  `description` varchar(20) NOT NULL DEFAULT '',
  `taxglcode` varchar(20) NOT NULL DEFAULT '0',
  `purchtaxglaccount` varchar(20) NOT NULL DEFAULT '0',
  `bank` varchar(50) NOT NULL DEFAULT '',
  `bankacctype` varchar(20) NOT NULL DEFAULT '',
  `bankacc` varchar(50) NOT NULL DEFAULT '',
  `bankswift` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`taxid`),
  KEY `TaxGLCode` (`taxglcode`),
  KEY `PurchTaxGLAccount` (`purchtaxglaccount`),
  CONSTRAINT `taxauthorities_ibfk_1` FOREIGN KEY (`taxglcode`) REFERENCES `chartmaster` (`accountcode`),
  CONSTRAINT `taxauthorities_ibfk_2` FOREIGN KEY (`purchtaxglaccount`) REFERENCES `chartmaster` (`accountcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `taxauthrates` (
  `taxauthority` tinyint NOT NULL DEFAULT '1',
  `dispatchtaxprovince` tinyint NOT NULL DEFAULT '1',
  `taxcatid` tinyint NOT NULL DEFAULT '0',
  `taxrate` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`taxauthority`,`dispatchtaxprovince`,`taxcatid`),
  KEY `TaxAuthority` (`taxauthority`),
  KEY `dispatchtaxprovince` (`dispatchtaxprovince`),
  KEY `taxcatid` (`taxcatid`),
  CONSTRAINT `taxauthrates_ibfk_1` FOREIGN KEY (`taxauthority`) REFERENCES `taxauthorities` (`taxid`),
  CONSTRAINT `taxauthrates_ibfk_2` FOREIGN KEY (`taxcatid`) REFERENCES `taxcategories` (`taxcatid`),
  CONSTRAINT `taxauthrates_ibfk_3` FOREIGN KEY (`dispatchtaxprovince`) REFERENCES `taxprovinces` (`taxprovinceid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `taxcategories` (
  `taxcatid` tinyint NOT NULL AUTO_INCREMENT,
  `taxcatname` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`taxcatid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `taxgroups` (
  `taxgroupid` tinyint NOT NULL AUTO_INCREMENT,
  `taxgroupdescription` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`taxgroupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `taxgrouptaxes` (
  `taxgroupid` tinyint NOT NULL DEFAULT '0',
  `taxauthid` tinyint NOT NULL DEFAULT '0',
  `calculationorder` tinyint NOT NULL DEFAULT '0',
  `taxontax` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`taxgroupid`,`taxauthid`),
  KEY `taxgroupid` (`taxgroupid`),
  KEY `taxauthid` (`taxauthid`),
  CONSTRAINT `taxgrouptaxes_ibfk_1` FOREIGN KEY (`taxgroupid`) REFERENCES `taxgroups` (`taxgroupid`),
  CONSTRAINT `taxgrouptaxes_ibfk_2` FOREIGN KEY (`taxauthid`) REFERENCES `taxauthorities` (`taxid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `taxprovinces` (
  `taxprovinceid` tinyint NOT NULL AUTO_INCREMENT,
  `taxprovincename` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`taxprovinceid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `tenderitems` (
  `tenderid` int NOT NULL DEFAULT '0',
  `stockid` varchar(64) NOT NULL DEFAULT '',
  `quantity` varchar(40) NOT NULL DEFAULT '',
  `units` varchar(20) NOT NULL DEFAULT 'each',
  PRIMARY KEY (`tenderid`,`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `tenders` (
  `tenderid` int NOT NULL DEFAULT '0',
  `location` varchar(5) NOT NULL DEFAULT '',
  `address1` varchar(40) NOT NULL DEFAULT '',
  `address2` varchar(40) NOT NULL DEFAULT '',
  `address3` varchar(40) NOT NULL DEFAULT '',
  `address4` varchar(40) NOT NULL DEFAULT '',
  `address5` varchar(20) NOT NULL DEFAULT '',
  `address6` varchar(15) NOT NULL DEFAULT '',
  `telephone` varchar(25) NOT NULL DEFAULT '',
  `closed` int NOT NULL DEFAULT '0',
  `requiredbydate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  PRIMARY KEY (`tenderid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `tendersuppliers` (
  `tenderid` int NOT NULL DEFAULT '0',
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `email` varchar(40) NOT NULL DEFAULT '',
  `responded` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`tenderid`,`supplierid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `timesheets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `wo` int NOT NULL COMMENT 'loose FK with workorders',
  `employeeid` int NOT NULL,
  `weekending` date NOT NULL DEFAULT '1900-01-01',
  `workcentre` varchar(5) NOT NULL COMMENT 'loose FK with workcentres',
  `day1` double NOT NULL DEFAULT '0',
  `day2` double NOT NULL DEFAULT '0',
  `day3` double NOT NULL DEFAULT '0',
  `day4` double NOT NULL DEFAULT '0',
  `day5` double NOT NULL DEFAULT '0',
  `day6` double NOT NULL DEFAULT '0',
  `day7` double NOT NULL DEFAULT '0',
  `status` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `workcentre` (`workcentre`),
  KEY `employees` (`employeeid`),
  KEY `wo` (`wo`),
  KEY `weekending` (`weekending`),
  CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`employeeid`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `unitsofmeasure` (
  `unitid` tinyint NOT NULL AUTO_INCREMENT,
  `unitname` varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`unitid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `woitems` (
  `wo` int NOT NULL,
  `stockid` varchar(64) NOT NULL DEFAULT '',
  `qtyreqd` double NOT NULL DEFAULT '1',
  `qtyrecd` double NOT NULL DEFAULT '0',
  `stdcost` double NOT NULL,
  `nextlotsnref` varchar(20) DEFAULT '',
  `comments` longblob DEFAULT NULL,
  PRIMARY KEY (`wo`,`stockid`),
  KEY `stockid` (`stockid`),
  CONSTRAINT `woitems_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `woitems_ibfk_2` FOREIGN KEY (`wo`) REFERENCES `workorders` (`wo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `worequirements` (
  `wo` int NOT NULL,
  `parentstockid` varchar(64) NOT NULL,
  `stockid` varchar(64) NOT NULL,
  `qtypu` double NOT NULL DEFAULT '1',
  `stdcost` double NOT NULL DEFAULT '0',
  `autoissue` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`wo`,`parentstockid`,`stockid`),
  KEY `stockid` (`stockid`),
  KEY `worequirements_ibfk_3` (`parentstockid`),
  CONSTRAINT `worequirements_ibfk_1` FOREIGN KEY (`wo`) REFERENCES `workorders` (`wo`),
  CONSTRAINT `worequirements_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `worequirements_ibfk_3` FOREIGN KEY (`wo`, `parentstockid`) REFERENCES `woitems` (`wo`, `stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `workcentres` (
  `code` char(5) NOT NULL DEFAULT '',
  `location` char(5) NOT NULL DEFAULT '',
  `description` char(20) NOT NULL DEFAULT '',
  `capacity` double NOT NULL DEFAULT '1',
  `overheadperhour` decimal(10,0) NOT NULL DEFAULT '0',
  `overheadrecoveryact` varchar(20) NOT NULL DEFAULT '0',
  `setuphrs` decimal(10,0) NOT NULL DEFAULT '0',
  PRIMARY KEY (`code`),
  KEY `Description` (`description`),
  KEY `Location` (`location`),
  CONSTRAINT `workcentres_ibfk_1` FOREIGN KEY (`location`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `workorders` (
  `wo` int NOT NULL,
  `loccode` char(5) NOT NULL DEFAULT '',
  `requiredby` date NOT NULL DEFAULT '1000-01-01',
  `startdate` date NOT NULL DEFAULT '1000-01-01',
  `costissued` double NOT NULL DEFAULT '0',
  `closed` tinyint NOT NULL DEFAULT '0',
  `closecomments` longblob DEFAULT NULL,
  `reference` varchar(40) NOT NULL DEFAULT '',
  `remark` text DEFAULT NULL,
  PRIMARY KEY (`wo`),
  KEY `LocCode` (`loccode`),
  KEY `StartDate` (`startdate`),
  KEY `RequiredBy` (`requiredby`),
  CONSTRAINT `worksorders_ibfk_1` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `woserialnos` (
  `wo` int NOT NULL,
  `stockid` varchar(64) NOT NULL,
  `serialno` varchar(30) NOT NULL,
  `quantity` double NOT NULL DEFAULT '1',
  `qualitytext` text NOT NULL,
  PRIMARY KEY (`wo`,`stockid`,`serialno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `www_users` (
  `userid` varchar(20) NOT NULL DEFAULT '',
  `password` text NOT NULL,
  `realname` varchar(35) NOT NULL DEFAULT '',
  `customerid` varchar(10) NOT NULL DEFAULT '',
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `salesman` char(3) NOT NULL,
  `phone` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(55) DEFAULT NULL,
  `defaultlocation` varchar(5) NOT NULL DEFAULT '',
  `fullaccess` int NOT NULL DEFAULT '1',
  `cancreatetender` tinyint(1) NOT NULL DEFAULT '0',
  `lastvisitdate` datetime DEFAULT NULL,
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `pagesize` varchar(20) NOT NULL DEFAULT 'A4',
  `timeout` tinyint NOT NULL DEFAULT '5',
  `modulesallowed` varchar(25) NOT NULL,
  `showdashboard` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Display dashboard after login',
  `showpagehelp` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Turn off/on page help',
  `showfieldhelp` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Turn off/on field help',
  `blocked` tinyint NOT NULL DEFAULT '0',
  `displayrecordsmax` int NOT NULL DEFAULT '0',
  `theme` varchar(30) NOT NULL DEFAULT 'fresh',
  `language` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  `pdflanguage` tinyint(1) NOT NULL DEFAULT '0',
  `fontsize` tinyint NOT NULL DEFAULT '1',
  `department` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`userid`),
  KEY `CustomerID` (`customerid`),
  KEY `DefaultLocation` (`defaultlocation`),
  CONSTRAINT `www_users_ibfk_1` FOREIGN KEY (`defaultlocation`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `config` VALUES ('AllowOrderLineItemNarrative','1'),('AllowSalesOfZeroCostItems','0'),('AutoAuthorisePO','1'),('AutoCreateWOs','1'),('AutoDebtorNo','0'),('AutoIssue','1'),('AutoSupplierNo','0'),('CheckCreditLimits','1'),('Check_Price_Charged_vs_Order_Price','1'),('Check_Qty_Charged_vs_Del_Qty','1'),('CountryOfOperation','GB'),('CreditingControlledItems_MustExist','0'),('DB_Maintenance','0'),('DB_Maintenance_LastRun','2015-08-14'),('DefaultBlindPackNote','1'),('DefaultCreditLimit','1000'),('DefaultCustomerType','1'),('DefaultDateFormat','d/m/Y'),('DefaultDisplayRecordsMax','50'),('DefaultFactoryLocation','MEL'),('DefaultPriceList','DE'),('DefaultSupplierType','1'),('DefaultTaxCategory','1'),('Default_Shipper','1'),('DefineControlledOnWOEntry','1'),('DispatchCutOffTime','14'),('DoFreightCalc','0'),('EDIHeaderMsgId','D:01B:UN:EAN010'),('EDIReference','WEBERP'),('EDI_Incoming_Orders','companies/weberpdemo/EDI_Incoming_Orders'),('EDI_MsgPending','companies/weberpdemo/EDI_Pending'),('EDI_MsgSent','companies/weberpdemo/EDI_Sent'),('ExchangeRateFeed','ECB'),('Extended_CustomerInfo','1'),('Extended_SupplierInfo','1'),('FactoryManagerEmail','manager@company.com'),('FreightChargeAppliesIfLessThan','1000'),('FreightTaxCategory','1'),('FrequentlyOrderedItems','0'),('geocode_integration','0'),('GoogleTranslatorAPIKey',''),('HTTPS_Only','0'),('InventoryManagerEmail','test@company.com'),('InvoicePortraitFormat','0'),('InvoiceQuantityDefault','1'),('ItemDescriptionLanguages', ','),('LastDayOfWeek','0'),('LogPath',''),('LogSeverity','4'),('MaxImageSize','300'),('MaxSerialItemsIssued','50'),('MonthsAuditTrail','1'),('NumberOfMonthMustBeShown','6'),('NumberOfPeriodsOfStockUsage','12'),('OverChargeProportion','30'),('OverReceiveProportion','20'),('PackNoteFormat','1'),('PageLength','48'),('PastDueDays1','30'),('PastDueDays2','60'),('PeriodProfitAccount',''),('PO_AllowSameItemMultipleTimes','1'),('ProhibitJournalsToControlAccounts','1'),('ProhibitNegativeStock','0'),('ProhibitPostingsBefore','2024-04-30'),('PurchasingManagerEmail','test@company.com'),('QualityCOAText',''),('QualityLogSamples','0'),('QualityProdSpecText',''),('QuickEntries','10'),('RadioBeaconFileCounter','/home/RadioBeacon/FileCounter'),('RadioBeaconFTP_user_name','RadioBeacon ftp server user name'),('RadioBeaconHomeDir','/home/RadioBeacon'),('RadioBeaconStockLocation','BL'),('RadioBraconFTP_server','192.168.2.2'),('RadioBreaconFilePrefix','ORDXX'),('RadionBeaconFTP_user_pass','Radio Beacon remote ftp server password'),('reports_dir','companies/weberp/reports'),('RequirePickingNote','1'),('RomalpaClause','Ownership will not pass to the buyer until the goods have been paid for in full.'),('ShopAboutUs','This web-shop software has been developed by Logic Works Ltd for webERP. For support contact Phil Daintree by rn&lt;a href=&quot;mailto:support@logicworks.co.nz&quot;&gt;email&lt;/a&gt;rn'),('ShopAllowBankTransfer','1'),('ShopAllowCreditCards','1'),('ShopAllowPayPal','1'),('ShopAllowSurcharges','1'),('ShopBankTransferSurcharge','0.0'),('ShopBranchCode','ANGRY'),('ShopContactUs','For support contact Logic Works Ltd by rn&lt;a href=&quot;mailto:support@logicworks.co.nz&quot;&gt;email&lt;/a&gt;'),('ShopCreditCardBankAccount','1030'),('ShopCreditCardGateway','SwipeHQ'),('ShopCreditCardSurcharge','2.95'),('ShopDebtorNo','ANGRY'),('ShopFreightMethod','NoFreight'),('ShopFreightPolicy','Shipping information'),('ShopManagerEmail','shopmanager@yourdomain.com'),('ShopMode','test'),('ShopName','webERP Demo Store'),('ShopPayFlowMerchant',''),('ShopPayFlowPassword',''),('ShopPayFlowUser',''),('ShopPayFlowVendor',''),('ShopPayPalBankAccount','1040'),('ShopPaypalCommissionAccount','1'),('ShopPayPalPassword',''),('ShopPayPalProPassword',''),('ShopPayPalProSignature',''),('ShopPayPalProUser',''),('ShopPayPalSignature',''),('ShopPayPalSurcharge','3.4'),('ShopPayPalUser',''),('ShopPrivacyStatement','&lt;h2&gt;We are committed to protecting your privacy.&lt;/h2&gt;&lt;p&gt;We recognise that your personal information is confidential and we understand that it is important for you to know how we treat your personal information. Please read on for more information about our Privacy Policy.&lt;/p&gt;&lt;ul&gt;&lt;li&gt;&lt;h2&gt;1. What information do we collect and how do we use it?&lt;/h2&gt;&lt;br /&gt;We use the information it collects from you for the following purposes:&lt;ul&gt;&lt;li&gt;To assist us in providing you with a quality service&lt;/li&gt;&lt;li&gt;To respond to, and process, your request&lt;/li&gt;&lt;li&gt;To notify competition winners or fulfil promotional obligations&lt;/li&gt;&lt;li&gt;To inform you of, and provide you with, new and existing products and services offered by us from time to time &lt;/li&gt;&lt;/ul&gt;&lt;p&gt;Any information we collect will not be used in ways that you have not consented to.&lt;/p&gt;&lt;p&gt;If you send us an email, we will store your email address and the contents of the email. This information will only be used for the purpose for which you have provided it. Electronic mail submitted to us is handled and saved according to the provisions of the the relevant statues.&lt;/p&gt;&lt;p&gt;When we offer contests and promotions, customers who choose to enter are asked to provide personal information. This information may then be used by us to notify winners, or to fulfil promotional obligations.&lt;/p&gt;&lt;p&gt;We may use the information we collect to occasionally notify you about important functionality changes to our website, new and special offers we think you will find valuable. If at any stage you no longer wish to receive these notifications you may opt out by sending us an email.&lt;/p&gt;&lt;p&gt;We do monitor this website in order to identify user trends and to improve the site if necessary. Any of this information, such as the type of site browser your computer has, will be used only in aggregate form and your individual details will not be identified.&lt;/p&gt;&lt;/li&gt;&lt;li&gt;&lt;h2&gt;2. How do we store and protect your personal information and who has access to that information?&lt;/h2&gt;&lt;p&gt;As required by statute, we follow strict procedures when storing and using the information you have provided.&lt;/p&gt;&lt;p&gt;We do not sell, trade or rent your personal information to others. We may provide aggregate statistics about our customers and website trends. However, these statistics will not have any personal information which would identify you.&lt;/p&gt;&lt;p&gt;Only specific employees within our company are able to access your personal data.&lt;/p&gt;&lt;p&gt;This policy means that we may require proof of identity before we disclose any information to you.&lt;/p&gt;&lt;/li&gt;&lt;li&gt;&lt;h2&gt;3. What should I do if I want to change my details or if I donÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢t want to be contacted any more?&lt;/h2&gt;&lt;p&gt;At any stage you have the right to access and amend or update your personal details. If you do not want to receive any communications from us you may opt out by contacting us see &lt;a href=&quot;index.php?Page=ContactUs&quot;&gt;the Contact Us Page&lt;/a&gt;&lt;/p&gt;&lt;/li&gt;&lt;li&gt;&lt;h2&gt;4. What happens if we decide to change this Privacy Policy?&lt;/h2&gt;&lt;p&gt;If we change any aspect of our Privacy Policy we will post these changes on this page so that you are always aware of how we are treating your personal information.&lt;/p&gt;&lt;/li&gt;&lt;li&gt;&lt;h2&gt;5. How can you contact us if you have any questions, comments or concerns about our Privacy Policy?&lt;/h2&gt;&lt;p&gt;We welcome any questions or comments you may have please email us via the contact details provided on our &lt;a href=&quot;index.php?Page=ContactUs&quot;&gt;Contact Us Page&lt;/a&gt;&lt;/p&gt;&lt;/li&gt;&lt;/ul&gt;&lt;p&gt;Please also refer to our &lt;a href=&quot;index.php?Page=TermsAndConditions&quot;&gt;Terms and Conditions&lt;/a&gt; for more information.&lt;/p&gt;'),('ShopShowOnlyAvailableItems','0'),('ShopShowQOHColumn','1'),('ShopStockLocations','MEL,TOR'),('ShopSurchargeStockID',''),('ShopSwipeHQAPIKey',''),('ShopSwipeHQMerchantID',''),('ShopTermsConditions','&lt;p&gt;These terms cover the use of this website. Use includes visits to our sites, purchases on our sites, participation in our database and promotions. These terms of use apply to you when you use our websites. Please read these terms carefully - if you need to refer to them again they can be accessed from the link at the bottom of any page of our websites.&lt;/p&gt;&lt;br /&gt;&lt;ul&gt;&lt;li&gt;&lt;h2&gt;1. Content&lt;/h2&gt;&lt;p&gt;While we endeavour to supply accurate information on this site, errors and omissions may occur. We do not accept any liability, direct or indirect, for any loss or damage which may directly or indirectly result from any advice, opinion, information, representation or omission whether negligent or otherwise, contained on this site. You are solely responsible for the actions you take in reliance on the content on, or accessed, through this site.&lt;/p&gt;&lt;p&gt;We reserve the right to make changes to the content on this site at any time and without notice.&lt;/p&gt;&lt;p&gt;To the extent permitted by law, we make no warranties in relation to the merchantability, fitness for purpose, freedom from computer virus, accuracy or availability of this web site or any other web site.&lt;/p&gt;&lt;/li&gt;&lt;li&gt;&lt;h2&gt;2. Making a contract with us&lt;/h2&gt;&lt;p&gt;When you place an order with us, you are making an offer to buy goods. We will send you an e-mail to confirm that we have received and accepted your order, which indicates that a contract has been made between us. We will take payment from you when we accept your order. In the unlikely event that the goods are no longer available, we will refund your payment to the account it originated from, and advise that the goods are no longer available.&lt;/p&gt;&lt;p&gt;An order is placed on our website via adding a product to the shopping cart and proceeding through our checkout process. The checkout process includes giving us delivery and any other relevant details for your order, entering payment information and submitting your order. The final step consists of a confirmation page with full details of your order, which you are able to print as a receipt of your order. We will also email you with confirmation of your order.&lt;/p&gt;&lt;p&gt;We reserve the right to refuse or cancel any orders that we believe, solely by our own judgement, to be placed for commercial purposes, e.g. any kind of reseller. We also reserve the right to refuse or cancel any orders that we believe, solely by our own judgement, to have been placed fraudulently.&lt;/p&gt;&lt;p&gt;We reserve the right to limit the number of an item customers can purchase in a single transaction.&lt;/p&gt;&lt;/li&gt;&lt;li&gt;&lt;h2&gt;3. Payment options&lt;/h2&gt;&lt;p&gt;We currently accept the following credit cards:&lt;/p&gt;&lt;ul&gt;&lt;li&gt;Visa&lt;/li&gt;&lt;li&gt;MasterCard&lt;/li&gt;&lt;li&gt;American Express&lt;/li&gt;&lt;/ul&gt;You can also pay using PayPal and internet bank transfer. Surcharges may apply for payment by PayPal or credit cards.&lt;/p&gt;&lt;/li&gt;&lt;li&gt;&lt;h2&gt;4. Pricing&lt;/h2&gt;&lt;p&gt;All prices listed are inclusive of relevant taxes.  All prices are correct when published. Please note that we reserve the right to alter prices at any time for any reason. If this should happen after you have ordered a product, we will contact you prior to processing your order. Online and in store pricing may differ.&lt;/p&gt;&lt;/li&gt;&lt;li&gt;&lt;h2&gt;5. Website and Credit Card Security&lt;/h2&gt;&lt;p&gt;We want you to have a safe and secure shopping experience online. All payments via our sites are processed using SSL (Secure Socket Layer) protocol, whereby sensitive information is encrypted to protect your privacy.&lt;/p&gt;&lt;p&gt;You can help to protect your details from unauthorised access by logging out each time you finish using the site, particularly if you are doing so from a public or shared computer.&lt;/p&gt;&lt;p&gt;For security purposes certain transactions may require proof of identification.&lt;/p&gt;&lt;/li&gt;&lt;li&gt;&lt;h2&gt;6. Delivery and Delivery Charges&lt;/h2&gt;&lt;p&gt;We do not deliver to Post Office boxes.&lt;/p&gt;&lt;p&gt;Please note that a signature is required for all deliveries. The goods become the recipientÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢s property and responsibility once they have been signed for at the time of delivery. If goods are lost or damaged in transit, please contact us within 7 business days &lt;a href=&quot;index.php?Page=ContactUs&quot;&gt;see Contact Us page for contact details&lt;/a&gt;. We will use this delivery information to make a claim against our courier company. We will offer you the choice of a replacement or a full refund, once we have received confirmation from our courier company that delivery was not successful.&lt;/p&gt;&lt;/li&gt;&lt;li&gt;&lt;h2&gt;7. Restricted Products&lt;/h2&gt;&lt;p&gt;Some products on our site carry an age restriction, if a product you have selected is R16 or R18 a message will appear in the cart asking you to confirm you are an appropriate age to purchase the item(s).  Confirming this means that you are of an eligible age to purchase the selected product(s).  You are also agreeing that you are not purchasing the item on behalf of a person who is not the appropriate age.&lt;/p&gt;&lt;/li&gt;&lt;li&gt;&lt;h2&gt;8. Delivery Period&lt;/h2&gt;&lt;p&gt;Delivery lead time for products may vary. Deliveries to rural addresses may take longer.  You will receive an email that confirms that your order has been dispatched.&lt;/p&gt;&lt;p&gt;To ensure successful delivery, please provide a delivery address where someone will be present during business hours to sign for the receipt of your package. You can track your order by entering the tracking number emailed to you in the dispatch email at the Courier&#039;s web-site.&lt;/p&gt;&lt;/li&gt;&lt;li&gt;&lt;h2&gt;9. Disclaimer&lt;/h2&gt;&lt;p&gt;Our websites are intended to provide information for people shopping our products and accessing our services, including making purchases via our website and registering on our database to receive e-mails from us.&lt;/p&gt;&lt;p&gt;While we endeavour to supply accurate information on this site, errors and omissions may occur. We do not accept any liability, direct or indirect, for any loss or damage which may directly or indirectly result from any advice, opinion, information, representation or omission whether negligent or otherwise, contained on this site. You are solely responsible for the actions you take in reliance on the content on, or accessed, through this site.&lt;/p&gt;&lt;p&gt;We reserve the right to make changes to the content on this site at any time and without notice.&lt;/p&gt;&lt;p&gt;To the extent permitted by law, we make no warranties in relation to the merchantability, fitness for purpose, freedom from computer virus, accuracy or availability of this web site or any other web site.&lt;/p&gt;&lt;/li&gt;&lt;li&gt;&lt;h2&gt;10. Links&lt;/h2&gt;&lt;p&gt;Please note that although this site has some hyperlinks to other third party websites, these sites have not been prepared by us are not under our control. The links are only provided as a convenience, and do not imply that we endorse, check, or approve of the third party site. We are not responsible for the privacy principles or content of these third party sites. We are not responsible for the availability of any of these links.&lt;/p&gt;&lt;/li&gt;&lt;li&gt;&lt;h2&gt;11. Jurisdiction&lt;/h2&gt;&lt;p&gt;This website is governed by, and is to be interpreted in accordance with, the laws of  ????.&lt;/p&gt;&lt;/li&gt;&lt;li&gt;&lt;h2&gt;12. Changes to this Agreement&lt;/h2&gt;&lt;p&gt;We reserve the right to alter, modify or update these terms of use. These terms apply to your order. We may change our terms and conditions at any time, so please do not assume that the same terms will apply to future orders.&lt;/p&gt;&lt;/li&gt;&lt;/ul&gt;'),('ShopTitle','Shop Home'),('ShortcutMenu','1'),('ShowStockidOnImages','0'),('ShowValueOnGRN','1'),('Show_Settled_LastMonth','1'),('SmtpSetting','0'),('SO_AllowSameItemMultipleTimes','1'),('StandardCostDecimalPlaces','2'),('StockUsageShowZeroWithinPeriodRange','0'),('TaxAuthorityReferenceName',''),('UpdateCurrencyRatesDaily','2025-03-09'),('VersionNumber','5.0.0'),('WeightedAverageCosting','0'),('WikiApp','DokuWiki'),('WikiPath','wiki'),('WorkingDaysWeek','5'),('YearEnd','12');

INSERT INTO `currencies` VALUES ('Pounds','GBP','England','Pence',2,1,0);


INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (1,'AP','Maintenance','Add Supplier','/Suppliers.php',1),(1,'AP','Maintenance','Maintain Factor Companies','/Factors.php',3),(1,'AP','Maintenance','Select Supplier','/SelectSupplier.php',2),(1,'AP','Reports','Aged Supplier Report','/AgedSuppliers.php',2),(1,'AP','Reports','List Daily Transactions','/PDFSuppTransListing.php',7),(1,'AP','Reports','Outstanding GRNs Report','/OutstandingGRNs.php',5),(1,'AP','Reports','Payment Run Report','/SuppPaymentRun.php',3),(1,'AP','Reports','Remittance Advices','/PDFRemittanceAdvice.php',4),(1,'AP','Reports','Supplier Balances At A Prior Month End','/SupplierBalsAtPeriodEnd.php',6),(1,'AP','Reports','Supplier Transaction Inquiries','/SupplierTransInquiry.php',8),(1,'AP','Reports','Where Allocated Inquiry','/SuppWhereAlloc.php',1),(1,'AP','Transactions','Select Supplier','/SelectSupplier.php',1),(1,'AP','Transactions','Supplier Allocations','/SupplierAllocations.php',2),(1,'AR','Maintenance','Add Customer','/Customers.php',1),(1,'AR','Maintenance','Select Customer','/SelectCustomer.php',2),(1,'AR','Reports','Aged Customer Balances/Overdues Report','/AgedDebtors.php',4),(1,'AR','Reports','Customer Activity and Balances','/CustomerBalancesMovement.php',10),(1,'AR','Reports','Customer Listing By Area/Salesperson','/PDFCustomerList.php',7),(1,'AR','Reports','Customer Transaction Inquiries','/CustomerTransInquiry.php',9),(1,'AR','Reports','Debtor Balances At A Prior Month End','/DebtorsAtPeriodEnd.php',6),(1,'AR','Reports','List Daily Transactions','/PDFCustTransListing.php',8),(1,'AR','Reports','Print Invoices or Credit Notes','/PrintCustTrans.php',2),(1,'AR','Reports','Print Statements','/PrintCustStatements.php',3),(1,'AR','Reports','Re-Print A Deposit Listing','/PDFBankingSummary.php',5),(1,'AR','Reports','Where Allocated Inquiry','/CustWhereAlloc.php',1),(1,'AR','Transactions','Allocate Receipts or Credit Notes','/CustomerAllocations.php',4),(1,'AR','Transactions','Create A Credit Note','/SelectCreditItems.php?NewCredit=Yes',2),(1,'AR','Transactions','Enter Receipts','/CustomerReceipt.php?NewReceipt=Yes&amp;Type=Customer',3),(1,'AR','Transactions','Select Order to Invoice','/SelectSalesOrder.php',1),(1,'FA','Maintenance','Add or Maintain Asset Locations','/FixedAssetLocations.php',2),(1,'FA','Maintenance','Fixed Asset Category Maintenance','/FixedAssetCategories.php',1),(1,'FA','Maintenance','Fixed Asset Maintenance Tasks','/MaintenanceTasks.php',3),(1,'FA','Reports','Asset Register','/FixedAssetRegister.php',1),(1,'FA','Reports','Maintenance Reminder Emails','/MaintenanceReminders.php',3),(1,'FA','Reports','My Maintenance Schedule','/MaintenanceUserSchedule.php',2),(1,'FA','Transactions','Add a new Asset','/FixedAssetItems.php',1),(1,'FA','Transactions','Change Asset Location','/FixedAssetTransfer.php',3),(1,'FA','Transactions','Depreciation Journal','/FixedAssetDepreciation.php',4),(1,'FA','Transactions','Select an Asset','/SelectAsset.php',2),(1,'GL','Maintenance','Account Groups','/AccountGroups.php',4),(1,'GL','Maintenance','Account Sections','/AccountSections.php',1),(1,'GL','Maintenance','Bank Account Authorised Users','/BankAccountUsers.php',11),(1,'GL','Maintenance','Bank Accounts','/BankAccounts.php',10),(1,'GL','Maintenance','Copy Authority Bank Accounts from one user to another','/GLBankAccountUsersCopyAuthority.php',21),(1,'GL','Maintenance','Copy Authority GL Accounts from one user to another','/GLAccountUsersCopyAuthority.php',20),(1,'GL','Maintenance','Create Budget Amounts','/GLBudgets.php',3),(1,'GL','Maintenance','Create/Amend General Ledger Budgets','/GLBudgetHeaders.php',2),(1,'GL','Maintenance','GL Account','/GLAccounts.php',5),(1,'GL','Maintenance','GL Account Authorised Users','/GLAccountUsers.php',6),(1,'GL','Maintenance','GL Tags','/GLTags.php',9),(1,'GL','Maintenance','Maintain Journal Templates','/GLJournalTemplates.php',13),(1,'GL','Maintenance','Setup Regular Payments','/RegularPaymentsSetup.php',14),(1,'GL','Maintenance','User Authorised Bank Accounts','/UserBankAccounts.php',12),(1,'GL','Maintenance','User Authorised GL Accounts','/UserGLAccounts.php',7),(1,'GL','Reports','Account Inquiry','/SelectGLAccount.php',5),(1,'GL','Reports','Account Listing','/GLAccountReport.php',7),(1,'GL','Reports','Account Listing to CSV File','/GLAccountCSV.php',8),(1,'GL','Reports','Balance Sheet','/GLBalanceSheet.php',11),(1,'GL','Reports','Bank Account Balances','/BankAccountBalances.php',1),(1,'GL','Reports','Bank Account Reconciliation Statement','/BankReconciliation.php',2),(1,'GL','Reports','Cheque Payments Listing','/PDFChequeListing.php',3),(1,'GL','Reports','Daily Bank Transactions','/DailyBankTransactions.php',4),(1,'GL','Reports','Financial Statements','/GLStatements.php',14),(1,'GL','Reports','General Ledger Journal Inquiry','/GLJournalInquiry.php',9),(1,'GL','Reports','Graph of Account Transactions','/GLAccountGraph.php',6),(1,'GL','Reports','Horizontal Analysis of Statement of Comprehensive Income','/AnalysisHorizontalIncome.php',16),(1,'GL','Reports','Horizontal Analysis of Statement of Financial Position','/AnalysisHorizontalPosition.php',15),(1,'GL','Reports','Income and Expenditure by Tag','/GLTagProfit_Loss.php',17),(1,'GL','Reports','Profit and Loss Statement','/GLProfit_Loss.php',12),(1,'GL','Reports','Statement of Cash Flows','/GLCashFlowsIndirect.php',13),(1,'GL','Reports','Tax Reports','/Tax.php',19),(1,'GL','Reports','Trial Balance','/GLTrialBalance.php',10),(1,'GL','Transactions','Bank Account Payments Entry','/Payments.php?NewPayment=Yes',1),(1,'GL','Transactions','Bank Account Payments Matching','/BankMatching.php?Type=Payments',4),(1,'GL','Transactions','Bank Account Receipts Entry','/CustomerReceipt.php?NewReceipt=Yes&amp;Type=GL',2),(1,'GL','Transactions','Bank Account Receipts Matching','/BankMatching.php?Type=Receipts',5),(1,'GL','Transactions','Journal Entry','/GLJournal.php?NewJournal=Yes',3),(1,'GL','Transactions','Import Bank Transactions','/ImportBankTrans.php',6),(1,'GL','Transactions','Process Regular Payments','/RegularPaymentsProcess.php',7),(1,'manuf','Maintenance','Auto Create Master Schedule','/MRPCreateDemands.php',5),(1,'manuf','Maintenance','Bills Of Material','/BOMs.php',2),(1,'manuf','Maintenance','Copy a Bill Of Materials Between Items','/CopyBOM.php',3),(1,'manuf','Maintenance','Employees','/Employees.php',9),(1,'manuf','Maintenance','Master Schedule','/MRPDemands.php',4),(1,'manuf','Maintenance','MRP Calculation','/MRP.php',6),(1,'manuf','Maintenance','Product Specifications','/ProductSpecs.php',8),(1,'manuf','Maintenance','Quality Tests Maintenance','/QATests.php',7),(1,'manuf','Maintenance','Work Centre','/WorkCentres.php',1),(1,'manuf','Reports','Bill Of Material Listing','/BOMListing.php',4),(1,'manuf','Reports','Costed Bill Of Material Inquiry','/BOMInquiry.php',2),(1,'manuf','Reports','Historical QA Test Results','/HistoricalTestResults.php',17),(1,'manuf','Reports','Indented Bill Of Material Listing','/BOMIndented.php',5),(1,'manuf','Reports','Indented Where Used Listing','/BOMIndentedReverse.php',9),(1,'manuf','Reports','List Components Required','/BOMExtendedQty.php',6),(1,'manuf','Reports','List Materials Not Used anywhere','/MaterialsNotUsed.php',7),(1,'manuf','Reports','MRP','/MRPReport.php',10),(1,'manuf','Reports','MRP Reschedules Required','/MRPReschedules.php',14),(1,'manuf','Reports','MRP Shortages','/MRPShortages.php',11),(1,'manuf','Reports','MRP Suggested Purchase Orders','/MRPPlannedPurchaseOrders.php',12),(1,'manuf','Reports','MRP Suggested Work Orders','/MRPPlannedWorkOrders.php',13),(1,'manuf','Reports','Multiple Work Orders Total Cost Inquiry','/CollectiveWorkOrderCost.php',18),(1,'manuf','Reports','Print Certificate of Analysis','/PDFCOA.php',16),(1,'manuf','Reports','Print Product Specification','/PDFProdSpec.php',15),(1,'manuf','Reports','Select A Work Order','/SelectWorkOrder.php',1),(1,'manuf','Reports','Where Used Inquiry','/WhereUsedInquiry.php',3),(1,'manuf','Reports','WO Items ready to produce','/WOCanBeProducedNow.php',8),(1,'manuf','Transactions','QA Samples and Test Results','/SelectQASamples.php',3),(1,'manuf','Transactions','Select A Work Order','/SelectWorkOrder.php',2),(1,'manuf','Transactions','Timesheet Entry','/Timesheets.php',4),(1,'manuf','Transactions','Work Order Entry','/WorkOrderEntry.php?New=True',1),(1,'PC','Maintenance','Expenses for Type of PC Tab','/PcExpensesTypeTab.php',4),(1,'PC','Maintenance','PC Expenses','/PcExpenses.php',3),(1,'PC','Maintenance','PC Tabs','/PcTabs.php',2),(1,'PC','Maintenance','Types of PC Tabs','/PcTypeTabs.php',1),(1,'PC','Reports','PC Expense General Report','/PcReportExpense.php',2),(1,'PC','Reports','PC Expenses Analysis','/PcAnalysis.php',4),(1,'PC','Reports','PC Tab Expenses List','/PcTabExpensesList.php',3),(1,'PC','Reports','PC Tab General Report','/PcReportTab.php',1),(1,'PC','Transactions','Assign Cash to PC Tab','/PcAssignCashToTab.php',1),(1,'PC','Transactions','Authorise Assigned Cash','/PcAuthorizeCash.php',5),(1,'PC','Transactions','Authorise Expenses','/PcAuthorizeExpenses.php',4),(1,'PC','Transactions','Claim Expenses From PC Tab','/PcClaimExpensesFromTab.php',3),(1,'PC','Transactions','Transfer Assigned Cash Between PC Tabs','/PcAssignCashTabToTab.php',2),(1,'PO','Maintenance','Maintain Supplier Price Lists','/SupplierPriceList.php',1),(1,'PO','Reports','Purchase Order Detail Or Summary Inquiries','/POReport.php',2),(1,'PO','Reports','Purchase Order Inquiry','/PO_SelectPurchOrder.php',1),(1,'PO','Reports','Purchase Orders Financial Planning','/POFinancialPlanning.php',20),(1,'PO','Reports','Purchases from Suppliers','/PurchasesReport.php',4),(1,'PO','Reports','Supplier Price List','/SuppPriceList.php',3),(1,'PO','Transactions','Create a New Tender','/SupplierTenderCreate.php?New=Yes',4),(1,'PO','Transactions','Edit Existing Tenders','/SupplierTenderCreate.php?Edit=Yes',5),(1,'PO','Transactions','New Purchase Order','/PO_Header.php?NewOrder=Yes',1),(1,'PO','Transactions','Orders to Authorise','/PO_AuthoriseMyOrders.php',7),(1,'PO','Transactions','Process Tenders and Offers','/OffersReceived.php',6),(1,'PO','Transactions','Purchase Order Grid Entry','/PurchaseByPrefSupplier.php',3),(1,'PO','Transactions','Purchase Orders','/PO_SelectOSPurchOrder.php',2),(1,'PO','Transactions','Select A Shipment','/Shipt_Select.php',9),(1,'PO','Transactions','Shipment Entry','/SelectSupplier.php',8),(1,'Sales','Maintenance','Create Contract','/Contracts.php',1),(1,'Sales','Maintenance','Select Contract','/SelectContract.php',2),(1,'Sales','Maintenance','Sell Through Support Deals','/SellThroughSupport.php',3),(1,'Sales','Reports','Daily Sales Inquiry','/DailySalesInquiry.php',5),(1,'Sales','Reports','Delivery In Full On Time (DIFOT) Report','/PDFDIFOT.php',13),(1,'Sales','Reports','Order Delivery Differences Report','/PDFDeliveryDifferences.php',12),(1,'Sales','Reports','Order Status Report','/PDFOrderStatus.php',3),(1,'Sales','Reports','Orders Invoiced Reports','/PDFOrdersInvoiced.php',4),(1,'Sales','Reports','Print Price Lists','/PDFPriceList.php',2),(1,'Sales','Reports','Sales Analysis Reports','/SalesAnalRepts.php',9),(1,'Sales','Reports','Sales By Category By Item Inquiry','/StockCategorySalesInquiry.php',8),(1,'Sales','Reports','Sales By Category Inquiry','/SalesCategoryPeriodInquiry.php',7),(1,'Sales','Reports','Sales By Sales Type Inquiry','/SalesByTypePeriodInquiry.php',6),(1,'Sales','Reports','Sales Commission Reports','/SalesCommissionReports.php',21),(1,'Sales','Reports','Sales Graphs','/SalesGraph.php',10),(1,'Sales','Reports','Sales Order Detail Or Summary Inquiries','/SalesInquiry.php',14),(1,'Sales','Reports','Sales Order Inquiry','/SelectCompletedOrder.php',1),(1,'Sales','Reports','Sales to Customers','/SalesReport.php',20),(1,'Sales','Reports','Sales With Low Gross Profit Report','/PDFLowGP.php',18),(1,'Sales','Reports','Sell Through Support Claims Report','/PDFSellThroughSupportClaim.php',19),(1,'Sales','Reports','Top Customers Inquiry','/SalesTopCustomersInquiry.php',16),(1,'Sales','Reports','Top Sales Items Report','/TopItems.php',15),(1,'Sales','Reports','Top Sellers Inquiry','/SalesTopItemsInquiry.php',11),(1,'Sales','Reports','Worst Sales Items Report','/NoSalesItems.php',17),(1,'Sales','Transactions','Enter Counter Returns','/CounterReturns.php',3),(1,'Sales','Transactions','Enter Counter Sales','/CounterSales.php',2),(1,'Sales','Transactions','Generate/Print Picking Lists','/PDFPickingList.php',4),(1,'Sales','Transactions','Maintain Picking Lists','/SelectPickingLists.php',9),(1,'Sales','Transactions','New Sales Order or Quotation','/SelectOrderItems.php?NewOrder=Yes',1),(1,'Sales','Transactions','Outstanding Sales Orders/Quotations','/SelectSalesOrder.php',5),(1,'Sales','Transactions','Process Recurring Orders','/RecurringSalesOrdersProcess.php',8),(1,'Sales','Transactions','Recurring Order Template','/SelectRecurringSalesOrder.php',7),(1,'Sales','Transactions','Special Order','/SpecialOrder.php',6),(1,'stock','Maintenance','Add A New Item','/Stocks.php',1),(1,'stock','Maintenance','Add or Update Prices Based On Costs','/PricesBasedOnMarkUp.php',6),(1,'stock','Maintenance','Brands Maintenance','/Manufacturers.php',5),(1,'stock','Maintenance','Reorder Level By Category/Location','/ReorderLevelLocation.php',9),(1,'stock','Maintenance','Review Translated Descriptions','/RevisionTranslations.php',3),(1,'stock','Maintenance','Sales Category Maintenance','/SalesCategories.php',4),(1,'stock','Maintenance','Select An Item','/SelectProduct.php',2),(1,'stock','Maintenance','Upload new prices from csv file','/UploadPriceList.php',8),(1,'stock','Maintenance','View or Update Prices Based On Costs','/PricesByCost.php',7),(1,'stock','Reports','Aged Controlled Stock Report','/AgedControlledInventory.php',23),(1,'stock','Reports','All Inventory Movements By Location/Date','/StockLocMovements.php',17),(1,'stock','Reports','Compare Counts Vs Stock Check Data','/PDFStockCheckComparison.php',16),(1,'stock','Reports','Historical Stock Quantity By Location/Category','/StockQuantityByDate.php',19),(1,'stock','Reports','Internal stock request inquiry','/InternalStockRequestInquiry.php',24),(1,'stock','Reports','Inventory Item Movements','/StockMovements.php',4),(1,'stock','Reports','Inventory Item Status','/StockStatus.php',5),(1,'stock','Reports','Inventory Item Usage','/StockUsage.php',6),(1,'stock','Reports','Inventory Planning Based On Preferred Supplier Data','/InventoryPlanningPrefSupplier.php',13),(1,'stock','Reports','Inventory Planning Report','/InventoryPlanning.php',12),(1,'stock','Reports','Inventory Quantities','/InventoryQuantities.php',7),(1,'stock','Reports','Inventory Stock Check Sheets','/StockCheck.php',14),(1,'stock','Reports','Inventory Valuation Report','/InventoryValuation.php',10),(1,'stock','Reports','List Inventory Status By Location/Category','/StockLocStatus.php',18),(1,'stock','Reports','List Negative Stocks','/PDFStockNegatives.php',20),(1,'stock','Reports','Mail Inventory Valuation Report','/MailInventoryValuation.php',11),(1,'stock','Reports','Make Inventory Quantities CSV','/StockQties_csv.php',15),(1,'stock','Reports','Period Stock Transaction Listing','/PDFPeriodStockTransListing.php',21),(1,'stock','Reports','Print Price Labels','/PDFPrintLabel.php',2),(1,'stock','Reports','Reorder Level','/ReorderLevel.php',8),(1,'stock','Reports','Reprint GRN','/ReprintGRN.php',3),(1,'stock','Reports','Serial Item Research Tool','/StockSerialItemResearch.php',1),(1,'stock','Reports','Stock Dispatch','/StockDispatch.php',9),(1,'stock','Reports','Stock Transfer Note','/PDFStockTransfer.php',22),(1,'stock','Transactions','Authorise Internal Stock Requests','/InternalStockRequestAuthorisation.php',9),(1,'stock','Transactions','Bulk Inventory Transfer - Dispatch','/StockLocTransfer.php',3),(1,'stock','Transactions','Bulk Inventory Transfer - Receive','/StockLocTransferReceive.php',4),(1,'stock','Transactions','Create a New Internal Stock Request','/InternalStockRequest.php?New=Yes',8),(1,'stock','Transactions','Enter Stock Counts','/StockCounts.php',7),(1,'stock','Transactions','Fulfil Internal Stock Requests','/InternalStockRequestFulfill.php',10),(1,'stock','Transactions','Inventory Adjustments','/StockAdjustments.php?NewAdjustment=Yes',5),(1,'stock','Transactions','Inventory Location Transfers','/StockTransfers.php?New=Yes',2),(1,'stock','Transactions','Receive Purchase Orders','/PO_SelectOSPurchOrder.php',1),(1,'stock','Transactions','Reverse Goods Received','/ReverseGRN.php',6),(1,'system','Maintenance','Copy Authority Locations from one user to another','/LocationUsersCopyAuthority.php',20),(1,'system','Maintenance','Dashboard Configuration','/DashboardConfig.php',13),(1,'system','Maintenance','Discount Category Maintenance','/DiscountCategories.php',5),(1,'system','Maintenance','Inventory Categories Maintenance','/StockCategories.php',1),(1,'system','Maintenance','Inventory Location Authorised Users Maintenance','/LocationUsers.php',3),(1,'system','Maintenance','Inventory Locations Maintenance','/Locations.php',2),(1,'system','Maintenance','Label Templates Maintenance','/Labels.php',12),(1,'system','Maintenance','Logged in users','/LoggedInUsers.php',8),(1,'system','Maintenance','Maintain Internal Departments','/Departments.php',10),(1,'system','Maintenance','Maintain Internal Stock Categories to User Roles','/InternalStockCategoriesByRole.php',11),(1,'system','Maintenance','MRP Available Production Days','/MRPCalendar.php',7),(1,'system','Maintenance','MRP Demand Types','/MRPDemandTypes.php',9),(1,'system','Maintenance','Units of Measure','/UnitsOfMeasure.php',6),(1,'system','Maintenance','User Authorised Inventory Locations Maintenance','/UserLocations.php',4),(1,'system','Reports','COGS GL Interface Postings','/COGSGLPostings.php',12),(1,'system','Reports','Credit Status','/CreditStatus.php',4),(1,'system','Reports','Customer Types','/CustomerTypes.php',2),(1,'system','Reports','Discount Matrix','/DiscountMatrix.php',14),(1,'system','Reports','Freight Costs Maintenance','/FreightCosts.php',13),(1,'system','Reports','Payment Methods','/PaymentMethods.php',7),(1,'system','Reports','Payment Terms','/PaymentTerms.php',5),(1,'system','Reports','Sales Areas','/Areas.php',9),(1,'system','Reports','Sales Commission Types','/SalesCommissionTypes.php',15),(1,'system','Reports','Sales GL Interface Postings','/SalesGLPostings.php',11),(1,'system','Reports','Sales People','/SalesPeople.php',8),(1,'system','Reports','Sales Types','/SalesTypes.php',1),(1,'system','Reports','Set Purchase Order Authorisation levels','/PO_AuthorisationLevels.php',6),(1,'system','Reports','Shippers','/Shippers.php',10),(1,'system','Reports','Supplier Types','/SupplierTypes.php',3),(1,'system','Transactions','Access Permissions Maintenance','/WWW_Access.php',5),(1,'system','Transactions','Company Preferences','/CompanyPreferences.php',1),(1,'system','Transactions','Currency Maintenance','/Currencies.php',7),(1,'system','Transactions','Dispatch Tax Province Maintenance','/TaxProvinces.php',10),(1,'system','Transactions','Form Designer','/FormDesigner.php',16),(1,'system','Transactions','Geocode Maintenance','/GeocodeSetup.php',15),(1,'system','Transactions','List Periods Defined','/PeriodsInquiry.php',12),(1,'system','Transactions','Mailing Group Maintenance','/MailingGroupMaintenance.php',19),(1,'system','Transactions','Maintain Security Tokens','/SecurityTokens.php',4),(1,'system','Transactions','Page Security Settings','/PageSecurity.php',6),(1,'system','Transactions','Report Builder Tool','/reportwriter/admin/ReportCreator.php',13),(1,'system','Transactions','SMTP Server Details','/SMTPServer.php',18),(1,'system','Transactions','System Parameters','/SystemParameters.php',2),(1,'system','Transactions','Tax Authorities and Rates Maintenance','/TaxAuthorities.php',8),(1,'system','Transactions','Tax Category Maintenance','/TaxCategories.php',11),(1,'system','Transactions','Tax Group Maintenance','/TaxGroups.php',9),(1,'system','Transactions','Users Maintenance','/WWW_Users.php',3),(1,'system','Transactions','View Audit Trail','/AuditTrail.php',14),(1,'system','Transactions','Web-Store Configuration','/ShopParameters.php',17),(1,'Utilities','Maintenance','Create new company template SQL file and submit to webERP','/Z_CreateCompanyTemplateFile.php',9),(1,'Utilities','Maintenance','Data Export Options','/Z_DataExport.php',3),(1,'Utilities','Maintenance','Import Customers from .csv file','/Z_ImportDebtors.php',4),(1,'Utilities','Maintenance','Import Fixed Assets from .csv file','/Z_ImportFixedAssets.php',7),(1,'Utilities','Maintenance','Import GL Payments Receipts Or Journals From .csv file','/Z_ImportGLTransactions.php',8),(1,'Utilities','Maintenance','Import Price List from .csv file','/Z_ImportPriceList.php',6),(1,'Utilities','Maintenance','Import Stock Items from .csv','/Z_ImportStocks.php',5),(1,'Utilities','Maintenance','Maintain Language Files','/Z_poAdmin.php',1),(1,'Utilities','Maintenance','Make New Company','/Z_MakeNewCompany.php',2),(1,'Utilities','Maintenance','Purge all old prices','/Z_DeleteOldPrices.php',12),(1,'Utilities','Maintenance','Remove all purchase back orders','/Z_RemovePurchaseBackOrders.php',13),(1,'Utilities','Reports','Debtors Balances By Currency Totals','/Z_CurrencyDebtorsBalances.php',1),(1,'Utilities','Reports','List of items without picture','/Z_ItemsWithoutPicture.php',4),(1,'Utilities','Reports','Show General Transactions That Do Not Balance','/Z_CheckGLTransBalance.php',3),(1,'Utilities','Reports','Suppliers Balances By Currency Totals','/Z_CurrencySuppliersBalances.php',2),(1,'Utilities','Transactions','Change A Customer Branch Code','/Z_ChangeBranchCode.php',2),(1,'Utilities','Transactions','Change A Customer Code','/Z_ChangeCustomerCode.php',1),(1,'Utilities','Transactions','Change A GL Account Code','/Z_ChangeGLAccountCode.php',3),(1,'Utilities','Transactions','Change A Location Code','/Z_ChangeLocationCode.php',5),(1,'Utilities','Transactions','Change A Salesman Code','/Z_ChangeSalesmanCode.php',6),(1,'Utilities','Transactions','Change A Stock Category Code','/Z_ChangeStockCategory.php',7),(1,'Utilities','Transactions','Change A Supplier Code','/Z_ChangeSupplierCode.php',8),(1,'Utilities','Transactions','Change An Inventory Item Code','/Z_ChangeStockCode.php',4),(1,'Utilities','Transactions','Copy Authority of GL Accounts from one user to another','/Z_GLAccountUsersCopyAuthority.php',15),(1,'Utilities','Transactions','Delete sales transactions','/Z_DeleteSalesTransActions.php',12),(1,'Utilities','Transactions','Re-apply costs to Sales Analysis','/Z_ReApplyCostToSA.php',11),(1,'Utilities','Transactions','Reverse all supplier payments on a specified date','/Z_ReverseSuppPaymentRun.php',13),(1,'Utilities','Transactions','Translate Item Descriptions','/AutomaticTranslationDescriptions.php',9),(1,'Utilities','Transactions','Update costs for all BOM items, from the bottom up','/Z_BottomUpCosts.php',10),(1,'Utilities','Transactions','Update sales analysis with latest customer data','/Z_UpdateSalesAnalysisWithLatestCustomerData.php',14),(2,'AP','Maintenance','Add Supplier','/Suppliers.php',1),(2,'AP','Maintenance','Maintain Factor Companies','/Factors.php',3),(2,'AP','Maintenance','Select Supplier','/SelectSupplier.php',2),(2,'AP','Reports','Aged Supplier Report','/AgedSuppliers.php',2),(2,'AP','Reports','List Daily Transactions','/PDFSuppTransListing.php',7),(2,'AP','Reports','Outstanding GRNs Report','/OutstandingGRNs.php',5),(2,'AP','Reports','Payment Run Report','/SuppPaymentRun.php',3),(2,'AP','Reports','Remittance Advices','/PDFRemittanceAdvice.php',4),(2,'AP','Reports','Supplier Balances At A Prior Month End','/SupplierBalsAtPeriodEnd.php',6),(2,'AP','Reports','Supplier Transaction Inquiries','/SupplierTransInquiry.php',8),(2,'AP','Reports','Where Allocated Inquiry','/SuppWhereAlloc.php',1),(2,'AP','Transactions','Select Supplier','/SelectSupplier.php',1),(2,'AP','Transactions','Supplier Allocations','/SupplierAllocations.php',2),(2,'AR','Maintenance','Add Customer','/Customers.php',1),(2,'AR','Maintenance','Select Customer','/SelectCustomer.php',2),(2,'AR','Reports','Aged Customer Balances/Overdues Report','/AgedDebtors.php',4),(2,'AR','Reports','Customer Activity and Balances','/CustomerBalancesMovement.php',10),(2,'AR','Reports','Customer Listing By Area/Salesperson','/PDFCustomerList.php',7),(2,'AR','Reports','Customer Transaction Inquiries','/CustomerTransInquiry.php',9),(2,'AR','Reports','Debtor Balances At A Prior Month End','/DebtorsAtPeriodEnd.php',6),(2,'AR','Reports','List Daily Transactions','/PDFCustTransListing.php',8),(2,'AR','Reports','Print Invoices or Credit Notes','/PrintCustTrans.php',2),(2,'AR','Reports','Print Statements','/PrintCustStatements.php',3),(2,'AR','Reports','Re-Print A Deposit Listing','/PDFBankingSummary.php',5),(2,'AR','Reports','Where Allocated Inquiry','/CustWhereAlloc.php',1),(2,'AR','Transactions','Allocate Receipts or Credit Notes','/CustomerAllocations.php',4),(2,'AR','Transactions','Create A Credit Note','/SelectCreditItems.php?NewCredit=Yes',2),(2,'AR','Transactions','Enter Receipts','/CustomerReceipt.php?NewReceipt=Yes&amp;Type=Customer',3),(2,'AR','Transactions','Select Order to Invoice','/SelectSalesOrder.php',1),(2,'FA','Maintenance','Add or Maintain Asset Locations','/FixedAssetLocations.php',2),(2,'FA','Maintenance','Fixed Asset Category Maintenance','/FixedAssetCategories.php',1),(2,'FA','Maintenance','Fixed Asset Maintenance Tasks','/MaintenanceTasks.php',3),(2,'FA','Reports','Asset Register','/FixedAssetRegister.php',1),(2,'FA','Reports','Maintenance Reminder Emails','/MaintenanceReminders.php',3),(2,'FA','Reports','My Maintenance Schedule','/MaintenanceUserSchedule.php',2),(2,'FA','Transactions','Add a new Asset','/FixedAssetItems.php',1),(2,'FA','Transactions','Change Asset Location','/FixedAssetTransfer.php',3),(2,'FA','Transactions','Depreciation Journal','/FixedAssetDepreciation.php',4),(2,'FA','Transactions','Select an Asset','/SelectAsset.php',2),(2,'GL','Maintenance','Account Groups','/AccountGroups.php',4),(2,'GL','Maintenance','Account Sections','/AccountSections.php',1),(2,'GL','Maintenance','Bank Account Authorised Users','/BankAccountUsers.php',11),(2,'GL','Maintenance','Bank Accounts','/BankAccounts.php',10),(2,'GL','Maintenance','Copy Authority Bank Accounts from one user to another','/GLBankAccountUsersCopyAuthority.php',21),(2,'GL','Maintenance','Copy Authority GL Accounts from one user to another','/GLAccountUsersCopyAuthority.php',20),(2,'GL','Maintenance','Create Budget Amounts','/GLBudgets.php',3),(2,'GL','Maintenance','Create/Amend General Ledger Budgets','/GLBudgetHeaders.php',2),(2,'GL','Maintenance','GL Account','/GLAccounts.php',5),(2,'GL','Maintenance','GL Account Authorised Users','/GLAccountUsers.php',6),(2,'GL','Maintenance','GL Tags','/GLTags.php',9),(2,'GL','Maintenance','Maintain Journal Templates','/GLJournalTemplates.php',13),(2,'GL','Maintenance','Setup Regular Payments','/RegularPaymentsSetup.php',14),(2,'GL','Maintenance','User Authorised Bank Accounts','/UserBankAccounts.php',12),(2,'GL','Maintenance','User Authorised GL Accounts','/UserGLAccounts.php',7),(2,'GL','Reports','Account Inquiry','/SelectGLAccount.php',5),(2,'GL','Reports','Account Listing','/GLAccountReport.php',7),(2,'GL','Reports','Account Listing to CSV File','/GLAccountCSV.php',8),(2,'GL','Reports','Balance Sheet','/GLBalanceSheet.php',11),(2,'GL','Reports','Bank Account Balances','/BankAccountBalances.php',1),(2,'GL','Reports','Bank Account Reconciliation Statement','/BankReconciliation.php',2),(2,'GL','Reports','Cheque Payments Listing','/PDFChequeListing.php',3),(2,'GL','Reports','Daily Bank Transactions','/DailyBankTransactions.php',4),(2,'GL','Reports','Financial Statements','/GLStatements.php',14),(2,'GL','Reports','General Ledger Journal Inquiry','/GLJournalInquiry.php',9),(2,'GL','Reports','Graph of Account Transactions','/GLAccountGraph.php',6),(2,'GL','Reports','Horizontal Analysis of Statement of Comprehensive Income','/AnalysisHorizontalIncome.php',16),(2,'GL','Reports','Horizontal Analysis of Statement of Financial Position','/AnalysisHorizontalPosition.php',15),(2,'GL','Reports','Income and Expenditure by Tag','/GLTagProfit_Loss.php',17),(2,'GL','Reports','Profit and Loss Statement','/GLProfit_Loss.php',12),(2,'GL','Reports','Statement of Cash Flows','/GLCashFlowsIndirect.php',13),(2,'GL','Reports','Tax Reports','/Tax.php',19),(2,'GL','Reports','Trial Balance','/GLTrialBalance.php',10),(2,'GL','Transactions','Bank Account Payments Entry','/Payments.php?NewPayment=Yes',1),(2,'GL','Transactions','Bank Account Payments Matching','/BankMatching.php?Type=Payments',4),(2,'GL','Transactions','Bank Account Receipts Entry','/CustomerReceipt.php?NewReceipt=Yes&amp;Type=GL',2),(2,'GL','Transactions','Bank Account Receipts Matching','/BankMatching.php?Type=Receipts',5),(2,'GL','Transactions','Journal Entry','/GLJournal.php?NewJournal=Yes',3),(2,'GL','Transactions','Import Bank Transactions','/ImportBankTrans.php',6),(2,'GL','Transactions','Process Regular Payments','/RegularPaymentsProcess.php',7),(2,'manuf','Maintenance','Auto Create Master Schedule','/MRPCreateDemands.php',5),(2,'manuf','Maintenance','Bills Of Material','/BOMs.php',2),(2,'manuf','Maintenance','Copy a Bill Of Materials Between Items','/CopyBOM.php',3),(2,'manuf','Maintenance','Employees','/Employees.php',9),(2,'manuf','Maintenance','Master Schedule','/MRPDemands.php',4),(2,'manuf','Maintenance','MRP Calculation','/MRP.php',6),(2,'manuf','Maintenance','Product Specifications','/ProductSpecs.php',8),(2,'manuf','Maintenance','Quality Tests Maintenance','/QATests.php',7),(2,'manuf','Maintenance','Work Centre','/WorkCentres.php',1),(2,'manuf','Reports','Bill Of Material Listing','/BOMListing.php',4),(2,'manuf','Reports','Costed Bill Of Material Inquiry','/BOMInquiry.php',2),(2,'manuf','Reports','Historical QA Test Results','/HistoricalTestResults.php',17),(2,'manuf','Reports','Indented Bill Of Material Listing','/BOMIndented.php',5),(2,'manuf','Reports','Indented Where Used Listing','/BOMIndentedReverse.php',9),(2,'manuf','Reports','List Components Required','/BOMExtendedQty.php',6),(2,'manuf','Reports','List Materials Not Used anywhere','/MaterialsNotUsed.php',7),(2,'manuf','Reports','MRP','/MRPReport.php',10),(2,'manuf','Reports','MRP Reschedules Required','/MRPReschedules.php',14),(2,'manuf','Reports','MRP Shortages','/MRPShortages.php',11),(2,'manuf','Reports','MRP Suggested Purchase Orders','/MRPPlannedPurchaseOrders.php',12),(2,'manuf','Reports','MRP Suggested Work Orders','/MRPPlannedWorkOrders.php',13),(2,'manuf','Reports','Multiple Work Orders Total Cost Inquiry','/CollectiveWorkOrderCost.php',18),(2,'manuf','Reports','Print Certificate of Analysis','/PDFCOA.php',16),(2,'manuf','Reports','Print Product Specification','/PDFProdSpec.php',15),(2,'manuf','Reports','Select A Work Order','/SelectWorkOrder.php',1),(2,'manuf','Reports','Where Used Inquiry','/WhereUsedInquiry.php',3),(2,'manuf','Reports','WO Items ready to produce','/WOCanBeProducedNow.php',8),(2,'manuf','Transactions','QA Samples and Test Results','/SelectQASamples.php',3),(2,'manuf','Transactions','Select A Work Order','/SelectWorkOrder.php',2),(2,'manuf','Transactions','Timesheet Entry','/Timesheets.php',4),(2,'manuf','Transactions','Work Order Entry','/WorkOrderEntry.php?New=True',1),(2,'PC','Maintenance','Expenses for Type of PC Tab','/PcExpensesTypeTab.php',4),(2,'PC','Maintenance','PC Expenses','/PcExpenses.php',3),(2,'PC','Maintenance','PC Tabs','/PcTabs.php',2),(2,'PC','Maintenance','Types of PC Tabs','/PcTypeTabs.php',1),(2,'PC','Reports','PC Expense General Report','/PcReportExpense.php',2),(2,'PC','Reports','PC Expenses Analysis','/PcAnalysis.php',4),(2,'PC','Reports','PC Tab Expenses List','/PcTabExpensesList.php',3),(2,'PC','Reports','PC Tab General Report','/PcReportTab.php',1),(2,'PC','Transactions','Assign Cash to PC Tab','/PcAssignCashToTab.php',1),(2,'PC','Transactions','Authorise Assigned Cash','/PcAuthorizeCash.php',5),(2,'PC','Transactions','Authorise Expenses','/PcAuthorizeExpenses.php',4),(2,'PC','Transactions','Claim Expenses From PC Tab','/PcClaimExpensesFromTab.php',3),(2,'PC','Transactions','Transfer Assigned Cash Between PC Tabs','/PcAssignCashTabToTab.php',2),(2,'PO','Maintenance','Maintain Supplier Price Lists','/SupplierPriceList.php',1),(2,'PO','Reports','Purchase Order Detail Or Summary Inquiries','/POReport.php',2),(2,'PO','Reports','Purchase Order Inquiry','/PO_SelectPurchOrder.php',1),(2,'PO','Reports','Purchase Orders Financial Planning','/POFinancialPlanning.php',20),(2,'PO','Reports','Purchases from Suppliers','/PurchasesReport.php',4),(2,'PO','Reports','Supplier Price List','/SuppPriceList.php',3),(2,'PO','Transactions','Create a New Tender','/SupplierTenderCreate.php?New=Yes',4),(2,'PO','Transactions','Edit Existing Tenders','/SupplierTenderCreate.php?Edit=Yes',5),(2,'PO','Transactions','New Purchase Order','/PO_Header.php?NewOrder=Yes',1),(2,'PO','Transactions','Orders to Authorise','/PO_AuthoriseMyOrders.php',7),(2,'PO','Transactions','Process Tenders and Offers','/OffersReceived.php',6),(2,'PO','Transactions','Purchase Order Grid Entry','/PurchaseByPrefSupplier.php',3),(2,'PO','Transactions','Purchase Orders','/PO_SelectOSPurchOrder.php',2),(2,'PO','Transactions','Select A Shipment','/Shipt_Select.php',9),(2,'PO','Transactions','Shipment Entry','/SelectSupplier.php',8),(2,'Sales','Maintenance','Create Contract','/Contracts.php',1),(2,'Sales','Maintenance','Select Contract','/SelectContract.php',2),(2,'Sales','Maintenance','Sell Through Support Deals','/SellThroughSupport.php',3),(2,'Sales','Reports','Daily Sales Inquiry','/DailySalesInquiry.php',5),(2,'Sales','Reports','Delivery In Full On Time (DIFOT) Report','/PDFDIFOT.php',13),(2,'Sales','Reports','Order Delivery Differences Report','/PDFDeliveryDifferences.php',12),(2,'Sales','Reports','Order Status Report','/PDFOrderStatus.php',3),(2,'Sales','Reports','Orders Invoiced Reports','/PDFOrdersInvoiced.php',4),(2,'Sales','Reports','Print Price Lists','/PDFPriceList.php',2),(2,'Sales','Reports','Sales Analysis Reports','/SalesAnalRepts.php',9),(2,'Sales','Reports','Sales By Category By Item Inquiry','/StockCategorySalesInquiry.php',8),(2,'Sales','Reports','Sales By Category Inquiry','/SalesCategoryPeriodInquiry.php',7),(2,'Sales','Reports','Sales By Sales Type Inquiry','/SalesByTypePeriodInquiry.php',6),(2,'Sales','Reports','Sales Commission Reports','/SalesCommissionReports.php',21),(2,'Sales','Reports','Sales Graphs','/SalesGraph.php',10),(2,'Sales','Reports','Sales Order Detail Or Summary Inquiries','/SalesInquiry.php',14),(2,'Sales','Reports','Sales Order Inquiry','/SelectCompletedOrder.php',1),(2,'Sales','Reports','Sales to Customers','/SalesReport.php',20),(2,'Sales','Reports','Sales With Low Gross Profit Report','/PDFLowGP.php',18),(2,'Sales','Reports','Sell Through Support Claims Report','/PDFSellThroughSupportClaim.php',19),(2,'Sales','Reports','Top Customers Inquiry','/SalesTopCustomersInquiry.php',16),(2,'Sales','Reports','Top Sales Items Report','/TopItems.php',15),(2,'Sales','Reports','Top Sellers Inquiry','/SalesTopItemsInquiry.php',11),(2,'Sales','Reports','Worst Sales Items Report','/NoSalesItems.php',17),(2,'Sales','Transactions','Enter Counter Returns','/CounterReturns.php',3),(2,'Sales','Transactions','Enter Counter Sales','/CounterSales.php',2),(2,'Sales','Transactions','Generate/Print Picking Lists','/PDFPickingList.php',4),(2,'Sales','Transactions','Maintain Picking Lists','/SelectPickingLists.php',9),(2,'Sales','Transactions','New Sales Order or Quotation','/SelectOrderItems.php?NewOrder=Yes',1),(2,'Sales','Transactions','Outstanding Sales Orders/Quotations','/SelectSalesOrder.php',5),(2,'Sales','Transactions','Process Recurring Orders','/RecurringSalesOrdersProcess.php',8),(2,'Sales','Transactions','Recurring Order Template','/SelectRecurringSalesOrder.php',7),(2,'Sales','Transactions','Special Order','/SpecialOrder.php',6),(2,'stock','Maintenance','Add A New Item','/Stocks.php',1),(2,'stock','Maintenance','Add or Update Prices Based On Costs','/PricesBasedOnMarkUp.php',6),(2,'stock','Maintenance','Brands Maintenance','/Manufacturers.php',5),(2,'stock','Maintenance','Reorder Level By Category/Location','/ReorderLevelLocation.php',9),(2,'stock','Maintenance','Review Translated Descriptions','/RevisionTranslations.php',3),(2,'stock','Maintenance','Sales Category Maintenance','/SalesCategories.php',4),(2,'stock','Maintenance','Select An Item','/SelectProduct.php',2),(2,'stock','Maintenance','Upload new prices from csv file','/UploadPriceList.php',8),(2,'stock','Maintenance','View or Update Prices Based On Costs','/PricesByCost.php',7),(2,'stock','Reports','Aged Controlled Stock Report','/AgedControlledInventory.php',23),(2,'stock','Reports','All Inventory Movements By Location/Date','/StockLocMovements.php',17),(2,'stock','Reports','Compare Counts Vs Stock Check Data','/PDFStockCheckComparison.php',16),(2,'stock','Reports','Historical Stock Quantity By Location/Category','/StockQuantityByDate.php',19),(2,'stock','Reports','Internal stock request inquiry','/InternalStockRequestInquiry.php',24),(2,'stock','Reports','Inventory Item Movements','/StockMovements.php',4),(2,'stock','Reports','Inventory Item Status','/StockStatus.php',5),(2,'stock','Reports','Inventory Item Usage','/StockUsage.php',6),(2,'stock','Reports','Inventory Planning Based On Preferred Supplier Data','/InventoryPlanningPrefSupplier.php',13),(2,'stock','Reports','Inventory Planning Report','/InventoryPlanning.php',12),(2,'stock','Reports','Inventory Quantities','/InventoryQuantities.php',7),(2,'stock','Reports','Inventory Stock Check Sheets','/StockCheck.php',14),(2,'stock','Reports','Inventory Valuation Report','/InventoryValuation.php',10),(2,'stock','Reports','List Inventory Status By Location/Category','/StockLocStatus.php',18),(2,'stock','Reports','List Negative Stocks','/PDFStockNegatives.php',20),(2,'stock','Reports','Mail Inventory Valuation Report','/MailInventoryValuation.php',11),(2,'stock','Reports','Make Inventory Quantities CSV','/StockQties_csv.php',15),(2,'stock','Reports','Period Stock Transaction Listing','/PDFPeriodStockTransListing.php',21),(2,'stock','Reports','Print Price Labels','/PDFPrintLabel.php',2),(2,'stock','Reports','Reorder Level','/ReorderLevel.php',8),(2,'stock','Reports','Reprint GRN','/ReprintGRN.php',3),(2,'stock','Reports','Serial Item Research Tool','/StockSerialItemResearch.php',1),(2,'stock','Reports','Stock Dispatch','/StockDispatch.php',9),(2,'stock','Reports','Stock Transfer Note','/PDFStockTransfer.php',22),(2,'stock','Transactions','Authorise Internal Stock Requests','/InternalStockRequestAuthorisation.php',9),(2,'stock','Transactions','Bulk Inventory Transfer - Dispatch','/StockLocTransfer.php',3),(2,'stock','Transactions','Bulk Inventory Transfer - Receive','/StockLocTransferReceive.php',4),(2,'stock','Transactions','Create a New Internal Stock Request','/InternalStockRequest.php?New=Yes',8),(2,'stock','Transactions','Enter Stock Counts','/StockCounts.php',7),(2,'stock','Transactions','Fulfil Internal Stock Requests','/InternalStockRequestFulfill.php',10),(2,'stock','Transactions','Inventory Adjustments','/StockAdjustments.php?NewAdjustment=Yes',5),(2,'stock','Transactions','Inventory Location Transfers','/StockTransfers.php?New=Yes',2),(2,'stock','Transactions','Receive Purchase Orders','/PO_SelectOSPurchOrder.php',1),(2,'stock','Transactions','Reverse Goods Received','/ReverseGRN.php',6),(2,'system','Maintenance','Copy Authority Locations from one user to another','/LocationUsersCopyAuthority.php',20),(2,'system','Maintenance','Dashboard Configuration','/DashboardConfig.php',13),(2,'system','Maintenance','Discount Category Maintenance','/DiscountCategories.php',5),(2,'system','Maintenance','Inventory Categories Maintenance','/StockCategories.php',1),(2,'system','Maintenance','Inventory Location Authorised Users Maintenance','/LocationUsers.php',3),(2,'system','Maintenance','Inventory Locations Maintenance','/Locations.php',2),(2,'system','Maintenance','Label Templates Maintenance','/Labels.php',12),(2,'system','Maintenance','Logged in users','/LoggedInUsers.php',8),(2,'system','Maintenance','Maintain Internal Departments','/Departments.php',10),(2,'system','Maintenance','Maintain Internal Stock Categories to User Roles','/InternalStockCategoriesByRole.php',11),(2,'system','Maintenance','MRP Available Production Days','/MRPCalendar.php',7),(2,'system','Maintenance','MRP Demand Types','/MRPDemandTypes.php',9),(2,'system','Maintenance','Units of Measure','/UnitsOfMeasure.php',6),(2,'system','Maintenance','User Authorised Inventory Locations Maintenance','/UserLocations.php',4),(2,'system','Reports','COGS GL Interface Postings','/COGSGLPostings.php',12),(2,'system','Reports','Credit Status','/CreditStatus.php',4),(2,'system','Reports','Customer Types','/CustomerTypes.php',2),(2,'system','Reports','Discount Matrix','/DiscountMatrix.php',14),(2,'system','Reports','Freight Costs Maintenance','/FreightCosts.php',13),(2,'system','Reports','Payment Methods','/PaymentMethods.php',7),(2,'system','Reports','Payment Terms','/PaymentTerms.php',5),(2,'system','Reports','Sales Areas','/Areas.php',9),(2,'system','Reports','Sales Commission Types','/SalesCommissionTypes.php',15),(2,'system','Reports','Sales GL Interface Postings','/SalesGLPostings.php',11),(2,'system','Reports','Sales People','/SalesPeople.php',8),(2,'system','Reports','Sales Types','/SalesTypes.php',1),(2,'system','Reports','Set Purchase Order Authorisation levels','/PO_AuthorisationLevels.php',6),(2,'system','Reports','Shippers','/Shippers.php',10),(2,'system','Reports','Supplier Types','/SupplierTypes.php',3),(2,'system','Transactions','Access Permissions Maintenance','/WWW_Access.php',5),(2,'system','Transactions','Company Preferences','/CompanyPreferences.php',1),(2,'system','Transactions','Currency Maintenance','/Currencies.php',7),(2,'system','Transactions','Dispatch Tax Province Maintenance','/TaxProvinces.php',10),(2,'system','Transactions','Form Designer','/FormDesigner.php',16),(2,'system','Transactions','Geocode Maintenance','/GeocodeSetup.php',15),(2,'system','Transactions','List Periods Defined','/PeriodsInquiry.php',12),(2,'system','Transactions','Mailing Group Maintenance','/MailingGroupMaintenance.php',19),(2,'system','Transactions','Maintain Security Tokens','/SecurityTokens.php',4),(2,'system','Transactions','Page Security Settings','/PageSecurity.php',6),(2,'system','Transactions','Report Builder Tool','/reportwriter/admin/ReportCreator.php',13),(2,'system','Transactions','SMTP Server Details','/SMTPServer.php',18),(2,'system','Transactions','System Parameters','/SystemParameters.php',2),(2,'system','Transactions','Tax Authorities and Rates Maintenance','/TaxAuthorities.php',8),(2,'system','Transactions','Tax Category Maintenance','/TaxCategories.php',11),(2,'system','Transactions','Tax Group Maintenance','/TaxGroups.php',9),(2,'system','Transactions','Users Maintenance','/WWW_Users.php',3),(2,'system','Transactions','View Audit Trail','/AuditTrail.php',14),(2,'system','Transactions','Web-Store Configuration','/ShopParameters.php',17),(2,'Utilities','Maintenance','Create new company template SQL file and submit to webERP','/Z_CreateCompanyTemplateFile.php',9),(2,'Utilities','Maintenance','Data Export Options','/Z_DataExport.php',3),(2,'Utilities','Maintenance','Import Customers from .csv file','/Z_ImportDebtors.php',4),(2,'Utilities','Maintenance','Import Fixed Assets from .csv file','/Z_ImportFixedAssets.php',7),(2,'Utilities','Maintenance','Import GL Payments Receipts Or Journals From .csv file','/Z_ImportGLTransactions.php',8),(2,'Utilities','Maintenance','Import Price List from .csv file','/Z_ImportPriceList.php',6),(2,'Utilities','Maintenance','Import Stock Items from .csv','/Z_ImportStocks.php',5),(2,'Utilities','Maintenance','Maintain Language Files','/Z_poAdmin.php',1),(2,'Utilities','Maintenance','Make New Company','/Z_MakeNewCompany.php',2),(2,'Utilities','Maintenance','Purge all old prices','/Z_DeleteOldPrices.php',12),(2,'Utilities','Maintenance','Remove all purchase back orders','/Z_RemovePurchaseBackOrders.php',13),(2,'Utilities','Reports','Debtors Balances By Currency Totals','/Z_CurrencyDebtorsBalances.php',1),(2,'Utilities','Reports','List of items without picture','/Z_ItemsWithoutPicture.php',4),(2,'Utilities','Reports','Show General Transactions That Do Not Balance','/Z_CheckGLTransBalance.php',3),(2,'Utilities','Reports','Suppliers Balances By Currency Totals','/Z_CurrencySuppliersBalances.php',2),(2,'Utilities','Transactions','Change A Customer Branch Code','/Z_ChangeBranchCode.php',2),(2,'Utilities','Transactions','Change A Customer Code','/Z_ChangeCustomerCode.php',1),(2,'Utilities','Transactions','Change A GL Account Code','/Z_ChangeGLAccountCode.php',3),(2,'Utilities','Transactions','Change A Location Code','/Z_ChangeLocationCode.php',5),(2,'Utilities','Transactions','Change A Salesman Code','/Z_ChangeSalesmanCode.php',6),(2,'Utilities','Transactions','Change A Stock Category Code','/Z_ChangeStockCategory.php',7),(2,'Utilities','Transactions','Change A Supplier Code','/Z_ChangeSupplierCode.php',8),(2,'Utilities','Transactions','Change An Inventory Item Code','/Z_ChangeStockCode.php',4),(2,'Utilities','Transactions','Copy Authority of GL Accounts from one user to another','/Z_GLAccountUsersCopyAuthority.php',15),(2,'Utilities','Transactions','Delete sales transactions','/Z_DeleteSalesTransActions.php',12),(2,'Utilities','Transactions','Re-apply costs to Sales Analysis','/Z_ReApplyCostToSA.php',11),(2,'Utilities','Transactions','Reverse all supplier payments on a specified date','/Z_ReverseSuppPaymentRun.php',13),(2,'Utilities','Transactions','Translate Item Descriptions','/AutomaticTranslationDescriptions.php',9),(2,'Utilities','Transactions','Update costs for all BOM items, from the bottom up','/Z_BottomUpCosts.php',10),(2,'Utilities','Transactions','Update sales analysis with latest customer data','/Z_UpdateSalesAnalysisWithLatestCustomerData.php',14),(3,'AP','Maintenance','Add Supplier','/Suppliers.php',1),(3,'AP','Maintenance','Maintain Factor Companies','/Factors.php',3),(3,'AP','Maintenance','Select Supplier','/SelectSupplier.php',2),(3,'AP','Reports','Aged Supplier Report','/AgedSuppliers.php',2),(3,'AP','Reports','List Daily Transactions','/PDFSuppTransListing.php',7),(3,'AP','Reports','Outstanding GRNs Report','/OutstandingGRNs.php',5),(3,'AP','Reports','Payment Run Report','/SuppPaymentRun.php',3),(3,'AP','Reports','Remittance Advices','/PDFRemittanceAdvice.php',4),(3,'AP','Reports','Supplier Balances At A Prior Month End','/SupplierBalsAtPeriodEnd.php',6),(3,'AP','Reports','Supplier Transaction Inquiries','/SupplierTransInquiry.php',8),(3,'AP','Reports','Where Allocated Inquiry','/SuppWhereAlloc.php',1),(3,'AP','Transactions','Select Supplier','/SelectSupplier.php',1),(3,'AP','Transactions','Supplier Allocations','/SupplierAllocations.php',2),(3,'AR','Maintenance','Add Customer','/Customers.php',1),(3,'AR','Maintenance','Select Customer','/SelectCustomer.php',2),(3,'AR','Reports','Aged Customer Balances/Overdues Report','/AgedDebtors.php',4),(3,'AR','Reports','Customer Activity and Balances','/CustomerBalancesMovement.php',10),(3,'AR','Reports','Customer Listing By Area/Salesperson','/PDFCustomerList.php',7),(3,'AR','Reports','Customer Transaction Inquiries','/CustomerTransInquiry.php',9),(3,'AR','Reports','Debtor Balances At A Prior Month End','/DebtorsAtPeriodEnd.php',6),(3,'AR','Reports','List Daily Transactions','/PDFCustTransListing.php',8),(3,'AR','Reports','Print Invoices or Credit Notes','/PrintCustTrans.php',2),(3,'AR','Reports','Print Statements','/PrintCustStatements.php',3),(3,'AR','Reports','Re-Print A Deposit Listing','/PDFBankingSummary.php',5),(3,'AR','Reports','Where Allocated Inquiry','/CustWhereAlloc.php',1),(3,'AR','Transactions','Allocate Receipts or Credit Notes','/CustomerAllocations.php',4),(3,'AR','Transactions','Create A Credit Note','/SelectCreditItems.php?NewCredit=Yes',2),(3,'AR','Transactions','Enter Receipts','/CustomerReceipt.php?NewReceipt=Yes&amp;Type=Customer',3),(3,'AR','Transactions','Select Order to Invoice','/SelectSalesOrder.php',1),(3,'FA','Maintenance','Add or Maintain Asset Locations','/FixedAssetLocations.php',2),(3,'FA','Maintenance','Fixed Asset Category Maintenance','/FixedAssetCategories.php',1),(3,'FA','Maintenance','Fixed Asset Maintenance Tasks','/MaintenanceTasks.php',3),(3,'FA','Reports','Asset Register','/FixedAssetRegister.php',1),(3,'FA','Reports','Maintenance Reminder Emails','/MaintenanceReminders.php',3),(3,'FA','Reports','My Maintenance Schedule','/MaintenanceUserSchedule.php',2),(3,'FA','Transactions','Add a new Asset','/FixedAssetItems.php',1),(3,'FA','Transactions','Change Asset Location','/FixedAssetTransfer.php',3),(3,'FA','Transactions','Depreciation Journal','/FixedAssetDepreciation.php',4),(3,'FA','Transactions','Select an Asset','/SelectAsset.php',2),(3,'GL','Maintenance','Account Groups','/AccountGroups.php',4),(3,'GL','Maintenance','Account Sections','/AccountSections.php',1),(3,'GL','Maintenance','Bank Account Authorised Users','/BankAccountUsers.php',11),(3,'GL','Maintenance','Bank Accounts','/BankAccounts.php',10),(3,'GL','Maintenance','Copy Authority Bank Accounts from one user to another','/GLBankAccountUsersCopyAuthority.php',21),(3,'GL','Maintenance','Copy Authority GL Accounts from one user to another','/GLAccountUsersCopyAuthority.php',20),(3,'GL','Maintenance','Create Budget Amounts','/GLBudgets.php',3),(3,'GL','Maintenance','Create/Amend General Ledger Budgets','/GLBudgetHeaders.php',2),(3,'GL','Maintenance','GL Account','/GLAccounts.php',5),(3,'GL','Maintenance','GL Account Authorised Users','/GLAccountUsers.php',6),(3,'GL','Maintenance','GL Tags','/GLTags.php',9),(3,'GL','Maintenance','Maintain Journal Templates','/GLJournalTemplates.php',13),(3,'GL','Maintenance','Setup Regular Payments','/RegularPaymentsSetup.php',14),(3,'GL','Maintenance','User Authorised Bank Accounts','/UserBankAccounts.php',12),(3,'GL','Maintenance','User Authorised GL Accounts','/UserGLAccounts.php',7),(3,'GL','Reports','Account Inquiry','/SelectGLAccount.php',5),(3,'GL','Reports','Account Listing','/GLAccountReport.php',7),(3,'GL','Reports','Account Listing to CSV File','/GLAccountCSV.php',8),(3,'GL','Reports','Balance Sheet','/GLBalanceSheet.php',11),(3,'GL','Reports','Bank Account Balances','/BankAccountBalances.php',1),(3,'GL','Reports','Bank Account Reconciliation Statement','/BankReconciliation.php',2),(3,'GL','Reports','Cheque Payments Listing','/PDFChequeListing.php',3),(3,'GL','Reports','Daily Bank Transactions','/DailyBankTransactions.php',4),(3,'GL','Reports','Financial Statements','/GLStatements.php',14),(3,'GL','Reports','General Ledger Journal Inquiry','/GLJournalInquiry.php',9),(3,'GL','Reports','Graph of Account Transactions','/GLAccountGraph.php',6),(3,'GL','Reports','Horizontal Analysis of Statement of Comprehensive Income','/AnalysisHorizontalIncome.php',16),(3,'GL','Reports','Horizontal Analysis of Statement of Financial Position','/AnalysisHorizontalPosition.php',15),(3,'GL','Reports','Income and Expenditure by Tag','/GLTagProfit_Loss.php',17),(3,'GL','Reports','Profit and Loss Statement','/GLProfit_Loss.php',12),(3,'GL','Reports','Statement of Cash Flows','/GLCashFlowsIndirect.php',13),(3,'GL','Reports','Tax Reports','/Tax.php',19),(3,'GL','Reports','Trial Balance','/GLTrialBalance.php',10),(3,'GL','Transactions','Bank Account Payments Entry','/Payments.php?NewPayment=Yes',1),(3,'GL','Transactions','Bank Account Payments Matching','/BankMatching.php?Type=Payments',4),(3,'GL','Transactions','Bank Account Receipts Entry','/CustomerReceipt.php?NewReceipt=Yes&amp;Type=GL',2),(3,'GL','Transactions','Bank Account Receipts Matching','/BankMatching.php?Type=Receipts',5),(3,'GL','Transactions','Journal Entry','/GLJournal.php?NewJournal=Yes',3),(3,'GL','Transactions','Import Bank Transactions','/ImportBankTrans.php',6),(3,'GL','Transactions','Process Regular Payments','/RegularPaymentsProcess.php',7),(3,'manuf','Maintenance','Auto Create Master Schedule','/MRPCreateDemands.php',5),(3,'manuf','Maintenance','Bills Of Material','/BOMs.php',2),(3,'manuf','Maintenance','Copy a Bill Of Materials Between Items','/CopyBOM.php',3),(3,'manuf','Maintenance','Employees','/Employees.php',9),(3,'manuf','Maintenance','Master Schedule','/MRPDemands.php',4),(3,'manuf','Maintenance','MRP Calculation','/MRP.php',6),(3,'manuf','Maintenance','Product Specifications','/ProductSpecs.php',8),(3,'manuf','Maintenance','Quality Tests Maintenance','/QATests.php',7),(3,'manuf','Maintenance','Work Centre','/WorkCentres.php',1),(3,'manuf','Reports','Bill Of Material Listing','/BOMListing.php',4),(3,'manuf','Reports','Costed Bill Of Material Inquiry','/BOMInquiry.php',2),(3,'manuf','Reports','Historical QA Test Results','/HistoricalTestResults.php',17),(3,'manuf','Reports','Indented Bill Of Material Listing','/BOMIndented.php',5),(3,'manuf','Reports','Indented Where Used Listing','/BOMIndentedReverse.php',9),(3,'manuf','Reports','List Components Required','/BOMExtendedQty.php',6),(3,'manuf','Reports','List Materials Not Used anywhere','/MaterialsNotUsed.php',7),(3,'manuf','Reports','MRP','/MRPReport.php',10),(3,'manuf','Reports','MRP Reschedules Required','/MRPReschedules.php',14),(3,'manuf','Reports','MRP Shortages','/MRPShortages.php',11),(3,'manuf','Reports','MRP Suggested Purchase Orders','/MRPPlannedPurchaseOrders.php',12),(3,'manuf','Reports','MRP Suggested Work Orders','/MRPPlannedWorkOrders.php',13),(3,'manuf','Reports','Multiple Work Orders Total Cost Inquiry','/CollectiveWorkOrderCost.php',18),(3,'manuf','Reports','Print Certificate of Analysis','/PDFCOA.php',16),(3,'manuf','Reports','Print Product Specification','/PDFProdSpec.php',15),(3,'manuf','Reports','Select A Work Order','/SelectWorkOrder.php',1),(3,'manuf','Reports','Where Used Inquiry','/WhereUsedInquiry.php',3),(3,'manuf','Reports','WO Items ready to produce','/WOCanBeProducedNow.php',8),(3,'manuf','Transactions','QA Samples and Test Results','/SelectQASamples.php',3),(3,'manuf','Transactions','Select A Work Order','/SelectWorkOrder.php',2),(3,'manuf','Transactions','Timesheet Entry','/Timesheets.php',4),(3,'manuf','Transactions','Work Order Entry','/WorkOrderEntry.php?New=True',1),(3,'PC','Maintenance','Expenses for Type of PC Tab','/PcExpensesTypeTab.php',4),(3,'PC','Maintenance','PC Expenses','/PcExpenses.php',3),(3,'PC','Maintenance','PC Tabs','/PcTabs.php',2),(3,'PC','Maintenance','Types of PC Tabs','/PcTypeTabs.php',1),(3,'PC','Reports','PC Expense General Report','/PcReportExpense.php',2),(3,'PC','Reports','PC Expenses Analysis','/PcAnalysis.php',4),(3,'PC','Reports','PC Tab Expenses List','/PcTabExpensesList.php',3),(3,'PC','Reports','PC Tab General Report','/PcReportTab.php',1),(3,'PC','Transactions','Assign Cash to PC Tab','/PcAssignCashToTab.php',1),(3,'PC','Transactions','Authorise Assigned Cash','/PcAuthorizeCash.php',5),(3,'PC','Transactions','Authorise Expenses','/PcAuthorizeExpenses.php',4),(3,'PC','Transactions','Claim Expenses From PC Tab','/PcClaimExpensesFromTab.php',3),(3,'PC','Transactions','Transfer Assigned Cash Between PC Tabs','/PcAssignCashTabToTab.php',2),(3,'PO','Maintenance','Maintain Supplier Price Lists','/SupplierPriceList.php',1),(3,'PO','Reports','Purchase Order Detail Or Summary Inquiries','/POReport.php',2),(3,'PO','Reports','Purchase Order Inquiry','/PO_SelectPurchOrder.php',1),(3,'PO','Reports','Purchase Orders Financial Planning','/POFinancialPlanning.php',20),(3,'PO','Reports','Purchases from Suppliers','/PurchasesReport.php',4),(3,'PO','Reports','Supplier Price List','/SuppPriceList.php',3),(3,'PO','Transactions','Create a New Tender','/SupplierTenderCreate.php?New=Yes',4),(3,'PO','Transactions','Edit Existing Tenders','/SupplierTenderCreate.php?Edit=Yes',5),(3,'PO','Transactions','New Purchase Order','/PO_Header.php?NewOrder=Yes',1),(3,'PO','Transactions','Orders to Authorise','/PO_AuthoriseMyOrders.php',7),(3,'PO','Transactions','Process Tenders and Offers','/OffersReceived.php',6),(3,'PO','Transactions','Purchase Order Grid Entry','/PurchaseByPrefSupplier.php',3),(3,'PO','Transactions','Purchase Orders','/PO_SelectOSPurchOrder.php',2),(3,'PO','Transactions','Select A Shipment','/Shipt_Select.php',9),(3,'PO','Transactions','Shipment Entry','/SelectSupplier.php',8),(3,'Sales','Maintenance','Create Contract','/Contracts.php',1),(3,'Sales','Maintenance','Select Contract','/SelectContract.php',2),(3,'Sales','Maintenance','Sell Through Support Deals','/SellThroughSupport.php',3),(3,'Sales','Reports','Daily Sales Inquiry','/DailySalesInquiry.php',5),(3,'Sales','Reports','Delivery In Full On Time (DIFOT) Report','/PDFDIFOT.php',13),(3,'Sales','Reports','Order Delivery Differences Report','/PDFDeliveryDifferences.php',12),(3,'Sales','Reports','Order Status Report','/PDFOrderStatus.php',3),(3,'Sales','Reports','Orders Invoiced Reports','/PDFOrdersInvoiced.php',4),(3,'Sales','Reports','Print Price Lists','/PDFPriceList.php',2),(3,'Sales','Reports','Sales Analysis Reports','/SalesAnalRepts.php',9),(3,'Sales','Reports','Sales By Category By Item Inquiry','/StockCategorySalesInquiry.php',8),(3,'Sales','Reports','Sales By Category Inquiry','/SalesCategoryPeriodInquiry.php',7),(3,'Sales','Reports','Sales By Sales Type Inquiry','/SalesByTypePeriodInquiry.php',6),(3,'Sales','Reports','Sales Commission Reports','/SalesCommissionReports.php',21),(3,'Sales','Reports','Sales Graphs','/SalesGraph.php',10),(3,'Sales','Reports','Sales Order Detail Or Summary Inquiries','/SalesInquiry.php',14),(3,'Sales','Reports','Sales Order Inquiry','/SelectCompletedOrder.php',1),(3,'Sales','Reports','Sales to Customers','/SalesReport.php',20),(3,'Sales','Reports','Sales With Low Gross Profit Report','/PDFLowGP.php',18),(3,'Sales','Reports','Sell Through Support Claims Report','/PDFSellThroughSupportClaim.php',19),(3,'Sales','Reports','Top Customers Inquiry','/SalesTopCustomersInquiry.php',16),(3,'Sales','Reports','Top Sales Items Report','/TopItems.php',15),(3,'Sales','Reports','Top Sellers Inquiry','/SalesTopItemsInquiry.php',11),(3,'Sales','Reports','Worst Sales Items Report','/NoSalesItems.php',17),(3,'Sales','Transactions','Enter Counter Returns','/CounterReturns.php',3),(3,'Sales','Transactions','Enter Counter Sales','/CounterSales.php',2),(3,'Sales','Transactions','Generate/Print Picking Lists','/PDFPickingList.php',4),(3,'Sales','Transactions','Maintain Picking Lists','/SelectPickingLists.php',9),(3,'Sales','Transactions','New Sales Order or Quotation','/SelectOrderItems.php?NewOrder=Yes',1),(3,'Sales','Transactions','Outstanding Sales Orders/Quotations','/SelectSalesOrder.php',5),(3,'Sales','Transactions','Process Recurring Orders','/RecurringSalesOrdersProcess.php',8),(3,'Sales','Transactions','Recurring Order Template','/SelectRecurringSalesOrder.php',7),(3,'Sales','Transactions','Special Order','/SpecialOrder.php',6),(3,'stock','Maintenance','Add A New Item','/Stocks.php',1),(3,'stock','Maintenance','Add or Update Prices Based On Costs','/PricesBasedOnMarkUp.php',6),(3,'stock','Maintenance','Brands Maintenance','/Manufacturers.php',5),(3,'stock','Maintenance','Reorder Level By Category/Location','/ReorderLevelLocation.php',9),(3,'stock','Maintenance','Review Translated Descriptions','/RevisionTranslations.php',3),(3,'stock','Maintenance','Sales Category Maintenance','/SalesCategories.php',4),(3,'stock','Maintenance','Select An Item','/SelectProduct.php',2),(3,'stock','Maintenance','Upload new prices from csv file','/UploadPriceList.php',8),(3,'stock','Maintenance','View or Update Prices Based On Costs','/PricesByCost.php',7),(3,'stock','Reports','Aged Controlled Stock Report','/AgedControlledInventory.php',23),(3,'stock','Reports','All Inventory Movements By Location/Date','/StockLocMovements.php',17),(3,'stock','Reports','Compare Counts Vs Stock Check Data','/PDFStockCheckComparison.php',16),(3,'stock','Reports','Historical Stock Quantity By Location/Category','/StockQuantityByDate.php',19),(3,'stock','Reports','Internal stock request inquiry','/InternalStockRequestInquiry.php',24),(3,'stock','Reports','Inventory Item Movements','/StockMovements.php',4),(3,'stock','Reports','Inventory Item Status','/StockStatus.php',5),(3,'stock','Reports','Inventory Item Usage','/StockUsage.php',6),(3,'stock','Reports','Inventory Planning Based On Preferred Supplier Data','/InventoryPlanningPrefSupplier.php',13),(3,'stock','Reports','Inventory Planning Report','/InventoryPlanning.php',12),(3,'stock','Reports','Inventory Quantities','/InventoryQuantities.php',7),(3,'stock','Reports','Inventory Stock Check Sheets','/StockCheck.php',14),(3,'stock','Reports','Inventory Valuation Report','/InventoryValuation.php',10),(3,'stock','Reports','List Inventory Status By Location/Category','/StockLocStatus.php',18),(3,'stock','Reports','List Negative Stocks','/PDFStockNegatives.php',20),(3,'stock','Reports','Mail Inventory Valuation Report','/MailInventoryValuation.php',11),(3,'stock','Reports','Make Inventory Quantities CSV','/StockQties_csv.php',15),(3,'stock','Reports','Period Stock Transaction Listing','/PDFPeriodStockTransListing.php',21),(3,'stock','Reports','Print Price Labels','/PDFPrintLabel.php',2),(3,'stock','Reports','Reorder Level','/ReorderLevel.php',8),(3,'stock','Reports','Reprint GRN','/ReprintGRN.php',3),(3,'stock','Reports','Serial Item Research Tool','/StockSerialItemResearch.php',1),(3,'stock','Reports','Stock Dispatch','/StockDispatch.php',9),(3,'stock','Reports','Stock Transfer Note','/PDFStockTransfer.php',22),(3,'stock','Transactions','Authorise Internal Stock Requests','/InternalStockRequestAuthorisation.php',9),(3,'stock','Transactions','Bulk Inventory Transfer - Dispatch','/StockLocTransfer.php',3),(3,'stock','Transactions','Bulk Inventory Transfer - Receive','/StockLocTransferReceive.php',4),(3,'stock','Transactions','Create a New Internal Stock Request','/InternalStockRequest.php?New=Yes',8),(3,'stock','Transactions','Enter Stock Counts','/StockCounts.php',7),(3,'stock','Transactions','Fulfil Internal Stock Requests','/InternalStockRequestFulfill.php',10),(3,'stock','Transactions','Inventory Adjustments','/StockAdjustments.php?NewAdjustment=Yes',5),(3,'stock','Transactions','Inventory Location Transfers','/StockTransfers.php?New=Yes',2),(3,'stock','Transactions','Receive Purchase Orders','/PO_SelectOSPurchOrder.php',1),(3,'stock','Transactions','Reverse Goods Received','/ReverseGRN.php',6),(3,'system','Maintenance','Copy Authority Locations from one user to another','/LocationUsersCopyAuthority.php',20),(3,'system','Maintenance','Dashboard Configuration','/DashboardConfig.php',13),(3,'system','Maintenance','Discount Category Maintenance','/DiscountCategories.php',5),(3,'system','Maintenance','Inventory Categories Maintenance','/StockCategories.php',1),(3,'system','Maintenance','Inventory Location Authorised Users Maintenance','/LocationUsers.php',3),(3,'system','Maintenance','Inventory Locations Maintenance','/Locations.php',2),(3,'system','Maintenance','Label Templates Maintenance','/Labels.php',12),(3,'system','Maintenance','Logged in users','/LoggedInUsers.php',8),(3,'system','Maintenance','Maintain Internal Departments','/Departments.php',10),(3,'system','Maintenance','Maintain Internal Stock Categories to User Roles','/InternalStockCategoriesByRole.php',11),(3,'system','Maintenance','MRP Available Production Days','/MRPCalendar.php',7),(3,'system','Maintenance','MRP Demand Types','/MRPDemandTypes.php',9),(3,'system','Maintenance','Units of Measure','/UnitsOfMeasure.php',6),(3,'system','Maintenance','User Authorised Inventory Locations Maintenance','/UserLocations.php',4),(3,'system','Reports','COGS GL Interface Postings','/COGSGLPostings.php',12),(3,'system','Reports','Credit Status','/CreditStatus.php',4),(3,'system','Reports','Customer Types','/CustomerTypes.php',2),(3,'system','Reports','Discount Matrix','/DiscountMatrix.php',14),(3,'system','Reports','Freight Costs Maintenance','/FreightCosts.php',13),(3,'system','Reports','Payment Methods','/PaymentMethods.php',7),(3,'system','Reports','Payment Terms','/PaymentTerms.php',5),(3,'system','Reports','Sales Areas','/Areas.php',9),(3,'system','Reports','Sales Commission Types','/SalesCommissionTypes.php',15),(3,'system','Reports','Sales GL Interface Postings','/SalesGLPostings.php',11),(3,'system','Reports','Sales People','/SalesPeople.php',8),(3,'system','Reports','Sales Types','/SalesTypes.php',1),(3,'system','Reports','Set Purchase Order Authorisation levels','/PO_AuthorisationLevels.php',6),(3,'system','Reports','Shippers','/Shippers.php',10),(3,'system','Reports','Supplier Types','/SupplierTypes.php',3),(3,'system','Transactions','Access Permissions Maintenance','/WWW_Access.php',5),(3,'system','Transactions','Company Preferences','/CompanyPreferences.php',1),(3,'system','Transactions','Currency Maintenance','/Currencies.php',7),(3,'system','Transactions','Dispatch Tax Province Maintenance','/TaxProvinces.php',10),(3,'system','Transactions','Form Designer','/FormDesigner.php',16),(3,'system','Transactions','Geocode Maintenance','/GeocodeSetup.php',15),(3,'system','Transactions','List Periods Defined','/PeriodsInquiry.php',12),(3,'system','Transactions','Mailing Group Maintenance','/MailingGroupMaintenance.php',19),(3,'system','Transactions','Maintain Security Tokens','/SecurityTokens.php',4),(3,'system','Transactions','Page Security Settings','/PageSecurity.php',6),(3,'system','Transactions','Report Builder Tool','/reportwriter/admin/ReportCreator.php',13),(3,'system','Transactions','SMTP Server Details','/SMTPServer.php',18),(3,'system','Transactions','System Parameters','/SystemParameters.php',2),(3,'system','Transactions','Tax Authorities and Rates Maintenance','/TaxAuthorities.php',8),(3,'system','Transactions','Tax Category Maintenance','/TaxCategories.php',11),(3,'system','Transactions','Tax Group Maintenance','/TaxGroups.php',9),(3,'system','Transactions','Users Maintenance','/WWW_Users.php',3),(3,'system','Transactions','View Audit Trail','/AuditTrail.php',14),(3,'system','Transactions','Web-Store Configuration','/ShopParameters.php',17),(3,'Utilities','Maintenance','Create new company template SQL file and submit to webERP','/Z_CreateCompanyTemplateFile.php',9),(3,'Utilities','Maintenance','Data Export Options','/Z_DataExport.php',3),(3,'Utilities','Maintenance','Import Customers from .csv file','/Z_ImportDebtors.php',4),(3,'Utilities','Maintenance','Import Fixed Assets from .csv file','/Z_ImportFixedAssets.php',7),(3,'Utilities','Maintenance','Import GL Payments Receipts Or Journals From .csv file','/Z_ImportGLTransactions.php',8),(3,'Utilities','Maintenance','Import Price List from .csv file','/Z_ImportPriceList.php',6),(3,'Utilities','Maintenance','Import Stock Items from .csv','/Z_ImportStocks.php',5),(3,'Utilities','Maintenance','Maintain Language Files','/Z_poAdmin.php',1),(3,'Utilities','Maintenance','Make New Company','/Z_MakeNewCompany.php',2),(3,'Utilities','Maintenance','Purge all old prices','/Z_DeleteOldPrices.php',12),(3,'Utilities','Maintenance','Remove all purchase back orders','/Z_RemovePurchaseBackOrders.php',13),(3,'Utilities','Reports','Debtors Balances By Currency Totals','/Z_CurrencyDebtorsBalances.php',1),(3,'Utilities','Reports','List of items without picture','/Z_ItemsWithoutPicture.php',4),(3,'Utilities','Reports','Show General Transactions That Do Not Balance','/Z_CheckGLTransBalance.php',3),(3,'Utilities','Reports','Suppliers Balances By Currency Totals','/Z_CurrencySuppliersBalances.php',2),(3,'Utilities','Transactions','Change A Customer Branch Code','/Z_ChangeBranchCode.php',2),(3,'Utilities','Transactions','Change A Customer Code','/Z_ChangeCustomerCode.php',1),(3,'Utilities','Transactions','Change A GL Account Code','/Z_ChangeGLAccountCode.php',3),(3,'Utilities','Transactions','Change A Location Code','/Z_ChangeLocationCode.php',5),(3,'Utilities','Transactions','Change A Salesman Code','/Z_ChangeSalesmanCode.php',6),(3,'Utilities','Transactions','Change A Stock Category Code','/Z_ChangeStockCategory.php',7),(3,'Utilities','Transactions','Change A Supplier Code','/Z_ChangeSupplierCode.php',8),(3,'Utilities','Transactions','Change An Inventory Item Code','/Z_ChangeStockCode.php',4),(3,'Utilities','Transactions','Copy Authority of GL Accounts from one user to another','/Z_GLAccountUsersCopyAuthority.php',15),(3,'Utilities','Transactions','Delete sales transactions','/Z_DeleteSalesTransActions.php',12),(3,'Utilities','Transactions','Re-apply costs to Sales Analysis','/Z_ReApplyCostToSA.php',11),(3,'Utilities','Transactions','Reverse all supplier payments on a specified date','/Z_ReverseSuppPaymentRun.php',13),(3,'Utilities','Transactions','Translate Item Descriptions','/AutomaticTranslationDescriptions.php',9),(3,'Utilities','Transactions','Update costs for all BOM items, from the bottom up','/Z_BottomUpCosts.php',10),(3,'Utilities','Transactions','Update sales analysis with latest customer data','/Z_UpdateSalesAnalysisWithLatestCustomerData.php',14),(4,'AP','Maintenance','Add Supplier','/Suppliers.php',1),(4,'AP','Maintenance','Maintain Factor Companies','/Factors.php',3),(4,'AP','Maintenance','Select Supplier','/SelectSupplier.php',2),(4,'AP','Reports','Aged Supplier Report','/AgedSuppliers.php',2),(4,'AP','Reports','List Daily Transactions','/PDFSuppTransListing.php',7),(4,'AP','Reports','Outstanding GRNs Report','/OutstandingGRNs.php',5),(4,'AP','Reports','Payment Run Report','/SuppPaymentRun.php',3),(4,'AP','Reports','Remittance Advices','/PDFRemittanceAdvice.php',4),(4,'AP','Reports','Supplier Balances At A Prior Month End','/SupplierBalsAtPeriodEnd.php',6),(4,'AP','Reports','Supplier Transaction Inquiries','/SupplierTransInquiry.php',8),(4,'AP','Reports','Where Allocated Inquiry','/SuppWhereAlloc.php',1),(4,'AP','Transactions','Select Supplier','/SelectSupplier.php',1),(4,'AP','Transactions','Supplier Allocations','/SupplierAllocations.php',2),(4,'AR','Maintenance','Add Customer','/Customers.php',1),(4,'AR','Maintenance','Select Customer','/SelectCustomer.php',2),(4,'AR','Reports','Aged Customer Balances/Overdues Report','/AgedDebtors.php',4),(4,'AR','Reports','Customer Activity and Balances','/CustomerBalancesMovement.php',10),(4,'AR','Reports','Customer Listing By Area/Salesperson','/PDFCustomerList.php',7),(4,'AR','Reports','Customer Transaction Inquiries','/CustomerTransInquiry.php',9),(4,'AR','Reports','Debtor Balances At A Prior Month End','/DebtorsAtPeriodEnd.php',6),(4,'AR','Reports','List Daily Transactions','/PDFCustTransListing.php',8),(4,'AR','Reports','Print Invoices or Credit Notes','/PrintCustTrans.php',2),(4,'AR','Reports','Print Statements','/PrintCustStatements.php',3),(4,'AR','Reports','Re-Print A Deposit Listing','/PDFBankingSummary.php',5),(4,'AR','Reports','Where Allocated Inquiry','/CustWhereAlloc.php',1),(4,'AR','Transactions','Allocate Receipts or Credit Notes','/CustomerAllocations.php',4),(4,'AR','Transactions','Create A Credit Note','/SelectCreditItems.php?NewCredit=Yes',2),(4,'AR','Transactions','Enter Receipts','/CustomerReceipt.php?NewReceipt=Yes&amp;Type=Customer',3),(4,'AR','Transactions','Select Order to Invoice','/SelectSalesOrder.php',1),(4,'FA','Maintenance','Add or Maintain Asset Locations','/FixedAssetLocations.php',2),(4,'FA','Maintenance','Fixed Asset Category Maintenance','/FixedAssetCategories.php',1),(4,'FA','Maintenance','Fixed Asset Maintenance Tasks','/MaintenanceTasks.php',3),(4,'FA','Reports','Asset Register','/FixedAssetRegister.php',1),(4,'FA','Reports','Maintenance Reminder Emails','/MaintenanceReminders.php',3),(4,'FA','Reports','My Maintenance Schedule','/MaintenanceUserSchedule.php',2),(4,'FA','Transactions','Add a new Asset','/FixedAssetItems.php',1),(4,'FA','Transactions','Change Asset Location','/FixedAssetTransfer.php',3),(4,'FA','Transactions','Depreciation Journal','/FixedAssetDepreciation.php',4),(4,'FA','Transactions','Select an Asset','/SelectAsset.php',2),(4,'GL','Maintenance','Account Groups','/AccountGroups.php',4),(4,'GL','Maintenance','Account Sections','/AccountSections.php',1),(4,'GL','Maintenance','Bank Account Authorised Users','/BankAccountUsers.php',11),(4,'GL','Maintenance','Bank Accounts','/BankAccounts.php',10),(4,'GL','Maintenance','Copy Authority Bank Accounts from one user to another','/GLBankAccountUsersCopyAuthority.php',21),(4,'GL','Maintenance','Copy Authority GL Accounts from one user to another','/GLAccountUsersCopyAuthority.php',20),(4,'GL','Maintenance','Create Budget Amounts','/GLBudgets.php',3),(4,'GL','Maintenance','Create/Amend General Ledger Budgets','/GLBudgetHeaders.php',2),(4,'GL','Maintenance','GL Account','/GLAccounts.php',5),(4,'GL','Maintenance','GL Account Authorised Users','/GLAccountUsers.php',6),(4,'GL','Maintenance','GL Tags','/GLTags.php',9),(4,'GL','Maintenance','Maintain Journal Templates','/GLJournalTemplates.php',13),(4,'GL','Maintenance','Setup Regular Payments','/RegularPaymentsSetup.php',14),(4,'GL','Maintenance','User Authorised Bank Accounts','/UserBankAccounts.php',12),(4,'GL','Maintenance','User Authorised GL Accounts','/UserGLAccounts.php',7),(4,'GL','Reports','Account Inquiry','/SelectGLAccount.php',5),(4,'GL','Reports','Account Listing','/GLAccountReport.php',7),(4,'GL','Reports','Account Listing to CSV File','/GLAccountCSV.php',8),(4,'GL','Reports','Balance Sheet','/GLBalanceSheet.php',11),(4,'GL','Reports','Bank Account Balances','/BankAccountBalances.php',1),(4,'GL','Reports','Bank Account Reconciliation Statement','/BankReconciliation.php',2),(4,'GL','Reports','Cheque Payments Listing','/PDFChequeListing.php',3),(4,'GL','Reports','Daily Bank Transactions','/DailyBankTransactions.php',4),(4,'GL','Reports','Financial Statements','/GLStatements.php',14),(4,'GL','Reports','General Ledger Journal Inquiry','/GLJournalInquiry.php',9),(4,'GL','Reports','Graph of Account Transactions','/GLAccountGraph.php',6),(4,'GL','Reports','Horizontal Analysis of Statement of Comprehensive Income','/AnalysisHorizontalIncome.php',16),(4,'GL','Reports','Horizontal Analysis of Statement of Financial Position','/AnalysisHorizontalPosition.php',15),(4,'GL','Reports','Income and Expenditure by Tag','/GLTagProfit_Loss.php',17),(4,'GL','Reports','Profit and Loss Statement','/GLProfit_Loss.php',12),(4,'GL','Reports','Statement of Cash Flows','/GLCashFlowsIndirect.php',13),(4,'GL','Reports','Tax Reports','/Tax.php',19),(4,'GL','Reports','Trial Balance','/GLTrialBalance.php',10),(4,'GL','Transactions','Bank Account Payments Entry','/Payments.php?NewPayment=Yes',1),(4,'GL','Transactions','Bank Account Payments Matching','/BankMatching.php?Type=Payments',4),(4,'GL','Transactions','Bank Account Receipts Entry','/CustomerReceipt.php?NewReceipt=Yes&amp;Type=GL',2),(4,'GL','Transactions','Bank Account Receipts Matching','/BankMatching.php?Type=Receipts',5),(4,'GL','Transactions','Journal Entry','/GLJournal.php?NewJournal=Yes',3),(4,'GL','Transactions','Import Bank Transactions','/ImportBankTrans.php',6),(4,'GL','Transactions','Process Regular Payments','/RegularPaymentsProcess.php',7),(4,'manuf','Maintenance','Auto Create Master Schedule','/MRPCreateDemands.php',5),(4,'manuf','Maintenance','Bills Of Material','/BOMs.php',2),(4,'manuf','Maintenance','Copy a Bill Of Materials Between Items','/CopyBOM.php',3),(4,'manuf','Maintenance','Employees','/Employees.php',9),(4,'manuf','Maintenance','Master Schedule','/MRPDemands.php',4),(4,'manuf','Maintenance','MRP Calculation','/MRP.php',6),(4,'manuf','Maintenance','Product Specifications','/ProductSpecs.php',8),(4,'manuf','Maintenance','Quality Tests Maintenance','/QATests.php',7),(4,'manuf','Maintenance','Work Centre','/WorkCentres.php',1),(4,'manuf','Reports','Bill Of Material Listing','/BOMListing.php',4),(4,'manuf','Reports','Costed Bill Of Material Inquiry','/BOMInquiry.php',2),(4,'manuf','Reports','Historical QA Test Results','/HistoricalTestResults.php',17),(4,'manuf','Reports','Indented Bill Of Material Listing','/BOMIndented.php',5),(4,'manuf','Reports','Indented Where Used Listing','/BOMIndentedReverse.php',9),(4,'manuf','Reports','List Components Required','/BOMExtendedQty.php',6),(4,'manuf','Reports','List Materials Not Used anywhere','/MaterialsNotUsed.php',7),(4,'manuf','Reports','MRP','/MRPReport.php',10),(4,'manuf','Reports','MRP Reschedules Required','/MRPReschedules.php',14),(4,'manuf','Reports','MRP Shortages','/MRPShortages.php',11),(4,'manuf','Reports','MRP Suggested Purchase Orders','/MRPPlannedPurchaseOrders.php',12),(4,'manuf','Reports','MRP Suggested Work Orders','/MRPPlannedWorkOrders.php',13),(4,'manuf','Reports','Multiple Work Orders Total Cost Inquiry','/CollectiveWorkOrderCost.php',18),(4,'manuf','Reports','Print Certificate of Analysis','/PDFCOA.php',16),(4,'manuf','Reports','Print Product Specification','/PDFProdSpec.php',15),(4,'manuf','Reports','Select A Work Order','/SelectWorkOrder.php',1),(4,'manuf','Reports','Where Used Inquiry','/WhereUsedInquiry.php',3),(4,'manuf','Reports','WO Items ready to produce','/WOCanBeProducedNow.php',8),(4,'manuf','Transactions','QA Samples and Test Results','/SelectQASamples.php',3),(4,'manuf','Transactions','Select A Work Order','/SelectWorkOrder.php',2),(4,'manuf','Transactions','Timesheet Entry','/Timesheets.php',4),(4,'manuf','Transactions','Work Order Entry','/WorkOrderEntry.php?New=True',1),(4,'PC','Maintenance','Expenses for Type of PC Tab','/PcExpensesTypeTab.php',4),(4,'PC','Maintenance','PC Expenses','/PcExpenses.php',3),(4,'PC','Maintenance','PC Tabs','/PcTabs.php',2),(4,'PC','Maintenance','Types of PC Tabs','/PcTypeTabs.php',1),(4,'PC','Reports','PC Expense General Report','/PcReportExpense.php',2),(4,'PC','Reports','PC Expenses Analysis','/PcAnalysis.php',4),(4,'PC','Reports','PC Tab Expenses List','/PcTabExpensesList.php',3),(4,'PC','Reports','PC Tab General Report','/PcReportTab.php',1),(4,'PC','Transactions','Assign Cash to PC Tab','/PcAssignCashToTab.php',1),(4,'PC','Transactions','Authorise Assigned Cash','/PcAuthorizeCash.php',5),(4,'PC','Transactions','Authorise Expenses','/PcAuthorizeExpenses.php',4),(4,'PC','Transactions','Claim Expenses From PC Tab','/PcClaimExpensesFromTab.php',3),(4,'PC','Transactions','Transfer Assigned Cash Between PC Tabs','/PcAssignCashTabToTab.php',2),(4,'PO','Maintenance','Maintain Supplier Price Lists','/SupplierPriceList.php',1),(4,'PO','Reports','Purchase Order Detail Or Summary Inquiries','/POReport.php',2),(4,'PO','Reports','Purchase Order Inquiry','/PO_SelectPurchOrder.php',1),(4,'PO','Reports','Purchase Orders Financial Planning','/POFinancialPlanning.php',20),(4,'PO','Reports','Purchases from Suppliers','/PurchasesReport.php',4),(4,'PO','Reports','Supplier Price List','/SuppPriceList.php',3),(4,'PO','Transactions','Create a New Tender','/SupplierTenderCreate.php?New=Yes',4),(4,'PO','Transactions','Edit Existing Tenders','/SupplierTenderCreate.php?Edit=Yes',5),(4,'PO','Transactions','New Purchase Order','/PO_Header.php?NewOrder=Yes',1),(4,'PO','Transactions','Orders to Authorise','/PO_AuthoriseMyOrders.php',7),(4,'PO','Transactions','Process Tenders and Offers','/OffersReceived.php',6),(4,'PO','Transactions','Purchase Order Grid Entry','/PurchaseByPrefSupplier.php',3),(4,'PO','Transactions','Purchase Orders','/PO_SelectOSPurchOrder.php',2),(4,'PO','Transactions','Select A Shipment','/Shipt_Select.php',9),(4,'PO','Transactions','Shipment Entry','/SelectSupplier.php',8),(4,'Sales','Maintenance','Create Contract','/Contracts.php',1),(4,'Sales','Maintenance','Select Contract','/SelectContract.php',2),(4,'Sales','Maintenance','Sell Through Support Deals','/SellThroughSupport.php',3),(4,'Sales','Reports','Daily Sales Inquiry','/DailySalesInquiry.php',5),(4,'Sales','Reports','Delivery In Full On Time (DIFOT) Report','/PDFDIFOT.php',13),(4,'Sales','Reports','Order Delivery Differences Report','/PDFDeliveryDifferences.php',12),(4,'Sales','Reports','Order Status Report','/PDFOrderStatus.php',3),(4,'Sales','Reports','Orders Invoiced Reports','/PDFOrdersInvoiced.php',4),(4,'Sales','Reports','Print Price Lists','/PDFPriceList.php',2),(4,'Sales','Reports','Sales Analysis Reports','/SalesAnalRepts.php',9),(4,'Sales','Reports','Sales By Category By Item Inquiry','/StockCategorySalesInquiry.php',8),(4,'Sales','Reports','Sales By Category Inquiry','/SalesCategoryPeriodInquiry.php',7),(4,'Sales','Reports','Sales By Sales Type Inquiry','/SalesByTypePeriodInquiry.php',6),(4,'Sales','Reports','Sales Commission Reports','/SalesCommissionReports.php',21),(4,'Sales','Reports','Sales Graphs','/SalesGraph.php',10),(4,'Sales','Reports','Sales Order Detail Or Summary Inquiries','/SalesInquiry.php',14),(4,'Sales','Reports','Sales Order Inquiry','/SelectCompletedOrder.php',1),(4,'Sales','Reports','Sales to Customers','/SalesReport.php',20),(4,'Sales','Reports','Sales With Low Gross Profit Report','/PDFLowGP.php',18),(4,'Sales','Reports','Sell Through Support Claims Report','/PDFSellThroughSupportClaim.php',19),(4,'Sales','Reports','Top Customers Inquiry','/SalesTopCustomersInquiry.php',16),(4,'Sales','Reports','Top Sales Items Report','/TopItems.php',15),(4,'Sales','Reports','Top Sellers Inquiry','/SalesTopItemsInquiry.php',11),(4,'Sales','Reports','Worst Sales Items Report','/NoSalesItems.php',17),(4,'Sales','Transactions','Enter Counter Returns','/CounterReturns.php',3),(4,'Sales','Transactions','Enter Counter Sales','/CounterSales.php',2),(4,'Sales','Transactions','Generate/Print Picking Lists','/PDFPickingList.php',4),(4,'Sales','Transactions','Maintain Picking Lists','/SelectPickingLists.php',9),(4,'Sales','Transactions','New Sales Order or Quotation','/SelectOrderItems.php?NewOrder=Yes',1),(4,'Sales','Transactions','Outstanding Sales Orders/Quotations','/SelectSalesOrder.php',5),(4,'Sales','Transactions','Process Recurring Orders','/RecurringSalesOrdersProcess.php',8),(4,'Sales','Transactions','Recurring Order Template','/SelectRecurringSalesOrder.php',7),(4,'Sales','Transactions','Special Order','/SpecialOrder.php',6),(4,'stock','Maintenance','Add A New Item','/Stocks.php',1),(4,'stock','Maintenance','Add or Update Prices Based On Costs','/PricesBasedOnMarkUp.php',6),(4,'stock','Maintenance','Brands Maintenance','/Manufacturers.php',5),(4,'stock','Maintenance','Reorder Level By Category/Location','/ReorderLevelLocation.php',9),(4,'stock','Maintenance','Review Translated Descriptions','/RevisionTranslations.php',3),(4,'stock','Maintenance','Sales Category Maintenance','/SalesCategories.php',4),(4,'stock','Maintenance','Select An Item','/SelectProduct.php',2),(4,'stock','Maintenance','Upload new prices from csv file','/UploadPriceList.php',8),(4,'stock','Maintenance','View or Update Prices Based On Costs','/PricesByCost.php',7),(4,'stock','Reports','Aged Controlled Stock Report','/AgedControlledInventory.php',23),(4,'stock','Reports','All Inventory Movements By Location/Date','/StockLocMovements.php',17),(4,'stock','Reports','Compare Counts Vs Stock Check Data','/PDFStockCheckComparison.php',16),(4,'stock','Reports','Historical Stock Quantity By Location/Category','/StockQuantityByDate.php',19),(4,'stock','Reports','Internal stock request inquiry','/InternalStockRequestInquiry.php',24),(4,'stock','Reports','Inventory Item Movements','/StockMovements.php',4),(4,'stock','Reports','Inventory Item Status','/StockStatus.php',5),(4,'stock','Reports','Inventory Item Usage','/StockUsage.php',6),(4,'stock','Reports','Inventory Planning Based On Preferred Supplier Data','/InventoryPlanningPrefSupplier.php',13),(4,'stock','Reports','Inventory Planning Report','/InventoryPlanning.php',12),(4,'stock','Reports','Inventory Quantities','/InventoryQuantities.php',7),(4,'stock','Reports','Inventory Stock Check Sheets','/StockCheck.php',14),(4,'stock','Reports','Inventory Valuation Report','/InventoryValuation.php',10),(4,'stock','Reports','List Inventory Status By Location/Category','/StockLocStatus.php',18),(4,'stock','Reports','List Negative Stocks','/PDFStockNegatives.php',20),(4,'stock','Reports','Mail Inventory Valuation Report','/MailInventoryValuation.php',11),(4,'stock','Reports','Make Inventory Quantities CSV','/StockQties_csv.php',15),(4,'stock','Reports','Period Stock Transaction Listing','/PDFPeriodStockTransListing.php',21),(4,'stock','Reports','Print Price Labels','/PDFPrintLabel.php',2),(4,'stock','Reports','Reorder Level','/ReorderLevel.php',8),(4,'stock','Reports','Reprint GRN','/ReprintGRN.php',3),(4,'stock','Reports','Serial Item Research Tool','/StockSerialItemResearch.php',1),(4,'stock','Reports','Stock Dispatch','/StockDispatch.php',9),(4,'stock','Reports','Stock Transfer Note','/PDFStockTransfer.php',22),(4,'stock','Transactions','Authorise Internal Stock Requests','/InternalStockRequestAuthorisation.php',9),(4,'stock','Transactions','Bulk Inventory Transfer - Dispatch','/StockLocTransfer.php',3),(4,'stock','Transactions','Bulk Inventory Transfer - Receive','/StockLocTransferReceive.php',4),(4,'stock','Transactions','Create a New Internal Stock Request','/InternalStockRequest.php?New=Yes',8),(4,'stock','Transactions','Enter Stock Counts','/StockCounts.php',7),(4,'stock','Transactions','Fulfil Internal Stock Requests','/InternalStockRequestFulfill.php',10),(4,'stock','Transactions','Inventory Adjustments','/StockAdjustments.php?NewAdjustment=Yes',5),(4,'stock','Transactions','Inventory Location Transfers','/StockTransfers.php?New=Yes',2),(4,'stock','Transactions','Receive Purchase Orders','/PO_SelectOSPurchOrder.php',1),(4,'stock','Transactions','Reverse Goods Received','/ReverseGRN.php',6),(4,'system','Maintenance','Copy Authority Locations from one user to another','/LocationUsersCopyAuthority.php',20),(4,'system','Maintenance','Dashboard Configuration','/DashboardConfig.php',13),(4,'system','Maintenance','Discount Category Maintenance','/DiscountCategories.php',5),(4,'system','Maintenance','Inventory Categories Maintenance','/StockCategories.php',1),(4,'system','Maintenance','Inventory Location Authorised Users Maintenance','/LocationUsers.php',3),(4,'system','Maintenance','Inventory Locations Maintenance','/Locations.php',2),(4,'system','Maintenance','Label Templates Maintenance','/Labels.php',12),(4,'system','Maintenance','Logged in users','/LoggedInUsers.php',8),(4,'system','Maintenance','Maintain Internal Departments','/Departments.php',10),(4,'system','Maintenance','Maintain Internal Stock Categories to User Roles','/InternalStockCategoriesByRole.php',11),(4,'system','Maintenance','MRP Available Production Days','/MRPCalendar.php',7),(4,'system','Maintenance','MRP Demand Types','/MRPDemandTypes.php',9),(4,'system','Maintenance','Units of Measure','/UnitsOfMeasure.php',6),(4,'system','Maintenance','User Authorised Inventory Locations Maintenance','/UserLocations.php',4),(4,'system','Reports','COGS GL Interface Postings','/COGSGLPostings.php',12),(4,'system','Reports','Credit Status','/CreditStatus.php',4),(4,'system','Reports','Customer Types','/CustomerTypes.php',2),(4,'system','Reports','Discount Matrix','/DiscountMatrix.php',14),(4,'system','Reports','Freight Costs Maintenance','/FreightCosts.php',13),(4,'system','Reports','Payment Methods','/PaymentMethods.php',7),(4,'system','Reports','Payment Terms','/PaymentTerms.php',5),(4,'system','Reports','Sales Areas','/Areas.php',9),(4,'system','Reports','Sales Commission Types','/SalesCommissionTypes.php',15),(4,'system','Reports','Sales GL Interface Postings','/SalesGLPostings.php',11),(4,'system','Reports','Sales People','/SalesPeople.php',8),(4,'system','Reports','Sales Types','/SalesTypes.php',1),(4,'system','Reports','Set Purchase Order Authorisation levels','/PO_AuthorisationLevels.php',6),(4,'system','Reports','Shippers','/Shippers.php',10),(4,'system','Reports','Supplier Types','/SupplierTypes.php',3),(4,'system','Transactions','Access Permissions Maintenance','/WWW_Access.php',5),(4,'system','Transactions','Company Preferences','/CompanyPreferences.php',1),(4,'system','Transactions','Currency Maintenance','/Currencies.php',7),(4,'system','Transactions','Dispatch Tax Province Maintenance','/TaxProvinces.php',10),(4,'system','Transactions','Form Designer','/FormDesigner.php',16),(4,'system','Transactions','Geocode Maintenance','/GeocodeSetup.php',15),(4,'system','Transactions','List Periods Defined','/PeriodsInquiry.php',12),(4,'system','Transactions','Mailing Group Maintenance','/MailingGroupMaintenance.php',19),(4,'system','Transactions','Maintain Security Tokens','/SecurityTokens.php',4),(4,'system','Transactions','Page Security Settings','/PageSecurity.php',6),(4,'system','Transactions','Report Builder Tool','/reportwriter/admin/ReportCreator.php',13),(4,'system','Transactions','SMTP Server Details','/SMTPServer.php',18),(4,'system','Transactions','System Parameters','/SystemParameters.php',2),(4,'system','Transactions','Tax Authorities and Rates Maintenance','/TaxAuthorities.php',8),(4,'system','Transactions','Tax Category Maintenance','/TaxCategories.php',11),(4,'system','Transactions','Tax Group Maintenance','/TaxGroups.php',9),(4,'system','Transactions','Users Maintenance','/WWW_Users.php',3),(4,'system','Transactions','View Audit Trail','/AuditTrail.php',14),(4,'system','Transactions','Web-Store Configuration','/ShopParameters.php',17),(4,'Utilities','Maintenance','Create new company template SQL file and submit to webERP','/Z_CreateCompanyTemplateFile.php',9),(4,'Utilities','Maintenance','Data Export Options','/Z_DataExport.php',3),(4,'Utilities','Maintenance','Import Customers from .csv file','/Z_ImportDebtors.php',4),(4,'Utilities','Maintenance','Import Fixed Assets from .csv file','/Z_ImportFixedAssets.php',7),(4,'Utilities','Maintenance','Import GL Payments Receipts Or Journals From .csv file','/Z_ImportGLTransactions.php',8),(4,'Utilities','Maintenance','Import Price List from .csv file','/Z_ImportPriceList.php',6),(4,'Utilities','Maintenance','Import Stock Items from .csv','/Z_ImportStocks.php',5),(4,'Utilities','Maintenance','Maintain Language Files','/Z_poAdmin.php',1),(4,'Utilities','Maintenance','Make New Company','/Z_MakeNewCompany.php',2),(4,'Utilities','Maintenance','Purge all old prices','/Z_DeleteOldPrices.php',12),(4,'Utilities','Maintenance','Remove all purchase back orders','/Z_RemovePurchaseBackOrders.php',13),(4,'Utilities','Reports','Debtors Balances By Currency Totals','/Z_CurrencyDebtorsBalances.php',1),(4,'Utilities','Reports','List of items without picture','/Z_ItemsWithoutPicture.php',4),(4,'Utilities','Reports','Show General Transactions That Do Not Balance','/Z_CheckGLTransBalance.php',3),(4,'Utilities','Reports','Suppliers Balances By Currency Totals','/Z_CurrencySuppliersBalances.php',2),(4,'Utilities','Transactions','Change A Customer Branch Code','/Z_ChangeBranchCode.php',2),(4,'Utilities','Transactions','Change A Customer Code','/Z_ChangeCustomerCode.php',1),(4,'Utilities','Transactions','Change A GL Account Code','/Z_ChangeGLAccountCode.php',3),(4,'Utilities','Transactions','Change A Location Code','/Z_ChangeLocationCode.php',5),(4,'Utilities','Transactions','Change A Salesman Code','/Z_ChangeSalesmanCode.php',6),(4,'Utilities','Transactions','Change A Stock Category Code','/Z_ChangeStockCategory.php',7),(4,'Utilities','Transactions','Change A Supplier Code','/Z_ChangeSupplierCode.php',8),(4,'Utilities','Transactions','Change An Inventory Item Code','/Z_ChangeStockCode.php',4),(4,'Utilities','Transactions','Copy Authority of GL Accounts from one user to another','/Z_GLAccountUsersCopyAuthority.php',15),(4,'Utilities','Transactions','Delete sales transactions','/Z_DeleteSalesTransActions.php',12),(4,'Utilities','Transactions','Re-apply costs to Sales Analysis','/Z_ReApplyCostToSA.php',11),(4,'Utilities','Transactions','Reverse all supplier payments on a specified date','/Z_ReverseSuppPaymentRun.php',13),(4,'Utilities','Transactions','Translate Item Descriptions','/AutomaticTranslationDescriptions.php',9),(4,'Utilities','Transactions','Update costs for all BOM items, from the bottom up','/Z_BottomUpCosts.php',10),(4,'Utilities','Transactions','Update sales analysis with latest customer data','/Z_UpdateSalesAnalysisWithLatestCustomerData.php',14),(5,'AP','Maintenance','Add Supplier','/Suppliers.php',1),(5,'AP','Maintenance','Maintain Factor Companies','/Factors.php',3),(5,'AP','Maintenance','Select Supplier','/SelectSupplier.php',2),(5,'AP','Reports','Aged Supplier Report','/AgedSuppliers.php',2),(5,'AP','Reports','List Daily Transactions','/PDFSuppTransListing.php',7),(5,'AP','Reports','Outstanding GRNs Report','/OutstandingGRNs.php',5),(5,'AP','Reports','Payment Run Report','/SuppPaymentRun.php',3),(5,'AP','Reports','Remittance Advices','/PDFRemittanceAdvice.php',4),(5,'AP','Reports','Supplier Balances At A Prior Month End','/SupplierBalsAtPeriodEnd.php',6),(5,'AP','Reports','Supplier Transaction Inquiries','/SupplierTransInquiry.php',8),(5,'AP','Reports','Where Allocated Inquiry','/SuppWhereAlloc.php',1),(5,'AP','Transactions','Select Supplier','/SelectSupplier.php',1),(5,'AP','Transactions','Supplier Allocations','/SupplierAllocations.php',2),(5,'AR','Maintenance','Add Customer','/Customers.php',1),(5,'AR','Maintenance','Select Customer','/SelectCustomer.php',2),(5,'AR','Reports','Aged Customer Balances/Overdues Report','/AgedDebtors.php',4),(5,'AR','Reports','Customer Activity and Balances','/CustomerBalancesMovement.php',10),(5,'AR','Reports','Customer Listing By Area/Salesperson','/PDFCustomerList.php',7),(5,'AR','Reports','Customer Transaction Inquiries','/CustomerTransInquiry.php',9),(5,'AR','Reports','Debtor Balances At A Prior Month End','/DebtorsAtPeriodEnd.php',6),(5,'AR','Reports','List Daily Transactions','/PDFCustTransListing.php',8),(5,'AR','Reports','Print Invoices or Credit Notes','/PrintCustTrans.php',2),(5,'AR','Reports','Print Statements','/PrintCustStatements.php',3),(5,'AR','Reports','Re-Print A Deposit Listing','/PDFBankingSummary.php',5),(5,'AR','Reports','Where Allocated Inquiry','/CustWhereAlloc.php',1),(5,'AR','Transactions','Allocate Receipts or Credit Notes','/CustomerAllocations.php',4),(5,'AR','Transactions','Create A Credit Note','/SelectCreditItems.php?NewCredit=Yes',2),(5,'AR','Transactions','Enter Receipts','/CustomerReceipt.php?NewReceipt=Yes&amp;Type=Customer',3),(5,'AR','Transactions','Select Order to Invoice','/SelectSalesOrder.php',1),(5,'FA','Maintenance','Add or Maintain Asset Locations','/FixedAssetLocations.php',2),(5,'FA','Maintenance','Fixed Asset Category Maintenance','/FixedAssetCategories.php',1),(5,'FA','Maintenance','Fixed Asset Maintenance Tasks','/MaintenanceTasks.php',3),(5,'FA','Reports','Asset Register','/FixedAssetRegister.php',1),(5,'FA','Reports','Maintenance Reminder Emails','/MaintenanceReminders.php',3),(5,'FA','Reports','My Maintenance Schedule','/MaintenanceUserSchedule.php',2),(5,'FA','Transactions','Add a new Asset','/FixedAssetItems.php',1),(5,'FA','Transactions','Change Asset Location','/FixedAssetTransfer.php',3),(5,'FA','Transactions','Depreciation Journal','/FixedAssetDepreciation.php',4),(5,'FA','Transactions','Select an Asset','/SelectAsset.php',2),(5,'GL','Maintenance','Account Groups','/AccountGroups.php',4),(5,'GL','Maintenance','Account Sections','/AccountSections.php',1),(5,'GL','Maintenance','Bank Account Authorised Users','/BankAccountUsers.php',11),(5,'GL','Maintenance','Bank Accounts','/BankAccounts.php',10),(5,'GL','Maintenance','Copy Authority Bank Accounts from one user to another','/GLBankAccountUsersCopyAuthority.php',21),(5,'GL','Maintenance','Copy Authority GL Accounts from one user to another','/GLAccountUsersCopyAuthority.php',20),(5,'GL','Maintenance','Create Budget Amounts','/GLBudgets.php',3),(5,'GL','Maintenance','Create/Amend General Ledger Budgets','/GLBudgetHeaders.php',2),(5,'GL','Maintenance','GL Account','/GLAccounts.php',5),(5,'GL','Maintenance','GL Account Authorised Users','/GLAccountUsers.php',6),(5,'GL','Maintenance','GL Tags','/GLTags.php',9),(5,'GL','Maintenance','Maintain Journal Templates','/GLJournalTemplates.php',13),(5,'GL','Maintenance','Setup Regular Payments','/RegularPaymentsSetup.php',14),(5,'GL','Maintenance','User Authorised Bank Accounts','/UserBankAccounts.php',12),(5,'GL','Maintenance','User Authorised GL Accounts','/UserGLAccounts.php',7),(5,'GL','Reports','Account Inquiry','/SelectGLAccount.php',5),(5,'GL','Reports','Account Listing','/GLAccountReport.php',7),(5,'GL','Reports','Account Listing to CSV File','/GLAccountCSV.php',8),(5,'GL','Reports','Balance Sheet','/GLBalanceSheet.php',11),(5,'GL','Reports','Bank Account Balances','/BankAccountBalances.php',1),(5,'GL','Reports','Bank Account Reconciliation Statement','/BankReconciliation.php',2),(5,'GL','Reports','Cheque Payments Listing','/PDFChequeListing.php',3),(5,'GL','Reports','Daily Bank Transactions','/DailyBankTransactions.php',4),(5,'GL','Reports','Financial Statements','/GLStatements.php',14),(5,'GL','Reports','General Ledger Journal Inquiry','/GLJournalInquiry.php',9),(5,'GL','Reports','Graph of Account Transactions','/GLAccountGraph.php',6),(5,'GL','Reports','Horizontal Analysis of Statement of Comprehensive Income','/AnalysisHorizontalIncome.php',16),(5,'GL','Reports','Horizontal Analysis of Statement of Financial Position','/AnalysisHorizontalPosition.php',15),(5,'GL','Reports','Income and Expenditure by Tag','/GLTagProfit_Loss.php',17),(5,'GL','Reports','Profit and Loss Statement','/GLProfit_Loss.php',12),(5,'GL','Reports','Statement of Cash Flows','/GLCashFlowsIndirect.php',13),(5,'GL','Reports','Tax Reports','/Tax.php',19),(5,'GL','Reports','Trial Balance','/GLTrialBalance.php',10),(5,'GL','Transactions','Bank Account Payments Entry','/Payments.php?NewPayment=Yes',1),(5,'GL','Transactions','Bank Account Payments Matching','/BankMatching.php?Type=Payments',4),(5,'GL','Transactions','Bank Account Receipts Entry','/CustomerReceipt.php?NewReceipt=Yes&amp;Type=GL',2),(5,'GL','Transactions','Bank Account Receipts Matching','/BankMatching.php?Type=Receipts',5),(5,'GL','Transactions','Journal Entry','/GLJournal.php?NewJournal=Yes',3),(5,'GL','Transactions','Import Bank Transactions','/ImportBankTrans.php',6),(5,'GL','Transactions','Process Regular Payments','/RegularPaymentsProcess.php',7),(5,'manuf','Maintenance','Auto Create Master Schedule','/MRPCreateDemands.php',5),(5,'manuf','Maintenance','Bills Of Material','/BOMs.php',2),(5,'manuf','Maintenance','Copy a Bill Of Materials Between Items','/CopyBOM.php',3),(5,'manuf','Maintenance','Employees','/Employees.php',9),(5,'manuf','Maintenance','Master Schedule','/MRPDemands.php',4),(5,'manuf','Maintenance','MRP Calculation','/MRP.php',6),(5,'manuf','Maintenance','Product Specifications','/ProductSpecs.php',8),(5,'manuf','Maintenance','Quality Tests Maintenance','/QATests.php',7),(5,'manuf','Maintenance','Work Centre','/WorkCentres.php',1),(5,'manuf','Reports','Bill Of Material Listing','/BOMListing.php',4),(5,'manuf','Reports','Costed Bill Of Material Inquiry','/BOMInquiry.php',2),(5,'manuf','Reports','Historical QA Test Results','/HistoricalTestResults.php',17),(5,'manuf','Reports','Indented Bill Of Material Listing','/BOMIndented.php',5),(5,'manuf','Reports','Indented Where Used Listing','/BOMIndentedReverse.php',9),(5,'manuf','Reports','List Components Required','/BOMExtendedQty.php',6),(5,'manuf','Reports','List Materials Not Used anywhere','/MaterialsNotUsed.php',7),(5,'manuf','Reports','MRP','/MRPReport.php',10),(5,'manuf','Reports','MRP Reschedules Required','/MRPReschedules.php',14),(5,'manuf','Reports','MRP Shortages','/MRPShortages.php',11),(5,'manuf','Reports','MRP Suggested Purchase Orders','/MRPPlannedPurchaseOrders.php',12),(5,'manuf','Reports','MRP Suggested Work Orders','/MRPPlannedWorkOrders.php',13),(5,'manuf','Reports','Multiple Work Orders Total Cost Inquiry','/CollectiveWorkOrderCost.php',18),(5,'manuf','Reports','Print Certificate of Analysis','/PDFCOA.php',16),(5,'manuf','Reports','Print Product Specification','/PDFProdSpec.php',15),(5,'manuf','Reports','Select A Work Order','/SelectWorkOrder.php',1),(5,'manuf','Reports','Where Used Inquiry','/WhereUsedInquiry.php',3),(5,'manuf','Reports','WO Items ready to produce','/WOCanBeProducedNow.php',8),(5,'manuf','Transactions','QA Samples and Test Results','/SelectQASamples.php',3),(5,'manuf','Transactions','Select A Work Order','/SelectWorkOrder.php',2),(5,'manuf','Transactions','Timesheet Entry','/Timesheets.php',4),(5,'manuf','Transactions','Work Order Entry','/WorkOrderEntry.php?New=True',1),(5,'PC','Maintenance','Expenses for Type of PC Tab','/PcExpensesTypeTab.php',4),(5,'PC','Maintenance','PC Expenses','/PcExpenses.php',3),(5,'PC','Maintenance','PC Tabs','/PcTabs.php',2),(5,'PC','Maintenance','Types of PC Tabs','/PcTypeTabs.php',1),(5,'PC','Reports','PC Expense General Report','/PcReportExpense.php',2),(5,'PC','Reports','PC Expenses Analysis','/PcAnalysis.php',4),(5,'PC','Reports','PC Tab Expenses List','/PcTabExpensesList.php',3),(5,'PC','Reports','PC Tab General Report','/PcReportTab.php',1),(5,'PC','Transactions','Assign Cash to PC Tab','/PcAssignCashToTab.php',1),(5,'PC','Transactions','Authorise Assigned Cash','/PcAuthorizeCash.php',5),(5,'PC','Transactions','Authorise Expenses','/PcAuthorizeExpenses.php',4),(5,'PC','Transactions','Claim Expenses From PC Tab','/PcClaimExpensesFromTab.php',3),(5,'PC','Transactions','Transfer Assigned Cash Between PC Tabs','/PcAssignCashTabToTab.php',2),(5,'PO','Maintenance','Maintain Supplier Price Lists','/SupplierPriceList.php',1),(5,'PO','Reports','Purchase Order Detail Or Summary Inquiries','/POReport.php',2),(5,'PO','Reports','Purchase Order Inquiry','/PO_SelectPurchOrder.php',1),(5,'PO','Reports','Purchase Orders Financial Planning','/POFinancialPlanning.php',20),(5,'PO','Reports','Purchases from Suppliers','/PurchasesReport.php',4),(5,'PO','Reports','Supplier Price List','/SuppPriceList.php',3),(5,'PO','Transactions','Create a New Tender','/SupplierTenderCreate.php?New=Yes',4),(5,'PO','Transactions','Edit Existing Tenders','/SupplierTenderCreate.php?Edit=Yes',5),(5,'PO','Transactions','New Purchase Order','/PO_Header.php?NewOrder=Yes',1),(5,'PO','Transactions','Orders to Authorise','/PO_AuthoriseMyOrders.php',7),(5,'PO','Transactions','Process Tenders and Offers','/OffersReceived.php',6),(5,'PO','Transactions','Purchase Order Grid Entry','/PurchaseByPrefSupplier.php',3),(5,'PO','Transactions','Purchase Orders','/PO_SelectOSPurchOrder.php',2),(5,'PO','Transactions','Select A Shipment','/Shipt_Select.php',9),(5,'PO','Transactions','Shipment Entry','/SelectSupplier.php',8),(5,'Sales','Maintenance','Create Contract','/Contracts.php',1),(5,'Sales','Maintenance','Select Contract','/SelectContract.php',2),(5,'Sales','Maintenance','Sell Through Support Deals','/SellThroughSupport.php',3),(5,'Sales','Reports','Daily Sales Inquiry','/DailySalesInquiry.php',5),(5,'Sales','Reports','Delivery In Full On Time (DIFOT) Report','/PDFDIFOT.php',13),(5,'Sales','Reports','Order Delivery Differences Report','/PDFDeliveryDifferences.php',12),(5,'Sales','Reports','Order Status Report','/PDFOrderStatus.php',3),(5,'Sales','Reports','Orders Invoiced Reports','/PDFOrdersInvoiced.php',4),(5,'Sales','Reports','Print Price Lists','/PDFPriceList.php',2),(5,'Sales','Reports','Sales Analysis Reports','/SalesAnalRepts.php',9),(5,'Sales','Reports','Sales By Category By Item Inquiry','/StockCategorySalesInquiry.php',8),(5,'Sales','Reports','Sales By Category Inquiry','/SalesCategoryPeriodInquiry.php',7),(5,'Sales','Reports','Sales By Sales Type Inquiry','/SalesByTypePeriodInquiry.php',6),(5,'Sales','Reports','Sales Commission Reports','/SalesCommissionReports.php',21),(5,'Sales','Reports','Sales Graphs','/SalesGraph.php',10),(5,'Sales','Reports','Sales Order Detail Or Summary Inquiries','/SalesInquiry.php',14),(5,'Sales','Reports','Sales Order Inquiry','/SelectCompletedOrder.php',1),(5,'Sales','Reports','Sales to Customers','/SalesReport.php',20),(5,'Sales','Reports','Sales With Low Gross Profit Report','/PDFLowGP.php',18),(5,'Sales','Reports','Sell Through Support Claims Report','/PDFSellThroughSupportClaim.php',19),(5,'Sales','Reports','Top Customers Inquiry','/SalesTopCustomersInquiry.php',16),(5,'Sales','Reports','Top Sales Items Report','/TopItems.php',15),(5,'Sales','Reports','Top Sellers Inquiry','/SalesTopItemsInquiry.php',11),(5,'Sales','Reports','Worst Sales Items Report','/NoSalesItems.php',17),(5,'Sales','Transactions','Enter Counter Returns','/CounterReturns.php',3),(5,'Sales','Transactions','Enter Counter Sales','/CounterSales.php',2),(5,'Sales','Transactions','Generate/Print Picking Lists','/PDFPickingList.php',4),(5,'Sales','Transactions','Maintain Picking Lists','/SelectPickingLists.php',9),(5,'Sales','Transactions','New Sales Order or Quotation','/SelectOrderItems.php?NewOrder=Yes',1),(5,'Sales','Transactions','Outstanding Sales Orders/Quotations','/SelectSalesOrder.php',5),(5,'Sales','Transactions','Process Recurring Orders','/RecurringSalesOrdersProcess.php',8),(5,'Sales','Transactions','Recurring Order Template','/SelectRecurringSalesOrder.php',7),(5,'Sales','Transactions','Special Order','/SpecialOrder.php',6),(5,'stock','Maintenance','Add A New Item','/Stocks.php',1),(5,'stock','Maintenance','Add or Update Prices Based On Costs','/PricesBasedOnMarkUp.php',6),(5,'stock','Maintenance','Brands Maintenance','/Manufacturers.php',5),(5,'stock','Maintenance','Reorder Level By Category/Location','/ReorderLevelLocation.php',9),(5,'stock','Maintenance','Review Translated Descriptions','/RevisionTranslations.php',3),(5,'stock','Maintenance','Sales Category Maintenance','/SalesCategories.php',4),(5,'stock','Maintenance','Select An Item','/SelectProduct.php',2),(5,'stock','Maintenance','Upload new prices from csv file','/UploadPriceList.php',8),(5,'stock','Maintenance','View or Update Prices Based On Costs','/PricesByCost.php',7),(5,'stock','Reports','Aged Controlled Stock Report','/AgedControlledInventory.php',23),(5,'stock','Reports','All Inventory Movements By Location/Date','/StockLocMovements.php',17),(5,'stock','Reports','Compare Counts Vs Stock Check Data','/PDFStockCheckComparison.php',16),(5,'stock','Reports','Historical Stock Quantity By Location/Category','/StockQuantityByDate.php',19),(5,'stock','Reports','Internal stock request inquiry','/InternalStockRequestInquiry.php',24),(5,'stock','Reports','Inventory Item Movements','/StockMovements.php',4),(5,'stock','Reports','Inventory Item Status','/StockStatus.php',5),(5,'stock','Reports','Inventory Item Usage','/StockUsage.php',6),(5,'stock','Reports','Inventory Planning Based On Preferred Supplier Data','/InventoryPlanningPrefSupplier.php',13),(5,'stock','Reports','Inventory Planning Report','/InventoryPlanning.php',12),(5,'stock','Reports','Inventory Quantities','/InventoryQuantities.php',7),(5,'stock','Reports','Inventory Stock Check Sheets','/StockCheck.php',14),(5,'stock','Reports','Inventory Valuation Report','/InventoryValuation.php',10),(5,'stock','Reports','List Inventory Status By Location/Category','/StockLocStatus.php',18),(5,'stock','Reports','List Negative Stocks','/PDFStockNegatives.php',20),(5,'stock','Reports','Mail Inventory Valuation Report','/MailInventoryValuation.php',11),(5,'stock','Reports','Make Inventory Quantities CSV','/StockQties_csv.php',15),(5,'stock','Reports','Period Stock Transaction Listing','/PDFPeriodStockTransListing.php',21),(5,'stock','Reports','Print Price Labels','/PDFPrintLabel.php',2),(5,'stock','Reports','Reorder Level','/ReorderLevel.php',8),(5,'stock','Reports','Reprint GRN','/ReprintGRN.php',3),(5,'stock','Reports','Serial Item Research Tool','/StockSerialItemResearch.php',1),(5,'stock','Reports','Stock Dispatch','/StockDispatch.php',9),(5,'stock','Reports','Stock Transfer Note','/PDFStockTransfer.php',22),(5,'stock','Transactions','Authorise Internal Stock Requests','/InternalStockRequestAuthorisation.php',9),(5,'stock','Transactions','Bulk Inventory Transfer - Dispatch','/StockLocTransfer.php',3),(5,'stock','Transactions','Bulk Inventory Transfer - Receive','/StockLocTransferReceive.php',4),(5,'stock','Transactions','Create a New Internal Stock Request','/InternalStockRequest.php?New=Yes',8),(5,'stock','Transactions','Enter Stock Counts','/StockCounts.php',7),(5,'stock','Transactions','Fulfil Internal Stock Requests','/InternalStockRequestFulfill.php',10),(5,'stock','Transactions','Inventory Adjustments','/StockAdjustments.php?NewAdjustment=Yes',5),(5,'stock','Transactions','Inventory Location Transfers','/StockTransfers.php?New=Yes',2),(5,'stock','Transactions','Receive Purchase Orders','/PO_SelectOSPurchOrder.php',1),(5,'stock','Transactions','Reverse Goods Received','/ReverseGRN.php',6),(5,'system','Maintenance','Copy Authority Locations from one user to another','/LocationUsersCopyAuthority.php',20),(5,'system','Maintenance','Dashboard Configuration','/DashboardConfig.php',13),(5,'system','Maintenance','Discount Category Maintenance','/DiscountCategories.php',5),(5,'system','Maintenance','Inventory Categories Maintenance','/StockCategories.php',1),(5,'system','Maintenance','Inventory Location Authorised Users Maintenance','/LocationUsers.php',3),(5,'system','Maintenance','Inventory Locations Maintenance','/Locations.php',2),(5,'system','Maintenance','Label Templates Maintenance','/Labels.php',12),(5,'system','Maintenance','Logged in users','/LoggedInUsers.php',8),(5,'system','Maintenance','Maintain Internal Departments','/Departments.php',10),(5,'system','Maintenance','Maintain Internal Stock Categories to User Roles','/InternalStockCategoriesByRole.php',11),(5,'system','Maintenance','MRP Available Production Days','/MRPCalendar.php',7),(5,'system','Maintenance','MRP Demand Types','/MRPDemandTypes.php',9),(5,'system','Maintenance','Units of Measure','/UnitsOfMeasure.php',6),(5,'system','Maintenance','User Authorised Inventory Locations Maintenance','/UserLocations.php',4),(5,'system','Reports','COGS GL Interface Postings','/COGSGLPostings.php',12),(5,'system','Reports','Credit Status','/CreditStatus.php',4),(5,'system','Reports','Customer Types','/CustomerTypes.php',2),(5,'system','Reports','Discount Matrix','/DiscountMatrix.php',14),(5,'system','Reports','Freight Costs Maintenance','/FreightCosts.php',13),(5,'system','Reports','Payment Methods','/PaymentMethods.php',7),(5,'system','Reports','Payment Terms','/PaymentTerms.php',5),(5,'system','Reports','Sales Areas','/Areas.php',9),(5,'system','Reports','Sales Commission Types','/SalesCommissionTypes.php',15),(5,'system','Reports','Sales GL Interface Postings','/SalesGLPostings.php',11),(5,'system','Reports','Sales People','/SalesPeople.php',8),(5,'system','Reports','Sales Types','/SalesTypes.php',1),(5,'system','Reports','Set Purchase Order Authorisation levels','/PO_AuthorisationLevels.php',6),(5,'system','Reports','Shippers','/Shippers.php',10),(5,'system','Reports','Supplier Types','/SupplierTypes.php',3),(5,'system','Transactions','Access Permissions Maintenance','/WWW_Access.php',5),(5,'system','Transactions','Company Preferences','/CompanyPreferences.php',1),(5,'system','Transactions','Currency Maintenance','/Currencies.php',7),(5,'system','Transactions','Dispatch Tax Province Maintenance','/TaxProvinces.php',10),(5,'system','Transactions','Form Designer','/FormDesigner.php',16),(5,'system','Transactions','Geocode Maintenance','/GeocodeSetup.php',15),(5,'system','Transactions','List Periods Defined','/PeriodsInquiry.php',12),(5,'system','Transactions','Mailing Group Maintenance','/MailingGroupMaintenance.php',19),(5,'system','Transactions','Maintain Security Tokens','/SecurityTokens.php',4),(5,'system','Transactions','Page Security Settings','/PageSecurity.php',6),(5,'system','Transactions','Report Builder Tool','/reportwriter/admin/ReportCreator.php',13),(5,'system','Transactions','SMTP Server Details','/SMTPServer.php',18),(5,'system','Transactions','System Parameters','/SystemParameters.php',2),(5,'system','Transactions','Tax Authorities and Rates Maintenance','/TaxAuthorities.php',8),(5,'system','Transactions','Tax Category Maintenance','/TaxCategories.php',11),(5,'system','Transactions','Tax Group Maintenance','/TaxGroups.php',9),(5,'system','Transactions','Users Maintenance','/WWW_Users.php',3),(5,'system','Transactions','View Audit Trail','/AuditTrail.php',14),(5,'system','Transactions','Web-Store Configuration','/ShopParameters.php',17),(5,'Utilities','Maintenance','Create new company template SQL file and submit to webERP','/Z_CreateCompanyTemplateFile.php',9),(5,'Utilities','Maintenance','Data Export Options','/Z_DataExport.php',3),(5,'Utilities','Maintenance','Import Customers from .csv file','/Z_ImportDebtors.php',4),(5,'Utilities','Maintenance','Import Fixed Assets from .csv file','/Z_ImportFixedAssets.php',7),(5,'Utilities','Maintenance','Import GL Payments Receipts Or Journals From .csv file','/Z_ImportGLTransactions.php',8),(5,'Utilities','Maintenance','Import Price List from .csv file','/Z_ImportPriceList.php',6),(5,'Utilities','Maintenance','Import Stock Items from .csv','/Z_ImportStocks.php',5),(5,'Utilities','Maintenance','Maintain Language Files','/Z_poAdmin.php',1),(5,'Utilities','Maintenance','Make New Company','/Z_MakeNewCompany.php',2),(5,'Utilities','Maintenance','Purge all old prices','/Z_DeleteOldPrices.php',12),(5,'Utilities','Maintenance','Remove all purchase back orders','/Z_RemovePurchaseBackOrders.php',13),(5,'Utilities','Reports','Debtors Balances By Currency Totals','/Z_CurrencyDebtorsBalances.php',1),(5,'Utilities','Reports','List of items without picture','/Z_ItemsWithoutPicture.php',4),(5,'Utilities','Reports','Show General Transactions That Do Not Balance','/Z_CheckGLTransBalance.php',3),(5,'Utilities','Reports','Suppliers Balances By Currency Totals','/Z_CurrencySuppliersBalances.php',2),(5,'Utilities','Transactions','Change A Customer Branch Code','/Z_ChangeBranchCode.php',2),(5,'Utilities','Transactions','Change A Customer Code','/Z_ChangeCustomerCode.php',1),(5,'Utilities','Transactions','Change A GL Account Code','/Z_ChangeGLAccountCode.php',3),(5,'Utilities','Transactions','Change A Location Code','/Z_ChangeLocationCode.php',5),(5,'Utilities','Transactions','Change A Salesman Code','/Z_ChangeSalesmanCode.php',6),(5,'Utilities','Transactions','Change A Stock Category Code','/Z_ChangeStockCategory.php',7),(5,'Utilities','Transactions','Change A Supplier Code','/Z_ChangeSupplierCode.php',8),(5,'Utilities','Transactions','Change An Inventory Item Code','/Z_ChangeStockCode.php',4),(5,'Utilities','Transactions','Copy Authority of GL Accounts from one user to another','/Z_GLAccountUsersCopyAuthority.php',15),(5,'Utilities','Transactions','Delete sales transactions','/Z_DeleteSalesTransActions.php',12),(5,'Utilities','Transactions','Re-apply costs to Sales Analysis','/Z_ReApplyCostToSA.php',11),(5,'Utilities','Transactions','Reverse all supplier payments on a specified date','/Z_ReverseSuppPaymentRun.php',13),(5,'Utilities','Transactions','Translate Item Descriptions','/AutomaticTranslationDescriptions.php',9),(5,'Utilities','Transactions','Update costs for all BOM items, from the bottom up','/Z_BottomUpCosts.php',10),(5,'Utilities','Transactions','Update sales analysis with latest customer data','/Z_UpdateSalesAnalysisWithLatestCustomerData.php',14),(6,'AP','Maintenance','Add Supplier','/Suppliers.php',1),(6,'AP','Maintenance','Maintain Factor Companies','/Factors.php',3),(6,'AP','Maintenance','Select Supplier','/SelectSupplier.php',2),(6,'AP','Reports','Aged Supplier Report','/AgedSuppliers.php',2),(6,'AP','Reports','List Daily Transactions','/PDFSuppTransListing.php',7),(6,'AP','Reports','Outstanding GRNs Report','/OutstandingGRNs.php',5),(6,'AP','Reports','Payment Run Report','/SuppPaymentRun.php',3),(6,'AP','Reports','Remittance Advices','/PDFRemittanceAdvice.php',4),(6,'AP','Reports','Supplier Balances At A Prior Month End','/SupplierBalsAtPeriodEnd.php',6),(6,'AP','Reports','Supplier Transaction Inquiries','/SupplierTransInquiry.php',8),(6,'AP','Reports','Where Allocated Inquiry','/SuppWhereAlloc.php',1),(6,'AP','Transactions','Select Supplier','/SelectSupplier.php',1),(6,'AP','Transactions','Supplier Allocations','/SupplierAllocations.php',2),(6,'AR','Maintenance','Add Customer','/Customers.php',1),(6,'AR','Maintenance','Select Customer','/SelectCustomer.php',2),(6,'AR','Reports','Aged Customer Balances/Overdues Report','/AgedDebtors.php',4),(6,'AR','Reports','Customer Activity and Balances','/CustomerBalancesMovement.php',10),(6,'AR','Reports','Customer Listing By Area/Salesperson','/PDFCustomerList.php',7),(6,'AR','Reports','Customer Transaction Inquiries','/CustomerTransInquiry.php',9),(6,'AR','Reports','Debtor Balances At A Prior Month End','/DebtorsAtPeriodEnd.php',6),(6,'AR','Reports','List Daily Transactions','/PDFCustTransListing.php',8),(6,'AR','Reports','Print Invoices or Credit Notes','/PrintCustTrans.php',2),(6,'AR','Reports','Print Statements','/PrintCustStatements.php',3),(6,'AR','Reports','Re-Print A Deposit Listing','/PDFBankingSummary.php',5),(6,'AR','Reports','Where Allocated Inquiry','/CustWhereAlloc.php',1),(6,'AR','Transactions','Allocate Receipts or Credit Notes','/CustomerAllocations.php',4),(6,'AR','Transactions','Create A Credit Note','/SelectCreditItems.php?NewCredit=Yes',2),(6,'AR','Transactions','Enter Receipts','/CustomerReceipt.php?NewReceipt=Yes&amp;Type=Customer',3),(6,'AR','Transactions','Select Order to Invoice','/SelectSalesOrder.php',1),(6,'FA','Maintenance','Add or Maintain Asset Locations','/FixedAssetLocations.php',2),(6,'FA','Maintenance','Fixed Asset Category Maintenance','/FixedAssetCategories.php',1),(6,'FA','Maintenance','Fixed Asset Maintenance Tasks','/MaintenanceTasks.php',3),(6,'FA','Reports','Asset Register','/FixedAssetRegister.php',1),(6,'FA','Reports','Maintenance Reminder Emails','/MaintenanceReminders.php',3),(6,'FA','Reports','My Maintenance Schedule','/MaintenanceUserSchedule.php',2),(6,'FA','Transactions','Add a new Asset','/FixedAssetItems.php',1),(6,'FA','Transactions','Change Asset Location','/FixedAssetTransfer.php',3),(6,'FA','Transactions','Depreciation Journal','/FixedAssetDepreciation.php',4),(6,'FA','Transactions','Select an Asset','/SelectAsset.php',2),(6,'GL','Maintenance','Account Groups','/AccountGroups.php',4),(6,'GL','Maintenance','Account Sections','/AccountSections.php',1),(6,'GL','Maintenance','Bank Account Authorised Users','/BankAccountUsers.php',11),(6,'GL','Maintenance','Bank Accounts','/BankAccounts.php',10),(6,'GL','Maintenance','Copy Authority Bank Accounts from one user to another','/GLBankAccountUsersCopyAuthority.php',21),(6,'GL','Maintenance','Copy Authority GL Accounts from one user to another','/GLAccountUsersCopyAuthority.php',20),(6,'GL','Maintenance','Create Budget Amounts','/GLBudgets.php',3),(6,'GL','Maintenance','Create/Amend General Ledger Budgets','/GLBudgetHeaders.php',2),(6,'GL','Maintenance','GL Account','/GLAccounts.php',5),(6,'GL','Maintenance','GL Account Authorised Users','/GLAccountUsers.php',6),(6,'GL','Maintenance','GL Tags','/GLTags.php',9),(6,'GL','Maintenance','Maintain Journal Templates','/GLJournalTemplates.php',13),(6,'GL','Maintenance','Setup Regular Payments','/RegularPaymentsSetup.php',14),(6,'GL','Maintenance','User Authorised Bank Accounts','/UserBankAccounts.php',12),(6,'GL','Maintenance','User Authorised GL Accounts','/UserGLAccounts.php',7),(6,'GL','Reports','Account Inquiry','/SelectGLAccount.php',5),(6,'GL','Reports','Account Listing','/GLAccountReport.php',7),(6,'GL','Reports','Account Listing to CSV File','/GLAccountCSV.php',8),(6,'GL','Reports','Balance Sheet','/GLBalanceSheet.php',11),(6,'GL','Reports','Bank Account Balances','/BankAccountBalances.php',1),(6,'GL','Reports','Bank Account Reconciliation Statement','/BankReconciliation.php',2),(6,'GL','Reports','Cheque Payments Listing','/PDFChequeListing.php',3),(6,'GL','Reports','Daily Bank Transactions','/DailyBankTransactions.php',4),(6,'GL','Reports','Financial Statements','/GLStatements.php',14),(6,'GL','Reports','General Ledger Journal Inquiry','/GLJournalInquiry.php',9),(6,'GL','Reports','Graph of Account Transactions','/GLAccountGraph.php',6),(6,'GL','Reports','Horizontal Analysis of Statement of Comprehensive Income','/AnalysisHorizontalIncome.php',16),(6,'GL','Reports','Horizontal Analysis of Statement of Financial Position','/AnalysisHorizontalPosition.php',15),(6,'GL','Reports','Income and Expenditure by Tag','/GLTagProfit_Loss.php',17),(6,'GL','Reports','Profit and Loss Statement','/GLProfit_Loss.php',12),(6,'GL','Reports','Statement of Cash Flows','/GLCashFlowsIndirect.php',13),(6,'GL','Reports','Tax Reports','/Tax.php',19),(6,'GL','Reports','Trial Balance','/GLTrialBalance.php',10),(6,'GL','Transactions','Bank Account Payments Entry','/Payments.php?NewPayment=Yes',1),(6,'GL','Transactions','Bank Account Payments Matching','/BankMatching.php?Type=Payments',4),(6,'GL','Transactions','Bank Account Receipts Entry','/CustomerReceipt.php?NewReceipt=Yes&amp;Type=GL',2),(6,'GL','Transactions','Bank Account Receipts Matching','/BankMatching.php?Type=Receipts',5),(6,'GL','Transactions','Journal Entry','/GLJournal.php?NewJournal=Yes',3),(6,'GL','Transactions','Import Bank Transactions','/ImportBankTrans.php',6),(6,'GL','Transactions','Process Regular Payments','/RegularPaymentsProcess.php',7),(6,'manuf','Maintenance','Auto Create Master Schedule','/MRPCreateDemands.php',5),(6,'manuf','Maintenance','Bills Of Material','/BOMs.php',2),(6,'manuf','Maintenance','Copy a Bill Of Materials Between Items','/CopyBOM.php',3),(6,'manuf','Maintenance','Employees','/Employees.php',9),(6,'manuf','Maintenance','Master Schedule','/MRPDemands.php',4),(6,'manuf','Maintenance','MRP Calculation','/MRP.php',6),(6,'manuf','Maintenance','Product Specifications','/ProductSpecs.php',8),(6,'manuf','Maintenance','Quality Tests Maintenance','/QATests.php',7),(6,'manuf','Maintenance','Work Centre','/WorkCentres.php',1),(6,'manuf','Reports','Bill Of Material Listing','/BOMListing.php',4),(6,'manuf','Reports','Costed Bill Of Material Inquiry','/BOMInquiry.php',2),(6,'manuf','Reports','Historical QA Test Results','/HistoricalTestResults.php',17),(6,'manuf','Reports','Indented Bill Of Material Listing','/BOMIndented.php',5),(6,'manuf','Reports','Indented Where Used Listing','/BOMIndentedReverse.php',9),(6,'manuf','Reports','List Components Required','/BOMExtendedQty.php',6),(6,'manuf','Reports','List Materials Not Used anywhere','/MaterialsNotUsed.php',7),(6,'manuf','Reports','MRP','/MRPReport.php',10),(6,'manuf','Reports','MRP Reschedules Required','/MRPReschedules.php',14),(6,'manuf','Reports','MRP Shortages','/MRPShortages.php',11),(6,'manuf','Reports','MRP Suggested Purchase Orders','/MRPPlannedPurchaseOrders.php',12),(6,'manuf','Reports','MRP Suggested Work Orders','/MRPPlannedWorkOrders.php',13),(6,'manuf','Reports','Multiple Work Orders Total Cost Inquiry','/CollectiveWorkOrderCost.php',18),(6,'manuf','Reports','Print Certificate of Analysis','/PDFCOA.php',16),(6,'manuf','Reports','Print Product Specification','/PDFProdSpec.php',15),(6,'manuf','Reports','Select A Work Order','/SelectWorkOrder.php',1),(6,'manuf','Reports','Where Used Inquiry','/WhereUsedInquiry.php',3),(6,'manuf','Reports','WO Items ready to produce','/WOCanBeProducedNow.php',8),(6,'manuf','Transactions','QA Samples and Test Results','/SelectQASamples.php',3),(6,'manuf','Transactions','Select A Work Order','/SelectWorkOrder.php',2),(6,'manuf','Transactions','Timesheet Entry','/Timesheets.php',4),(6,'manuf','Transactions','Work Order Entry','/WorkOrderEntry.php?New=True',1),(6,'PC','Maintenance','Expenses for Type of PC Tab','/PcExpensesTypeTab.php',4),(6,'PC','Maintenance','PC Expenses','/PcExpenses.php',3),(6,'PC','Maintenance','PC Tabs','/PcTabs.php',2),(6,'PC','Maintenance','Types of PC Tabs','/PcTypeTabs.php',1),(6,'PC','Reports','PC Expense General Report','/PcReportExpense.php',2),(6,'PC','Reports','PC Expenses Analysis','/PcAnalysis.php',4),(6,'PC','Reports','PC Tab Expenses List','/PcTabExpensesList.php',3),(6,'PC','Reports','PC Tab General Report','/PcReportTab.php',1),(6,'PC','Transactions','Assign Cash to PC Tab','/PcAssignCashToTab.php',1),(6,'PC','Transactions','Authorise Assigned Cash','/PcAuthorizeCash.php',5),(6,'PC','Transactions','Authorise Expenses','/PcAuthorizeExpenses.php',4),(6,'PC','Transactions','Claim Expenses From PC Tab','/PcClaimExpensesFromTab.php',3),(6,'PC','Transactions','Transfer Assigned Cash Between PC Tabs','/PcAssignCashTabToTab.php',2),(6,'PO','Maintenance','Maintain Supplier Price Lists','/SupplierPriceList.php',1),(6,'PO','Reports','Purchase Order Detail Or Summary Inquiries','/POReport.php',2),(6,'PO','Reports','Purchase Order Inquiry','/PO_SelectPurchOrder.php',1),(6,'PO','Reports','Purchase Orders Financial Planning','/POFinancialPlanning.php',20),(6,'PO','Reports','Purchases from Suppliers','/PurchasesReport.php',4),(6,'PO','Reports','Supplier Price List','/SuppPriceList.php',3),(6,'PO','Transactions','Create a New Tender','/SupplierTenderCreate.php?New=Yes',4),(6,'PO','Transactions','Edit Existing Tenders','/SupplierTenderCreate.php?Edit=Yes',5),(6,'PO','Transactions','New Purchase Order','/PO_Header.php?NewOrder=Yes',1),(6,'PO','Transactions','Orders to Authorise','/PO_AuthoriseMyOrders.php',7),(6,'PO','Transactions','Process Tenders and Offers','/OffersReceived.php',6),(6,'PO','Transactions','Purchase Order Grid Entry','/PurchaseByPrefSupplier.php',3),(6,'PO','Transactions','Purchase Orders','/PO_SelectOSPurchOrder.php',2),(6,'PO','Transactions','Select A Shipment','/Shipt_Select.php',9),(6,'PO','Transactions','Shipment Entry','/SelectSupplier.php',8),(6,'Sales','Maintenance','Create Contract','/Contracts.php',1),(6,'Sales','Maintenance','Select Contract','/SelectContract.php',2),(6,'Sales','Maintenance','Sell Through Support Deals','/SellThroughSupport.php',3),(6,'Sales','Reports','Daily Sales Inquiry','/DailySalesInquiry.php',5),(6,'Sales','Reports','Delivery In Full On Time (DIFOT) Report','/PDFDIFOT.php',13),(6,'Sales','Reports','Order Delivery Differences Report','/PDFDeliveryDifferences.php',12),(6,'Sales','Reports','Order Status Report','/PDFOrderStatus.php',3),(6,'Sales','Reports','Orders Invoiced Reports','/PDFOrdersInvoiced.php',4),(6,'Sales','Reports','Print Price Lists','/PDFPriceList.php',2),(6,'Sales','Reports','Sales Analysis Reports','/SalesAnalRepts.php',9),(6,'Sales','Reports','Sales By Category By Item Inquiry','/StockCategorySalesInquiry.php',8),(6,'Sales','Reports','Sales By Category Inquiry','/SalesCategoryPeriodInquiry.php',7),(6,'Sales','Reports','Sales By Sales Type Inquiry','/SalesByTypePeriodInquiry.php',6),(6,'Sales','Reports','Sales Commission Reports','/SalesCommissionReports.php',21),(6,'Sales','Reports','Sales Graphs','/SalesGraph.php',10),(6,'Sales','Reports','Sales Order Detail Or Summary Inquiries','/SalesInquiry.php',14),(6,'Sales','Reports','Sales Order Inquiry','/SelectCompletedOrder.php',1),(6,'Sales','Reports','Sales to Customers','/SalesReport.php',20),(6,'Sales','Reports','Sales With Low Gross Profit Report','/PDFLowGP.php',18),(6,'Sales','Reports','Sell Through Support Claims Report','/PDFSellThroughSupportClaim.php',19),(6,'Sales','Reports','Top Customers Inquiry','/SalesTopCustomersInquiry.php',16),(6,'Sales','Reports','Top Sales Items Report','/TopItems.php',15),(6,'Sales','Reports','Top Sellers Inquiry','/SalesTopItemsInquiry.php',11),(6,'Sales','Reports','Worst Sales Items Report','/NoSalesItems.php',17),(6,'Sales','Transactions','Enter Counter Returns','/CounterReturns.php',3),(6,'Sales','Transactions','Enter Counter Sales','/CounterSales.php',2),(6,'Sales','Transactions','Generate/Print Picking Lists','/PDFPickingList.php',4),(6,'Sales','Transactions','Maintain Picking Lists','/SelectPickingLists.php',9),(6,'Sales','Transactions','New Sales Order or Quotation','/SelectOrderItems.php?NewOrder=Yes',1),(6,'Sales','Transactions','Outstanding Sales Orders/Quotations','/SelectSalesOrder.php',5),(6,'Sales','Transactions','Process Recurring Orders','/RecurringSalesOrdersProcess.php',8),(6,'Sales','Transactions','Recurring Order Template','/SelectRecurringSalesOrder.php',7),(6,'Sales','Transactions','Special Order','/SpecialOrder.php',6),(6,'stock','Maintenance','Add A New Item','/Stocks.php',1),(6,'stock','Maintenance','Add or Update Prices Based On Costs','/PricesBasedOnMarkUp.php',6),(6,'stock','Maintenance','Brands Maintenance','/Manufacturers.php',5),(6,'stock','Maintenance','Reorder Level By Category/Location','/ReorderLevelLocation.php',9),(6,'stock','Maintenance','Review Translated Descriptions','/RevisionTranslations.php',3),(6,'stock','Maintenance','Sales Category Maintenance','/SalesCategories.php',4),(6,'stock','Maintenance','Select An Item','/SelectProduct.php',2),(6,'stock','Maintenance','Upload new prices from csv file','/UploadPriceList.php',8),(6,'stock','Maintenance','View or Update Prices Based On Costs','/PricesByCost.php',7),(6,'stock','Reports','Aged Controlled Stock Report','/AgedControlledInventory.php',23),(6,'stock','Reports','All Inventory Movements By Location/Date','/StockLocMovements.php',17),(6,'stock','Reports','Compare Counts Vs Stock Check Data','/PDFStockCheckComparison.php',16),(6,'stock','Reports','Historical Stock Quantity By Location/Category','/StockQuantityByDate.php',19),(6,'stock','Reports','Internal stock request inquiry','/InternalStockRequestInquiry.php',24),(6,'stock','Reports','Inventory Item Movements','/StockMovements.php',4),(6,'stock','Reports','Inventory Item Status','/StockStatus.php',5),(6,'stock','Reports','Inventory Item Usage','/StockUsage.php',6),(6,'stock','Reports','Inventory Planning Based On Preferred Supplier Data','/InventoryPlanningPrefSupplier.php',13),(6,'stock','Reports','Inventory Planning Report','/InventoryPlanning.php',12),(6,'stock','Reports','Inventory Quantities','/InventoryQuantities.php',7),(6,'stock','Reports','Inventory Stock Check Sheets','/StockCheck.php',14),(6,'stock','Reports','Inventory Valuation Report','/InventoryValuation.php',10),(6,'stock','Reports','List Inventory Status By Location/Category','/StockLocStatus.php',18),(6,'stock','Reports','List Negative Stocks','/PDFStockNegatives.php',20),(6,'stock','Reports','Mail Inventory Valuation Report','/MailInventoryValuation.php',11),(6,'stock','Reports','Make Inventory Quantities CSV','/StockQties_csv.php',15),(6,'stock','Reports','Period Stock Transaction Listing','/PDFPeriodStockTransListing.php',21),(6,'stock','Reports','Print Price Labels','/PDFPrintLabel.php',2),(6,'stock','Reports','Reorder Level','/ReorderLevel.php',8),(6,'stock','Reports','Reprint GRN','/ReprintGRN.php',3),(6,'stock','Reports','Serial Item Research Tool','/StockSerialItemResearch.php',1),(6,'stock','Reports','Stock Dispatch','/StockDispatch.php',9),(6,'stock','Reports','Stock Transfer Note','/PDFStockTransfer.php',22),(6,'stock','Transactions','Authorise Internal Stock Requests','/InternalStockRequestAuthorisation.php',9),(6,'stock','Transactions','Bulk Inventory Transfer - Dispatch','/StockLocTransfer.php',3),(6,'stock','Transactions','Bulk Inventory Transfer - Receive','/StockLocTransferReceive.php',4),(6,'stock','Transactions','Create a New Internal Stock Request','/InternalStockRequest.php?New=Yes',8),(6,'stock','Transactions','Enter Stock Counts','/StockCounts.php',7),(6,'stock','Transactions','Fulfil Internal Stock Requests','/InternalStockRequestFulfill.php',10),(6,'stock','Transactions','Inventory Adjustments','/StockAdjustments.php?NewAdjustment=Yes',5),(6,'stock','Transactions','Inventory Location Transfers','/StockTransfers.php?New=Yes',2),(6,'stock','Transactions','Receive Purchase Orders','/PO_SelectOSPurchOrder.php',1),(6,'stock','Transactions','Reverse Goods Received','/ReverseGRN.php',6),(6,'system','Maintenance','Copy Authority Locations from one user to another','/LocationUsersCopyAuthority.php',20),(6,'system','Maintenance','Dashboard Configuration','/DashboardConfig.php',13),(6,'system','Maintenance','Discount Category Maintenance','/DiscountCategories.php',5),(6,'system','Maintenance','Inventory Categories Maintenance','/StockCategories.php',1),(6,'system','Maintenance','Inventory Location Authorised Users Maintenance','/LocationUsers.php',3),(6,'system','Maintenance','Inventory Locations Maintenance','/Locations.php',2),(6,'system','Maintenance','Label Templates Maintenance','/Labels.php',12),(6,'system','Maintenance','Logged in users','/LoggedInUsers.php',8),(6,'system','Maintenance','Maintain Internal Departments','/Departments.php',10),(6,'system','Maintenance','Maintain Internal Stock Categories to User Roles','/InternalStockCategoriesByRole.php',11),(6,'system','Maintenance','MRP Available Production Days','/MRPCalendar.php',7),(6,'system','Maintenance','MRP Demand Types','/MRPDemandTypes.php',9),(6,'system','Maintenance','Units of Measure','/UnitsOfMeasure.php',6),(6,'system','Maintenance','User Authorised Inventory Locations Maintenance','/UserLocations.php',4),(6,'system','Reports','COGS GL Interface Postings','/COGSGLPostings.php',12),(6,'system','Reports','Credit Status','/CreditStatus.php',4),(6,'system','Reports','Customer Types','/CustomerTypes.php',2),(6,'system','Reports','Discount Matrix','/DiscountMatrix.php',14),(6,'system','Reports','Freight Costs Maintenance','/FreightCosts.php',13),(6,'system','Reports','Payment Methods','/PaymentMethods.php',7),(6,'system','Reports','Payment Terms','/PaymentTerms.php',5),(6,'system','Reports','Sales Areas','/Areas.php',9),(6,'system','Reports','Sales Commission Types','/SalesCommissionTypes.php',15),(6,'system','Reports','Sales GL Interface Postings','/SalesGLPostings.php',11),(6,'system','Reports','Sales People','/SalesPeople.php',8),(6,'system','Reports','Sales Types','/SalesTypes.php',1),(6,'system','Reports','Set Purchase Order Authorisation levels','/PO_AuthorisationLevels.php',6),(6,'system','Reports','Shippers','/Shippers.php',10),(6,'system','Reports','Supplier Types','/SupplierTypes.php',3),(6,'system','Transactions','Access Permissions Maintenance','/WWW_Access.php',5),(6,'system','Transactions','Company Preferences','/CompanyPreferences.php',1),(6,'system','Transactions','Currency Maintenance','/Currencies.php',7),(6,'system','Transactions','Dispatch Tax Province Maintenance','/TaxProvinces.php',10),(6,'system','Transactions','Form Designer','/FormDesigner.php',16),(6,'system','Transactions','Geocode Maintenance','/GeocodeSetup.php',15),(6,'system','Transactions','List Periods Defined','/PeriodsInquiry.php',12),(6,'system','Transactions','Mailing Group Maintenance','/MailingGroupMaintenance.php',19),(6,'system','Transactions','Maintain Security Tokens','/SecurityTokens.php',4),(6,'system','Transactions','Page Security Settings','/PageSecurity.php',6),(6,'system','Transactions','Report Builder Tool','/reportwriter/admin/ReportCreator.php',13),(6,'system','Transactions','SMTP Server Details','/SMTPServer.php',18),(6,'system','Transactions','System Parameters','/SystemParameters.php',2),(6,'system','Transactions','Tax Authorities and Rates Maintenance','/TaxAuthorities.php',8),(6,'system','Transactions','Tax Category Maintenance','/TaxCategories.php',11),(6,'system','Transactions','Tax Group Maintenance','/TaxGroups.php',9),(6,'system','Transactions','Users Maintenance','/WWW_Users.php',3),(6,'system','Transactions','View Audit Trail','/AuditTrail.php',14),(6,'system','Transactions','Web-Store Configuration','/ShopParameters.php',17),(6,'Utilities','Maintenance','Create new company template SQL file and submit to webERP','/Z_CreateCompanyTemplateFile.php',9),(6,'Utilities','Maintenance','Data Export Options','/Z_DataExport.php',3),(6,'Utilities','Maintenance','Import Customers from .csv file','/Z_ImportDebtors.php',4),(6,'Utilities','Maintenance','Import Fixed Assets from .csv file','/Z_ImportFixedAssets.php',7),(6,'Utilities','Maintenance','Import GL Payments Receipts Or Journals From .csv file','/Z_ImportGLTransactions.php',8),(6,'Utilities','Maintenance','Import Price List from .csv file','/Z_ImportPriceList.php',6),(6,'Utilities','Maintenance','Import Stock Items from .csv','/Z_ImportStocks.php',5),(6,'Utilities','Maintenance','Maintain Language Files','/Z_poAdmin.php',1),(6,'Utilities','Maintenance','Make New Company','/Z_MakeNewCompany.php',2),(6,'Utilities','Maintenance','Purge all old prices','/Z_DeleteOldPrices.php',12),(6,'Utilities','Maintenance','Remove all purchase back orders','/Z_RemovePurchaseBackOrders.php',13),(6,'Utilities','Reports','Debtors Balances By Currency Totals','/Z_CurrencyDebtorsBalances.php',1),(6,'Utilities','Reports','List of items without picture','/Z_ItemsWithoutPicture.php',4),(6,'Utilities','Reports','Show General Transactions That Do Not Balance','/Z_CheckGLTransBalance.php',3),(6,'Utilities','Reports','Suppliers Balances By Currency Totals','/Z_CurrencySuppliersBalances.php',2),(6,'Utilities','Transactions','Change A Customer Branch Code','/Z_ChangeBranchCode.php',2),(6,'Utilities','Transactions','Change A Customer Code','/Z_ChangeCustomerCode.php',1),(6,'Utilities','Transactions','Change A GL Account Code','/Z_ChangeGLAccountCode.php',3),(6,'Utilities','Transactions','Change A Location Code','/Z_ChangeLocationCode.php',5),(6,'Utilities','Transactions','Change A Salesman Code','/Z_ChangeSalesmanCode.php',6),(6,'Utilities','Transactions','Change A Stock Category Code','/Z_ChangeStockCategory.php',7),(6,'Utilities','Transactions','Change A Supplier Code','/Z_ChangeSupplierCode.php',8),(6,'Utilities','Transactions','Change An Inventory Item Code','/Z_ChangeStockCode.php',4),(6,'Utilities','Transactions','Copy Authority of GL Accounts from one user to another','/Z_GLAccountUsersCopyAuthority.php',15),(6,'Utilities','Transactions','Delete sales transactions','/Z_DeleteSalesTransActions.php',12),(6,'Utilities','Transactions','Re-apply costs to Sales Analysis','/Z_ReApplyCostToSA.php',11),(6,'Utilities','Transactions','Reverse all supplier payments on a specified date','/Z_ReverseSuppPaymentRun.php',13),(6,'Utilities','Transactions','Translate Item Descriptions','/AutomaticTranslationDescriptions.php',9),(6,'Utilities','Transactions','Update costs for all BOM items, from the bottom up','/Z_BottomUpCosts.php',10),(6,'Utilities','Transactions','Update sales analysis with latest customer data','/Z_UpdateSalesAnalysisWithLatestCustomerData.php',14),(7,'AP','Maintenance','Add Supplier','/Suppliers.php',1),(7,'AP','Maintenance','Maintain Factor Companies','/Factors.php',3),(7,'AP','Maintenance','Select Supplier','/SelectSupplier.php',2),(7,'AP','Reports','Aged Supplier Report','/AgedSuppliers.php',2),(7,'AP','Reports','List Daily Transactions','/PDFSuppTransListing.php',7),(7,'AP','Reports','Outstanding GRNs Report','/OutstandingGRNs.php',5),(7,'AP','Reports','Payment Run Report','/SuppPaymentRun.php',3),(7,'AP','Reports','Remittance Advices','/PDFRemittanceAdvice.php',4),(7,'AP','Reports','Supplier Balances At A Prior Month End','/SupplierBalsAtPeriodEnd.php',6),(7,'AP','Reports','Supplier Transaction Inquiries','/SupplierTransInquiry.php',8),(7,'AP','Reports','Where Allocated Inquiry','/SuppWhereAlloc.php',1),(7,'AP','Transactions','Select Supplier','/SelectSupplier.php',1),(7,'AP','Transactions','Supplier Allocations','/SupplierAllocations.php',2),(7,'AR','Maintenance','Add Customer','/Customers.php',1),(7,'AR','Maintenance','Select Customer','/SelectCustomer.php',2),(7,'AR','Reports','Aged Customer Balances/Overdues Report','/AgedDebtors.php',4),(7,'AR','Reports','Customer Activity and Balances','/CustomerBalancesMovement.php',10),(7,'AR','Reports','Customer Listing By Area/Salesperson','/PDFCustomerList.php',7),(7,'AR','Reports','Customer Transaction Inquiries','/CustomerTransInquiry.php',9),(7,'AR','Reports','Debtor Balances At A Prior Month End','/DebtorsAtPeriodEnd.php',6),(7,'AR','Reports','List Daily Transactions','/PDFCustTransListing.php',8),(7,'AR','Reports','Print Invoices or Credit Notes','/PrintCustTrans.php',2),(7,'AR','Reports','Print Statements','/PrintCustStatements.php',3),(7,'AR','Reports','Re-Print A Deposit Listing','/PDFBankingSummary.php',5),(7,'AR','Reports','Where Allocated Inquiry','/CustWhereAlloc.php',1),(7,'AR','Transactions','Allocate Receipts or Credit Notes','/CustomerAllocations.php',4),(7,'AR','Transactions','Create A Credit Note','/SelectCreditItems.php?NewCredit=Yes',2),(7,'AR','Transactions','Enter Receipts','/CustomerReceipt.php?NewReceipt=Yes&amp;Type=Customer',3),(7,'AR','Transactions','Select Order to Invoice','/SelectSalesOrder.php',1),(7,'FA','Maintenance','Add or Maintain Asset Locations','/FixedAssetLocations.php',2),(7,'FA','Maintenance','Fixed Asset Category Maintenance','/FixedAssetCategories.php',1),(7,'FA','Maintenance','Fixed Asset Maintenance Tasks','/MaintenanceTasks.php',3),(7,'FA','Reports','Asset Register','/FixedAssetRegister.php',1),(7,'FA','Reports','Maintenance Reminder Emails','/MaintenanceReminders.php',3),(7,'FA','Reports','My Maintenance Schedule','/MaintenanceUserSchedule.php',2),(7,'FA','Transactions','Add a new Asset','/FixedAssetItems.php',1),(7,'FA','Transactions','Change Asset Location','/FixedAssetTransfer.php',3),(7,'FA','Transactions','Depreciation Journal','/FixedAssetDepreciation.php',4),(7,'FA','Transactions','Select an Asset','/SelectAsset.php',2),(7,'GL','Maintenance','Account Groups','/AccountGroups.php',4),(7,'GL','Maintenance','Account Sections','/AccountSections.php',1),(7,'GL','Maintenance','Bank Account Authorised Users','/BankAccountUsers.php',11),(7,'GL','Maintenance','Bank Accounts','/BankAccounts.php',10),(7,'GL','Maintenance','Copy Authority Bank Accounts from one user to another','/GLBankAccountUsersCopyAuthority.php',21),(7,'GL','Maintenance','Copy Authority GL Accounts from one user to another','/GLAccountUsersCopyAuthority.php',20),(7,'GL','Maintenance','Create Budget Amounts','/GLBudgets.php',3),(7,'GL','Maintenance','Create/Amend General Ledger Budgets','/GLBudgetHeaders.php',2),(7,'GL','Maintenance','GL Account','/GLAccounts.php',5),(7,'GL','Maintenance','GL Account Authorised Users','/GLAccountUsers.php',6),(7,'GL','Maintenance','GL Tags','/GLTags.php',9),(7,'GL','Maintenance','Maintain Journal Templates','/GLJournalTemplates.php',13),(7,'GL','Maintenance','Setup Regular Payments','/RegularPaymentsSetup.php',14),(7,'GL','Maintenance','User Authorised Bank Accounts','/UserBankAccounts.php',12),(7,'GL','Maintenance','User Authorised GL Accounts','/UserGLAccounts.php',7),(7,'GL','Reports','Account Inquiry','/SelectGLAccount.php',5),(7,'GL','Reports','Account Listing','/GLAccountReport.php',7),(7,'GL','Reports','Account Listing to CSV File','/GLAccountCSV.php',8),(7,'GL','Reports','Balance Sheet','/GLBalanceSheet.php',11),(7,'GL','Reports','Bank Account Balances','/BankAccountBalances.php',1),(7,'GL','Reports','Bank Account Reconciliation Statement','/BankReconciliation.php',2),(7,'GL','Reports','Cheque Payments Listing','/PDFChequeListing.php',3),(7,'GL','Reports','Daily Bank Transactions','/DailyBankTransactions.php',4),(7,'GL','Reports','Financial Statements','/GLStatements.php',14),(7,'GL','Reports','General Ledger Journal Inquiry','/GLJournalInquiry.php',9),(7,'GL','Reports','Graph of Account Transactions','/GLAccountGraph.php',6),(7,'GL','Reports','Horizontal Analysis of Statement of Comprehensive Income','/AnalysisHorizontalIncome.php',16),(7,'GL','Reports','Horizontal Analysis of Statement of Financial Position','/AnalysisHorizontalPosition.php',15),(7,'GL','Reports','Income and Expenditure by Tag','/GLTagProfit_Loss.php',17),(7,'GL','Reports','Profit and Loss Statement','/GLProfit_Loss.php',12),(7,'GL','Reports','Statement of Cash Flows','/GLCashFlowsIndirect.php',13),(7,'GL','Reports','Tax Reports','/Tax.php',19),(7,'GL','Reports','Trial Balance','/GLTrialBalance.php',10),(7,'GL','Transactions','Bank Account Payments Entry','/Payments.php?NewPayment=Yes',1),(7,'GL','Transactions','Bank Account Payments Matching','/BankMatching.php?Type=Payments',4),(7,'GL','Transactions','Bank Account Receipts Entry','/CustomerReceipt.php?NewReceipt=Yes&amp;Type=GL',2),(7,'GL','Transactions','Bank Account Receipts Matching','/BankMatching.php?Type=Receipts',5),(7,'GL','Transactions','Journal Entry','/GLJournal.php?NewJournal=Yes',3),(7,'GL','Transactions','Import Bank Transactions','/ImportBankTrans.php',6),(7,'GL','Transactions','Process Regular Payments','/RegularPaymentsProcess.php',7),(7,'manuf','Maintenance','Auto Create Master Schedule','/MRPCreateDemands.php',5),(7,'manuf','Maintenance','Bills Of Material','/BOMs.php',2),(7,'manuf','Maintenance','Copy a Bill Of Materials Between Items','/CopyBOM.php',3),(7,'manuf','Maintenance','Employees','/Employees.php',9),(7,'manuf','Maintenance','Master Schedule','/MRPDemands.php',4),(7,'manuf','Maintenance','MRP Calculation','/MRP.php',6),(7,'manuf','Maintenance','Product Specifications','/ProductSpecs.php',8),(7,'manuf','Maintenance','Quality Tests Maintenance','/QATests.php',7),(7,'manuf','Maintenance','Work Centre','/WorkCentres.php',1),(7,'manuf','Reports','Bill Of Material Listing','/BOMListing.php',4),(7,'manuf','Reports','Costed Bill Of Material Inquiry','/BOMInquiry.php',2),(7,'manuf','Reports','Historical QA Test Results','/HistoricalTestResults.php',17),(7,'manuf','Reports','Indented Bill Of Material Listing','/BOMIndented.php',5),(7,'manuf','Reports','Indented Where Used Listing','/BOMIndentedReverse.php',9),(7,'manuf','Reports','List Components Required','/BOMExtendedQty.php',6),(7,'manuf','Reports','List Materials Not Used anywhere','/MaterialsNotUsed.php',7),(7,'manuf','Reports','MRP','/MRPReport.php',10),(7,'manuf','Reports','MRP Reschedules Required','/MRPReschedules.php',14),(7,'manuf','Reports','MRP Shortages','/MRPShortages.php',11),(7,'manuf','Reports','MRP Suggested Purchase Orders','/MRPPlannedPurchaseOrders.php',12),(7,'manuf','Reports','MRP Suggested Work Orders','/MRPPlannedWorkOrders.php',13),(7,'manuf','Reports','Multiple Work Orders Total Cost Inquiry','/CollectiveWorkOrderCost.php',18),(7,'manuf','Reports','Print Certificate of Analysis','/PDFCOA.php',16),(7,'manuf','Reports','Print Product Specification','/PDFProdSpec.php',15),(7,'manuf','Reports','Select A Work Order','/SelectWorkOrder.php',1),(7,'manuf','Reports','Where Used Inquiry','/WhereUsedInquiry.php',3),(7,'manuf','Reports','WO Items ready to produce','/WOCanBeProducedNow.php',8),(7,'manuf','Transactions','QA Samples and Test Results','/SelectQASamples.php',3),(7,'manuf','Transactions','Select A Work Order','/SelectWorkOrder.php',2),(7,'manuf','Transactions','Timesheet Entry','/Timesheets.php',4),(7,'manuf','Transactions','Work Order Entry','/WorkOrderEntry.php?New=True',1),(7,'PC','Maintenance','Expenses for Type of PC Tab','/PcExpensesTypeTab.php',4),(7,'PC','Maintenance','PC Expenses','/PcExpenses.php',3),(7,'PC','Maintenance','PC Tabs','/PcTabs.php',2),(7,'PC','Maintenance','Types of PC Tabs','/PcTypeTabs.php',1),(7,'PC','Reports','PC Expense General Report','/PcReportExpense.php',2),(7,'PC','Reports','PC Expenses Analysis','/PcAnalysis.php',4),(7,'PC','Reports','PC Tab Expenses List','/PcTabExpensesList.php',3),(7,'PC','Reports','PC Tab General Report','/PcReportTab.php',1),(7,'PC','Transactions','Assign Cash to PC Tab','/PcAssignCashToTab.php',1),(7,'PC','Transactions','Authorise Assigned Cash','/PcAuthorizeCash.php',5),(7,'PC','Transactions','Authorise Expenses','/PcAuthorizeExpenses.php',4),(7,'PC','Transactions','Claim Expenses From PC Tab','/PcClaimExpensesFromTab.php',3),(7,'PC','Transactions','Transfer Assigned Cash Between PC Tabs','/PcAssignCashTabToTab.php',2),(7,'PO','Maintenance','Maintain Supplier Price Lists','/SupplierPriceList.php',1),(7,'PO','Reports','Purchase Order Detail Or Summary Inquiries','/POReport.php',2),(7,'PO','Reports','Purchase Order Inquiry','/PO_SelectPurchOrder.php',1),(7,'PO','Reports','Purchase Orders Financial Planning','/POFinancialPlanning.php',20),(7,'PO','Reports','Purchases from Suppliers','/PurchasesReport.php',4),(7,'PO','Reports','Supplier Price List','/SuppPriceList.php',3),(7,'PO','Transactions','Create a New Tender','/SupplierTenderCreate.php?New=Yes',4),(7,'PO','Transactions','Edit Existing Tenders','/SupplierTenderCreate.php?Edit=Yes',5),(7,'PO','Transactions','New Purchase Order','/PO_Header.php?NewOrder=Yes',1),(7,'PO','Transactions','Orders to Authorise','/PO_AuthoriseMyOrders.php',7),(7,'PO','Transactions','Process Tenders and Offers','/OffersReceived.php',6),(7,'PO','Transactions','Purchase Order Grid Entry','/PurchaseByPrefSupplier.php',3),(7,'PO','Transactions','Purchase Orders','/PO_SelectOSPurchOrder.php',2),(7,'PO','Transactions','Select A Shipment','/Shipt_Select.php',9),(7,'PO','Transactions','Shipment Entry','/SelectSupplier.php',8),(7,'Sales','Maintenance','Create Contract','/Contracts.php',1),(7,'Sales','Maintenance','Select Contract','/SelectContract.php',2),(7,'Sales','Maintenance','Sell Through Support Deals','/SellThroughSupport.php',3),(7,'Sales','Reports','Daily Sales Inquiry','/DailySalesInquiry.php',5),(7,'Sales','Reports','Delivery In Full On Time (DIFOT) Report','/PDFDIFOT.php',13),(7,'Sales','Reports','Order Delivery Differences Report','/PDFDeliveryDifferences.php',12),(7,'Sales','Reports','Order Status Report','/PDFOrderStatus.php',3),(7,'Sales','Reports','Orders Invoiced Reports','/PDFOrdersInvoiced.php',4),(7,'Sales','Reports','Print Price Lists','/PDFPriceList.php',2),(7,'Sales','Reports','Sales Analysis Reports','/SalesAnalRepts.php',9),(7,'Sales','Reports','Sales By Category By Item Inquiry','/StockCategorySalesInquiry.php',8),(7,'Sales','Reports','Sales By Category Inquiry','/SalesCategoryPeriodInquiry.php',7),(7,'Sales','Reports','Sales By Sales Type Inquiry','/SalesByTypePeriodInquiry.php',6),(7,'Sales','Reports','Sales Commission Reports','/SalesCommissionReports.php',21),(7,'Sales','Reports','Sales Graphs','/SalesGraph.php',10),(7,'Sales','Reports','Sales Order Detail Or Summary Inquiries','/SalesInquiry.php',14),(7,'Sales','Reports','Sales Order Inquiry','/SelectCompletedOrder.php',1),(7,'Sales','Reports','Sales to Customers','/SalesReport.php',20),(7,'Sales','Reports','Sales With Low Gross Profit Report','/PDFLowGP.php',18),(7,'Sales','Reports','Sell Through Support Claims Report','/PDFSellThroughSupportClaim.php',19),(7,'Sales','Reports','Top Customers Inquiry','/SalesTopCustomersInquiry.php',16),(7,'Sales','Reports','Top Sales Items Report','/TopItems.php',15),(7,'Sales','Reports','Top Sellers Inquiry','/SalesTopItemsInquiry.php',11),(7,'Sales','Reports','Worst Sales Items Report','/NoSalesItems.php',17),(7,'Sales','Transactions','Enter Counter Returns','/CounterReturns.php',3),(7,'Sales','Transactions','Enter Counter Sales','/CounterSales.php',2),(7,'Sales','Transactions','Generate/Print Picking Lists','/PDFPickingList.php',4),(7,'Sales','Transactions','Maintain Picking Lists','/SelectPickingLists.php',9),(7,'Sales','Transactions','New Sales Order or Quotation','/SelectOrderItems.php?NewOrder=Yes',1),(7,'Sales','Transactions','Outstanding Sales Orders/Quotations','/SelectSalesOrder.php',5),(7,'Sales','Transactions','Process Recurring Orders','/RecurringSalesOrdersProcess.php',8),(7,'Sales','Transactions','Recurring Order Template','/SelectRecurringSalesOrder.php',7),(7,'Sales','Transactions','Special Order','/SpecialOrder.php',6),(7,'stock','Maintenance','Add A New Item','/Stocks.php',1),(7,'stock','Maintenance','Add or Update Prices Based On Costs','/PricesBasedOnMarkUp.php',6),(7,'stock','Maintenance','Brands Maintenance','/Manufacturers.php',5),(7,'stock','Maintenance','Reorder Level By Category/Location','/ReorderLevelLocation.php',9),(7,'stock','Maintenance','Review Translated Descriptions','/RevisionTranslations.php',3),(7,'stock','Maintenance','Sales Category Maintenance','/SalesCategories.php',4),(7,'stock','Maintenance','Select An Item','/SelectProduct.php',2),(7,'stock','Maintenance','Upload new prices from csv file','/UploadPriceList.php',8),(7,'stock','Maintenance','View or Update Prices Based On Costs','/PricesByCost.php',7),(7,'stock','Reports','Aged Controlled Stock Report','/AgedControlledInventory.php',23),(7,'stock','Reports','All Inventory Movements By Location/Date','/StockLocMovements.php',17),(7,'stock','Reports','Compare Counts Vs Stock Check Data','/PDFStockCheckComparison.php',16),(7,'stock','Reports','Historical Stock Quantity By Location/Category','/StockQuantityByDate.php',19),(7,'stock','Reports','Internal stock request inquiry','/InternalStockRequestInquiry.php',24),(7,'stock','Reports','Inventory Item Movements','/StockMovements.php',4),(7,'stock','Reports','Inventory Item Status','/StockStatus.php',5),(7,'stock','Reports','Inventory Item Usage','/StockUsage.php',6),(7,'stock','Reports','Inventory Planning Based On Preferred Supplier Data','/InventoryPlanningPrefSupplier.php',13),(7,'stock','Reports','Inventory Planning Report','/InventoryPlanning.php',12),(7,'stock','Reports','Inventory Quantities','/InventoryQuantities.php',7),(7,'stock','Reports','Inventory Stock Check Sheets','/StockCheck.php',14),(7,'stock','Reports','Inventory Valuation Report','/InventoryValuation.php',10),(7,'stock','Reports','List Inventory Status By Location/Category','/StockLocStatus.php',18),(7,'stock','Reports','List Negative Stocks','/PDFStockNegatives.php',20),(7,'stock','Reports','Mail Inventory Valuation Report','/MailInventoryValuation.php',11),(7,'stock','Reports','Make Inventory Quantities CSV','/StockQties_csv.php',15),(7,'stock','Reports','Period Stock Transaction Listing','/PDFPeriodStockTransListing.php',21),(7,'stock','Reports','Print Price Labels','/PDFPrintLabel.php',2),(7,'stock','Reports','Reorder Level','/ReorderLevel.php',8),(7,'stock','Reports','Reprint GRN','/ReprintGRN.php',3),(7,'stock','Reports','Serial Item Research Tool','/StockSerialItemResearch.php',1),(7,'stock','Reports','Stock Dispatch','/StockDispatch.php',9),(7,'stock','Reports','Stock Transfer Note','/PDFStockTransfer.php',22),(7,'stock','Transactions','Authorise Internal Stock Requests','/InternalStockRequestAuthorisation.php',9),(7,'stock','Transactions','Bulk Inventory Transfer - Dispatch','/StockLocTransfer.php',3),(7,'stock','Transactions','Bulk Inventory Transfer - Receive','/StockLocTransferReceive.php',4),(7,'stock','Transactions','Create a New Internal Stock Request','/InternalStockRequest.php?New=Yes',8),(7,'stock','Transactions','Enter Stock Counts','/StockCounts.php',7),(7,'stock','Transactions','Fulfil Internal Stock Requests','/InternalStockRequestFulfill.php',10),(7,'stock','Transactions','Inventory Adjustments','/StockAdjustments.php?NewAdjustment=Yes',5),(7,'stock','Transactions','Inventory Location Transfers','/StockTransfers.php?New=Yes',2),(7,'stock','Transactions','Receive Purchase Orders','/PO_SelectOSPurchOrder.php',1),(7,'stock','Transactions','Reverse Goods Received','/ReverseGRN.php',6),(7,'system','Maintenance','Copy Authority Locations from one user to another','/LocationUsersCopyAuthority.php',20),(7,'system','Maintenance','Dashboard Configuration','/DashboardConfig.php',13),(7,'system','Maintenance','Discount Category Maintenance','/DiscountCategories.php',5),(7,'system','Maintenance','Inventory Categories Maintenance','/StockCategories.php',1),(7,'system','Maintenance','Inventory Location Authorised Users Maintenance','/LocationUsers.php',3),(7,'system','Maintenance','Inventory Locations Maintenance','/Locations.php',2),(7,'system','Maintenance','Label Templates Maintenance','/Labels.php',12),(7,'system','Maintenance','Logged in users','/LoggedInUsers.php',8),(7,'system','Maintenance','Maintain Internal Departments','/Departments.php',10),(7,'system','Maintenance','Maintain Internal Stock Categories to User Roles','/InternalStockCategoriesByRole.php',11),(7,'system','Maintenance','MRP Available Production Days','/MRPCalendar.php',7),(7,'system','Maintenance','MRP Demand Types','/MRPDemandTypes.php',9),(7,'system','Maintenance','Units of Measure','/UnitsOfMeasure.php',6),(7,'system','Maintenance','User Authorised Inventory Locations Maintenance','/UserLocations.php',4),(7,'system','Reports','COGS GL Interface Postings','/COGSGLPostings.php',12),(7,'system','Reports','Credit Status','/CreditStatus.php',4),(7,'system','Reports','Customer Types','/CustomerTypes.php',2),(7,'system','Reports','Discount Matrix','/DiscountMatrix.php',14),(7,'system','Reports','Freight Costs Maintenance','/FreightCosts.php',13),(7,'system','Reports','Payment Methods','/PaymentMethods.php',7),(7,'system','Reports','Payment Terms','/PaymentTerms.php',5),(7,'system','Reports','Sales Areas','/Areas.php',9),(7,'system','Reports','Sales Commission Types','/SalesCommissionTypes.php',15),(7,'system','Reports','Sales GL Interface Postings','/SalesGLPostings.php',11),(7,'system','Reports','Sales People','/SalesPeople.php',8),(7,'system','Reports','Sales Types','/SalesTypes.php',1),(7,'system','Reports','Set Purchase Order Authorisation levels','/PO_AuthorisationLevels.php',6),(7,'system','Reports','Shippers','/Shippers.php',10),(7,'system','Reports','Supplier Types','/SupplierTypes.php',3),(7,'system','Transactions','Access Permissions Maintenance','/WWW_Access.php',5),(7,'system','Transactions','Company Preferences','/CompanyPreferences.php',1),(7,'system','Transactions','Currency Maintenance','/Currencies.php',7),(7,'system','Transactions','Dispatch Tax Province Maintenance','/TaxProvinces.php',10),(7,'system','Transactions','Form Designer','/FormDesigner.php',16),(7,'system','Transactions','Geocode Maintenance','/GeocodeSetup.php',15),(7,'system','Transactions','List Periods Defined','/PeriodsInquiry.php',12),(7,'system','Transactions','Mailing Group Maintenance','/MailingGroupMaintenance.php',19),(7,'system','Transactions','Maintain Security Tokens','/SecurityTokens.php',4),(7,'system','Transactions','Page Security Settings','/PageSecurity.php',6),(7,'system','Transactions','Report Builder Tool','/reportwriter/admin/ReportCreator.php',13),(7,'system','Transactions','SMTP Server Details','/SMTPServer.php',18),(7,'system','Transactions','System Parameters','/SystemParameters.php',2),(7,'system','Transactions','Tax Authorities and Rates Maintenance','/TaxAuthorities.php',8),(7,'system','Transactions','Tax Category Maintenance','/TaxCategories.php',11),(7,'system','Transactions','Tax Group Maintenance','/TaxGroups.php',9),(7,'system','Transactions','Users Maintenance','/WWW_Users.php',3),(7,'system','Transactions','View Audit Trail','/AuditTrail.php',14),(7,'system','Transactions','Web-Store Configuration','/ShopParameters.php',17),(7,'Utilities','Maintenance','Create new company template SQL file and submit to webERP','/Z_CreateCompanyTemplateFile.php',9),(7,'Utilities','Maintenance','Data Export Options','/Z_DataExport.php',3),(7,'Utilities','Maintenance','Import Customers from .csv file','/Z_ImportDebtors.php',4),(7,'Utilities','Maintenance','Import Fixed Assets from .csv file','/Z_ImportFixedAssets.php',7),(7,'Utilities','Maintenance','Import GL Payments Receipts Or Journals From .csv file','/Z_ImportGLTransactions.php',8),(7,'Utilities','Maintenance','Import Price List from .csv file','/Z_ImportPriceList.php',6),(7,'Utilities','Maintenance','Import Stock Items from .csv','/Z_ImportStocks.php',5),(7,'Utilities','Maintenance','Maintain Language Files','/Z_poAdmin.php',1),(7,'Utilities','Maintenance','Make New Company','/Z_MakeNewCompany.php',2),(7,'Utilities','Maintenance','Purge all old prices','/Z_DeleteOldPrices.php',12),(7,'Utilities','Maintenance','Remove all purchase back orders','/Z_RemovePurchaseBackOrders.php',13),(7,'Utilities','Reports','Debtors Balances By Currency Totals','/Z_CurrencyDebtorsBalances.php',1),(7,'Utilities','Reports','List of items without picture','/Z_ItemsWithoutPicture.php',4),(7,'Utilities','Reports','Show General Transactions That Do Not Balance','/Z_CheckGLTransBalance.php',3),(7,'Utilities','Reports','Suppliers Balances By Currency Totals','/Z_CurrencySuppliersBalances.php',2),(7,'Utilities','Transactions','Change A Customer Branch Code','/Z_ChangeBranchCode.php',2),(7,'Utilities','Transactions','Change A Customer Code','/Z_ChangeCustomerCode.php',1),(7,'Utilities','Transactions','Change A GL Account Code','/Z_ChangeGLAccountCode.php',3),(7,'Utilities','Transactions','Change A Location Code','/Z_ChangeLocationCode.php',5),(7,'Utilities','Transactions','Change A Salesman Code','/Z_ChangeSalesmanCode.php',6),(7,'Utilities','Transactions','Change A Stock Category Code','/Z_ChangeStockCategory.php',7),(7,'Utilities','Transactions','Change A Supplier Code','/Z_ChangeSupplierCode.php',8),(7,'Utilities','Transactions','Change An Inventory Item Code','/Z_ChangeStockCode.php',4),(7,'Utilities','Transactions','Copy Authority of GL Accounts from one user to another','/Z_GLAccountUsersCopyAuthority.php',15),(7,'Utilities','Transactions','Delete sales transactions','/Z_DeleteSalesTransActions.php',12),(7,'Utilities','Transactions','Re-apply costs to Sales Analysis','/Z_ReApplyCostToSA.php',11),(7,'Utilities','Transactions','Reverse all supplier payments on a specified date','/Z_ReverseSuppPaymentRun.php',13),(7,'Utilities','Transactions','Translate Item Descriptions','/AutomaticTranslationDescriptions.php',9),(7,'Utilities','Transactions','Update costs for all BOM items, from the bottom up','/Z_BottomUpCosts.php',10),(7,'Utilities','Transactions','Update sales analysis with latest customer data','/Z_UpdateSalesAnalysisWithLatestCustomerData.php',14),(8,'AP','Maintenance','Add Supplier','/Suppliers.php',1),(8,'AP','Maintenance','Maintain Factor Companies','/Factors.php',3),(8,'AP','Maintenance','Select Supplier','/SelectSupplier.php',2),(8,'AP','Reports','Aged Supplier Report','/AgedSuppliers.php',2),(8,'AP','Reports','List Daily Transactions','/PDFSuppTransListing.php',7),(8,'AP','Reports','Outstanding GRNs Report','/OutstandingGRNs.php',5),(8,'AP','Reports','Payment Run Report','/SuppPaymentRun.php',3),(8,'AP','Reports','Remittance Advices','/PDFRemittanceAdvice.php',4),(8,'AP','Reports','Supplier Balances At A Prior Month End','/SupplierBalsAtPeriodEnd.php',6),(8,'AP','Reports','Supplier Transaction Inquiries','/SupplierTransInquiry.php',8),(8,'AP','Reports','Where Allocated Inquiry','/SuppWhereAlloc.php',1),(8,'AP','Transactions','Select Supplier','/SelectSupplier.php',1),(8,'AP','Transactions','Supplier Allocations','/SupplierAllocations.php',2),(8,'AR','Maintenance','Add Customer','/Customers.php',1),(8,'AR','Maintenance','Select Customer','/SelectCustomer.php',2),(8,'AR','Reports','Aged Customer Balances/Overdues Report','/AgedDebtors.php',4),(8,'AR','Reports','Customer Activity and Balances','/CustomerBalancesMovement.php',10),(8,'AR','Reports','Customer Listing By Area/Salesperson','/PDFCustomerList.php',7),(8,'AR','Reports','Customer Transaction Inquiries','/CustomerTransInquiry.php',9),(8,'AR','Reports','Debtor Balances At A Prior Month End','/DebtorsAtPeriodEnd.php',6),(8,'AR','Reports','List Daily Transactions','/PDFCustTransListing.php',8),(8,'AR','Reports','Print Invoices or Credit Notes','/PrintCustTrans.php',2),(8,'AR','Reports','Print Statements','/PrintCustStatements.php',3),(8,'AR','Reports','Re-Print A Deposit Listing','/PDFBankingSummary.php',5),(8,'AR','Reports','Where Allocated Inquiry','/CustWhereAlloc.php',1),(8,'AR','Transactions','Allocate Receipts or Credit Notes','/CustomerAllocations.php',4),(8,'AR','Transactions','Create A Credit Note','/SelectCreditItems.php?NewCredit=Yes',2),(8,'AR','Transactions','Enter Receipts','/CustomerReceipt.php?NewReceipt=Yes&amp;Type=Customer',3),(8,'AR','Transactions','Select Order to Invoice','/SelectSalesOrder.php',1),(8,'FA','Maintenance','Add or Maintain Asset Locations','/FixedAssetLocations.php',2),(8,'FA','Maintenance','Fixed Asset Category Maintenance','/FixedAssetCategories.php',1),(8,'FA','Maintenance','Fixed Asset Maintenance Tasks','/MaintenanceTasks.php',3),(8,'FA','Reports','Asset Register','/FixedAssetRegister.php',1),(8,'FA','Reports','Maintenance Reminder Emails','/MaintenanceReminders.php',3),(8,'FA','Reports','My Maintenance Schedule','/MaintenanceUserSchedule.php',2),(8,'FA','Transactions','Add a new Asset','/FixedAssetItems.php',1),(8,'FA','Transactions','Change Asset Location','/FixedAssetTransfer.php',3),(8,'FA','Transactions','Depreciation Journal','/FixedAssetDepreciation.php',4),(8,'FA','Transactions','Select an Asset','/SelectAsset.php',2),(8,'GL','Maintenance','Account Groups','/AccountGroups.php',4),(8,'GL','Maintenance','Account Sections','/AccountSections.php',1),(8,'GL','Maintenance','Bank Account Authorised Users','/BankAccountUsers.php',11),(8,'GL','Maintenance','Bank Accounts','/BankAccounts.php',10),(8,'GL','Maintenance','Copy Authority Bank Accounts from one user to another','/GLBankAccountUsersCopyAuthority.php',21),(8,'GL','Maintenance','Copy Authority GL Accounts from one user to another','/GLAccountUsersCopyAuthority.php',20),(8,'GL','Maintenance','Create Budget Amounts','/GLBudgets.php',3),(8,'GL','Maintenance','Create/Amend General Ledger Budgets','/GLBudgetHeaders.php',2),(8,'GL','Maintenance','GL Account','/GLAccounts.php',5),(8,'GL','Maintenance','GL Account Authorised Users','/GLAccountUsers.php',6),(8,'GL','Maintenance','GL Tags','/GLTags.php',9),(8,'GL','Maintenance','Maintain Journal Templates','/GLJournalTemplates.php',13),(8,'GL','Maintenance','Setup Regular Payments','/RegularPaymentsSetup.php',14),(8,'GL','Maintenance','User Authorised Bank Accounts','/UserBankAccounts.php',12),(8,'GL','Maintenance','User Authorised GL Accounts','/UserGLAccounts.php',7),(8,'GL','Reports','Account Inquiry','/SelectGLAccount.php',5),(8,'GL','Reports','Account Listing','/GLAccountReport.php',7),(8,'GL','Reports','Account Listing to CSV File','/GLAccountCSV.php',8),(8,'GL','Reports','Balance Sheet','/GLBalanceSheet.php',11),(8,'GL','Reports','Bank Account Balances','/BankAccountBalances.php',1),(8,'GL','Reports','Bank Account Reconciliation Statement','/BankReconciliation.php',2),(8,'GL','Reports','Cheque Payments Listing','/PDFChequeListing.php',3),(8,'GL','Reports','Daily Bank Transactions','/DailyBankTransactions.php',4),(8,'GL','Reports','Financial Statements','/GLStatements.php',14),(8,'GL','Reports','General Ledger Journal Inquiry','/GLJournalInquiry.php',9),(8,'GL','Reports','Graph of Account Transactions','/GLAccountGraph.php',6),(8,'GL','Reports','Horizontal Analysis of Statement of Comprehensive Income','/AnalysisHorizontalIncome.php',16),(8,'GL','Reports','Horizontal Analysis of Statement of Financial Position','/AnalysisHorizontalPosition.php',15),(8,'GL','Reports','Income and Expenditure by Tag','/GLTagProfit_Loss.php',17),(8,'GL','Reports','Profit and Loss Statement','/GLProfit_Loss.php',12),(8,'GL','Reports','Statement of Cash Flows','/GLCashFlowsIndirect.php',13),(8,'GL','Reports','Tax Reports','/Tax.php',19),(8,'GL','Reports','Trial Balance','/GLTrialBalance.php',10),(8,'GL','Transactions','Bank Account Payments Entry','/Payments.php?NewPayment=Yes',1),(8,'GL','Transactions','Bank Account Payments Matching','/BankMatching.php?Type=Payments',4),(8,'GL','Transactions','Bank Account Receipts Entry','/CustomerReceipt.php?NewReceipt=Yes&amp;Type=GL',2),(8,'GL','Transactions','Bank Account Receipts Matching','/BankMatching.php?Type=Receipts',5),(8,'GL','Transactions','Journal Entry','/GLJournal.php?NewJournal=Yes',3),(8,'GL','Transactions','Import Bank Transactions','/ImportBankTrans.php',6),(8,'GL','Transactions','Process Regular Payments','/RegularPaymentsProcess.php',7),(8,'manuf','Maintenance','Auto Create Master Schedule','/MRPCreateDemands.php',5),(8,'manuf','Maintenance','Bills Of Material','/BOMs.php',2),(8,'manuf','Maintenance','Copy a Bill Of Materials Between Items','/CopyBOM.php',3),(8,'manuf','Maintenance','Employees','/Employees.php',9),(8,'manuf','Maintenance','Master Schedule','/MRPDemands.php',4),(8,'manuf','Maintenance','MRP Calculation','/MRP.php',6),(8,'manuf','Maintenance','Product Specifications','/ProductSpecs.php',8),(8,'manuf','Maintenance','Quality Tests Maintenance','/QATests.php',7),(8,'manuf','Maintenance','Work Centre','/WorkCentres.php',1),(8,'manuf','Reports','Bill Of Material Listing','/BOMListing.php',4),(8,'manuf','Reports','Costed Bill Of Material Inquiry','/BOMInquiry.php',2),(8,'manuf','Reports','Historical QA Test Results','/HistoricalTestResults.php',17),(8,'manuf','Reports','Indented Bill Of Material Listing','/BOMIndented.php',5),(8,'manuf','Reports','Indented Where Used Listing','/BOMIndentedReverse.php',9),(8,'manuf','Reports','List Components Required','/BOMExtendedQty.php',6),(8,'manuf','Reports','List Materials Not Used anywhere','/MaterialsNotUsed.php',7),(8,'manuf','Reports','MRP','/MRPReport.php',10),(8,'manuf','Reports','MRP Reschedules Required','/MRPReschedules.php',14),(8,'manuf','Reports','MRP Shortages','/MRPShortages.php',11),(8,'manuf','Reports','MRP Suggested Purchase Orders','/MRPPlannedPurchaseOrders.php',12),(8,'manuf','Reports','MRP Suggested Work Orders','/MRPPlannedWorkOrders.php',13),(8,'manuf','Reports','Multiple Work Orders Total Cost Inquiry','/CollectiveWorkOrderCost.php',18),(8,'manuf','Reports','Print Certificate of Analysis','/PDFCOA.php',16),(8,'manuf','Reports','Print Product Specification','/PDFProdSpec.php',15),(8,'manuf','Reports','Select A Work Order','/SelectWorkOrder.php',1),(8,'manuf','Reports','Where Used Inquiry','/WhereUsedInquiry.php',3),(8,'manuf','Reports','WO Items ready to produce','/WOCanBeProducedNow.php',8),(8,'manuf','Transactions','QA Samples and Test Results','/SelectQASamples.php',3),(8,'manuf','Transactions','Select A Work Order','/SelectWorkOrder.php',2),(8,'manuf','Transactions','Timesheet Entry','/Timesheets.php',4),(8,'manuf','Transactions','Work Order Entry','/WorkOrderEntry.php?New=True',1),(8,'PC','Maintenance','Expenses for Type of PC Tab','/PcExpensesTypeTab.php',4),(8,'PC','Maintenance','PC Expenses','/PcExpenses.php',3),(8,'PC','Maintenance','PC Tabs','/PcTabs.php',2),(8,'PC','Maintenance','Types of PC Tabs','/PcTypeTabs.php',1),(8,'PC','Reports','PC Expense General Report','/PcReportExpense.php',2),(8,'PC','Reports','PC Expenses Analysis','/PcAnalysis.php',4),(8,'PC','Reports','PC Tab Expenses List','/PcTabExpensesList.php',3),(8,'PC','Reports','PC Tab General Report','/PcReportTab.php',1),(8,'PC','Transactions','Assign Cash to PC Tab','/PcAssignCashToTab.php',1),(8,'PC','Transactions','Authorise Assigned Cash','/PcAuthorizeCash.php',5),(8,'PC','Transactions','Authorise Expenses','/PcAuthorizeExpenses.php',4),(8,'PC','Transactions','Claim Expenses From PC Tab','/PcClaimExpensesFromTab.php',3),(8,'PC','Transactions','Transfer Assigned Cash Between PC Tabs','/PcAssignCashTabToTab.php',2),(8,'PO','Maintenance','Maintain Supplier Price Lists','/SupplierPriceList.php',1),(8,'PO','Reports','Purchase Order Detail Or Summary Inquiries','/POReport.php',2),(8,'PO','Reports','Purchase Order Inquiry','/PO_SelectPurchOrder.php',1),(8,'PO','Reports','Purchase Orders Financial Planning','/POFinancialPlanning.php',20),(8,'PO','Reports','Purchases from Suppliers','/PurchasesReport.php',4),(8,'PO','Reports','Supplier Price List','/SuppPriceList.php',3),(8,'PO','Transactions','Create a New Tender','/SupplierTenderCreate.php?New=Yes',4),(8,'PO','Transactions','Edit Existing Tenders','/SupplierTenderCreate.php?Edit=Yes',5),(8,'PO','Transactions','New Purchase Order','/PO_Header.php?NewOrder=Yes',1),(8,'PO','Transactions','Orders to Authorise','/PO_AuthoriseMyOrders.php',7),(8,'PO','Transactions','Process Tenders and Offers','/OffersReceived.php',6),(8,'PO','Transactions','Purchase Order Grid Entry','/PurchaseByPrefSupplier.php',3),(8,'PO','Transactions','Purchase Orders','/PO_SelectOSPurchOrder.php',2),(8,'PO','Transactions','Select A Shipment','/Shipt_Select.php',9),(8,'PO','Transactions','Shipment Entry','/SelectSupplier.php',8),(8,'Sales','Maintenance','Create Contract','/Contracts.php',1),(8,'Sales','Maintenance','Select Contract','/SelectContract.php',2),(8,'Sales','Maintenance','Sell Through Support Deals','/SellThroughSupport.php',3),(8,'Sales','Reports','Daily Sales Inquiry','/DailySalesInquiry.php',5),(8,'Sales','Reports','Delivery In Full On Time (DIFOT) Report','/PDFDIFOT.php',13),(8,'Sales','Reports','Order Delivery Differences Report','/PDFDeliveryDifferences.php',12),(8,'Sales','Reports','Order Status Report','/PDFOrderStatus.php',3),(8,'Sales','Reports','Orders Invoiced Reports','/PDFOrdersInvoiced.php',4),(8,'Sales','Reports','Print Price Lists','/PDFPriceList.php',2),(8,'Sales','Reports','Sales Analysis Reports','/SalesAnalRepts.php',9),(8,'Sales','Reports','Sales By Category By Item Inquiry','/StockCategorySalesInquiry.php',8),(8,'Sales','Reports','Sales By Category Inquiry','/SalesCategoryPeriodInquiry.php',7),(8,'Sales','Reports','Sales By Sales Type Inquiry','/SalesByTypePeriodInquiry.php',6),(8,'Sales','Reports','Sales Commission Reports','/SalesCommissionReports.php',21),(8,'Sales','Reports','Sales Graphs','/SalesGraph.php',10),(8,'Sales','Reports','Sales Order Detail Or Summary Inquiries','/SalesInquiry.php',14),(8,'Sales','Reports','Sales Order Inquiry','/SelectCompletedOrder.php',1),(8,'Sales','Reports','Sales to Customers','/SalesReport.php',20),(8,'Sales','Reports','Sales With Low Gross Profit Report','/PDFLowGP.php',18),(8,'Sales','Reports','Sell Through Support Claims Report','/PDFSellThroughSupportClaim.php',19),(8,'Sales','Reports','Top Customers Inquiry','/SalesTopCustomersInquiry.php',16),(8,'Sales','Reports','Top Sales Items Report','/TopItems.php',15),(8,'Sales','Reports','Top Sellers Inquiry','/SalesTopItemsInquiry.php',11),(8,'Sales','Reports','Worst Sales Items Report','/NoSalesItems.php',17),(8,'Sales','Transactions','Enter Counter Returns','/CounterReturns.php',3),(8,'Sales','Transactions','Enter Counter Sales','/CounterSales.php',2),(8,'Sales','Transactions','Generate/Print Picking Lists','/PDFPickingList.php',4),(8,'Sales','Transactions','Maintain Picking Lists','/SelectPickingLists.php',9),(8,'Sales','Transactions','New Sales Order or Quotation','/SelectOrderItems.php?NewOrder=Yes',1),(8,'Sales','Transactions','Outstanding Sales Orders/Quotations','/SelectSalesOrder.php',5),(8,'Sales','Transactions','Process Recurring Orders','/RecurringSalesOrdersProcess.php',8),(8,'Sales','Transactions','Recurring Order Template','/SelectRecurringSalesOrder.php',7),(8,'Sales','Transactions','Special Order','/SpecialOrder.php',6),(8,'stock','Maintenance','Add A New Item','/Stocks.php',1),(8,'stock','Maintenance','Add or Update Prices Based On Costs','/PricesBasedOnMarkUp.php',6),(8,'stock','Maintenance','Brands Maintenance','/Manufacturers.php',5),(8,'stock','Maintenance','Reorder Level By Category/Location','/ReorderLevelLocation.php',9),(8,'stock','Maintenance','Review Translated Descriptions','/RevisionTranslations.php',3),(8,'stock','Maintenance','Sales Category Maintenance','/SalesCategories.php',4),(8,'stock','Maintenance','Select An Item','/SelectProduct.php',2),(8,'stock','Maintenance','Upload new prices from csv file','/UploadPriceList.php',8),(8,'stock','Maintenance','View or Update Prices Based On Costs','/PricesByCost.php',7),(8,'stock','Reports','Aged Controlled Stock Report','/AgedControlledInventory.php',23),(8,'stock','Reports','All Inventory Movements By Location/Date','/StockLocMovements.php',17),(8,'stock','Reports','Compare Counts Vs Stock Check Data','/PDFStockCheckComparison.php',16),(8,'stock','Reports','Historical Stock Quantity By Location/Category','/StockQuantityByDate.php',19),(8,'stock','Reports','Internal stock request inquiry','/InternalStockRequestInquiry.php',24),(8,'stock','Reports','Inventory Item Movements','/StockMovements.php',4),(8,'stock','Reports','Inventory Item Status','/StockStatus.php',5),(8,'stock','Reports','Inventory Item Usage','/StockUsage.php',6),(8,'stock','Reports','Inventory Planning Based On Preferred Supplier Data','/InventoryPlanningPrefSupplier.php',13),(8,'stock','Reports','Inventory Planning Report','/InventoryPlanning.php',12),(8,'stock','Reports','Inventory Quantities','/InventoryQuantities.php',7),(8,'stock','Reports','Inventory Stock Check Sheets','/StockCheck.php',14),(8,'stock','Reports','Inventory Valuation Report','/InventoryValuation.php',10),(8,'stock','Reports','List Inventory Status By Location/Category','/StockLocStatus.php',18),(8,'stock','Reports','List Negative Stocks','/PDFStockNegatives.php',20),(8,'stock','Reports','Mail Inventory Valuation Report','/MailInventoryValuation.php',11),(8,'stock','Reports','Make Inventory Quantities CSV','/StockQties_csv.php',15),(8,'stock','Reports','Period Stock Transaction Listing','/PDFPeriodStockTransListing.php',21),(8,'stock','Reports','Print Price Labels','/PDFPrintLabel.php',2),(8,'stock','Reports','Reorder Level','/ReorderLevel.php',8),(8,'stock','Reports','Reprint GRN','/ReprintGRN.php',3),(8,'stock','Reports','Serial Item Research Tool','/StockSerialItemResearch.php',1),(8,'stock','Reports','Stock Dispatch','/StockDispatch.php',9),(8,'stock','Reports','Stock Transfer Note','/PDFStockTransfer.php',22),(8,'stock','Transactions','Authorise Internal Stock Requests','/InternalStockRequestAuthorisation.php',9),(8,'stock','Transactions','Bulk Inventory Transfer - Dispatch','/StockLocTransfer.php',3),(8,'stock','Transactions','Bulk Inventory Transfer - Receive','/StockLocTransferReceive.php',4),(8,'stock','Transactions','Create a New Internal Stock Request','/InternalStockRequest.php?New=Yes',8),(8,'stock','Transactions','Enter Stock Counts','/StockCounts.php',7),(8,'stock','Transactions','Fulfil Internal Stock Requests','/InternalStockRequestFulfill.php',10),(8,'stock','Transactions','Inventory Adjustments','/StockAdjustments.php?NewAdjustment=Yes',5),(8,'stock','Transactions','Inventory Location Transfers','/StockTransfers.php?New=Yes',2),(8,'stock','Transactions','Receive Purchase Orders','/PO_SelectOSPurchOrder.php',1),(8,'stock','Transactions','Reverse Goods Received','/ReverseGRN.php',6),(8,'system','Maintenance','Copy Authority Locations from one user to another','/LocationUsersCopyAuthority.php',20),(8,'system','Maintenance','Dashboard Configuration','/DashboardConfig.php',13),(8,'system','Maintenance','Discount Category Maintenance','/DiscountCategories.php',5),(8,'system','Maintenance','Inventory Categories Maintenance','/StockCategories.php',1),(8,'system','Maintenance','Inventory Location Authorised Users Maintenance','/LocationUsers.php',3),(8,'system','Maintenance','Inventory Locations Maintenance','/Locations.php',2),(8,'system','Maintenance','Label Templates Maintenance','/Labels.php',12),(8,'system','Maintenance','Logged in users','/LoggedInUsers.php',8),(8,'system','Maintenance','Maintain Internal Departments','/Departments.php',10),(8,'system','Maintenance','Maintain Internal Stock Categories to User Roles','/InternalStockCategoriesByRole.php',11),(8,'system','Maintenance','MRP Available Production Days','/MRPCalendar.php',7),(8,'system','Maintenance','MRP Demand Types','/MRPDemandTypes.php',9),(8,'system','Maintenance','Units of Measure','/UnitsOfMeasure.php',6),(8,'system','Maintenance','User Authorised Inventory Locations Maintenance','/UserLocations.php',4),(8,'system','Reports','COGS GL Interface Postings','/COGSGLPostings.php',12),(8,'system','Reports','Credit Status','/CreditStatus.php',4),(8,'system','Reports','Customer Types','/CustomerTypes.php',2),(8,'system','Reports','Discount Matrix','/DiscountMatrix.php',14),(8,'system','Reports','Freight Costs Maintenance','/FreightCosts.php',13),(8,'system','Reports','Payment Methods','/PaymentMethods.php',7),(8,'system','Reports','Payment Terms','/PaymentTerms.php',5),(8,'system','Reports','Sales Areas','/Areas.php',9),(8,'system','Reports','Sales Commission Types','/SalesCommissionTypes.php',15),(8,'system','Reports','Sales GL Interface Postings','/SalesGLPostings.php',11),(8,'system','Reports','Sales People','/SalesPeople.php',8),(8,'system','Reports','Sales Types','/SalesTypes.php',1),(8,'system','Reports','Set Purchase Order Authorisation levels','/PO_AuthorisationLevels.php',6),(8,'system','Reports','Shippers','/Shippers.php',10),(8,'system','Reports','Supplier Types','/SupplierTypes.php',3),(8,'system','Transactions','Access Permissions Maintenance','/WWW_Access.php',5),(8,'system','Transactions','Company Preferences','/CompanyPreferences.php',1),(8,'system','Transactions','Currency Maintenance','/Currencies.php',7),(8,'system','Transactions','Dispatch Tax Province Maintenance','/TaxProvinces.php',10),(8,'system','Transactions','Form Designer','/FormDesigner.php',16),(8,'system','Transactions','Geocode Maintenance','/GeocodeSetup.php',15),(8,'system','Transactions','List Periods Defined','/PeriodsInquiry.php',12),(8,'system','Transactions','Mailing Group Maintenance','/MailingGroupMaintenance.php',19),(8,'system','Transactions','Maintain Security Tokens','/SecurityTokens.php',4),(8,'system','Transactions','Page Security Settings','/PageSecurity.php',6),(8,'system','Transactions','Report Builder Tool','/reportwriter/admin/ReportCreator.php',13),(8,'system','Transactions','SMTP Server Details','/SMTPServer.php',18),(8,'system','Transactions','System Parameters','/SystemParameters.php',2),(8,'system','Transactions','Tax Authorities and Rates Maintenance','/TaxAuthorities.php',8),(8,'system','Transactions','Tax Category Maintenance','/TaxCategories.php',11),(8,'system','Transactions','Tax Group Maintenance','/TaxGroups.php',9),(8,'system','Transactions','Users Maintenance','/WWW_Users.php',3),(8,'system','Transactions','View Audit Trail','/AuditTrail.php',14),(8,'system','Transactions','Web-Store Configuration','/ShopParameters.php',17),(8,'Utilities','Maintenance','Create new company template SQL file and submit to webERP','/Z_CreateCompanyTemplateFile.php',9),(8,'Utilities','Maintenance','Data Export Options','/Z_DataExport.php',3),(8,'Utilities','Maintenance','Import Customers from .csv file','/Z_ImportDebtors.php',4),(8,'Utilities','Maintenance','Import Fixed Assets from .csv file','/Z_ImportFixedAssets.php',7),(8,'Utilities','Maintenance','Import GL Payments Receipts Or Journals From .csv file','/Z_ImportGLTransactions.php',8),(8,'Utilities','Maintenance','Import Price List from .csv file','/Z_ImportPriceList.php',6),(8,'Utilities','Maintenance','Import Stock Items from .csv','/Z_ImportStocks.php',5),(8,'Utilities','Maintenance','Maintain Language Files','/Z_poAdmin.php',1),(8,'Utilities','Maintenance','Make New Company','/Z_MakeNewCompany.php',2),(8,'Utilities','Maintenance','Purge all old prices','/Z_DeleteOldPrices.php',12),(8,'Utilities','Maintenance','Remove all purchase back orders','/Z_RemovePurchaseBackOrders.php',13),(8,'Utilities','Reports','Debtors Balances By Currency Totals','/Z_CurrencyDebtorsBalances.php',1),(8,'Utilities','Reports','List of items without picture','/Z_ItemsWithoutPicture.php',4),(8,'Utilities','Reports','Show General Transactions That Do Not Balance','/Z_CheckGLTransBalance.php',3),(8,'Utilities','Reports','Suppliers Balances By Currency Totals','/Z_CurrencySuppliersBalances.php',2),(8,'Utilities','Transactions','Change A Customer Branch Code','/Z_ChangeBranchCode.php',2),(8,'Utilities','Transactions','Change A Customer Code','/Z_ChangeCustomerCode.php',1),(8,'Utilities','Transactions','Change A GL Account Code','/Z_ChangeGLAccountCode.php',3),(8,'Utilities','Transactions','Change A Location Code','/Z_ChangeLocationCode.php',5),(8,'Utilities','Transactions','Change A Salesman Code','/Z_ChangeSalesmanCode.php',6),(8,'Utilities','Transactions','Change A Stock Category Code','/Z_ChangeStockCategory.php',7),(8,'Utilities','Transactions','Change A Supplier Code','/Z_ChangeSupplierCode.php',8),(8,'Utilities','Transactions','Change An Inventory Item Code','/Z_ChangeStockCode.php',4),(8,'Utilities','Transactions','Copy Authority of GL Accounts from one user to another','/Z_GLAccountUsersCopyAuthority.php',15),(8,'Utilities','Transactions','Delete sales transactions','/Z_DeleteSalesTransActions.php',12),(8,'Utilities','Transactions','Re-apply costs to Sales Analysis','/Z_ReApplyCostToSA.php',11),(8,'Utilities','Transactions','Reverse all supplier payments on a specified date','/Z_ReverseSuppPaymentRun.php',13),(8,'Utilities','Transactions','Translate Item Descriptions','/AutomaticTranslationDescriptions.php',9),(8,'Utilities','Transactions','Update costs for all BOM items, from the bottom up','/Z_BottomUpCosts.php',10),(8,'Utilities','Transactions','Update sales analysis with latest customer data','/Z_UpdateSalesAnalysisWithLatestCustomerData.php',14),(9,'AP','Maintenance','Add Supplier','/Suppliers.php',1),(9,'AP','Maintenance','Maintain Factor Companies','/Factors.php',3),(9,'AP','Maintenance','Select Supplier','/SelectSupplier.php',2),(9,'AP','Reports','Aged Supplier Report','/AgedSuppliers.php',2),(9,'AP','Reports','List Daily Transactions','/PDFSuppTransListing.php',7),(9,'AP','Reports','Outstanding GRNs Report','/OutstandingGRNs.php',5),(9,'AP','Reports','Payment Run Report','/SuppPaymentRun.php',3),(9,'AP','Reports','Remittance Advices','/PDFRemittanceAdvice.php',4),(9,'AP','Reports','Supplier Balances At A Prior Month End','/SupplierBalsAtPeriodEnd.php',6),(9,'AP','Reports','Supplier Transaction Inquiries','/SupplierTransInquiry.php',8),(9,'AP','Reports','Where Allocated Inquiry','/SuppWhereAlloc.php',1),(9,'AP','Transactions','Select Supplier','/SelectSupplier.php',1),(9,'AP','Transactions','Supplier Allocations','/SupplierAllocations.php',2),(9,'AR','Maintenance','Add Customer','/Customers.php',1),(9,'AR','Maintenance','Select Customer','/SelectCustomer.php',2),(9,'AR','Reports','Aged Customer Balances/Overdues Report','/AgedDebtors.php',4),(9,'AR','Reports','Customer Activity and Balances','/CustomerBalancesMovement.php',10),(9,'AR','Reports','Customer Listing By Area/Salesperson','/PDFCustomerList.php',7),(9,'AR','Reports','Customer Transaction Inquiries','/CustomerTransInquiry.php',9),(9,'AR','Reports','Debtor Balances At A Prior Month End','/DebtorsAtPeriodEnd.php',6),(9,'AR','Reports','List Daily Transactions','/PDFCustTransListing.php',8),(9,'AR','Reports','Print Invoices or Credit Notes','/PrintCustTrans.php',2),(9,'AR','Reports','Print Statements','/PrintCustStatements.php',3),(9,'AR','Reports','Re-Print A Deposit Listing','/PDFBankingSummary.php',5),(9,'AR','Reports','Where Allocated Inquiry','/CustWhereAlloc.php',1),(9,'AR','Transactions','Allocate Receipts or Credit Notes','/CustomerAllocations.php',4),(9,'AR','Transactions','Create A Credit Note','/SelectCreditItems.php?NewCredit=Yes',2),(9,'AR','Transactions','Enter Receipts','/CustomerReceipt.php?NewReceipt=Yes&amp;Type=Customer',3),(9,'AR','Transactions','Select Order to Invoice','/SelectSalesOrder.php',1),(9,'FA','Maintenance','Add or Maintain Asset Locations','/FixedAssetLocations.php',2),(9,'FA','Maintenance','Fixed Asset Category Maintenance','/FixedAssetCategories.php',1),(9,'FA','Maintenance','Fixed Asset Maintenance Tasks','/MaintenanceTasks.php',3),(9,'FA','Reports','Asset Register','/FixedAssetRegister.php',1),(9,'FA','Reports','Maintenance Reminder Emails','/MaintenanceReminders.php',3),(9,'FA','Reports','My Maintenance Schedule','/MaintenanceUserSchedule.php',2),(9,'FA','Transactions','Add a new Asset','/FixedAssetItems.php',1),(9,'FA','Transactions','Change Asset Location','/FixedAssetTransfer.php',3),(9,'FA','Transactions','Depreciation Journal','/FixedAssetDepreciation.php',4),(9,'FA','Transactions','Select an Asset','/SelectAsset.php',2),(9,'GL','Maintenance','Account Groups','/AccountGroups.php',4),(9,'GL','Maintenance','Account Sections','/AccountSections.php',1),(9,'GL','Maintenance','Bank Account Authorised Users','/BankAccountUsers.php',11),(9,'GL','Maintenance','Bank Accounts','/BankAccounts.php',10),(9,'GL','Maintenance','Copy Authority Bank Accounts from one user to another','/GLBankAccountUsersCopyAuthority.php',21),(9,'GL','Maintenance','Copy Authority GL Accounts from one user to another','/GLAccountUsersCopyAuthority.php',20),(9,'GL','Maintenance','Create Budget Amounts','/GLBudgets.php',3),(9,'GL','Maintenance','Create/Amend General Ledger Budgets','/GLBudgetHeaders.php',2),(9,'GL','Maintenance','GL Account','/GLAccounts.php',5),(9,'GL','Maintenance','GL Account Authorised Users','/GLAccountUsers.php',6),(9,'GL','Maintenance','GL Tags','/GLTags.php',9),(9,'GL','Maintenance','Maintain Journal Templates','/GLJournalTemplates.php',13),(9,'GL','Maintenance','Setup Regular Payments','/RegularPaymentsSetup.php',14),(9,'GL','Maintenance','User Authorised Bank Accounts','/UserBankAccounts.php',12),(9,'GL','Maintenance','User Authorised GL Accounts','/UserGLAccounts.php',7),(9,'GL','Reports','Account Inquiry','/SelectGLAccount.php',5),(9,'GL','Reports','Account Listing','/GLAccountReport.php',7),(9,'GL','Reports','Account Listing to CSV File','/GLAccountCSV.php',8),(9,'GL','Reports','Balance Sheet','/GLBalanceSheet.php',11),(9,'GL','Reports','Bank Account Balances','/BankAccountBalances.php',1),(9,'GL','Reports','Bank Account Reconciliation Statement','/BankReconciliation.php',2),(9,'GL','Reports','Cheque Payments Listing','/PDFChequeListing.php',3),(9,'GL','Reports','Daily Bank Transactions','/DailyBankTransactions.php',4),(9,'GL','Reports','Financial Statements','/GLStatements.php',14),(9,'GL','Reports','General Ledger Journal Inquiry','/GLJournalInquiry.php',9),(9,'GL','Reports','Graph of Account Transactions','/GLAccountGraph.php',6),(9,'GL','Reports','Horizontal Analysis of Statement of Comprehensive Income','/AnalysisHorizontalIncome.php',16),(9,'GL','Reports','Horizontal Analysis of Statement of Financial Position','/AnalysisHorizontalPosition.php',15),(9,'GL','Reports','Income and Expenditure by Tag','/GLTagProfit_Loss.php',17),(9,'GL','Reports','Profit and Loss Statement','/GLProfit_Loss.php',12),(9,'GL','Reports','Statement of Cash Flows','/GLCashFlowsIndirect.php',13),(9,'GL','Reports','Tax Reports','/Tax.php',19),(9,'GL','Reports','Trial Balance','/GLTrialBalance.php',10),(9,'GL','Transactions','Bank Account Payments Entry','/Payments.php?NewPayment=Yes',1),(9,'GL','Transactions','Bank Account Payments Matching','/BankMatching.php?Type=Payments',4),(9,'GL','Transactions','Bank Account Receipts Entry','/CustomerReceipt.php?NewReceipt=Yes&amp;Type=GL',2),(9,'GL','Transactions','Bank Account Receipts Matching','/BankMatching.php?Type=Receipts',5),(9,'GL','Transactions','Journal Entry','/GLJournal.php?NewJournal=Yes',3),(9,'GL','Transactions','Import Bank Transactions','/ImportBankTrans.php',6),(9,'GL','Transactions','Process Regular Payments','/RegularPaymentsProcess.php',7),(9,'manuf','Maintenance','Auto Create Master Schedule','/MRPCreateDemands.php',5),(9,'manuf','Maintenance','Bills Of Material','/BOMs.php',2),(9,'manuf','Maintenance','Copy a Bill Of Materials Between Items','/CopyBOM.php',3),(9,'manuf','Maintenance','Employees','/Employees.php',9),(9,'manuf','Maintenance','Master Schedule','/MRPDemands.php',4),(9,'manuf','Maintenance','MRP Calculation','/MRP.php',6),(9,'manuf','Maintenance','Product Specifications','/ProductSpecs.php',8),(9,'manuf','Maintenance','Quality Tests Maintenance','/QATests.php',7),(9,'manuf','Maintenance','Work Centre','/WorkCentres.php',1),(9,'manuf','Reports','Bill Of Material Listing','/BOMListing.php',4),(9,'manuf','Reports','Costed Bill Of Material Inquiry','/BOMInquiry.php',2),(9,'manuf','Reports','Historical QA Test Results','/HistoricalTestResults.php',17),(9,'manuf','Reports','Indented Bill Of Material Listing','/BOMIndented.php',5),(9,'manuf','Reports','Indented Where Used Listing','/BOMIndentedReverse.php',9),(9,'manuf','Reports','List Components Required','/BOMExtendedQty.php',6),(9,'manuf','Reports','List Materials Not Used anywhere','/MaterialsNotUsed.php',7),(9,'manuf','Reports','MRP','/MRPReport.php',10),(9,'manuf','Reports','MRP Reschedules Required','/MRPReschedules.php',14),(9,'manuf','Reports','MRP Shortages','/MRPShortages.php',11),(9,'manuf','Reports','MRP Suggested Purchase Orders','/MRPPlannedPurchaseOrders.php',12),(9,'manuf','Reports','MRP Suggested Work Orders','/MRPPlannedWorkOrders.php',13),(9,'manuf','Reports','Multiple Work Orders Total Cost Inquiry','/CollectiveWorkOrderCost.php',18),(9,'manuf','Reports','Print Certificate of Analysis','/PDFCOA.php',16),(9,'manuf','Reports','Print Product Specification','/PDFProdSpec.php',15),(9,'manuf','Reports','Select A Work Order','/SelectWorkOrder.php',1),(9,'manuf','Reports','Where Used Inquiry','/WhereUsedInquiry.php',3),(9,'manuf','Reports','WO Items ready to produce','/WOCanBeProducedNow.php',8),(9,'manuf','Transactions','QA Samples and Test Results','/SelectQASamples.php',3),(9,'manuf','Transactions','Select A Work Order','/SelectWorkOrder.php',2),(9,'manuf','Transactions','Timesheet Entry','/Timesheets.php',4),(9,'manuf','Transactions','Work Order Entry','/WorkOrderEntry.php?New=True',1),(9,'PC','Maintenance','Expenses for Type of PC Tab','/PcExpensesTypeTab.php',4),(9,'PC','Maintenance','PC Expenses','/PcExpenses.php',3),(9,'PC','Maintenance','PC Tabs','/PcTabs.php',2),(9,'PC','Maintenance','Types of PC Tabs','/PcTypeTabs.php',1),(9,'PC','Reports','PC Expense General Report','/PcReportExpense.php',2),(9,'PC','Reports','PC Expenses Analysis','/PcAnalysis.php',4),(9,'PC','Reports','PC Tab Expenses List','/PcTabExpensesList.php',3),(9,'PC','Reports','PC Tab General Report','/PcReportTab.php',1),(9,'PC','Transactions','Assign Cash to PC Tab','/PcAssignCashToTab.php',1),(9,'PC','Transactions','Authorise Assigned Cash','/PcAuthorizeCash.php',5),(9,'PC','Transactions','Authorise Expenses','/PcAuthorizeExpenses.php',4),(9,'PC','Transactions','Claim Expenses From PC Tab','/PcClaimExpensesFromTab.php',3),(9,'PC','Transactions','Transfer Assigned Cash Between PC Tabs','/PcAssignCashTabToTab.php',2),(9,'PO','Maintenance','Maintain Supplier Price Lists','/SupplierPriceList.php',1),(9,'PO','Reports','Purchase Order Detail Or Summary Inquiries','/POReport.php',2),(9,'PO','Reports','Purchase Order Inquiry','/PO_SelectPurchOrder.php',1),(9,'PO','Reports','Purchase Orders Financial Planning','/POFinancialPlanning.php',20),(9,'PO','Reports','Purchases from Suppliers','/PurchasesReport.php',4),(9,'PO','Reports','Supplier Price List','/SuppPriceList.php',3),(9,'PO','Transactions','Create a New Tender','/SupplierTenderCreate.php?New=Yes',4),(9,'PO','Transactions','Edit Existing Tenders','/SupplierTenderCreate.php?Edit=Yes',5),(9,'PO','Transactions','New Purchase Order','/PO_Header.php?NewOrder=Yes',1),(9,'PO','Transactions','Orders to Authorise','/PO_AuthoriseMyOrders.php',7),(9,'PO','Transactions','Process Tenders and Offers','/OffersReceived.php',6),(9,'PO','Transactions','Purchase Order Grid Entry','/PurchaseByPrefSupplier.php',3),(9,'PO','Transactions','Purchase Orders','/PO_SelectOSPurchOrder.php',2),(9,'PO','Transactions','Select A Shipment','/Shipt_Select.php',9),(9,'PO','Transactions','Shipment Entry','/SelectSupplier.php',8),(9,'Sales','Maintenance','Create Contract','/Contracts.php',1),(9,'Sales','Maintenance','Select Contract','/SelectContract.php',2),(9,'Sales','Maintenance','Sell Through Support Deals','/SellThroughSupport.php',3),(9,'Sales','Reports','Daily Sales Inquiry','/DailySalesInquiry.php',5),(9,'Sales','Reports','Delivery In Full On Time (DIFOT) Report','/PDFDIFOT.php',13),(9,'Sales','Reports','Order Delivery Differences Report','/PDFDeliveryDifferences.php',12),(9,'Sales','Reports','Order Status Report','/PDFOrderStatus.php',3),(9,'Sales','Reports','Orders Invoiced Reports','/PDFOrdersInvoiced.php',4),(9,'Sales','Reports','Print Price Lists','/PDFPriceList.php',2),(9,'Sales','Reports','Sales Analysis Reports','/SalesAnalRepts.php',9),(9,'Sales','Reports','Sales By Category By Item Inquiry','/StockCategorySalesInquiry.php',8),(9,'Sales','Reports','Sales By Category Inquiry','/SalesCategoryPeriodInquiry.php',7),(9,'Sales','Reports','Sales By Sales Type Inquiry','/SalesByTypePeriodInquiry.php',6),(9,'Sales','Reports','Sales Commission Reports','/SalesCommissionReports.php',21),(9,'Sales','Reports','Sales Graphs','/SalesGraph.php',10),(9,'Sales','Reports','Sales Order Detail Or Summary Inquiries','/SalesInquiry.php',14),(9,'Sales','Reports','Sales Order Inquiry','/SelectCompletedOrder.php',1),(9,'Sales','Reports','Sales to Customers','/SalesReport.php',20),(9,'Sales','Reports','Sales With Low Gross Profit Report','/PDFLowGP.php',18),(9,'Sales','Reports','Sell Through Support Claims Report','/PDFSellThroughSupportClaim.php',19),(9,'Sales','Reports','Top Customers Inquiry','/SalesTopCustomersInquiry.php',16),(9,'Sales','Reports','Top Sales Items Report','/TopItems.php',15),(9,'Sales','Reports','Top Sellers Inquiry','/SalesTopItemsInquiry.php',11),(9,'Sales','Reports','Worst Sales Items Report','/NoSalesItems.php',17),(9,'Sales','Transactions','Enter Counter Returns','/CounterReturns.php',3),(9,'Sales','Transactions','Enter Counter Sales','/CounterSales.php',2),(9,'Sales','Transactions','Generate/Print Picking Lists','/PDFPickingList.php',4),(9,'Sales','Transactions','Maintain Picking Lists','/SelectPickingLists.php',9),(9,'Sales','Transactions','New Sales Order or Quotation','/SelectOrderItems.php?NewOrder=Yes',1),(9,'Sales','Transactions','Outstanding Sales Orders/Quotations','/SelectSalesOrder.php',5),(9,'Sales','Transactions','Process Recurring Orders','/RecurringSalesOrdersProcess.php',8),(9,'Sales','Transactions','Recurring Order Template','/SelectRecurringSalesOrder.php',7),(9,'Sales','Transactions','Special Order','/SpecialOrder.php',6),(9,'stock','Maintenance','Add A New Item','/Stocks.php',1),(9,'stock','Maintenance','Add or Update Prices Based On Costs','/PricesBasedOnMarkUp.php',6),(9,'stock','Maintenance','Brands Maintenance','/Manufacturers.php',5),(9,'stock','Maintenance','Reorder Level By Category/Location','/ReorderLevelLocation.php',9),(9,'stock','Maintenance','Review Translated Descriptions','/RevisionTranslations.php',3),(9,'stock','Maintenance','Sales Category Maintenance','/SalesCategories.php',4),(9,'stock','Maintenance','Select An Item','/SelectProduct.php',2),(9,'stock','Maintenance','Upload new prices from csv file','/UploadPriceList.php',8),(9,'stock','Maintenance','View or Update Prices Based On Costs','/PricesByCost.php',7),(9,'stock','Reports','Aged Controlled Stock Report','/AgedControlledInventory.php',23),(9,'stock','Reports','All Inventory Movements By Location/Date','/StockLocMovements.php',17),(9,'stock','Reports','Compare Counts Vs Stock Check Data','/PDFStockCheckComparison.php',16),(9,'stock','Reports','Historical Stock Quantity By Location/Category','/StockQuantityByDate.php',19),(9,'stock','Reports','Internal stock request inquiry','/InternalStockRequestInquiry.php',24),(9,'stock','Reports','Inventory Item Movements','/StockMovements.php',4),(9,'stock','Reports','Inventory Item Status','/StockStatus.php',5),(9,'stock','Reports','Inventory Item Usage','/StockUsage.php',6),(9,'stock','Reports','Inventory Planning Based On Preferred Supplier Data','/InventoryPlanningPrefSupplier.php',13),(9,'stock','Reports','Inventory Planning Report','/InventoryPlanning.php',12),(9,'stock','Reports','Inventory Quantities','/InventoryQuantities.php',7),(9,'stock','Reports','Inventory Stock Check Sheets','/StockCheck.php',14),(9,'stock','Reports','Inventory Valuation Report','/InventoryValuation.php',10),(9,'stock','Reports','List Inventory Status By Location/Category','/StockLocStatus.php',18),(9,'stock','Reports','List Negative Stocks','/PDFStockNegatives.php',20),(9,'stock','Reports','Mail Inventory Valuation Report','/MailInventoryValuation.php',11),(9,'stock','Reports','Make Inventory Quantities CSV','/StockQties_csv.php',15),(9,'stock','Reports','Period Stock Transaction Listing','/PDFPeriodStockTransListing.php',21),(9,'stock','Reports','Print Price Labels','/PDFPrintLabel.php',2),(9,'stock','Reports','Reorder Level','/ReorderLevel.php',8),(9,'stock','Reports','Reprint GRN','/ReprintGRN.php',3),(9,'stock','Reports','Serial Item Research Tool','/StockSerialItemResearch.php',1),(9,'stock','Reports','Stock Dispatch','/StockDispatch.php',9),(9,'stock','Reports','Stock Transfer Note','/PDFStockTransfer.php',22),(9,'stock','Transactions','Authorise Internal Stock Requests','/InternalStockRequestAuthorisation.php',9),(9,'stock','Transactions','Bulk Inventory Transfer - Dispatch','/StockLocTransfer.php',3),(9,'stock','Transactions','Bulk Inventory Transfer - Receive','/StockLocTransferReceive.php',4),(9,'stock','Transactions','Create a New Internal Stock Request','/InternalStockRequest.php?New=Yes',8),(9,'stock','Transactions','Enter Stock Counts','/StockCounts.php',7),(9,'stock','Transactions','Fulfil Internal Stock Requests','/InternalStockRequestFulfill.php',10),(9,'stock','Transactions','Inventory Adjustments','/StockAdjustments.php?NewAdjustment=Yes',5),(9,'stock','Transactions','Inventory Location Transfers','/StockTransfers.php?New=Yes',2),(9,'stock','Transactions','Receive Purchase Orders','/PO_SelectOSPurchOrder.php',1),(9,'stock','Transactions','Reverse Goods Received','/ReverseGRN.php',6),(9,'system','Maintenance','Copy Authority Locations from one user to another','/LocationUsersCopyAuthority.php',20),(9,'system','Maintenance','Dashboard Configuration','/DashboardConfig.php',13),(9,'system','Maintenance','Discount Category Maintenance','/DiscountCategories.php',5),(9,'system','Maintenance','Inventory Categories Maintenance','/StockCategories.php',1),(9,'system','Maintenance','Inventory Location Authorised Users Maintenance','/LocationUsers.php',3),(9,'system','Maintenance','Inventory Locations Maintenance','/Locations.php',2),(9,'system','Maintenance','Label Templates Maintenance','/Labels.php',12),(9,'system','Maintenance','Logged in users','/LoggedInUsers.php',8),(9,'system','Maintenance','Maintain Internal Departments','/Departments.php',10),(9,'system','Maintenance','Maintain Internal Stock Categories to User Roles','/InternalStockCategoriesByRole.php',11),(9,'system','Maintenance','MRP Available Production Days','/MRPCalendar.php',7),(9,'system','Maintenance','MRP Demand Types','/MRPDemandTypes.php',9),(9,'system','Maintenance','Units of Measure','/UnitsOfMeasure.php',6),(9,'system','Maintenance','User Authorised Inventory Locations Maintenance','/UserLocations.php',4),(9,'system','Reports','COGS GL Interface Postings','/COGSGLPostings.php',12),(9,'system','Reports','Credit Status','/CreditStatus.php',4),(9,'system','Reports','Customer Types','/CustomerTypes.php',2),(9,'system','Reports','Discount Matrix','/DiscountMatrix.php',14),(9,'system','Reports','Freight Costs Maintenance','/FreightCosts.php',13),(9,'system','Reports','Payment Methods','/PaymentMethods.php',7),(9,'system','Reports','Payment Terms','/PaymentTerms.php',5),(9,'system','Reports','Sales Areas','/Areas.php',9),(9,'system','Reports','Sales Commission Types','/SalesCommissionTypes.php',15),(9,'system','Reports','Sales GL Interface Postings','/SalesGLPostings.php',11),(9,'system','Reports','Sales People','/SalesPeople.php',8),(9,'system','Reports','Sales Types','/SalesTypes.php',1),(9,'system','Reports','Set Purchase Order Authorisation levels','/PO_AuthorisationLevels.php',6),(9,'system','Reports','Shippers','/Shippers.php',10),(9,'system','Reports','Supplier Types','/SupplierTypes.php',3),(9,'system','Transactions','Access Permissions Maintenance','/WWW_Access.php',5),(9,'system','Transactions','Company Preferences','/CompanyPreferences.php',1),(9,'system','Transactions','Currency Maintenance','/Currencies.php',7),(9,'system','Transactions','Dispatch Tax Province Maintenance','/TaxProvinces.php',10),(9,'system','Transactions','Form Designer','/FormDesigner.php',16),(9,'system','Transactions','Geocode Maintenance','/GeocodeSetup.php',15),(9,'system','Transactions','List Periods Defined','/PeriodsInquiry.php',12),(9,'system','Transactions','Mailing Group Maintenance','/MailingGroupMaintenance.php',19),(9,'system','Transactions','Maintain Security Tokens','/SecurityTokens.php',4),(9,'system','Transactions','Page Security Settings','/PageSecurity.php',6),(9,'system','Transactions','Report Builder Tool','/reportwriter/admin/ReportCreator.php',13),(9,'system','Transactions','SMTP Server Details','/SMTPServer.php',18),(9,'system','Transactions','System Parameters','/SystemParameters.php',2),(9,'system','Transactions','Tax Authorities and Rates Maintenance','/TaxAuthorities.php',8),(9,'system','Transactions','Tax Category Maintenance','/TaxCategories.php',11),(9,'system','Transactions','Tax Group Maintenance','/TaxGroups.php',9),(9,'system','Transactions','Users Maintenance','/WWW_Users.php',3),(9,'system','Transactions','View Audit Trail','/AuditTrail.php',14),(9,'system','Transactions','Web-Store Configuration','/ShopParameters.php',17),(9,'Utilities','Maintenance','Create new company template SQL file and submit to webERP','/Z_CreateCompanyTemplateFile.php',9),(9,'Utilities','Maintenance','Data Export Options','/Z_DataExport.php',3),(9,'Utilities','Maintenance','Import Customers from .csv file','/Z_ImportDebtors.php',4),(9,'Utilities','Maintenance','Import Fixed Assets from .csv file','/Z_ImportFixedAssets.php',7),(9,'Utilities','Maintenance','Import GL Payments Receipts Or Journals From .csv file','/Z_ImportGLTransactions.php',8),(9,'Utilities','Maintenance','Import Price List from .csv file','/Z_ImportPriceList.php',6),(9,'Utilities','Maintenance','Import Stock Items from .csv','/Z_ImportStocks.php',5),(9,'Utilities','Maintenance','Maintain Language Files','/Z_poAdmin.php',1),(9,'Utilities','Maintenance','Make New Company','/Z_MakeNewCompany.php',2),(9,'Utilities','Maintenance','Purge all old prices','/Z_DeleteOldPrices.php',12),(9,'Utilities','Maintenance','Remove all purchase back orders','/Z_RemovePurchaseBackOrders.php',13),(9,'Utilities','Reports','Debtors Balances By Currency Totals','/Z_CurrencyDebtorsBalances.php',1),(9,'Utilities','Reports','List of items without picture','/Z_ItemsWithoutPicture.php',4),(9,'Utilities','Reports','Show General Transactions That Do Not Balance','/Z_CheckGLTransBalance.php',3),(9,'Utilities','Reports','Suppliers Balances By Currency Totals','/Z_CurrencySuppliersBalances.php',2),(9,'Utilities','Transactions','Change A Customer Branch Code','/Z_ChangeBranchCode.php',2),(9,'Utilities','Transactions','Change A Customer Code','/Z_ChangeCustomerCode.php',1),(9,'Utilities','Transactions','Change A GL Account Code','/Z_ChangeGLAccountCode.php',3),(9,'Utilities','Transactions','Change A Location Code','/Z_ChangeLocationCode.php',5),(9,'Utilities','Transactions','Change A Salesman Code','/Z_ChangeSalesmanCode.php',6),(9,'Utilities','Transactions','Change A Stock Category Code','/Z_ChangeStockCategory.php',7),(9,'Utilities','Transactions','Change A Supplier Code','/Z_ChangeSupplierCode.php',8),(9,'Utilities','Transactions','Change An Inventory Item Code','/Z_ChangeStockCode.php',4),(9,'Utilities','Transactions','Copy Authority of GL Accounts from one user to another','/Z_GLAccountUsersCopyAuthority.php',15),(9,'Utilities','Transactions','Delete sales transactions','/Z_DeleteSalesTransActions.php',12),(9,'Utilities','Transactions','Re-apply costs to Sales Analysis','/Z_ReApplyCostToSA.php',11),(9,'Utilities','Transactions','Reverse all supplier payments on a specified date','/Z_ReverseSuppPaymentRun.php',13),(9,'Utilities','Transactions','Translate Item Descriptions','/AutomaticTranslationDescriptions.php',9),(9,'Utilities','Transactions','Update costs for all BOM items, from the bottom up','/Z_BottomUpCosts.php',10),(9,'Utilities','Transactions','Update sales analysis with latest customer data','/Z_UpdateSalesAnalysisWithLatestCustomerData.php',14),(1, 'manuf', 'Maintenance', 'Product Spec Groups Maintenance', '/ProdSpecGroups.php', 9),(2, 'manuf', 'Maintenance', 'Product Spec Groups Maintenance', '/ProdSpecGroups.php', 9),(3, 'manuf', 'Maintenance', 'Product Spec Groups Maintenance', '/ProdSpecGroups.php', 9),(4, 'manuf', 'Maintenance', 'Product Spec Groups Maintenance', '/ProdSpecGroups.php', 9),(5, 'manuf', 'Maintenance', 'Product Spec Groups Maintenance', '/ProdSpecGroups.php', 9),(6, 'manuf', 'Maintenance', 'Product Spec Groups Maintenance', '/ProdSpecGroups.php', 9),(7, 'manuf', 'Maintenance', 'Product Spec Groups Maintenance', '/ProdSpecGroups.php', 9),(8, 'manuf', 'Maintenance', 'Product Spec Groups Maintenance', '/ProdSpecGroups.php', 9),(9, 'manuf', 'Maintenance', 'Product Spec Groups Maintenance', '/ProdSpecGroups.php', 9);
INSERT INTO `modules` VALUES (1,'AP','ap','Payables',4),(1,'AR','ar','Receivables',2),(1,'FA','fa','Asset Manager',8),(1,'GL','gl','General Ledger',7),(1,'manuf','man','Manufacturing',6),(1,'PC','pc','Petty Cash',9),(1,'PO','prch','Purchases',3),(1,'Sales','ord','Sales',1),(1,'stock','inv','Inventory',5),(1,'system','sys','Setup',10),(1,'Utilities','util','Utilities',11),(2,'AP','ap','Payables',4),(2,'AR','ar','Receivables',2),(2,'FA','fa','Asset Manager',8),(2,'GL','gl','General Ledger',7),(2,'manuf','man','Manufacturing',6),(2,'PC','pc','Petty Cash',9),(2,'PO','prch','Purchases',3),(2,'Sales','ord','Sales',1),(2,'stock','inv','Inventory',5),(2,'system','sys','Setup',10),(2,'Utilities','util','Utilities',11),(3,'AP','ap','Payables',4),(3,'AR','ar','Receivables',2),(3,'FA','fa','Asset Manager',8),(3,'GL','gl','General Ledger',7),(3,'manuf','man','Manufacturing',6),(3,'PC','pc','Petty Cash',9),(3,'PO','prch','Purchases',3),(3,'Sales','ord','Sales',1),(3,'stock','inv','Inventory',5),(3,'system','sys','Setup',10),(3,'Utilities','util','Utilities',11),(4,'AP','ap','Payables',4),(4,'AR','ar','Receivables',2),(4,'FA','fa','Asset Manager',8),(4,'GL','gl','General Ledger',7),(4,'manuf','man','Manufacturing',6),(4,'PC','pc','Petty Cash',9),(4,'PO','prch','Purchases',3),(4,'Sales','ord','Sales',1),(4,'stock','inv','Inventory',5),(4,'system','sys','Setup',10),(4,'Utilities','util','Utilities',11),(5,'AP','ap','Payables',4),(5,'AR','ar','Receivables',2),(5,'FA','fa','Asset Manager',8),(5,'GL','gl','General Ledger',7),(5,'manuf','man','Manufacturing',6),(5,'PC','pc','Petty Cash',9),(5,'PO','prch','Purchases',3),(5,'Sales','ord','Sales',1),(5,'stock','inv','Inventory',5),(5,'system','sys','Setup',10),(5,'Utilities','util','Utilities',11),(6,'AP','ap','Payables',4),(6,'AR','ar','Receivables',2),(6,'FA','fa','Asset Manager',8),(6,'GL','gl','General Ledger',7),(6,'manuf','man','Manufacturing',6),(6,'PC','pc','Petty Cash',9),(6,'PO','prch','Purchases',3),(6,'Sales','ord','Sales',1),(6,'stock','inv','Inventory',5),(6,'system','sys','Setup',10),(6,'Utilities','util','Utilities',11),(7,'AP','ap','Payables',4),(7,'AR','ar','Receivables',2),(7,'FA','fa','Asset Manager',8),(7,'GL','gl','General Ledger',7),(7,'manuf','man','Manufacturing',6),(7,'PC','pc','Petty Cash',9),(7,'PO','prch','Purchases',3),(7,'Sales','ord','Sales',1),(7,'stock','inv','Inventory',5),(7,'system','sys','Setup',10),(7,'Utilities','util','Utilities',11),(8,'AP','ap','Payables',4),(8,'AR','ar','Receivables',2),(8,'FA','fa','Asset Manager',8),(8,'GL','gl','General Ledger',7),(8,'manuf','man','Manufacturing',6),(8,'PC','pc','Petty Cash',9),(8,'PO','prch','Purchases',3),(8,'Sales','ord','Sales',1),(8,'stock','inv','Inventory',5),(8,'system','sys','Setup',10),(8,'Utilities','util','Utilities',11),(9,'AP','ap','Payables',4),(9,'AR','ar','Receivables',2),(9,'FA','fa','Asset Manager',8),(9,'GL','gl','General Ledger',7),(9,'manuf','man','Manufacturing',6),(9,'PC','pc','Petty Cash',9),(9,'PO','prch','Purchases',3),(9,'Sales','ord','Sales',1),(9,'stock','inv','Inventory',5),(9,'system','sys','Setup',10),(9,'Utilities','util','Utilities',11);
INSERT INTO prodspecgroups (groupid, groupname, groupbyNo, headertitle, trailertext, labels, numcols) VALUES
(1, 'PhysicalProperty', 1, 'Physical Properties', NULL, 'Physical Property,Value,Test Method', 3),
(2, 'MechanicalProperty', 2, NULL, NULL, '', 3),
(3, 'ThermalProperty', 3, NULL, NULL, '', 3),
(4, 'Processing', 6, 'Injection Molding Processing Guidelines', '* Desicant type dryer required.', 'Setting,Value', 2),
(5, 'RegulatoryCompliance', 5, 'Regulatory Compliance', NULL, 'Regulatory Compliance,Value', 2);
INSERT INTO `scripts` VALUES ('AccountGroups.php',10,'Defines the groupings of general ledger accounts'),('AccountSections.php',10,'Defines the sections in the general ledger reports'),('AddCustomerContacts.php',3,'Adds customer contacts'),('AddCustomerNotes.php',3,'Adds notes about customers'),('AddCustomerTypeNotes.php',3,''),('AgedControlledInventory.php',11,'Report of Controlled Items and their age'),('AgedDebtors.php',2,'Lists customer account balances in detail or summary in selected currency'),('AgedSuppliers.php',2,'Lists supplier account balances in detail or summary in selected currency'),('AnalysisHorizontalIncome.php',8,'Shows the horizontal analysis of the statement of comprehensive income'),('AnalysisHorizontalPosition.php',8,'Shows the horizontal analysis of the statement of financial position'),('Areas.php',3,'Defines the sales areas - all customers must belong to a sales area for the purposes of sales analysis'),('AuditTrail.php',15,'Shows the activity with SQL statements and who performed the changes'),('AutomaticTranslationDescriptions.php',15,'Translates via Google Translator all empty translated descriptions'),('BankAccountBalances.php',1,'Shows bank accounts authorised for with balances'),('BankAccounts.php',10,'Defines the general ledger code for bank accounts and specifies that bank transactions be created for these accounts for the purposes of reconciliation'),('BankAccountUsers.php',15,'Maintains table bankaccountusers (Authorized users to work with a bank account in webERP)'),('BankMatching.php',7,'Allows payments and receipts to be matched off against bank statements'),('BankReconciliation.php',7,'Displays the bank reconciliation for a selected bank account'),('bank_trans.php',3,''),('BOMExtendedQty.php',2,'Shows the component requirements to make an item'),('BOMIndented.php',2,'Shows the bill of material indented for each level'),('BOMIndentedReverse.php',2,''),('BOMInquiry.php',2,'Displays the bill of material with cost information'),('BOMListing.php',2,'Lists the bills of material for a selected range of items'),('BOMs.php',9,'Administers the bills of material for a selected item'),('BOMs_SingleLevel.php',2,'Single Level BOM entry'),('COGSGLPostings.php',10,'Defines the general ledger account to be used for cost of sales entries'),('CollectiveWorkOrderCost.php',2,'Multiple work orders cost review'),('CompanyPreferences.php',10,'Defines the settings applicable for the company, including name, address, tax authority reference, whether GL integration used, etc.'),('ConfirmDispatchControlled_Invoice.php',2,'Specifies the batch references/serial numbers of items dispatched that are being invoiced'),('ConfirmDispatch_Invoice.php',2,'Creates sales invoices from entered sales orders based on the quantities dispatched that can be modified'),('ContractBOM.php',6,'Creates the item requirements from stock for a contract as part of the contract cost build up'),('ContractCosting.php',6,'Shows a contract cost - the components and other non-stock costs issued to the contract'),('ContractOtherReqts.php',4,'Creates the other requirements for a contract cost build up'),('Contracts.php',6,'Creates or modifies a customer contract costing'),('CopyBOM.php',9,'Allows a bill of material to be copied between items'),('CostUpdate',10,'NB Not a script but allows users to maintain item costs from withing StockCostUpdate.php'),('CounterReturns.php',5,'Allows credits and refunds from the default Counter Sale account for an inventory location'),('CounterSales.php',1,'Allows sales to be entered against a cash sale customer account defined in the users location record'),('CreditItemsControlled.php',3,'Specifies the batch references/serial numbers of items being credited back into stock'),('CreditStatus.php',3,'Defines the credit status records. Each customer account is given a credit status from this table. Some credit status records can prohibit invoicing and new orders being entered.'),('Credit_Invoice.php',3,'Creates a credit note based on the details of an existing invoice'),('Currencies.php',9,'Defines the currencies available. Each customer and supplier must be defined as transacting in one of the currencies defined here.'),('CustEDISetup.php',11,'Allows the set up the customer specified EDI parameters for server, email or ftp.'),('CustItem.php',11,'Customer Items'),('CustLoginSetup.php',15,''),('CustomerAccount.php',1,'Shows customer account/statement on screen rather than PDF'),('CustomerAllocations.php',3,'Allows customer receipts and credit notes to be allocated to sales invoices'),('CustomerBalancesMovement.php',3,'Allow customers to be listed in local currency with balances and activity over a date range'),('CustomerBranches.php',3,'Defines the details of customer branches such as delivery address and contact details - also sales area, representative etc'),('CustomerInquiry.php',1,'Shows the customers account transactions with balances outstanding, links available to drill down to invoice/credit note or email invoices/credit notes'),('CustomerPurchases.php',5,'Shows the purchases a customer has made.'),('CustomerReceipt.php',3,'Entry of both customer receipts against accounts receivable and also general ledger or nominal receipts'),('Customers.php',3,'Defines the setup of a customer account, including payment terms, billing address, credit status, currency etc'),('CustomerTransInquiry.php',2,'Lists in html the sequence of customer transactions, invoices, credit notes or receipts by a user entered date range'),('CustomerTypes.php',15,''),('customer_orders.php',2,''),('CustWhereAlloc.php',2,'Shows to which invoices a receipt was allocated to'),('DailyBankTransactions.php',8,'Allows you to view all bank transactions for a selected date range, and the inquiry can be filtered by matched or unmatched transactions, or all transactions can be chosen'),('DailySalesInquiry.php',2,'Shows the daily sales with GP in a calendar format'),('Dashboard.php',1,'Display outstanding debtors, creditors etc'),('DashboardConfig.php',15,''),('DebtorsAtPeriodEnd.php',2,'Shows the debtors control account as at a previous period end - based on system calendar monthly periods'),('DeliveryDetails.php',1,'Used during order entry to allow the entry of delivery addresses other than the defaulted branch delivery address and information about carrier/shipping method etc'),('Departments.php',1,'Create business departments'),('DiscountCategories.php',11,'Defines the items belonging to a discount category. Discount Categories are used to allow discounts based on quantities across a range of producs'),('DiscountMatrix.php',11,'Defines the rates of discount applicable to discount categories and the customer groupings to which the rates are to apply'),('EDIMessageFormat.php',10,'Specifies the EDI message format used by a customer - administrator use only.'),('EDIProcessOrders.php',11,'Processes incoming EDI orders into sales orders'),('EDISendInvoices.php',15,'Processes invoiced EDI customer invoices into EDI messages and sends using the customers preferred method either ftp or email attachments.'),('EmailConfirmation.php',2,''),('EmailCustStatements.php',2,'Email Customer Statements'),('EmailCustTrans.php',2,'Emails selected invoice or credit to the customer'),('Employees.php',10,'Employees requiring time-sheets maintenance and entry '),('ExchangeRateTrend.php',2,'Shows the trend in exchange rates as retrieved from ECB'),('Factors.php',5,'Defines supplier factor companies'),('FixedAssetCategories.php',11,'Defines the various categories of fixed assets'),('FixedAssetDepreciation.php',10,'Calculates and creates GL transactions to post depreciation for a period'),('FixedAssetItems.php',11,'Allows fixed assets to be defined'),('FixedAssetLocations.php',11,'Allows the locations of fixed assets to be defined'),('FixedAssetRegister.php',11,'Produces a csv, html or pdf report of the fixed assets over a period showing period depreciation, additions and disposals'),('FixedAssetTransfer.php',11,'Allows the fixed asset locations to be changed in bulk'),('FormDesigner.php',14,'Customizes the form layout without requiring the use of scripting or technical development'),('FormMaker.php',1,'Allows running user defined Forms'),('FreightCosts.php',11,'Defines the setup of the freight cost using different shipping methods to different destinations. The system can use this information to calculate applicable freight if the items are defined with the correct kgs and cubic volume'),('FTP_RadioBeacon.php',2,'FTPs sales orders for dispatch to a radio beacon software enabled warehouse dispatching facility'),('GeneratePickingList.php',11,'Generate a picking list'),('geocode.php',3,''),('GeocodeSetup.php',3,'Sets the configuration for geocoding of customers and suppliers'),('geocode_genxml_customers.php',3,''),('geocode_genxml_suppliers.php',3,''),('geo_displaymap_customers.php',3,''),('geo_displaymap_suppliers.php',3,''),('GetStockImage.php',1,''),('GLAccountCSV.php',8,'Produces a CSV of the GL transactions for a particular range of periods and GL account'),('GLAccountGraph.php',8,'Shows a graph of GL account transactions'),('GLAccountInquiry.php',8,'Shows the general ledger transactions for a specified account over a specified range of periods'),('GLAccountReport.php',8,'Produces a report of the GL transactions for a particular account'),('GLAccounts.php',10,'Defines the general ledger accounts'),('GLAccountUsers.php',15,'Maintenance of users allowed to a GL Account'),('GLAccountUsersCopyAuthority.php',15,''),('GLBalanceSheet.php',8,'Shows the balance sheet for the company as at a specified date'),('GLBankAccountUsersCopyAuthority.php',15,''),('GLBudgetHeaders.php',10,''),('GLBudgets.php',10,'Defines GL Budgets'),('GLCashFlowsIndirect.php',8,'Shows a statement of cash flows for the period using the indirect method'),('GLCashFlowsSetup.php',8,'Setups the statement of cash flows sections'),('GLCodesInquiry.php',8,'Shows the list of general ledger codes defined with account names and groupings'),('GLJournal.php',10,'Entry of general ledger journals, periods are calculated based on the date entered here'),('GLJournalInquiry.php',15,'General Ledger Journal Inquiry'),('GLJournalTemplates.php',15,'Maintain Journal templates'),('GLProfit_Loss.php',8,'Shows the profit and loss of the company for the range of periods entered'),('GLStatements.php',8,'Shows a set of financial statements'),('GLTagProfit_Loss.php',8,''),('GLTags.php',10,'Allows GL tags to be defined'),('GLTransInquiry.php',8,'Shows the general ledger journal created for the sub ledger transaction specified'),('GLTrialBalance.php',8,'Shows the trial balance for the month and the for the period selected together with the budgeted trial balances'),('GLTrialBalance_csv.php',8,'Produces a CSV of the Trial Balance for a particular period'),('GoodsReceived.php',11,'Entry of items received against purchase orders'),('GoodsReceivedControlled.php',11,'Entry of the serial numbers or batch references for controlled items received against purchase orders'),('GoodsReceivedNotInvoiced.php',2,'Shows the list of goods received but not yet invoiced, both in supplier currency and home currency. Total in home curency should match the GL Account for Goods received not invoiced. Any discrepancy is due to multicurrency errors.'),('HistoricalTestResults.php',16,'Historical Test Results'),('ImportBankTrans.php',11,'Imports bank transactions'),('ImportBankTransAnalysis.php',11,'Allows analysis of bank transactions being imported'),('index.php',1,'The main menu from where all functions available to the user are accessed by clicking on the links'),('InternalStockCategoriesByRole.php',15,'Maintains the stock categories to be used as internal for any user security role'),('InternalStockRequest.php',1,'Create an internal stock request'),('InternalStockRequestAuthorisation.php',1,'Authorise internal stock requests'),('InternalStockRequestFulfill.php',1,'Fulfill an internal stock request'),('InternalStockRequestInquiry.php',1,'Internal Stock Request inquiry'),('InventoryPlanning.php',2,'Creates a pdf report showing the last 4 months use of items including as a component of assemblies together with stock quantity on hand, current demand for the item and current quantity on sales order.'),('InventoryPlanningPrefSupplier.php',2,'Produces a report showing the inventory to be ordered by supplier'),('InventoryPlanningPrefSupplier_CSV.php',2,'Inventory planning spreadsheet'),('InventoryQuantities.php',2,''),('InventoryValuation.php',2,'Creates a pdf report showing the value of stock at standard cost for a range of product categories selected'),('Labels.php',15,'Produces item pricing labels in a pdf from a range of selected criteria'),('latest_grns.php',2,''),('latest_po.php',3,''),('latest_po_auth.php',2,''),('latest_stock_status.php',3,''),('Locations.php',11,'Defines the inventory stocking locations or warehouses'),('LocationUsers.php',15,'Allows users that have permission to access a location to be defined'),('LocationUsersCopyAuthority.php',15,''),('LoggedInUsers.php',8,''),('Logout.php',1,'Shows when the user logs out of webERP'),('MailingGroupMaintenance.php',15,'Mainting mailing lists for items to mail'),('MailInventoryValuation.php',1,'Meant to be run as a scheduled process to email the stock valuation off to a specified person. Creates the same stock valuation report as InventoryValuation.php'),('MailSalesReport_csv.php',15,'Mailing the sales report'),('MaintenanceReminders.php',1,'Sends email reminders for scheduled asset maintenance tasks'),('MaintenanceTasks.php',1,'Allows set up and edit of scheduled maintenance tasks'),('MaintenanceUserSchedule.php',1,'List users or managers scheduled maintenance tasks and allow to be flagged as completed'),('Manufacturers.php',15,'Maintain brands of sales products'),('MaterialsNotUsed.php',4,'Lists the items from Raw Material Categories not used in any BOM (thus, not used at all)'),('MRP.php',9,''),('MRPCalendar.php',9,''),('MRPCreateDemands.php',9,''),('MRPDemands.php',9,''),('MRPDemandTypes.php',9,''),('MRPPlannedPurchaseOrders.php',2,''),('MRPPlannedWorkOrders.php',2,''),('MRPReport.php',2,''),('MRPReschedules.php',2,''),('MRPShortages.php',2,''),('mrp_dashboard.php',3,''),('NoSalesItems.php',2,'Shows the No Selling (worst) items'),('OffersReceived.php',4,''),('OrderDetails.php',1,'Shows the detail of a sales order'),('OrderEntryDiscountPricing',13,'Not a script but an authority level marker - required if the user is allowed to enter discounts and special pricing against a customer order'),('OutstandingGRNs.php',2,'Creates a pdf showing all GRNs for which there has been no purchase invoice matched off against.'),('PageSecurity.php',15,'Changes the security token of a script'),('PaymentAllocations.php',5,''),('PaymentMethods.php',15,''),('Payments.php',5,'Entry of bank account payments either against an AP account or a general ledger payment - if the AP-GL link in company preferences is set'),('PaymentTerms.php',10,'Defines the payment terms records, these can be expressed as either a number of days credit or a day in the following month. All customers and suppliers must have a corresponding payment term recorded against their account'),('PcAnalysis.php',15,'Creates an Excel with details of PC expnese for 24 months'),('PcAssignCashTabToTab.php',12,'Assign cash from one tab to another'),('PcAssignCashToTab.php',6,''),('PcAuthorizeCash.php',6,'Authorisation of assigned cash'),('PcAuthorizeExpenses.php',6,''),('PcClaimExpensesFromTab.php',6,''),('PcExpenses.php',15,''),('PcExpensesTypeTab.php',15,''),('PcReportExpense.php',15,''),('PcReportTab.php',6,''),('PcTabExpensesList.php',15,'Creates excel with all movements of tab between dates'),('PcTabs.php',15,''),('PcTypeTabs.php',15,''),('PDFAck.php',15,'Print an acknowledgement'),('PDFBankingSummary.php',3,'Creates a pdf showing the amounts entered as receipts on a specified date together with references for the purposes of banking'),('PDFChequeListing.php',3,'Creates a pdf showing all payments that have been made from a specified bank account over a specified period. This can be emailed to an email account defined in config.php - ie a financial controller'),('PDFCOA.php',0,'PDF of COA'),('PDFCustomerList.php',2,'Creates a report of the customer and branch information held. This report has options to print only customer branches in a specified sales area and sales person. Additional option allows to list only those customers with activity either under or over a specified amount, since a specified date.'),('PDFCustTransListing.php',3,''),('PDFDeliveryDifferences.php',3,'Creates a pdf report listing the delivery differences from what the customer requested as recorded in the order entry. The report calculates a percentage of order fill based on the number of orders filled in full on time'),('PDFDIFOT.php',3,'Produces a pdf showing the delivery in full on time performance'),('PDFFGLabel.php',11,'Produces FG Labels'),('PDFGLJournal.php',15,'General Ledger Journal Print'),('PDFGLJournalCN.php',1,'Print GL Journal Chinese version'),('PDFGrn.php',2,'Produces a GRN report on the receipt of stock'),('PDFLowGP.php',2,'Creates a pdf report showing the low gross profit sales made in the selected date range. The percentage of gp deemed acceptable can also be entered'),('PDFOrdersInvoiced.php',3,'Produces a pdf of orders invoiced based on selected criteria'),('PDFOrderStatus.php',3,'Reports on sales order status by date range, by stock location and stock category - producing a pdf showing each line items and any quantites delivered'),('PDFPeriodStockTransListing.php',3,'Allows stock transactions of a specific transaction type to be listed over a single day or period range'),('PDFPickingList.php',2,''),('PDFPriceList.php',2,'Creates a pdf of the price list applicable to a given sales type and customer. Also allows the listing of prices specific to a customer'),('PDFPrintLabel.php',10,''),('PDFProdSpec.php',0,'PDF OF Product Specification'),('PDFQALabel.php',2,'Produces a QA label on receipt of stock'),('PDFQuotation.php',2,''),('PDFQuotationPortrait.php',2,'Portrait quotation'),('PDFReceipt.php',2,''),('PDFRemittanceAdvice.php',2,''),('PDFSellThroughSupportClaim.php',9,'Reports the sell through support claims to be made against all suppliers for a given date range.'),('PDFShipLabel.php',15,'Print a ship label'),('PDFStockCheckComparison.php',2,'Creates a pdf comparing the quantites entered as counted at a given range of locations against the quantity stored as on hand as at the time a stock check was initiated.'),('PDFStockLocTransfer.php',1,'Creates a stock location transfer docket for the selected location transfer reference number'),('PDFStockNegatives.php',1,'Produces a pdf of the negative stocks by location'),('PDFStockTransfer.php',2,'Produces a report for stock transfers'),('PDFSuppTransListing.php',3,''),('PDFTestPlan.php',16,'PDF of Test Plan'),('PDFTopItems.php',2,'Produces a pdf report of the top items sold'),('PDFWOPrint.php',11,'Produces W/O Paperwork'),('PeriodsInquiry.php',2,'Shows a list of all the system defined periods'),('PickingLists.php',11,'Picking List Maintenance'),('PickingListsControlled.php',11,'Picking List Maintenance - Controlled'),('POFinancialPlanning.php',4,''),('POReport.php',2,''),('PO_AuthorisationLevels.php',15,''),('PO_AuthoriseMyOrders.php',4,''),('PO_Header.php',4,'Entry of a purchase order header record - date, references buyer etc'),('PO_Items.php',4,'Entry of a purchase order items - allows entry of items with lookup of currency cost from Purchasing Data previously entered also allows entry of nominal items against a general ledger code if the AP is integrated to the GL'),('PO_OrderDetails.php',2,'Purchase order inquiry shows the quantity received and invoiced of purchase order items as well as the header information'),('PO_PDFPurchOrder.php',2,'Creates a pdf of the selected purchase order for printing or email to one of the supplier contacts entered'),('PO_SelectOSPurchOrder.php',2,'Shows the outstanding purchase orders for selecting with links to receive or modify the purchase order header and items'),('PO_SelectPurchOrder.php',2,'Allows selection of any purchase order with links to the inquiry'),('PriceMatrix.php',11,'Mantain stock prices according to quantity break and sales types'),('Prices.php',9,'Entry of prices for a selected item also allows selection of sales type and currency for the price'),('PricesBasedOnMarkUp.php',11,''),('PricesByCost.php',11,'Allows prices to be updated based on cost'),('Prices_Customer.php',11,'Entry of prices for a selected item and selected customer/branch. The currency and sales type is defaulted from the customer\'s record'),('PrintCheque.php',5,''),('PrintCustOrder.php',2,'Creates a pdf of the dispatch note - by default this is expected to be on two part pre-printed stationery to allow pickers to note discrepancies for the confirmer to update the dispatch at the time of invoicing'),('PrintCustOrder_generic.php',2,'Creates two copies of a laser printed dispatch note - both copies need to be written on by the pickers with any discrepancies to advise customer of any shortfall and on the office copy to ensure the correct quantites are invoiced'),('PrintCustStatements.php',2,'Creates a pdf for the customer statements in the selected range'),('PrintCustTrans.php',1,'Creates either a html invoice or credit note or a pdf. A range of invoices or credit notes can be selected also.'),('PrintCustTransPortrait.php',1,''),('PrintSalesOrder_generic.php',2,''),('PrintWOItemSlip.php',4,'PDF WO Item production Slip '),('ProdSpecGroups.php', 16, 'Product Spec Groups Maintenance'),('ProductSpecs.php',16,'Product Specification Maintenance'),('PurchaseByPrefSupplier.php',2,'Purchase ordering by preferred supplier'),('PurchasesReport.php',2,'Shows a report of purchases from suppliers for the range of selected dates'),('PurchData.php',4,'Entry of supplier purchasing data, the suppliers part reference and the suppliers currency cost of the item'),('QATests.php',16,'Quality Test Maintenance'),('RecurringSalesOrders.php',1,''),('RecurringSalesOrdersProcess.php',1,'Process Recurring Sales Orders'),('RegularPaymentsProcess.php',5,''),('RegularPaymentsSetup.php',5,''),('RelatedItemsUpdate.php',2,'Maintains Related Items'),('ReorderLevel.php',2,'Allows reorder levels of inventory to be updated'),('ReorderLevelLocation.php',2,''),('ReportCreator.php',13,'Report Writer and Form Creator script that creates templates for user defined reports and forms'),('ReportMaker.php',1,'Produces reports from the report writer templates created'),('reportwriter/admin/ReportCreator.php',15,'Report Writer'),('ReprintGRN.php',11,'Allows selection of a goods received batch for reprinting the goods received note given a purchase order number'),('ReverseGRN.php',11,'Reverses the entry of goods received - creating stock movements back out and necessary general ledger journals to effect the reversal'),('RevisionTranslations.php',15,'Human revision for automatic descriptions translations'),('SalesAnalReptCols.php',2,'Entry of the definition of a sales analysis report\'s columns.'),('SalesAnalRepts.php',2,'Entry of the definition of a sales analysis report headers'),('SalesAnalysis_UserDefined.php',2,'Creates a pdf of a selected user defined sales analysis report'),('SalesByTypePeriodInquiry.php',2,'Shows sales for a selected date range by sales type/price list'),('SalesCategories.php',11,''),('SalesCategoryDescriptions.php',15,'Maintain translations for sales categories'),('SalesCategoryPeriodInquiry.php',2,'Shows sales for a selected date range by stock category'),('SalesCommissionRates.php',15,''),('SalesCommissionReports.php',3,''),('SalesCommissionTypes.php',15,''),('SalesGLPostings.php',10,'Defines the general ledger accounts used to post sales to based on product categories and sales areas'),('SalesGraph.php',6,''),('SalesInquiry.php',2,''),('SalesPeople.php',3,'Defines the sales people of the business'),('SalesReport.php',2,'Shows a report of sales to customers for the range of selected dates'),('SalesTopCustomersInquiry.php',1,'Shows the top customers'),('SalesTopItemsInquiry.php',2,'Shows the top item sales for a selected date range'),('SalesTypes.php',15,'Defines the sales types - prices are held against sales types they can be considered price lists. Sales analysis records are held by sales type too.'),('SecurityTokens.php',15,'Administration of security tokens'),('SelectAsset.php',2,'Allows a fixed asset to be selected for modification or viewing'),('SelectCompletedOrder.php',1,'Allows the selection of completed sales orders for inquiries - choices to select by item code or customer'),('SelectContract.php',6,'Allows a contract costing to be selected for modification or viewing'),('SelectCreditItems.php',3,'Entry of credit notes from scratch, selecting the items in either quick entry mode or searching for them manually'),('SelectCustomer.php',2,'Selection of customer - from where all customer related maintenance, transactions and inquiries start'),('SelectGLAccount.php',8,'Selection of general ledger account from where all general ledger account maintenance, or inquiries are initiated'),('SelectOrderItems.php',1,'Entry of sales order items with both quick entry and part search functions'),('SelectPickingLists.php',11,'Select a picking list'),('SelectProduct.php',2,'Selection of items. All item maintenance, transactions and inquiries start with this script'),('SelectQASamples.php',16,'Select  QA Samples'),('SelectRecurringSalesOrder.php',2,''),('SelectSalesOrder.php',2,'Selects a sales order irrespective of completed or not for inquiries'),('SelectSupplier.php',2,'Selects a supplier. A supplier is required to be selected before any AP transactions and before any maintenance or inquiry of the supplier'),('SelectWorkOrder.php',2,''),('SellThroughSupport.php',9,'Defines the items, period and quantum of support for which supplier has agreed to provide.'),('ShipmentCosting.php',11,'Shows the costing of a shipment with all the items invoice values and any shipment costs apportioned. Updating the shipment has an option to update standard costs of all items on the shipment and create any general ledger variance journals'),('Shipments.php',11,'Entry of shipments from outstanding purchase orders for a selected supplier - changes in the delivery date will cascade into the different purchase orders on the shipment'),('Shippers.php',15,'Defines the shipping methods available. Each customer branch has a default shipping method associated with it which must match a record from this table'),('ShiptsList.php',2,'Shows a list of all the open shipments for a selected supplier. Linked from POItems.php'),('Shipt_Select.php',11,'Selection of a shipment for displaying and modification or updating'),('ShopParameters.php',15,'Maintain web-store configuration and set up'),('SMTPServer.php',15,'Sets the SMTP server'),('SpecialOrder.php',4,'Allows for a sales order to be created and an indent order to be created on a supplier for a one off item that may never be purchased again. A dummy part is created based on the description and cost details given.'),('StockAdjustments.php',11,'Entry of quantity corrections to stocks in a selected location.'),('StockAdjustmentsControlled.php',11,'Entry of batch references or serial numbers on controlled stock items being adjusted'),('StockCategories.php',11,'Defines the stock categories. All items must refer to one of these categories. The category record also allows the specification of the general ledger codes where stock items are to be posted - the balance sheet account and the profit and loss effect of any adjustments and the profit and loss effect of any price variances'),('StockCategorySalesInquiry.php',2,'Sales inquiry by stock category showing top items'),('StockCheck.php',2,'Allows creation of a stock check file - copying the current quantites in stock for later comparison to the entered counts. Also produces a pdf for the count sheets.'),('StockClone.php',11,'Script to copy a stock item and associated properties, image, price, purchase and cost data'),('StockCostUpdate.php',9,'Allows update of the standard cost of items producing general ledger journals if the company preferences stock GL interface is active'),('StockCounts.php',2,'Allows entry of stock counts'),('StockDispatch.php',2,''),('StockLocMovements.php',2,'Inquiry shows the Movements of all stock items for a specified location'),('StockLocStatus.php',2,'Shows the stock on hand together with outstanding sales orders and outstanding purchase orders by stock location for all items in the selected stock category'),('StockLocTransfer.php',11,'Entry of a bulk stock location transfer for many parts from one location to another.'),('StockLocTransferReceive.php',11,'Effects the transfer and creates the stock movements for a bulk stock location transfer initiated from StockLocTransfer.php'),('StockMovements.php',2,'Shows a list of all the stock movements for a selected item and stock location including the price at which they were sold in local currency and the price at which they were purchased for in local currency'),('StockQties_csv.php',5,'Makes a comma separated values (CSV)file of the stock item codes and quantities'),('StockQuantityByDate.php',2,'Shows the stock on hand for each item at a selected location and stock category as at a specified date'),('StockReorderLevel.php',4,'Entry and review of the re-order level of items by stocking location'),('Stocks.php',11,'Defines an item - maintenance and addition of new parts'),('StockSerialItemResearch.php',3,''),('StockSerialItems.php',2,'Shows a list of the serial numbers or the batch references and quantities of controlled items. This inquiry is linked from the stock status inquiry'),('StockStatus.php',2,'Shows the stock on hand together with outstanding sales orders and outstanding purchase orders by stock location for a selected part. Has a link to show the serial numbers in stock at the location selected if the item is controlled'),('StockTransferControlled.php',11,'Entry of serial numbers/batch references for controlled items being received on a stock transfer. The script is used by both bulk transfers and point to point transfers'),('StockTransfers.php',11,'Entry of point to point stock location transfers of a single part'),('StockUsage.php',2,'Inquiry showing the quantity of stock used by period calculated from the sum of the stock movements over that period - by item and stock location. Also available over all locations'),('StockUsageGraph.php',2,''),('SuppContractChgs.php',5,''),('SuppCreditGRNs.php',5,'Entry of a supplier credit notes (debit notes) against existing GRN which have already been matched in full or in part'),('SuppFixedAssetChgs.php',5,''),('SuppInvGRNs.php',5,'Entry of supplier invoices against goods received'),('SupplierAllocations.php',5,'Entry of allocations of supplier payments and credit notes to invoices'),('SupplierBalsAtPeriodEnd.php',2,''),('SupplierContacts.php',5,'Entry of supplier contacts and contact details including email addresses'),('SupplierCredit.php',5,'Entry of supplier credit notes (debit notes)'),('SupplierGRNAndInvoiceInquiry.php',5,'Supplier\'s delivery note and grn relationship inquiry'),('SupplierInquiry.php',2,'Inquiry showing invoices, credit notes and payments made to suppliers together with the amounts outstanding'),('SupplierInvoice.php',5,'Entry of supplier invoices'),('SupplierPriceList.php',4,'Maintain Supplier Price Lists'),('Suppliers.php',5,'Entry of new suppliers and maintenance of existing suppliers'),('SupplierTenderCreate.php',4,'Create or Edit tenders'),('SupplierTenders.php',9,''),('SupplierTransInquiry.php',2,''),('SupplierTypes.php',4,''),('SuppLoginSetup.php',15,''),('SuppPaymentRun.php',5,'Automatic creation of payment records based on calculated amounts due from AP invoices entered'),('SuppPriceList.php',2,''),('SuppShiptChgs.php',5,'Entry of supplier invoices against shipments as charges against a shipment'),('SuppTransGLAnalysis.php',5,'Entry of supplier invoices against general ledger codes'),('SuppWhereAlloc.php',3,'Suppliers Where allocated'),('SystemParameters.php',15,'Sets the main system configuration parameters'),('Tax.php',2,'Creates a report of the ad-valorem tax -GST/VAT- for the period selected from accounts payable and accounts receivable data'),('TaxAuthorities.php',15,'Entry of tax authorities - the state intitutions that charge tax'),('TaxAuthorityRates.php',11,'Entry of the rates of tax applicable to the tax authority depending on the item tax level'),('TaxCategories.php',15,'Allows for categories of items to be defined that might have different tax rates applied to them'),('TaxGroups.php',15,'Allows for taxes to be grouped together where multiple taxes might apply on sale or purchase of items'),('TaxProvinces.php',15,'Allows for inventory locations to be defined so that tax applicable from sales in different provinces can be dealt with'),('TestPlanResults.php',16,'Test Plan Results Entry'),('Timesheets.php',1,'Entry of Timesheets'),('TopItems.php',2,'Shows the top selling items'),('total_dashboard.php',1,''),('UnitsOfMeasure.php',15,'Allows for units of measure to be defined'),('unpaid_invoice.php',2,''),('UpgradeDatabase.php',15,'Allows for the database to be automatically upgraded based on currently recorded DBUpgradeNumber config option'),('UserBankAccounts.php',15,'Maintains table bankaccountusers (Authorized users to work with a bank account in webERP)'),('UserGLAccounts.php',15,'Maintenance of GL Accounts allowed for a user'),('UserLocations.php',15,'Location User Maintenance'),('UserSettings.php',1,'Allows the user to change system wide defaults for the theme - appearance, the number of records to show in searches and the language to display messages in'),('WhereUsedInquiry.php',2,'Inquiry showing where an item is used ie all the parents where the item is a component of'),('WOCanBeProducedNow.php',4,'List of WO items that can be produced with available stock in location'),('WorkCentres.php',9,'Defines the various centres of work within a manufacturing company. Also the overhead and labour rates applicable to the work centre and its standard capacity'),('WorkOrderCosting.php',11,''),('WorkOrderEntry.php',10,'Entry of new work orders'),('WorkOrderIssue.php',11,'Issue of materials to a work order'),('WorkOrderReceive.php',11,'Allows for receiving of works orders'),('WorkOrderStatus.php',11,'Shows the status of works orders'),('work_orders.php',3,''),('WOSerialNos.php',10,''),('WWW_Access.php',15,'Adds or removes security roles by a system administrator'),('WWW_Users.php',15,'Entry of users and security settings of users'),('Z_BottomUpCosts.php',15,''),('Z_ChangeBranchCode.php',15,'Utility to change the branch code of a customer that cascades the change through all the necessary tables'),('Z_ChangeCustomerCode.php',15,'Utility to change a customer code that cascades the change through all the necessary tables'),('Z_ChangeGLAccountCode.php',15,'Script to change a GL account code accross all tables necessary'),('Z_ChangeLocationCode.php',15,'Change a locations code and in all tables where the old code was used to the new code'),('Z_ChangeSalesmanCode.php',15,'Utility to change a salesman code'),('Z_ChangeStockCategory.php',15,''),('Z_ChangeStockCode.php',15,'Utility to change an item code that cascades the change through all the necessary tables'),('Z_ChangeSupplierCode.php',15,'Script to change a supplier code accross all tables necessary'),('Z_CheckAllocationsFrom.php',15,''),('Z_CheckAllocs.php',2,''),('Z_CheckDebtorsControl.php',15,'Inquiry that shows the total local currency (functional currency) balance of all customer accounts to reconcile with the general ledger debtors account'),('Z_CheckGLTransBalance.php',15,'Checks all GL transactions balance and reports problem ones'),('Z_CreateChartDetails.php',9,'Utility page to create chart detail records for all general ledger accounts and periods created - needs expert assistance in use'),('Z_CreateCompany.php',15,'Utility to insert company number 1 if not already there - actually only company 1 is used - the system is not multi-company'),('Z_CreateCompanyTemplateFile.php',15,''),('Z_CurrencyDebtorsBalances.php',15,'Inquiry that shows the total foreign currency together with the total local currency (functional currency) balances of all customer accounts to reconcile with the general ledger debtors account'),('Z_CurrencySuppliersBalances.php',15,'Inquiry that shows the total foreign currency amounts and also the local currency (functional currency) balances of all supplier accounts to reconcile with the general ledger creditors account'),('Z_DataExport.php',15,''),('Z_DeleteCreditNote.php',15,'Utility to reverse a customer credit note - a desperate measure that should not be used except in extreme circumstances'),('Z_DeleteInvoice.php',15,'Utility to reverse a customer invoice - a desperate measure that should not be used except in extreme circumstances'),('Z_DeleteOldPrices.php',15,'Deletes all old prices'),('Z_DeleteSalesTransActions.php',15,'Utility to delete all sales transactions, sales analysis the lot! Extreme care required!!!'),('Z_DescribeTable.php',11,''),('Z_Fix1cAllocations.php',9,''),('Z_FixGLTransPeriods.php',15,'Fixes periods where GL transactions were not created correctly'),('Z_GLAccountUsersCopyAuthority.php',15,'Utility to copy authority of GL accounts from one user to another'),('Z_ImportChartOfAccounts.php',11,''),('Z_ImportDebtors.php',15,'Import debtors by csv file'),('Z_ImportFixedAssets.php',15,'Allow fixed assets to be imported from a csv'),('Z_ImportGLAccountGroups.php',11,''),('Z_ImportGLAccountSections.php',11,''),('Z_ImportGLTransactions.php',15,'Import General Ledger Transactions'),('Z_ImportPartCodes.php',11,'Allows inventory items to be imported from a csv'),('Z_ImportPriceList.php',15,'Loads a new price list from a csv file'),('Z_ImportStocks.php',15,''),('Z_index.php',15,'Utility menu page'),('Z_ItemsWithoutPicture.php',15,'Shows the list of curent items without picture in webERP'),('Z_MakeLocUsers.php',15,'Create User Location records'),('Z_MakeNewCompany.php',15,''),('Z_MakeStockLocns.php',15,'Utility to make LocStock records for all items and locations if not already set up.'),('Z_poAddLanguage.php',15,'Add a New Language to the System'),('Z_poAdmin.php',15,'Allows for a gettext language po file to be administered'),('Z_poEditLangHeader.php',15,'Edit a Language File Header'),('Z_poEditLangModule.php',15,'Edit a Language File Module'),('Z_poEditLangRemaining.php',15,'Edit Remaining Strings For This Language'),('Z_poRebuildDefault.php',15,'Rebuild the System Default Language File'),('Z_PriceChanges.php',15,'Utility to make bulk pricing alterations to selected sales type price lists or selected customer prices only'),('Z_ReApplyCostToSA.php',15,'Utility to allow the sales analysis table to be updated with the latest cost information - the sales analysis takes the cost at the time the sale was made to reconcile with the enteries made in the gl.'),('Z_RemovePurchaseBackOrders.php',1,'Removes all purchase order back orders'),('Z_RePostGLFromPeriod.php',15,'Utility to repost all general ledger transaction commencing from a specified period. This can take some time in busy environments. Normally GL transactions are posted automatically each time a trial balance or profit and loss account is run'),('Z_ReverseSuppPaymentRun.php',15,'Utility to reverse an entire Supplier payment run'),('Z_SalesIntegrityCheck.php',15,''),('Z_UpdateChartDetailsBFwd.php',15,'Utility to recalculate the ChartDetails table B/Fwd balances - extreme care!!'),('Z_UpdateItemCosts.php',15,'Use CSV of item codes and costs to update webERP item costs'),('Z_UpdateSalesAnalysisWithLatestCustomerData.php',15,'Updates the salesanalysis table with the latest data from the customer debtorsmaster salestype and custbranch sales area and sales person irrespective of the sales type, area, salesperson at the time when the sale was made'),('Z_Upgrade3.10.php',15,''),('Z_Upgrade_3.01-3.02.php',15,''),('Z_Upgrade_3.04-3.05.php',15,''),('Z_Upgrade_3.05-3.06.php',15,''),('Z_Upgrade_3.07-3.08.php',15,''),('Z_Upgrade_3.08-3.09.php',15,''),('Z_Upgrade_3.09-3.10.php',15,''),('Z_Upgrade_3.10-3.11.php',15,''),('Z_Upgrade_3.11-4.00.php',15,''),('Z_UploadForm.php',15,'Utility to upload a file to a remote server'),('Z_UploadResult.php',15,'Utility to upload a file to a remote server');
INSERT INTO `securitygroups` VALUES (1,0),(1,1),(1,2),(1,5),(2,0),(2,1),(2,2),(2,11),(3,0),(3,1),(3,2),(3,3),(3,4),(3,5),(3,11),(4,0),(4,1),(4,2),(4,5),(5,0),(5,1),(5,2),(5,3),(5,11),(6,0),(6,1),(6,2),(6,3),(6,4),(6,5),(6,6),(6,7),(6,8),(6,9),(6,10),(6,11),(7,0),(7,1),(8,0),(8,1),(8,2),(8,3),(8,4),(8,5),(8,6),(8,7),(8,8),(8,9),(8,10),(8,11),(8,12),(8,13),(8,14),(8,15),(8,16),(8,20),(9,0),(9,9);
INSERT INTO `securityroles` VALUES (1,'Inquiries/Order Entry'),(2,'Manufac/Stock Admin'),(3,'Purchasing Officer'),(4,'AP Clerk'),(5,'AR Clerk'),(6,'Accountant'),(7,'Customer Log On Only'),(8,'System Administrator'),(9,'Supplier Log On Only');
INSERT INTO `securitytokens` VALUES (0,'Main Index Page'),(1,'Order Entry/Inquiries customer access only'),(2,'Basic Reports and Inquiries with selection options'),(3,'Credit notes and AR management'),(4,'Purchasing data/PO Entry/Reorder Levels'),(5,'Accounts Payable'),(6,'Petty Cash'),(7,'Bank Reconciliations'),(8,'General ledger reports/inquiries'),(9,'Supplier centre - Supplier access only'),(10,'General Ledger Maintenance, stock valuation & Configuration'),(11,'Inventory Management and Pricing'),(12,'Prices Security'),(13,'Customer services Price modifications'),(14,'Unknown'),(15,'User Management and System Administration'),(16,'QA'),(18,'Cost authority'),(19,'Internal stock request fully access authority'),(20,'Timesheet administrator');
INSERT INTO `systypes` VALUES (0,'Journal - GL',0),(1,'Payment - GL',0),(2,'Receipt - GL',0),(3,'Standing Journal',0),(4,'Journal Template Number',0),(10,'Sales Invoice',0),(11,'Credit Note',0),(12,'Receipt',0),(15,'Journal - Debtors',0),(16,'Location Transfer',0),(17,'Stock Adjustment',0),(18,'Purchase Order',0),(19,'Picking List',0),(20,'Purchase Invoice',0),(21,'Debit Note',0),(22,'Creditors Payment',0),(23,'Creditors Journal',0),(25,'Purchase Order Delivery',0),(26,'Work Order Receipt',0),(28,'Work Order Issue',0),(29,'Work Order Variance',0),(30,'Sales Order',0),(31,'Shipment Close',0),(32,'Contract Close',0),(35,'Cost Update',0),(36,'Exchange Difference',0),(37,'Tenders',0),(38,'Stock Requests',0),(39,'Sales Commision Accruals',0),(40,'Work Order',0),(41,'Asset Addition',0),(42,'Asset Category Change',0),(43,'Delete w/down asset',0),(44,'Depreciation',0),(49,'Import Fixed Assets',0),(50,'Opening Balance',0),(500,'Auto Debtor Number',0),(600,'Auto Supplier Number',0);
CREATE TRIGGER `gltrans_after_delete` AFTER DELETE ON `gltrans` FOR EACH ROW
BEGIN
	UPDATE gltotals
	SET amount = amount - OLD.amount
	WHERE account = OLD.account AND period = OLD.periodno;
END;
CREATE TRIGGER gltrans_after_insert AFTER INSERT ON gltrans FOR EACH ROW
BEGIN
	INSERT INTO gltotals (account, period, amount)
	VALUES (NEW.account, NEW.periodno, NEW.amount)
	ON DUPLICATE KEY UPDATE amount = amount + NEW.amount;
END;
CREATE TRIGGER `gltrans_after_update` AFTER UPDATE ON `gltrans` FOR EACH ROW
BEGIN
	IF NEW.account <> OLD.account OR NEW.periodno <> OLD.periodno THEN
		-- Handle account or period changes.
		-- Deduct the old amount from the old account/period.
		UPDATE gltotals
		SET amount = amount - OLD.amount
		WHERE account = OLD.account AND period = OLD.periodno;

		-- Add the new amount to the new account/period.
		INSERT INTO gltotals (account, period, amount)
		VALUES (NEW.account, NEW.periodno, NEW.amount)
		ON DUPLICATE KEY UPDATE amount = amount + NEW.amount;
	ELSE
		-- Just update the amount if account and period are the same.
		UPDATE gltotals
		SET amount = amount - OLD.amount + NEW.amount
		WHERE account = NEW.account AND period = NEW.periodno;
	END IF;
END;
