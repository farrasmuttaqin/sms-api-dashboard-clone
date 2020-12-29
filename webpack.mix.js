let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

/**
 * Mix Style files
 * @type {Array}
 */
let styles = [
    'resources/assets/vendors/uikit/css/uikit.almost-flat.min.css',
    'resources/assets/vendors/jqwidgets/styles/jqx.base.css',
    'resources/assets/vendors/jqwidgets/styles/jqx.metro_light.css',
    'resources/assets/vendors/uikit/css/components/form-file.min.css',
    'resources/assets/vendors/uikit/css/components/placeholder.almost-flat.min.css',
    'resources/assets/vendors/uikit/css/components/notify.almost-flat.min.css',
    'resources/assets/vendors/uikit/css/components/tooltip.almost-flat.min.css',
    'resources/assets/vendors/uikit/css/components/progress.almost-flat.min.css',
    'resources/assets/css/custom.css',
];

mix.copyDirectory('resources/assets/vendors/uikit/fonts', 'public/dist/fonts');
mix.copyDirectory('resources/assets/vendors/jqwidgets/styles/images', 'public/dist/css/images');
mix.styles(styles, 'public/dist/css/all.css').version();

/**
 * Mix script files
 * @type {Array}
 */
let scripts = [
    'resources/assets/js/jquery-1.12.0.min.js',
    'node_modules/moment/moment.js',
    'resources/assets/vendors/uikit/js/uikit.min.js',
    'resources/assets/vendors/uikit/js/components/notify.min.js',
    'resources/assets/vendors/uikit/js/components/tooltip.min.js',
    'resources/assets/vendors/jqwidgets/jqxcore.js',
    'resources/assets/vendors/jqwidgets/jqxbuttons.js',
    'resources/assets/vendors/jqwidgets/jqxswitchbutton.js',
    'resources/assets/vendors/jqwidgets/jqxdata.js',
    'resources/assets/vendors/jqwidgets/jqxscrollbar.js',
    'resources/assets/vendors/jqwidgets/jqxlistbox.js',
    'resources/assets/vendors/jqwidgets/jqxloader.js',
    'resources/assets/vendors/jqwidgets/jqxdropdownlist.js',
    'resources/assets/vendors/jqwidgets/jqxcombobox.js',
    'resources/assets/vendors/jqwidgets/jqxtooltip.js',
    'resources/assets/vendors/jqwidgets/jqxpasswordinput.js',
    'resources/assets/vendors/jqwidgets/jqxvalidator.js',
    'resources/assets/vendors/jqwidgets/jqxwindow.js',
    'resources/assets/vendors/jqwidgets/jqxdatetimeinput.js',
    'resources/assets/vendors/jqwidgets/jqxcalendar.js',
    'resources/assets/vendors/jqwidgets/jqxdraw.js',
    'resources/assets/vendors/jqwidgets/jqxchart.core.js',
    'resources/assets/vendors/jqwidgets/jqxdatatable.js',
    'resources/assets/vendors/jqwidgets/globalization/globalize.js',
    'resources/assets/js/jqwidget.custom.js',
    'resources/assets/js/main.js'
];
mix.scripts(scripts, 'public/dist/js/all.js').version();