/*
 * @package   caboodle
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2013 Enovation Solutions
 */


/**
 * This function returns search string from search box to be used
 * for initial search URL.
 */
var buttonUrl = function() {
    // another script
    var initialsearch = document.getElementById("id_config_search");

    var checked_repos = getAllCheckedRepos();
    
    var student_search_option = getStudentSearchOption();
    
    var number_of_display_items = getNumberOfDisplayItems();
    
    var blacklist = getBlackilstItems();

    return escape(initialsearch.value) + checked_repos + student_search_option + number_of_display_items + blacklist;
}

var getAllCheckedRepos = function() {
    var repositories = document.getElementById('caboodle_repositories').getElementsByTagName("input");
    var ret = '';

    for (var i = 0; i < repositories.length; i++ ) {

        if (repositories[i].attributes.getNamedItem('type') !== 'hidden' && repositories[i].checked === true) {
            ret = ret + '&repo_' + i + '=1';
        }

    }
    
    return ret;
}

var getStudentSearchOption = function() {
    var student_option = document.getElementById('id_config_student_search');
    var choice = student_option.options[student_option.selectedIndex].value;
    var ret = '&student_option=' + choice;
    return ret;
}

var getNumberOfDisplayItems = function() {
    var number_of_display_items = document.getElementById('id_config_search_items_displayed');
    var choice = number_of_display_items[number_of_display_items.selectedIndex].value;
    var ret = '&number_items=' + choice;
    return ret;
}

var getBlackilstItems = function() {
    var textarea = document.getElementById('id_config_blacklist').value;
    
    // see: http://www.webtoolkit.info/javascript-base64.html
    // js script in caboodle: /mod/caboodle/js/base64-encode.js
    var ret = CaboodleBase64.encode(textarea);
    
    return '&blacklisted=' + encodeURIComponent(ret);
}

var in_page_search = function(event, url) {
    if (event.keyCode == 13) {
        event.preventDefault();
        document.location.href = url + buttonUrl();
    }
}
