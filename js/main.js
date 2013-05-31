/*
 * @package   caboodle
 * @author    Grzegorz Adamowicz (greg.adamowicz@enovation.ie)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2013 Enovation Solutions
 */

/**
 * This function returns search string from search box to be used
 * for initial search.
 * See: edit_form.php lines 54-62
 */
function buttonUrl() {
    // another script
    var initialsearch = document.getElementById("id_config_search");

    return initialsearch.value;
}
