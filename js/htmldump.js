/*
 * @package   caboodle
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2013 Enovation Solutions
 */


var htmlDump = function(courseid, html_list) {
    var xmlhttp;

    if (window.XMLHttpRequest) {
        // code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    } else {
        // code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }

    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            window.location.reload();
            // xmlhttp.responseText
        } else if (xmlhttp.readyState == 4 && xmlhttp.status !== 200) {
            alert("Error occured. Try again.\n" + xmlhttp.responseText);
            return false;
        }
    }

    var urltoquery = this.M.cfg.wwwroot + '/blocks/caboodle/ajax/htmldump.php?courseid=' + courseid + '&html_list=' + html_list;

    xmlhttp.open("GET", urltoquery, true);
    xmlhttp.send();
}