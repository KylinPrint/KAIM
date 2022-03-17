-- ----------------------------
-- Columns update for Bind histories
-- ----------------------------
ALTER TABLE `kylinprint`.`pbinds` ADD COLUMN `admin_users_id` BIGINT ( 20 ) UNSIGNED NOT NULL COMMENT '当前适配状态责任人' AFTER `statuses_id`,
ADD CONSTRAINT `pbinds_admin_users_id_foreign` FOREIGN KEY ( `admin_users_id` ) REFERENCES `kylinprint`.`admin_users` ( `id` ) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `kylinprint`.`sbinds` ADD COLUMN `admin_users_id` BIGINT ( 20 ) UNSIGNED NOT NULL COMMENT '当前适配状态责任人' AFTER `statuses_id`,
ADD CONSTRAINT `sbinds_admin_users_id_foreign` FOREIGN KEY ( `admin_users_id` ) REFERENCES `kylinprint`.`admin_users` ( `id` ) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `kylinprint`.`pbind_histories` ADD COLUMN `admin_users_id` BIGINT ( 20 ) UNSIGNED NOT NULL COMMENT '当前适配状态责任人' AFTER `status_new`,
ADD CONSTRAINT `pbind_histories_admin_users_id_foreign` FOREIGN KEY ( `admin_users_id` ) REFERENCES `kylinprint`.`admin_users` ( `id` ) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `kylinprint`.`sbind_histories` ADD COLUMN `admin_users_id` BIGINT ( 20 ) UNSIGNED NOT NULL COMMENT '当前适配状态责任人' AFTER `status_new`,
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
-- Records of admin_permissions
-- ----------------------------
INSERT INTO `admin_permissions` VALUES (7, '外设适配情况权限', 'pbinds', NULL, '/pbinds*', 7, 0, '2022-03-07 09:55:07', NULL);
INSERT INTO `admin_permissions` VALUES (8, '外设适配情况-查看', 'pbinds-get', 'GET', '/pbinds', 8, 13, '2022-03-07 09:55:07', NULL);
INSERT INTO `admin_permissions` VALUES (9, '外设适配情况-编辑', 'pbinds-edit', 'GET,POST,PUT', '/pbinds/*/edit', 9, 13, '2022-03-07 09:55:07', NULL);
INSERT INTO `admin_permissions` VALUES (10, '外设适配情况-新建', 'pbinds-create', 'GET,POST,PUT', '/pbinds/create*', 10, 13, '2022-03-07 09:55:07', NULL);
INSERT INTO `admin_permissions` VALUES (11, '外设适配情况-删除', 'pbinds-delete', 'DELETE', '/pbinds*', 11, 13, '2022-03-07 09:55:07', NULL);
INSERT INTO `admin_permissions` VALUES (12, '外设适配情况-操作', 'pbinds-action', NULL, NULL, 12, 13, '2022-03-07 09:55:07', NULL);
INSERT INTO `admin_permissions` VALUES (13, '外设适配情况-导入', 'pbinds-import', 'GET,POST,PUT', NULL, 13, 13, '2022-03-07 09:55:07', NULL);
INSERT INTO `admin_permissions` VALUES (14, '外设适配情况-导出', 'pbinds-export', 'GET,POST,PUT', NULL, 14, 13, '2022-03-07 09:55:07', NULL);
INSERT INTO `admin_permissions` VALUES (15, '外设数据管理权限', 'peripherals', NULL, NULL, 15, 0, '2022-03-07 09:55:07', NULL);
INSERT INTO `admin_permissions` VALUES (16, '外设数据管理-查看', 'peripherals-get', 'GET', '/peripherals', 16, 21, '2022-03-07 09:55:07', NULL);
INSERT INTO `admin_permissions` VALUES (17, '外设数据管理-编辑', 'peripherals-edit', 'GET,POST,PUT', '/peripherals/*/edit', 17, 21, '2022-03-07 09:55:07', NULL);
INSERT INTO `admin_permissions` VALUES (18, '外设数据管理-新建', 'peripherals-create', 'GET,POST,PUT', '/peripherals/create*', 18, 21, '2022-03-07 09:55:07', NULL);
INSERT INTO `admin_permissions` VALUES (19, '外设数据管理-删除', 'peripherals-delete', 'DELETE', '/peripherals*', 19, 21, '2022-03-07 09:55:07', NULL);
INSERT INTO `admin_permissions` VALUES (20, '外设数据管理-操作', 'peripherals-action', NULL, NULL, 20, 21, '2022-03-07 09:55:07', NULL);
INSERT INTO `admin_permissions` VALUES (21, '外设数据管理-导入', 'peripherals-import', 'GET,POST,PUT', NULL, 21, 21, '2022-03-07 09:55:07', NULL);
INSERT INTO `admin_permissions` VALUES (22, '外设数据管理-导出', 'peripherals-export', 'GET,POST,PUT', NULL, 22, 21, '2022-03-07 09:55:07', NULL);
INSERT INTO `admin_permissions` VALUES (23, '软件适配情况权限', 'sbinds', NULL, '/sbinds*', 23, 0, '2022-03-07 09:55:07', NULL);
INSERT INTO `admin_permissions` VALUES (24, '软件适配情况-查看', 'sbinds-get', 'GET', '/sbinds', 24, 29, '2022-03-07 09:55:07', NULL);
INSERT INTO `admin_permissions` VALUES (25, '软件适配情况-编辑', 'sbinds-edit', 'GET,POST,PUT', '/sbinds/*/edit', 25, 29, '2022-03-07 09:55:07', NULL);
INSERT INTO `admin_permissions` VALUES (26, '软件适配情况-新建', 'sbinds-create', 'GET,POST,PUT', '/sbinds/create*', 26, 29, '2022-03-07 09:55:07', NULL);
INSERT INTO `admin_permissions` VALUES (27, '软件适配情况-删除', 'sbinds-delete', 'DELETE', '/sbinds*', 27, 29, '2022-03-07 09:55:07', NULL);
INSERT INTO `admin_permissions` VALUES (28, '软件适配情况-操作', 'sbinds-action', NULL, NULL, 28, 29, '2022-03-07 09:55:07', NULL);
INSERT INTO `admin_permissions` VALUES (29, '软件适配情况-导入', 'sbinds-import', 'GET,POST,PUT', NULL, 29, 29, '2022-03-07 09:55:07', NULL);
INSERT INTO `admin_permissions` VALUES (30, '软件适配情况-导出', 'sbinds-export', 'GET,POST,PUT', NULL, 30, 29, '2022-03-07 09:55:07', NULL);
INSERT INTO `admin_permissions` VALUES (31, '外设数据管理权限', 'softwares', NULL, NULL, 31, 0, '2022-03-07 09:55:07', NULL);
INSERT INTO `admin_permissions` VALUES (32, '外设数据管理-查看', 'softwares-get', 'GET', '/softwares', 32, 37, '2022-03-07 09:55:07', NULL);
INSERT INTO `admin_permissions` VALUES (33, '外设数据管理-编辑', 'softwares-edit', 'GET,POST,PUT', '/softwares/*/edit', 33, 37, '2022-03-07 09:55:07', NULL);
INSERT INTO `admin_permissions` VALUES (34, '外设数据管理-新建', 'softwares-create', 'GET,POST,PUT', '/softwares/create*', 34, 37, '2022-03-07 09:55:07', NULL);
INSERT INTO `admin_permissions` VALUES (35, '外设数据管理-删除', 'softwares-delete', 'DELETE', '/softwares*', 35, 37, '2022-03-07 09:55:07', NULL);
INSERT INTO `admin_permissions` VALUES (36, '外设数据管理-操作', 'softwares-action', NULL, NULL, 36, 37, '2022-03-17 16:53:15', '2022-03-17 16:53:17');
INSERT INTO `admin_permissions` VALUES (37, '外设数据管理-导入', 'softwares-import', 'GET,POST,PUT', NULL, 37, 37, '2022-03-17 16:53:15', '2022-03-17 16:53:17');
INSERT INTO `admin_permissions` VALUES (38, '外设数据管理-导出', 'softwares-export', 'GET,POST,PUT', NULL, 38, 37, '2022-03-17 16:53:15', '2022-03-17 16:53:17');
INSERT INTO `admin_permissions` VALUES (39, '关联信息管理-试运行', 'setting-debug', NULL, NULL, 39, 0, '2022-03-17 17:02:55', '2022-03-17 17:02:55');
INSERT INTO `admin_permissions` VALUES (40, '厂商信息管理-试运行', 'manufacture-all', NULL, '/manufactors*', 40, 45, '2022-03-17 17:04:05', '2022-03-17 17:04:05');
INSERT INTO `admin_permissions` VALUES (41, '品牌信息管理-试运行', 'brands-all', NULL, '/brands*', 41, 45, '2022-03-17 17:04:49', '2022-03-17 17:04:49');
INSERT INTO `admin_permissions` VALUES (42, '芯片信息管理-试运行', 'chips-all', NULL, '/chips*', 42, 45, '2022-03-17 17:05:42', '2022-03-17 17:05:42');
INSERT INTO `admin_permissions` VALUES (43, '系统信息管理-试运行', 'release-all', NULL, '/releases*', 43, 45, '2022-03-17 17:06:42', '2022-03-17 17:06:42');
INSERT INTO `admin_permissions` VALUES (44, '分类信息管理-试运行', 'types-all', NULL, '/types*', 44, 45, '2022-03-17 17:07:35', '2022-03-17 17:07:35');
INSERT INTO `admin_permissions` VALUES (45, '参数名管理-试运行', 'specifications-all', NULL, '/specifications*', 45, 45, '2022-03-17 17:08:46', '2022-03-17 17:08:46');
INSERT INTO `admin_permissions` VALUES (46, '状态信息管理-试运行', 'status-all', NULL, '/statuses*', 46, 45, '2022-03-17 17:09:28', '2022-03-17 17:09:28');
INSERT INTO `admin_permissions` VALUES (47, '解决方案信息管理-试运行', 'solutions-all', NULL, '/solutions*', 47, 45, '2022-03-17 17:10:06', '2022-03-17 17:10:06');

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
