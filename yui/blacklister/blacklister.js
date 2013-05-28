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
            var blacklisted = Y.all('li.caboodle_blacklisted_item img');
            // perform blacklist action on click
            items.on('click', this.blacklist);
            blacklisted.on('click', this.unblacklist);
        },

        blacklist : function(e) {
            // move clicked url to blacklist textarea
            var url = e.currentTarget.ancestor().getElementsByTagName('a').get('href');
            // new url in blacklisted list
            var formatted_url = '<li class="caboodle_blacklisted_item" style="margin: 3px 0;">New: <a href="' + url + '">' + url + '</a></li>';
            // textarea id="id_config_blacklist"
            var textarea = Y.one('textarea#id_config_blacklist');
            // get textarea content
            var text = textarea.getContent();

            text = text + '\n' + url; // TODO add checking for newlines

            // modify current text
            textarea.setContent(text);
            // hide list element
            e.currentTarget.ancestor().hide();
            // add element to blacklisted list
            var blacklisted = Y.one('ul.caboodle_blacklisted');
            blacklisted.append(formatted_url);
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

                if (url[0].toUpperCase() != arraytext[i].toUpperCase()) {
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
                                // It should be in lower case without space
                                // as YUI use it for name space sometimes.
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
