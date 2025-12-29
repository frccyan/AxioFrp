<?php
namespace chhcn;

use chhcn;

OB_START();
SESSION_START();

define("ROOT", __DIR__);

include(ROOT . "/configuration.php");
include(ROOT . "/core/Database.php");
include(ROOT . "/core/Regex.php");
include(ROOT . "/core/Utils.php");
include(ROOT . "/core/Settings.php");
include(ROOT . "/core/Smtp.php");

$conn = null;
$db = new chhcn\Database();

include(ROOT . "/core/Pages.php");
include(ROOT . "/core/UserManager.php");
include(ROOT . "/core/NodeManager.php");
include(ROOT . "/core/GroupManager.php");
include(ROOT . "/core/PackageManager.php");
include(ROOT . "/core/ProxyManager.php");
include(ROOT . "/core/BalanceManager.php");
include(ROOT . "/core/RedeemCodeManager.php");
include(ROOT . "/core/VerificationManager.php");
include(ROOT . "/core/PostHandler.php");
include(ROOT . "/core/Router.php");