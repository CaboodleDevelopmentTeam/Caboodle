<?php

/**
 *
 *
 * @package    caboodle
 * @subpackage cli
 * @author     Grzegorz Adamowicz ()
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require_once(dirname(__FILE__) . '/../../../config.php');

final class caboodle_cli {

    public $search_str;
    public $numsearch;

    private $blockid;
    private $config;
    private $content = '';
    private $tmp_table;

    public function __construct($argv) {
        // process commandline options
        $this->process_options($argv);
        
        // get block saved options
        $this->get_block_options();
        
        // temporary table to keep all data
        $this->create_tmp_table();
    }

    public function run() {
        
        $resources = $this->get_resources();

        foreach ($resources as $resourceid => $resource) {

            if ($this->config->resource[$resourceid] == 1) {
                $pid[$resourceid] = pcntl_fork();

                if (!$pid) {
                    $results = $this->perform_search($resourceid, $search_str);
                    $this->save_temp_search($results);
                    die();
                }

            }

        }

        foreach ($resources as $resourceid => $resource) {

            // wait for childs to terminate
            pcntl_waitpid($pid[$resourceid], $status, WUNTRACED);

            if ($this->config->resource[$resourceid] == 1) {

                $this->content .= "<h4>" . $resource->name . "</h4>";

                // get saved search
                //$results = $this->perform_search($resourceid, $search_str);

                $this->content->text .= '<ul class="caboodle_results">';

                if (!empty($results)) {

                    $count = 0;

                    foreach($results as $r => $result) {

                        if ($count < $this->config->search_items_displayed) {
                            $this->content .= '<li class="caboodle_results_item" style="margin: 3px 0;">';
                            $this->content .= '<a href="' . $result['url']  .'" target="_blank">' . $result['title'] . '</a>';
                            $this->content .= "</li>";
                            $count++;
                        }
                    }

                } else {
                    // no results
                    $this->content .=  '<li>'. get_string('nothing_found', 'block_caboodle') . '</li>';
                }

                $this->content .= "</ul>";

            } // if
        } // foreach

    
    } // run

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

        $api = new $api_class($resourceid, $this->instance->id, $numresults);

        $results = $api->search($search_str);

        return $results;
    } // perform_search

    private function save_temp_search($results) {
        global $DB;

        // $this->content;
        $record = new stdClass();
        $record->content = serialize(base64_encode($results));

        $DB->insert_record($this->tmp_table, $record);
    }

    private function create_tmp_table() {
        global $DB;

        $tmp_tbl_name = "mdl_caboodle_temp_" . time();
        $this->tmp_table = $tmp_tbl_name;

        $sql = '';
    }

    private function get_resources() {
        global $DB;

        $resources = $DB->get_records('caboodle_resources', array());

        return $resources;
    } // get_resources

    private function process_options($options_array) {
        // we don't need script name
        unset($options_array[0]);

        if(count($options_array) == 0 || count($options_array) <> 3) {
            echo "\nError: Wrong options\n\n";
            $this->display_help();
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

    public function display_help() {
        echo "\nHelp:\n";
    }
} // caboodle_cli

$cli = new caboodle_cli($argv);

$cli->run();