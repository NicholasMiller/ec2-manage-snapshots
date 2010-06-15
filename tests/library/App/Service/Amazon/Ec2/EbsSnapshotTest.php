<?php
/**
 * Wrapper Test for the EBS snapshot array from Amazon
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

require_once dirname(__FILE__) . '/../../../../../Helper.php';

/**
 * @author Nick Miller <nicholas.j.miller@gmail.com>
 * @category App
 * @package Tests
 * @subpackage Service
 */
class App_Service_Amazon_Ec2_EbsSnapshotTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var App_Service_Amazon_Ec2_EbsSnapshot
     */
    protected $_snapshot;

    /**
     * Filler content
     * @var array
     */
    protected $_data = array(
        'snapshotId' => 'snap-123456',
        'volumeId' => 'vol-123456',
        'status' => 'completed',
        'startTime' => '2010-04-10T04:09:30.000Z',
        'progress' => '100%'
    );


    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->_snapshot = new App_Service_Amazon_Ec2_EbsSnapshot($this->_data);
    }


    /**
     * Make sure that that class returns an exception when bad data is passed
     * @expectedException InvalidArgumentException
     * @return void
     */
    public function testInvalidConstructorArgument()
    {
        $s = new App_Service_Amazon_Ec2_EbsSnapshot(array());
    }


    /**
     * Makes sure that the weeks elapsed is working correctly
     * @return void
     */
    public function testWeeksElapsed()
    {
        $data = $this->_data;
        $data['startTime'] = gmdate('c', strtotime('-2 week'));

        $s = new App_Service_Amazon_Ec2_EbsSnapshot($data);

        $this->assertEquals(2, $s->weeksElapsed());
    }

    /**
     * Makes sure the flooring for weeksElapsed is working
     * @return void
     */
    public function testWeeksElapsedWithIntermediateDay()
    {
        $data = $this->_data;
        $data['startTime'] = gmdate('c', strtotime('-17 day'));

        $s = new App_Service_Amazon_Ec2_EbsSnapshot($data);
        $this->assertEquals(2, $s->weeksElapsed());
    }

    /**
     * Make sure items within one week are returned as zero.
     * This is incredibly important, as if this doesn't work everything will
     * be deleted :(
     *
     * @return void
     */
    public function testZeroWeek()
    {
        $data = $this->_data;
        $data['startTime'] = gmdate('c', strtotime('-6 day'));

        $s = new App_Service_Amazon_Ec2_EbsSnapshot($data);
        $this->assertEquals(0, $s->weeksElapsed());
    }

    /**
     * Test for postivie first day of month result
     * @return void
     */
    public function testIsFirstDayOfTheMonthTrue()
    {
        $data = $this->_data;
        $data['startTime'] = gmdate('c', strtotime('1/1/2010 00:00:00'));

        $s = new App_Service_Amazon_Ec2_EbsSnapshot($data);
        $this->assertEquals(true, $s->isFirstDayOfTheMonth());
    }

    /**
     * Test for postivie first day of month result
     * @return void
     */
    public function testIsFirstDayOfTheMonthFalse()
    {
        $data = $this->_data;
        $data['startTime'] = gmdate('c', strtotime('1/6/2010 00:00:00'));

        $s = new App_Service_Amazon_Ec2_EbsSnapshot($data);
        $this->assertEquals(false, $s->isFirstDayOfTheMonth());
    }

    /**
     * Tests monthsElapsed
     * @return void
     */
    public function testMonthsElapsed()
    {
        $start = strtotime($this->_data['startTime']);

        $nowMonths = gmdate('Y') + gmdate('n');
        $startMonths = gmdate('Y', $start) + gmdate('n', $start);

        $months = $nowMonths - $startMonths;

        $s = new App_Service_Amazon_Ec2_EbsSnapshot($this->_data);
        $this->assertEquals($months, $s->monthsElapsed());
    }

    /**
     * Makes sure sundays never happen on an un-sunday :)
     * @return void
     */
    public function testIsSunday()
    {
        $data = $this->_data;

        $data['startTime'] = gmdate('c', strtotime('next Sunday'));
        $s = new App_Service_Amazon_Ec2_EbsSnapshot($data);
        $this->assertTrue($s->isSunday());

        $data['startTime'] = gmdate('c', strtotime('next Thursday'));
        $s = new App_Service_Amazon_Ec2_EbsSnapshot($data);
        $this->assertFalse($s->isSunday());
    }

    /**
     * Make sure we get the data as we sent it
     * @return void
     */
    public function testGetData()
    {
        $this->assertEquals($this->_data, $this->_snapshot->getData());
    }
}
