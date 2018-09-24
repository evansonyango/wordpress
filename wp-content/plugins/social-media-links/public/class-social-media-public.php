<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Social_Media
 * @subpackage Social_Media/public
 * @author     Designncoding <wpdesigncoding@gmail.com>
 */
class Social_Media_Public {

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
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        add_shortcode('social_media_link', array(__CLASS__, 'social_media_link_function'));
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
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
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/social-media-public.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
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
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/social-media-public.js', array('jquery'), $this->version, false);
    }

    public function social_media_link_function() {

        $sm_social_shortcode = (get_option('sm_social_enable')) ? get_option('sm_social_enable') : '';
        if ((isset($sm_social_shortcode) && !empty($sm_social_shortcode) && 'yes' == $sm_social_shortcode)) {

            $sm_social_display_type = ((get_option('sm_social_display_type') == 'horizontal') ? 'sm_horizontal' : '');
            $sm_icon_size = (get_option('sm_icon_size')) ? get_option('sm_icon_size') : '16';

            $sm_social_new_window = get_option('sm_social_new_window', "no") === "yes" ? 'target="_blank"' : 'target="_self"';

            //$social_array_link = (get_option('social_media_array')) ? get_option('social_media_array') : '';
            //$social_array_video = (get_option('social_media_video_array')) ? get_option('social_media_video_array') : '';
            // $social_array = self::push_array_sm_link_and_video($social_array_link, $social_array_video);

            $sm_social_display_possition = "sm_txt_center";
            if (get_option('sm_social_display_possition') == 'left') {
                $sm_social_display_possition = "sm_txt_left";
            } else if (get_option('sm_social_display_possition') == 'right') {
                $sm_social_display_possition = "sm_txt_right";
            }

            $social_array = (get_option('social_sorting_position')) ? get_option('social_sorting_position') : '';
            $output = "";
            if (is_array($social_array) && count($social_array) > 0) {
                $output .= "<ul class=\"social-midea-plugin $sm_social_display_type $sm_social_display_possition \">";
                foreach ($social_array as $key => $value) {
                    if (isset($value['link']) && strlen($value['link']) > 0) {
                        $image_url = plugins_url() . "/social-media-links/public/images/default/" . $sm_icon_size . '/' . $value['name'] . ".png";
                        $output .= "<li><a href=\"" . $value['link'] . "\" $sm_social_new_window ><img src=\"" . $image_url . "\" alt=\"" . $value['name'] . "\"></a></li>";
                    }
                }
                $output .= "</ul>";
            }
            return $output;
        }
    }

}
