<?php
/**
 * Extension of Zend's Getopt class
 * Defines the application's acceptable parameters 
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
 * @author Nicholas Miller <nicholas.j.miller@gmail.com>
 * @category App
 * @package Library
 * @subpackage Console
 */
class App_Console_Getopt extends Zend_Console_Getopt
{
    /**
     * Class Constructor
     * Set to protected so that class may only be instatiated via signleton
     * 
     * @param array $rules 
     */
    public function  __construct(array $rules)
    {
        parent::__construct($rules);
    }

    /**
     * Enhanced parsing with script config information
     *
     * @param Zend_Config $config Config object to compare values against
     * @throws Zend_Console_Getopt_Exception if options are not valid
     * @return void
     */
    public function parseAndMergeWithConfig(Zend_Config $config)
    {
        $this->parse();

        if ($this->getOption('help')) {
            echo $this->getUsageMessage();
            exit();
        }

        $errors = array();
        if ($this->getOption('v') == '') {
            $errors[] = 'Error --volume-id|-v cannot be empty';
        }

        if (!$this->getOption('region')) {
            $this->r = $config->ec2->default_region;
        }

        $validator = new App_Validate_Ec2Region();
        if (!$validator->isValid($this->getOption('region'))) {
            $errors[] = 'The region "' . $this->getOption('region') . '" is not valid';
        }

        if (!$this->getOption('access-key')) {
            $this->a = $config->ec2->default_access_key;
        }

        if (!$this->getOption('access-key')) {
            $errors[] = 'You must specify either a default access key or one ' .
                        'as an argument with --access-key|-a';
        }

        if (!$this->getOption('secret-key')) {
            $this->s = $config->ec2->default_secret_access_key;
        }

        if (!$this->getOption('secret-key')) {
            $errors[] = 'You must specify either a default secret key or one ' .
                        'as an argument with --secret-key|-s';
        }

        if (!empty($errors)) {
            throw new Zend_Console_Getopt_Exception(implode("\n", $errors));
        }
    }

    /**
     * Adds a nice little application description to the top of the usage menu
     * @return string
     */
    public function getUsageMessage()
    {
        $str = "ec2-manage-snapshots prunes old volume snapshots from " .
               "your Amazon EC2 account\n\n";
        
        return $str . parent::getUsageMessage() . "\n";
    }


    /**
     * Returns a pre-configured console options class
     *
     * @see Singleton Pattern
     * @staticvar App_Console_Getopt $instance
     * @return App_Console_Getopt
     */
    public static function getInstance()
    {
        static $instance;

        if ($instance instanceof App_Console_Getopt) {
            return $instance;
        }

        $opts = array (
            'volume|v=s'=> 'EC2 Volume ID to prune',
            'region|r=s' => 'EC2 region',
            'dry-run|d' => 'Will not perform any actions, but will output what would otherwise have been completed',
            'access-key|a=s' => 'AWS access key. If not provided the value will be taken from the ../etc/config.ini file',
            'secret-key|s=s'  => 'AWS secret key. If not provided the value will be taken from the ../etc/config.ini file',
            'verbose|t' => 'Display progress to stdout',
            'help|h' => 'Show this help screen',
            'quiet|q' => 'Supress output which is not error related'
        );

        $instance = new App_Console_Getopt($opts);

        return $instance;
    }
}

