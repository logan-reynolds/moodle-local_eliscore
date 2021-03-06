<?php
/**
 * ELIS(TM): Enterprise Learning Intelligence Suite
 * Copyright (C) 2008-2013 Remote-Learner.net Inc (http://www.remote-learner.net)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    eliscore_etl
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2008-2013 Remote-Learner.net Inc (http://www.remote-learner.net)
 *
 */

require_once(dirname(__FILE__).'/../../../test_config.php');
global $CFG;
require_once($CFG->dirroot.'/local/eliscore/lib/setup.php');
require_once($CFG->libdir.'/upgradelib.php');
require_once(elis::plugin_file('eliscore_etl', 'db/upgrade.php'));
require_once(elis::plugin_file('eliscore_etl', 'etl.php'));
require_once(elis::plugin_file('eliscore_etl', 'lib.php'));

/**
 * Testing the user_activity plugin.
 *
 * @group local_eliscore
 * @group eliscore_etl
 */
class eliscore_etl_testcase extends elis_database_test {
    /**
     * This is a data provider for test_validate_parameter_with_valid_parameters()
     * @return array An array with arrays of data
     */
    public function valid_parameters_provider() {
        $data = array(
                array(1),
                array(22),
                array(333),
                array(1.5),
                array('111'),
                array('1.5'),
        );

        return $data;
    }

    /**
     * Test validating parameters
     * @dataProvider valid_parameters_provider
     * @param int $value Values from the dataprovider
     */
    public function test_validate_parameter_with_valid_parameters($value) {
        $result = validate_parameter($value);
        $this->assertTrue($result);
    }

    /**
     * This is a data provider for test_validate_parameter_with_invalid_parameters()
     * @return array An array with arrays of data
     */
    public function invalid_parameters_provider() {
        $data = array(
                array(0),
                array(-1),
                array(-11),
                array('asdf123'),
                array('-asdf123'),
                array('-11'),
                array('111asdf'),
                array('-111asdf'),
                array('asdf')
        );

        return $data;
    }

    /**
     * Test validating parameters
     * @dataProvider invalid_parameters_provider
     * @param int $value Values from the dataprovider
     */
    public function test_validate_parameter_with_invalid_parameters($value) {
        $result = validate_parameter($value);
        $this->assertFalse($result);
    }

    /**
     * Test converting time period into seconds
     */
    public function test_convert_time_to_seconds() {
        // Assert conversion of time period into seconds
        $period = new stdClass();
        $period->minutes = 1;
        $period->hours = 0;
        $result = convert_time_to_seconds($period);
        $this->assertEquals($result, 60);

        $period->minutes = 0;
        $period->hours = 1;
        $result = convert_time_to_seconds($period);
        $this->assertEquals($result, 3600);

        $period->minutes = 0;
        $period->hours = 1;
        $result = convert_time_to_seconds($period);
        $this->assertEquals($result, 3600);

        $period->minutes = 30;
        $period->hours = 1;
        $result = convert_time_to_seconds($period);
        $this->assertEquals($result, 5400);
    }

    /**
     * Test converting time period with invalid input
     */
    public function test_convert_time_to_seconds_with_invalid_input() {
        // Asert missing properties returns false
        $period = new stdClass();
        $result = convert_time_to_seconds($period);
        $this->assertFalse($result);

        $periodone = new stdClass();
        $periodone->minutes = 0;
        $result = convert_time_to_seconds($periodone);
        $this->assertFalse($result);

        $periodtwo = new stdClass();
        $periodtwo->hours = 0;
        $result = convert_time_to_seconds($periodtwo);
        $this->assertFalse($result);
    }

    /**
     * Test setting the blocked value of ETL scheduled task.
     * @uses $DB
     */
    public function test_set_etl_task_blocked() {
        global $DB;

        $now = time();

        $etlobj = new eliscore_etl_useractivity;

        $etlobj->set_etl_task_blocked($now);
        $blocked = $DB->get_field('local_eliscore_sched_tasks', 'blocked', array('plugin' => 'eliscore_etl'));
        $this->assertEquals($blocked, $now);

        $etlobj->set_etl_task_blocked(0);
        $blocked = $DB->get_field('local_eliscore_sched_tasks', 'blocked', array('plugin' => 'eliscore_etl'));
        $this->assertEquals($blocked, 0);
    }
}
