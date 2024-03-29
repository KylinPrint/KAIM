-- ----------------------------
-- Records of admin_permissions
-- ----------------------------
TRUNCATE TABLE `admin_permissions`;
INSERT INTO `admin_permissions` VALUES (1, 'Auth management', 'auth-management', NULL, NULL, 0, 0, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (2, 'Users', 'users', NULL, '/auth/users*', 0, 1, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (3, 'Roles', 'roles', NULL, '/auth/roles*', 0, 1, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (4, 'Permissions', 'permissions', NULL, '/auth/permissions*', 0, 1, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (5, 'Menu', 'menu', NULL, '/auth/menu*', 0, 1, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (6, 'Extension', 'extension', NULL, '/auth/extensions*', 0, 1, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (7, '外设适配情况权限', 'pbinds', NULL, '/pbinds*', 0, 0, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (8, '外设适配情况-查看', 'pbinds-get', 'GET', '/pbinds*', 0, 7, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (9, '外设适配情况-编辑', 'pbinds-edit', 'GET,POST,PUT', '/pbinds*', 0, 7, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (10, '外设适配情况-删除', 'pbinds-delete', 'DELETE', '/pbinds*', 0, 7, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (11, '外设适配情况-操作', 'pbinds-action', NULL, NULL, 0, 7, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (12, '外设适配情况-导入', 'pbinds-import', 'GET,POST,PUT', NULL, 0, 7, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (13, '外设适配情况-导出', 'pbinds-export', 'GET,POST,PUT', NULL, 0, 7, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (14, '外设数据管理权限', 'peripherals', NULL, '/peripherals*', 0, 0, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (15, '外设数据管理-查看', 'peripherals-get', 'GET', '/peripherals', 0, 14, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (16, '外设数据管理-编辑', 'peripherals-edit', 'GET,POST,PUT', '/peripherals*', 0, 14, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (17, '外设数据管理-删除', 'peripherals-delete', 'DELETE', '/peripherals*', 0, 14, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (18, '外设数据管理-操作', 'peripherals-action', NULL, NULL, 0, 14, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (19, '外设数据管理-导入', 'peripherals-import', 'GET,POST,PUT', NULL, 0, 14, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (20, '外设数据管理-导出', 'peripherals-export', 'GET,POST,PUT', NULL, 0, 14, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (21, '软件适配情况权限', 'sbinds', NULL, '/sbinds*', 0, 0, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (22, '软件适配情况-查看', 'sbinds-get', 'GET', '/sbinds*', 0, 21, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (23, '软件适配情况-编辑', 'sbinds-edit', 'GET,POST,PUT', '/sbinds*', 0, 21, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (24, '软件适配情况-删除', 'sbinds-delete', 'DELETE', '/sbinds*', 0, 21, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (25, '软件适配情况-操作', 'sbinds-action', NULL, NULL, 0, 21, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (26, '软件适配情况-导入', 'sbinds-import', 'GET,POST,PUT', NULL, 0, 21, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (27, '软件适配情况-导出', 'sbinds-export', 'GET,POST,PUT', NULL, 0, 21, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (28, '软件数据管理权限', 'softwares', NULL, '/softwares*', 0, 0, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (29, '软件数据管理-查看', 'softwares-get', 'GET', '/softwares', 0, 28, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (30, '软件数据管理-编辑', 'softwares-edit', 'GET,POST,PUT', '/softwares*', 0, 28, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (31, '软件数据管理-删除', 'softwares-delete', 'DELETE', '/softwares*', 0, 28, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (32, '软件数据管理-操作', 'softwares-action', NULL, NULL, 0, 28, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (33, '软件数据管理-导入', 'softwares-import', 'GET,POST,PUT', NULL, 0, 28, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (34, '软件数据管理-导出', 'softwares-export', 'GET,POST,PUT', NULL, 0, 28, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (35, '关联信息管理-试运行', 'others', NULL, NULL, 0, 0, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (36, '厂商信息管理-试运行', 'manufactors', NULL, '/manufactors*', 0, 35, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (37, '品牌信息管理-试运行', 'brands', NULL, '/brands*', 0, 35, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (38, '芯片信息管理-试运行', 'chips', NULL, '/chips*', 0, 35, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (39, '系统信息管理-试运行', 'release', NULL, '/releases*', 0, 35, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (40, '分类信息管理-试运行', 'types', NULL, '/*types*', 0, 35, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (41, '参数名管理-试运行', 'specifications', NULL, '/specifications*', 0, 35, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (42, '状态信息管理-试运行', 'statuses', NULL, '/statuses*', 0, 35, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (43, '解决方案快速筛查', 'solution-match', NULL, '/solution-match*', 0, 0, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (44, '数据统计', 'statistics', NULL, '/statistics*', 0, 0, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (45, 'API', 'API', NULL, NULL, 0, 0, '2022-04-01 16:50:54', '2022-04-01 16:50:54');
INSERT INTO `admin_permissions` VALUES (46, 'P分页', 'pPag', NULL, '/api/peripherals*', 0, 45, '2022-04-01 16:54:10', '2022-04-01 16:55:11');
INSERT INTO `admin_permissions` VALUES (47, 'S分页', 'sPag', NULL, '/api/softwares*', 0, 45, '2022-04-01 16:55:02', '2022-04-01 16:55:11');
INSERT INTO `admin_permissions` VALUES (48, '软件需求管理权限', 'srequests', NULL, '/srequests*', 0, 0, '2022-04-02 08:29:04', '2022-04-02 08:30:14');
INSERT INTO `admin_permissions` VALUES (49, '软件需求管理-查看', 'srequests-get', 'GET', '/srequests', 0, 48, '2022-04-02 08:31:30', '2022-04-02 08:31:30');
INSERT INTO `admin_permissions` VALUES (50, '软件需求管理-编辑', 'srequests-edit', 'GET,POST,PUT', '/srequests*', 0, 48, '2022-04-02 08:33:20', '2022-04-02 08:35:45');
INSERT INTO `admin_permissions` VALUES (51, '软件需求管理-删除', 'srequests-delete', 'DELETE', '/srequests*', 0, 48, '2022-04-02 08:34:12', '2022-04-02 08:34:12');
INSERT INTO `admin_permissions` VALUES (52, '软件需求管理-操作', 'srequests-action', NULL, NULL, 0, 48, '2022-04-02 08:35:36', '2022-04-02 08:35:36');
INSERT INTO `admin_permissions` VALUES (53, '外设需求管理权限', 'prequests', NULL, '/prequests*', 0, 0, '2022-04-02 08:36:19', '2022-04-02 08:37:25');
INSERT INTO `admin_permissions` VALUES (54, '外设需求管理-查看', 'prequests-get', 'GET', '/prequests', 0, 53, '2022-04-02 08:37:15', '2022-04-02 08:37:15');
INSERT INTO `admin_permissions` VALUES (55, '外设需求管理-编辑', 'prequests-edit', 'GET,POST,PUT', '/prequests*', 0, 53, '2022-04-02 08:37:15', '2022-04-02 08:37:15');
INSERT INTO `admin_permissions` VALUES (56, '外设需求管理-删除', 'prequests-delete', 'DELETE', '/prequests*', 0, 53, '2022-04-02 08:37:15', '2022-04-02 08:37:15');
INSERT INTO `admin_permissions` VALUES (57, '外设需求管理-操作', 'prequests-action', NULL, NULL, 0, 53, '2022-04-02 08:37:15', '2022-04-02 08:37:15');
INSERT INTO `admin_permissions` VALUES (58, '整机适配情况权限', 'oems', NULL, '/oems*', 0, 0, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (59, '整机适配情况-查看', 'oems-get', 'GET', '/oems*', 0, 58, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (60, '整机适配情况-删除', 'oems-delete', 'DELETE', '/oems*', 0, 58, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (61, '整机适配情况-导入', 'oems-import', 'GET,POST,PUT', NULL, 0, 58, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
INSERT INTO `admin_permissions` VALUES (62, '整机适配情况-导出', 'oems-export', 'GET,POST,PUT', NULL, 0, 58, '2022-02-21 06:02:26', '2022-02-21 06:02:26');
