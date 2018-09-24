<?php

/**
 * @class       Social_Media_HTML_output
 * @version	1.0.0
 * @package	social-media-links
 * @category	Class
 * @author      @author     Designncoding <wpdesigncoding@gmail.com>
 */
class Social_Media_Setting {

    /**
     * Hook in methods
     * @since    1.0.0
     * @access   static
     */
    public static function init() {
        
        add_action('sm_general_tab_setting', array(__CLASS__, 'sm_general_tab_setting'));
        add_action('sm_general_tab_setting_save_field', array(__CLASS__, 'sm_general_tab_setting_save_field'));

        add_action('sm_social_tab_setting', array(__CLASS__, 'sm_social_tab_setting'));
        add_action('sm_social_tab_setting_save_field', array(__CLASS__, 'sm_social_tab_setting_save_field'));
        
        add_action('sm_social_video_tab_setting', array(__CLASS__, 'sm_social_video_tab_setting'));
        add_action('sm_social_video_tab_setting_save_field', array(__CLASS__, 'sm_social_video_tab_setting_save_field'));
        
        add_action('sm_social_display_position_tab_setting', array(__CLASS__, 'sm_social_display_position_tab_setting_fields'));
        add_action('sm_social_display_position_tab_setting_save_field', array(__CLASS__, 'sm_social_display_position_tab_setting_save_field'));
    } 
    
    public static function sm_general_tab_setting() {

        $sm_general_tab_setting_fields = self::sm_general_tab_setting_fields();
        $Html_output = new Social_Media_HTML_Output();
        ?>
        <form id="sm_general_tab_form" enctype="multipart/form-data" action="" method="post">
            <?php $Html_output->init($sm_general_tab_setting_fields); ?>
            <p class="submit">
                <input type="submit" name="sm_general_tab" class="button-primary" value="<?php esc_attr_e('Save changes', 'Option'); ?>" />
            </p>
        </form>
        <?php
    }

    public static function sm_general_tab_setting_fields() {
       
        $fields[] = array('title' => __('General Settings', 'social-media-links'), 'type' => 'title', 'desc' => 'You can use this shortcode "<b>[social_media_link]</b>" in your page or post anywhere', 'id' => 'general_options');
        $fields[] = array(
            'title' => __('Shortcode', 'social-media-links'),
            'type' => 'text',
            'id' => 'sm_social_shortcode',
            'default' => '[social_media_link]',
            'css' => 'min-width:300px;',
            'readonly'=>'readonly'
            
        );        
        $fields[] = array(
            'title' => __('Enable/Disable', 'social-media-links'),
            'type' => 'checkbox',
            'id' => 'sm_social_enable',
            'label' => __('Enable/Disable Social Media', 'social-media-links'),
            'default' => 'no',
            'css' => 'min-width:300px;',
            'desc' => sprintf(__('Enable/Disable Social Media Link', 'social-media-links')),
        );          
        
        $fields[] = array(
            'title' => __('Display Type', 'social-media-links'),
            'desc' => __('Vertical / Horizontal Display Social Button.', 'social-media-links'),
            'id' => 'sm_social_display_type',
            'css' => 'width:25em;',
            'type' => 'select',
            'default' => 'horizontal',
            'class' => 'chosen_select',
            'options' => array('horizontal'=>'Horizontal','vertical'=>'Vertical')
        );
        $fields[] = array(
            'title' => __('Display Position', 'social-media-links'),
            'desc' => __('Display Position Social Media Button.', 'social-media-links'),
            'id' => 'sm_social_display_possition',
            'css' => 'width:25em;',
            'type' => 'select',
            'default' => 'horizontal',
            'class' => 'chosen_select',
            'options' => array('left'=>'Left','center'=>'Center','right'=>'Right')
        );
        
        $fields[] = array(
            'title' => __('Icon Size', 'social-media-links'),
            'desc' => __('Display Icon Size PX.', 'social-media-links'),
            'id' => 'sm_icon_size',
            'css' => 'width:25em;',
            'type' => 'select',
            'default' => '32',
            'class' => 'chosen_select',
            'options' => array('16'=>'16 X 16','32'=>'32 X 32','64'=>'64 X 64')
        );
        
        $fields[] = array(
            'title' => __('Open New Window', 'social-media-links'),
            'type' => 'checkbox',
            'id' => 'sm_social_new_window',
            'label' => __('Click to Open New Window', 'social-media-links'),
            'default' => 'yes',
            'css' => 'min-width:300px;',
            'desc' => sprintf(__('Enable/Disable New Window', 'social-media-links')),
        );
        
       /* $fields[] = array(
            'title' => __('Fixed', 'social-media-links'),
            'type' => 'checkbox',
            'id' => 'sm_social_fixed',
            'label' => __('Fiexd Social Media Button', 'social-media-links'),
            'default' => 'yes',
            'css' => 'min-width:300px;',
            'desc' => sprintf(__('Enable/Disable Fiexd Social Media Button', 'social-media-links')),
        );*/
        
        
        $fields[] = array('type' => 'sectionend', 'id' => 'general_options');
        return $fields;
    }

    public static function sm_general_tab_setting_save_field() {
        $sm_general_tab_setting_fields = self::sm_general_tab_setting_fields();
        $Html_output = new Social_Media_HTML_Output();
        $Html_output->save_fields($sm_general_tab_setting_fields);
    }
    

    public static function sm_social_tab_setting() {

        $sm_social_tab_setting_fields = self::sm_social_tab_setting_fields();
        $Html_output = new Social_Media_HTML_Output();
        ?>
        <form id="sm_social_tab_form" enctype="multipart/form-data" action="" method="post">
            <input type="hidden" name="social_name" value="link">
            <?php $Html_output->init($sm_social_tab_setting_fields); ?>
            <p class="submit">
                <input type="submit" name="sm_social_tab" class="button-primary" value="<?php esc_attr_e('Save changes', 'Option'); ?>" />
            </p>
        </form>
        <?php
    }

    public static function sm_social_tab_setting_fields() {
       
        $fields[] = array('title' => __('Social Networking', 'social-media-links'), 'type' => 'title', 'desc' => 'Add Social Networking link', 'id' => 'general_options');
               
        
        $fields[] = array(
            'title' => __('Facebook URL:', 'social-media-links'),
            'id' => 'sm_facebook',
            'type' => 'text',
            'css' => 'width:25em;',
            'default' => ''
        );
        $fields[] = array(
            'title' => __('Google+ URL:', 'social-media-links'),
            'id' => 'sm_googleplus',
            'type' => 'text',
            'css' => 'width:25em;',
            'default' => ''
        );
        $fields[] = array(
            'title' => __('LinkedIn URL:', 'social-media-links'),
            'id' => 'sm_linkedin',
            'type' => 'text',
            'css' => 'width:25em;',
            'default' => ''
        );
        $fields[] = array(
            'title' => __('Twitter URL:', 'social-media-links'),
            'id' => 'sm_twitter',
            'type' => 'text',
            'css' => 'width:25em;',
            'default' => ''
        );
        $fields[] = array(
            'title' => __('MySpace URL:', 'social-media-links'),
            'id' => 'sm_myspace',
            'type' => 'text',
            'css' => 'width:25em;',
            'default' => ''
        );        
        $fields[] = array(
            'title' => __('Orkut URL:', 'social-media-links'),
            'id' => 'sm_orkut',
            'type' => 'text',
            'css' => 'width:25em;',
            'default' => ''
        );         
        $fields[] = array(
            'title' => __('Hyves URL:', 'social-media-links'),
            'id' => 'sm_hyves',
            'type' => 'text',
            'css' => 'width:25em;',
            'default' => ''
        );
        $fields[] = array(
            'title' => __('aSmallWorld URL:', 'social-media-links'),
            'id' => 'sm_asmallworld',
            'type' => 'text',
            'css' => 'width:25em;',
            'default' => ''
        );
        $fields[] = array(
            'title' => __('Foursquare URL:', 'social-media-links'),
            'id' => 'sm_foursquare',
            'type' => 'text',
            'css' => 'width:25em;',
            'default' => ''
        );
        $fields[] = array(
            'title' => __('Meetup URL:', 'social-media-links'),
            'id' => 'sm_meetup',
            'type' => 'text',
            'css' => 'width:25em;',
            'default' => ''
        );
        $fields[] = array(
            'title' => __('About.me URL:', 'social-media-links'),
            'id' => 'sm_aboutme',
            'type' => 'text',
            'css' => 'width:25em;',
            'default' => ''
        );
        $fields[] = array(
            'title' => __('Skyrock URL:', 'social-media-links'),
            'id' => 'sm_skyrock',
            'type' => 'text',
            'css' => 'width:25em;',
            'default' => ''
        );
        $fields[] = array(
            'title' => __('Goodreads URL:', 'social-media-links'),
            'id' => 'sm_goodreads',
            'type' => 'text',
            'css' => 'width:25em;',
            'default' => ''
        );
        $fields[] = array(
            'title' => __('Github URL:', 'social-media-links'),
            'id' => 'sm_github',
            'type' => 'text',
            'css' => 'width:25em;',
            'default' => ''
        );
        $fields[] = array(
            'title' => __('VK URL:', 'social-media-links'),
            'id' => 'sm_vk',
            'type' => 'text',
            'css' => 'width:25em;',
            'default' => ''
        );        
        
        $fields[] = array('type' => 'sectionend', 'id' => 'general_options');
        return $fields;
    }

    public static function sm_social_tab_setting_save_field() {
        $sm_social_tab_setting_fields = self::sm_social_tab_setting_fields();
        $Html_output = new Social_Media_HTML_Output();
        $Html_output->save_fields($sm_social_tab_setting_fields);
    }
    
    
    public static function sm_social_video_tab_setting() {

        $sm_social_video_tab_setting_fields = self::sm_social_video_tab_setting_fields();
        $Html_output = new Social_Media_HTML_Output();
        ?>
        <form id="sm_social_video_tab_form" enctype="multipart/form-data" action="" method="post">
            <input type="hidden" name="social_name" value="video">
            <?php $Html_output->init($sm_social_video_tab_setting_fields); ?>
            <p class="submit">
                <input type="submit" name="sm_social_video_tab" class="button-primary" value="<?php esc_attr_e('Save changes', 'Option'); ?>" />
            </p>
        </form>
        <?php
    }

    public static function sm_social_video_tab_setting_fields() {
       
        $fields[] = array('title' => __('Images and Video', 'social-media-links'), 'type' => 'title', 'desc' => 'Add Images and Video link', 'id' => 'general_options');
        
        $fields[] = array(
            'title' => __('Flickr URL', 'social-media-links'),
            'id' => 'sm_flickr',
            'type' => 'text',
            'css' => 'width:25em;',
            'default' => ''
        );
        $fields[] = array(
            'title' => __('Picasa URL:', 'social-media-links'),
            'id' => 'sm_picasa',
            'type' => 'text',
            'css' => 'width:25em;',
            'default' => ''
        );
        $fields[] = array(
            'title' => __('Instagram URL:', 'social-media-links'),
            'id' => 'sm_instagram',
            'type' => 'text',
            'css' => 'width:25em;',
            'default' => ''
        );
        $fields[] = array(
            'title' => __('Pinterest URL:', 'social-media-links'),
            'id' => 'sm_pinterest',
            'type' => 'text',
            'css' => 'width:25em;',
            'default' => ''
        );
        $fields[] = array(
            'title' => __('DeviantArt URL:', 'social-media-links'),
            'id' => 'sm_deviantart',
            'type' => 'text',
            'css' => 'width:25em;',
            'default' => ''
        );        
        $fields[] = array(
            'title' => __('YouTube URL:', 'social-media-links'),
            'id' => 'sm_youtube',
            'type' => 'text',
            'css' => 'width:25em;',
            'default' => ''
        );
        $fields[] = array(
            'title' => __('Hulu URL:', 'social-media-links'),
            'id' => 'sm_hulu',
            'type' => 'text',
            'css' => 'width:25em;',
            'default' => ''
        );
        $fields[] = array(
            'title' => __('UStream URL:', 'social-media-links'),
            'id' => 'sm_ustream',
            'type' => 'text',
            'css' => 'width:25em;',
            'default' => ''
        );
        $fields[] = array(
            'title' => __('Vimeo URL:', 'social-media-links'),
            'id' => 'sm_vimeo',
            'type' => 'text',
            'css' => 'width:25em;',
            'default' => ''
        );
        $fields[] = array(
            'title' => __('Flixster URL:', 'social-media-links'),
            'id' => 'sm_flixster',
            'type' => 'text',
            'css' => 'width:25em;',
            'default' => ''
        );
        $fields[] = array(
            'title' => __('IMDb URL:', 'social-media-links'),
            'id' => 'sm_imdb',
            'type' => 'text',
            'css' => 'width:25em;',
            'default' => ''
        );
        $fields[] = array('type' => 'sectionend', 'id' => 'general_options');
        return $fields;
    }

    public static function sm_social_video_tab_setting_save_field() {
        $sm_social_video_tab_setting_fields = self::sm_social_video_tab_setting_fields();
        $Html_output = new Social_Media_HTML_Output();
        $Html_output->save_fields($sm_social_video_tab_setting_fields);
    }
    
    
    
    public static function sm_social_display_position_tab_setting_fields() {
        $result = self::GetSocialmediaList();        
        echo "<form action=\"\" method=\"post\">";
        echo "<table class=\"form-table widefat viewsociallink\" style=\"width:50%;\">";            
            echo "<tbody>";
        if(is_array($result) && count($result) > 0){
            
            $count = 0;
            ?>
            <style>
              .viewsociallink tr:nth-child(even) {background: #CCC}
            .viewsociallink tr:nth-child(odd) {background: #FFF}
            </style>
                            
            <?php
            foreach ($result as $key => $value) { 
                $count++;             
                echo "<tr style=\"cursor: move; \">";                
                echo "<td style=\"border:1px solid #000;\">";
                echo ucfirst($value['name']).' - '. $value['link'];
                echo "<input type=\"hidden\" name=\"".$value['id']."\" value=\"".$value['link']."\" >";
                echo "</td>";                                
                echo "</tr>";
                     
            }
        } else {
            echo "<tr><td colspan=\"3\">Social Link Not Found.!</td></tr>";
        } 
        echo "</tbody>";
        echo "</table>";
        echo "<p class=\"submit\">";        
        echo "<button type=\"submit\" name=\"save\" class=\"button-primary\" value=\"save\">Save changes</button>";
        echo "</p>";
        echo "</form>";
    }
    
     public static function sm_social_display_position_tab_setting_save_field() {       
        $Html_output = new Social_Media_HTML_Output();
        $Html_output->save_fields('');
    }
    
    public static function GetSocialmediaList(){
        $result = array();
        
        if(is_array(get_option('social_sorting_position')) && count(get_option('social_sorting_position')) > 0 ){
            foreach (get_option('social_sorting_position') as $key => $value) {
                if(strlen($value['link']) > 0  ){
                    $resulttemp['name'] = $value['name'];
                    $resulttemp['id'] = $value['id'];
                    $resulttemp['link'] = $value['link'];
                    array_push($result, $resulttemp);
                }
            }
            return $result;
        }
        
        if(is_array(get_option('social_media_array')) && count(get_option('social_media_array')) > 0 ){
            foreach (get_option('social_media_array') as $key => $value) {
                if(strlen($value['link']) > 0  ){
                    $resulttemp['name'] = $value['name'];
                    $resulttemp['id'] = $value['id'];
                    $resulttemp['link'] = $value['link'];
                    array_push($result, $resulttemp);
                }
            }
        }
        
        if(is_array(get_option('social_media_video_array')) && count(get_option('social_media_video_array')) > 0 ){
            foreach (get_option('social_media_video_array') as $key => $value) {
                if(strlen($value['link']) > 0  ){
                    $resulttemp['name'] = $value['name'];
                    $resulttemp['id'] = $value['id'];
                    $resulttemp['link'] = $value['link'];
                    array_push($result, $resulttemp);
                }
            }
        }
        
        return $result;
    }

}

Social_Media_Setting::init();
