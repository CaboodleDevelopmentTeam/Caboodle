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
            // move clicked url to blacklist textarea
            var url = e.currentTarget.ancestor().getElementsByTagName('a').get('href');
            var title = e.currentTarget.ancestor().getElementsByTagName('a').getContent();
            // new url in blacklisted list
            var cross = '<img alt="blacklist" class="smallicon" title="blacklist" src="http://gadamowicz/caboodle/theme/image.php/standard/core/1369325419/i/cross_red_small" />';
            var formatted_url = '<li class="caboodle_blacklisted_item" style="margin: 3px 0;">' + cross + '<a href="' + url + '">' + title + '</a> (' + url + ')</li>';
            // textarea id="id_config_blacklist"
            var textarea = Y.one('textarea#id_config_blacklist');
            // get textarea content
            var text = textarea.getContent();

            //text = text + '\n' + url;
            text = text + '\n' + title + '::' + url;

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

            // define on click action
            // we cant use unblacklist method, so this function must be redefined here
            // this is a workaround
            last_img.once('click', function(e){
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
                            
                            var the_text = arraytext[i];
                            var arraytext_title = the_text.replace(/::.*$/, '');
                            var arraytext_url = the_text.replace(/^.*::/, '');
                            
                            if (url[0].toUpperCase() != arraytext_url.toUpperCase()) {
                                text = text + arraytext[i] + '\n';
                            }

                        }
                        var ct = e.currentTarget.ancestor();
                        // set new content
                        textarea.setContent(text);
                        e.currentTarget.ancestor().hide();
            });

        },

        unblacklist : function(e) {
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

                var the_text = arraytext[i];
                var arraytext_title = the_text.replace(/::.*$/, '');
                var arraytext_url = the_text.replace(/^.*::/, '');

                if (url[0].toUpperCase() != arraytext_url.toUpperCase()) {
                    text = text + arraytext[i] + '\n';
                }
                
            }

            // set new content
            textarea.setContent(text);
            // hide list element
            e.currentTarget.ancestor().hide();
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
 * See: edit_form.php lines 54-62
 */
function buttonUrl() {
    // another script
    var initialsearch = document.getElementById("id_config_search");

    return initialsearch.value;
}