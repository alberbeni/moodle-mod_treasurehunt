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
 * Define all the backup steps that will be used by the backup_treasurehunt_activity_task
 *
 * @package   mod_treasurehunt
 * @category  backup
 * @copyright 2015 Your Name <your@email.adress>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

/**
 * Define the complete treasurehunt structure for backup, with file and id annotations
 *
 * @package   mod_treasurehunt
 * @category  backup
 * @copyright 2015 Your Name <your@email.adress>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_treasurehunt_activity_structure_step extends backup_activity_structure_step {

    /**
     * Defines the backup structure of the module
     *
     * @return backup_nested_element
     */
    protected function define_structure() {
        global $CFG,$DB;
        // Needed for get_geometry_functions();
        require_once($CFG->dirroot . '/mod/treasurehunt/locallib.php');
        // Get know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define the root element describing the treasurehunt instance.
        $treasurehunt = new backup_nested_element('treasurehunt', array('id'), array(
            'name', 'intro', 'introformat', 'timecreated', 'timemodified', 'playwithoutmoving',
            'groupmode', 'alwaysshowdescription', 'allowattemptsfromdate',
            'cutoffdate', 'grade', 'grademethod', 'gradepenlocation', 'gradepenanswer'));

        $roads = new backup_nested_element('roads');

        $road = new backup_nested_element('road', array('id'), array(
            'name', 'timecreated', 'timemodified', 'groupid', 'groupingid', 'validated'));

        $riddles = new backup_nested_element('riddles');

        $riddle = new backup_nested_element('riddle', array('id'), array(
            'name', 'number', 'description', 'descriptionformat', 'descriptiontrust',
            'timecreated', 'timemodified', 'activitytoend', 'questiontext',
            'questiontextformat', 'questiontexttrust', 'geom'));

        $answers = new backup_nested_element('answers');

        $answer = new backup_nested_element('answer', array('id'), array(
            'answertext', 'answertextformat', 'answertexttrust', 'timecreated',
            'timemodified', 'correct'));

        $attempts = new backup_nested_element('attempts');

        $attempt = new backup_nested_element('attempt', array('id'), array(
            'timecreated', 'userid', 'groupid', 'success',
            'penalty', 'type', 'questionsolved', 'completionsolved',
            'geometrysolved', 'location'));

        // Build the tree
        $treasurehunt->add_child($roads);
        $roads->add_child($road);

        $road->add_child($riddles);
        $riddles->add_child($riddle);

        $riddle->add_child($answers);
        $answers->add_child($answer);

        $riddle->add_child($attempts);
        $attempts->add_child($attempt);


        // Define sources
        $treasurehunt->set_source_table('treasurehunt', array('id' => backup::VAR_ACTIVITYID));

        $road->set_source_table('treasurehunt_roads', array('treasurehuntid' => backup::VAR_PARENTID), 'id ASC');
        $riddle->set_source_table('treasurehunt_riddles', array('roadid' => backup::VAR_PARENTID));
        $answer->set_source_table('treasurehunt_answers', array('riddleid' => backup::VAR_PARENTID), 'id ASC');

        // All the rest of elements only happen if we are including user info
        if ($userinfo) {
            $attempt->set_source_table('treasurehunt_attempts', array('riddleid' => backup::VAR_PARENTID));
        }


        // Define id annotations
        $road->annotate_ids('groups', 'groupid');
        $road->annotate_ids('groupings', 'groupingid');
        $riddle->annotate_ids('course_modules', 'activitytoend');
        $attempt->annotate_ids('user', 'userid');
        $attempt->annotate_ids('groups', 'groupid');

        // Define file annotations
        $treasurehunt->annotate_files('mod_treasurehunt', 'intro', null);
        $riddle->annotate_files('mod_treasurehunt', 'description', 'id');
        $riddle->annotate_files('mod_treasurehunt', 'questiontext', 'id');
        $answer->annotate_files('mod_treasurehunt', 'answertext', 'id');

        // Return the root element (treasurehunt), wrapped into standard activity structure.
        return $this->prepare_activity_structure($treasurehunt);
    }

}
