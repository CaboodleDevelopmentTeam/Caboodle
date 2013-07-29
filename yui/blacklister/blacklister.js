/*
 * @package   caboodle
 * @author    Grzegorz Adamowicz (greg.adamowicz@enovation.ie)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2013 Enovation Solutions
 */


YUI.add('moodle-block_caboodle-blacklister', function(Y) {
    var BLACKLISTER = 'blacklister';
    var BLACKLISTER = function() {
        BLACKLISTER.superclass.constructor.apply(this, arguments);
    };

    Y.extend(BLACKLISTER, Y.Base, {
        initializer : function() {
            // match all blaclist-able items
            var items = Y.all('li.caboodle_blacklister_item img');
            var blacklisted_items = Y.all('li.caboodle_blacklisted_item img');

            // perform blacklist action on click
            items.on('click', this.blacklist);
            blacklisted_items.on('click', this.unblacklist);
        },

        blacklist : function(e) {
            // don't propagate up the DOM tree
            e.stopPropagation();
            // move clicked url to blacklist textarea
            var url = e.currentTarget.ancestor().getElementsByTagName('a').get('href');
            var title = e.currentTarget.ancestor().getElementsByTagName('a').getContent();
            // get ul id which will represent repository id
            var resource = e.currentTarget.ancestor().ancestor().getAttribute('id');
            // new url in blacklisted list
            //var cross_url = document.URL;
            var cross = '<img alt="blacklist" class="smallicon" title="blacklist" src="../theme/image.php/standard/core/1369325419/i/cross_red_small" />';
            var formatted_url = '<li class="caboodle_blacklisted_item" style="margin: 3px 0;">' + cross + '<a href="' + url + '" target="_blank">' + title + '</a> (' + url + ')</li>';
            // textarea id="id_config_blacklist"
            var textarea = Y.one('textarea#id_config_blacklist');
            // get textarea content
            var text = textarea.getContent();

            text = text + '\n' + title + '::' + url + '::' + resource;

            // modify current text
            textarea.setContent(text);
            // hide list element
            e.currentTarget.ancestor().hide();
            // get NodeList for blacklisted ul
            var blacklisted = Y.one('ul.caboodle_blacklisted');
            
            // destroy empty list element, we don't need it
            var empty_list = Y.one('li#blacklist_empty');
            
            if (empty_list != null) {
                empty_list.hide();
            }            

            // add blacklisted item to ul
            blacklisted.append(formatted_url);

            // get added li element
            var last = blacklisted.get('lastChild');
            // select img to be used as on click icon
            var last_img = last.getElementsByTagName('img');

            // define on click action with already defined method
            var unblacklist = BLACKLISTER.prototype.unblacklist;
            last_img.once('click', unblacklist);
            
        },

        unblacklist : function(e) {
            // don't propagate up the DOM tree
            e.stopPropagation();
            // get URL
            var url = e.currentTarget.ancestor().getElementsByTagName('a').get('href');
            // pinpoint of textarea (it is hidden form field)
            var textarea = Y.one('textarea#id_config_blacklist');
            var text = '';
            // get text area content and split it by a newline
            var arraytext = textarea.getContent().split('\n');
            // get lenght of above array
            var arraytext_length = arraytext.length;

            // foreach array element and re-set its content filtering out clicked url
            for (var i = 0; i < arraytext_length; i++ ) {

                var the_text = arraytext[i].split('::');
                var arraytext_title = the_text[0];
                var arraytext_url = the_text[1];
                var arraytext_resource = the_text[2];

                if (url[0].toUpperCase() != arraytext_url.toUpperCase()) {
                    text = text + arraytext[i] + '\n';
                }
                
            }

            // set new content
            textarea.setContent(text);
            // hide list element
            e.currentTarget.ancestor().hide();
            
            var repository = Y.one('ul#'+arraytext_resource);
            // new url in blacklisted list
            var cross = '<img alt="blacklist" class="smallicon" title="blacklist" src="../theme/image.php/standard/core/1369325419/i/cross_red_small" />';
            var formatted_url = '<li class="caboodle_blacklisted_item" style="margin: 3px 0;">'+ cross +'<a href="' + arraytext_url + '" target="_blank">' + arraytext_title + '</a> (' + arraytext_url + ')</li>';
            repository.append(formatted_url);
            
            // get added li element
            var last = repository.get('lastChild');
            // select img to be used as on click icon
            var last_img = last.getElementsByTagName('img');

            // define on click action with already defined method
            var blacklist = BLACKLISTER.prototype.blacklist;
            last_img.once('click', blacklist);

        }

    }, {
        NAME : BLACKLISTER, //module name is something mandatory.

        ATTRS : {
                 aparam : {}
        } // Attributes are the parameters sent when the $PAGE->requires->yui_module calls the module.
          // Here you can declare default values or run functions on the parameter.
          // The param names must be the same as the ones declared
          // in the $PAGE->requires->yui_module call.
    });

    M.block_caboodle = M.block_caboodle || {}; // This line use existing name path if it exists, otherwise create a new one.
                                                 // This is to avoid to overwrite previously loaded module with same name.
    M.block_caboodle.init_blacklister = function() { // 'config' contains the parameter values

        return new BLACKLISTER(); // 'config' contains the parameter values
    };
  }, '@VERSION@', {
      requires:['node', 'base', 'event']
  });



/**
 * This function returns search string from search box to be used
 * for initial search.
 * It should be moved to separate js file
 * See: edit_form.php lines 54-62
 */
function buttonUrl() {
    // another script
    var initialsearch = document.getElementById("id_config_search");

    var checked_repos = getAllCheckedRepos();
    
    var student_search_option = getStudentSearchOption();
    
    var number_of_display_items = getNumberOfDisplayItems();
    
    var blacklist = getBlackilstItems();

    return escape(initialsearch.value) + checked_repos + student_search_option + number_of_display_items + blacklist;
}

function getAllCheckedRepos() {
    var repositories = document.getElementById('caboodle_repositories').getElementsByTagName("input");
    var ret = '';

    for (var i = 0; i < repositories.length; i++ ) {

        if (repositories[i].attributes.getNamedItem('type') !== 'hidden' && repositories[i].checked === true) {
            ret = ret + '&repo_' + i + '=1';
        }

    }
    
    return ret;
}

function getStudentSearchOption() {
    var student_option = document.getElementById('id_config_student_search');
    var choice = student_option.options[student_option.selectedIndex].value;
    var ret = '&student_option=' + choice;
    return ret;
}

function getNumberOfDisplayItems() {
    var number_of_display_items = document.getElementById('id_config_search_items_displayed');
    var choice = number_of_display_items[number_of_display_items.selectedIndex].value;
    var ret = '&number_items=' + choice;
    return ret;
}

function getBlackilstItems() {
    var textarea = document.getElementById('id_config_blacklist').value;
    
    // see: http://www.webtoolkit.info/javascript-base64.html
    // js script in caboodle: /mod/caboodle/js/base64-encode.js
    var ret = CaboodleBase64.encode(textarea);
    
    return '&blacklisted=' + encodeURIComponent(ret);
}

function in_page_search(event, url) {
    if (event.keyCode == 13) {
        event.preventDefault();
        document.location.href = url + buttonUrl();
    }
}