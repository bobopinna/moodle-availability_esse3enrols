<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Unit tests for the esse3enrols condition.
 *
 * @package availability_esse3enrols
 * @copyright 2022 Roberto Pinna
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace availability_esse3enrols;

use \core_availability\mock_info;
use \core_availability\tree;
use availability_esse3enrols\condition;
use moodle_exception;

/**
 * Unit tests for the esse3enrols condition.
 *
 * @package   availability_esse3enrols
 * @copyright 2022 Roberto Pinna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \availability_esse3enrols
 */
class condition_test extends \advanced_testcase {

    /**
     * Load required classes.
     */
    public function setUp():void {
        // Load the mock info class so that it can be used.
        global $CFG;
        require_once($CFG->dirroot . '/availability/tests/fixtures/mock_info.php');
    }

    /**
     * Tests constructing and using esse3enrols condition as part of tree.
     * @covers \availability_esse3enrols\condition
     */
    public function test_in_tree() {
        global $DB;
        $this->resetAfterTest();

        // Create course with esse3enrols turned on and a Page.
        set_config('enableavailability', true);
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user1 = $generator->create_user()->id;
        $user2 = $generator->create_user()->id;
        $DB->set_field('user', 'idnumber', '1234567', ['id' => $user1]);
        $DB->set_field('user', 'idnumber', '7654321', ['id' => $user2]);

        $info1 = new mock_info($course, $user1);
        $info2 = new mock_info($course, $user2);

        $arr1 = ['type' => 'esse3enrols', 'idnumbers' => '1234567,0987654'];
        $arr2 = ['type' => 'esse3enrols', 'idnumbers' => '7654321'];
        $tree1 = new \core_availability\tree((object)['op' => '|', 'show' => true, 'c' => [(object)$arr1]]);
        $tree2 = new \core_availability\tree((object)['op' => '|', 'show' => true, 'c' => [(object)$arr2]]);

        // Initial check.
        $this->setAdminUser();
        $this->assertFalse($tree1->check_available(false, $info1, true, null)->is_available());
        $this->assertFalse($tree2->check_available(false, $info2, true, null)->is_available());
        $this->assertTrue($tree1->check_available(false, $info1, true, $user1)->is_available());
        $this->assertTrue($tree1->check_available(false, $info2, true, $user1)->is_available());
        $this->assertFalse($tree1->check_available(false, $info1, true, $user2)->is_available());
        $this->assertFalse($tree1->check_available(false, $info2, true, $user2)->is_available());
        $this->assertFalse($tree2->check_available(false, $info2, true, $user1)->is_available());
        $this->assertFalse($tree2->check_available(false, $info1, true, $user1)->is_available());
        $this->assertTrue($tree2->check_available(false, $info1, true, $user2)->is_available());
        $this->assertTrue($tree2->check_available(false, $info2, true, $user2)->is_available());
        // Change user.
        $this->setuser($user1);
        $this->assertTrue($tree1->check_available(false, $info1, true, $user1)->is_available());
        $this->assertFalse($tree1->check_available(true, $info1, true, $user1)->is_available());
        $this->assertFalse($tree2->check_available(false, $info1, true, $user1)->is_available());
        $this->assertTrue($tree2->check_available(true, $info1, true, $user1)->is_available());
        $this->setuser($user2);
        $this->assertFalse($tree1->check_available(false, $info2, true, $user2)->is_available());
        $this->assertTrue($tree1->check_available(true, $info2, true, $user2)->is_available());
        $this->assertTrue($tree2->check_available(false, $info2, true, $user2)->is_available());
        $this->assertFalse($tree2->check_available(true, $info2, true, $user2)->is_available());
    }

    /**
     * Tests section availability.
     * @covers \availability_esse3enrols\condition
     */
    public function test_sections() {
        global $DB;
        $this->resetAfterTest();
        set_config('enableavailability', true);
        // Create course with esse3enrols turned on and a Page.
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user1 = $generator->create_user()->id;
        $DB->set_field('user', 'idnumber', '1234567', ['id' => $user1]);
        $user2 = $generator->create_user()->id;
        $generator->enrol_user($user1, $course->id);
        $generator->enrol_user($user2, $course->id);
        $cond = '{"op":"|","show":false,"c":[{"type":"esse3enrols","idnumbers":"0987654,1234567"}]}';
        $DB->set_field('course_sections', 'availability', $cond, ['course' => $course->id, 'section' => 0]);
        $cond = '{"op":"|","show":true,"c":[{"type":"esse3enrols","id":"1111111,2222222,3333333"}]}';
        $DB->set_field('course_sections', 'availability', $cond, ['course' => $course->id, 'section' => 1]);
        $cond = '{"op":"|","show":true,"c":[{"type":"esse3enrols","id":"1234567,0987654"}]}';
        $DB->set_field('course_sections', 'availability', $cond, ['course' => $course->id, 'section' => 2]);
        $modinfo1 = get_fast_modinfo($course, $user1);
        $modinfo2 = get_fast_modinfo($course, $user2);
        $this->assertTrue($modinfo1->get_section_info(0)->uservisible);
        $this->assertFalse($modinfo1->get_section_info(1)->uservisible);
        $this->assertFalse($modinfo1->get_section_info(2)->uservisible);
        $this->assertFalse($modinfo2->get_section_info(0)->uservisible);
        $this->assertFalse($modinfo2->get_section_info(1)->uservisible);
        $this->assertFalse($modinfo2->get_section_info(2)->uservisible);
    }

    /**
     * Tests the constructor including error conditions.
     * @covers \availability_esse3enrols\condition
     */
    public function test_constructor() {
        // This works with no parameters.
        $structure = (object)[];
        $esse3enrols = new condition($structure);
        $this->assertNotEmpty($esse3enrols);

        // Invalid ->idnumbers.
        $esse3enrols = null;
        $structure->idnumbers = null;
        try {
            $esse3enrols = new condition($structure);
        } catch (\coding_exception $e) {
            $this->assertStringContainsString('Invalid ->idnumbers for Esse3 enrolments condition', $e->getMessage());
        }
        $structure->idnumbers = "";
        try {
            $esse3enrols = new condition($structure);
        } catch (\coding_exception $e) {
            $this->assertStringContainsString('Invalid ->idnumbers for Esse3 enrolments condition', $e->getMessage());
        }
        $this->assertEquals(null, $esse3enrols);
    }

    /**
     * Tests the save() function.
     * @covers \availability_esse3enrols\condition
     */
    public function test_save() {
        $structure = (object)['idnumbers' => '1234567'];
        $cond = new condition($structure);
        $structure->type = 'esse3enrols';
        $this->assertEqualsCanonicalizing($structure, $cond->save());
        $this->assertEqualsCanonicalizing((object)['type' => 'esse3enrols', 'idnumbers' => '1234567'], $cond->get_json('1234567'));
    }

    /**
     * Tests the get_description and get_standalone_description functions.
     * @covers \availability_esse3enrols\condition
     */
    public function test_get_description() {
        $info = new mock_info();
        $esse3enrols = new condition((object)['type' => 'esse3enrols', 'idnumbers' => '1234567']);
        $desc = $esse3enrols->get_description(true, false, $info);
        $this->assertEquals('The user\'s idnumber is in (1234567)', $desc);
        $desc = $esse3enrols->get_description(true, true, $info);
        $this->assertEquals('The user\'s idnumber is not in (1234567)', $desc);
        $desc = $esse3enrols->get_standalone_description(true, false, $info);
        $this->assertStringContainsString('Not available unless: The user\'s idnumber is in (1234567)', $desc);
        $result = \phpunit_util::call_internal_method($esse3enrols, 'get_debug_string', [], 'availability_esse3enrols\condition');
        $this->assertEquals('1234567', $result);
    }

    /**
     * Tests using esse3enrols condition in front end.
     * @covers \availability_esse3enrols\frontend
     */
    public function test_frontend() {
        global $CFG;
        require_once($CFG->dirroot.'/mod/lesson/locallib.php');
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('enableavailability', true);
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $les = new \lesson($generator->get_plugin_generator('mod_lesson')->create_instance(['course' => $course, 'section' => 0]));
        $user = $generator->create_user();
        $modinfo = get_fast_modinfo($course);
        $cm = $modinfo->get_cm($les->cmid);
        $sections = $modinfo->get_section_info_all();
        $generator->enrol_user($user->id, $course->id);

        $name = 'availability_esse3enrols\frontend';
        $frontend = new \availability_esse3enrols\frontend();
        $this->assertCount(0, \phpunit_util::call_internal_method($frontend, 'get_javascript_init_params', [$course], $name));
    }


    /**
     * Tests using esse3enrols condition in back end.
     * @covers \availability_esse3enrols\condition
     */
    public function test_backend() {
        global $CFG, $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('enableavailability', true);
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $context = \context_course::instance($course->id);
        $user = $generator->create_user();
        $generator->enrol_user($user->id, $course->id);
        $pagegen = $generator->get_plugin_generator('mod_page');
        $restriction = \core_availability\tree::get_root_json([condition::get_json('0987654')]);
        $pagegen->create_instance(['course' => $course, 'availability' => json_encode($restriction)]);
        $restriction = \core_availability\tree::get_root_json([condition::get_json('1234567')]);
        $pagegen->create_instance(['course' => $course, 'availability' => json_encode($restriction)]);
        $restriction = \core_availability\tree::get_root_json([condition::get_json('7654321')]);
        $pagegen->create_instance(['course' => $course, 'availability' => json_encode($restriction)]);
        rebuild_course_cache($course->id, true);
        $mpage = new \moodle_page();
        $mpage->set_url('/course/index.php', ['id' => $course->id]);
        $mpage->set_context($context);
        $format = course_get_format($course);
        $renderer = $mpage->get_renderer('format_topics');
        $branch = (int)$CFG->branch;
        if ($branch > 311) {
            $outputclass = $format->get_output_classname($branch == 311 ? 'course_format' : 'content');
            $output = new $outputclass($format);
            ob_start();
            echo $renderer->render($output);
        } else {
            ob_start();
            echo $renderer->print_multiple_section_page($course, null, null, null, null);
        }
        $out = ob_get_clean();
        $this->assertStringContainsString('Not available unless: The user\'s idnumber is in (0987654)', $out);
        $DB->set_field('user', 'idnumber', '0987654', ['id' => $user->id]);
        $this->setuser($user);
        rebuild_course_cache($course->id, true);
        ob_start();
        if ($branch > 311) {
            echo $renderer->render($output);
        } else {
            echo $renderer->print_multiple_section_page($course, null, null, null, null);
        }
        $out = ob_get_clean();
        $this->assertStringNotContainsString('Not available unless: The user\'s idnumber is in (1111111)', $out);
    }
}
