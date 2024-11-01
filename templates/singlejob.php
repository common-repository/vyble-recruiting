<?php
	get_header();
	$post = get_post(get_the_id());
	$postmeta = get_post_meta(get_the_id());
	$data = json_decode($postmeta['job_data'][0]);
	$voptions = get_option( 'blech_theme_jobtexts' );
	#print_r($data);
	echo vyble_writevyblestyle();
?>
	<div class="vyble_vjobdetails">
		<div class="vyble_jobahead"><?php echo esc_textarea($data->job_type); ?>, <?php echo esc_textarea(implode('/ ',$data->employing_companies));?></div>
		<h1><?php echo esc_textarea($data->posting_title);?></h1>
		<div class="vyble_vjobdetleft">
			<h2><?php echo esc_textarea($data->introduction_title);?></h2>
			<div class="vyble_vtext"><?php echo wp_kses_post($data->introduction_text);?></div>
			<h2><?php echo esc_textarea($data->company_description_title);?></h2>
			<div class="vyble_vtext"><?php echo wp_kses_post($data->company_description_text);?></div>
			<h2><?php echo esc_textarea($data->profile_description_title);?></h2>
			<div class="vyble_vtext"><?php echo wp_kses_post($data->profile_description_text);?></div>
			<h2><?php echo esc_textarea($data->company_advantages_title);?></h2>
			<div class="vyble_vtext"><?php echo wp_kses_post($data->company_advantages_text);?></div>
			<h2><?php echo esc_textarea($data->contact_data_title);?></h2>
			<div class="vyble_vtext"><?php echo wp_kses_post($data->contact_data_text);?></div>
		</div>
		<div class="vyble_vjobdetright">
			<div class="vyble_vjobdetrightbox">
				<h2><?php echo esc_textarea($voptions['reqformhead']);?></h2>
				<p>
					<?php echo nl2br(esc_textarea($voptions['reqformtext']));?>
				</p>
				<form id="vyble_vdata" method="post" enctype="multipart/form-data">
					<p>
						<input type="text" name="vprename" id="vprename" value="" size="30" maxlength="60" class="" placeholder="Vorname*" />
					</p>
					<p>
						<input type="text" name="vsurname" id="vsurname" value="" size="30" maxlength="60" class="" placeholder="Nachname*" />
					</p>
					<p>
						<input type="email" name="vemail" id="vemail" value="" size="30" maxlength="60" class="" placeholder="E-Mail*" />
					</p>
					<p>
						<input type="text" name="vtel" id="vtel" value="" size="30" maxlength="60" class="" placeholder="Telefonnummer*" />
					</p>
					<p>
						<textarea rows="6" name="vmessage" placeholder="Nachricht" id="vmessage"></textarea> 
					</p>
					<div class="vyble_vupload">
						<label>Anschreiben (PDF max. 5MB)</label><br>
						<input type="file" name="anschreiben" id="anschreiben" size="40" accept=".pdf" />
					</div>
					<div class="vyble_vupload">
						<label>Lebenslauf (PDF max. 5MB)*</label><br>
						<input type="file" name="lebenslauf" id="lebenslauf" size="40" accept=".pdf" />
					</div>
					<div class="vyble_vupload vyble_vlast">
						<label>Zeugnisse (PDF max. 5MB)</label><br>
						<input type="file" name="zeugnisse" id="zeugnisse" size="40" accept=".pdf" />
					</div>
					<p class="vyble_vstarinfo">Mit "*" markierte Felder sind Pflicht.</p>
					<div class="vyble_vdscheck">
						<input type="checkbox" name="vds" id="vds" value="" />Ja, ich stimme zu, dass meine Angaben aus dem Kontaktformular zur Beantwortung meiner Anfrage erhoben und verarbeitet werden. Hinweis: Sie können Ihre Einwilligung jederzeit für die Zukunft per E-Mail an <?php echo esc_textarea($voptions['emailds'])?> widerrufen. Detaillierte Informationen zum Umgang mit Nutzerdaten finden Sie in unserer <a href="<?php echo esc_url($voptions['urlds'])?>" target="_blank">Datenschutzerklärung</a>.
					</div>
					<div class="vyble_vformfeedback"></div>
					<input type="button" value="Jetzt Bewerbung absenden" class="vyble_vsubmit" onclick="submitvform();" />
					<?php
						if ($data->job_info_url!='')
						{
					?>
							<a href="<?php echo esc_attr($data->job_info_url);?>" target="_blank" class="vyble_vsubmit vyble_assissubmit">Bewerbungsassistent starten</a>
					<?php
						}
					?>
					<input type="hidden" value="<?php echo get_the_id();?>" name="wpjobid" />
				</form>
			</div>
			<?php
				if ($voptions['anhead']!='')
				{
			?>
					<div class="vyble_vjobdetrightbox">
						<h2><?php echo esc_textarea($voptions['anhead']);?></h2>
						<div class="vyble_anleftimg">
							<img src="<?php echo esc_url($voptions['animg'])?>" alt="Ansprechpartner" />
						</div>
						<div class="vyble_anright">
							<?php echo esc_textarea($voptions['anname']);?><br />
							<?php echo esc_textarea($voptions['anpos']);?><br />
							<a href="mailto:<?php echo esc_textarea($voptions['anemail']);?>"><?php echo esc_textarea($voptions['anemail']);?></a>
						</div>
						<div class="vyble_vclear"></div>
					</div>
			<?php
				}
			?>
		</div>
		<div class="vyble_vclear"></div>
	</div>
<?php
	get_footer();
?>