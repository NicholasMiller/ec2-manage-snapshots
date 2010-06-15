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
class App_Service_Amazon_Ec2_EbsSnapshotPruneResultTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var App_Service_Amazon_Ec2_EbsSnapshotPruneResult
     */
    protected $_result;

    protected function setUp()
    {
        $this->_result = new App_Service_Amazon_Ec2_EbsSnapshotPruneResult;
    }


    public function testGetSetDeleted()
    {
        $deleted = array(1, 2, 3, 4);

        $this->_result->setDeleted($deleted);
        $this->assertEquals($deleted, $this->_result->getDeleted());
    }


    public function testSetIsDryRun()
    {
        $this->_result->setDryRun(true);
        $this->assertTrue($this->_result->isDryRun());

        $this->_result->setDryRun(false);
        $this->assertFalse($this->_result->isDryRun());
    }

    public function testGetSetKept()
    {
        $kept = array(4, 5, 6);
        $this->_result->setKept($kept);
        $this->assertEquals($kept, $this->_result->getKept());
    }

    public function testGetAndAddWarnings()
    {
        $this->_result->addWarning('Testing');
        $this->_result->addWarning('Testing2');

        $this->assertEquals(array('Testing', 'Testing2'), $this->_result->getWarnings());
    }

}
