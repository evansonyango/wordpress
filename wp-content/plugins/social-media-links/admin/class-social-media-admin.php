<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Social_Media
 * @subpackage Social_Media/admin
 * @author     Designncoding <wpdesigncoding@gmail.com>
 */
class Social_Media_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->load_dependencies_file();
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Plugin_Name_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Plugin_Name_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/social-media-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Plugin_Name_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Plugin_Name_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/social-media-admin.js', array('jquery'), $this->version, false);
        wp_enqueue_script($this->plugin_name . 'sort', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.js', array('jquery'), $this->version, false);
    }

    private function load_dependencies_file() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/class-social-media-admin-display.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/class-social-media-general-setting.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/class-social-media-html-output.php';
    }

    public function update_position() {

        if (!get_option('social_sorting_position')) {
//            $social_array_link = (get_option('social_media_array')) ? get_option('social_media_array') : '';
//            $social_array_video = (get_option('social_media_video_array')) ? get_option('social_media_video_array') : '';
//            $update_options_array = array();
//            if(is_array($social_array_link) && count($social_array_link) > 0 && is_array($social_array_video) && count($social_array_video) > 0){
//                $update_options_array = array_merge($social_array_link,$social_array_video);
//            } else if(is_array($social_array_link) && count($social_array_link) > 0 ){
//              $update_options_array= $social_array_link;
//            }else if(is_array($social_array_video) && count($social_array_video) > 0 ){
//             $update_options_array = $social_array_video;
//            } 
//            update_option('social_sorting_position', $update_options_array);


            $update_options_array = array();
            if (is_array(get_option('social_media_array')) && count(get_option('social_media_array')) > 0) {
                foreach (get_option('social_media_array') as $key => $value) {
                    if (strlen($value['link']) > 0) {
                        $resulttemp['name'] = $value['name'];
                        $resulttemp['id'] = $value['id'];
                        $resulttemp['link'] = $value['link'];
                        array_push($update_options_array, $resulttemp);
                    }
                }
            }
            if (is_array(get_option('social_media_video_array')) && count(get_option('social_media_video_array')) > 0) {
                foreach (get_option('social_media_video_array') as $key => $value) {
                    if (strlen($value['link']) > 0) {
                        $resulttemp['name'] = $value['name'];
                        $resulttemp['id'] = $value['id'];
                        $resulttemp['link'] = $value['link'];
                        array_push($update_options_array, $resulttemp);
                    }
                }
            }
            update_option('social_sorting_position', $update_options_array);
        }
    }

}
