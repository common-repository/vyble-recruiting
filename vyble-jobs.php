<?php
/**
 * Plugin Name:       vyble® Recruiting
 * Description:       Recruiting leicht gemacht: Die vyble® HR-Plattform hilft Unternehmen, ihren Recruiting-Prozess zu digitalisieren und effizienter zu machen. Das vyble® Recruiting Plugin bringt die Stellenausschreibungen auf Ihre Website und überträgt alle Bewerbungen in das vyble® Recruiting System.
 * Version:           1.2
 * Author:            vyble.io
 * Author URI:        https://www.vyble.io
 */

#include('includes/pagemeta.php');
include('includes/jobs.php');
include('includes/themetexts.php');

add_action( 'wp_enqueue_scripts', function() {
	if (!is_admin())
	{
		if ( ! wp_script_is( 'jquery', 'enqueued' )) 
		{
			wp_enqueue_script( 'jquery' );
		}
		wp_enqueue_script( 'vyble-jobs', plugins_url( 'js/app.js?v='.time(), __FILE__ ) ); 
		wp_enqueue_style( 'vyble-jobs', plugins_url('css/style.css?v='.time(), __FILE__ )  ); 
	}
});

function vyble_deactivate() {
    wp_clear_scheduled_hook( 'vyble_cron' );
}
 
add_action('init', function() {
    add_action( 'vyble_cron', 'vyble_getjobs_callback_action' );
    register_deactivation_hook( __FILE__, 'vyble_deactivate' );
 
    if (! wp_next_scheduled ( 'vyble_cron' )) {
        wp_schedule_event( time(), 'hourly', 'vyble_cron' );
    }
});
 

function vyble_joboverview_shortcode( $atts ) 
{
	$args = array(
		'posts_per_page' => 10000000,
		'post_type' => 'cus_vyblejob',
		'orderby' => 'title',
		'order' => 'ASC',
		'post_status' => 'any'
	);
	$jobs = get_posts($args);
	$html = '<div class="vyble_anzeigeouter">';
	$html .= vyble_writevyblestyle();
		foreach ($jobs as $j)
		{
			
			$jm = get_post_meta($j->ID);
			if ($jm['job_status'][0]=='aktiv')
			{
				$data = json_decode($jm['job_data'][0]);
				$html .= '<div class="vyble_anzeige">
					<div class="vyble_stellenname">
					  <span class="vyble_name" style="padding-top: 10px;">'.esc_textarea($data->posting_title).'</span><br>
					  <div class="vyble_beschaeftigungszeit">'.esc_textarea($data->job_type).'<span class="vyble_vonlymobile">, '.esc_textarea(implode('/ ',$data->employing_companies)).'</span></div>

					</div>
					<div class="vyble_standort">
					  <a href="'.get_permalink($j->ID).'" class="vyble_karrierebutton">mehr erfahren</a>
					  <div class="vyble_standortname">'.esc_textarea(implode('/ ',$data->employing_companies)).'</div>
					</div>
					</div>';
			}
		}
	$html .= '</div>';
	return $html;
}
add_shortcode( 'vyble_jobs', 'vyble_joboverview_shortcode' );

add_action('wp_ajax_nopriv_getjobs_action', 'vyble_getjobs_callback_action');
add_action('wp_ajax_getjobs_action', 'vyble_getjobs_callback_action');

add_action('wp_ajax_nopriv_vyble_send_form_action', 'vyble_send_form_action');
add_action('wp_ajax_vyble_send_form_action', 'vyble_send_form_action');

add_filter('single_template', 'vyble_jobs_template');
function vyble_jobs_template($single) {

    global $post;

    /* Checks for single template by post type */
    if ( $post->post_type == 'cus_vyblejob' ) {
		if ( file_exists(  get_template_directory() . '/singlejobcustom.php' ) ) {
            return  get_template_directory() . '/singlejobcustom.php';
        }
        else if ( file_exists(  plugin_dir_path( __FILE__ ) . '/templates/singlejob.php' ) ) {
            return  plugin_dir_path( __FILE__ ) . '/templates/singlejob.php';
        }
    }

    return $single;

}

function vyble_writevyblestyle()
{
	$options = get_option( 'blech_theme_jobtexts' );
	$html = '';
		if (isset($options['btncolor']) && $options['btncolor']!='')
		{
			$html .= '.vyble_vjobdetright .vyble_vsubmit, a.vyble_karrierebutton {background-color:'.$options['btncolor'].';}';
			$html .= '.vyble_vjobdetright .vyble_vsubmit {background-color:'.$options['btncolor'].';border:1px solid '.$options['btncolor'].';}';
			$html .= '.vyble_vjobdetright .vyble_vsubmit.vyble_assissubmit {color:'.$options['btncolor'].';border:1px solid '.$options['btncolor'].';}';
		}
		if (isset($options['btncolorhover']) && $options['btncolorhover']!='')
		{
			$html .= '.vyble_vjobdetright .vyble_vsubmit:HOVER,.vyble_vjobdetright .vyble_vsubmit.vyble_assissubmit:HOVER {background-color:'.$options['btncolorhover'].';border:1px solid #22246c;}';
			$html .= 'a.vyble_karrierebutton:hover {background-color: '.$options['btncolorhover'].';}';
		}
		if (isset($options['customcss']) && $options['customcss']!='')
		{
			$html .= $options['customcss'];
		}			
	$html = wp_kses_post($html);
	return '<style>'.$html.'</style>';
}

function vyble_send_form_action() 
{
	if (isset($_POST['wpjobid']))
	{
		$pm = get_post_meta(sanitize_text_field($_POST['wpjobid']));
		$data = json_decode($pm['job_data'][0]);
		$realfiles = array();
		foreach ($_FILES as $f)
		{
			if ($f['error']==0 && $f['type']=='application/pdf')
			{
				$temp = new StdClass();
				$temp->name = $f['name'];
				$temp->file = base64_encode(file_get_contents($f['tmp_name']));
				$realfiles[] = $temp;
			}
		}
		$token = vyble_getapitoken();
		$json = '{
				"applicant": {          
					"prename": "'.((isset($_POST['vprename'])) ? sanitize_text_field($_POST['vprename']) : '').'",
					"lastname": "'.((isset($_POST['vsurname'])) ? sanitize_text_field($_POST['vsurname']) : '').'",
					"email": "'.((isset($_POST['vemail'])) ? sanitize_email($_POST['vemail']) : '').'",
					"telephone": "'.((isset($_POST['vtel'])) ? sanitize_text_field($_POST['vtel']) : '').'",
					 "internal_note": "'.((isset($_POST['vmessage'])) ? preg_replace( "/\r|\n/", "", sanitize_textarea_field($_POST['vmessage'])) : '').'"
				},
				"application_documents": '.json_encode($realfiles).',
				"application_date": "'.date('Y-m-d').'",
				"status": 1,
				"job_posting": '.$data->id.'
			}
		';

		$options = get_option( 'blech_theme_jobtexts' );

		$url =  'https://'.$options['vybleinstanz'].'/vapi/v1/companies/'.$options['company_id'].'/applications/';
		$result = wp_remote_post($url, array(
			'headers'     => array('Content-Type' => 'application/json; charset=utf-8',"Authorization" => "Token ".$token),
			'body'        => $json,
			'method'      => 'POST',
			'data_format' => 'body',
		));
		$answer = wp_remote_retrieve_body($result);
		
		$message = '<html><body>';
			$message .= '<p>Neue Bewerbung für Job: '.$data->posting_title.'</p>';
			$message .= '<p>Vorname: '.sanitize_text_field($_POST['vprename']).'</p>';
			$message .= '<p>Nachname: '.sanitize_text_field($_POST['vsurname']).'</p>';
			$message .= '<p>E-Mail: '.sanitize_email($_POST['vemail']).'</p>';
			$message .= '<p>Telefon: '.sanitize_text_field($_POST['vtel']).'</p>';
			$message .= '<hr />';
			$message .= '<p>Nachricht:</p>';
			$message .= nl2br(sanitize_textarea_field($_POST['vmessage']));
		$message .= '</body></html>';
		
		#$data->contact_email = 'menz@profectus-webdesign.de';

		$j = 0;
		$atta = array();
		foreach ($_FILES as $key=>$f)
		{
			$j++;
			if ($f['error']==0)
			{
				$file_path = dirname($f['tmp_name']);
				$new_file_uri = $file_path.'/'.$j.'_'.$f['name'];
				$moved = move_uploaded_file($f['tmp_name'], $new_file_uri);
				$attachment_file = $moved ? $new_file_uri : $f['tmp_name'];
				$atta[] = $attachment_file;
			}
		}
		$headers = array('Content-Type: text/html; charset=UTF-8','From: neue-bewerbung@vyble.io');
		wp_mail($data->contact_email, 'Neue Bewerbung '.$data->posting_title, $message, $headers,$atta );
		foreach ($atta as $a)
		{
			unlink($a);
		}
		
		$upload_dir = wp_upload_dir();
		$logdir = $upload_dir['basedir'].'/vyble_logs';
		if (!is_dir($logdir)) 
		{
			mkdir($logdir);       
		}
		
		$fp = fopen($logdir.'/'.sanitize_file_name($_POST['vprename'].'-'.$_POST['vsurname']).'-'.$answer[1].'-'.date('Y-m-d-H-i-s').'.txt', 'w');
		fwrite($fp, $url);
		fwrite($fp, $json);
		fwrite($fp, $answer[0]);
		fclose($fp);
		
	}
}

function vyble_getvcompanies()
{
	$options = get_option( 'blech_theme_jobtexts' );
	$token = vyble_getapitoken();
	$url =  'https://'.$options['vybleinstanz'].'/vapi/v1/companies/';
	$result = wp_remote_get($url, array(
		'headers'     => array("Authorization" => "Token ".$token),
		'method'      => 'GET'
	));
	$answer = wp_remote_retrieve_body($result);
	$companies = json_decode($answer);
	return $companies;
}

function vyble_getapitoken()
{
	$options = get_option( 'blech_theme_jobtexts' );
	$url = 'https://'.$options['vybleinstanz'].'/vapi/v1/auth/authenticate';
	
	$json = '{
	  "apikey": "'.$options['apikey'].'"
	}';
	
	$json = new StdClass();
	$json->apikey = $options['apikey'];
	
	$result = wp_remote_post($url, array(
		'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
		'body'        => json_encode($json),
		'method'      => 'POST',
		'data_format' => 'body',
	));
	$body = wp_remote_retrieve_body($result);
	
	$result = json_decode($body);
	return $result->token;
}

function vyble_getjobs_callback_action()
{
	$token = vyble_getapitoken();
	$options = get_option( 'blech_theme_jobtexts' );

	$url =  'https://'.$options['vybleinstanz'].'/vapi/v1/companies/'.$options['company_id'].'/job-postings';
	$result = wp_remote_get($url, array(
		'headers'     => array("Authorization" => "Token ".$token),
		'method'      => 'GET'
	));
	$answer = wp_remote_retrieve_body($result);
	$jobs = json_decode($answer);
	if ($jobs && is_array($jobs))
	{
		$args = array(
			'posts_per_page' => 10000000,
			'post_type' => 'cus_vyblejob',
			'orderby' => 'title',
			'order' => 'ASC',
			'post_status' => 'any'
		);
		
		$exjobs = get_posts($args);
		
		
		foreach ($exjobs as $key=>$value)
		{
			$exjobs[$key]->meta = get_post_meta($value->ID);
		}
		
		foreach ($jobs as $a)
		{
			foreach ($exjobs as $key=>$j)
			{
				if ($j->meta['job_vybleid'][0]==$a->id)
				{
					unset($exjobs[$key]);
				}
			}
			
			
			$args = array(		
				'meta_key' => 'job_vybleid',
				'meta_value' => $a->id,
				'post_type' => 'cus_vyblejob'
			);	
			$existingpost = new WP_Query($args);
			$existingpost = $existingpost->get_posts();
			$html = false;
			foreach ($a as $nkey=>$nval)
			{
				if (is_string($nval))
				{
					if (strpos($nval,'<li>')===false && strpos($nval,'<div>')===false && strpos($nval,'<p>')===false && strpos($nval,'</a>')===false)
					{
						$nval = str_replace(PHP_EOL,'<br />',$nval);
						$nval = str_replace("\r\n",'<br />',$nval);
						$nval = str_replace("\r",'',$nval);
						$a->$nkey = $nval;
					}
					else
					{
						$nval = str_replace(PHP_EOL,'',$nval);
						$nval = str_replace("\r\n",'',$nval);
						$nval = str_replace("\r",'',$nval);
						$a->$nkey = $nval;
						$html = true;
					}
				}
			}
			
			if (!$html)
			{
				$json = str_replace(PHP_EOL,'<br />',json_encode($a,JSON_UNESCAPED_UNICODE));
			}
			else
			{
				$json = str_replace(PHP_EOL,' ',json_encode($a,JSON_UNESCAPED_UNICODE));
			}
			#echo $json;die();
			
			//IF JOB DOES NOT EXIST
			if (count($existingpost)==0)
			{
				
				if ($a->status=='Aktiv')
				{
					$tpost = array(
					  'comment_status' => 'closed',
					  'ping_status'    => 'closed',
					 
					  'post_name'      => sanitize_text_field($a->posting_title),
					  'post_status'    => 'publish',
					  'post_title'     => sanitize_text_field($a->posting_title),
					  'post_type'      => 'cus_vyblejob'
					);
				
				
				
				
					$id=wp_insert_post( $tpost );
					
					//SET META
					add_post_meta($id, 'job_vybleid', $a->id, true);
					add_post_meta($id, 'job_data', wp_slash($json), true);
					add_post_meta($id, 'job_status', strtolower($a->status), true);
					#add_post_meta($id, '_yoast_wpseo_title', $a['Title'].' '.implode(',',$a['Locations']), true);
					#add_post_meta($id, '_yoast_wpseo_canonical', get_permalink($id), true);
					$i++;
				}
			}
			else
			{
				$id = $existingpost[0]->ID;
				
				if ($a->status=='Inaktiv')
				{
					wp_delete_post($id);
				}
				else if ($a->status=='Aktiv')
				{
					$tpost = array(
					  'comment_status' => 'closed',
					  'ping_status'    => 'closed',
					 
					  'post_name'      => sanitize_text_field($a->posting_title),
					  'post_status'    => 'publish',
					  'post_title'     => sanitize_text_field($a->posting_title),
					  'post_type'      => 'cus_vyblejob',
					  'ID'			   => $id
					);
					
					wp_update_post( $tpost );
				
					$jm = get_post_meta($id);
					
					
					update_post_meta($id, 'job_vybleid', $a->id);
					update_post_meta($id, 'job_data', wp_slash($json));
					update_post_meta($id, 'job_status', strtolower($a->status));
				}
				
				
				
				/*
				update_post_meta($id, '_yoast_wpseo_meta-robots-noindex', 2);
				if ($jm['_yoast_wpseo_title'][0]=='')
				{
					update_post_meta($id, '_yoast_wpseo_title', $a['Title'].' '.implode(',',$a['Locations']));
				}
				if ($jm['_yoast_wpseo_metadesc'][0]=='')
				{
					//update_post_meta($id, '_yoast_wpseo_metadesc', $a['Title'].' '.implode(',',$a['Locations']));
				}
				update_post_meta($id, '_yoast_wpseo_canonical', get_permalink($id));
				*/
			}
		}
		
		foreach ($exjobs as $key=>$j)
		{
			$id = $j->ID;
			wp_delete_post($id);
			#update_post_meta($j->ID, '_yoast_wpseo_meta-robots-noindex', 1);
		}
	}
	die('Jobs erfolgreich aktualisiert!');
}

add_action('admin_head', 'vyble_embedUploaderCode');
add_action('admin_print_scripts', 'vyble_my_admin_scripts');
add_action('admin_print_styles', 'vyble_my_admin_styles');

function vyble_my_admin_scripts() {
	wp_enqueue_script('media-upload');
	wp_enqueue_script('thickbox');
}
 
function vyble_my_admin_styles() {
	wp_enqueue_style('thickbox');
}


function vyble_embedUploaderCode()
{
  ?>
 
  <script type="text/javascript">
 
  
  jQuery(document).ready(function() {
	jQuery('.removeImageBtn').click(function() {
      jQuery(this).closest('p').prev('.awdMetaImage').html('');   
      jQuery(this).prev().prev().val('');
      return false;
    });
 
    jQuery('.image_upload_button').click(function() {
      inputField = jQuery(this).prev('.metaValueField');
      tb_show('', 'media-upload.php?TB_iframe=true');
      window.send_to_editor = function(html) {
		 url = jQuery(html).attr('href');
        inputField.val(url);
        inputField.closest('p').prev('.awdMetaImage').html('<p>URL: '+ url + '</p>');  
        tb_remove();
      };
      return false;
    });
  });
 
  </script>
  <?php
}




function vyble_boxtemplate($fields)
{
	global $post;
	echo '<input type="hidden" name="custom_meta_box_nonce" value="'.wp_create_nonce(basename(__FILE__)).'" />';  
      
    // Begin the field table and loop  
    echo '<table class="form-table">';  
    foreach ($fields as $field) {  
        // get value of this field if it exists for this post  
        $meta = get_post_meta($post->ID, $field['id'], true);  
		$meta = esc_textarea($meta);
		// begin a table row with  
        echo '<tr> 
                <th><label for="'.esc_attr($field['id']).'">'.$field['label'].'</label></th> 
                <td>';  
                switch($field['type']) {  
                    case 'text':  
						echo '<input style="width:100%;" type="text" name="'.esc_attr($field['id']).'" id="'.esc_attr($field['id']).'" value="'.$meta.'" size="30" /> 
							<br /><span class="description">'.wp_kses_post($field['desc']).'</span>';  
					break; 
					case 'readonly':  
						echo '<input readonly type="text" name="'.esc_attr($field['id']).'" id="'.esc_attr($field['id']).'" value="'.$meta.'" size="30" /> 
							<br /><span class="description">'.wp_kses_post($field['desc']).'</span>';  
					break; 
					case 'textarea':  
						echo '<textarea style="width:100%;height:100px;" name="'.esc_attr($field['id']).'" id="'.esc_attr($field['id']).'">'.$meta.'</textarea>
							<br /><span class="description">'.wp_kses_post($field['desc']).'</span>';  
					break; 
					case 'hiddentextarea':  
						echo '<div style="display:none;"><textarea style="width:100%;height:100px;" name="'.esc_attr($field['id']).'" id="'.esc_attr($field['id']).'">'.$meta.'</textarea>
							<br /><span class="description">'.wp_kses_post($field['desc']).'</span></div>';  
					break; 
					// select
					case 'select':
						echo '<select name="'.esc_attr($field['id']).'" id="'.esc_attr($field['id']).'">';
						foreach ($field['options'] as $option) {
							echo '<option', $meta == $option['value'] ? ' selected="selected"' : '', ' value="'.$option['value'].'">'.$option['label'].'</option>';
						}
						echo '</select><br /><span class="description">'.wp_kses_post($field['desc']).'</span>';
					break;
					// selectmulti
					case 'selectmulti':
						echo '<select name="'.esc_attr($field['id']).'[]" id="'.esc_attr($field['id']).'" style="height:200px;" multiple>';
						
						foreach ($field['options'] as $option) {
							
							echo '<option', (is_array($meta) && in_array($option['value'],$meta)) ? ' selected="selected"' : '', ' value="'.$option['value'].'">'.$option['label'].'</option>';
						}
						echo '</select><br /><span class="description">'.wp_kses_post($field['desc']).'</span>';
					break;
					case 'media':
						echo '<div class="awdMetaImage">';
							echo '<input class="metaValueField" type="text" name="'.esc_attr($field['id']).'" id="'.esc_attr($field['id']).'" value="'.$meta.'" />';
							echo '<input class="image_upload_button" type="button" id="meta-image-button_banner_mainmedia" class="button" value="auswählen" /><br /><span class="description">'.wp_kses_post($field['desc']).'</span>';
						echo '</div>';
					break;
					case 'wysiwyg':  
						wp_editor($meta,$field['id']);
						echo '<br /><span class="description">'.wp_kses_post($field['desc']).'</span>';  
					break; 
					case 'label':  
						echo '<hr />';  
					break; 
                } //end switch  
        echo '</td></tr>';  
    } // end foreach  
    echo '</table>'; // end table 
}

function vyble_boxsavetemplate($fields)
{
	global $post;  
	if (isset($post->ID))
	{
		$post_id = $post->ID;
    }
	else
	{
		return;
	}
    // verify nonce 
	if(isset($_POST['custom_meta_box_nonce']))
	{
		if (!wp_verify_nonce(sanitize_text_field($_POST['custom_meta_box_nonce']), basename(__FILE__)))   
			return $post_id;  
	}
    // check autosave  
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)  
        return $post_id;  
    // check permissions  
	if(isset($_POST['post_type']))
	{
		if ('page' == sanitize_text_field($_POST['post_type'])) {  
			if (!current_user_can('edit_page', $post_id))  
				return $post_id;  
			} elseif (!current_user_can('edit_post', $post_id)) {  
				return $post_id;  
		} 
	}
      
    // loop through fields and save the data  
    foreach ($fields as $field) {  
        if(isset($_POST[$field['id']]))
		{
			$old = get_post_meta($post_id, $field['id'], true);  
			$new = sanitize_text_field($_POST[$field['id']]);  
			if ($new && $new != $old) {  
				update_post_meta($post_id, $field['id'], $new);  
			} elseif ('' == $new && $old) {  
				delete_post_meta($post_id, $field['id'], $old);  
			}
		}
    } // end foreach  
}  

