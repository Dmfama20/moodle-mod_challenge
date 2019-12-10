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

namespace mod_challenge\external;

use coding_exception;
use dml_exception;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_value;
use invalid_parameter_exception;
use mod_challenge\external\exporter\tournament_question_dto;
use mod_challenge\util;
use moodle_exception;
use restricted_context_exception;

defined('MOODLE_INTERNAL') || die();

class get_tournament_questions extends external_api {

    /**
     * Definition of parameters for {@see get}.
     *
     * @return external_function_parameters
     */
    public static function get_parameters() {
        return new external_function_parameters([
            'coursemoduleid' => new external_value(PARAM_INT, 'course module id'),
            'tournamentid' => new external_value(PARAM_INT, 'tournament id'),
        ]);
    }

    /**
     * Definition of return type for {@see get}.
     *
     * @return external_multiple_structure
     */
    public static function get_returns() {
        return new external_multiple_structure(
            tournament_question_dto::get_read_structure()
        );
    }

    /**
     * Get all questions of a tournament.
     *
     * @param int $coursemoduleid
     * @param int $tournamentid
     *
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws restricted_context_exception
     */
    public static function get($coursemoduleid, $tournamentid) {
        $params = ['coursemoduleid' => $coursemoduleid, 'tournamentid' => $tournamentid];
        self::validate_parameters(self::get_parameters(), $params);

        // load context
        list($course, $coursemodule) = get_course_and_cm_from_cmid($coursemoduleid, 'challenge');
        self::validate_context($coursemodule->context);
        global $PAGE;
        $renderer = $PAGE->get_renderer('core');
        $ctx = $coursemodule->context;
        $game = util::get_game($coursemodule);
        $tournament = util::get_tournament($tournamentid);
        util::validate_tournament($game, $tournament);

        // collect export data
        $result = [];
        $questions = $tournament->get_questions();
        foreach ($questions as $question) {
            $exporter = new tournament_question_dto($question, $game, $ctx);
            $result[] = $exporter->export($renderer);
        }
        return $result;
    }
}
