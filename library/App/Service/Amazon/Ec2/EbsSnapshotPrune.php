<?php
/**
 * Manages a rotating snapshot scheme for Amazon's EC2/EBS service
 *
 * Keeps the last week of snapshots regardless of their frequency,
 * and after that keeps only the one snapshot from every Sunday.
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
 *
 */
class App_Service_Amazon_Ec2_EbsSnapshotPrune
{
    /**
     * @var Zend_Service_Amazon_Ec2_Ebs
     */
    protected $_ebs;

    /**
     * Class Constructor
     * 
     * @param Zend_Service_Amazon_Ec2_Ebs $ebs EBS Object
     * 
     */
    public function  __construct(Zend_Service_Amazon_Ec2_Ebs $ebs)
    {
        $this->_ebs = $ebs;
    }

    /**
     * Prunes Snapshots for the Given Volume
     *
     * @param string $volumeId
     * @param boolean $dryRun (Optional) Indicates if we should perform a dry run
     * @throws InvalidArgumentException if the volume id is not valid or $weeks is not numeric
     * @return App_Service_Amazon_Ec2_EbsSnapshotPruneResult 
     */
    public function prune($volumeId, $dryRun = false)
    {
        if (!$this->_volumeExists($volumeId)) {
            throw new InvalidArgumentException(
                'The provided "' . $volumeId . '" volume does not exist'
            );
        }

        // Storage mechanism for to-be saved snapshots
        $save = array(
            'month-first-day' => array(), // First day of the month
            'sundays' => array(), // Each sunday for the past four weeks
            'past-week' => array(), // Every backup from the past week
            'only' => array() // Failsafe if there backups to delete, but none to save
        );

        $delete = array();
        $savedAtLeastOne = false;
        foreach ($this->_ebs->describeSnapshot() as $s) {

            if (strcasecmp($s['volumeId'], $volumeId) !== 0) {
                continue;
            }

            $obj = new App_Service_Amazon_Ec2_EbsSnapshot($s);

            if ($obj->monthsElapsed() >= 1 && $obj->isFirstDayOfTheMonth() && !isset($save['month-first-day'][$obj->monthsElapsed()])) {
                $save['month-first-day'][$obj->monthsElapsed()] = $obj;
                $savedAtLeastOne = true;
            } else if ($obj->weeksElapsed() > 0 && $obj->weeksElapsed() < 4 && $obj->isSunday() && !isset($save['sundays'][$obj->weeksElapsed()])) {
                $save['sundays'][$obj->weeksElapsed()] = $obj;
                $savedAtLeastOne = true;
            } else if ($obj->weeksElapsed() == 0) {
                $save['past-week'][] = $obj;
                $savedAtLeastOne = true;
            } else {
                $delete[] = $obj;
            }
        }

        // Make sure we leave at least one backup
        // @todo: make this a config option
        if (!$savedAtLeastOne && !empty($delete)) {
            $newest['time'] = 0;
            $newest['obj'] = null;
            $newest['index'] = null;
            
            foreach ($delete as $k => $d) {
                /* @var $d App_Service_Amazon_Ec2_EbsSnapshot */
                $data = $d->getData();
                if (strtotime($data['startTime']) > $newest['time']) {
                    $newest['obj'] = $d;
                    $newest['index'] = $k;
                    $newest['time'] = strtotime($data['startTime']);
                }
            }

            if (empty($newest['obj'])) {
                throw new RuntimeException(
                    'Sanity check failed. There should be at ' .
                    'least one backup to be saved'
                );
            }

            unset($delete[$newest['index']]);
            // rekey delete
            $delete = array_values($delete);
        }


        $result = new App_Service_Amazon_Ec2_EbsSnapshotPruneResult();
        $result->setDeleted($delete);
        $result->setKept($save);

        if ($dryRun) {
            $result->setDryRun(true);
            return $result;
        }

        foreach ($delete as $d) {
            /* @var $d App_Service_Amazon_Ec2_EbsSnapshot */
            $data = $d->getData();

            if (!array_key_exists('snapshotId', $data)) {
                continue;
            }

            try {
                $this->_ebs->deleteSnapshot($data['snapshotId']);
            } catch (Zend_Service_Amazon_Ec2_Exception $e) {
                if (!strstr($e->getMessage(), 'is currently in use by ami')) {
                    throw $e;
                }

                $result->addWarning($e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Checks if a given Amazon EBS volume exists
     * 
     * @param string $volumeId
     * @return boolean
     */
    protected function _volumeExists($volumeId)
    {
        if (empty($volumeId)) {
            return false;
        }

        try {
            $this->_ebs->describeVolume($volumeId);
            return true;
        } catch (Zend_Service_Amazon_Ec2_Exception $e) {
            return false;
        }
    }
}