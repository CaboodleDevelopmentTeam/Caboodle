<?php

/**
 *
 *
 * @package    caboodle
 * @subpackage cli
 * @author     Grzegorz Adamowicz (greg.adamowicz@enovation.ie)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require_once(dirname(__FILE__) . '/../../../config.php');

final class caboodle_cli {

    public $search_str;
    public $numsearch;

    private $blockid;
    private $config;

    public function __construct($argv) {
        
        // process commandline options
        $this->process_options($argv);
        
        if (strlen($this->search_str) == 0) {
            exit(1);
        }
        
        // get block saved options
        $this->get_block_options();

    }

    public function run() {

        if (!function_exists('pcntl_fork')) {
            // no pcntl, run search one by one
            $this->run_nofork();
        } else {
            // pcntl enabled, run in threads
            $this->run_fork();
        }

        exit(0);
    } // run

    private function run_fork() {
        $resources = $this->get_resources();

        foreach ($resources as $resourceid => $resource) {

            if ($this->config->resource[$resourceid] == 1) {
                $pid[$resourceid] = pcntl_fork();

                if (!$pid[$resourceid]) {
                    $results[$resourceid] = $this->perform_search($resourceid, $this->search_str);
                    // format result and output as JSON
                    echo json_encode($results) . "\n";
                    die();
                }

            }

        }

        foreach ($resources as $resourceid => $resource) {

            // wait for childs to terminate
            pcntl_waitpid($pid[$resourceid], $status, WUNTRACED);

        } // foreach
    }

    private function run_nofork() {
        $resources = $this->get_resources();

        foreach ($resources as $resourceid => $resource) {

            if ($this->config->resource[$resourceid] == 1) {

                $results[$resourceid] = $this->perform_search($resourceid, $this->search_str);
                echo json_encode($results) . "\n";
                unset($results);

            } // if

        } // foreach

    }

    private function perform_search($resourceid, $search_str) {
        global $DB;

        $numresults = $this->numsearch;

        $sql = "SELECT r.name, rt.typeclass FROM {caboodle_resources} r, {caboodle_resource_types} rt
                 WHERE r.type = rt.id
                 AND r.id = ". $resourceid;
        $resource_data = $DB->get_record_sql($sql);

        $api_class_file = dirname(__FILE__) . '/../lib_api/' .$resource_data->typeclass . ".php";
        $api_class = $resource_data->typeclass;

        require_once($api_class_file);

        $api = new $api_class($resourceid, $this->blockid, $numresults);

        $results = $api->search($search_str);
        
        if (empty($results) && !empty($api->lasterror)) {
            $results[$resourceid]['title'] = get_string('usersearch_error', 'block_caboodle', $api->lasterror);
            $results[$resourceid]['url'] = '#';
        }

        return $results;
    } // perform_search

    private function get_resources() {
        global $DB;

        $resources = $DB->get_records('caboodle_resources', array());

        return $resources;
    } // get_resources

    private function process_options($options_array) {
        // we don't need script name
        unset($options_array[0]);

        if(count($options_array) == 0 || count($options_array) <> 3) {
            exit(1);
        }

        foreach ($options_array as $id => $option) {
            switch ($id) {
                case 1:
                    $this->blockid = (int)$option;
                    break;
                case 2:
                    $this->numsearch = (int)$option;
                case 3:
                    $this->search_str = $option;
                    break;
            }
        }

        // @TODO - add checks

    } // process_options

    private function get_block_options() {
        global $DB;

        if (!$block = $DB->get_record('block_instances', array('id' => $this->blockid))) {
            return false;
        }

        $this->config = unserialize(base64_decode($block->configdata));

        return true;
    }

} // caboodle_cli

$cli = new caboodle_cli($argv);

$cli->run();