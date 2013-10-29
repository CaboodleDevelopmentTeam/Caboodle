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
            //e.stopPropagation();
            // move clicked url to blacklist textarea
            var url = e.currentTarget.ancestor().getElementsByTagName('a').get('href');
            var title = e.currentTarget.ancestor().getElementsByTagName('a').getContent();
            // get ul id which will represent repository id
            var resource = e.currentTarget.ancestor().ancestor().getAttribute('id');
            // new url in blacklisted list
            //var cross_url = document.URL;
            var cross = '<img alt="Unexclude" class="smallicon" title="Unexclude" src="' + M.cfg.wwwroot + '/theme/image.php/standard/core/1369325419/i/cross_red_small" />';
            var formatted_url = '<li class="caboodle_blacklisted_item" style="margin: 3px 0;">' + cross + ' <a href="' + url + '" target="_blank">' + title + '</a> (' + url + ')</li>';
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
            //e.stopPropagation();
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
                if (url[0].toLowerCase() !== unescape(arraytext_url.toLowerCase().replace(/&amp;/g, '&'))) {
                    // re-add to text box if not matched
                    text = text + arraytext[i] + '\n';
                } else {
                    // found clicked url, append it to blacklist
                    var repository = Y.one('ul#'+arraytext_resource);
                    // new url in blacklisted list
                    var cross = '<img alt="blacklist" class="smallicon" title="blacklist" src="' + M.cfg.wwwroot + '/theme/image.php/standard/core/1369325419/i/cross_red_small" />';
                    var formatted_url = '<li class="caboodle_blacklisted_item" style="margin: 3px 0;">'+ cross +' <a href="' + arraytext_url + '" target="_blank">' + arraytext_title + '</a> (' + arraytext_url + ')</li>';
                    
                    // append url to a resource unordered list
                    repository.append(formatted_url);

                } // if ... else
                
            }

            // set new content
            textarea.setContent(text);
            // hide list element
            e.currentTarget.ancestor().hide();
            
            // get last added li element
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
