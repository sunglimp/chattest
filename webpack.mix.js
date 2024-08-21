const mix = require('laravel-mix');

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

mix
        //        .styles([
        //                'public/fonts/fontawesome/css/all.min.css',
        //                'public/css/perfect-scrollbar.css',
        //                'public/css/dataTables.min.css',
        //            ], 'public/css/all.css')
        .babel([
                'resources/js/lib/jquery.min.js',
                'resources/js/lib/dataTables.min.js',
                'resources/js/lib/main.js',
                'resources/js/lib/perfect-scrollbar.js',
                'resources/js/lib/dropdown.js',
                'resources/js/custom/organization.js',
                'resources/js/lib/dcalendar.picker.js',
                'resources/js/custom/messages.js',
                'resources/js/custom/common.js'
        ], 'public/js/organization.js')
        .babel([
                'resources/js/lib/jquery.min.js',
                'resources/js/lib/dataTables.min.js',
                'resources/js/lib/main.js',
                'resources/js/lib/perfect-scrollbar.js',
                'resources/js/lib/dropdown.js',
                'resources/js/custom/permission.js',
                'resources/js/custom/group.js',
                'resources/js/custom/messages.js',
                'resources/js/custom/common.js',
                'resources/js/custom/notification.js'
        ], 'public/js/permission.js')
        .babel([
                'resources/js/lib/jquery.min.js',
                'resources/js/lib/dataTables.min.js',
                'resources/js/lib/main.js',
                'resources/js/lib/perfect-scrollbar.js',
                'resources/js/lib/dropdown.js',
                'resources/js/custom/user.js',
                'resources/js/custom/messages.js',
                'resources/js/custom/common.js'
        ], 'public/js/user.js')
        .babel([
                'resources/js/lib/jquery.min.js',
                'resources/js/lib/main.js',
        ], 'public/js/login.js')
        .babel([
                'resources/js/lib/jquery.min.js',
                'resources/js/lib/dataTables.min.js',
                'resources/js/lib/moment.min.js',
                'resources/js/lib/daterangepicker.min.js',
                'resources/js/lib/perfect-scrollbar.js',
                'resources/js/lib/slick.min.js',
                'resources/js/lib/main.js',
                'resources/js/lib/dropdown.js',
                'resources/js/custom/dashboard.js',
                'resources/js/custom/messages.js',
                'resources/js/custom/common.js',
        ], 'public/js/dashboard.js')
        .babel([
                'resources/js/lib/jquery.min.js',
                'resources/js/lib/dataTables.min.js',
                'resources/js/lib/main.js',
                'resources/js/lib/perfect-scrollbar.js',
                'resources/js/lib/dropdown.js',
                'resources/js/custom/messages.js',
                'resources/js/custom/common.js',
                'resources/js/lib/dcalendar.picker.js',
                'resources/js/custom/sent-items.js',
        ], 'public/js/sent-items.js')
        .babel([
                'resources/js/lib/jquery.min.js',
                'resources/js/lib/jquery.session.js',
                'resources/js/lib/dataTables.min.js',
                'resources/js/lib/moment.min.js',
                'resources/js/lib/daterangepicker.min.js',
                'resources/js/lib/main.js',
                'resources/js/lib/perfect-scrollbar.js',
                'resources/js/lib/dropdown.js',
                'resources/js/custom/history.js',
                'resources/js/custom/messages.js',
                'resources/js/custom/common.js'
        ], 'public/js/history.js')
        .babel([
                'resources/js/lib/jquery.min.js',
                'resources/js/lib/dataTables.min.js',
                'resources/js/lib/main.js',
                'resources/js/lib/perfect-scrollbar.js',
                'resources/js/lib/dropdown.js',
        ], 'public/js/translator-common.js')
        .babel([
                'resources/js/lib/jquery.min.js',
                'resources/js/lib/jquery.session.js',
                'resources/js/lib/dataTables.min.js',
                'resources/js/lib/moment.min.js',
                'resources/js/lib/daterangepicker.min.js',
                'resources/js/lib/main.js',
                'resources/js/custom/messages.js',
                'resources/js/custom/common.js',
                'resources/js/custom/offline-queries.js'
        ], 'public/js/offline-queries.js')
        .sass('resources/sass/main.scss', 'public/css')
        .version();
