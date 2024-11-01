<?php

add_action( 'admin_init', 'vyble_theme_texts_init' );
add_action( 'admin_menu', 'vyble_theme_texts_add_page' );

function vyble_theme_texts_init(){
	register_setting( 'blech_texts-1', 'blech_theme_jobtexts' );

	
}

function vyble_theme_texts_add_page() {
	add_menu_page('vyble® Recruiting Einstellungen', 'vyble® Recruiting Einstellungen', 'publish_posts', 'theme-texts-1', 'vyble_blech_theme_job_page' ); 
	
}


function vyble_blech_theme_job_page() 
{
	global $select_options, $radio_options;




	if (!isset( $_REQUEST['settings-updated']))
	{
		$_REQUEST['settings-updated'] = false;
	}
	?>

	<div class="wrap"> 
	<h2>Einstellungen</h2> 

	<?php 
	if ( false !== $_REQUEST['settings-updated'] ) 
	{ 
	?> 
		<div class="updated fade">
			<p><strong>Einstellungen gespeichert!</strong></p>
		</div>
	<?php 
	}
	?>

	  <form method="post" action="options.php" >
		<?php settings_fields( 'blech_texts-1' ); ?>
		<?php $options = get_option( 'blech_theme_jobtexts' ); ?>
		
		<!-- submit -->
		<p class="submit"><input type="submit" class="button-primary" value="Einstellungen speichern" /></p>
		
		<div style="background-color:#FFF;padding:20px;border:2px solid #000;margin-top:20px;">
	   
			<h2>Inhalte</h2>
			
			<table class="form-table">
				<tr valign="top">
					<th scope="row">vyble Instanz - kompletter Hostname, ohne https://</th>
					<td><input type="text" style="width:100%" id="blech_theme_jobtexts[vybleinstanz]" placeholder="Text" name="blech_theme_jobtexts[vybleinstanz]" value="<?php echo esc_textarea( $options['vybleinstanz'] ); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">apikey</th>
					<td><input type="text" style="width:100%" id="blech_theme_jobtexts[apikey]" placeholder="Text" name="blech_theme_jobtexts[apikey]" value="<?php echo esc_textarea( $options['apikey'] ); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">Company</th>
					<td>
						<?php
							if (isset($options['apikey']) && $options['apikey']!='' && isset($options['vybleinstanz']) && $options['vybleinstanz']!='')
							{
								$companies = vyble_getvcompanies();
								#print_r($companies);
						?>
								<select id="blech_theme_jobtexts[company_id]" style="width:100%;max-width:none;" name="blech_theme_jobtexts[company_id]">
									<option value="0">Bitte auswählen</option>
									<?php
										foreach ($companies as $c)
										{
											$sel = '';
											if ($c->id==$options['company_id'])
											{
												$sel = 'selected';
											}
									?>
											<option value="<?php echo esc_attr($c->id); ?>" <?php echo $sel; ?>><?php echo esc_attr($c->name);?></option>
									<?php
										}
									?>
								</select>
						<?php
							}
							else
							{
						?>
								Bitte vyble Instanz und apikey eingeben!
						<?php
							}
						?>
						</td>
				</tr>
				<tr valign="top">
					<th scope="row">Überschrift Bewerbungsformular</th>
					<td><input type="text" style="width:100%" id="blech_theme_jobtexts[reqformhead]" placeholder="Text" name="blech_theme_jobtexts[reqformhead]" value="<?php echo esc_textarea( $options['reqformhead'] ); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">Einleitungstext Bewerbungsformular</th>
					<td><textarea style="width:100%;height:150px;" id="blech_theme_jobtexts[reqformtext]" placeholder="Text" name="blech_theme_jobtexts[reqformtext]"><?php echo esc_textarea( $options['reqformtext'] ); ?></textarea></td>
				</tr>
				<tr valign="top">
					<th scope="row">URL Datenschutzerklärung</th>
					<td><input type="text" style="width:100%" id="blech_theme_jobtexts[urlds]" placeholder="Text" name="blech_theme_jobtexts[urlds]" value="<?php echo esc_textarea( $options['urlds'] ); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">E-Mail Datenschutz-Widerruf</th>
					<td><input type="text" style="width:100%" id="blech_theme_jobtexts[emailds]" placeholder="Text" name="blech_theme_jobtexts[emailds]" value="<?php echo esc_textarea( $options['emailds'] ); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">Farbe Buttons (Standardfarbe, wenn nichts ausgewählt ist: #00ab9d)</th>
					<td><input type="color" style="width:100%" id="blech_theme_jobtexts[btncolor]" placeholder="Text" name="blech_theme_jobtexts[btncolor]" value="<?php echo esc_textarea( $options['btncolor'] ); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">Farbe Buttons Mouseover (Standardfarbe, wenn nichts ausgewählt ist: #22246c)</th>
					<td><input type="color" style="width:100%" id="blech_theme_jobtexts[btncolorhover]" placeholder="Text" name="blech_theme_jobtexts[btncolorhover]" value="<?php echo esc_textarea( $options['btncolorhover'] ); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">individuelles CSS</th>
					<td><textarea style="width:100%;height:150px;" id="blech_theme_jobtexts[customcss]" placeholder="Text" name="blech_theme_jobtexts[customcss]"><?php echo esc_textarea( $options['customcss'] ); ?></textarea></td>
				</tr>
				<tr valign="top">
					<th scope="row">Ansprechpartner Überschrift (wenn leer, wird Ansprechpartner unter Bewerbungsformular nicht angezeigt)</th>
					<td><input type="text" style="width:100%" id="blech_theme_jobtexts[anhead]" placeholder="Text" name="blech_theme_jobtexts[anhead]" value="<?php echo esc_textarea( $options['anhead'] ); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">Ansprechpartner-Bild</th>
					<td><div class="awdMetaImage"><input class="metaValueField" type="text" name="blech_theme_jobtexts[animg]" id="blech_theme_jobtexts[animg]" value="<?php echo esc_textarea( $options['animg'] ); ?>" /><input class="image_upload_button" type="button" id="meta-image-button_banner_mainmedia" class="button" value="auswählen" /></div></td>
				</tr>
				<tr valign="top">
					<th scope="row">Ansprechpartner Name</th>
					<td><input type="text" style="width:100%" id="blech_theme_jobtexts[anname]" placeholder="Text" name="blech_theme_jobtexts[anname]" value="<?php echo esc_textarea( $options['anname'] ); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">Ansprechpartner Position</th>
					<td><input type="text" style="width:100%" id="blech_theme_jobtexts[anpos]" placeholder="Text" name="blech_theme_jobtexts[anpos]" value="<?php echo esc_textarea( $options['anpos'] ); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">Ansprechpartner E-Mail-Adresse</th>
					<td><input type="text" style="width:100%" id="blech_theme_jobtexts[anemail]" placeholder="Text" name="blech_theme_jobtexts[anemail]" value="<?php echo esc_textarea( $options['anemail'] ); ?>" /></td>
				</tr>
			</table>
			
			
		</div>

		
		<!-- submit -->
		<p class="submit"><input type="submit" class="button-primary" value="Einstellungen speichern" /></p>
		<a href="<?php admin_url('admin-ajax.php');?>?action=getjobs_action" class="submit" target="_blank"><input type="button" class="button-primary" value="Jobs aktualisieren" /></a>
	  </form>
	 
	</div>
<?php 
	} 
?>