
(function ($) {

    /**
     * Extends JqxDatatables to support responsive design
     * The table will show single column on small device
     * and show full table on large device
     * Note : if want add checkbox column, give datafield column options with "_checkbox"
     * see compose message page when select destination from phonebook
     * modules/Broadcast/Resources/views/compose/form.blade.php
     * @param  options object
     * @param {String} options.title Titel for single column
     */
    $.fn.responsiveTable  = function(options,a,b){
        if(!this || this.length === 0 ){
            return;
        }
        else if(typeof options == 'string'){
            return this.jqxDataTable(options,a,b);
        }
        if(options == undefined){
            options = {};
        }else{
            options = $.extend({}, options);
        }
        /**
         * Assign initial columns and source options to datatables dom.
         * To prevent the second initialize if columns and source option is undefined
         */
        if(this.data('initialize') && this.data('options')){
            options = $.extend(true, {}, this.data('options'), options);
        }

        if(this.data('columns')  &&  !options.columns){
            options.columns = this.data('columns');
        }else if( options.columns){
            this.data('columns', options.columns);
        }

        if(this.data('source') && !options.source ){
            options.source  = this.data('source');
        }else if(options.source ){
            this.data('source', options.source);
        }

        /**
         * Check whether the table has been initialized
         * and bind window resize event for table element
         * add data initialize to table element
         */
        if(!this.data('initialize')){
            var table = this;
            /**
             * Change table to single column when resize's even is fired
             * and screen has widht < 600px
             */
            $(window).on('resize', this,function(e){
                width = e.currentTarget.innerWidth;
                if(!table.data('single') && width < 600){
                    table.data('single', true);
                    table.responsiveTable($.extend(options, { title: options.title } ));
                }else if(table.data('single') && width >= 600){
                    table.data('single', false);
                    table.responsiveTable(options);
                }
            });
            this
                .data('title', options.title)
                .data('options', options)
                .data('initialize',true);
        }

        /**
         * Delete title propery on options object if exist
         */
        if(this.data('title') && options.title){
            options.title = undefined;
        }

        /**
         * Default options for jqxDatatables
         * @type {Object}
         */
        var single          = this.data('single'),
            def             = {
                                enableBrowserSelection : true,
                                theme: 'metro_light',
                                pageable: true,
                                columnsResize : !single,
                                sortable: true,
                                altRows: true,
                                pageSizeOptions: ['10', '20', '50'],
                                pageSize: 10,
                                pagerMode: 'advanced',
                                serverProcessing: true,
                                width: '100%',
                                height: 400,
                                // localization: tableLocalizationObj,
                                ready:function(){
                                    $(window).trigger('resize');//trigger event resize to change size of table following screen size
                                }
            },
            /**
             * Default column option when datatables on mobile view
             * @type {Object}
             */
            singleColumns   = {
                                    text            : this.data('title') || $.trim($('.uk-breadcrumb').text()) || 'Table',
                                    sortable        : false,
                                    draggable       : false,
                                    resizable       : false,
                                    editable        : false,
                                    filterable      : false,
                                    dataField       : null,
                                    align           : 'left',
                                    cellsAlign      : 'left',
                                    width           : '100%',
                                    className       : 'single-column-header',
                                    cellsRenderer   : function(id, column, value, row){
                                        var html        = $('<ul class="uk-list uk-list-line"></ul>');
                                        this.owner.columns.records.forEach(function(item,index){
                                            if(item.text && item.datafield && item.datafield !== '_checkbox'){
                                                if(item.cellsRenderer != "" && item.cellsRenderer ){
                                                    html.append("<li><strong>"+item.text+"</strong> : "+item.cellsRenderer(id,item.datafield,row[item.datafield],row)+"</li>");
                                                }else{
                                                    html.append("<li><strong>"+item.text+"</strong> : "+(row[item.datafield] ? row[item.datafield] : "-")+"</li>");
                                                }
                                            }
                                        });
                                        return html[0].outerHTML;
                                    },
                            },
            columns         = options.columns,
            hasCheckbox     = false,

        /**
         * Do iteration to Hide except checkbox column on mobile view
         */
            columns         = columns.map(
                                function(item, index){
                                    if(item.dataField !== '_checkbox'){
                                        item.hidden = single === true;
                                    }else{
                                        hasCheckbox = true;
                                    }
                                    return item;
                                });
        /**
         * Insert single column to options columns
         * @param  {[boolean]} single
         */
        if(single){
            columns.splice(hasCheckbox ? 1 : 0, 0, singleColumns);
        }
        options.columns     = columns;

        return this.jqxDataTable($.extend({}, def, options));
    }

    /**
     * Extends jqxWindow to support responsive design
     * the window will be adjusted follow the screen size
     * Here the required options for responsiveWindow
     * @param  options object
     * @param {String | Integer} options.width        size of width for window on large size
     * @param {String | Integer} options.height       size of height for window on large size
     * #OPTIONAL
     * see documentaion for more options
     * http://www.jqwidgets.com/jquery-widgets-documentation/documentation/jqxwindow/jquery-window-api.htm
     */
    $.fn.responsiveWindow = function(options, a, b){
        if(!this && this.length === 0 )
            return;
        if(typeof options == 'string'){
            return this.jqxWindow(options, a, b);
        }
        if(options == undefined){
            options = {};
        }

        /**
         * Assign initial width and height options to data attribute for first initialize
         * To prevent the second initialize if width and height options  is undefined
         */
        if(this.data('width') && this.data('height')  && !options.height && !options.width ){
            options.width  = this.data('width');
        }else if( options.width && options.height ){
            this
                .data('width', options.width);
        }

        /**
         * Function to get responsive size follow screen size
         * @return {Object} 
         */
        var responsiveOptions   = function(el){
                width               = $(window).width() < options.width ? $(window).width() : options.width,
                position            = 'center';
                return {
                        position: position,
                        width: width,
                    };
            },
            /**
             * Default options for jqxWindow for responsive style
             * @type {Object}
             */
            defaultOptions      = {
                                resizable: false,
                                minWidth: 300,
                                maxWidth: 1000,
                                minHeight: 50,
                                maxHeight: 1000,
                                autoOpen: false,
                                isModal: true,
                                collapsed:false,
                                showCollapseButton: false,
                                theme: 'metrolight',
                            };

        /**
         * Register event Listener for jqxWindow
         * resize event will re-initialize jqxWindow
         * add event listener to botton with attribute close-window to close jqxWindow
         */
        if(! this.data('initialized')){
            var self  = this;
            $(window).on('resize', function(event) {
                setTimeout(function(){
                    self.jqxWindow($.extend({}, options, responsiveOptions()));
                }, 350);
            });
            self.find('[close-window]').on('click',function(e){
                e.preventDefault();
                self.jqxWindow('close');
            });
            this.data('initialized',true);
        }

        return this.jqxWindow($.extend(true, {}, defaultOptions, options, responsiveOptions()));
    }
}(jQuery));