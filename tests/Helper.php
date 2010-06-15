<?php
/**
 * Unit Test Helper File
 *
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

/**
 * @author Nick Miller <nicholas.j.miller@gmail.com>
 *
 * WARNING : USE AT YOU OWN RISK!!!
 * This application will delete snapshots unless you use the --dry-run option
 */

/**
 * Root application path for the script
 */
define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/..'));

$paths = array(
    get_include_path(),
    APPLICATION_PATH . '/library/'
);

set_include_path(implode(PATH_SEPARATOR, $paths));

require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->registerNamespace('App_');
$loader->registerNamespace('PHPUnit_');
