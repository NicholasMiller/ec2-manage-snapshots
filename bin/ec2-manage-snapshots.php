<?php
/**
 * Manages a rotating snapshot scheme for Amazon's EC2/EBS service
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
 *
 * Based off scripts from:
 * Oren Solomianik
 * ----------------------
 * http://orensol.com/2009/02/12/how-to-delete-those-old-ec2-ebs-snapshots/
 *
 * Erik Dasque
 * ----------------------
 * http://www.thecloudsaga.com/aws-ec2-manage-snapshots/
 */

/**
 * @author Nick Miller <nicholas.j.miller@gmail.com>
 * @version 1.0
 *
 * WARNING USE AT YOU OWN RISK!!!
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

$configFile = APPLICATION_PATH . '/etc/config.ini';
if (!is_file($configFile) || !is_readable($configFile)) {
    $msg = 'The file "' . $configFile . "' does either not exist or is not readable\n\n";
    die($msg);
}

$config = new Zend_Config_Ini(APPLICATION_PATH . '/etc/config.ini');

try {
    $opts = App_Console_Getopt::getInstance();
    $opts->parseAndMergeWithConfig($config);
} catch (Zend_Console_Getopt_Exception $e) {
    echo $e->getMessage() . "\n\n";
    echo $opts->getUsageMessage();
    exit();
}

$ebs = Zend_Service_Amazon_Ec2::factory(
    'ebs',
    $opts->getOption('access-key'),
    $opts->getOption('secret-key')

);
/* @var $ebs Zend_Service_Amazon_Ec2_Ebs */


$prune = new App_Service_Amazon_Ec2_EbsSnapshotPrune($ebs);

try {
    $result = $prune->prune($opts->getOption('volume'), $opts->getOption('dry-run'));
} catch (Exception $e) {
    echo 'There was an error running ' . __FILE__ . "\n" .
         'Error Message: ' . $e->getMessage() . "\n\n";

    echo $opts->getUsageMessage();
    exit();
}

if ($opts->getOption('quiet')) {
    exit();
}



echo $result->printSummary();