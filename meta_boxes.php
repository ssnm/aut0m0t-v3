<?php
//********************************************
//	Custom meta boxes
//***********************************************************
function plugin_add_custom_boxes(){
	add_meta_box( "listing", __("Listing Tabs", "listings"), "listing_tabs", "listings", "normal", "core", null);
	add_meta_box( "gallery", __("Listing Options", "listings"), "gallery_images", "listings", "normal", "core", null);
}

function listing_tabs(){ 
	global $post, $lwp_options; ?>
    <div id="listing_tabs">
        
            <ul>   
                <?php 
                $first_tab  = (isset($lwp_options['first_tab']) && !empty($lwp_options['first_tab']) ? $lwp_options['first_tab'] : "" );
                $second_tab = (isset($lwp_options['second_tab']) && !empty($lwp_options['second_tab']) ? $lwp_options['second_tab'] : "" );
                $third_tab  = (isset($lwp_options['third_tab']) && !empty($lwp_options['third_tab']) ? $lwp_options['third_tab'] : "" );
                $fourth_tab = (isset($lwp_options['fourth_tab']) && !empty($lwp_options['fourth_tab']) ? $lwp_options['fourth_tab'] : "" );
                $fifth_tab  = (isset($lwp_options['fifth_tab']) && !empty($lwp_options['fifth_tab']) ? $lwp_options['fifth_tab'] : "" ); ?>

                <?php echo (!empty($first_tab) ? "<li><a href=\"#tabs-1\">" . $first_tab . "</a></li>" : ""); ?>
                <?php echo (!empty($second_tab) ? "<li data-action=\"options\"><a href=\"#tabs-2\">" . $second_tab . "</a></li>" : ""); ?>
                <?php echo (!empty($third_tab) ? "<li><a href=\"#tabs-3\">" . $third_tab . "</a></li>" : ""); ?>
                <?php echo (!empty($fourth_tab) ? "<li data-action=\"map\"><a href=\"#tabs-4\">" . $fourth_tab . "</a></li>" : ""); ?>
                <?php echo (!empty($fifth_tab) ? "<li><a href=\"#tabs-5\">" . $fifth_tab . "</a></li>" : ""); ?>
            </ul>
            
            <?php if(!empty($first_tab)){ ?>
                <div id="tabs-1">
                    <?php
                    if($first_tab == 'Vehicle Overview'){
                        $ov_items_meta = get_post_meta($post->ID, "ov_items_meta", true);
                        $ov_items_meta_arr = (array)json_decode($ov_items_meta);
                        //print_r($ov_items_meta_arr);
                        //echo ($ov_items_meta_arr['']);
                        ?>
                        <p>Select the following items. These will be shown at the top of the overview content:</p>
                        <table>
                            <tr>
                                <td>
                                    <label for="ov_item_1"><input type="checkbox" <?php echo $ov_items_meta_arr['mileage'] == 'mileage' ? 'checked="checked"' : ''; ?> value="mileage" name="ov_icon_items[mileage]" id="ov_item_1"> Mileage</label>
                                </td>
                                <td>
                                    <label for="ov_item_2"><input type="checkbox" <?php echo $ov_items_meta_arr['automatic'] == 'Automatic' ? 'checked="checked"' : ''; ?> value="Automatic" name="ov_icon_items[automatic]" id="ov_item_2"> Automatic</label>
                                </td>
                                <td>
                                    <label for="ov_item_3"><input type="checkbox" <?php echo $ov_items_meta_arr['electric'] == 'Electric' ? 'checked="checked"' : ''; ?> value="Electric" name="ov_icon_items[electric]" id="ov_item_3"> Electric</label>
                                </td>
                                <td>
                                    <label for="ov_item_4"><input type="checkbox" <?php echo $ov_items_meta_arr['rw_drive'] == 'Rear Wheel Drive' ? 'checked="checked"' : ''; ?> value="Rear Wheel Drive" name="ov_icon_items[rw_drive]" id="ov_item_4"> Rear Wheel Drive</label>
                                </td>
                            </tr>
                        </table>
                        <hr style="margin-bottom: 20px;border-color: #aaa;border-bottom: none;"/>
                        <?php
                    }
                    ?>
                	<?php wp_editor( $post->post_content, "post_content", array("textarea_rows" => 22) ); ?>
                </div>
            <?php } ?>
            
            <?php if(!empty($second_tab)){ ?>
                <div id="tabs-2">
                    <!-- If you are looking at this, you really want to access this tab. But it's in the right hand sidebar labelled "Options". -->
                </div>
            <?php } ?>
            
            <?php if(!empty($third_tab)){ ?>
                <div id="tabs-3">
                	<?php $technical_specifications = get_post_meta($post->ID, "technical_specifications", true); 
    				wp_editor( $technical_specifications, "technical_specifications", array("media_buttons" => true, "textarea_rows" => 22) ); ?>
                </div>
            <?php } ?>
            
            <?php if(!empty($fourth_tab)){ ?>
                <div id="tabs-4">
                    <i class='fa-info-circle info fa' data-title="<?php _e("Right click on the google map to store the coordinates of a location", "listings"); ?>!"></i>
    				<?php $location = get_post_meta($post->ID, "location_map", true);

                    if(empty($location)){
                        $location['latitude']  = (isset($lwp_options['default_value_lat']) && !empty($lwp_options['default_value_lat']) ? $lwp_options['default_value_lat'] : "");
                        $location['longitude'] = (isset($lwp_options['default_value_long']) && !empty($lwp_options['default_value_long']) ? $lwp_options['default_value_long'] : "");
                        $location['zoom']      = (isset($lwp_options['default_value_zoom']) && !empty($lwp_options['default_value_zoom']) ? $lwp_options['default_value_zoom'] : "");
                    }

                    ?>
                    <table border='0'>
                        <tr><td><?php _e("Latitude", "listings"); ?>:  </td><td> <input type='text' name='location_map[latitude]' class='location_value' data-location='latitude' value='<?php echo (isset($location['latitude']) && !empty($location['latitude']) ? $location['latitude'] : "43.653226"); ?>' /></td></tr>
                        <tr><td><?php _e("Longitude", "listings"); ?>: </td><td> <input type='text' name='location_map[longitude]' class='location_value' data-location='longitude' value='<?php echo (isset($location['longitude']) && !empty($location['longitude']) ? $location['longitude'] : "-79.3831843"); ?>' /></td></tr>
                    	<tr><td><?php _e("Zoom", "listings"); ?>: </td><td><span class='zoom_level_text'></span><input type='hidden' readonly="readonly" class='zoom_level' name='location_map[zoom]' value='<?php echo (isset($location['zoom']) && !empty($location['zoom']) ? $location['zoom'] : 10); ?>' /></td></tr>
                    </table><br />
                    
                    <div id='google-map'<?php echo " data-latitude='" . (isset($location['latitude']) && !empty($location['latitude']) ? $location['latitude'] : "43.653226") . "'"; 
    										  echo " data-longitude='" . (isset($location['longitude']) && !empty($location['longitude']) ? $location['longitude'] : "-79.3831843") . "'"; ?>></div>
                                              
                    <div id="slider-vertical" style="height: 400px;" data-value="<?php echo (isset($location['zoom']) && !empty($location['zoom']) ? $location['zoom'] : 10); ?>"></div>
                </div>
            <?php } ?>

            <?php if(!empty($fifth_tab)){ ?>
                <div id="tabs-5">
                    <?php $other_comments = get_post_meta($post->ID, "other_comments", true); 
    				wp_editor( $other_comments, "other_comments", array("media_buttons" => true, "textarea_rows" => 22) ); ?>
                </div>    
            <?php } ?>    
    </div>

<?php 	
}

function gallery_images(){ 
	global $post, $lwp_options;
	
	$saved_images   = get_post_meta($post->ID, 'gallery_images');
	if(isset($saved_images[0]) && !empty($saved_images[0])){
		$gallery_images = array_values(array_filter($saved_images));
		$gallery_images = $gallery_images[0];
	}
		
	$post_options = get_post_meta($post->ID, "listing_options");
	$options      = @unserialize($post_options[0]); ?>
	<div id="meta_tabs">
    
    	<ul>
        	<li><a href="#tab-1"><?php _e("Gallery Images", "listings"); ?></a></li>
            <li><a href="#tab-2"><?php _e("Details", "listings"); ?></a></li>
            <li><a href="#tab-3"><?php _e("Video", "listings"); ?></a></li>
            <li><a href="#tab-4"><?php _e("Listing Categories", "listings"); ?></a></li>
        </ul>
    
    	<div id="tab-1">
            <table id="gallery_images">
                <?php 
                if(isset($gallery_images) && !empty($gallery_images)){                    				
					global $slider_thumbnails;
					
					$width  = $slider_thumbnails['width'];
					$height = $slider_thumbnails['height'];
					
                    $i = 1;
                    echo "<tbody>";
                    foreach($gallery_images as $gallery_image){
                        echo "<tr><td><div class='top_header'>" . __('Image', 'listings') . " #{$i}</div>";
						echo "<div class='image_preview'>" . auto_image($gallery_image, "auto_thumb") . "</div>";
                        echo "<div class='buttons'><span class='button add_image_gallery' data-id='" . $i . "'>" . __( 'Change image', 'listings' ) . "</span> ";
                        echo "<span class='button make_default_image" . ($i == 1 ? " active_image" : "") . "'>" . __( 'Set default image', 'listings' ) . "</span> ";
                        echo "<span class='button delete_image'>" . __( 'Delete image', 'listings' ) . "</span> ";
                        echo "<span class='button move_image'>" . __( 'Move Image', 'listings' ) . "</span></div>";
                        echo "<input type='hidden' name='gallery_images[]' value='" . $gallery_image . "'>";
                        echo "</td></tr>";
                        $i++;
                    }
                    echo "</tbody>";
                } else { 
                    /*<tr><td>1</td><td> <button class='button add_image_gallery'><?php _e( 'Set image', 'listings' ); ?></button></td></tr>*/ ?>
                    <tr><td><div class="top_header"><?php _e("Image", "listings"); ?> #1</div><div class="image_preview"><?php _e("No Image", "listings"); ?></div><div class="buttons"><span class="button add_image_gallery" data-id="1"><?php _e("Change image", "listings"); ?></span> </div></td></tr>
                <?php } ?>
            </table>
            <button class='add_image button button-primary'><?php _e("Add Image", "listings"); ?></button>
            
            <div class='clear'></div>
        </div>
        
        <div id="tab-2">
            	<?php	
				global $other_options, $lwp_options;
			
				foreach($other_options as $key => $option){					
					$term    = strtolower(str_replace(" ", "_", $option));
					$low_tax = "display " . strtolower($option);

					$name  = "options[" . str_replace(" ", "_", strtolower($option)) . "]";

					$display_name = $name . "[display]";
					$text_name    = $name . "[text]";
					$value_name   = $name . "[value]";

                    // check 
                    $label = (isset($options[$term]['text']) ? $options[$term]['text'] : "");

                    if(empty($label)){
                        $label = (isset($lwp_options['default_value_' . $key]) && !empty($lwp_options['default_value_' . $key]) ? $lwp_options['default_value_' . $key] : "");
                    }

					echo "<table style='margin-bottom: 15px;'>";

					echo "<tr><td colspan='2'><h2 class='detail_heading'>" . ($key == "price" ? __("Current", "listings") . " " : "") . ucwords($option) . "</h2></td></tr>";
					echo "<tr><td>" . __("Label", "listings") . ": </td><td><input type='text' name='" . $text_name . "' value='" . $label . "' /></td></tr>";
					echo "<tr><td>" . __("Value", "listings") . ": </td><td><input type='text' name='" . $value_name . "' value='" . (isset($options[$term]['value']) ? $options[$term]['value'] : "") . "' class='info " . $term . "' data-placement='right' data-trigger='focus' data-title=\"<img src='" . THUMBNAIL_URL . "widget_slider/example-" . $term . ".png' style='opactiy: 1'>\" data-html='true' /></td></tr>";

					echo "</table>";

                    if($key == "price"){
                        echo "<table style='margin-bottom: 15px;'>";

                        echo "<tr><td colspan='2'><h2 class='detail_heading'>" . __("Original Price", "listings") . "</h2></td></tr>";
                        echo "<tr><td>" . __("Value", "listings") . ": </td><td><input type='text' name='" . $name . "[original]' value='" . (isset($options[$term]['original']) ? $options[$term]['original'] : "") . "' class='info " . $term . "' data-placement='right' data-trigger='focus' data-title=\"<img src='" . THUMBNAIL_URL . "widget_slider/example-" . $term . ".png' style='opactiy: 1'>\" data-html='true' /></td></tr>";

                        echo "</table>";
                    }
				}
				?>

            <?php /* ?>

            <hr>

            <h2 class="no_bottom_margin"><?php _e("Listing Badge", "listings"); ?></h2>

            <table>
            	<tr><td><?php _e("Badge Text", "listings"); ?>: </td><td> <input type="text" name="options[badge_text]"<?php echo (isset($options['badge_text']) ? " value='" . $options['badge_text'] . "'" : ""); ?> class="info" data-placement='right' data-trigger="focus" data-title="<img src='<?php echo THUMBNAIL_URL; ?>widget_slider/example-badge.png' width='211' height='200' style='opactiy: 1'>" data-html='true' ></td></tr>
            	<tr><td><?php _e("Color", "listings"); ?>: </td><td> <select name="options[badge_color]" class="badge_color">
            		<?php $colors = array("theme color", "red", "orange", "yellow", "green", "blue", "indigo", "violet", "black", "custom");

            		$options['badge_color'] = (!isset($options['badge_color']) && empty($options['badge_color']) ? "theme color" : $options['badge_color']);

            		foreach($colors as $color){
            			echo "<option value='" . $color . "' " . selected($color, $options['badge_color']) . ">" . sprintf( __("%s", "listings"), $color ) . "</option>";
            		} ?>
		        	</select></td></tr>
            </table>
			
			<div class="badge_hint" style='<?php echo (isset($options['badge_color']) && $options['badge_color'] == "custom" ? "" : " display: none;"); ?>margin-top:15px;font-size:12px;'>
				<?php _e("Add this code to your CSS and replace the bolded text with your color", "listings"); ?>: <br>
				.angled_badge.custom:before { border-color: rgba(0, 0, 0, 0) #<b>FFFFFF</b> rgba(0, 0, 0, 0); }
			</div><?php */ ?>

            <hr>

            <?php /* $pdf_brochure = get_post_meta($post->ID, "pdf_brochure_input", true);
                  $pdf_link     = wp_get_attachment_url($pdf_brochure); ?>

            <h2 class="no_bottom_margin"><?php _e("PDF Brochure", "listings"); ?></h2>
            <button class="pick_pdf_brochure button primary"><?php _e("Choose a PDF Brochure", "listings"); ?></button> <br><br> <?php _e("Current File", "listings"); ?>: <span class="pdf_brochure_label"><a href="<?php echo $pdf_link; ?>" target="_blank"><?php echo $pdf_link; ?></a></span>
            <input type="hidden" name="pdf_brochure_input" class="pdf_brochure_input" value="<?php echo $pdf_brochure; ?>">
            
            <br />
            
            <br />

            <hr>
            
            <h2 class="no_bottom_margin"><?php _e("Other Details", "listings"); ?></h2><?php */ ?>
            <table>
	            <?php 
				/*$checked = get_post_meta($post->ID, "verified", true);
				echo "<tr><td><label for='verified'>" . __("Show vehicle history report image", "listings") . ":</label></td><td><input type='checkbox' name='verified' value='yes' id='verified'" . ((isset($checked) && !empty($checked)) || is_edit_page('new') ? " checked='checked'" : "") . "></td></tr>";			
				
				$additional_categories = (isset($lwp_options['additional_categories']) && !empty($lwp_options['additional_categories']) ? $lwp_options['additional_categories'] : "");

				if(!empty($additional_categories['value'])){
					foreach($additional_categories['value'] as $key => $category){
                        if(!empty($category)){
						$safe_handle = str_replace(" ", "_", strtolower($category));
						$current_val = get_post_meta($post->ID, $safe_handle, true);

                        if(is_edit_page('new') && isset($additional_categories['check'][$key]) && $additional_categories['check'][$key] == "on"){
                            $current_val = 1;
                        }

						echo "<tr><td><label for='" . $safe_handle . "'>" . $category . ":</label></td><td><input type='checkbox' name='additional_categories[value][" . $safe_handle . "]' id='" . $safe_handle . "' value='1'" . ($current_val == 1 ? "checked='checked'" : "") . "></td></tr>";
					}
				}
				}
                */?>
			</table>
            
            <?php /* _e("Short Description For Vehicle Slider Widget", "listings"); ?>:<br />
            <input type='text' name='options[short_desc]'<?php echo (isset($options['short_desc']) && !empty($options['short_desc']) ? " value='" . $options['short_desc'] . "'" : ""); ?> class='info' data-placement='right' data-trigger="focus" data-title="<img src='<?php echo THUMBNAIL_URL; ?>widget_slider/example.png' width='183' height='201' style='opactiy: 1'>" data-html='true' />            
  			
            <?php */ ?>
            
        </div>
        
        <div id="tab-3">
        	<?php _e("YouTube/Vimeo link", "listings"); ?>: <input type='text' name='options[video]' id='listing_video_input' style='width: 500px;'<?php echo (isset($options['video']) && !empty($options['video']) ? " value='" . $options['video'] . "'" : ""); ?> />
            
            <div id='listing_video'>
            <?php if(isset($options['video']) && !empty($options['video'])){ 
            	$url = parse_url($options['video']);
				
				if($url['host'] == "www.youtube.com" || $url['host'] == "youtube.com"){
					$video_id = str_replace("v=", "", $url['query']);
					
					echo "<br><br><iframe width=\"644\" height=\"400\" src=\"http://www.youtube.com/embed/" . $video_id . "\" frameborder=\"0\" allowfullscreen></iframe>";
				} elseif($url['host'] == "www.vimeo.com" || $url['host'] == "vimeo.com"){
					$video_id = $url['path'];
					
					echo "<br><br><iframe width=\"644\" height=\"400\" src=\"http://player.vimeo.com/video" . $video_id . "\" frameborder=\"0\" allowfullscreen></iframe>";
				} else {
					echo __("Not a valid YouTube/Vimeo link", "listings") . "...";
				}
            } ?>
            </div>
        </div>

        <div id="tab-4">
        	<table style="width: 100%;">
        		<?php
        			$listing_categories = get_listing_categories();

                    // D($listing_categories);

        			foreach($listing_categories as $category){
						$name = str_replace(" ", "_", strtolower($category['singular']));
						$category['link_value'] = (isset($category['link_value']) && !empty($category['link_value']) ? $category['link_value'] : "");

                        $valid_name = preg_replace("/[^a-z0-9.]+/i", "", $name);//preg_replace('/\W+/','',strtolower(strip_tags($name)));

						// link value
						if(empty($category['link_value']) || $category['link_value'] == "none"){
	        				echo "<tr><td>" . wpml_category_translate($category['singular'], "singular", $category['singular']) . ": </td><td>"; 

	        				if(!isset($category['compare_value']) || (isset($category['compare_value']) && $category['compare_value'] == "=")){
	        					echo "<select name='" . $name . "' style='width: 100%;' id='" . $valid_name . "'>";
		        				echo "<option value='" . __("None", "listings") . "'>" . __("None", "listings") . "</option>";

		        				// sort
                                if(!empty($category['terms'])){
    		        				if(isset($category['sort_terms']) && $category['sort_terms'] == "desc"){
                                        arsort($category['terms']);
                                    } else {
                                        asort($category['terms']);
                                    }
                                }

                                if(!empty($category['terms'])){
    		        				foreach($category['terms'] as $term_key => $term){
                                        $option_value = $term;
                                        $term         = $term;
    		        					echo "<option value='" . ($option_value) . "' " . selected(htmlspecialchars_decode(stripslashes($term), ENT_QUOTES), stripslashes(get_post_meta( $post->ID, $name, true )), false) . ">" . stripslashes($term) . "</option>";
    		        				}
                                }

		        				echo "</select>";
		        			} else {
		        				$text_value = get_post_meta($post->ID, str_replace(" ", "_", strtolower($category['singular'])), true);
		        				echo "<input type='text' name='" . $name . "' value='" . htmlspecialchars(stripslashes($text_value), ENT_QUOTES) . "'>";
		        			}

	        				echo "</td><td style='text-align: right; width: 350px; max-width: 350px;'> <a href='#' class='hide-if-no-js add_new_name' data-id='" . $valid_name . "'>+ " . __("Add New Term", "listings") . "</a>";
	        				echo '<div class="add_new_content ' . $valid_name . '_sh" style="display: none;">
						    	<input class="' . $valid_name . '" type="text" style="margin-left: 0;" />
						        <button class="button submit_new_name" data-type="' . $valid_name . '" data-exact="' . $name . '">' . __("Add New Term", "listings") . '</button>
						    </div>';
	        				echo "</td></tr>";
	        			}
        			}
        		?>
        	</table>
        </div>
        
    </div>
<?php
}

add_action( 'add_meta_boxes', 'plugin_add_custom_boxes' );

function plugin_add_after_editor(){
	global $post, $wp_meta_boxes;
	
	do_meta_boxes(get_current_screen(), 'advanced', $post);
	
	$post_types = get_post_types();
	
	foreach($post_types as $post_type){
		unset($wp_meta_boxes[$post_type]['advanced']);
	}
}

add_action("edit_form_after_title", "plugin_add_after_editor");

function plugin_secondary_title(){
	global $post;
	
	$secondary_title = get_post_meta($post->ID, "secondary_title", true);
	echo "<input type='text' value='" . $secondary_title . "' name='secondary_title' style='width:100%;'/>";
}

//********************************************
//	Custom meta boxes for custom categories
//***********************************************************
function plugin_add_custom_meta_boxes(){
    add_meta_box( "vehicle_inspector", __("Vehicle Inspector", "listings"), "vehicle_inspector_make_meta_box", "listings", "side", "core", array("name" => "vehicle_inspector"));
	add_meta_box( "vehicle_status", __("Vehicle Status", "listings"), "vehicle_status_make_meta_box", "listings", "side", "core", array("name" => "vehicle_status"));
    add_meta_box( "options", __("Options", "listings"), "plugin_make_meta_box", "listings", "side", "core", array("name" => "options"));
	$listing_categories = get_listing_categories();
	
	foreach($listing_categories as $category){	
			
		$sfield = str_replace(" ", "_", strtolower($category['singular']));
				
		$field = $name = $category['singular'];

		if($category['filterable'] == 1){
			$field = $field . " (" . __("Filterable", "listings") . ")";
		}
	}
}
function vehicle_status_make_meta_box($post, $metabox){
    $car_sold = get_post_meta($post->ID, "car_sold", true);
    ?>
    <p>
        <label for="sold_check_sold"><input type='radio' name='car_sold' id='sold_check_sold' value='sold' <?php echo isset($car_sold) && $car_sold == "sold" ? "checked='checked'" : ""; ?>> Sold</label>
    </p>
    <p>
        <label for="sold_check_reserved"><input type='radio' name='car_sold' id='sold_check_reserved' value='reserved' <?php echo isset($car_sold) && $car_sold == "reserved" ? "checked='checked'" : ""; ?>> Reserved</label>
    </p>
    <p>
        <label for="sold_check_none"><input type='radio' name='car_sold' id='sold_check_none' value='none' <?php echo isset($car_sold) && $car_sold == "none" ? "checked='checked'" : ""; ?>> Reset</label>
    </p>
    <?php
}
function vehicle_inspector_make_meta_box($post, $metabox){
    $inspector_name = get_post_meta($post->ID, 'vehicle_inspector_name', true);
    $inspector_email = get_post_meta($post->ID, 'vehicle_inspector_email', true);

    ?>
    <p>
        <label for="vehicle_inspector_name">Name: <input type="text" name="vehicle_inspector_name" id="vehicle_inspector_name" value="<?php echo $inspector_name; ?>" /></label>
    </p>
    <p>
    <label for="vehicle_inspector_email">Email: <input type="email" name="vehicle_inspector_email" id="vehicle_inspector_email" value="<?php echo $inspector_email; ?>" /></label>
    </p>
    <?php
}

function plugin_make_meta_box($post, $metabox){
	$name  = $metabox['args']['name'];
	$lower = str_replace(" ", "_", strtolower($name));
	
	$sname  = str_replace(" ", "_", strtolower($name));

	$single_category = get_single_listing_category($lower);
	$options         = (isset($single_category['terms']) && !empty($single_category['terms']) ? $single_category['terms'] : "");

	$selected = get_post_meta($post->ID, $lower, true);

    /* Default Options */
    $default_options = get_option("options_default_auto");
	
	echo "<select style='width: 100%;' id='" . $lower . "' name='" . ($name == "options" ? "multi_options[]" : $lower) . "' " . ($name == "options" ? "multiple " : "") . "" . ($name == "options" ? " class='chosen-dropdown'" : "") . ">";
	echo ($name != "options" ? "<option value='none'>" . __("None", "listings") . "</option>" : "");
		
	if($name == "options"){
		$multi_options = get_post_meta($post->ID, "multi_options", true);

        sort($options, SORT_NATURAL | SORT_FLAG_CASE);
				
		foreach($options as $option){
			echo "<option value='" . $option . "'" . (is_array($multi_options) && (in_array($option, $multi_options)) || (is_edit_page('new') && is_array($default_options) && in_array($option, $default_options)) ? " selected='selected'" : "") . ">" . $option . "</option>";
		}
	} else { 
		if(!empty($options)){
			foreach($options as $option){
				echo "<option value='" . $option . "'" . selected($option, $selected) . ">" . $option . "</option>";
			}
		}
	}

	echo "</select>"; ?>    
    
	<h4 style="margin-bottom: 5px;"><a href="#" class="hide-if-no-js add_new_name" data-id="<?php echo $sname; ?>">+ <?php _e("Add New Option", "listings"); ?></a></h4>
    
    <div class='add_new_content <?php echo $sname; ?>_sh' style="display: none;">
    	<input class='<?php echo $sname; ?>' type='text' style="width: 100%; margin-left: 0;" />
        <button class='button submit_new_name' data-type='<?php echo $sname; ?>' data-exact="<?php echo $sname; ?>"><?php _e("Add New Option", "listings"); ?></button>
    </div>
<?php	
}

function plugin_register_menu_pages(){	
	add_submenu_page( 'edit.php?post_type=listings', "Options", "Options", 'manage_options', 'options', 'plugin_my_custom_submenu_page_callback');	

	$listing_categories = get_listing_categories();
	
	foreach($listing_categories as $field){
		$sfield   = str_replace(" ", "_", strtolower($field['plural']));
		
        $plural   = wpml_category_translate($field['singular'], "plural", $field['plural']);
        $singular = wpml_category_translate($field['singular'], "singular", $field['singular']);

		add_submenu_page( 'edit.php?post_type=listings', $plural, $plural, 'manage_options', str_replace(" ", "_", mb_strtolower($field['singular'])), 'plugin_my_custom_submenu_page_callback' ); 
			
	}
} 


function plugin_my_custom_submenu_page_callback() {	
	$value  = $_GET['page']; 
	$svalue = str_replace(" ", "_", mb_strtolower($value));

    $is_options = false;

	$listing_categories = get_listing_categories();

    // if( ($value == "options" && isset($lwp_options['second_tab']) && !empty($lwp_options['second_tab'])) ){
    $category = get_single_listing_category($svalue);
	
	if($value == "options"){
		$label      = __("Options", "listings");
        $is_options = true;

        $default    = get_option("options_default_auto");
	} else {		
		$label      = wpml_category_translate($category['singular'], "singular", $category['singular']);//ucwords(str_replace("_", " ", $_GET['page']));
	} 

	?>
    <style type="text/css"> .delete_name { cursor: pointer } </style>
	<div class='wrap nosubsub'>
    	<div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
        <h2 style="margin-bottom:25px;"><?php echo ucwords($label); ?></h2>
        
        <div id='col-container'> 
            <div id='col-left' style='display: inline-block; width: auto; vertical-align: top;'>
            	<strong style="display: block;"><?php _e("Add New", "listings"); ?> <?php echo ucwords($label); ?></strong><br />
            	<form method="POST" action="">
                	<table border='0'>
                		<tr>
							<?php if($_REQUEST['page'] != "color"){ ?><td><?php _e("Value", "listings");?>: </td><?php } ?>
                            <td <?php if($_REQUEST['page']=="color"){ ?>colspan="2"<?php } ?>> <?php echo (isset($category['compare_value']) && !empty($category['compare_value']) && $category['compare_value'] != "=" ? $category['compare_value'] : ""); ?> <input <?php if($_REQUEST['page']=="color"){ echo 'class="listedco-color-picker"'; } ?> type='text' name='new_name' /></td>
                       	</tr>
                        
                        <?php if($_REQUEST['page'] == "color"){ ?>
						<tr>
                        	<td>Color Name</td>
                            <td><input type="text" name="color_name" /></td>
                        </tr>
						<?php } ?>
                        
                    	<tr><td colspan="2"><input type='submit' class='button-primary' name='add_new_name' value='<?php _e("Add", "listings"); ?>' /></td></tr>						
                       
                    </table>
                </form>
            </div>
            
            <div id='col-right' style='display: inline-block; float: none;'>
                <form method="POST" action="">
                	<table border='0' class='wp-list-table widefat fixed tags listing_table'>
                    	<thead>
                        	<tr>
                                <th><?php _e("Value", "listings"); ?></th>
                                <th><?php _e("Posts", "listings"); ?></th>
                                <?php if(isset($category['location_email']) && !empty($category['location_email'])){ ?>
                                    <th><?php _e("Email Address", "listings"); ?></th>

                                    <?php $location_email = get_option("location_email");
                                    } ?>
                                <th><?php _e("Delete", "listings"); ?></th>
                                <?php echo ($is_options ? "<th>" . __("Default Selection", "listings") . "</th>": ""); ?>
                            </tr>
                        </thead>
                        
                        <tbody>
                        	<?php
    						$options = (isset($category['terms']) && !empty($category['terms']) ? $category['terms'] : "");
    						$i       = 0;
    						if(empty($options)){
    							echo "<tr><td colspan='3'>" . __("No terms yet", "listings") . "</td></tr>";
    						} else {
    							foreach($options as $key => $option){
                                    $option_label = $option;//wpml_term_translate($category['singular'], stripslashes($option), $key);

    								echo "<tr" . ($i %2 == 0 ? " class='alt'" : "") . " id='t_" . $i . "'><td>" . $option_label . "</td><td>" . get_total_meta($svalue, $option, ($is_options)) . "</td>";

                                    if(isset($category['location_email']) && !empty($category['location_email'])){
                                        echo "<td><input type='email' placeholder='" . __("Email", "listings") . "' value='" . (isset($location_email[htmlspecialchars_decode($option)]) && !empty($location_email[htmlspecialchars_decode($option)]) ? $location_email[htmlspecialchars_decode($option)] : "") . "' name='location_email[" . htmlspecialchars($option, ENT_QUOTES) . "]'></td>";
                                    }

                                    echo "<td><span class='delete_name button-primary' data-id='" . $key . "' data-type='" . $svalue . "' data-row='" . $i . "'>" . __("Delete", "listings") . "</span></td>";
    								
                                    if($is_options){
                                        echo "<td><input type='checkbox' name='default[]' value='" . $option . "' " . (!empty($default) && in_array($option, $default) ? " checked='checked'" : "") . "></td>";
                                    }

                                    echo "</tr>";
                                    $i++;
    							}
    						}
    						?>
                        </tbody>
                    </table>

                    <input type="submit" name="submit" value="Save Default" class="button button-primary" style="margin-top: 15px;">

                </form>
            </div>
        </div>
    </div>
    <script type="application/javascript">
		jQuery(function($){
			$(".delete_name").click( function(){
				var id   = $(this).data('id');
				var type = $(this).data('type');
				var row  = $(this).data('row');
				
				jQuery.ajax({
				   type : "post",
				   url : myAjax.ajaxurl,
				   data : {action: "delete_name", id: id, type: type},
				   success: function(response) {
					  var table = $("#t_" + row).closest("table");
					  
					  $("#t_" + row).closest("tr").fadeOut(400, function(){
						  $(this).remove();
					  
						  table.find("tr").each( function( index ){
							  $(this).removeClass("alt");
							  $(this).addClass((index%2 != 0 ? "alt" : ""));
						  });
					  });
				   }
				});
			});
		});
	</script>    
<?php	
}

// deleting
function plugin_delete_name(){
	$id   = $_POST['id'];
	$type = $_POST['type'];
	
	$listing_categories = get_listing_categories(true);
	$current_category   = (isset($listing_categories[$type]) && !empty($listing_categories[$type]) ? $listing_categories[$type] : "");

	// update the var
	$listing_categories[$type] = $current_category;
	
	unset($listing_categories[$type]['terms'][$id]);
	
	update_option('listing_categories', $listing_categories);
	
	die;
}

add_action("wp_ajax_delete_name", "plugin_delete_name");
add_action("wp_ajax_nopriv_delete_name", "plugin_delete_name");

// ajax save
function plugin_add_name() {
	$name  = $_POST['value'];
	$type  = $_POST['type'];
    $exact = $_POST['exact'];

	$listing_categories = get_listing_categories(true);
	$listing_categories[$exact]['terms'][] = htmlspecialchars($name, ENT_QUOTES);

	update_option("listing_categories", $listing_categories);

	die;
}

add_action("wp_ajax_add_name", "plugin_add_name");
add_action("wp_ajax_nopriv_add_name", "plugin_add_name");

// saving
function plugin_save_new_custom_meta(){
	if(isset($_POST['add_new_name'])){
		$name = $_POST['new_name'];
		$type = str_replace(" ", "_", mb_strtolower($_GET['page']));

	    $listing_categories = get_listing_categories(true);
		$current_category   = (isset($listing_categories[$type]) && !empty($listing_categories[$type]) ? $listing_categories[$type] : "");

		if(!empty($current_category['terms'])){
			$current_category['terms'][] = $name;
		} else {
			$current_category['terms'] = array($name);
		}

		// update the var
		$listing_categories[$type] = $current_category;

		update_option( 'listing_categories', $listing_categories );
	}

    if(isset($_POST['location_email']) && !empty($_POST['location_email'])){
        update_option("location_email", $_POST['location_email']);
    }

    if(isset($_POST['default']) && !empty($_POST['default'])){

        update_option("options_default_auto", $_POST['default']);

    }
}

add_action( 'init', 'plugin_save_new_custom_meta' );
add_action( 'admin_menu', 'plugin_register_menu_pages' );
add_action( 'add_meta_boxes', 'plugin_add_custom_meta_boxes' );
?>