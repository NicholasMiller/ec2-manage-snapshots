<?php
/**
 * Wrapper for the EBS snapshot array from Amazon
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
 * @category App
 * @package Library
 * @subpackage Service
 */
class App_Service_Amazon_Ec2_EbsSnapshot
{

    /**
     * @var array
     */
    protected $_data = array();

    /**
     * Class Constructor
     * 
     * @param array $data
     * @throws InvalidArguementException If $data doesn't appear to be valid snapshot information
     */
    public function  __construct(array $data)
    {
        if (!array_key_exists('startTime', $data)) {
            throw new InvalidArgumentException(
                'The data array provided does not appear valid.'
            );
        }
        
        $this->_data = $data;
    }

    /**
     * Returns the number of weeks elapsed since the provided snapshot was taken
     *
     * @return integer Number of weeks elapsed since the snapshot was taken
     */
    public function weeksElapsed()
    {
        $start = strtotime($this->_data['startTime']);
        $now   = time();

        return floor(($now - $start) / 60 / 60 / 24 / 7);
    }

    /**
     * Returns the number of months elapsed since the provided snapshot was taken
     * 
     * @return integer 
     */
    public function monthsElapsed()
    {
        $start = strtotime($this->_data['startTime']);

        $nowMonths = gmdate('Y') + gmdate('n');
        $startMonths = gmdate('Y', $start) + gmdate('n', $start);

        return $nowMonths - $startMonths;
    }


    /**
     * Indicates if the snapshot provided is on a sunday
     *
     * @return boolean
     */
    public function isSunday()
    {
        return date('w', strtotime($this->_data['startTime'])) == 0;
    }

    /**
     * Indicates if the snapshot occured on the first day of the month
     * @return boolean
     */
    public function isFirstDayOfTheMonth()
    {
        return date('j', strtotime($this->_data['startTime'])) == 1;
    }

    /**
     * Returns the array being wrapped
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }
}