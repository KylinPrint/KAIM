-- ----------------------------
-- Columns update for Bind histories
-- ----------------------------
ALTER TABLE `kylinprint`.`pbinds` ADD COLUMN `admin_users_id` BIGINT ( 20 ) UNSIGNED NOT NULL COMMENT '当前适配状态责任人' AFTER `statuses_id`,
ADD CONSTRAINT `pbinds_admin_users_id_foreign` FOREIGN KEY ( `admin_users_id` ) REFERENCES `kylinprint`.`admin_users` ( `id` ) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `kylinprint`.`sbinds` ADD COLUMN `admin_users_id` BIGINT ( 20 ) UNSIGNED NOT NULL COMMENT '当前适配状态责任人' AFTER `statuses_id`,
ADD CONSTRAINT `sbinds_admin_users_id_foreign` FOREIGN KEY ( `admin_users_id` ) REFERENCES `kylinprint`.`admin_users` ( `id` ) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `kylinprint`.`pbind_histories` ADD COLUMN `admin_users_id` BIGINT ( 20 ) UNSIGNED NOT NULL COMMENT '当前适配状态责任人' AFTER `statuses_new`,
ADD CONSTRAINT `pbind_histories_admin_users_id_foreign` FOREIGN KEY ( `admin_users_id` ) REFERENCES `kylinprint`.`admin_users` ( `id` ) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `kylinprint`.`sbind_histories` ADD COLUMN `admin_users_id` BIGINT ( 20 ) UNSIGNED NOT NULL COMMENT '当前适配状态责任人' AFTER `statuses_new`,
ADD CONSTRAINT `sbind_histories_admin_users_id_foreign` FOREIGN KEY ( `admin_users_id` ) REFERENCES `kylinprint`.`admin_users` ( `id` ) ON DELETE RESTRICT ON UPDATE RESTRICT;

-- ----------------------------
-- Records of admin_menu
-- ----------------------------
TRUNCATE table `admin_menu`;
INSERT INTO `admin_menu` VALUES (1, 0, 1, 'Index', 'feather icon-bar-chart-2', '/', '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (2, 0, 2, 'Softwares_Management', NULL, NULL, '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (3, 2, 3, 'Software_Adaptions', NULL, 'sbinds', '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (4, 2, 4, 'Softwares', NULL, 'softwares', '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (5, 0, 5, 'Peripherals_Management', NULL, NULL, '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (6, 5, 6, 'Peripheral_Adaptions', NULL, 'pbinds', '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (7, 5, 7, 'Printers', NULL, 'peripherals?type=5', '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (8, 5, 8, 'Scanners', NULL, 'peripherals?type=6', '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (9, 5, 9, 'Touchscreens', NULL, 'peripherals?type=7', '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (10, 5, 10, 'BarcodeScanners', NULL, 'peripherals?type=8', '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (11, 5, 11, 'WebCams', NULL, 'peripherals?type=9', '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (12, 5, 12, 'Gaopaiyi', NULL, 'peripherals?type=10', '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (13, 5, 13, 'CardReader', NULL, 'peripherals?type=11', '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (14, 5, 14, 'IDScanner', NULL, 'peripherals?type=12', '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (15, 5, 15, 'FIngers', NULL, 'peripherals?type=13', '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (16, 5, 16, 'Shouxieban', NULL, 'peripherals?type=14', '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (17, 5, 17, 'Shouxieping', NULL, 'peripherals?type=15', '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (18, 5, 18, 'WLANAdapters', NULL, 'peripherals?type=16', '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (19, 5, 19, 'UKeys', NULL, 'peripherals?type=17', '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (20, 0, 20, 'Others', NULL, NULL, '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (21, 20, 21, 'Manufactors', NULL, 'manufactors', '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (22, 20, 22, 'Brands', NULL, 'brands', '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (23, 20, 23, 'Chips', NULL, 'chips', '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (24, 20, 24, 'Releases', NULL, 'releases', '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (25, 20, 25, 'Types', NULL, 'types', '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (26, 20, 26, 'Specifications', NULL, 'specifications', '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (27, 20, 27, 'Statuses', NULL, 'statuses', '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (28, 20, 28, 'Solutions', NULL, 'solutions', '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (29, 0, 29, 'Requirements', 'fa-bicycle', NULL, '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (30, 0, 30, 'Tools', 'fa-wrench', NULL, '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (31, 30, 31, 'Solution_Query', NULL, 'solution_matches', '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (32, 0, 32, 'Admin', 'feather icon-settings', NULL, '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (33, 32, 33, 'Users', NULL, 'auth/users', '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (34, 32, 34, 'Roles', NULL, 'auth/roles', '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (35, 32, 35, 'Permission', NULL, 'auth/permissions', '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (36, 32, 36, 'Menu', NULL, 'auth/menu', '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `admin_menu` VALUES (37, 32, 37, 'Extensions', NULL, 'auth/extensions', '', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');

-- ----------------------------
-- Records of industries
-- ----------------------------
INSERT INTO `industries` VALUES (1, '政务', '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `industries` VALUES (2, '金融', '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `industries` VALUES (3, '通信', '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `industries` VALUES (4, '交通', '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `industries` VALUES (5, '医疗', '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `industries` VALUES (6, '教育', '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `industries` VALUES (7, '电力', '2022-02-21 14:02:26', '2022-02-21 14:02:26');

-- ----------------------------
-- Records of statuses
-- ----------------------------
INSERT INTO `statuses` VALUES (1, '未适配', NULL, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (2, '适配中', NULL, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (3, '已适配', NULL, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (4, '待验证', NULL, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (5, '适配暂停', NULL, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (6, '厂商暂无适配计划', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (7, '待与厂商沟通', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (8, '与厂商正在沟通', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (9, '云平台资源准备中', 2, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (10, '适配方案开发中', 2, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (11, '厂商安排测试环境中', 2, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (12, '测试相关设备借收货中', 2, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (13, '厂商适配申请准备中', 2, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (14, '适配申请生态审核中', 2, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (15, '适配测试中—远程测试', 2, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (16, '适配测试中—出差测试', 2, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (17, '适配测试中—视频复测', 2, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (18, '适配测试中—麒麟内测', 2, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (19, '适配问题定位分析中', 2, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (20, '问题修复中—厂商问题', 2, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (21, '问题修复中—系统问题', 2, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (22, '问题修复中—其他问题', 2, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (23, '问题复测中', 2, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (24, '测试报告确认中', 2, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (25, '互认证证书制作中', 3, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (26, '证书邮寄中（后期）', 3, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (27, '证书归档中（后期）', 3, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (28, '证书已归档', 3, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (29, '测试报告已上传至生态网站', 3, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (30, '适配成果数据已更新至生态网站', 3, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (31, '适配成果已上架至软件商店', 3, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (32, '麒麟自研适配方案，内部已验证通过', 3, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (33, '麒麟自研适配方案，待内部验证', 4, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (34, '麒麟原因', 5, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (35, '厂商原因', 5, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `statuses` VALUES (36, '其他原因', 5, '2022-02-21 14:02:26', '2022-02-21 14:02:26');

-- ----------------------------
-- Records of types
-- ----------------------------
INSERT INTO `types` VALUES (1, '输出设备', NULL, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `types` VALUES (2, '输入设备', NULL, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `types` VALUES (3, '网络设备', NULL, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `types` VALUES (4, '软硬件一体', NULL, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `types` VALUES (5, '打印机', 1, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `types` VALUES (6, '扫描仪', 2, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `types` VALUES (7, '触摸屏', 2, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `types` VALUES (8, '扫描枪', 2, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `types` VALUES (9, '摄像头', 2, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `types` VALUES (10, '高拍仪', 2, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `types` VALUES (11, '读卡器', 2, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `types` VALUES (12, '身份证阅读机具', 2, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `types` VALUES (13, '指纹设备', 2, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `types` VALUES (14, '手写板', 2, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `types` VALUES (15, '手写屏', 2, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `types` VALUES (16, '无线网卡', 3, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
INSERT INTO `types` VALUES (17, 'UKey', 4, '2022-02-21 14:02:26', '2022-02-21 14:02:26');
