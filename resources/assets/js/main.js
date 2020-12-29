$(document).ready(function() {
    /**
     * Listener event onClick on button with id #btn-swap (button menu)
     * This listener will show or hide left menu
     * left menu will show when aside.left has style of left is 0
     * left menu will hide when aside.left has style of left is -250
     * @return void
     */
    $('#btn-swap').on('click', function() {
        if ($('#sidebar').css('left') == '0px') {
            $('#sidebar').animate({
                left: '-210px'
            }, 250);
            $('.section-content,.section-footer').animate({
                left: "0"
            }, 50);
        } else {
            $('#sidebar').animate({
                left: "0"
            }, 300);
            $('.section-content').animate({
                left: '210px'
            }, 50);
            $('.section-footer').animate({
                left: '210px'
            }, 250);
        }
        /**
         * Trigger event resize
         * Tables are not resize automatically if this event is not triggered.
         * Resize event will triggered after 300ms because it wait section page to fully change the size
         */
        setTimeout(function() {
            $(window).trigger('resize');
        }, 250);
    });


    /**
     * Delete Any alert after 5 second
     */
    setTimeout(function() {
        $('.uk-alert.uk-alert-hide').hide('slow');
    }, 5000);
});
/**
 * add the CSRF token to all request headers
 * https://laravel.com/docs/5.5/csrf#csrf-x-csrf-token
 */
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

/**
 * Make the first letter of a string uppercase
 *
 * @return String
 */
String.prototype.capitalize = function() {
    return this.charAt(0).toUpperCase() + this.slice(1);
}

var DashboardPage = function(opt) {
    opt = opt || {};

    this.formatData = function(data) {
        var localData = [];

        if (data.total === 0) {
            return [{ 'status': lang.empty, 'value': 1 }];
        }

        for (var key in data) {
            if (key != 'total') {
                localData.push({ 'status': lang[key], 'value': data[key] });
            }
        }

        return localData;
    }

    this.initLoader = function(type) {
        $('#loader-' + type).jqxLoader({ width: 100, height: 100 });
    }

    this.initChart = function(type, data) {
        var setting = this.getSetting({
            type: type,
            dataSource: this.formatData(data),
            total: data.total,
        });

        $('#chart-' + type.toLowerCase()).jqxChart(setting);
    }

    this.init = function() {
        $.jqx._jqxChart.prototype.colorSchemes.push({ name: 'emptyStatus', colors: ['#477eb6'] });
        $.jqx._jqxChart.prototype.colorSchemes.push({ name: 'messageStatus', colors: ['#76FF03', '#FFEB3B', '#2196F3', '#E57373'] });

        var type = ['daily', 'weekly', 'monthly'];

        for (var i = 0; i < type.length; i++) {
            var time = type[i];
            this.initLoader(type[i]);
            this.getChartData(type[i]);
        }

        this.addSpaceAfterTitle();
    }


    this.getChartData = function(type) {
        var obj = this;
        $('#loader-'+type).jqxLoader('open');
        return $.ajax({
                url: '',
                type: 'GET',
                dataType: 'json',
                data: { summary: type },
            })
            .done(function(data) {
                obj.initChart(type, data);
            })
            .fail(function() {
                obj.initChart(type, { total: 0 });
            })
            .always(function() {
                $('#loader-'+type).jqxLoader('close');
            });
    }

    this.addSpaceAfterTitle = function() {
        var dom = $('.jqx-chart-title-description');
        var newY = parseInt(dom.attr('y')) + 3;
        dom.attr('y', newY);
    }

    this.getSetting = function(options) {
        var total = options.total ? options.total.toLocaleString() : 0;
        return {
            title: lang[options.type],
            description: "Total: " + total + " SMS",
            enableAnimations: true,
            showLegend: true,
            showBorderLine: false,
            legendPosition: {
                left: 0,
                top: 0,
                width: 0,
                height: 0
            },
            padding: {
                left: 5,
                top: 5,
                right: 5,
                bottom: 0
            },
            titlePadding: {
                left: 0,
                top: 10,
                right: 0,
                bottom: 10
            },
            source: options.dataSource,
            colorScheme: options.total > 0 ? 'messageStatus' : 'emptyStatus',
            seriesGroups: [{
                type: 'donut',
                showLabels: options.total > 0,
                showToolTips: false,
                series: [{
                    dataField: 'value',
                    displayText: 'status',
                    useGradientColors: false,
                    labelRadius: 55,
                    initialAngle: 15,
                    radius: 75,
                    innerRadius: 30,
                    formatSettings: {
                        thousandsSeparator: ',',
                    },
                }]
            }]
        };
    }
}


/**
 * User Module
 */
var UserPage = function(opt) {
    opt = opt || {};
    var idTable = 'table-user';
    var tableUrl = opt.tableUrl;
    var editUrl = opt.editUrl;
    var deleteWindowButtonId = opt.deleteWindowButtonId || 'window-button-delete';
    var closeWindowButtonId = opt.closeWindowButtonId || 'window-button-close';
    var canDelete = opt.canDelete || false;
    var canEdit = opt.canEdit || false;
    var deleteButtonClass = 'button-delete';
    var searchButtonId = 'search-button';
    var clearButtonId = 'search-clear';
    this.dataSource = null;
    /**
     * Table User
     */
    this.table = function() {
        var table = $('#' + idTable);
        if (table.length == 0) {
            return;
        }
        return table.responsiveTable(
            this.tableOptions()
        );
    };
    this.tableOptions = function() {
        return {
            title: "Users",
            source: this.getDataSource(),
            height: 'auto',
            enableHover: false,
            columns: [{
                text: lang.name,
                dataField: 'name',
                align: 'left',
                minWidth: 150,
            }, {
                text: lang.email,
                dataField: 'email',
                minWidth: 150,
                align: 'left',
            }, {
                text: lang.company,
                dataField: 'company_name',
                minWidth: 150,
                width: '10%',
                align: 'left',
                sortable: false,
            }, {
                text: lang.active,
                dataField: 'active',
                minWidth: 50,
                width: '10%',
                align: 'left',
                cellsRenderer: function(row, column, value, rowData) {
                    var className = value == 1 ? 'uk-badge-info' : 'uk-badge-danger';
                    var text = value == 1 ? 'active' : 'inactive';
                    return '<div class="uk-badge ' + className + '">' +
                        text +
                        '</div>';
                }
            }, {
                text: lang.created_at,
                dataField: 'created_at',
                minWidth: 150,
                align: 'left',
                width: '10%'
            }, {
                text: lang.action,
                dataField: 'ad_user_id',
                minWidth: 100,
                width: '10%',
                align: 'center',
                cellsAlign: 'center',
                sortable: false,
                cellsRenderer: function(row, column, value, rowData) {
                    var buttons = "";
                    if (canEdit) {
                        buttons += '<a href="' + editUrl + '/' + value + '" data-uk-tooltip title="' + lang.edit + '" class="button-edit uk-icon-hover uk-icon-pencil-square-o uk-margin-small-right uk-icon-small" data-id=' + value + '></a>';
                    }
                    if (canDelete) {
                        buttons += '<a href="#!" class="' + deleteButtonClass + ' uk-icon-hover uk-icon-trash uk-icon-small" data-uk-tooltip title="' + lang.delete + '" data-id=' + value + '></a>';
                    }
                    return '<div class="table-actions">' + buttons + '</div>';
                }
            }]
        };
    };
    this.getDataSource = function() {
        var source = {
            dataType: 'json',
            root: 'data',
            url: tableUrl,
            dataFields: [{
                name: 'ad_user_id',
                type: 'string'
            }, {
                name: 'name',
                type: 'string'
            }, {
                name: 'email',
                type: 'string'
            }, {
                name: 'created_at',
                type: 'string'
            }, {
                name: 'active',
                type: 'string'
            }, {
                name: 'company_name',
                type: 'string'
            }, ],
            beforeprocessing: function(data) {
                this.totalrecords = data.total;
            }
        };

        var setting = {
            formatData: this.formatDataTable.bind(this)
        };

        return this.dataSource = new $.jqx.dataAdapter(source, setting);
    }

    this.formatDataTable = function(data) {
        return $.extend({}, data, this.searchData());
    }

    this.searchData = function() {
        var data = $('#search-panel :input').serializeArray();
        var result = {};
        data.filter(function(item) {
            return item.value != "";
        }).forEach(function(item) {
            result[item.name] = item.value;
        });

        return result;
    }

    this.onClickButtonDelete = function(e) {
        var dataId = e.currentTarget.dataset.id;
        this.deleteWindow.openWindow(dataId);
    }

    this.onClickSearchButton = function(e) {
        this.dataSource.dataBind();
    }

    this.onClickClearButton = function(e) {
        $('#search-panel :input').each(function() {
            $(this).val('');
        });
        this.dataSource.dataBind();
    }

    //Register event with event listener
    this.bindEvent = function() {
        $(document)
            .on('click', '.' + deleteButtonClass, this.onClickButtonDelete.bind(this))
            .on('click', '#' + searchButtonId, this.onClickSearchButton.bind(this))
            .on('click', '#' + clearButtonId, this.onClickClearButton.bind(this))
    }
    this.initComponent = function() {
        //Register Delete window
        var windowOpt = $.extend({}, opt, {
            dataSource: this.dataSource
        });
        this.deleteWindow = new DeleteWindow(windowOpt);
        this.deleteWindow.init();
        this.deleteWindow.bindEvent();
    }
}

var UserForm = function(opt) {
    var clientUrl = opt.clientUrl || location.href;
    var apiUserUrl = opt.apiUserUrl || location.href;
    var roleUrl = opt.roleUrl || location.href;
    var clientInputId = 'input-client_id';
    var activeInputId = 'input-active';
    var apiUserInputId = 'input-api_user';
    var passwordInputClass = 'input-password';
    var avatarInputId = 'input-avatar';
    var rolesInputId = 'input-roles';
    var checkAllButtonId = 'button-checkAll';
    var uncheckAllButtonId = 'button-uncheckAll';
    /**
     * Client/Company Input
     */
    this.clientInput = function(value) {
        var input = $("#" + clientInputId);

        if (input.length === 0)
            return;

        var options = this.getClientOptions();
        return input.jqxComboBox(options)
            .on('bindingComplete', function() {
                if (value) {
                    var array = value.split(',');
                    for (var i = 0; i < array.length; i++) {
                        input.jqxComboBox('val', array[i]);
                    }
                }
            });
    };

    this.getClientOptions = function() {
        return {
            source: this.getClientSource(),
            displayMember: 'company_name',
            valueMember: 'client_id',
            width: '100%',
            height: 28,
            dropDownHeight: 150
        };
    }

    this.getClientSource = function() {
        var source = {
            dataType: 'json',
            root: '',
            url: clientUrl,
            dataFields: [{
                name: 'client_id',
                type: 'string'
            }, {
                name: 'company_name',
                type: 'string'
            }]
        };
        return this.clientSource = new $.jqx.dataAdapter(source);
    }

    this.getClientInputValue = function() {
        return $("#" + clientInputId).val() || 0;
    }

    this.onSelectClientInput = function(e) {
        if (this.apiUserSource) {
            this.apiUserSource.dataBind();
        } else {
            this.apiUserInput();
        }
    }
    /**
     * Active Input
     */
    this.activeInput = function(value) {
        var dom = $("#" + activeInputId);

        if (dom.length === 0)
            return;

        var isChecked = value == 0 ? 0 : 1;
        var options = {
            theme: 'metro_light',
            width: '65',
            height: '27',
            onLabel: '<span class="uk-icon-check"></span>',
            offLabel: '<span class="uk-icon-close"></span>',
            checked: isChecked
        };

        $('#input-active-hidden').val(isChecked);

        dom.jqxSwitchButton(options)
            .on('change', function(e) {
                $('#input-active-hidden').val($(this).val() ? 1 : 0);
            });
    }
    /**
     * Password Input
     */
    this.passwordInput = function() {
        return $("." + passwordInputClass).jqxPasswordInput();
    }
    /**
     * API User Input
     */
    this.formatDataApiUserSource = function(data) {
        var clientId = this.getClientInputValue();
        return $.extend({}, data, { client_id: clientId });
    }

    this.getApiUserSource = function() {
        var source = {
            datatype: "json",
            datafields: [{
                name: 'user_id'
            }, {
                name: 'user_name'
            }],
            id: 'user_id',
            url: apiUserUrl,
            formatData: this.formatDataApiUserSource.bind(this),
            loadError: function(jqXHR, status, error) {}
        };
        return this.apiUserSource = new $.jqx.dataAdapter(source);
    }

    this.apiUserInput = function(value) {
        var input = $("#" + apiUserInputId);

        if (input.length === 0)
            return;

        var options = {
            checkboxes: true,
            width: '100%',
            height: 140,
            valueMember: 'user_id',
            displayMember: 'user_name',
            enableHover: false,
            filterable: true,
            filterPlaceHolder: 'search',
            source: this.getApiUserSource()
        };

        input
            .jqxListBox(options)
            .on('bindingComplete', function() {
                if (value) {
                    var array = value.split(',');
                    for (var i = 0; i < array.length; i++) {
                        $("#" + apiUserInputId).jqxListBox('checkItem', array[i]);
                    }
                }
            })
            .find('#filter' + apiUserInputId)
            .css('position', 'initial');

        $('#' + clientInputId).on('select', this.onSelectClientInput.bind(this))
    }

    this.onClickCheckAllApiUsers = function(e) {
        $("#" + apiUserInputId).jqxListBox('checkAll');
    }

    this.onClickUncheckAllApiUsers = function(e) {
        $("#" + apiUserInputId).jqxListBox('uncheckAll');
    }

    /**
     * Avatar Input
     */
    this.onChangeAvatarInput = function(e) {
        var files = e.currentTarget.files || [];
        var fileName = $(e.currentTarget).siblings('.file-name');
        if (files.length > 0) {
            if (files[0].size / 1000 >= 500) {
                return;
            }
            fileName.html(files[0].name);
            this.readFileUpload(files[0]);
        } else {
            fileName.html('No file chosen');
        }
    }

    this.readFileUpload = function(file) {
        var reader = new FileReader();
        var img = $('#image-avatar');

        reader.onloadend = function() {
            img.attr('src', reader.result);
        }

        reader.readAsDataURL(file);
    }
    /**
     * Roles Input
     */
    this.roleInput = function(value) {
        var input = $("#" + rolesInputId);

        if (input.length === 0)
            return;

        var source = this.getRoleSource();
        var options = {
            source: source,
            displayMember: 'role_name',
            valueMember: 'role_id',
            width: '100%',
            multiSelect: true,
            autoOpen: false,
            dropDownHeight: 100
        };
        input.jqxComboBox(options)
            .on('bindingComplete', function() {
                if (value) {
                    var array = value.split(',');
                    for (var i = 0; i < array.length; i++) {
                        input.jqxComboBox('val', array[i]);
                    }
                }
            });
    };

    this.getRoleSource = function() {
        var source = {
            dataType: 'json',
            root: '',
            url: roleUrl,
            dataFields: [{
                name: 'role_name',
                type: 'string'
            }, {
                name: 'role_id',
                type: 'string'
            }]
        };
        return this.roleSource = new $.jqx.dataAdapter(source);
    }
    /**
     * Bind Event
     */
    this.bindEvent = function() {
        $(document)
            .on('change', '#' + avatarInputId, this.onChangeAvatarInput.bind(this))
            .on('click', '#' + checkAllButtonId, this.onClickCheckAllApiUsers)
            .on('click', '#' + uncheckAllButtonId, this.onClickUncheckAllApiUsers)
    }
}

/**
 * Report Module
 */
var ReportPage = function(opt) {
    opt = opt || {};
    var idTable = 'table-report';
    var tableUrl = opt.tableUrl;
    var cancelUrl = opt.cancelUrl;
    var processingUrl = opt.processingUrl;
    var regenerateUrl = opt.regenerateUrl;
    var deleteWindowButtonId = opt.deleteWindowButtonId || 'window-button-delete';
    var closeWindowButtonId = opt.closeWindowButtonId || 'window-button-close';
    var canDelete = opt.canDelete || false;
    var canDownload = opt.canDownload || false;
    var deleteButtonClass = 'button-delete';
    var searchButtonId = 'search-button';
    var clearButtonId = 'search-clear';
    this.dataSource = null;
    var processing = false;
    var intervalValue = 0;
    var local = opt.local || {};

    /**
     * Table User
     */
    this.table = function() {
        var table = $('#' + idTable);
        if (table.length == 0) {
            return;
        }
        return table.responsiveTable(
            this.tableOptions()
        );
    };
    this.tableOptions = function() {
        return {
            title: "Reports",
            source: this.getDataSource(),
            height: 'auto',
            enableHover: false,
            rendered: this.handleTableRendered.bind(this),
            columns: [{
                    text: lang.name,
                    dataField: 'report_name',
                    // width: '15%',
                    align: 'center',
                    cellsAlign: 'center',
                    minWidth: 150,
                },
                {
                    text: lang.created_by,
                    dataField: 'api_user_dashboard',
                    minWidth: 150,
                    width: '10%',
                    align: 'center',
                    cellsAlign: 'center',
                    sortable: false,
                    cellsRenderer: function(row, column, value, rowData) {
                        return value ? value.name : '';
                    }
                },
                {
                    text: lang.start_date,
                    dataField: 'start_date',
                    minWidth: 50,
                    width: '13%',
                    align: 'center',
                    cellsAlign: 'center',
                },
                {
                    text: lang.end_date,
                    dataField: 'end_date',
                    minWidth: 50,
                    width: '13%',
                    align: 'center',
                },
                {
                    text: lang.status,
                    dataField: 'message_status',
                    sortable: false,
                    minWidth: 50,
                    width: '15%',
                    align: 'center',
                    cellsAlign: 'center',
                    cellsRenderer: function(row, column, value, rowData) {
                        return value ? value.split(',').map(function(item) {
                            return item.replace(/^./, item[0].toUpperCase());
                        }).join(', ') : '';
                    }
                },
                {
                    text: lang.file_type,
                    dataField: 'file_type',
                    minWidth: 50,
                    width: '7%',
                    align: 'center',
                    cellsAlign: 'center',
                },
                {
                    text: lang.generate_at,
                    dataField: 'generated_at',
                    minWidth: 150,
                    width: '10%',
                    align: 'center',
                    cellsAlign: 'center',
                    cellClassName: function(row, column, value, data) {
                        if (data.generate_status === 0) {
                            return 'status-report-' + data.report_id;
                        }

                        return '';
                    },
                    cellsRenderer: function(row, column, value, rowData) {
                        if (rowData.generate_status === 1) {
                            return lang.processing;
                        }

                        if (rowData.generate_status === 0) {
                            return lang.queue;
                        }

                        if (rowData.generate_status === 3) {
                            return lang.failed;
                        }

                        if (rowData.generate_status === 4) {
                            return lang.canceled;
                        }

                        return value;
                    }
                },
                {
                    text: lang.action,
                    dataField: 'report_id',
                    minWidth: 100,
                    width: '10%',
                    align: 'center',
                    cellsAlign: 'center',
                    sortable: false,
                    cellsRenderer: this.renderActionColumn.bind(this)
                }
            ]
        };
    };

    this.handleTableRendered = function() {
        var data = this.dataSource.getrecords();

        if (!Array.isArray(data)) {
            data = [];
        }

        var inProgress = data.filter(function(item) {
            return item.generate_status === 1 || item.generate_status === 0;
        });

        if (inProgress.length > 0 && processing === false) {
            this.processingReport();
        } else if (inProgress.length === 0 && processing === true) {
            this.stopProcessingReport();
        }
    }

    this.getProcessingReport = function() {
            $.ajax({
                url: processingUrl,
                type: 'GET',
                dataType: 'json',
            })
            .done(this.updateProcessingProgress.bind(this))
            .fail(function() {
                this.stopProcessingReport();
            });
    }

    this.updateProcessingProgress = function(data) {
        if (data.length === 0 && processing=== true) {
            this.stopProcessingReport();
        }

        for (var i = 0; i < data.length; i++) {
            if ($('#progress-report-' + data[i].report_id).is(':visible')) {
                $('#progress-report-' + data[i].report_id).html(this.renderProgressBar(data[i].percentage, data[i].report_id));
                $('.status-report-' + data[i].report_id).html(lang.processing);
            }
        }

        if ($("div[id*='progress-report']").length > data.length) {
            this.dataSource.dataBind();
            // $('#' + idTable).jqxDataTable('refresh');
        }
    }

    this.renderProgressBar = function(percentage, report_id) {
        var value = parseInt(parseFloat(percentage) * 100);

        return "<div class='uk-progress uk-progress-success uk-progress-striped uk-active uk-margin-bottom-remove'>" +
                "<div class='uk-progress-bar' style='width: " + value + "%;'>" +value + "%</div>" +
                "<a href= '#' class='uk-close uk-float-right cancel-report uk-text-danger' data-id='"+ report_id +"'></a>" +
                "</div>";
    }

    this.handleCancelGenerateReport = function(event) {
        var id = event.currentTarget.dataset.id;

        if (!id) return;

        this.cancelWindow.openWindow(id);
    }

    this.requestCancelReport = function(id) {
        return $.ajax({
            url: cancelUrl,
            type: 'GET',
            data: {report_id: id},
        });
    }

    this.processingReport = function() {
        if (processing === false) {
            intervalValue = setInterval(this.getProcessingReport.bind(this), 3000);
            processing = true
        }
    }

    this.stopProcessingReport = function() {
        clearInterval(intervalValue);
        intervalValue = 0;
        processing = false;
    }

    this.renderRefreshButton = function(value) {
        return '<a href="' + regenerateUrl + '/' + value + '" class="uk-margin-small-right uk-icon-hover uk-icon-refresh uk-icon-small" data-uk-tooltip title="Generate" data-id=' + value + '></a>' ;
    }

    this.renderProgressContainer = function(reportId, child) {
        return '<div id="progress-report-'+ reportId + '">' + child + '</div>';
    }

    this.renderActionColumn = function(row, column, value, rowData) {
        var buttons = "";

        if (rowData.generate_status == 1) {
            return this.renderProgressContainer(rowData.report_id, this.renderProgressBar(rowData.percentage, rowData.report_id));
        }

        if (canDownload && rowData.generate_status == 2) {
            buttons += '<a href="' + rowData.download_url + '" class="uk-margin-small-right uk-icon-hover uk-icon-download uk-icon-small"  data-uk-tooltip title="Download"  data-id=' + value + '></a>';
        }

        if (canDelete) {
            buttons += '<a href="#!" class="' + deleteButtonClass + ' uk-icon-hover uk-icon-trash uk-icon-small" data-uk-tooltip title="Delete"  data-id=' + value + '></a>';

            if (rowData.generate_status === 3 || rowData.generate_status === 0 || rowData.generate_status === 4) {
                buttons = this.renderRefreshButton(value) + buttons;
            }

            if (rowData.generate_status === 0) {
                buttons = this.renderProgressContainer(value, buttons);
            }
        }

        return '<div class="table-actions">' + buttons + '</div>';
    }

    this.getDataSource = function() {
        var source = {
            dataType: 'json',
            root: 'data',
            url: tableUrl,
            dataFields: [{
                name: 'report_id',
                type: 'integer'
            }, {
                name: 'report_name',
                type: 'string'
            }, {
                name: 'message_status',
                type: 'string'
            }, {
                name: 'generate_status',
                type: 'integer'
            }, {
                name: 'generated_at',
                type: 'string'
            }, {
                name: 'api_user_dashboard',
                type: 'string'
            }, {
                name: 'file_type',
                type: 'string'
            }, {
                name: 'start_date',
                type: 'string'
            }, {
                name: 'end_date',
                type: 'string'
            }, {
                name: 'download_url',
                type: 'string'
            }, {
                name: 'percentage',
                type: 'float'
            }, ],
            beforeprocessing: function(data) {
                this.totalrecords = data.total;
            },
        };

        var setting = {
            formatData: this.formatDataTable.bind(this)
        };

        return this.dataSource = new $.jqx.dataAdapter(source, setting);
    }

    this.formatDataTable = function(data) {
        return $.extend({}, data, this.searchData());
    }

    this.searchData = function() {
        var data = $('#search-panel :input').serializeArray();
        var result = {};
        data.filter(function(item) {
            return item.value != "";
        }).forEach(function(item) {
            result[item.name] = item.value;
        });

        return result;
    }

    this.onClickButtonDelete = function(e) {
        var dataId = e.currentTarget.dataset.id;
        this.deleteWindow.openWindow(dataId);
    }

    this.onClickSearchButton = function(e) {

        this.dataSource.dataBind();
    }

    this.onClickClearButton = function(e) {
        $('#search-panel :input').each(function() {
            $(this).val('');
        });
        this.dataSource.dataBind();
    }

    //Register event with event listener
    this.bindEvent = function() {
        $(document)
            .on('click', '.' + deleteButtonClass, this.onClickButtonDelete.bind(this))
            .on('click', '.cancel-report', this.handleCancelGenerateReport.bind(this))
            .on('click', '#' + searchButtonId, this.onClickSearchButton.bind(this))
            .on('click', '#' + clearButtonId, this.onClickClearButton.bind(this))
    }
    this.initComponent = function() {
        //Register Delete window
        var windowOpt = $.extend({}, opt, {
            dataSource: this.dataSource,
            successText: local.success_delete,
            failedText: local.failed_delete,
        });
        this.deleteWindow = new DeleteWindow(windowOpt);
        this.deleteWindow.init();
        this.deleteWindow.bindEvent();

        var cancelOpt = {
            dataSource: this.dataSource,
            successText: local.success_cancel,
            failedText: local.failed_cancel,
            onDelete: this.requestCancelReport,
            deleteWindowId: 'window-cancel',
        }
        this.cancelWindow = new DeleteWindow(cancelOpt);
        this.cancelWindow.init();
        this.cancelWindow.bindEvent();
    }
}

var ReportForm = function(opt) {
    var apiUserUrl = opt.apiUserUrl || location.href;
    var apiUserInputId = 'input-api_user';
    var startDateInputId = 'input-start_date';
    var endDateInputId = 'input-end_date';
    var checkAllButtonId = 'button-checkAll';
    var uncheckAllButtonId = 'button-uncheckAll';
    var dateRangeTextId = 'date-range-text';
    var selectStatusId = 'select-status';
    var local = opt.local || {};

    /**
     * API User Input
     */
    this.getApiUserSource = function() {
        var source = {
            datatype: "json",
            datafields: [{
                name: 'user_id'
            }, {
                name: 'user_name'
            }],
            id: 'user_id',
            url: apiUserUrl,
        };
        return this.apiUserSource = new $.jqx.dataAdapter(source);
    }

    this.apiUserDropdown = function() {

        var input = $("#" + apiUserInputId);

        if (input.length === 0)
            return;

        var options = {
            source: this.getApiUserSource(),
            displayMember: 'user_name',
            valueMember: 'user_id',
            width: '100%',
            height: 28,
            dropDownHeight: 150
        };

        input.jqxComboBox(options);
    }

    this.apiUserInput = function(value) {
        var input = $("#" + apiUserInputId);

        if (input.length === 0)
            return;

        var options = {
            checkboxes: true,
            width: '100%',
            height: 180,
            valueMember: 'user_id',
            displayMember: 'user_name',
            enableHover: false,
            filterable: true,
            filterPlaceHolder: 'search',
            source: this.getApiUserSource()
        };

        input
            .jqxListBox(options)
            .on('bindingComplete', function() {
                if (value) {
                    var array = value.split(',');
                    for (var i = 0; i < array.length; i++) {
                        $("#" + apiUserInputId).jqxListBox('checkItem', array[i]);
                    }
                }
            })
            .find('#filter' + apiUserInputId)
            .css('position', 'initial');
    }

    this.onClickCheckAllApiUsers = function(e) {
        $("#" + apiUserInputId).jqxListBox('checkAll');
    }

    this.onClickUncheckAllApiUsers = function(e) {
        $("#" + apiUserInputId).jqxListBox('uncheckAll');
    }

    /**
     * DatePicker
     */
    this.datePicker = function(start, end) {
        var startDate = $('#' + startDateInputId);
        var endDate = $('#' + endDateInputId);
        var min = moment().subtract(90, 'days').startOf('day');
        var max = moment().endOf('day').toDate();

        start = start ? moment(start).toDate() : min.toDate();

        var startDateOptions = {
            width: '100%',
            value: start,
            min: min.toDate(),
            max: max,
            dropDownVerticalAlignment: 'top',
            showTimeButton: true,
            formatString: 'yyyy-MM-dd HH:mm:ss',
            dropDownHorizontalAlignment: 'right'
        }

        end = end ? moment(end).toDate() : max;

        var endDateOptions = {
            width: '100%',
            min: min.toDate(),
            max: max,
            value: end,
            showTimeButton: true,
            formatString: 'yyyy-MM-dd HH:mm:ss',
            dropDownHorizontalAlignment: 'right'
        }

        startDate
            .jqxDateTimeInput(startDateOptions)
            .on('valueChanged', function(event) {
                endDate.jqxDateTimeInput('setMinDate', event.args.date);
            });
        endDate
            .jqxDateTimeInput(endDateOptions)
            .on('valueChanged', function(event) {
                startDate.jqxDateTimeInput('setMaxDate', event.args.date);
            });

    }

    /**
     * Select Status
     */
    this.selectStatus = function(value) {
        var input = $("#" + selectStatusId);

        if (input.length === 0)
            return;

        var source = this.getSelectStatusSource();
        var options = {
            source: source,
            checkboxes: true,
            displayMember: 'name',
            valueMember: 'value',
            width: '100%',
            height: 105
        };

        input.jqxListBox(options);
        if (value) {
            $.each(value.split(','), function(index, val) {
                input.jqxListBox('checkItem', val);
            });
        } else {
            input.jqxListBox('checkAll');
        }
    }

    this.getSelectStatusSource = function() {
        var data = [
            { 'name': lang.sent, 'value': 'sent' },
            { 'name': lang.delivered, 'value': 'delivered' },
            { 'name': lang.undelivered, 'value': 'undelivered' },
            { 'name': lang.rejected, 'value': 'rejected' },
        ];
        var source = {
            dataType: 'json',
            localdata: data,
            dataFields: [{
                name: 'name',
                type: 'string'
            }, {
                name: 'value',
                type: 'string'
            }]
        };
        return this.selectStatusSource = new $.jqx.dataAdapter(source);
    };
    /**
     * Bind Event
     */
    this.bindEvent = function() {
        $(document)
            .on('click', '#' + checkAllButtonId, this.onClickCheckAllApiUsers)
            .on('click', '#' + uncheckAllButtonId, this.onClickUncheckAllApiUsers)
    }
}

var LoginPage = function() {
    var refreshCaptchaId = 'refresh-captcha';
    this.bindEvent = function() {
        $('#' + refreshCaptchaId).on('click', this.onClickRefreshCaptcha.bind(this));
    }
    this.onClickRefreshCaptcha = function(e) {
        e.preventDefault();
        var url = e.currentTarget.href;
        this.requestRefreshCaptcha(url);
    }
    this.requestRefreshCaptcha = function(url) {
        return $.ajax({
            url: url,
            type: 'GET',
        }).done(function(data) {
            $('#' + refreshCaptchaId + ' img.captcha').attr('src', data.source);
        });
    }
}

var DeleteWindow = function(opt) {
    opt = opt || {};
    var deleteUrl = opt.deleteUrl;
    var deleteWindowId = opt.deleteWindowId || 'window-delete';
    var deleteWindowButtonId = opt.deleteWindowButtonId || 'window-button-delete';
    var closeWindowButtonId = opt.closeWindowButtonId || 'window-button-close';
    var deleteWindowId = opt.deleteWindowId || 'window-delete';
    var dataSource = opt.dataSource || {};
    var successText = opt.successText || 'Success delete data';
    var failedText = opt.failedText || 'Failed delete data';

    /**
     * Initialize Delete Window
     */
    this.init = function() {
        var options = {
            draggable: false,
            isModal: true,
            resizable: false,
            minHeight: 100,
            cancelButton: $('#' + closeWindowButtonId),
        }
        $('#' + deleteWindowId).responsiveWindow(options);
        //Register Notify
        this.notify = new Notify();
        this.onDelete = opt.onDelete || this.deleteRequest;
    }
    this.openWindow = function(dataId) {
        var dom = $('#' + deleteWindowId);
        dom.jqxWindow('open');
        dom.find(':input[name="data_id"]').val(dataId);
    }
    this.closeWindow = function() {
        $('#' + deleteWindowId).jqxWindow('close');
    }
    this.onClickDeleteWindowButton = function(e) {
        var dataId = $('#' + deleteWindowId + ' :input[name="data_id"]').val() || 0;
        var self = this;

        this.onDelete(dataId)
            .done(function(data) {
                if (data.success) {
                    self.closeWindow();
                    if (dataSource.dataBind) {
                        dataSource.dataBind();
                    }
                    self.notify.showSuccess(successText);
                } else {
                    self.notify.showFailed(failedText);
                }
            })
            .fail(function() {})
            .always(this.closeWindow);
    }

    this.deleteRequest = function(dataId) {
        var data = { '_method': 'DELETE' };
        return $.ajax({
            url: deleteUrl + '/' + dataId,
            type: 'POST',
            dataType: 'json',
            data: data,
        });
    }
    this.bindEvent = function() {
        $(document)
            .on('click', '#' + deleteWindowId + ' #' + deleteWindowButtonId, this.onClickDeleteWindowButton.bind(this))
    }
}

var Notify = function() {
    var options = {
        pos: 'top-center',
        timeout: 3000
    }
    this.showSuccess = function(msg) {
        var msg = '<i class="uk-icon-check"></i> ' + msg;
        var opt = $.extend(options, { status: 'success' });
        UIkit.notify(msg, opt);
    }
    this.showFailed = function(msg) {
        var msg = '<i class="uk-icon-check"></i> ' + msg;
        var opt = $.extend(options, { status: 'danger' });
        UIkit.notify(msg, opt);
    }
}

var SelectActive = function(opt) {
    var selectActiveId = 'select-active';

    this.init = function(value) {
        var input = $("#" + selectActiveId);

        if (input.length === 0)
            return;

        var source = this.getSource();
        var options = {
            source: source,
            displayMember: 'name',
            valueMember: 'client_id',
            width: '100%',
            height: 28,
            dropDownHeight: 80
        };

        input.jqxComboBox(options)
            .on('bindingComplete', function() {
                if (value) {
                    var array = value.split(',');
                    for (var i = 0; i < array.length; i++) {
                        input.jqxComboBox('val', array[i]);
                    }
                }
            });
    };
    this.getSource = function() {
        var data = [
            { 'name': 'All', value: '' },
            { 'name': 'Active', value: 1 },
            { 'name': 'Inactive', value: 0 },
        ];
        var source = {
            dataType: 'json',
            localdata: data,
            dataFields: [{
                name: 'name',
                type: 'string'
            }, {
                name: 'value',
                type: 'string'
            }]
        };
        return this.source = new $.jqx.dataAdapter(source);
    }
    this.getInputValue = function() {
        return $("#" + clientInputId).jqxComboBox('val');
    }
}

var DatePicker = function(opt) {
    var opt = opt || {};
    var inputId = opt.inputId;

    /**
     * Select Status
     */
    this.init = function(value) {
        var input = $("#" + inputId);

        if (input.length === 0)
            return;

        var options = {
            displayMember: 'name',
            valueMember: 'value',
            selectedIndex: 0,
            width: '100%',
            height: 28,
            dropDownHeight: isSearching ? 80 : 55
        };

        input.jqxComboBox(options);
    }

}

var FileType = function(opt) {
    var inputId = 'select-file_type';
    var opt = opt || {};
    var isSearching = opt.isSearching || false;

    /**
     * Select Status
     */
    this.init = function(value) {
        var input = $("#" + inputId);

        if (input.length === 0)
            return;

        var source = this.getSource();
        var options = {
            source: source,
            displayMember: 'name',
            valueMember: 'value',
            selectedIndex: 0,
            width: '100%',
            height: 28,
            dropDownHeight: isSearching ? 80 : 55
        };

        input.jqxComboBox(options);

        if (value) {
            input.jqxComboBox('selectItem', value);
        }
    }

    this.getSource = function() {
        var data = [
            { 'name': lang.all, 'value': '' },
            { 'name': 'XLSX', 'value': 'xlsx' },
            { 'name': 'CSV', 'value': 'csv' },
        ];

        if (isSearching === false) {
            data.shift();
        }

        var source = {
            dataType: 'json',
            localdata: data,
            dataFields: [{
                name: 'name',
                type: 'string'
            }, {
                name: 'value',
                type: 'string'
            }]
        };
        return this.inputSource = new $.jqx.dataAdapter(source);
    };
}
