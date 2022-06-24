<?php
/**
 * Hybula Looking Glass
 *
 * Does the actual backend work for executed commands.
 *
 * @copyright 2022 Hybula B.V.
 * @license Mozilla Public License 2.0
 * @version 0.1
 * @since File available since release 0.1
 * @link https://github.com/hybula/lookingglass
 */

declare(strict_types=1);

require __DIR__.'/config.php';
require __DIR__.'/LookingGlass.php';

use Hybula\LookingGlass;

LookingGlass::validateConfig();
LookingGlass::startSession();

if ($_SESSION['TARGET'] && $_SESSION['METHOD'] && isset($_SESSION['BACKEND']) && isset($_SESSION['LINK'])) {
    unset($_SESSION['BACKEND']);
    switch ($_SESSION['METHOD']) {
        case 'ping':
            LookingGlass::ping($_SESSION['TARGET'], $_SESSION['LINK']);
            break;
        case 'ping6':
            LookingGlass::ping6($_SESSION['TARGET'], $_SESSION['LINK']);
            break;
        case 'mtr':
            LookingGlass::mtr($_SESSION['TARGET'], $_SESSION['LINK']);
            break;
        case 'mtr6':
            LookingGlass::mtr6($_SESSION['TARGET'], $_SESSION['LINK']);
            break;
        case 'traceroute':
            LookingGlass::traceroute($_SESSION['TARGET'], $_SESSION['LINK']);
            break;
        case 'traceroute6':
            LookingGlass::traceroute6($_SESSION['TARGET'], $_SESSION['LINK']);
            break;
    }
}
