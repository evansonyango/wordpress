<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       
 * @since      1.0.0
 *
 * @package    Social_Media
 * @subpackage Social_Media/admin/partials
 */
class Social_Media_Admin_Display {

    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_settings_menu'));
    }

    public static function add_settings_menu() {
        //add_options_page('Social Media Option', 'Social Media', 'manage_options', 'social-media', array(__CLASS__, 'social_media_function'));
        add_menu_page( 'Social Media Option', 'Social Media Links', 'manage_options', 'social-media', array(__CLASS__, 'social_media_function'),'dashicons-share');
    }

    public static function social_media_function() {
        $setting_tabs = apply_filters('social_media_options_setting_tab', array('general_tab' => 'General Setting','social_tab' => 'Social Networking', 'social_video_tab' => 'Images and Video','social_display_position_tab'=>'Networking Display Position'));
        $current_tab = (isset($_GET['tab'])) ? $_GET['tab'] : 'general_tab';
        ?>
        <h2 class="nav-tab-wrapper">
        <?php
        foreach ($setting_tabs as $name => $label)
            echo '<a href="' . admin_url('admin.php?page=social-media&tab=' . $name) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';
        ?>
        </h2>
            <?php
            foreach ($setting_tabs as $setting_tabkey => $setting_tabvalue) {
                switch ($setting_tabkey) {
                    case $current_tab:
                        do_action('sm_' . $setting_tabkey . '_setting_save_field');
                        do_action('sm_' . $setting_tabkey . '_setting');
                        break;
                }
            }
        }

    }

    Social_Media_Admin_Display::init();