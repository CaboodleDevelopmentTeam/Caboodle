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
            //module body
            //Y.delegate('click', this.hide, '.caboodle_blacklister_item', this);

            var items = Y.all('li.caboodle_blacklister_item');

            items.on('click', this.hide);
            items.on('click', this.move);

        },

        hide : function(e) {
            // hide blocked url
            e.currentTarget.hide(); // setStyle
            //alert('you clicked ');
        },

        move : function(e) {
            // move clicked url to blacklist textarea
            var url = e.target.getElementsByTagName('a').get('href');
            // textarea id="id_config_blacklist"
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
