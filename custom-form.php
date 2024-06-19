<?php
/*
Plugin Name: Custom Form Test Plugin
Description: Плагин для отпарвки email сообщений
Version: 1.0
Author: Никита Осмаковский
License: GPL2
*/

/*  Copyright 2024  Nikita Osmakovskii  (email: dekantis@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define( 'CUSTOM_FORM_VERSION', '1.0.0' );
define( 'CUSTOM_FORM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define('CUSTOM_FORM_SEND_FORM_ACTION' , 'send_custom_form');
define('CUSTOM_FORM_LOG_ENABLED' , true);

add_action( 'admin_menu', 'custom_form_admin_menu' );

if (
    (is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ))
    && $_GET['page'] == 'custom-form'

) {
    require_once( CUSTOM_FORM_PLUGIN_DIR . 'class.custom-form.php' );

    add_action( 'init', array( 'CustomForm', 'init' ) );
}

function custom_form_admin_menu() {
    add_menu_page(
    'Custom Email Form',
    'Custom Email Form',
    'manage_options',
    CUSTOM_FORM_PLUGIN_DIR,
    array( 'CustomForm', 'custom_email_form'),
    'dashicons-email-alt',
    6
    );
}