/*
Navicat MySQL Data Transfer

Source Server         : testOa
Source Server Version : 50709
Source Host           : 192.168.32.121:3306
Source Database       : testoa3

Target Server Type    : MYSQL
Target Server Version : 50709
File Encoding         : 65001

Date: 2016-10-12 16:28:31
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for oa_org
-- ----------------------------
DROP TABLE IF EXISTS `oa_org`;
CREATE TABLE `oa_org` (
  `org_id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL DEFAULT '1' COMMENT '公司id',
  `org_name` varchar(50) NOT NULL COMMENT '组织名称',
  `parent_org_id` int(11) NOT NULL DEFAULT '0' COMMENT '上级组织id',
  `org_points` int(11) NOT NULL DEFAULT '0' COMMENT '组织积分',
  `org_all_points` int(11) NOT NULL DEFAULT '0' COMMENT '已拨分值',
  `all_children_id` varchar(500) NOT NULL DEFAULT '0' COMMENT '子id',
  `all_parent_id` varchar(500) NOT NULL DEFAULT '0' COMMENT '所有的父id',
  PRIMARY KEY (`org_id`)
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of oa_org
-- ----------------------------
INSERT INTO `oa_org` VALUES ('2', '1', '纳米娱乐', '0', '0', '0', '63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,56,57,58,59,60,61,62,79,80,82,12,13,14,15,16,17,18,19,20,21,22,23,24,25,27,28,29,30,31,81,83,84,85,86,87,88,3,4,5,6,7,8,9,10,11,89,90,2', '2');
INSERT INTO `oa_org` VALUES ('3', '1', '项目中心', '2', '0', '0', '32,35,36,37,38,39,40,41,42,43,49,50,80,82,12,18,81,3', '2,3');
INSERT INTO `oa_org` VALUES ('4', '1', '数据中心', '2', '0', '0', '13,14,4', '2,4');
INSERT INTO `oa_org` VALUES ('5', '1', '支持中心', '2', '0', '0', '15,27,33,34,5', '2,5');
INSERT INTO `oa_org` VALUES ('6', '1', '管理中心', '2', '0', '0', '47,48,16,17,24,28,84,6', '2,6');
INSERT INTO `oa_org` VALUES ('7', '1', 'QA中心', '2', '2000', '2000', '44,45,46,19,20,21,7', '2,7');
INSERT INTO `oa_org` VALUES ('8', '1', '信息中心', '2', '200', '200', '22,23,8', '2,8');
INSERT INTO `oa_org` VALUES ('9', '1', '技术中心', '2', '0', '0', '25,83,9', '2,9');
INSERT INTO `oa_org` VALUES ('10', '1', '孵化中心', '2', '0', '0', '85,86,87,88,10', '2,10');
INSERT INTO `oa_org` VALUES ('11', '1', '美术中心', '2', '2000', '2000', '63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,56,57,58,59,60,61,62,79,29,30,31,11', '2,11');
INSERT INTO `oa_org` VALUES ('12', '1', '游戏项目部', '3', '0', '0', '32,35,43,49,50,80,82,12', '3,2,12');
INSERT INTO `oa_org` VALUES ('13', '1', '数据分析部', '4', '0', '0', '13', '4,2,13');
INSERT INTO `oa_org` VALUES ('14', '1', '数据平台部', '4', '0', '0', '14', '4,2,14');
INSERT INTO `oa_org` VALUES ('15', '1', '运维部', '5', '0', '0', '33,34,15', '5,2,15');
INSERT INTO `oa_org` VALUES ('16', '1', '人力资源部', '6', '0', '0', '16', '6,2,16');
INSERT INTO `oa_org` VALUES ('17', '1', '行政部', '6', '0', '0', '17', '6,2,17');
INSERT INTO `oa_org` VALUES ('18', '1', '产品项目部', '3', '0', '0', '36,37,38,39,40,41,42,18', '3,2,18');
INSERT INTO `oa_org` VALUES ('19', '1', '测试技术部', '7', '100', '100', '19', '7,2,19');
INSERT INTO `oa_org` VALUES ('20', '1', '测试技术部', '7', '1000', '1000', '20', '7,2,20');
INSERT INTO `oa_org` VALUES ('21', '1', '产品测试部', '7', '0', '0', '44,45,46,21', '7,2,21');
INSERT INTO `oa_org` VALUES ('22', '1', '内部培训部', '8', '0', '0', '22', '8,2,22');
INSERT INTO `oa_org` VALUES ('23', '1', '情报部', '8', '0', '0', '23', '8,2,23');
INSERT INTO `oa_org` VALUES ('24', '1', '公共关系部', '6', '0', '0', '47,48,24', '6,2,24');
INSERT INTO `oa_org` VALUES ('25', '1', '核心技术部', '9', '0', '0', '25', '9,2,25');
INSERT INTO `oa_org` VALUES ('27', '1', '产品运营部', '5', '0', '0', '27', '5,2,27');
INSERT INTO `oa_org` VALUES ('28', '1', '财务部', '6', '0', '0', '28', '6,2,28');
INSERT INTO `oa_org` VALUES ('29', '1', '项目部', '11', '0', '0', '29', '11,2,29');
INSERT INTO `oa_org` VALUES ('30', '1', '美术制作部', '11', '0', '0', '56,57,58,59,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,30', '11,2,30');
INSERT INTO `oa_org` VALUES ('31', '1', '项目创新部', '11', '0', '0', '60,61,79,31', '11,2,31');
INSERT INTO `oa_org` VALUES ('32', '1', '众神传说项目组', '12', '0', '0', '32', '12,3,2,32');
INSERT INTO `oa_org` VALUES ('33', '1', 'IT组', '15', '0', '0', '33', '15,5,2,33');
INSERT INTO `oa_org` VALUES ('34', '1', '产品运维组', '15', '0', '0', '34', '15,5,2,34');
INSERT INTO `oa_org` VALUES ('35', '1', '浪漫星海项目组', '12', '0', '0', '35', '12,3,2,35');
INSERT INTO `oa_org` VALUES ('36', '1', '前端', '18', '0', '0', '36', '18,3,2,36');
INSERT INTO `oa_org` VALUES ('37', '1', '产品', '18', '-4', '0', '37', '18,3,2,37');
INSERT INTO `oa_org` VALUES ('38', '1', 'php', '18', '-21', '0', '38', '18,3,2,38');
INSERT INTO `oa_org` VALUES ('39', '1', 'C++', '18', '0', '0', '39', '18,3,2,39');
INSERT INTO `oa_org` VALUES ('40', '1', 'UI', '18', '0', '0', '40', '18,3,2,40');
INSERT INTO `oa_org` VALUES ('41', '1', 'Android', '18', '0', '0', '41', '18,3,2,41');
INSERT INTO `oa_org` VALUES ('42', '1', 'IOS', '18', '0', '0', '42', '18,3,2,42');
INSERT INTO `oa_org` VALUES ('43', '1', '僵尸世界项目组', '12', '0', '0', '43', '12,3,2,43');
INSERT INTO `oa_org` VALUES ('44', '1', 'QA二组', '21', '0', '0', '44', '21,7,2,44');
INSERT INTO `oa_org` VALUES ('45', '1', 'QA四组', '21', '0', '0', '45', '21,7,2,45');
INSERT INTO `oa_org` VALUES ('46', '1', 'QA一组', '21', '0', '0', '46', '21,7,2,46');
INSERT INTO `oa_org` VALUES ('47', '1', '媒体宣传', '24', '0', '0', '47', '24,6,2,47');
INSERT INTO `oa_org` VALUES ('48', '1', '政府关系', '24', '0', '0', '48', '24,6,2,48');
INSERT INTO `oa_org` VALUES ('49', '1', '权力游戏项目组', '12', '0', '0', '49', '12,3,2,49');
INSERT INTO `oa_org` VALUES ('50', '1', '山口山战记项目组', '12', '0', '0', '50', '12,3,2,50');
INSERT INTO `oa_org` VALUES ('56', '1', '3D制作组', '30', '0', '0', '63,64,56', '30,11,2,56');
INSERT INTO `oa_org` VALUES ('57', '1', 'UI组', '30', '0', '0', '68,57', '30,11,2,57');
INSERT INTO `oa_org` VALUES ('58', '1', '动作设计组', '30', '0', '0', '65,66,67,58', '30,11,2,58');
INSERT INTO `oa_org` VALUES ('59', '1', '特效设计组', '30', '0', '0', '69,70,71,59', '30,11,2,59');
INSERT INTO `oa_org` VALUES ('60', '1', '休闲游戏二组', '31', '0', '0', '60', '31,11,2,60');
INSERT INTO `oa_org` VALUES ('61', '1', '休闲游戏一组', '31', '0', '0', '61', '31,11,2,61');
INSERT INTO `oa_org` VALUES ('62', '1', '原画设计组', '30', '0', '0', '72,73,74,75,76,77,78,62', '30,11,2,62');
INSERT INTO `oa_org` VALUES ('63', '1', '3D二组', '56', '0', '0', '63', '56,30,11,2,63');
INSERT INTO `oa_org` VALUES ('64', '1', '3D一组', '56', '0', '0', '64', '56,30,11,2,64');
INSERT INTO `oa_org` VALUES ('65', '1', '动作二组', '58', '0', '0', '65', '58,30,11,2,65');
INSERT INTO `oa_org` VALUES ('66', '1', '动作三组', '58', '0', '0', '66', '58,30,11,2,66');
INSERT INTO `oa_org` VALUES ('67', '1', '动作一组', '58', '0', '0', '67', '58,30,11,2,67');
INSERT INTO `oa_org` VALUES ('68', '1', '平面设计组', '57', '0', '0', '68', '57,30,11,2,68');
INSERT INTO `oa_org` VALUES ('69', '1', '特效二组', '59', '0', '0', '69', '59,30,11,2,69');
INSERT INTO `oa_org` VALUES ('70', '1', '特效三组', '59', '0', '0', '70', '59,30,11,2,70');
INSERT INTO `oa_org` VALUES ('71', '1', '特效一组', '59', '0', '0', '71', '59,30,11,2,71');
INSERT INTO `oa_org` VALUES ('72', '1', '实习组', '62', '0', '0', '72', '62,30,11,2,72');
INSERT INTO `oa_org` VALUES ('73', '1', '休闲组', '62', '0', '0', '73', '62,30,11,2,73');
INSERT INTO `oa_org` VALUES ('74', '1', '原画二组', '62', '0', '0', '74', '62,30,11,2,74');
INSERT INTO `oa_org` VALUES ('75', '1', '原画三组', '62', '0', '0', '75', '62,30,11,2,75');
INSERT INTO `oa_org` VALUES ('76', '1', '原画四组', '62', '0', '0', '76', '62,30,11,2,76');
INSERT INTO `oa_org` VALUES ('77', '1', '原画五组', '62', '0', '0', '77', '62,30,11,2,77');
INSERT INTO `oa_org` VALUES ('78', '1', '原画一组', '62', '0', '0', '78', '62,30,11,2,78');
INSERT INTO `oa_org` VALUES ('79', '1', '休闲游戏三组', '31', '0', '0', '79', '31,11,2,79');
INSERT INTO `oa_org` VALUES ('80', '1', '项目七组', '12', '0', '0', '80', '12,3,2,80');
INSERT INTO `oa_org` VALUES ('81', '1', '平台项目部', '3', '0', '0', '81', '3,2,81');
INSERT INTO `oa_org` VALUES ('82', '1', '棋牌项目组', '12', '0', '0', '82', '12,3,2,82');
INSERT INTO `oa_org` VALUES ('83', '1', '引擎组', '9', '0', '0', '83', '9,2,83');
INSERT INTO `oa_org` VALUES ('84', '1', '法务部', '6', '0', '0', '84', '6,2,84');
INSERT INTO `oa_org` VALUES ('85', '1', '孵化平台', '10', '0', '0', '85', '10,2,85');
INSERT INTO `oa_org` VALUES ('86', '1', '孵化1组', '10', '0', '0', '86', '10,2,86');
INSERT INTO `oa_org` VALUES ('87', '1', '孵化2组', '10', '0', '0', '87', '10,2,87');
INSERT INTO `oa_org` VALUES ('88', '1', '孵化3组', '10', '0', '0', '88', '10,2,88');
INSERT INTO `oa_org` VALUES ('89', '1', '2', '2', '0', '0', '89', '2,89');
INSERT INTO `oa_org` VALUES ('90', '1', '一二三四五六七八九十三四五六七八九十一', '0', '0', '0', '90', '90');
