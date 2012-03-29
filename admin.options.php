	<div class="wrap">
		<div id="icon-options-general" class="icon32"></div>
		<h2><?php _e( 'Events Calendar Options', 'extevtcal_plugin' ); ?></h2>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery("#extevtcal-custom_formatting").change(function(evt) {
					if (evt.currentTarget.checked) {
						jQuery("#extevtcal-customformat").removeAttr('disabled');
					}
				});
			});
		</script>
		<form method="POST" action="options.php">
		<?php settings_fields( 'extevtcal-settings' );
		$setting_date_formatting = get_option( 'extevtcal_date_formatting' );
		$setting_date_customformat = get_option( 'extevtcal_date_customformat' );
		$setting_link_position = get_option( 'extevtcal_link_position' );
		$setting_use_css = get_option( 'extevtcal_use_css' );
		$setting_currentday_behavior = get_option( 'extevtcal_currentday_behavior' );
		//dummy values to demonstrate date formats
		$timezone = ( get_option( 'timezone_string' ) ) ? get_option( 'timezone_string' ) : date( 'e' );
		date_default_timezone_set( $timezone );
		$today = date( 'F j, Y g:ia' );
		$later = date( 'F j, Y g:ia', time( ) + 1014400 ); ?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php _e( 'Date format to use:', 'extevtcal_plugin' ); ?></th>
					<td>
						<table cellpadding="0" cellspacing="0">
							<tr valign="top">
								<td style="padding-top:3px;">
									<input type="radio"
										   name="extevtcal_date_formatting"
										   id="extevtcal-builtin"
										   value="builtin" <?php
				checked( $setting_date_formatting, "builtin" ); ?>/></td>
								<td style="margin:0;padding:0;"><label
										for="extevtcal-builtin"><?php
				echo __( 'Use dates/times as entered (may not work well with non-US English date formats)', 'extevtcal_plugin' ) . '<br /><em>';
								echo __( 'Example', 'extevtcal_plugin' ) . ': ' . processDateListing( $today, $later, false ); ?></em></label>
								</td>
							</tr>
							<tr valign="top">
								<td style="padding-top:3px;"><input type="radio"
																	name="extevtcal_date_formatting"
																	id="extevtcal-WP_setting"
																	value="WP_setting" <?php
				checked( $setting_date_formatting, "WP_setting" ); ?>/></td>
								<td style="margin:0;padding:0;"><label
										for="extevtcal-WP_setting"><?php
				echo __( 'Use localized WordPress date format setting', 'extevtcal_plugin' ) . '<br /><em>';
								echo __( 'Example', 'extevtcal_plugin' ) . ': ' . processDateListing( $today, $later, get_option( 'date_format' ) ) . '<br /></em>'; ?></label>
								</td>
							</tr>
							<tr valign="top">
								<td style="padding-top:3px;"><input type="radio"
																	name="extevtcal_date_formatting"
																	id="extevtcal-WP_setting_time"
																	value="WP_setting_time" <?php
				checked( $setting_date_formatting, "WP_setting_time" ); ?>/>
								</td>
								<td style="margin:0;padding:0;"><label
										for="extevtcal-WP_setting_time"><?php
				echo __( 'Use localized WordPress date format setting with time', 'extevtcal_plugin' ) . '<br /><em>';
								echo __( 'Example', 'extevtcal_plugin' ) . ': ' . processDateListing( $today, $later, get_option( 'links_updated_date_format' ) ) . '<br /></em>'; ?></label>
								</td>
							</tr>
							<tr valign="top">
								<td style="padding-top:3px;"><input type="radio"
																	name="extevtcal_date_formatting"
																	id="extevtcal-custom_formatting"
																	value="custom_formatting" <?php
				checked( $setting_date_formatting, "custom_formatting" ); ?>/>
								</td>
								<td style="margin:0;padding:0;"><label for="extevtcal-custom_formatting"><?php
				echo __( 'Custom date/time formatting (use standard PHP date formats)', 'extevtcal_plugin' ) . '<br />'; ?></label>
									<input type="text" id="extevtcal-customformat"
										   name="extevtcal_date_customformat"
										   value="<?php echo ( !empty( $setting_date_customformat ) ) ? $setting_date_customformat : get_option( 'date_format' ); ?>"
									<?php disabled( $setting_date_formatting != 'custom_formatting' ); ?> />
									<label for="extevtcal-customformat"><?php echo '<a href="' . __( 'http://php.net/manual/en/function.date.php' ) . '" target="_blank">' .
											__( 'See available formats on the PHP Manual' ) . '</a>'; ?></label>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Wrap link around:', 'extevtcal_plugin' ); ?></th>
					<td>
						<table cellpadding="0" cellspacing="0">
							<tr valign="top">
								<td style="padding-top:3px;"><input type="radio"
																	name="extevtcal_link_position"
																	id="extevtcal-titleonly"
																	value="title_only" <?php checked( $setting_link_position, "title_only" ); ?>/>
								</td>
								<td style="margin:0;padding:0;"><label for="extevtcal-titleonly"><?php
				echo __( 'Wrap link around title only (standard)', 'extevtcal_plugin' ); ?></label></td>
							</tr>
							<tr valign="top">
								<td style="padding-top:3px;"><input type="radio"
																	name="extevtcal_link_position"
																	id="extevtcal-entire_li"
																	value="entire_li" <?php checked( $setting_link_position, "entire_li" ); ?>/>
								</td>
								<td style="margin:0;padding:0;"><label for="extevtcal-entire_li"><?php
				echo __( 'Wrap link around entire &lt;li&gt; (for block hover effects)', 'extevtcal_plugin' ); ?></label></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Use default CSS styling:', 'extevtcal_plugin' ); ?></th>
					<td>
						<table cellpadding="0" cellspacing="0">
							<tr valign="top">
								<td style="padding-top:3px;"><input type="radio"
																	name="extevtcal_use_css"
																	id="extevtcal-use-css-true"
																	value="1" <?php if ($setting_use_css == true) echo 'checked="checked" '; ?>/>
								</td>
								<td style="margin:0;padding:0;"><label for="extevtcal-use-css-true"><?php
				printf( __( 'Include (see %s for styles defined)', 'extevtcal_plugin' ), '<a href="' . EXTEVTCAL_PLUGIN_DIR . '/gad-events-calendar.css">' . EXTEVTCAL_PLUGIN_DIR . '/gad-events-calendar.css</a>' ); ?></label></td>
							</tr>
							<tr valign="top">
								<td style="padding-top:3px;"><input type="radio"
																	name="extevtcal_use_css"
																	id="extevtcal-use-css-false"
																	value="0" <?php if ($setting_use_css != true) echo 'checked="checked" '; ?>/>
								</td>
								<td style="margin:0;padding:0;"><label for="extevtcal-use-css-false"><?php
				echo __( 'Do not include default css styles (Note: make sure to define your own)', 'extevtcal_plugin' ); ?></label></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Current day behavior:', 'extevtcal_plugin' ); ?></th>
					<td><?php echo __( 'Note: settings defined here apply only to listings of upcoming events. Listings including past events will not be affected by these settings.' ); ?>
						<br>
						<table cellpadding="0" cellspacing="0"
							   style="margin-top: 6px;">
							<tr valign="top">
								<td style="padding-top:3px;">
									<input type="radio" id="extevtcal-currentday_default"
										   name="extevtcal_currentday_behavior"
										   value="default"
									<?php checked( empty( $setting_currentday_behavior ) || ( $setting_currentday_behavior == 'default' ) ); ?> />
								</td>
								<td style="margin:0;padding:0;"><label
										for="extevtcal-currentday_default"><?php echo __( 'Events drop off the calendar once the start time is past (default)', 'extevtcal_plugin' ); ?>
									<br>
									<em><?php echo __( 'Note: events without a time specified will drop off the calendar at midnight of the date specified.' ); ?></em>
								</label>
								</td>
							</tr>
							<tr valign="top">
								<td style="padding-top:3px;">
									<input type="radio" id="extevtcal-currentday_enddate"
										   name="extevtcal_currentday_behavior"
										   value="enddate"
									<?php checked( $setting_currentday_behavior == 'enddate' ); ?> />
								</td>
								<td style="margin:0;padding:0;"><label
										for="extevtcal-currentday_enddate"><?php echo __( 'Events drop off the calendar once the end date is past', 'extevtcal_plugin' ); ?></label>
								</td>
							</tr>
							<tr valign="top">
								<td style="padding-top:3px;">
									<input type="radio" id="extevtcal-currentday_today"
										   name="extevtcal_currentday_behavior"
										   value="today"
									<?php checked( $setting_currentday_behavior == 'today' ); ?> />
								</td>
								<td style="margin:0;padding:0;"><label
										for="extevtcal-currentday_today"><?php echo __( "Show all the current day's events", 'extevtcal_plugin' ); ?></label>
								</td>
							</tr>
							<tr valign="top">
								<td style="padding-top:3px;">
									<input type="radio" id="extevtcal-currentday_thisweek"
										   name="extevtcal_currentday_behavior"
										   value="thisweek"
									<?php checked( $setting_currentday_behavior == 'thisweek' ); ?> />
								</td>
								<td style="margin:0;padding:0;"><label
										for="extevtcal-currentday_thisweek"><?php echo __( 'Show all events from the current week', 'extevtcal_plugin' ); ?></label>
								</td>
							</tr>

						</table>
					</td>
				</tr>

				<tr>
					<th scope="row"></th>
					<td><p class="submit">
						<input type="submit" class="button-primary"
							   value="<?php _e( 'Save Changes' ) ?>"/>
					</p></td>
				</tr>
			</table>


		</form>
	</div>