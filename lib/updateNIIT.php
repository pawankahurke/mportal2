<?php

die("Code was removed");

// require_once 'l-db.php';
// include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";

// dropPreviousDashTables();
// createNewTables();

// function dropPreviousDashTables()
// {
//     $db = pdo_connect();

//     $sql1 = $db->prepare("DROP TABLE ".$GLOBALS['PREFIX']."core.`Dashboards`;");
//     $sql1->execute();

//     $sql2 = $db->prepare("DROP TABLE ".$GLOBALS['PREFIX']."core.`DashboardUsers`;");
//     $sql2->execute();

//     $sql3 = $db->prepare("DROP TABLE ".$GLOBALS['PREFIX']."core.`VisualizationUsers`;");
//     $sql3->execute();

//     $sql4 = $db->prepare("DROP TABLE ".$GLOBALS['PREFIX']."core.`DashboardTypes`;");
//     $sql4->execute();

//     $sql5 = $db->prepare("DROP TABLE ".$GLOBALS['PREFIX']."core.`VisualTypes`;");
//     $sql5->execute();

//     $sql6 = $db->prepare("DROP TABLE ".$GLOBALS['PREFIX']."core.`DashboardItem`;");
//     $sql6->execute();

//     $sql7 = $db->prepare("DROP TABLE ".$GLOBALS['PREFIX']."core.`UserDashboards`;");
//     $sql7->execute();
// }


// function createNewTables()
// {
//     $db = pdo_connect();
//     $sql = $db->prepare("select * from information_schema.TABLES where TABLE_SCHEMA='core' AND TABLE_NAME='Dashboards'");
//     $sql->execute();
//     $isExist = $sql->fetch(PDO::FETCH_ASSOC);

//     if (!$isExist) {
//         $query = "CREATE TABLE ".$GLOBALS['PREFIX']."core.`Dashboards` (
//                 `id` int(11) NOT NULL AUTO_INCREMENT,
//                 `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
//                 `visualization` int(11) DEFAULT NULL,
//                 `type` int(11) DEFAULT NULL,
//                 `createdby` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
//                 `createdon` int(11) DEFAULT NULL,
//                 `global` int(11) DEFAULT NULL,
//                 `home` int(11) DEFAULT NULL,
//                 UNIQUE KEY `id` (`id`),
//                 UNIQUE KEY `name` (`name`)
//               ) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
//         $sql = $db->prepare($query);
//         $sql->execute();
//         echo 'Created '.$GLOBALS['PREFIX'].'core.Dashboards TABLE<br />';
//     }

//     $sql = $db->prepare("select * from information_schema.TABLES where TABLE_SCHEMA='core' AND TABLE_NAME='DashboardUsers'");
//     $sql->execute();
//     $isExist = $sql->fetch(PDO::FETCH_ASSOC);

//     if (!$isExist) {
//         $query = "CREATE TABLE ".$GLOBALS['PREFIX']."core.`DashboardUsers` (
//                 `dashid` int(11) DEFAULT NULL,
//                 `user` varchar(5000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
//                 `allowedit` varchar(5000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
//                 `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
//               ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
//         $sql = $db->prepare($query);
//         $sql->execute();
//         echo 'Created '.$GLOBALS['PREFIX'].'core.DashboardUsers TABLE<br />';

//         $sql = $db->prepare("select distinct userid from ".$GLOBALS['PREFIX']."core.Users");
//         $sql->execute();
//         $res = $sql->fetchAll(PDO::FETCH_ASSOC);
//         $usrIdArr = array();
//         foreach ($res as $k => $v) {
//             array_push($usrIdArr, $v['userid']);
//         }
//         $userids = implode(',', $usrIdArr);
//         $sql = $db->prepare("INSERT INTO ".$GLOBALS['PREFIX']."core.`DashboardUsers` (`dashid`, `user`, `allowedit`, `type`) VALUES (?,?,?,?);");
//         $sql->execute([209, $userids, 1, 'org']);
//         echo 'Inserted Default Entries Into '.$GLOBALS['PREFIX'].'core.DashboardUsers TABLE<br />';
//     } else {
//         $sql = $db->prepare("select distinct userid from ".$GLOBALS['PREFIX']."core.Users");
//         $sql->execute();
//         $res = $sql->fetchAll(PDO::FETCH_ASSOC);
//         $usrIdArr = array();
//         foreach ($res as $k => $v) {
//             array_push($usrIdArr, $v['user_id']);
//         }

//         $userids = implode(',', $usrIdArr);
//     }

//     $sql = $db->prepare("select * from information_schema.TABLES where TABLE_SCHEMA='core' AND TABLE_NAME='VisualizationUsers'");
//     $sql->execute();
//     $isExist = $sql->fetch(PDO::FETCH_ASSOC);

//     if (!$isExist) {
//         $query = "CREATE TABLE ".$GLOBALS['PREFIX']."core.`VisualizationUsers` (
//                 `dashid` int(11) DEFAULT NULL,
//                 `user` varchar(5000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
//                 `allowedit` varchar(5000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
//                 `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
//               ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
//         $sql = $db->prepare($query);
//         $sql->execute();
//         echo 'Created '.$GLOBALS['PREFIX'].'core.VisualizationUsers TABLE<br />';
//     }

//     $sql = $db->prepare("select * from information_schema.TABLES where TABLE_SCHEMA='core' AND TABLE_NAME='DashboardTypes'");
//     $sql->execute();
//     $isExist = $sql->fetch(PDO::FETCH_ASSOC);

//     if (!$isExist) {
//         $query = "CREATE TABLE ".$GLOBALS['PREFIX']."core.`DashboardTypes` (
//             `dashType` int(11) NOT NULL AUTO_INCREMENT,
//             `description` varchar(50) DEFAULT NULL,
//             `schema_id` INT(11) NULL DEFAULT NULL,
//             PRIMARY KEY (`dashType`)
//           ) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1";
//         $sql = $db->prepare($query);
//         $sql->execute();
//         echo 'Created '.$GLOBALS['PREFIX'].'core.DashboardTypes TABLE<br />';

//         $sql = $db->prepare("INSERT INTO ".$GLOBALS['PREFIX']."core.`DashboardTypes` (`dashType`,`description`,`schema_id`) VALUES (1,'Experience',0),
//                 (2,'Consumption',0),
//                 (3,'Utilization',0),
//                 (4,'Automation',4),
//                 (5,'Compliance',0);");
//         $sql->execute();
//         echo 'Inserted Default Entries Into '.$GLOBALS['PREFIX'].'core.DashboardTypes TABLE<br />';
//     }

//     $sql = $db->prepare("select * from information_schema.TABLES where TABLE_SCHEMA='core' AND TABLE_NAME='VisualTypes'");
//     $sql->execute();
//     $isExist = $sql->fetch(PDO::FETCH_ASSOC);

//     if (!$isExist) {
//         $query = "CREATE TABLE ".$GLOBALS['PREFIX']."core.`VisualTypes` (
//                 `vizType` int(11) NOT NULL AUTO_INCREMENT,
//                 `description` varchar(50) DEFAULT NULL,
//         	`schema_id` INT(11) NULL DEFAULT NULL,
//                 PRIMARY KEY (`vizType`)
//               ) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC";
//         $sql = $db->prepare($query);
//         $sql->execute();
//         echo 'Created '.$GLOBALS['PREFIX'].'core.VisualTypes TABLE<br />';

//         $sql = $db->prepare("INSERT INTO ".$GLOBALS['PREFIX']."core.`VisualTypes` (`vizType`,`description`,`schema_id`) VALUES (1,'Experience',0),
//                 (2,'Consumption',0),
//                 (3,'Utilization',0),
//                 (4,'Automation',0),
//                 (5,'Compliance',0);");
//         $sql->execute();
//         echo 'Inserted Default Entries Into '.$GLOBALS['PREFIX'].'core.VisualTypes TABLE<br />';
//     }

//     $sql = $db->prepare("select * from information_schema.TABLES where TABLE_SCHEMA='core' AND TABLE_NAME='DashboardItem'");
//     $sql->execute();
//     $isExist = $sql->fetch(PDO::FETCH_ASSOC);

//     if (!$isExist) {
//         $query = "CREATE TABLE ".$GLOBALS['PREFIX']."core.`DashboardItem` (
//                 `id` int(11) NOT NULL AUTO_INCREMENT,
//                 `layout` varchar(150) DEFAULT NULL,
//                 `vizState` longtext,
//                 `name` varchar(150) DEFAULT NULL,
//                 `dashid` int(20) DEFAULT NULL,
//                 PRIMARY KEY (`id`)
//               ) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1";
//         $sql = $db->prepare($query);
//         $sql->execute();
//         echo 'Created '.$GLOBALS['PREFIX'].'core.DashboardItem TABLE<br />';
//     }

//     $sql = $db->prepare("select * from information_schema.TABLES where TABLE_SCHEMA='core' AND TABLE_NAME='UserDashboardItem'");
//     $sql->execute();
//     $isExist = $sql->fetch(PDO::FETCH_ASSOC);

//     if (!$isExist) {
//         $query = "CREATE TABLE ".$GLOBALS['PREFIX']."core.`UserDashboardItem` (
//                 `id` int(11) NOT NULL AUTO_INCREMENT,
//                 `layout` varchar(150) DEFAULT NULL,
//                 `vizState` longtext,
//                 `name` varchar(150) DEFAULT NULL,
//                 `dashid` int(20) DEFAULT NULL,
//                 `username` varchar(100) DEFAULT NULL,
//                 PRIMARY KEY (`id`)
//               ) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=latin1";
//         $sql = $db->prepare($query);
//         $sql->execute();
//         echo 'Created '.$GLOBALS['PREFIX'].'core.UserDashboardItem TABLE<br />';
//     }

//     $sql = $db->prepare("select * from information_schema.TABLES where TABLE_SCHEMA='core' AND TABLE_NAME='UserDashboards'");
//     $sql->execute();
//     $isExist = $sql->fetch(PDO::FETCH_ASSOC);

//     if (!$isExist) {
//         $query = "CREATE TABLE ".$GLOBALS['PREFIX']."core.`UserDashboards` (
//             `id` int(11) NOT NULL AUTO_INCREMENT,
//             `did` int(11) DEFAULT NULL,
//             `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
//             `type` int(11) DEFAULT NULL,
//             `visualization` int(11) DEFAULT NULL,
//             `createdby` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
//             `createdon` int(11) DEFAULT NULL,
//             `global` int(11) DEFAULT NULL,
//             `home` int(11) DEFAULT NULL,
//             UNIQUE KEY `id` (`id`),
//             UNIQUE KEY `name` (`name`)
//           ) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC";
//         $sql = $db->prepare($query);
//         $sql->execute();
//         echo 'Created '.$GLOBALS['PREFIX'].'core.UserDashboards TABLE<br />';

//         $sql = $db->prepare("INSERT INTO ".$GLOBALS['PREFIX']."core.`UserDashboards` (`id`, `did`, `name`, `type`, `visualization`, `createdby`, `createdon`, `global`, `home`) VALUES (907, NULL, 'Automation analytics', 1, 0, 'admin', 1609831303, 1, 1),  
//             (935, NULL, 'Insight : Device Reporting', 1, 0, 'admin', 1610185315, 1, 1),     
//             (936, NULL, 'Insight : Resolutions executed', 1, 0, 'admin', 1610188011, 1, 1),
//             (941, NULL, 'Asset Information', 1, 0, 'admin', 1610188011, 1, 1),
//             (942, NULL, 'Device Summary', 1, 0, 'admin', 1610188011, 1, 1),
//             (944, NULL, 'Installed Softwares', 1, 0, 'admin', 1610188011, 1, 1),
//             (945, NULL, 'Persona Device Health', 1, 0, 'admin', 1610188011, 1, 1),
//             (946, NULL, 'Persona Devices', 1, 0, 'admin', 1610188011, 1, 1),
//             (947, NULL, 'Events Details', 1, 0, 'admin', 1610188011, 1, 1);");
//         $sql->execute();
//         echo 'Inserted Default Entries Into '.$GLOBALS['PREFIX'].'core.DashboardTypes TABLE<br />';
//     }
// }
