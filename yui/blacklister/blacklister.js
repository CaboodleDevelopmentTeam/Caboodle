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
        initializer : function() { // 'config' contains the parameter values
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
            var text = textarea.getContent();

            text = text + '\n' + url; // TODO add checking for newlines

            // modify current text
            textarea.setContent(text);
            // hide list element
            e.currentTarget.ancestor().hide();
            // add element to blacklisted list
            var blacklisted = Y.one('ul.caboodle_blacklisted');
            blacklisted.append(formatted_url);

            var mexyk = '';
        },

        unblacklist : function(e) {
            var url = e.currentTarget.ancestor().getElementsByTagName('a').get('href');
            var textarea = Y.one('textarea#id_config_blacklist');
            var text = '';
            var arraytext = textarea.getContent().split('\n');
            var arraytext_length = arraytext.length;

            for (var i = 0; i < arraytext_length; i++ ) {

                if (url[0].toUpperCase() != arraytext[i].toUpperCase()) {
                    text = text + arraytext[i] + '\n';
                }

            }

            textarea.setContent(text);
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
