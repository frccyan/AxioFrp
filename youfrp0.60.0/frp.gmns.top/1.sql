-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2025-07-11 07:31:11
-- 服务器版本： 5.7.44-log
-- PHP 版本： 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `1`
--

-- --------------------------------------------------------

--
-- 表的结构 `balance_logs`
--

CREATE TABLE `balance_logs` (
  `id` int(10) NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户名',
  `amount` decimal(10,2) NOT NULL COMMENT '变动金额',
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '类型：recharge充值 consume消费',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '描述',
  `created_at` int(11) NOT NULL COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `balance_logs`
--

INSERT INTO `balance_logs` (`id`, `username`, `amount`, `type`, `description`, `created_at`) VALUES
(1, 'admin', '100.00', 'recharge', '兑换码充值：A7A8B951D5C4D498', 1751962271),
(2, 'admin', '19.90', 'consume', '购买套餐：黄金VIP套餐', 1751962285),
(3, 'fanke', '100.00', 'recharge', '兑换码充值：00F99D4656D36116', 1751962390),
(4, 'fanke', '19.90', 'consume', '购买套餐：黄金VIP套餐', 1751962393),
(5, 'fanke', '9.92', 'consume', '购买套餐：青铜VIP套餐', 1751962980),
(6, 'admin', '1.00', 'consume', '购买套餐：测试套餐', 1751963276),
(7, 'admin', '9.92', 'consume', '购买套餐：青铜VIP套餐', 1751963417),
(8, 'admin', '9.92', 'consume', '购买套餐：青铜VIP套餐', 1751965263),
(9, 'admin', '39.90', 'consume', '购买套餐：钻石VIP套餐', 1751965283),
(10, 'admin', '9.92', 'consume', '购买套餐：青铜VIP套餐', 1751965518),
(11, 'admin', '1.00', 'consume', '购买套餐：测试套餐', 1751965879),
(12, 'admin', '2000.00', 'recharge', '兑换码充值：B45F6C8286FF3172', 1751965943),
(13, 'admin', '9.92', 'consume', '购买套餐：青铜VIP套餐', 1751966015),
(14, 'admin', '9.92', 'consume', '购买套餐：青铜VIP套餐', 1751966447),
(15, 'admin', '19.90', 'consume', '购买套餐：黄金VIP套餐', 1751967489),
(16, 'luyuanbo', '100.00', 'recharge', '兑换码充值：1F03777C2B6E45B3', 1751970780),
(17, 'luyuanbo', '1.00', 'recharge', '兑换码充值：3CE9A85CD1D4CF34', 1751970851),
(18, 'luyuanbo', '39.90', 'consume', '购买套餐：钻石VIP套餐', 1751970920),
(19, 'admin', '1.00', 'recharge', '兑换码充值：DEA813860ABB9C9B', 1751970925),
(20, 'fanke', '39.90', 'consume', '购买套餐：钻石VIP套餐', 1751971336),
(21, 'fanke', '9.92', 'consume', '购买套餐：青铜VIP套餐', 1751971858),
(22, 'fanke', '9.92', 'consume', '购买套餐：青铜VIP套餐', 1751972505),
(23, 'runckey', '100.00', 'recharge', '兑换码充值：D3E101C44A367918', 1752033179),
(24, 'runckey', '39.90', 'consume', '购买套餐：钻石VIP套餐', 1752033189),
(25, 'fanke', '1.00', 'recharge', '兑换码充值：FF31DE6F170CE251', 1752112572),
(26, 'admin', '1.00', 'recharge', '兑换码充值：297675D26B91F3C4', 1752140511),
(27, 'fanke', '9.92', 'consume', '购买套餐：青铜VIP套餐', 1752140963);

-- --------------------------------------------------------

--
-- 表的结构 `findpass`
--

CREATE TABLE `findpass` (
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `time` bigint(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `findpass`
--

INSERT INTO `findpass` (`username`, `link`, `time`) VALUES
('admin', 'ee94aded8526882dd5ed2c031856649cfdb328c9', 1752185376);

-- --------------------------------------------------------

--
-- 表的结构 `groups`
--

CREATE TABLE `groups` (
  `id` int(10) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `friendly_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `traffic` bigint(255) NOT NULL,
  `proxies` bigint(255) NOT NULL,
  `inbound` bigint(255) NOT NULL,
  `outbound` bigint(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `groups`
--

INSERT INTO `groups` (`id`, `name`, `friendly_name`, `traffic`, `proxies`, `inbound`, `outbound`) VALUES
(1, 'default', '默认组', 1024, 5, 1024, 1024),
(2, 'vip1', '青铜VIP', 10241, 10, 2048, 2048),
(3, 'vip2', '黄金VIP', 20480, 15, 3072, 3072),
(4, 'vip3', '钻石VIP', 40960, 20, 4096, 4096),
(5, 'vip4', '荣耀VIP', 102400, 100, 102400, 102400),
(6, 'VIP5', '荣耀VIP', 102400, 150, 102400, 102400);

-- --------------------------------------------------------

--
-- 表的结构 `invitecode`
--

CREATE TABLE `invitecode` (
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `limits`
--

CREATE TABLE `limits` (
  `id` int(10) NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `inbound` bigint(16) NOT NULL,
  `outbound` bigint(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `nodes`
--

CREATE TABLE `nodes` (
  `id` int(10) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hostname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `port` int(5) NOT NULL,
  `admin_port` int(5) DEFAULT NULL,
  `admin_pass` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `group` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `packages`
--

CREATE TABLE `packages` (
  `id` int(10) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '套餐名称',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT '套餐描述',
  `price` decimal(10,2) NOT NULL COMMENT '套餐价格',
  `duration` int(11) NOT NULL DEFAULT '0' COMMENT '有效期(天)',
  `group_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '对应的用户组名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：0禁用 1启用'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `packages`
--

INSERT INTO `packages` (`id`, `name`, `description`, `price`, `duration`, `group_name`, `status`) VALUES
(1, '青铜VIP套餐', '10G流量，10个隧道', '9.92', 0, 'vip1', 1),
(2, '黄金VIP套餐', '20G流量，15个隧道', '19.90', 0, 'vip2', 1),
(3, '钻石VIP套餐', '40G流量，20个隧道', '39.90', 0, 'vip3', 1),
(4, '测试套餐', '20G流量，15个隧道', '1.00', 2, 'vip4', 1),
(5, '测试套餐2', '22', '1.00', 0, 'VIP5', 0);

-- --------------------------------------------------------

--
-- 表的结构 `package_orders`
--

CREATE TABLE `package_orders` (
  `id` int(10) NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户名',
  `package_id` int(10) NOT NULL COMMENT '套餐ID',
  `order_time` int(11) NOT NULL COMMENT '购买时间',
  `expire_time` int(11) DEFAULT NULL COMMENT '到期时间',
  `price` decimal(10,2) NOT NULL COMMENT '购买价格'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `package_orders`
--

INSERT INTO `package_orders` (`id`, `username`, `package_id`, `order_time`, `expire_time`, `price`) VALUES
(1, 'runckey', 3, 1752033190, 0, '39.90'),
(2, 'fanke', 1, 1752140963, 0, '9.92');

-- --------------------------------------------------------

--
-- 表的结构 `proxies`
--

CREATE TABLE `proxies` (
  `id` int(10) NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `proxy_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `proxy_type` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `local_ip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `local_port` int(5) DEFAULT NULL,
  `use_encryption` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `use_compression` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `domain` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `locations` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `host_header_rewrite` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remote_port` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `sk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `header_X-From-Where` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastupdate` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `node` int(10) NOT NULL,
  `customdomains` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `proxies`
--

INSERT INTO `proxies` (`id`, `username`, `proxy_name`, `proxy_type`, `local_ip`, `local_port`, `use_encryption`, `use_compression`, `domain`, `locations`, `host_header_rewrite`, `remote_port`, `sk`, `header_X-From-Where`, `status`, `lastupdate`, `node`) VALUES
(16, 'vrrutt', 'fnys', 'tcp', '192.168.9.2', 8005, 'true', 'true', '', '', '', '1234', '', '', '0', '1751760814', 1),
(18, 'admin', 'uush', 'http', '192.168.31.43', 80, 'true', 'true', '[\"me.catgo.xx.kg\"]', '', '', '80', '', '', '0', '1751763202', 1),
(19, 'admin', 'ddns80', 'http', '192.168.31.43', 80, 'true', 'true', '[\"youddns.site\"]', '', '', '80', '', '', '0', '1751763619', 1),
(20, 'admin', 'ddns443', 'https', '192.168.31.43', 443, 'true', 'true', '[\"youddns.site\"]', '', '', '443', '', '', '0', '1751763683', 1),
(21, 'admin', 'dns443', 'https', '192.168.31.43', 443, 'true', 'true', '[\"dns.youddns.site\"]', '', '', '443', '', '', '0', '1751763700', 1),
(22, 'admin', 'dns80', 'http', '192.168.31.43', 80, 'true', 'true', '[\"dns.youddns.site\"]', '', '', '80', '', '', '0', '1751763741', 1),
(23, 'admin', 'wwwddns', 'http', '192.168.31.43', 80, 'true', 'true', '[\"www.youddns.site\"]', '', '', '80', '', '', '0', '1751763778', 1),
(24, 'admin', 'wwwddns443', 'https', '192.168.31.43', 443, 'true', 'true', '[\"www.youddns.site\"]', '', '', '443', '', '', '0', '1751763790', 1),
(25, 'admin', 'dnscn443', 'https', '192.168.31.43', 443, 'true', 'true', '[\"dns.cngames.site\"]', '', '', '443', '', '', '0', '1751763821', 1),
(26, 'admin', 'dnscn80', 'http', '192.168.31.43', 80, 'true', 'true', '[\"dns.cngames.site\"]', '', '', '80', '', '', '0', '1751763834', 1),
(27, 'admin', 'cngames80', 'http', '192.168.31.43', 80, 'true', 'true', '[\"cngames.weisuan.top\"]', '', '', '80', '', '', '0', '1751763862', 1),
(29, 'kimi11', 'youfrp80', 'http', '192.168.31.43', 80, 'true', 'true', '[\"youfrp.gmns.top\"]', '', '', '80', '', '', '0', '1751764587', 1),
(30, 'kimi11', 'youfrp443', 'https', '192.168.31.43', 443, 'true', 'true', '[\"youfrp.gmns.top\"]', '', '', '443', '', '', '0', '1751764603', 1),
(35, 'admin', '9090', 'tcp', '192.168.31.43', 80, 'true', 'true', '', '', '', '9090', '', '', '0', '1751782863', 1),
(36, 'admin', 'uussh', 'https', '192.168.31.43', 443, 'true', 'true', '[\"me.catgo.xx.kg\"]', '', '', '443', '', '', '0', '1751787494', 1),
(38, 'admin', 'cngames443', 'https', '192.168.31.43', 443, 'true', 'true', '[\"cngames.weisuan.top\"]', '', '', '443', '', '', '0', '1751808746', 1),
(40, 'admin', 'CN3333', 'tcp', '192.168.31.43', 80, 'true', 'true', '', '', '', '3333', '', '', '0', '1751840433', 3),
(41, 'admin', 'askdj', 'http', '127.0.0.1', 80, 'true', 'true', '[\"cs.cngames.dpdns.org\"]', '', '', '9009', '', '', '0', '1751842167', 1),
(45, 'boring_student', 'dfdsgrdg', 'udp', '127.0.0.1', 5244, 'true', 'true', '[\"1.com\"]', '', '', '55577', '', '', '0', '1752037596', 5),
(46, 'runckey', 'mm8766', 'tcp', '127.0.0.1', 8766, 'true', 'true', '', '', '', '8766', '', '', '0', '1752041333', 1),
(47, 'runckey', 'mm27016', 'tcp', '127.0.0.1', 27016, 'true', 'true', '', '', '', '27016', '', '', '0', '1752041356', 1),
(48, 'runckey', 'mm9700', 'tcp', '127.0.0.1', 9700, 'true', 'true', '', '', '', '9700', '', '', '0', '1752041375', 1),
(49, 'tzy', 'test4564632', 'tcp', '127.0.0.1', 22, 'true', 'true', '', '', '', '22973', '', '', '1', '1752068577', 5),
(50, 'sushao', 'dns', 'http', '127.0.0.1', 80, 'true', 'true', '[\"dns.szh.2.1.225521.xyz\"]', '', '', '38207', '', '', '0', '1752163462', 1);

-- --------------------------------------------------------

--
-- 表的结构 `redeem_codes`
--

CREATE TABLE `redeem_codes` (
  `code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '兑换码',
  `amount` decimal(10,2) NOT NULL COMMENT '金额',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态：0未使用 1已使用',
  `created_at` int(11) NOT NULL COMMENT '创建时间',
  `used_at` int(11) DEFAULT NULL COMMENT '使用时间',
  `used_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '使用者'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `redeem_codes`
--

INSERT INTO `redeem_codes` (`code`, `amount`, `status`, `created_at`, `used_at`, `used_by`) VALUES
('00F99D4656D36116', '100.00', 1, 1751962345, 1751962390, 'fanke'),
('1F03777C2B6E45B3', '100.00', 1, 1751970427, 1751970780, 'luyuanbo'),
('297675D26B91F3C4', '1.00', 1, 1751961533, 1752140510, 'admin'),
('3CE9A85CD1D4CF34', '1.00', 1, 1751961548, 1751970851, 'luyuanbo'),
('445C655BA7748140', '1.00', 0, 1751961521, NULL, NULL),
('4AF3427FADC77655', '10.00', 0, 1752140668, NULL, NULL),
('A7A8B951D5C4D498', '100.00', 1, 1751930884, 1751962271, 'admin'),
('B45F6C8286FF3172', '2000.00', 1, 1751965935, 1751965942, 'admin'),
('C447397D553C58AB', '1.00', 0, 1752140820, NULL, NULL),
('CED4D31B3C9F611B', '1.00', 0, 1751961518, NULL, NULL),
('D3E101C44A367918', '100.00', 1, 1752033012, 1752033179, 'runckey'),
('D9322D6ED7115B23', '1.00', 0, 1751961515, NULL, NULL),
('DEA813860ABB9C9B', '1.00', 1, 1751961548, 1751970925, 'admin'),
('EE48C7BAD9EE3F08', '1.00', 0, 1751933782, NULL, NULL),
('EE958911B5AFDD6F', '1.00', 0, 1751930864, NULL, NULL),
('FB1E1513478342FF', '100.00', 0, 1752140661, NULL, NULL),
('FF31DE6F170CE251', '1.00', 1, 1751961548, 1752112571, 'fanke');

-- --------------------------------------------------------

--
-- 表的结构 `settings`
--

CREATE TABLE `settings` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` longtext COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `settings`
--

INSERT INTO `settings` (`key`, `value`) VALUES
('broadcast', '欢迎使用 德欣 Youfrp内网穿透管理面板\n官方QQ群：\n群号: 766865191 , 1011690081 , 1036050697 , 650510813\n管理员会在满员时不定时清理最久不发言的成员\n添加一个群即可, 添加多个群会被管理拒绝加入\n提示：\n官方 QQ 群需在验证问题处填写访问密钥，由机器人自动审核加群请求并绑定账户\n为了维护良好的交流环境，只有通过实名认证的用户才能加入官方反馈群'),
('helpinfo', '德欣 Youfrp内网穿透帮助：\n官方QQ群号: 766865191 , 1011690081 , 1036050697 , 650510813\n有什么不会的看帮助文档，我就不多说了。\n下面是使用帮助文档\nhttps://doc.natfrp.com/');

-- --------------------------------------------------------

--
-- 表的结构 `sign`
--

CREATE TABLE `sign` (
  `id` int(10) NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `signdate` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `totalsign` bigint(255) DEFAULT NULL,
  `totaltraffic` bigint(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `sign`
--

INSERT INTO `sign` (`id`, `username`, `signdate`, `totalsign`, `totaltraffic`) VALUES
(1, 'admin', '1752185766', 6, 30),
(3, 'kimi11', '1751850848', 2, 7),
(4, 'luyuanbo', '1752158009', 3, 16),
(5, 'scjyy', '1751706532', 1, 10),
(6, 'vrrutt', '1751760629', 1, 3),
(7, 'tzy', '1752150648', 3, 15),
(8, 'yinuo8394', '1751850725', 1, 1),
(9, 'runckey', '1752028716', 1, 8),
(10, 'boring_student', '1752037615', 1, 7),
(11, 'pdyvc', '1752147241', 2, 13),
(12, 'fanke', '1752141062', 1, 7),
(13, 'fanke11', '1752141977', 1, 9),
(14, 'sushao', '1752163627', 1, 9);

-- --------------------------------------------------------

--
-- 表的结构 `todaytraffic`
--

CREATE TABLE `todaytraffic` (
  `user` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `traffic` bigint(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `todaytraffic`
--

INSERT INTO `todaytraffic` (`user`, `traffic`) VALUES
('admin', 297851066),
('kimi11', 3212222),
('runckey', 0),
('tzy', 0);

-- --------------------------------------------------------

--
-- 表的结构 `tokens`
--

CREATE TABLE `tokens` (
  `id` int(10) NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `tokens`
--

INSERT INTO `tokens` (`id`, `username`, `token`, `status`) VALUES
(1, 'admin', '1c95e1d4f44e380a', '0'),
(2, 'kimi11', '433ea04a371ff4e0', '0'),
(3, 'luyuanbo', 'd290f82a081fbe63', '0'),
(4, 'scjyy', '561fe114dc1d10ca', '0'),
(5, 'vrrutt', '5b006e7c77a3b968', '0'),
(6, 'tzy', 'f683ac945e37e53b', '0'),
(7, 'yinuo8394', 'e996a82bbd340a9a', '0'),
(8, 'fanke', 'eea34b8ab94c06fd', '0'),
(9, 'fanke11', '9b5234a95deb0f7c', '0'),
(10, 'runckey', '9481e824f33084e6', '0'),
(11, 'xnyxy-i-love', 'ada6bbed9e158adf', '0'),
(12, 'boring_student', '22a3f4b4f5d45000', '0'),
(13, '1234567890', '334468d42b1685c7', '0'),
(14, 'pdyvc', '2e5791e2d66dc352', '0'),
(15, 'szh', '694231bde4037713', '0'),
(16, 'sushao', '2a7d708084f3a104', '0');

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE `users` (
  `id` int(10) NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `traffic` bigint(20) NOT NULL,
  `proxies` int(10) NOT NULL,
  `group` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `regtime` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `realname` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '真实姓名',
  `idcard` varchar(18) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '身份证号码',
  `sex` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '性别',
  `age` int(3) DEFAULT NULL COMMENT '年龄',
  `birthday` date DEFAULT NULL COMMENT '出生日期',
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '地址'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `traffic`, `proxies`, `group`, `regtime`, `status`, `realname`, `idcard`, `sex`, `age`, `birthday`, `address`) VALUES
(1, 'admin', '$2y$10$fR2al6pQgw0lCGLLUTb/f.OLDZGiPZiqIKvCr483zATQeaX87C9K2', '3123717439@qq.com', 11259000142187000, 5, 'admin', '1744882295', '0', '彭春福', NULL, NULL, NULL, NULL, NULL),
(2, 'kimi11', '$2y$10$XD2Eqcqe/s/c0R3mrcuG8eisoGclak9lBq4Guyy3He.7RQ3.DeQCm', '2014131458@qq.com', 11259000142168000, 5, 'default', '1751704336', '0', NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'luyuanbo', '$2y$10$oWL1t55W4cHfpsMmOEgEbeAtR1kKtzdlCZKqxmwq2FAiXxPiIjl6i', 'luyuanbo79@163.com', 55296, 20, 'vip3', '1751704434', '0', '赵金朋', '411202196209114067', '女', 62, '1962-09-11', '河南省三门峡市湖滨区'),
(4, 'scjyy', '$2y$10$VjDewKBp1Ah/gwBKZPzPOeElJQ7/zxUMWlbvNglKTuGwemdNdPeBK', '5074894@qq.com', 10241, 5, 'default', '1751706307', '0', NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'vrrutt', '$2y$10$RfhFyetc7nsJs9jztRkkmuf4zpRV0noQLC/TBWsF2K3BBzeJX1IHq', 'vrrutt@gmail.com', 10241, 5, 'default', '1751760575', '0', NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'tzy', '$2y$10$DQXbaRO9mmcYEPsrTJhuDuF.8WruSYeERJ0dgtWMWwnpvDbCKvMVq', '1602291339@qq.com', 16384, 5, 'default', '1751849792', '0', NULL, NULL, NULL, NULL, NULL, NULL),
(7, 'yinuo8394', '$2y$10$YgJtLvHJ6AM3JWEc2SGXC.amjFDQtfTZUQ85N/.jE0pnjTBL1ddy2', '1069137617@qq.com', 2048, 5, 'default', '1751850407', '0', NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'fanke', '$2y$10$EZEvs2LKEJdATGgpg35yse/dqLLCjSoobprR.WxgT05pr0bzsNNKq', 'qazwseftgh@gmail.com', 17409, 10, 'vip1', '1751898074', '0', NULL, NULL, NULL, NULL, NULL, NULL),
(9, 'fanke11', '$2y$10$g/80E3k0RXW.PRBS2nfQfOmgllx0jI9pwDjRGDucCx4XKAVaK6viu', 'cvtwjwh938@ahouse.top', 10240, 5, 'default', '1751973394', '0', NULL, NULL, NULL, NULL, NULL, NULL),
(10, 'runckey', '$2y$10$NAEIwkJpxu7NUb647lq.yuyW12dUEKJkg3cpRE340T3dR3bVyhzDa', 'runckey@qq.com', 40960, 20, 'vip3', '1752028674', '0', NULL, NULL, NULL, NULL, NULL, NULL),
(11, 'xnyxy-i-love', '$2y$10$/1dBWm8LBgrTv.YHD.tIC.U2t1ezC.i/tyITzVv4ofg1XIGXU8Fsu', 'i-love-xnyxy@qq.com', 1024, 5, 'default', '1752037233', '0', NULL, NULL, NULL, NULL, NULL, NULL),
(12, 'boring_student', '$2y$10$Dni9.mfwZ1mX0fP7La2gW.McCHM9DGlTibhza0iIvSlSD776q/pYq', 'boring_student@qq.com', 8192, 5, 'default', '1752037402', '0', NULL, NULL, NULL, NULL, NULL, NULL),
(13, '1234567890', '$2y$10$oQd2bYOg7YpZP3AB5zUgSeCwo293l8UEjoXo6RvdJatFTKiViJyRm', '111@1', 1024, 5, 'default', '1752037601', '0', NULL, NULL, NULL, NULL, NULL, NULL),
(14, 'pdyvc', '$2y$10$K8xrt5dRVsePTxUj7CICEOkkj2JfroYr2Cx7Jy927DofuVuV5njTy', '38381498@qq.com', 14336, 5, 'default', '1752058549', '0', NULL, NULL, NULL, NULL, NULL, NULL),
(15, 'szh', '$2y$10$88wT1ijcMiiSZP4CfTuDyOsrMsPq8WyWTv/zEvsJrh.nPgRqyLdWm', '3631569386@qq.com', 1024, 5, 'default', '1752162989', '0', NULL, NULL, NULL, NULL, NULL, NULL),
(16, 'sushao', '$2y$10$K9NeUUvzrMx479mGTYmnhuhLMVtzZVJl8IaHk0FykdVKGvDpeFLEa', '3971843534@qq.com', 10240, 5, 'default', '1752163188', '0', NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `user_balance`
--

CREATE TABLE `user_balance` (
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户名',
  `balance` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户余额'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `user_balance`
--

INSERT INTO `user_balance` (`username`, `balance`) VALUES
('admin', '1970.70'),
('boring_student', '0.00'),
('fanke', '1.52'),
('fanke11', '0.00'),
('kimi11', '0.00'),
('luyuanbo', '61.10'),
('pdyvc', '0.00'),
('runckey', '60.10'),
('sushao', '0.00'),
('tzy', '0.00'),
('yinuo8394', '0.00');

--
-- 转储表的索引
--

--
-- 表的索引 `balance_logs`
--
ALTER TABLE `balance_logs`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `username` (`username`);

--
-- 表的索引 `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `invitecode`
--
ALTER TABLE `invitecode`
  ADD PRIMARY KEY (`code`) USING BTREE;

--
-- 表的索引 `limits`
--
ALTER TABLE `limits`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `nodes`
--
ALTER TABLE `nodes`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `package_orders`
--
ALTER TABLE `package_orders`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `username` (`username`),
  ADD KEY `package_id` (`package_id`);

--
-- 表的索引 `proxies`
--
ALTER TABLE `proxies`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `redeem_codes`
--
ALTER TABLE `redeem_codes`
  ADD PRIMARY KEY (`code`) USING BTREE;

--
-- 表的索引 `sign`
--
ALTER TABLE `sign`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `todaytraffic`
--
ALTER TABLE `todaytraffic`
  ADD PRIMARY KEY (`user`) USING BTREE;

--
-- 表的索引 `tokens`
--
ALTER TABLE `tokens`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- 表的索引 `user_balance`
--
ALTER TABLE `user_balance`
  ADD PRIMARY KEY (`username`) USING BTREE;

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `balance_logs`
--
ALTER TABLE `balance_logs`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- 使用表AUTO_INCREMENT `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 使用表AUTO_INCREMENT `limits`
--
ALTER TABLE `limits`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `nodes`
--
ALTER TABLE `nodes`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `packages`
--
ALTER TABLE `packages`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `package_orders`
--
ALTER TABLE `package_orders`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `proxies`
--
ALTER TABLE `proxies`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- 使用表AUTO_INCREMENT `sign`
--
ALTER TABLE `sign`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- 使用表AUTO_INCREMENT `tokens`
--
ALTER TABLE `tokens`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- 使用表AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
