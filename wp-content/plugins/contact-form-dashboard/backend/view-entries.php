<?php

// don't load directly
if ( ! defined( 'ABSPATH' ) || empty( $post->ID ))
	die( '-1' );

$entries = new CFD_Entries();
$_fields_arr = get_post_meta($post->ID, "_fields", true);
$_meta_arr = get_post_meta($post->ID, "_meta", true);
$_properties_arr = get_post_meta($post->ID, "_properties", true);
$_body = (isset($_properties_arr['mail']['body']) && $_properties_arr['mail']['body'] != '') ? $_properties_arr['mail']['body'] : '';

if ( $_body != '' ) {
    $final_body = $_body;
    foreach ($_fields_arr as $key => $value) {
        $final_body = str_replace('['.$key.']', get_post_meta($post->ID, "_field_".$key, true), $final_body);
    }
}
$_response_manualy = get_post_meta($post->ID, "_response_manualy", true);
$_total_response = get_post_meta($post->ID, "_total_response", true);
if ($_total_response > 0) {
    $response = get_post_meta($post->ID, "_response_" . $_total_response , true);
}

?>
<style type="text/css">
    #screen-meta-links .screen-meta-toggle {display: none;}
</style>
<div class="wrap">

	<div class="alignleft full-width">
        <h1 style="display: inline-block;"><?php
            echo esc_html( __( 'Message Detail', CFD_PLUGIN_TEXT_DOMAIN ) );
        ?></h1>
        <a href="javascript:void(0)" class="button float-right hidden-print print-btn" style="margin: 9px 0 4px;" onclick="window.print();">
            <span class="print-icon"></span>Print
        </a>
    </div>

	<table class="form-table view-entry-table">
        <tr>
            <th>&nbsp;</th>
            <td class="sub-title">
                <h3>Inquiry Details</h3>
            </td>
        </tr>
		<tr>
            <th>Form</th>
            <td>
                <?php echo get_the_terms($post->ID, $entries->getGlob('channel_taxonomy'))[0]->name; ?>
            </td>
        </tr>
        <tr>
            <th>From</th>
            <td>
                <strong><?php 
                        $_from_str = get_post_meta($post->ID, "_from", true);
                        echo htmlspecialchars(( $_from_str != '' ? $_from_str : "-" ));
                ?></strong>
            </td>
        </tr>
        <tr>
            <th>To</th>
            <td>
                <?php echo ((isset($_meta_arr['post_author_email']) && $_meta_arr['post_author_email'] != '') ? $_meta_arr['post_author_email'] : '-' ); ?>
            </td>
        </tr>
        <tr>
            <th>Subject</th>
            <td>
                <?php echo ( get_post_meta($post->ID, '_subject', true) != '' ? get_post_meta($post->ID, '_subject', true) : '-' ); ?>
            </td>
        </tr>
        <tr>
            <th>&nbsp;</th>
            <td>
                <div class="mail-content">
                	<pre><?php echo htmlspecialchars($final_body); ?></pre>
                </div>
            </td>
        </tr>
        <tr>
            <th>&nbsp;</th>
            <td class="sub-title">
                <h3>Other Details</h3>
            </td>
        </tr>
        <tr>
            <th>IP Address</th>
            <td>
                <?php echo ((isset($_meta_arr['remote_ip']) && $_meta_arr['remote_ip'] != '') ? $_meta_arr['remote_ip'] : '-' ); ?>
            </td>
        </tr>
        <tr>
            <th>User Agent</th>
            <td>
                <?php echo ((isset($_meta_arr['user_agent']) && $_meta_arr['user_agent'] != '') ? '<code>'.$_meta_arr['user_agent'].'</code>' : '-' ); ?>
            </td>
        </tr>
        <tr>
            <th>Client Url</th>
            <td>
                <?php echo ((isset($_meta_arr['url']) && $_meta_arr['url'] != '') ? '<a href="'.$_meta_arr['url'].'" target="_blank">'.$_meta_arr['url'].'</a>' : '-' ); ?>
            </td>
        </tr>
        <tr>
            <th>Time</th>
            <td>
                <?php echo ((isset($_meta_arr['date']) && $_meta_arr['date'] != '') ? $_meta_arr['date'].' '.$_meta_arr['time'] : '-' ); ?>
            </td>
        </tr>
        <?php if ($_total_response > 0) { ?>
            <tr>
                <th>&nbsp;</th>
                <td class="sub-title" style="padding-top: 25px;">
                    <h3>Our Response</h3>
                </td>
            </tr>
            <tr>
                <th>&nbsp;</th>
                <td>
                <?php if ( $_response_manualy && $response == '') { 
                    echo "<div class='color-green'> Marked as Replied. </div>";
                } else { ?>
                    <div class="mail-content">
                        <pre><?php echo $response; ?></pre>
                    </div>
                <?php } ?>
                </td>
            </tr>
        <?php } ?>
	</table>

</div>