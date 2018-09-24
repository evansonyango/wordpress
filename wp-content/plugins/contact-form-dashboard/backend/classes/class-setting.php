<?php

$GLOB_SETTING = array(
    'default_tab' => 'general',
    'tab_arr' => array(
        'general' => __( 'General Settings', CFD_PLUGIN_TEXT_DOMAIN ),
        'search' => __( 'Search Settings', CFD_PLUGIN_TEXT_DOMAIN ),
        'keywords' => __( 'Keywords Search Settings', CFD_PLUGIN_TEXT_DOMAIN )
    )
);

class CFD_Setting {

    public $glob;

    public function __construct() {
        global $GLOB_SETTING;
        $this->globSetting =& $GLOB_SETTING;
    }

    public function getGlobSetting($key = '') {
        return $this->globSetting[$key];
    }

    function cfd_admin_tabs( $current = '' ) {
        
        if ($current == '')
            $current = $this->getGlobSetting('default_tab');
        
        $tabs = $this->getGlobSetting('tab_arr');

        echo '<h2 class="nav-tab-wrapper">';
        foreach( $tabs as $tab => $name ){
            $class = ( $tab == $current ) ? ' nav-tab-active' : '';
            echo sprintf( '<a href="%1$s" class="nav-tab%2$s">%3$s</a>',
                    admin_url( 'admin.php?page=wts_wp_cfd_setting&tab=' . esc_html ( $tab ) ),
                    esc_html ( $class ),
                    esc_html ( $name ) );

        }
        echo '</h2>';
    }

    function cfd_settings_page() {
        
        $default_excludes = "the, be, to, of, and, a, in, that, have, i, it, for, not, on, with, he, as, you, do, at, this, but, his, by, from, they, we, say, her, she, or, an, will, my, one, all, would, there, their, what, so, up, out, if, about, who, get, which, go, me, when, make, can, like, time, no, just, him, know, take, people, into, year, your, good, some, could, them, see, other, than, then, now, look, only, come, its, over, think, also, back, after, use, two, how, our, work, first, well, way, even, new, want, because, any, these, give, day, most, us, thank, okay, sorry, is, are, am, does, did, please, regards, where, why, hi, hello, greetings, concern";

        $plugin_settings = get_option( "cfd_settings" );
        $plugin_search_settings = get_option( "cfd_search_settings" );
        $plugin_keyword_search_settings = get_option( "cfd_keyword_search_settings" );
        
        //generic HTML and code goes here
        if ( isset ( $_GET['tab'] ) ) $this->cfd_admin_tabs($_GET['tab']); else $this->cfd_admin_tabs();

        ?>
            <form method="post" action="<?php admin_url( 'themes.php?page=wts_wp_cfd_setting' ); ?>">
                <?php

                if ( $_GET['page'] == 'wts_wp_cfd_setting' ){

                   if ( isset ( $_GET['tab'] ) ) $tab = $_GET['tab'];
                   else $tab = $this->getGlobSetting('default_tab');

                   echo '<div class="settings-section">';
                   $channel_array = $this->cfd_get_contact_form_7('default_channel', '', true);
                   switch ( $tab ){
                        case 'general' :
                            echo '<table class="form-table settings-table">';
                            ?>
                            <tr>
                                <td colspan="2" style="padding-left: 0px;">
                                    <input name="enable" type="checkbox" <?php echo esc_html ( (isset($plugin_settings["enable"])) ? 'checked="checked"' : '' ); ?> value="true" />
                                    <label for="ilc_tag_class">Enable Contact Form Dashboard Plugin.</label>
                                </td>
                            </tr>
                            <tr>
                                <th>Select Default Form</th>
                                <td>
                                    <?php 
                                        $this->cfd_get_contact_form_7('default_channel', $plugin_settings["default_channel"]);
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Hide Contact Form</th>
                                <td>
                                    <div class="field-list">
                                        <?php
                                            $all_channel_array = $this->cfd_get_contact_form_7('default_channel', '', true, false);
                                            foreach ($all_channel_array as $key => $value) { ?>
                                                <div class="field">
                                                    <input name="cfd_disable[]" type="checkbox" value="<?php echo esc_html ( $key ); ?>" <?php echo esc_html ( (isset($plugin_settings['cfd_disable']) && in_array($key, $plugin_settings['cfd_disable'])) ? 'checked="checked"': '' ); ?>/>
                                                    <label for="ilc_tag_class"><?php echo esc_html ( $value ); ?></label>
                                                </div>
                                            <?php
                                            }
                                        ?>  
                                    </div>
                                </td>
                            </tr>
                            <?php
                            echo '</table>';
                            echo '<p class="submit" style="clear: both;">
                                    <input type="submit" name="submit" class="button-primary" value="Update Settings" />
                                </p>';
                        break;
                        case 'search' :
                            echo '<legend>'.esc_html ( __( 'From following list, please select the fields you want to keep searchable on messages list.', CFD_PLUGIN_TEXT_DOMAIN ) ).'</legend>';
                            echo '<table class="form-table settings-table settings-search">';
                            
                                foreach ($channel_array as $key => $value) { 
                                    $term = get_term_meta( $key, '_'.$key, true );
                                    ?>
                                    <tr>
                                        <th><?php echo esc_html ( $value ); ?></th>
                                        <td><div class="field-list"> 
                                            <?php
                                            foreach ($term as $k => $v) { ?>
                                                <div class="field">
                                                    <input name="cfd_<?php echo esc_html ( $key ); ?>[]" type="checkbox" value="<?php echo esc_html ( $k ); ?>" <?php echo esc_html ( (isset($plugin_search_settings['cfd_'.$key]) && in_array($k, $plugin_search_settings['cfd_'.$key])) ? 'checked="checked"':'' ); ?> />
                                                    <label for="ilc_tag_class"><?php echo esc_html ( $k ); ?></label>
                                                </div>
                                            <?php
                                            }
                                        ?>  </div>
                                        </td>
                                    </tr>
                                <?php
                                }
                            echo '<input type="hidden" name="search_setting" value="true">';
                            echo '</table>';
                            echo '<p class="submit" style="clear: both;">
                                    <input type="submit" name="submit" class="button-primary" value="Update Settings" />
                                </p>';
                        break;
                        case 'keywords' :
                            echo '<legend>'.esc_html ( __( 'Please select the fields below to apply keyword analytics. You can remove or add words to Excluded Words list as per your requirements. Click the Update Settings button to save your changes.', CFD_PLUGIN_TEXT_DOMAIN ) ).'</legend><br>';
                            echo '<legend><strong>Note: </strong>'. esc_html ( __( 'To exclude words from analytics, add comma separated words in only lower case.', CFD_PLUGIN_TEXT_DOMAIN ) ).'</legend>';
                            echo '<table class="form-table settings-table settings-search">';
                            
                                foreach ($channel_array as $key => $value) { 
                                    $term = get_term_meta( $key, '_'.$key, true );
                                    ?>
                                    <tr>
                                        <th><?php echo esc_html ( $value ); ?></th>
                                        <td><div class="field-list"> 
                                            <?php
                                            foreach ($term as $k => $v) { ?>
                                                <div class="field">
                                                    <input name="cfd_<?php echo esc_html ( $key ); ?>[]" type="checkbox" value="<?php echo esc_html ( $k ); ?>" <?php echo esc_html ( (isset($plugin_keyword_search_settings['cfd_'.$key]) && in_array($k, $plugin_keyword_search_settings['cfd_'.$key])) ? 'checked="checked"':'' ); ?> />
                                                    <label for="ilc_tag_class"><?php echo esc_html ( $k ); ?></label>
                                                </div>
                                            <?php
                                            }
                                        ?>  </div>
                                            <legend>Excluded Words</legend>
                                            <textarea name="cfd_exclude_<?php echo esc_html ( $key ); ?>" style="width: 100%; height: 90px;"><?php echo esc_textarea ( (isset($plugin_keyword_search_settings['cfd_exclude_'.$key]) ? stripslashes($plugin_keyword_search_settings['cfd_exclude_'.$key]) : stripslashes($default_excludes) ) ); ?></textarea>
                                        </td>
                                    </tr>
                                <?php
                                }
                            echo '<input type="hidden" name="keyword_search_setting" value="true">';
                            echo '</table>';
                            echo '<p class="submit" style="clear: both;">
                                    <input type="submit" name="submit" class="button-primary" value="Update Settings" />
                                </p>';
                        break;
                   }
                   echo '</div>';
                }

                ?>
                </form>
        <?php

    }

    function cfd_get_contact_form_7 ($name, $selected = '', $in_array = false, $only_enable = true) {

        $taxonomyName = "cfd_entries_channel";
        $parent_terms = get_terms( $taxonomyName, array( 'parent' => 0, 'orderby' => 'term_id', 'hide_empty' => false ) );

        if ( $only_enable ) {
            $plugin_settings = get_option( "cfd_settings" );
        }

        $channel_array = array();
        $echo = '';
        $echo .= '<select name="'.$name.'" id="'.$name.'">';
        foreach ( $parent_terms as $pterm ) {
            //Get the Child terms
            $terms = get_terms( $taxonomyName, array( 'parent' => $pterm->term_id, 'orderby' => 'term_id', 'hide_empty' => false ) );

            if (count($terms) > 0) {
                foreach ( $terms as $term ) {
                    if ( !$only_enable || !isset( $plugin_settings['cfd_disable'] ) || ( $only_enable && !in_array($term->term_id, $plugin_settings['cfd_disable']) ) ) {
                        $channel_array[$term->term_id] = $term->name;
                        $echo .= '<option value="' . $term->term_id . '"  '. (($selected == $term->term_id)? 'selected=selected':'') .' >' . $term->name . '</option>';
                    }
                }
            }
        }
        $echo .= '</select>';

        if ( $in_array ) {
            return $channel_array;
        } else {
            echo $echo;
        }
    }

}