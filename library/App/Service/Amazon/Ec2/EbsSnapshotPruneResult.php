<?php
/**
 * Encapsulates information regarding the pruning of the snapshots
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
 *
 */
class App_Service_Amazon_Ec2_EbsSnapshotPruneResult
{
    /**
     * @var array
     */
    protected $_deleted = array();

    /**
     * @var array
     */
    protected $_kept = array(
        'sundays' => array(),
        'past-week' => array()
    );

    /**
     * @var boolean
     */
    protected $_dryRun = false;

    /**
     * @var array
     */
    protected $_warnings = array();

    /**
     * Gets any warnings that were recorded
     * @return array
     */
    public function getWarnings()
    {
        return $this->_warnings;
    }

    /**
     * Sets any warnings that occur
     * @param array $warnings
     */
    public function addWarning($warning)
    {
        $this->_warnings[] = $warning;
    }

    /**
     * Returns the snapshots which were deleted
     * @return array
     */
    public function getDeleted()
    {
        return $this->_deleted;
    }

    /**
     * Sets snapshots which where deleted
     * @param array $deleted
     */
    public function setDeleted($deleted)
    {
        $this->_deleted = $deleted;
    }

    /**
     * Returns the snapshots which were not deleted
     * @return array
     */
    public function getKept()
    {
        return $this->_kept;
    }

    /**
     * Sets the snapshots which were not deleted
     * @param array $kept
     */
    public function setKept($kept)
    {
        $this->_kept = $kept;
    }

    /**
     * Returns if the execution was a dry run
     * @return boolean
     */
    public function isDryRun()
    {
        return $this->_dryRun;
    }

    /**
     * Sets if the execution was a dry run
     * @param boolean $dryRun
     */
    public function setDryRun($dryRun)
    {
        $this->_dryRun = (boolean)$dryRun;
    }


    /**
     * Displays a summary of the execution
     */
    public function printSummary()
    {
        echo "\nRun Summary (" . date('m/d/Y H:i:s') . ")\n";
        echo "====================================\n\n";
        echo "Dry Run: " . ($this->isDryRun() ? 'Yes' : 'No') . "\n\n";
        echo "Snapshots Removed: \n";
        
        foreach ($this->getDeleted() as $deleted) {
            /* @var $deleted App_Service_Amazon_Ec2_EbsSnapshot */
            $data = $deleted->getData();
            echo $data['snapshotId'] . ': ' . $deleted->weeksElapsed() . " weeks old\n";
        }

        if (count($this->getWarnings())) {
            echo "\n\nWARNINGS PRESENT:\n";
            echo "====================================\n";
            echo "* " . implode("\n* ", $this->getWarnings());
        }

        echo "\n\nTotal Snapshots Removed: " . count($this->getDeleted()) . "\n\n\n";
    }

}

