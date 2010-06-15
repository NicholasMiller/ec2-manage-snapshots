<?php
/**
 * Amazon Region Validator
 * Ensures a provided region is provided by Amazon's EC2 service
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
 * @subpackage Validate
 */
class App_Validate_Ec2Region implements Zend_Validate_Interface
{
    /**
     * Returns if the region is listed by Amazon
     *
     * @todo Make sure the list of regions is correct
     * @param string $value Amazon EC2 Region
     * @return boolean True if the region is valid
     */
    public function isValid($value)
    {
        $allowed = array(
            'us-east-1a', 'us-east-1b',
            'us-east-1c', 'us-east-1d',
            'us-west-1a', 'us-west-1b',
            'eu-west-1a', 'eu-west-1b',
            'ap-southeast-1a', 'ap-southeast-1b'
        );

        return in_array($value, $allowed);
    }

    /**
     * Returns an array of messages that explain why the most recent isValid()
     * call returned false. The array keys are validation failure message identifiers,
     * and the array values are the corresponding human-readable message strings.
     *
     * @return array
     */
    public function getMessages()
    {
        return array('The provided region was not valid');
    }
}

