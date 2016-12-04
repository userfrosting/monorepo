/**
 * ufModal plugin.  Handles 
 *
 *
 * UserFrosting https://www.userfrosting.com
 * @author Alexander Weissman https://alexanderweissman.com
 */

(function( $ )
{    
    /**
     * The plugin namespace, ie for $('.selector').ufModal(options)
     * 
     * Also the id for storing the object state via $('.selector').data()  
     */
    var PLUGIN_NS = 'ufModal';

    var Plugin = function ( target, options )
    {

        this.$T = $(target); 

        /** #### OPTIONS #### */
        this.options= $.extend(
            true,               // deep extend
            {
                sourceUrl : "",
                ajaxParams: {},
                msgTarget : null,
                DEBUG: false
            },
            options
        ); 
        
        this._init( target, options );   
        
        return this; 
    };

    /** #### INITIALISER #### */
    Plugin.prototype._init = function ( target, options )
    { 
        var base = this;
        var $el = $(target);

        // Delete any existing instance of the modal (should have been deleted already anyway)
        if ($el.find(".modal").length) {
            $el.find(".modal").remove();
        }

        // Fetch and render the form
        $.ajax({
          type: "GET",  
          url: base.options.sourceUrl,
          data: base.options.ajaxParams,
          cache: false
        })
        .then(
            // Fetch successful
            function (data) {
                // Append the form as a modal dialog to the body
                $el.append(data);
                $el.find(".modal").modal('show');
                
                // Bind modal to be deleted when closed
                $el.find(".modal").on("hidden.bs.modal", function () {
                    base.destroy();
                });
                
                base.$T.trigger('renderSuccess.ufModal');
                return data;
            },
            // Fetch failed
            function (data) {
                // Error messages
                if ((typeof site !== "undefined") && site.debug.ajax) {
                    base.$T.trigger('renderError.ufModal');
                    document.write(data.responseText);
                    document.close();
                } else {
                    if (base.options.DEBUG) {
                        console.log("Error (" + data.status + "): " + data.responseText );
                    }
                    // Display errors on failure
                    // TODO: ufAlerts widget should have a 'destroy' method
                    if (!base.options.msgTarget.data('ufAlerts')) {
                        base.options.msgTarget.ufAlerts();
                    } else {
                        base.options.msgTarget.ufAlerts('clear');
                    }
                    
                    base.options.msgTarget.ufAlerts('fetch').ufAlerts('render');
                    base.options.msgTarget.on("render.ufAlerts", function () {
                        base.$T.trigger('renderError.ufModal');
                    });
                }
                
                base.destroy();
                
                return data;
            }
        );
    };

    Plugin.prototype.destroy = function () {
        var base = this;
        var $el = base.$T;

        // Delete the plugin object
        base.delete;

        // Remove the modal
        $el.find('.modal').remove();

        // Unbind any bound events
        $el.off('.ufModal');

        // Remove plugin name from data-* attribute
        $el.removeData(PLUGIN_NS);
    };
    
    /**
     * EZ Logging/Warning (technically private but saving an '_' is worth it imo)
     */    
    Plugin.prototype.DLOG = function () 
    {
        if (!this.DEBUG) return;
        for (var i in arguments) {
            console.log( PLUGIN_NS + ': ', arguments[i] );    
        }
    }
    Plugin.prototype.DWARN = function () 
    {
        this.DEBUG && console.warn( arguments );    
    }


/*###################################################################################
 * JQUERY HOOK
 ###################################################################################*/

    /**
     * Generic jQuery plugin instantiation method call logic 
     * 
     * Method options are stored via jQuery's data() method in the relevant element(s)
     * Notice, myActionMethod mustn't start with an underscore (_) as this is used to
     * indicate private methods on the PLUGIN class.   
     */    
    $.fn[ PLUGIN_NS ] = function( methodOrOptions )
    {
        if (!$(this).length) {
            return $(this);
        }
        var instance = $(this).data(PLUGIN_NS);
        
        // CASE: action method (public method on PLUGIN class)        
        if ( instance 
                && methodOrOptions.indexOf('_') != 0 
                && instance[ methodOrOptions ] 
                && typeof( instance[ methodOrOptions ] ) == 'function' ) {
            
            return instance[ methodOrOptions ]( Array.prototype.slice.call( arguments, 1 ) ); 
                
                
        // CASE: argument is options object or empty = initialise            
        } else if ( typeof methodOrOptions === 'object' || ! methodOrOptions ) {

            instance = new Plugin( $(this), methodOrOptions );    // ok to overwrite if this is a re-init
            $(this).data( PLUGIN_NS, instance );
            return $(this);
        
        // CASE: method called before init
        } else if ( !instance ) {
            $.error( 'Plugin must be initialised before using method: ' + methodOrOptions );
        
        // CASE: invalid method
        } else if ( methodOrOptions.indexOf('_') == 0 ) {
            $.error( 'Method ' +  methodOrOptions + ' is private!' );
        } else {
            $.error( 'Method ' +  methodOrOptions + ' does not exist.' );
        }
    };
})(jQuery);
