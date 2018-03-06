<?php
/**
 * Adds settings to the permalinks admin settings page
 *
 * @class       GeoDir_Admin_Permalink_Settings
 * @author      AyeCode
 * @category    Admin
 * @package     GeoDirectory/Admin
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'GeoDir_Admin_Permalink_Settings', false ) ) :

	/**
	 * GeoDir_Admin_Permalink_Settings Class.
	 */
	class GeoDir_Admin_Permalink_Settings {

		/**
		 * Permalink settings.
		 *
		 * @var string
		 */
		private $permalinks = '';

		/**
		 * Hook in tabs.
		 */
		public function __construct() {
			$this->settings_init();
			$this->settings_save();
		}

		/**
		 * Init our settings.
		 */
		public function settings_init() {
			// Add a section to the permalinks page
			add_settings_section( 'geodir-permalink', __( 'GeoDirectory permalinks', 'geodirectory' ), array(
				$this,
				'settings'
			), 'permalink' );


			$this->permalinks = geodir_get_permalink_structure();
		}


		/**
		 * Show the settings.
		 */
		public function settings() {
			echo wpautop( __( 'These settings control the permalinks used specifically for GeoDirectory CPTs', 'geodirectory' ) );

			// Get shop page
			$base_slug    = geodir_get_ctp_slug( 'gd_place' );
			$default_location = geodir_get_default_location();
			//print_r($default_location);


			$structures = array(
				__( 'Default', 'geodirectory' )     => array(
					'value' => '',
					'example' => esc_html( home_url() ).'/'. esc_html( $base_slug ).'/sample-place/'
				),
				__( 'Full location', 'geodirectory' )     => array(
					'value' => '/%country%/%region%/%city%/%postname%/',
					'example' => esc_html( home_url() ) . '/' . trailingslashit( $base_slug ) .
					             trailingslashit( $default_location->country_slug ).
					             trailingslashit( $default_location->region_slug ).
					             trailingslashit( $default_location->city_slug ) . 'sample-place/'
				),
				__( 'Full location with category', 'geodirectory' )     => array(
					'value' => '/%country%/%region%/%city%/%category%/%postname%/',
					'example' => esc_html( home_url() ) . '/' . trailingslashit( $base_slug ) .
					             trailingslashit( $default_location->country_slug ).
					             trailingslashit( $default_location->region_slug ).
					             trailingslashit( $default_location->city_slug ) .
					             'attractions/' .
					             'sample-place/'
				)
			);

			$is_default = false;
			
			$available_tags = array(
				/* translators: %s: permalink structure tag */
				'country'     => __( '%s (Country slug of the post. Ex: united-states.)', 'geodirectory' ),
				/* translators: %s: permalink structure tag */
				'region' => __( '%s (Region slug of the post. Ex: pennsylvania.)', 'geodirectory' ),
				/* translators: %s: permalink structure tag */
				'city'      => __( '%s (City slug of the post. Ex: philadelphia.)', 'geodirectory' ),
				/* translators: %s: permalink structure tag */
				'category' => __( '%s (Category slug. Nested sub-categories appear as nested directories in the URL.)', 'geodirectory' ),
				/* translators: %s: permalink structure tag */
				'postname' => __( '%s (The sanitized post title (slug).)', 'geodirectory' ),
				/* translators: %s: permalink structure tag */
				'post_id'  => __( '%s (The unique ID of the post. Ex: 423.)', 'geodirectory' ),
			);

			/**
			 * Filters the list of available permalink structure tags on the Permalinks settings page.
			 *
			 * @since 2.0.0
			 *
			 * @param array $available_tags A key => value pair of available permalink structure tags.
			 */
			$available_tags = apply_filters( 'geodir_available_permalink_structure_tags', $available_tags );
			
			/* translators: %s: permalink structure tag */
			$structure_tag_added = __( '%s added to permalink structure', 'geodirectory' );

			/* translators: %s: permalink structure tag */
			$structure_tag_already_used = __( '%s (already used in permalink structure)', 'geodirectory' );

			//print_r($structures);
			?>
			<table class="form-table gd-permalink-structure">
				<tbody>



				<?php foreach ($structures as $label => $structure ){
					if( $structure['value'] == $this->permalinks){$is_default = true;}
					?>
				<tr>
					<th>
						<label>
							<input name="geodirectory_permalink" type="radio"
					                  value="<?php echo esc_attr( $structure['value'] ); ?>"
					                  class="gdtog" <?php checked( $structure['value'] , $this->permalinks ); ?> />
							<?php echo $label; ?>
						</label>
					</th>
					<td>
						<?php if( $label == __( 'Default', 'geodirectory' ) ) {?>
							<code class="default-example">
								<?php echo esc_html( home_url() ); ?>/?gd_place=sample-place
							</code>
							<code class="non-default-example">
								<?php echo $structure['example']; ?>
							</code>
						<?php }else{?>
						<code>
							<?php echo $structure['example']; ?>
						</code>
						<?php }?>
					</td>
				</tr>
				<?php }?>


				<tr>
					<th>
						<label>
							<input name="geodirectory_permalink" id="geodir_custom_selection" type="radio" value="custom"
					                  class="tog" <?php checked( $is_default , false ); ?> />
							<?php _e( 'Custom base', 'geodirectory' ); ?>
						</label>
					</th>
					<td>
						<code><?php echo esc_html( home_url() ).'/%cpt_slug%'; ?></code>
						<input name="geodirectory_permalink_structure" id="geodir_permalink_structure" type="text"
						       value="<?php echo esc_attr( $this->permalinks ? trailingslashit( $this->permalinks) : '' ); ?>"
						       class="regular-text code">
						<br /><br />
						<div class="gd-available-structure-tags hide-if-no-js">
							<div id="gd_custom_selection_updated" aria-live="assertive" class="screen-reader-text"></div>
							<?php if ( ! empty( $available_tags ) ) { ?>
								<p><?php _e( 'Available tags:' ); ?></p>
								<ul role="list">
									<?php
									foreach ( $available_tags as $tag => $explanation ) {
										?>
										<li>
											<button type="button"
													class="button button-secondary"
													aria-label="<?php echo esc_attr( sprintf( $explanation, $tag ) ); ?>"
													data-added="<?php echo esc_attr( sprintf( $structure_tag_added, $tag ) ); ?>"
													data-used="<?php echo esc_attr( sprintf( $structure_tag_already_used, $tag ) ); ?>">
												<?php echo '%' . $tag . '%'; ?>
											</button>
										</li>
										<?php
									}
									?>
								</ul>
							<?php } ?>
						</div>
					</td>
				</tr>
				</tbody>
			</table>
			<style>.form-table.gd-permalink-structure .gd-available-structure-tags li{float:left;margin-right:5px}</style>
			<script type="text/javascript">
			var gdPermalinkStructureFocused = false,
				$gdPermalinkStructure = jQuery('#geodir_permalink_structure'),
				$gdPermalinkStructureInputs = jQuery('.gd-permalink-structure input:radio'),
				$gdPermalinkCustomSelection = jQuery('#geodir_custom_selection'),
				$gdAvailableStructureTags = jQuery('.form-table.gd-permalink-structure .gd-available-structure-tags button');
			// Change permalink structure input when selecting one of the common structures.
			$gdPermalinkStructureInputs.on('change', function() {
				if ('custom' === this.value) {
					return;
				}
				$gdPermalinkStructure.val(this.value);
				// Update button states after selection.
				$gdAvailableStructureTags.each(function() {
					gdChangeStructureTagButtonState(jQuery(this));
				});
			});
			$gdPermalinkStructure.on('click input', function() {
				$gdPermalinkCustomSelection.prop('checked', true);
			});
			// Check if the permalink structure input field has had focus at least once.
			$gdPermalinkStructure.on('focus', function(event) {
				gdPermalinkStructureFocused = true;
				jQuery(this).off(event);
			});
			/**
			 * Enables or disables a structure tag button depending on its usage.
			 *
			 * If the structure is already used in the custom permalink structure,
			 * it will be disabled.
			 *
			 * @param {object} button Button jQuery object.
			 */
			function gdChangeStructureTagButtonState(button) {
				if (-1 !== $gdPermalinkStructure.val().indexOf(button.text().trim())) {
					button.attr('data-label', button.attr('aria-label'));
					button.attr('aria-label', button.attr('data-used'));
					button.attr('aria-pressed', true);
					button.addClass('active');
				} else if (button.attr('data-label')) {
					button.attr('aria-label', button.attr('data-label'));
					button.attr('aria-pressed', false);
					button.removeClass('active');
				}
			}
			// Check initial button state.
			$gdAvailableStructureTags.each(function() {
				gdChangeStructureTagButtonState(jQuery(this));
			});
			// Observe permalink structure field and disable buttons of tags that are already present.
			$gdPermalinkStructure.on('change', function() {
				$gdAvailableStructureTags.each(function() {
					gdChangeStructureTagButtonState(jQuery(this));
				});
			});
			$gdAvailableStructureTags.on('click', function() {
				var permalinkStructureValue = $gdPermalinkStructure.val(),
					selectionStart = $gdPermalinkStructure[0].selectionStart,
					selectionEnd = $gdPermalinkStructure[0].selectionEnd,
					textToAppend = jQuery(this).text().trim(),
					textToAnnounce = jQuery(this).attr('data-added'),
					newSelectionStart;
				// Remove structure tag if already part of the structure.
				if (-1 !== permalinkStructureValue.indexOf(textToAppend)) {
					permalinkStructureValue = permalinkStructureValue.replace(textToAppend + '/', '');
					$gdPermalinkStructure.val('/' === permalinkStructureValue ? '' : permalinkStructureValue);
					// Announce change to screen readers.
					jQuery('#custom_selection_updated').text(textToAnnounce);
					// Disable button.
					gdChangeStructureTagButtonState(jQuery(this));
					return;
				}
				// Input field never had focus, move selection to end of input.
				if (!gdPermalinkStructureFocused && 0 === selectionStart && 0 === selectionEnd) {
					selectionStart = selectionEnd = permalinkStructureValue.length;
				}
				$gdPermalinkCustomSelection.prop('checked', true);
				// Prepend and append slashes if necessary.
				if ('/' !== permalinkStructureValue.substr(0, selectionStart).substr(-1)) {
					textToAppend = '/' + textToAppend;
				}
				if ('/' !== permalinkStructureValue.substr(selectionEnd, 1)) {
					textToAppend = textToAppend + '/';
				}
				// Insert structure tag at the specified position.
				$gdPermalinkStructure.val(permalinkStructureValue.substr(0, selectionStart) + textToAppend + permalinkStructureValue.substr(selectionEnd));
				// Announce change to screen readers.
				jQuery('#custom_selection_updated').text(textToAnnounce);
				// Disable button.
				gdChangeStructureTagButtonState(jQuery(this));
				// If input had focus give it back with cursor right after appended text.
				if (gdPermalinkStructureFocused && $gdPermalinkStructure[0].setSelectionRange) {
					newSelectionStart = (permalinkStructureValue.substr(0, selectionStart) + textToAppend).length;
					$gdPermalinkStructure[0].setSelectionRange(newSelectionStart, newSelectionStart);
					$gdPermalinkStructure.focus();
				}
			});
			jQuery(function($) {
				jQuery('.permalink-structure input').change(function() {
					jQuery('.gd-permalink-structure').find('code.non-default-example, code.default-example').hide();
					if (jQuery(this).val()) {
						jQuery('.gd-permalink-structure code.non-default-example').show();
						jQuery('.gd-permalink-structure input').removeAttr('disabled');
						jQuery('.gd-available-structure-tags li button').removeAttr('disabled');
					} else {
						jQuery('.gd-permalink-structure code.default-example').show();
						jQuery('.gd-permalink-structure input:eq(0)').click();
						jQuery('.gd-permalink-structure input').attr('disabled', 'disabled');
						jQuery('.gd-available-structure-tags li button').attr('disabled', 'disabled');
					}
				});
				jQuery('.permalink-structure input:checked').change();
			});
			</script>
			<?php
		}

		/**
		 * Save the settings.
		 */
		public function settings_save() {
			if ( ! is_admin() ) {
				return;
			}

			// We need to save the options ourselves; settings api does not trigger save for the permalinks page.
			if ( isset( $_POST['permalink_structure'] ) ) {
				if ( function_exists( 'switch_to_locale' ) ) {
					switch_to_locale( get_locale() );
				}

				$permalink_structure = isset( $_POST['geodirectory_permalink_structure'] ) ? trim( $_POST['geodirectory_permalink_structure'] ) : '';
				if ( ! empty( $permalink_structure ) ) {
					$permalink_structure = preg_replace( '#/+#', '/', '/' . str_replace( '#', '', $permalink_structure ) );
				}
				$permalink_structure = sanitize_option( 'permalink_structure', $permalink_structure );

				// Set permalink structure.
				geodir_set_permalink_structure( $permalink_structure );

				if ( function_exists( 'restore_current_locale' ) ) {
					restore_current_locale();
				}
			}
		}
	}

endif;

return new GeoDir_Admin_Permalink_Settings();
