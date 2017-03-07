<?php
add_action('get_header', 'my_filter_head');

function my_filter_head() {
    remove_action('wp_head', '_admin_bar_bump_cb');
}

// image sizes
add_image_size("auto_thumb", $slider_thumbnails['width'], $slider_thumbnails['height'], true);
add_image_size("auto_slider", $slider_thumbnails['slider']['width'], $slider_thumbnails['slider']['height'], true);
add_image_size("auto_listing", $slider_thumbnails['listing']['width'], $slider_thumbnails['listing']['height'], true);
add_image_size("auto_portfolio", 770, 450, true);

function auto_image($id, $size, $url = false){
    $return = ($url == true ? wp_get_attachment_image_src( $id, $size ) : wp_get_attachment_image( $id, $size ));

    return ($url == true ? $return[0] : $return);
}

// category functions
if(!function_exists("get_listing_categories")){
    function get_listing_categories($multi_options = false){
        $current_categories = get_option( 'listing_categories' );
        if($current_categories == false){
            $current_categories = array();
        }

        if($multi_options == false && isset($current_categories['options']) && !is_string($current_categories['options'])){
            unset($current_categories['options']);
        }

        return $current_categories;
    }
}

if(!function_exists("get_single_listing_category")){
    function get_single_listing_category($category){
        $current_categories = get_option( 'listing_categories' );
        if(!isset($current_categories[$category]) && empty($current_categories[$category])){
            $return = array();
        } else {
            $return = $current_categories[$category];
        }

        return $return;
    }
}

if(!function_exists("get_filterable_listing_categories")){
    function get_filterable_listing_categories(){
        $current_categories = get_option( 'listing_categories' );
        $filterable_categories = array();

        if($current_categories == false){
            $current_categories = array();
        } else {
            if(is_array($current_categories) && !empty($current_categories)){
                foreach($current_categories as $key => $category){
                    if(isset($category['filterable']) && $category['filterable'] == 1){
                        $filterable_categories[$key] = $category;
                    }
                }
            }
        }

        return $filterable_categories;
    }
}

if(!function_exists("get_location_email_category")){
    function get_location_email_category(){
        $current_categories = get_option( 'listing_categories' );
        $return = "";

        if(is_array($current_categories) && !empty($current_categories)){
            foreach($current_categories as $category){
                if(isset($category['location_email']) && $category['location_email'] == 1){
                    $return = $category['singular'];
                }
            }
        }

        return $return;
    }
}

if(!function_exists("get_column_categories")){
    function get_column_categories(){
        $current_categories = get_option( 'listing_categories' );
        $return = "";

        if(is_array($current_categories) && !empty($current_categories)){
            foreach($current_categories as $category){
                if(isset($category['column']) && $category['column'] == 1){
                    $return[] = $category;
                }
            }
        }

        return $return;
    }
}

if(!function_exists("get_use_on_listing_categories")){
    function get_use_on_listing_categories(){
        $use_on_categories = array();

        $current_categories = get_option( 'listing_categories' );
        if($current_categories == false){
            $current_categories = array();
        } else {
            foreach($current_categories as $category){
                if(isset($category['use_on_listing']) && $category['use_on_listing'] == 1){
                    $use_on_categories[$category['singular']] = $category;
                }
            }
        }

        return $use_on_categories;
    }
}

if(!function_exists("get_category_correct_case")){
    function get_category_correct_case($category, $value){
        $category      = str_replace(array("-", " "), "_", mb_strtolower($category));

        // if WPML not english
        if(defined("ICL_LANGUAGE_CODE") && ICL_LANGUAGE_CODE != "en"){
            foreach(get_listing_categories() as $key => $for_category){
                if(str_replace(" ", "_", strtolower($for_category['singular'])) == $category){
                    $category = $key;
                }
            }
        }

        $list_category = get_single_listing_category($category);
        $return        = false;
        $value 		   = str_replace("--", "-", $value);

        if(!empty($list_category['terms'])){
            foreach($list_category['terms'] as $term){
                if(str_replace(" ", "-", mb_strtolower($term)) == $value){
                    $return = stripslashes($term);
                }
            }
        }

        return $return;
    }
}

function wpml_category_translate($singular, $singular_or_plural, $term){
    if(function_exists("icl_translate")){
        return icl_translate("Automotive Listing Category", $singular . " " . ucfirst($singular_or_plural), $term);
    } else {
        return $term;
    }
}

function wpml_term_translate($singular, $term, $term_key){
    if(function_exists("icl_translate")){
        return icl_translate("Automotive Listing Category", $singular . " Term " . ($term_key + 1), $term);
    } else {
        return $term;
    }
}

if(!function_exists("get_listing_categories_to_redux_select")){
    function get_listing_categories_to_redux_select(){
        $return = array();

        foreach(get_listing_categories() as $key => $category){
            $return[$key] = $category['singular'];
        }

        return $return;
    }
}

function automotive_plugin_editor_styles() {
    add_editor_style( CSS_DIR . 'wp.css' );
    //add_editor_style( CSS_DIR . 'bootstrap.css' );
    add_editor_style( CSS_DIR . 'bootstrap.min.css' );
}
add_action( 'init', 'automotive_plugin_editor_styles' );

//********************************************
//	Register Sidebar
//***********************************************************
$args = array(
    'name'          => __( 'Listings Sidebar', 'listings' ),
    'id'            => 'listing_sidebar',
    'description'   => '',
    'class'         => '',
    'before_widget' => '<div class="side-widget padding-bottom-50">',
    'after_widget' => '</div>',
    'before_title' => '<h3 class="side-widget-title margin-bottom-25">',
    'after_title' => '</h3>' );

register_sidebar( $args );

//********************************************
//	Get Table Prefix
//***********************************************************
if(!function_exists("get_table_prefix")){
    function get_table_prefix() {
        global $wpdb;
        return $wpdb->prefix;
    }
}

//********************************************
//	Inventory Listing
//***********************************************************
if(!function_exists("inventory_listing")){
    function inventory_listing($id, $layout = "fullwidth"){
        global $lwp_options;

        ob_start();

        $listing   = get_post($id);
        $post_meta = get_post_meta_all($id);

        $listing_options = (isset($post_meta['listing_options']) && !empty($post_meta['listing_options']) ? unserialize(unserialize($post_meta['listing_options'])) : array());

        // determine image
        $gallery_images = unserialize((isset($post_meta['gallery_images']) && !empty($post_meta['gallery_images']) ? $post_meta['gallery_images'] : ""));

        //D($gallery_images);

        if(isset($gallery_images) && !empty($gallery_images) && isset($gallery_images[0])){
            $main_image_src = auto_image($gallery_images[0], "listing-right-thumb", true);
            if($gallery_images[1]){
                $left_top_src = auto_image($gallery_images[1], "listing-left-thumb", true);
            }else{
                $left_top_src = auto_image($gallery_images[0], "listing-left-thumb", true);
            }
            if($gallery_images[2]){
                $left_bottom_src = auto_image($gallery_images[2], "listing-left-thumb", true);
            }else{
                $left_bottom_src = auto_image($gallery_images[0], "listing-left-thumb", true);
            }
        } elseif(empty($gallery_images[0]) && isset($lwp_options['not_found_image']['url']) && !empty($lwp_options['not_found_image']['url'])){
            $main_image_src = $lwp_options['not_found_image']['url'];
            $left_top_src = $lwp_options['not_found_image']['url'];
            $left_bottom_src = $lwp_options['not_found_image']['url'];
        } else {
            $main_image_src = get_bloginfo('stylesheet_directory').'/images/2015-honda-fit-lx-4dr-hatchback-hero-520x374.jpg';
            $left_top_src = get_bloginfo('stylesheet_directory').'/images/2015-honda-fit-lx-4dr-hatchback-left-260x173.jpg';
            $left_bottom_src = get_bloginfo('stylesheet_directory').'/images/2015-honda-fit-lx-4dr-hatchback-right-260x173.jpg';
        }
        ?>
 <div id="<?php echo $id; ?>" class="carItem">
            <div class="small-12 columns">
                <div class="carContainer">
                    <div class="carMessage non-leasable">
                        <div class="highlight">Financing Available <span class="launch finance info"></span></div>
                        <div>Est. $240/Month</div>
                    </div>
                    <div class="carImagesContainer">
                        <?php
                        if($post_meta['car_sold']){
                            if($post_meta['car_sold'] == 'sold'){
                                ?>
                                <div class="car_status car_sold"><span>Sold</span></div>
                                <?php
                            }else if($post_meta['car_sold'] == 'reserved'){
                                ?>
                                <div class="car_status car_reserved"><span>Reserved</span></div>
                                <?php
                            }else{

                            }
                        }
                        ?>
                        <a href="<?php echo get_permalink($id); ?>">
                            <div class="carOverlay">
                                <div class="checkMeOut"></div>
                            </div>
                        </a>
                        <div class="leftCol">
                            <div class="first-image-container">
                                <img src="<?php echo $left_top_src; ?>" alt="<?php echo get_the_title($id); ?>" />
                                <div class="img-bg-color" style="background-color:red"></div>
                            </div>
                            <div style="vertical-align: bottom; bottom: 0; position: absolute;">
                                <img src="<?php echo $left_bottom_src; ?>" alt="<?php echo get_the_title($id); ?>" />
                                <div class="img-bg-color" style="vertical-align: bottom; bottom: 0; position: absolute;background-color:green"></div>
                            </div>
                        </div>
                        <div class="rightCol">
                            <div class="banner"></div>
                            <img class="cld-responsive cld-hidpr" alt="<?php echo $listing->post_title; ?>" src="<?php echo $main_image_src; ?>">
                            <div class="img-bg-color" style="background-color:blue"></div>
                        </div>
                    </div>
                    <div class="carDescription">
                        <div class="topTitle">
                            <a class="carName" href="<?php echo get_permalink($id); ?>">
                                <span class="name"><?php echo $listing->post_title; ?></span>
                            </a>

                            <a class="carPrice" href="<?php echo get_permalink($id); ?>">
                                <span class=""><?php echo $listing_options['price']['value'] ? format_currency($listing_options['price']['value']).' <small>+Taxes</small>' : "FREE!?"; ?></span>
                            </a>

                        </div>
                        <a class="carFeatures" href="<?php echo get_permalink($id); ?>">
                            <div class="features"><?php echo $post_meta['mileage'] ? $post_meta['mileage'].' km' : "Brand New"; ?> <?php //echo $post_meta['multi_options']; ?></div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php

        return ob_get_clean();
    }
}

function car_listing_container($layout){
    $return = array();

    if($layout == "boxed_fullwidth"){
        $return['start'] = '<div class="inventory_box car_listings boxed boxed_full">';
        $return['end']   = '</div>';
    } elseif($layout == "wide_fullwidth"){
        $return['start'] = '<div class="content-wrap car_listings">';
        $return['end']   = '</div>';
    } elseif($layout == "boxed_left"){
        $return['start'] = '<div class="car_listings boxed boxed_left col-md-9 col-lg-push-3 col-md-push-3">';
        $return['end']   = '</div>';
    } elseif($layout == "boxed_right"){
        $return['start'] = '<div class="car_listings boxed boxed_right col-md-9">';
        $return['end']   = '</div>';
    } elseif($layout == "wide_left"){
        $return['start'] = '<div class="inventory-wide-sidebar-left col-md-9  col-lg-push-3 col-md-push-3 car_listings"><div class="sidebar">';
        $return['end']   = '</div></div>';
    } elseif($layout == "wide_right"){
        $return['start'] = '<div class="inventory-wide-sidebar-right car_listings col-md-9 padding-right-15"><div class="sidebar">';
        $return['end']   = '</div></div>';
    } else {
        $return['start'] = '<div class="inventory_box car_listings">';
        $return['end']   = '</div>';
    }

    return $return;
}

if(!function_exists("listing_youtube_video")){
    function listing_youtube_video(){
        return '<div id="youtube_video">
			<iframe width="560" height="315" src="" allowfullscreen style="width: 560px; height: 315px; border: 0;"></iframe>
		</div>';
    }
}

if(!function_exists("listing_template")){
    function listing_template($layout, $is_ajax = false, $ajax_array = false){
        if($is_ajax == false) { ?>
            <div class="inner-page row">
        <?php
        }
        global $lwp_options;

        $args     = ($is_ajax == false ? listing_args($_GET) : listing_args($_GET, false, $ajax_array));

        $listings = get_posts($args[0]);

        //echo count($listings);

        if($is_ajax == false){
            listing_filter_sort();
        }

        $container = car_listing_container($layout);
        $view_class = '';
        if($layout == 'boxed_fullwidth'){
            $view_class = 'grid-view';
        }
        echo (!$is_ajax ? "<div id='carList' class='generate_new $view_class'>" : "") . $container['start'];

        if(!empty($listings)){
            foreach($listings as $listing){
                echo inventory_listing($listing->ID, $layout);
            }
        } else {
            echo do_shortcode('[alert type="2" close="No"]' . __("No match found. Try another combination.", "listings") . '[/alert]') . "<div class='clearfix'></div>";
        }

        echo "<div class=\"clearfix\"></div>";
        echo $container['end'];

        if($layout == "boxed_left"){
            echo "<div class=\" col-md-3 col-sm-12 col-lg-pull-9 col-md-pull-9 left-sidebar side-content listing-sidebar\">";
            dynamic_sidebar("listing_sidebar");
            echo "</div>";
        } elseif($layout == "boxed_right"){
            echo "<div class=\"inventory-sidebar col-md-3 side-content listing-sidebar\">";
            dynamic_sidebar("listing_sidebar");
            echo "</div>";
        } elseif($layout == "wide_left"){
            echo "<div class=\" col-md-3 col-lg-pull-9 col-md-pull-9 left-sidebar side-content listing-sidebar\">";
            dynamic_sidebar("listing_sidebar");
            echo "</div>";
        } elseif($layout == "wide_right"){
            echo "<div class=\"inventory-sidebar col-md-3 side-content listing-sidebar\">";
            dynamic_sidebar("listing_sidebar");
            echo "</div>";
        }

        if($is_ajax == false){
            echo bottom_page_box($layout);
            echo "</div>";
        }

        //echo "<div id='preview_slideshow'></div>";

        echo (!$is_ajax ? "</div>" : "");
        echo listing_youtube_video();
    }
}

function preview_slideshow_ajax(){
    $id = sanitize_text_field( $_POST['id'] );

    $gallery_images = get_post_meta($id, "gallery_images", true);


    if(!empty($gallery_images)){
        $full_images  = "";
        $thumb_images = "";

        foreach($gallery_images as $gallery_image){
            $gallery_thumb  = auto_image($gallery_image, "auto_thumb", true);
            $gallery_slider = auto_image($gallery_image, "auto_slider", true);
            $full 			= wp_get_attachment_image_src($gallery_image, "full");
            $full 			= $full[0];

            $full_images  .= "<li data-thumb=\"" . $gallery_thumb . "\"> <img src=\"" . $gallery_slider . "\" alt=\"\" data-full-image=\"" . $full . "\" /> </li>\n";
            $thumb_images .= "<li data-thumb=\"" . $gallery_thumb . "\"> <img src=\"" . $gallery_thumb . "\" alt=\"\" /> </li>\n";
        }
    } ?>

    <div class="listing-slider">
        <section class="slider home-banner">
            <a title="Close" class="fancybox-item fancybox-close" href="javascript:;" id="close_preview_area"></a>

            <div class="flexslider loading" id="home-slider-canvas">
                <ul class="slides">
                    <?php echo (!empty($full_images) ? $full_images : ""); ?>
                </ul>
            </div>
        </section>
        <section class="home-slider-thumbs">
            <div class="flexslider" id="home-slider-thumbs">
                <ul class="slides">
                    <?php echo (!empty($thumb_images) ? $thumb_images : ""); ?>
                </ul>
            </div>
        </section>
    </div>
    <!--CLOSE OF SLIDER-->
    <?php

    die;
}
add_action("wp_ajax_preview_slideshow_ajax", "preview_slideshow_ajax");
add_action("wp_ajax_nopriv_preview_slideshow_ajax", "preview_slideshow_ajax");

if(!function_exists("listing_view")){
    function listing_view($layout, $fake_get = null){
        global $lwp_options;

        $get_holder = (!is_null($fake_get) && !empty($fake_get) ? $fake_get : $_GET);

        if(is_array($fake_get) && !empty($fake_get)){
            foreach($get_holder as $key => $value){
                if(strstr($key, "_")){
                    $get_holder[str_replace("_", "-", $key)] = $value;
                    unset($get_holder[$key]);
                }
            }
        }

        $listings = listing_args($get_holder, true);
        $listings[0]['posts_per_page'] = -1;
        $listings = count(get_posts($listings[0]));

        $vehicle_singular = (isset($lwp_options['vehicle_singular_form']) && !empty($lwp_options['vehicle_singular_form']) ? $lwp_options['vehicle_singular_form'] : __('Car found', 'listings') );
        $vehicle_plural   = (isset($lwp_options['vehicle_plural_form']) && !empty($lwp_options['vehicle_plural_form']) ? $lwp_options['vehicle_plural_form'] : __('Cars found', 'listings') );
    }
}

// create new inventory listings for select view buttons
function generate_new_view(){
    $layout = $_POST['layout'];
    $page   = sanitize_text_field((isset($_POST['page']) && !empty($_POST['page']) ? $_POST['page'] : 1));
    $params = json_decode(stripslashes($_POST['params']), true);

    // paged fix
    if(isset($page) && !empty($page)){
        $params['paged'] = $page;
    }

    ob_start();
    listing_template($layout, true, $params);
    $html = ob_get_clean();

    echo json_encode(array(
        "html"        => $html,
        "top_page"    => page_of_box($page),
        "bottom_page" => bottom_page_box(false, $page),
    ));

    die;
}
add_action("wp_ajax_generate_new_view", "generate_new_view");
add_action("wp_ajax_nopriv_generate_new_view", "generate_new_view");

// for shortcode
function listing_dropdown_terms($name, $comparison = true, $encode_terms = false){
    $name   = str_replace(" ", "_", mb_strtolower($name));
    $key    = get_single_listing_category($name);
    $return = "";

    if(!empty($key['terms'])){

        asort($key['terms']);

        foreach($key['terms'] as $key => $term){
            $on_select = $term;

            if(isset($key['currency']) && $key['currency'] == 1){
                $on_select = format_currency($on_select);
            }

            if(isset($key['compare_value']) && $key['compare_value'] != "=" && $comparison === true){
                $on_select = $key['compare_value'] . " " . $on_select;
            }

            $check = (isset($_GET[str_replace(" ", "-", mb_strtolower($key['singular']))]) && !empty($_GET[str_replace(" ", "-", mb_strtolower($key['singular']))]) ? $_GET[str_replace(" ", "-", mb_strtolower($key['singular']))] : "");

            $return .= "<option value='" . str_replace(" ", "-", mb_strtolower($term)) . "'" . selected($check, str_replace(" ", "-", mb_strtolower($term)), false ) . " data-key='" . $key . "'>" . $on_select . "</option>";
        }
    } else {
        $return .= "<option>" . __("No options", "listings") . "</option>";
    }

    return $return;
}

function listing_dropdown_width($name){
    global $lwp_options;

    $name = str_replace(" ", "_", mb_strtolower($name));

    return (isset($lwp_options[$name . '_width']) && !empty($lwp_options[$name . '_width']) ? $lwp_options[$name . '_width'] . "px" : "");
}

if(!function_exists("listing_filter_sort")){
    function listing_filter_sort($fake_get = null){
        global $lwp_options;
        $get_holder = (!is_null($fake_get) && !empty($fake_get) ? $fake_get : $_GET);
        //print_r($get_holder);
        if(is_array($fake_get)){
            foreach($get_holder as $key => $value){
                if(strstr($key, "_") && isset($get_holder[str_replace("_", "-", $key)]) && !is_array($get_holder[str_replace("_", "-", $key)])){
                    $get_holder[str_replace("_", "-", $key)] = str_replace(" ", "-", mb_strtolower($value));
                    unset($get_holder[$key]);
                }
            }
        }
        ?>
        <div class="clearfix"></div>
        <form method="post" action="#" class="listing_sort">
            <div id="stickyFiller"></div>
            <div id="stickyBar">
                <div id="topSection">
                    <ul id="searchBar">
                        <li id="searchInputContainer">
                            <input id="searchInput" type="text" placeholder="I want to find a..." class="tt-input" />
                        </li>
                        <li id="refineButton" class="right"><a href="javascript:void(0);" data-dropdown="filter-dropdown" aria-expanded="false"></a></li>
                    </ul>
                    <ul id="searchControl" data-layout="boxed_fullwidth">
                        <?php if(isset($lwp_options['sortby']) && $lwp_options['sortby'] == 1){ ?>
                            <li id="sort">
                                <label>Sort by</label>
                                <div class="price-ascending-dropdown">
                                    <?php
                                    $order = (isset($get_holder['order']) && !empty($get_holder['order']) ? $get_holder['order'] : "");
                                    $listing_orderby = get_option("listing_orderby");
                                    ?>

                                    <select name="price_order" class="listing_filter" tabindex="1" >
                                        <?php
                                        if(!empty($listing_orderby)){
                                            $order_selected = (isset($_GET['order']) && !empty($_GET['order']) ? $_GET['order'] : "");
                                            if(empty($order_selected)){
                                                $selected = reset($listing_orderby);
                                                $selected = key($listing_orderby);
                                                $order_selected = $selected . "|" . (isset($lwp_options['sortby_default']) && !empty($lwp_options['sortby_default']) && $lwp_options['sortby_default'] == 1 ? "ASC" : "DESC");
                                            }

                                            echo $order_selected;

                                            foreach($listing_orderby as $key => $value){
                                                $orderby_category = get_single_listing_category($key);
                                                echo "<option value='" . $key . "|ASC' " . selected( $order_selected, $key . "|ASC" ) . ">" . $orderby_category['singular'] . " " . __("&uarr;", "listings") . "</option>";
                                                echo "<option value='" . $key . "|DESC' " . selected( $order_selected, $key . "|DESC" ) . ">" . $orderby_category['singular'] . " " . __("&darr;", "listings") . "</option>";
                                            }
                                        } else{
                                            echo "<option value='none'>" . __("Configure in listing categories", "listings") . "</option>";
                                        } ?>
                                    </select>
                                </div>
                            </li>
                        <?php } ?>

                        <?php if($lwp_options['inventory_listing_toggle'] == 1){ ?>

                            <?php
                            $buttons = array("wide_fullwidth", "boxed_fullwidth");
                            foreach($buttons as $button){
                                if($button == 'wide_fullwidth'){
                                    $id_ = "normalViewToggle";
                                }else{
                                    $id_ = "gridViewToggle";
                                }
                                $btn_class = "class='".$button."'";
                                echo "<li id='".$id_."' class='page-view-toggler " . ($button == 'boxed_fullwidth' ? " selected" : "") . "' data-layout='" . $button . "'><a ".$btn_class." href=\"#\"><i class=\"fa\"></i></a></li>";
                            }

                        }
                        ?>

                    </ul>
                </div>
                <div id="bottomSection">
                    <div id="resultsBarContainer">
                        <div class="chances">
                            <?php
                            $listings_count = listing_args($get_holder, true);
                            //print_r($listings_count);
                            $listings_count[0]['posts_per_page'] = -1;
                            $listings_count = count(get_posts($listings_count[0]));
                            ?>
                            <div class="numChances"><?php echo $listings_count==1 ? '1 car found' : ($listings_count > 9 ? $listings_count.' cars found' : $listings_count.'  cars found'); ?></div>
                            <?php /* ?><div class="subChances">To Fall in Love</div><?php */ ?>
                        </div>
                        <ul id="resultsBar">
                            <?php
                            if(!empty($get_holder)){

                                foreach($get_holder as $filter_type => $filter_val){
                                    if($filter_type!='order'){
                                        ?>
                                        <li class="filterOption" data-type="<?php echo $filter_type == 'yr' ? 'year' : $filter_type; ?>">
                                            <a class="filterOptionText" data-type="<?php echo $filter_type == 'yr' ? 'year' : $filter_type; ?>" data-val="<?php echo $filter_val; ?>" href="javascript:void(0);"><?php echo $filter_type == 'price' ? 'max $'.listingNumberAbbreviation($filter_val) : ($filter_type == 'mileage' ? 'max '.listingNumberAbbreviation($filter_val).' Km' : $filter_val); ?></a>
                                            <a class="filterOptionClose" data-type="<?php echo $filter_type == 'yr' ? 'year' : $filter_type; ?>" href="javascript:void(0);"></a>
                                        </li>
                                    <?php
                                    }
                                }

                            }
                            ?>
                            <li id="filter-clear"><a class="clear-all" href="#">Clear Filters</a></li>
                        </ul>
                    </div>
                    <div id="notExactMatch">
                        <div class="leftText"><span class="redText">Nothing Quite Matches</span></div>
                    </div>
                    <?php listing_view("boxed_fullwidth"); ?>
                </div>
                <div id="filter-dropdown" class="mega scrollable content f-dropdown" data-dropdown-content="" aria-autoclose="false" aria-hidden="true" tabindex="-1">
                    <div class="row dd-container">
                        <div class="small-12 columns">
                            <div id="dropDownHeader"></div>
                            <a class="close-dd" href="javascript:void(0);"></a>
                        </div>
                        <div class="small-12 columns dropdown-filter-section">
                            <div class="select-wrapper listing_select clearfix margin-bottom-15">

                                <?php
                                $filterable_categories = get_filterable_listing_categories();

                                arsort($filterable_categories);

                                foreach($filterable_categories as $key){
                                    $meta    = str_replace("_", "-", mb_strtolower($key['singular']));
                                    $text    = str_replace("_", " ", $key['plural']);
                                    $wpml_term = wpml_category_translate($key['singular'], "singular", $key['singular']);
                                    ?>
                                    <div class="my-dropdown <?php echo str_replace(" ", "_", $meta); ?>-dropdown">
                                        <?php
                                        if(function_exists("icl_translate")){
                                            $text = icl_translate("Automotive Listing Category", $key['singular'] . " Plural", $key['plural']);
                                        }
                                        ?>
                                        <div class="filter-label"><?php echo $text; ?></div>
                                        <?php
                                        $singular = $key['singular'];
                                        $get_select = (mb_strtolower($singular) == "year" ? "yr" : str_replace(" ", "_", mb_strtolower($singular)));
                                        $get_value  = (isset($get_holder[$get_select]) && !empty($get_holder[$get_select]) ? $get_holder[$get_select] : "");
                                        $get_value  = str_replace("--", "-", $get_value);

                                        if($key['singular'] == 'Mileage' || $key['singular'] == 'Price'){
                                        if(!empty($key['terms'])){
                                            $all_values = $key['terms'];
                                            $min_value = $all_values[0];
                                            $max_value = $all_values[count($all_values)-1];
                                            ?>
                                        <input data-sort="<?php echo str_replace(" ", "_", mb_strtolower($key['singular'])); ?>" type="text" value="" name="<?php echo str_replace(" ", "_", mb_strtolower($key['singular'])); ?>" id="<?php echo str_replace(" ", "_", mb_strtolower($singular)); ?>_slider" />
                                            <script type="text/javascript">
                                                jQuery(window).load(function(){
                                                    var range_<?php echo str_replace(" ", "_", mb_strtolower($singular)); ?> = jQuery('#<?php echo str_replace(" ", "_", mb_strtolower($singular)); ?>_slider');
                                                    range_<?php echo str_replace(" ", "_", mb_strtolower($singular)); ?>.ionRangeSlider({
                                                        type : 'single',
                                                        min : 0,
                                                        max : <?php echo $max_value; ?>,
                                                        <?php if($get_value){ ?>from: <?php echo $get_value; ?>,<?php } ?>
                                                        step: 1e4,
                                                        hide_min_max: !1,
                                                        prettify_enabled: !0,
                                                        force_edges: !0,
                                                        prettify: function(n){
                                                            <?php if($key['singular'] == 'Mileage'){ ?>
                                                            return n === this.min ? "NEW" : Utilities.numberWithCommas(n) + " Km"
                                                            <?php }else{ ?>
                                                            return n === this.min ? "FREE" : "$" + Utilities.numberWithCommas(n)
                                                            <?php } ?>
                                                        },
                                                        name: "<?php echo str_replace(" ", "_", mb_strtolower($singular)); ?>Slider",
                                                        onFinish: sliderChange
                                                    });
                                                    var range_slider_<?php echo str_replace(" ", "_", mb_strtolower($singular)); ?> = range_<?php echo str_replace(" ", "_", mb_strtolower($singular)); ?>.data("ionRangeSlider");
                                                });
                                            </script>
                                        <?php
                                        }else{
                                            echo '<p>No data available.</p>';
                                        }
                                        }else{

                                        ?>
                                        <input type="radio" data-sort="<?php echo str_replace(" ", "_", mb_strtolower($key['singular'])); ?>" name="<?php echo (mb_strtolower($key['singular']) == "year" ? "yr" : str_replace(" ", "_", mb_strtolower($key['singular']))); ?>" value="all" <?php echo checked( $get_value, '', false ); ?>><label> <?php _e("All", "listings"); ?></label>

                                            <?php
                                        if(!empty($key['terms'])){
                                            if(isset($key['sort_terms']) && $key['sort_terms'] == "desc"){
                                                arsort($key['terms']);
                                            } else{
                                                asort($key['terms']);
                                            }

                                        foreach($key['terms'] as $term_key => $term){
                                            $term = stripslashes($term);
                                            $option_val = $term;
                                            if(function_exists("icl_translate")){
                                                $term = icl_translate("Automotive Listing Category", $key['singular'] . " Term " . ($term_key + 1), $term);
                                            }

                                            $on_select  = stripslashes($term);
                                            $get_select = (mb_strtolower($singular) == "year" ? "yr" : str_replace(" ", "_", mb_strtolower($singular)));

                                            $get_value  = (isset($get_holder[$get_select]) && !empty($get_holder[$get_select]) ? $get_holder[$get_select] : "");

                                            // dashes
                                            $get_value  = str_replace("--", "-", $get_value);

                                            if(isset($key['currency']) && $key['currency'] == 1){
                                                $on_select = format_currency($on_select);
                                            }

                                            if(isset($key['compare_value']) && $key['compare_value'] != "="){
                                                $on_select = $key['compare_value'] . " " . $on_select;
                                            }

                                            $translated_term = $term;
                                            ?>
                                        <input type="radio" name="<?php echo (mb_strtolower($key['singular']) == "year" ? "yr" : str_replace(" ", "_", mb_strtolower($key['singular']))); ?>" value="<?php echo $option_val; ?>" <?php echo checked( $get_value, str_replace(" ", "-", mb_strtolower($term)), false ); ?> data-key="<?php echo $term_key; ?>" data-sort="<?php echo str_replace(" ", "_", mb_strtolower($key['singular'])); ?>" data-label-plural="<?php echo $key['plural']; ?>" data-label-singular="<?php echo $key['singular']; ?>" tabindex="1" <?php echo ($key['compare_value'] != "=" ? " data-compare-value='" . htmlspecialchars($key['compare_value']) . "' " : ""); ?> <?php echo (isset($key['dependancy']) && !empty($key['dependancy']) ? " data-dependancy='" . $key['dependancy'] . "'" : ""); ?> <?php echo (!empty($wpml_term) && $wpml_term != $key['singular'] ? " data-translate-term='" . $wpml_term . "'" : ""); ?> /> <label><?php echo $on_select; ?></label>
                                        <?php
                                        }
                                        } else{
                                            //echo "<option>" . __("No options", "listings") . "</option>";
                                        }
                                        }
                                        ?>
                                    </div>

                                <?php } ?>

                                <div class="loading_results">
                                    <i class="fa fa-circle-o-notch fa-spin"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="select-wrapper pagination clearfix margin-bottom-none">
                <?php $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                global $lwp_options; ?>
                <div class="text-center">
                    <?php echo page_of_box(false, $fake_get); ?>
                </div>
            </div>
        </form>
    <?php
    }
}

if(!function_exists("listing_content")){
    function listing_content(){
        global $post, $lwp_options;

        //wp_enqueue_script( 'google-maps' );

        $post_meta       = get_post_meta_all($post->ID);
        $location        = (isset($post_meta['location_map']) && !empty($post_meta['location_map']) ? unserialize($post_meta['location_map']) : "");
        if(isset($post_meta['listing_options']) && !empty($post_meta['listing_options'])){
            $listing_options = unserialize(unserialize($post_meta['listing_options']));
            $options         = unserialize(unserialize($post_meta['listing_options']));
        }

        $price_text  = '';

        if(isset($listing_options['price']['value']) && !empty($listing_options['price']['value'])){
            $original = (isset($listing_options['price']['original']) && !empty($listing_options['price']['original']) ? $listing_options['price']['original'] : $listing_options['price']['value']);

            $price_text = $price_text_original = !empty($original) ? format_currency($original) : "";
        }

        $biweekly_price = get_post_meta($post->ID, 'car_biweekly_price', true);
        if(!empty($biweekly_price)){
            $price_text .= ' <span>EST. '.format_currency($biweekly_price).'/Bi-Weekly</span>';
        }

        $gallery_images  = get_post_meta($post->ID, "gallery_images");
        $gallery_images  = (isset($gallery_images[0]) && !empty($gallery_images[0]) ? $gallery_images[0] : "");

        $slick_full = wp_get_attachment_image_src($gallery_images[0], "full");
        $slick_full = $slick_full[0];

        $multi_text      = "";
        $multi_text_all      = "";
        $multi_pdf       = "";
        if(isset($post_meta['multi_options']) && !empty($post_meta['multi_options'])){
            $multi_options = unserialize($post_meta['multi_options']);
            $counter = 0;
            foreach($multi_options as $option){
                $counter++;
                if($counter <= 4){
                    $multi_text .= "<li><span class=\"fa-li ".strtolower(str_replace(' ', '_', $option))."\"></span> " . $option . "</li>";
                }

                $multi_text_all .= "<li><i class='fa fa-cog' aria-hidden='true'></i>  " . $option . "</li>";

                if($counter == 5){
                    $multi_text .="<li><a class='see_all_features' href='javascript:void(0)'>See all</a></li>";
                }

                $multi_pdf  .= $option . ", ";
            }

            $multi_pdf = rtrim($multi_pdf, ", ");
        } else {
            //$text = __("There are no features available", "listings");

            //$multi_text .= "<li>" . $text . "</li>";
            //$multi_pdf  .= $text;
        } ?>


        <div class="wrapper">
            <div id="sb-site">
                <div class="nav only-wide caption">
                    <div class="container">
                        <div class="row">
                            <section class="12u">
                                <div class="blueRibbon">
                                    <table style="width: 100%;">
                                        <tbody>
                                        <tr>
                                            <td style="width: 200px;">
                                                <a href="<?php echo site_url('buy'); ?>" style="text-decoration: none;">
                                                    <div class="bt_back_conten">
                                                        <div class="icon_back sprite_carpage"></div>
                                                        <div class="text_back txt18">Back</div>
                                                    </div>
                                                </a>
                                            </td>
                                            <td class="txt21 white" style="text-align: center; vertical-align: middle;">
                                                <div>
                                                    	<span style="font-weight: 700;">
                                                        	<span id="cphMain_cphMain_cphMain_lblYearMakeModel_Wide"><?php the_title(); ?></span>
                                                        </span>
                                                </div>
                                            </td>
                                            <td style="width: 200px;">
                                                <a href="http://www.pinterest.com/pin/create/button/?url=<?php the_permalink(); ?>&amp;media=<?php echo $slick_full; ?>&amp;description=<?php the_title(); ?>" target="_blank" style="cursor: pointer;">
                                                    <div class="sprite_share bt_pinterest"></div>
                                                </a>
                                                <a href="https://twitter.com/share?url=<?php the_permalink(); ?>&amp;text=<?php the_title(); ?>" target="_blank" style="cursor: pointer;">
                                                    <div class="sprite_share bt_twitter"></div>
                                                </a>
                                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php the_permalink(); ?>" target="_blank" style="cursor: pointer;">
                                                    <div class="sprite_share bt_facebook"></div>
                                                </a>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
                <div class="topbar not-wide">
                    <div class="container">
                        <div class="row">
                            <section class="12u" id="content">
                                <a href="<?php echo site_url('buy'); ?>"><div class="bt_back sprite_carpage"></div></a>
                                <div class="topbar_conten_txt">
                                    <div class="topbar_txt txt16 white">
                                        <strong><span id="cphMain_cphMain_cphMain_lblYearMakeModel_Mobile"><?php the_title(); ?></span></strong>
                                    </div>
                                </div>
                                <div class="bt_share sprite_share see_pop_up_share"></div>
                            </section>
                        </div>
                    </div>
                </div>



                <?php
                if(!empty($gallery_images)){
                    $full_images  = "";
                    $thumb_images = "";
                    $top_slider = '';
                    $magnified_slider = '';
                    $thumb_slider = '';
                    foreach($gallery_images as $gallery_image){

                        $slick_full = wp_get_attachment_image_src($gallery_image, "full");
                        $slick_full = $slick_full[0];
                        $slick_thumb = auto_image($gallery_image, "slick-thumb", true);

                        $top_slider .= '<div><div class="pic" data-src="'.$slick_full.'"></div></div>';
                        $magnified_slider .= '<div style="background-image: url('.$slick_full.')" class="image-magnified" data-src="'.$slick_full.'"></div>';
                        $thumb_slider .= '<div><img src="'.$slick_thumb.'" /></div>';

                    }

                    if($post_meta['car_sold'] && ($post_meta['car_sold'] == 'sold' || $post_meta['car_sold'] == 'reserved') ){
                        ?>
                        <div class="car_status car_<?php echo $post_meta['car_sold']; ?>">
                            <span><?php echo $post_meta['car_sold']; ?></span>
                        </div>
                        <?php
                    }

                    ?>

                    <div class="top_slider">
                        <?php echo $top_slider; ?>
                    </div>

                    <div id="overlayGallery" class="skel-panels-overlay skel-panels-overlay-top" style="backface-visibility: hidden; transition: -webkit-transform 0.25s ease-in-out; z-index: 10000; position: fixed; width: 100%; height: 100%; top: 0px; left: 50%; margin-left: -50%;">
                        <div data-action="moveCell" data-args="pop_up_gallery,content">
                            <div id="pop_up_gallery" class="pop_up_gallery">
                                <div class="slider-options" style="width: 96px;">
                                    <div class="screen-options fit-to-screen" style="display: block;"></div>
                                    <div class="close-gallery" style="left: 40px; top: -24px;"></div>
                                </div>
                                <div class="pics">
                                    <?php echo $magnified_slider; ?>
                                </div>
                                <div class="thumbs_nav">
                                    <div class="wrapper">
                                        <div class="close-thumb-nav"></div>
                                        <div class="thumbs">
                                            <?php echo $thumb_slider; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="car_band">
                        <div class="price-band">
                            <div class="container" id="content">
                                <div class="row">
                                    <section class="12u">
                                        <div class="open_gallery">
                                            <div class="bt_gallery txt21 white open_gallery" style="line-height: 30px;">
                                                <div class="gallery_icon sprite_carpage"></div>
                                                <div class="gallery_txt">Open photo gallery</div>
                                            </div>
                                        </div>
                                        <div class="car_price white">
                                            <div class="content_price">
                                                <p class="price"><?php echo $price_text; ?></p>
                                            </div>
                                        </div>
                                    </section>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php /*?><div id="topBar" class="skel-panels-overlay skel-panels-overlay-top" style="backface-visibility: hidden; transition: -webkit-transform 0.25s ease-in-out; z-index: 10000; position: fixed; width: 100%; height: 50px; top: 0px; left: 50%; margin-left: -50%;">
                	<div data-action="moveCell" data-args="top_bar,content">
                    	<div class="top_bar not-mobile" id="top_bar" style="width: auto; display: block;">
                            <div class="container" id="content">
                                <div class="row">
                                    <section class="12u">
                                        <div class="car_conten_txt">
                                            <div id="cphMain_cphMain_cphMain_pnlTopBarCarPhoto" class="car_pic">
                                        </div>
                                            <div class="car_txt txt16 white">
                                                <strong>
                                                    <span id="cphMain_cphMain_cphMain_lblYearMakeModel_Overlay"><?php the_title(); ?></span></strong>
                                            </div>


                                            <div id="topbar-buyBtn" class="bt_cyan white txt18" style="display: block"><span class="ubuntu_bold btnBuyMe" id="mixpanel-buyBtn-ribbon" style="color: #ffffff">Buy me!</span></div>


                                            <div class="pirce white txt30" style="display: block;">
                                                <div>
                                                    <span style="color: #ffffff"><strong>
                                                        <span id="cphMain_cphMain_cphMain_lblBeepiPrice_TopBarNarrow"><?php echo $price_text; ?></span></strong></span>
                                                </div>
                                            </div>
                                        </div>
                                    </section>
                                </div>
                            </div>
                		</div>
                  	</div>
              	</div><?php */?>

                <?php } ?>

                <div class="features-overlay" style="display: none;">
                    <div class="features-modal-wrap">
                        <div class="features-modal">
                            <a class="close-icon-grey"></a>
                            <div class="clearfix"></div>
                            <div class="features_and_options">
                                <div class="col-sm-12 modal-title text-center">Features & Options</div>
                                <ul class="fa-ul">
                                    <?php echo $multi_text_all; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if(!empty($gallery_images)){ ?>

                    <div class="carSliderConten not-wide not-mobile">
                        <div class="carSlider">
                            <div class="carRibbons only-mobile">
                                <div class="ribbon">
                                    <div class="conten">
                                        <div class="text bt_gallery">Open gallery</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php } ?>

                <div id="main" class="main">
                    <div class="container" id="content">
                        <div class="row">
                            <div class="8u ajuste7u">

                                <?php
                                $first_tab 	= (isset($lwp_options['first_tab']) && !empty($lwp_options['first_tab']) ? $lwp_options['first_tab'] : "" );
                                $second_tab = (isset($lwp_options['second_tab']) && !empty($lwp_options['second_tab']) ? $lwp_options['second_tab'] : "" );
                                $third_tab 	= (isset($lwp_options['third_tab']) && !empty($lwp_options['third_tab']) ? $lwp_options['third_tab'] : "" );
                                $fourth_tab = (isset($lwp_options['fourth_tab']) && !empty($lwp_options['fourth_tab']) ? $lwp_options['fourth_tab'] : "" );
                                $fifth_tab 	= (isset($lwp_options['fifth_tab']) && !empty($lwp_options['fifth_tab']) ? $lwp_options['fifth_tab'] : "" );
                                ?>
                                <?php if($first_tab){ ?>
                                    <div class="first-tab car-section clearfix vehical-overview">
                                        <h2 class="listing-section-title txt24"><?php echo $first_tab; ?></h2>
                                      	<div class="readmore-js">
                                      	<?php
                                      	$ov_items_meta = get_post_meta($post->ID, "ov_items_meta", true);
                                      	$ov_items_meta_arr = (array)json_decode($ov_items_meta);
                                      	if(!empty($ov_items_meta_arr)){
                                      	    echo '<table class="ov_icon_items clearfix clear"><tr>';
                                      	    $count = 0;
                                      	    foreach ($ov_items_meta_arr as $ov_item){
                                      	        $count++;
                                      	        ?>
                                      	        <td><span><img src="<?php bloginfo('stylesheet_directory');?>/images/<?php echo /*strtolower($ov_item).*/'mileage.png'; ?>"></span><?php echo $ov_item; ?></td>
                                      	        <?php
                                      	        if($count % 4 == 0){
                                      	            echo '</tr><tr>';
                                      	        }
                                      	    }
                                      	    echo '</tr></table>';
                                      	}
                                      	?>
                                      	<style>
                                      	/*.first-tab.vehical-overview { background: transparent; }
                                      	.ov_text { background: #F7F7F7; padding: 20px 20px 10px 20px; border-top: 2px solid #ECECEC; }*/
                                      	.ov_text.seemore-div{ height: 110px; }
                                      	.ov_icon_items { margin-bottom: 30px; }
                                      	.ov_icon_items td { text-transform: capitalize; }
                                      	.ov_icon_items td img { margin-right: 5px; }
                                      	.ov_icon_items td span { display: inline-block; vertical-align: middle; }
                                      	</style>
                                      	<div class="ov_text seemore-div">
                                            <?php the_content(); ?>
                                        </div>
                                        <a style="padding-left: 0;" class="seemore" href="javascript:void(0)">see more</a>
                                    	</div>
                                      </div>
                                <?php } ?>

                            </div>

                            <!--<section class="1u only-wide">&nbsp;</section>-->

                            <section class="4u full-in-mobile">
                                <div class="greyBorder">
                                    <div class="inspectorProfile2">
                                        <div class="text-center">
                                            <form action="<?php echo site_url('payment'); ?>" method="get">
                                                <input type="hidden" name="car" value="<?php the_id(); ?>" />
                                                <button type="submit" name="financing" value="true" class="blueButton txt18 margin-bottom-10"><strong>Financing</strong></button>
                                                <p style="margin-bottom: 10px;"><a target="_blank" href="<?php echo site_url('financing'); ?>"><small><em>* Learn more about financing</em></small></a></p>
                                                <button type="submit" name="fullpay" value="true" class="blueButton txt18 margin-bottom-10"><strong>Full Pay</strong></button>
                                            </form>
                                            <div class="not-mobile open-buyform-btn btBordeCelesteBuyNow celeste txt18 blueButton" id="open-buyform-btn"><strong>Contact Us!</strong></div>
                                            <?php
                                            if(isset($lwp_options['fuel_efficiency_show']) && $lwp_options['fuel_efficiency_show'] == 1){
                                                ?>
                                                <div class="efficiency-rating text-center padding-vertical-15 margin-bottom-0 margin-top-15">
                                                    <ul>
                                                        <?php $fuel_icon = (isset($lwp_options['fuel_efficiency_image']) && !empty($lwp_options['fuel_efficiency_image']) ? $lwp_options['fuel_efficiency_image']['url'] : ICON_DIR . "fuel_pump.png"); ?>
                                                        <li class="city_mpg"><?php echo (isset($listing_options['city_mpg']['text']) && !empty($listing_options['city_mpg']['text']) ? $listing_options['city_mpg']['text'] : ""); ?>: <strong><?php echo (isset($listing_options['city_mpg']['value']) && !empty($listing_options['city_mpg']['value']) ? $listing_options['city_mpg']['value'].' <subscript>L/100KM</subscript>' : __("N/A", "listings")); ?></strong></li>
                                                        <li class="fuel"><img src="<?php echo $fuel_icon; ?>" alt="" class="aligncenter"></li>
                                                        <li class="hwy_mpg"><?php echo (isset($listing_options['highway_mpg']['text']) && !empty($listing_options['highway_mpg']['text']) ? $listing_options['highway_mpg']['text'] : ""); ?>: <strong><?php echo (isset($listing_options['highway_mpg']['value']) && !empty($listing_options['highway_mpg']['value']) ? $listing_options['highway_mpg']['value'].' <subscript>L/100KM</subscript>' : __("N/A", "listings")); ?></strong></li>
                                                    </ul>
                                                    <p><?php echo (isset($lwp_options['fuel_efficiency_text']) ? $lwp_options['fuel_efficiency_text'] : ""); ?></p>
                                                </div>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>

                            </section>

                            <section class="clearfix 12u">

                                <style>
                                    .vehicle_history_report_request span.wpcf7-not-valid-tip{ display: none !important; }
                                    .vehicle_history_report_request div.wpcf7-response-output {
                                        max-width: 450px;
                                        margin-left: auto;
                                        margin-right: auto;
                                    }
                                </style>
                                <div class="second-tab car-section text-center clearfix vehicle_history_report_request margin-top-40">
                                    <h2 class="listing-section-title txt24">FREE vehicle history report</h2>
                                    <div class="vehicle_history_report_request" id="vehicle_history">
                                        <?php echo do_shortcode('[contact-form-7 id="571" title="Vehicle History Report Request Form"]'); ?>
                                    </div>
                                    <div class="text-center">Provided by: <img style="vertical-align: middle;" src="<?php bloginfo('stylesheet_directory'); ?>/images/carproof_small.jpg" /></div>
                                </div>

                                <?php
                                if($third_tab){
                                    ?>
                                    <div class="third-tab car-section clearfix vehical-technical">
                                        <h2 class="listing-section-title text-center txt24"><?php echo $third_tab; ?></h2>
                                        <div id="technical" class="seemore-div">
                                            <?php echo wpautop(do_shortcode($post_meta['technical_specifications'])); ?>
                                        </div>
                                      <a class="seemore" href="javascript:void(0);">see more</a>
                                    </div>
                                <?php
                                }
                                ?>

                                <div class="car-section clearfix text-center margin-top-40">
                                    <h2 class="listing-section-title txt24">200-Point Inspection</h2>
                                    <div class="col-md-3">
                                        <?php
                                        if($post_meta['vehicle_inspector_name']){
                                            $auth_email = $post_meta['vehicle_inspector_name'];
                                        }else{
                                            $auth_email = get_the_author_meta('email', $post->post_author);
                                        }
                                       if($post_meta['vehicle_inspector_name']){
                                            $auth_name = $post_meta['vehicle_inspector_name'];
                                       }else{
                                           $auth_name = get_the_author_meta('display_name', $post->post_author);
                                       }
                                        echo '<p><span class="inspector-circle">'.get_avatar($auth_email, '200').'</span></p>';
                                        echo '<p class="small margin-top-5">'.$auth_name.'</p>';
                                        echo '<h2 class="txt24 margin-top-5">Vehicle Inspector</h2>';
                                        ?>
                                    </div>
                                    <div class="col-md-9">
                                        <p class="inspection-criterias margin-bottom-15">
                                            <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/CarPage/ins-1.png">
                                            <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/CarPage/ins-2.png">
                                            <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/CarPage/ins-3.png">
                                            <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/CarPage/ins-4.png">
                                            <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/CarPage/ins-5.png">
                                        </p>
                                        <h4 class="margin-bottom-15 margin-top-15 bold"><?php the_title(); ?></h4>
                                        <p style="max-width: 768px;margin-left: auto; margin-right: auto;">In addition to having a clean title, we have ensured that this car is in good mechanical condition. It has been thoroughly examined by a qualified mechanic. We have looked for overly worn tires and brakes, checked for any fluid or oil leaks, and addressed all safety related components.</p>
                                        <p class="text-center related-cars"><a data-listing_id="<?php the_ID(); ?>" class="blueButton txt24 viewinspectionreport" href="javascript:void(0);">View Inspection Report</a></p>
                                    </div>
                                </div>

                                <div class="car-section clearfix car-pricing-section">
                                    <h2 class="listing-section-title text-center txt24">Pricing</h2>
                                    <div class="features_and_options" id="car_pricing">
                                        <?php if($post_meta['car_pricing_text']){ ?><div style="max-width: 500px; margin: 0 auto 20px;" class="text-center"><?php echo $post_meta['car_pricing_text']; ?></div><?php } ?>
                                        <div class="txt24 text-center" style="color: #029eff; font-weight: 700;"><?php echo $price_text_original; ?></div>
                                        <div class="text-center small text-uppercase margin-top-10"><span>Our Awesome Price!</span></div>
                                    </div>
                                </div>

                                <?php
                                if($second_tab){
                                    ?>
                                    <div class="second-tab car-section clearfix features_and_options">
                                        <h2 class="listing-section-title text-center txt24"><?php echo $second_tab; ?></h2>
                                        <div class="features_and_options" id="features">
                                            <ul class="fa-ul">
                                                <?php echo $multi_text_all; ?>
                                            </ul>
                                        </div>
                                    </div>
                                <?php
                                }
                                ?>

                                <?php
                                if($fourth_tab){/*
                                    ?>
                                    <div class="fourth-tab car-section clearfix vehical-location">
                                        <h2 class="listing-section-title txt24"><?php echo $fourth_tab; ?></h2>
                                        <div id="location">
                                            <?php
                                            $latitude  = (isset($location['latitude']) && !empty($location['latitude']) ? $location['latitude'] : "");
                                            $longitude = (isset($location['longitude']) && !empty($location['longitude']) ? $location['longitude'] : "");
                                            $zoom      = (isset($location['zoom']) && !empty($location['zoom']) ? $location['zoom'] : 11);

                                            if(!empty($latitude) && !empty($longitude)){ ?>
                                                <div class="google_map_init" class="contact" data-longitude='<?php echo $longitude; ?>' data-latitude='<?php echo $latitude; ?>' data-zoom='15' data-scroll="false" style="height: 350px;" data-parallax="false"></div>
                                            <?php } else { ?>
                                                <?php _e("No location available", "listings"); ?>
                                            <?php } ?>
                                        </div>
                                    </div>
                                <?php
                                */}

                                /*if(isset($lwp_options['display_vehicle_video']) && $lwp_options['display_vehicle_video'] == 1 && !empty($listing_options['video'])){
                                    ?>
                                    <div class="fifth-tab car-section clearfix vehicle_video">
                                        <h2 class="listing-section-title txt24">Vehicle Video</h2>
                                        <?php
                                        $url = parse_url($listing_options['video']);
                                        $video_frame = '';
                                        if($url['host'] == "www.youtube.com" || $url['host'] == "youtube.com"){
                                            $video_id = str_replace("v=", "", $url['query']);
                                            $video_frame = "<iframe width=\"560\" height=\"315\" src=\"//www.youtube.com/embed/" . $video_id . "\" frameborder=\"0\" allowfullscreen></iframe>";
                                        } elseif($url['host'] == "www.vimeo.com" || $url['host'] == "vimeo.com"){
                                            $video_id = $url['path'];
                                            $video_frame = "<iframe width=\"560\" height=\"315\" src=\"//player.vimeo.com/video" . $video_id . "\" frameborder=\"0\" allowfullscreen></iframe>";
                                        }
                                        ?>
                                        <div id="vehicle_video">
                                            <div class="txt16">
                                                <?php if($video_frame){ echo $video_frame; }else{ echo '<p>No videos available.</p>'; } ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                }*/
                                /*if($fifth_tab){
                                    ?>
                                    <div class="sixth-tab car-section clearfix vehical-comments">
                                        <h2 class="listing-section-title txt24"><?php echo $fifth_tab; ?></h2>
                                        <div id="comments">
                                            <div class="txt16" style="line-height: 22px;"><?php echo (isset($post_meta['other_comments']) && !empty($post_meta['other_comments']) ? wpautop(do_shortcode($post_meta['other_comments'])) : ""); ?></div>
                                        </div>
                                    </div>
                                <?php
                                }*/
                                ?>
                                <div class="clearfix car-section text-center share-car">
                                    <h2 class="listing-section-title txt24">Share this car</h2>
                                    <div class="clearfix">
                                        <a href="https://twitter.com/share?url=<?php the_permalink(); ?>&amp;text=<?php the_title(); ?>" target="_blank" style="cursor: pointer;">
                                            <i class="fa fa-twitter-square"></i> Tweet this car
                                        </a>
                                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php the_permalink(); ?>" target="_blank" style="cursor: pointer;">
                                            <i class="fa fa-facebook-square"></i> Share this car
                                        </a>
                                        <a href="mailto:?subject=<?php the_title(); ?>&amp;body=I%20thought%20you%20might%20like%20this%20<?php the_title(); ?>-%20<?php the_permalink(); ?>.%20It%E2%80%99s%20being%20sold%20through%20a%20company%20called%20Listedmotors%20-%20they%E2%80%99ll%20even%20bring%20it%20to%20your%20door%20for%20a%20test%20drive."><i class="fa fa-envelope"></i> Email this car</a>
                                    </div>
                                </div>
                                <div class="clearfix related-cars single-listing">
                                    <h2 class="listing-section-title txt24 text-center">Other cars you might like</h2>
                                    <p class="text-center">Make: <strong><?php echo $make = get_post_meta(get_the_ID(), 'make', true) ?></strong></p>
                                    <div id="carList" class="generate_new grid-view">
                                        <div class="inventory_box car_listings boxed boxed_full">
                                            <?php
                                            if($make && $make!='None') {
                                                $args = array(
                                                    'post_type' => 'listings',
                                                    'posts_per_page' => 3,
                                                    'exclude' => get_the_ID(),
                                                    'meta_key' => 'make',
                                                    'meta_value' => $make,
                                                    'orderby' => 'date',
                                                    'order' => 'desc'
                                                );
                                                $cars = get_posts($args);
                                                //print_r($cars);
                                                if ($cars) {
                                                    foreach ($cars as $car) {
                                                        echo inventory_listing($car->ID, "fullwidth");
                                                    }
                                                }
                                            }else{
                                                ?>
                                                <p class="text-center">No matches found.</p>
                                            <?php
                                            }
                                            ?>
                                            <div class="clearfix"></div>
                                            <div class="clearfix text-center">
                                                <a class="txt24 blueButton margin-top-15 margin-bottom-15" href="<?php echo site_url('buy'); ?>">Browse more cars</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="car-section clearfix contact-for-car">
                                    <h2 class="listing-section-title txt24 text-center">Don't find the car for you?</h2>
                                    <div class="open-buyform-btn btBordeCelesteBuyNow celeste txt18 blueButton"><strong>Contact Us!</strong></div>
                                </div>
                            </section>

                        </div>
                    </div>
                </div>
                <!-- } Content (main) -->

                <?php /* ?><div class="variant_conten_mobile only-mobile">
        <div class="container" id="content">
            <div class="row">
                <section class="12u">
                    <div class="greyBorder contact-us-form-wrap">
                    	<br clear="all">
                        <h2 class="text-center">Ask us about the car</h2>
                        <br clear="all">
						<?php echo do_shortcode('[contact-form-7 id="137" title="Buy Form (mobile)"]'); ?>
                        <br clear="all">
                        <br clear="all">
                    </div>
                </section>
            </div>
        </div>
    </div>
    <?php */ ?>
                <!-- } Pre Footer - Only Mobile -->

                <!-- } Footer - Narrow, Wide  -->

                <!-- Overlays { -->
                <!-- Top Bar - Narrow, Wide { -->
                <div id="skel-panels-tmp-top_bar"></div>
                <!-- } Top Bar - Narrow, Wide -->

                <!-- Pop Up Icons { -->
                <div id="skel-panels-tmp-pop_up_icons"></div>
                <!-- } Pop Up Icons -->

                <!-- Pop Up Tabs { -->
                <div id="skel-panels-tmp-pop_up_tabs"></div>
                <!-- } Pop Up Tabs -->

                <!-- Pop Up Shields { -->
                <div id="pop_up_shields" class="pop_up_shields">
                    <div class="pop_up_shields_conten">
                        <div class="feature_spec_conten_close">
                            <div class="bt_close_pop_up_shields sprite_carpage"></div>
                        </div>

                        <div class="feature_spec_conten">

                            <div class="feature_spec_txt" id="shield_01">
                                <div class="guarantee_content">
                                    <div class="ico_guarantee_1 sprite_guarantee"></div>
                                </div>
                                <br>
                                <div class="guarantee_text">
                                    This Beepi car comes with a Beepi-Certified
                                    <span id="cphMain_cphMain_cphMain_lblBeepiWarranty_Description_Mobile"></span>
                                    Peace of Mind complete warranty.
                                </div>
                            </div>
                            <div class="feature_spec_txt" id="shield_02">
                                <div class="guarantee_content">
                                    <div class="ico_guarantee_2 sprite_guarantee"></div>
                                </div>
                                <br>
                                <div class="guarantee_text">Every Beepi car comes with Beepi's unparalleled <b>10-day money-back guarantee</b>. Don't absolutely love your new Beepi? We'll pick it up for a full refund!</div>
                            </div>


                            <div class="feature_spec_txt" id="shield_03">
                                <div class="guarantee_content">
                                    <div class="ico_guarantee_3 sprite_guarantee"></div>
                                </div>
                                <br>
                                <div class="guarantee_text">
                                    This Beepi car is covered by
                                    <span id="cphMain_cphMain_cphMain_lblMakeName_FullWarranty_Mobile">Mazda</span>'s <strong>full warranty</strong> for an additional
                                    <span id="cphMain_cphMain_cphMain_lblFullWarranty_Time_Mobile">12 months / 11,500 Km</span>
                                </div>
                            </div>



                            <div class="feature_spec_txt" id="shield_04">
                                <div class="guarantee_content">
                                    <div class="ico_guarantee_4 sprite_guarantee"></div>
                                </div>
                                <br>
                                <div class="guarantee_text">
                                    This Beepi car is covered by
                                    <span id="cphMain_cphMain_cphMain_lblMakeName_DrivetrainWarranty_Mobile">Mazda</span>'s <strong>drivetrain warranty</strong> for an additional
                                    <span id="cphMain_cphMain_cphMain_lblDrivetrainWarranty_Time_Mobile">36 months / 35,500 Km</span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- } Pop Up Shields -->
                <!-- Pop Up Zoom { -->
                <div id="skel-panels-tmp-pop_up_zoom"></div>
                <!-- } Pop Up Zoom -->

                <!-- Pop Up Share { -->
                <div id="pop_up_share" class="pop_up_share">
                    <div class="pop_up_share_conten">

                        <div class="feature_spec_conten_close">
                            <div class="bt_close_pop_up_share sprite_carpage"></div>
                        </div>

                        <div class="feature_spec_conten">

                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php the_permalink(); ?>" target="_blank" style="cursor: pointer;">
                                <div class="module_share_conten">
                                    <div class="sprite_share bt_facebook"></div>
                                    <div class="module_share_txt">Share on Facebook</div>
                                </div>
                            </a>

                            <a href="https://twitter.com/share?url=<?php the_permalink(); ?>&amp;text=<?php the_title(); ?>" target="_blank" style="cursor: pointer;">
                                <div class="module_share_conten">
                                    <div class="sprite_share bt_twitter"></div>
                                    <div class="module_share_txt">Share on Twitter</div>
                                </div>
                            </a>

                            <a href="http://www.pinterest.com/pin/create/button/?url=<?php the_permalink(); ?>&amp;media=<?php echo $slick_full; ?>&amp;description=<?php the_title(); ?>" target="_blank" style="cursor: pointer;">
                                <div class="module_share_conten">
                                    <div class="sprite_share bt_pinterest"></div>
                                    <div class="module_share_txt">Share on Pinterest</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
            <div class="push"></div>
        </div>

    <?php
    }
}

if(!function_exists("automotive_forms_footer")){
    function automotive_forms_footer(){
        global $lwp_options; ?>
        <div id="email_fancybox_form" class="" style="display: none">
            <?php if(!isset($lwp_options['email_friend_form_shortcode']) || empty($lwp_options['email_friend_form_shortcode'])){ ?>
                <h3><?php _e("Email to a Friend", "listings"); ?></h3>

                <form name="email_friend" method="post" class="ajax_form">
                    <table>
                        <tr><td><?php _e("Name", "listings"); ?>: </td> <td> <input type="text" name="name"></td></tr>
                        <tr><td><?php _e("Email", "listings"); ?>: </td> <td> <input type="text" name="email"></td></tr>
                        <tr><td><?php _e("Friends Email", "listings"); ?>: </td> <td> <input type="text" name="friends_email"></td></tr>
                        <tr><td colspan="2"><?php _e("Message", "listings"); ?>:<br>
                                <textarea name="message" class="fancybox_textarea"></textarea></td></tr>
                        <?php
                        if($lwp_options['recaptcha_enabled'] == 1 && isset($lwp_options['recaptcha_public_key']) && !empty($lwp_options['recaptcha_public_key'])){
                            echo "<tr><td colspan='2'>" . __("reCAPTCHA", "listings") . ": <br><div id='email_fancybox_form_recaptcha' class='recaptcha_holder'></div></td></tr>";
                        } ?>
                        <tr><td colspan="2"><input type="submit" value="<?php _e("Submit", "listings"); ?>"> <i class="fa fa-refresh fa-spin loading_icon_form"></td></tr>
                    </table>
                </form>
            <?php } else {
                echo do_shortcode($lwp_options['email_friend_form_shortcode']);
            } ?>
        </div>

        <div id="trade_fancybox_form" class="" style="display: none">
            <?php if(!isset($lwp_options['tradein_form_shortcode']) || empty($lwp_options['tradein_form_shortcode'])){ ?>
                <h3><?php _e("Trade-In", "listings"); ?></h3>

                <form name="trade_in" method="post" class="ajax_form">
                    <table class="left_table">
                        <tr>
                            <td colspan="2"><h4><?php _e("Contact Information", "listings"); ?></h4></td>
                        </tr>
                        <tr>
                            <td><?php _e("First Name", "listings"); ?><br><input type="text" name="first_name"></td>
                            <td><?php _e("Last Name", "listings"); ?><br><input type="text" name="last_name"></td>
                        </tr>
                        <tr>
                            <td><?php _e("Work Phone", "listings"); ?><br><input type="text" name="work_phone"></td>
                            <td><?php _e("Phone", "listings"); ?><br><input type="text" name="phone"></td>
                        </tr>
                        <tr>
                            <td><?php _e("Email", "listings"); ?><br><input type="text" name="email"></td>
                            <td><?php _e("Preferred Contact", "listings"); ?><br>  <input type="radio" name="contact_method" value="email" id="email"> <label for="email"><?php _e("Email", "listings"); ?></label>  <input type="radio" name="contact_method" value="phone" id="phone"> <label for="phone"><?php _e("Phone", "listings"); ?></label> </td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php _e("Comments", "listings"); ?><br><textarea name="comments" style="width: 89%;" rows="5"></textarea></td>
                        </tr>
                    </table>

                    <table class="right_table">
                        <tr>
                            <td colspan="2"><h4><?php _e("Options", "listings"); ?></h4></td>
                        </tr>

                        <?php

                        $options    = get_single_listing_category("options");
                        $options    = (isset($options['terms']) && !empty($options['terms']) ? $options['terms'] : array());
                        ?>
                        <tr>
                            <td><select name="options" multiple style="height: 200px;">
                                    <?php

                                    if(empty($options)){
                                        echo "<option value='" . __("Not availiable", "listings") . "'>N/A</option>";
                                    } else {

                                        array_multisort(array_map('strtolower', $options), $options);

                                        foreach($options as $option){
                                            echo "<option value='" . $option . "'>" . $option . "</option>";
                                        }
                                    }

                                    ?>
                                </select></td>

                        </tr>
                    </table>

                    <div style="clear:both;"></div>

                    <table class="left_table">
                        <tr><td colspan="2"><h4><?php _e("Vehicle Information", "listings"); ?></h4></td></tr>

                        <tr>
                            <td><?php _e("Year", "listings"); ?><br><input type="text" name="year"></td>
                            <td><?php _e("Make", "listings"); ?><br><input type="text" name="make"></td>
                        </tr>
                        <tr>
                            <td><?php _e("Model", "listings"); ?><br><input type="text" name="model"></td>
                            <td><?php _e("Exterior Colour", "listings"); ?><br><input type="text" name="exterior_colour"></td>
                        </tr>
                        <tr>
                            <td><?php _e("VIN", "listings"); ?><br><input type="text" name="vin"></td>
                            <td><?php _e("Kilometres", "listings"); ?><br><input type="text" name="kilometres"></td>
                        </tr>
                        <tr>
                            <td><?php _e("Engine", "listings"); ?><br><input type="text" name="engine"></td>
                            <td><?php _e("Doors", "listings"); ?><br><select name="doors" class="css-dropdowns"><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option></select></td>
                        </tr>
                        <tr>
                            <td><?php _e("Transmission", "listings"); ?><br><select name="transmission" class="css-dropdowns"><option value="Automatic"><?php _e("Automatic", "listings"); ?></option><option value="Manual"><?php _e("Manual", "listings"); ?></option></select></td>
                            <td><?php _e("Drivetrain", "listings"); ?><br><select name="drivetrain" class="css-dropdowns"><option value="2WD"><?php _e("2WD", "listings"); ?></option><option value="4WD"><?php _e("4WD", "listings"); ?></option><option value="AWD"><?php _e("AWD", "listings"); ?></option></select></td>
                        </tr>

                    </table>

                    <table class="right_table">
                        <tr><td colspan="2"><h4><?php _e("Vehicle Rating", "listings"); ?></h4></td></tr>

                        <tr>
                            <td><?php _e("Body (dents, dings, rust, rot, damage)", "listings"); ?><br><select name="body_rating" class="css-dropdowns"><option value="10">10 - <?php _e("best", "listings"); ?></option><option value="9">9</option><option value="8">8</option><option value="7">7</option><option value="6">6</option><option value="5">5</option><option value="4">4</option><option value="3">3</option><option value="2">2</option><option value="1">1 - <?php _e("worst", "listings"); ?></option></select></td>
                            <td><?php _e("Tires (tread wear, mismatched)", "listings"); ?><br><select name="tire_rating" class="css-dropdowns"><option value="10">10 - <?php _e("best", "listings"); ?></option><option value="9">9</option><option value="8">8</option><option value="7">7</option><option value="6">6</option><option value="5">5</option><option value="4">4</option><option value="3">3</option><option value="2">2</option><option value="1">1 - <?php _e("worst", "listings"); ?></option></select></td>
                        </tr>
                        <tr>
                            <td><?php _e("Engine (running condition, burns oil, knocking)", "listings"); ?><br><select name="engine_rating" class="css-dropdowns"><option value="10">10 - <?php _e("best", "listings"); ?></option><option value="9">9</option><option value="8">8</option><option value="7">7</option><option value="6">6</option><option value="5">5</option><option value="4">4</option><option value="3">3</option><option value="2">2</option><option value="1">1 - <?php _e("worst", "listings"); ?></option></select></td>
                            <td><?php _e("Transmission / Clutch (slipping, hard shift, grinds)", "listings"); ?><br><select name="transmission_rating" class="css-dropdowns"><option value="10">10 - <?php _e("best", "listings"); ?></option><option value="9">9</option><option value="8">8</option><option value="7">7</option><option value="6">6</option><option value="5">5</option><option value="4">4</option><option value="3">3</option><option value="2">2</option><option value="1">1 - <?php _e("worst", "listings"); ?></option></select></td>
                        </tr>
                        <tr>
                            <td><?php _e("Glass (chips, scratches, cracks, pitted)", "listings"); ?><br><select name="glass_rating" class="css-dropdowns"><option value="10">10 - <?php _e("best", "listings"); ?></option><option value="9">9</option><option value="8">8</option><option value="7">7</option><option value="6">6</option><option value="5">5</option><option value="4">4</option><option value="3">3</option><option value="2">2</option><option value="1">1 - worst</option></select></td>
                            <td><?php _e("Interior (rips, tears, burns, faded/worn, stains)", "listings"); ?><br><select name="interior_rating" class="css-dropdowns"><option value="10">10 - <?php _e("best", "listings"); ?></option><option value="9">9</option><option value="8">8</option><option value="7">7</option><option value="6">6</option><option value="5">5</option><option value="4">4</option><option value="3">3</option><option value="2">2</option><option value="1">1 - <?php _e("worst", "listings"); ?></option></select></td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php _e("Exhaust (rusted, leaking, noisy)", "listings"); ?><br><select name="exhaust_rating" class="css-dropdowns"><option value="10">10 - <?php _e("best", "listings"); ?></option><option value="9">9</option><option value="8">8</option><option value="7">7</option><option value="6">6</option><option value="5">5</option><option value="4">4</option><option value="3">3</option><option value="2">2</option><option value="1">1 - <?php _e("worst", "listings"); ?></option></select></td>
                        </tr>
                    </table>

                    <div style="clear:both;"></div>

                    <table class="left_table">
                        <tr><td><h4><?php _e("Vehicle History", "listings"); ?></h4></td></tr>

                        <tr>
                            <td><?php _e("Was it ever a lease or rental return?", "listings"); ?> <br><select name="rental_return" class="css-dropdowns"><option value="Yes"><?php _e("Yes", "listings"); ?></option><option value="No"><?php _e("No", "listings"); ?></option></select></td>
                        </tr>
                        <tr>
                            <td><?php _e("Is the odometer operational and accurate?", "listings"); ?> <br><select name="odometer_accurate" class="css-dropdowns"><option value="Yes"><?php _e("Yes", "listings"); ?></option><option value="No"><?php _e("No", "listings"); ?></option></select></td>
                        </tr>
                        <tr>
                            <td><?php _e("Detailed service records available?", "listings"); ?> <br><select name="service_records" class="css-dropdowns"><option value="Yes"><?php _e("Yes", "listings"); ?></option><option value="No"><?php _e("No", "listings"); ?></option></select></td>
                        </tr>
                    </table>

                    <table class="right_table">
                        <tr>
                            <td><h4><?php _e("Title History", "listings"); ?></h4></td>
                        </tr>

                        <tr>
                            <td><?php _e("Is there a lienholder?", "listings"); ?> <br><input type="text" name="lienholder"></td>
                        </tr>
                        <tr>
                            <td><?php _e("Who holds this title?", "listings"); ?> <br><input type="text" name="titleholder"></td>
                        </tr>
                    </table>

                    <div style="clear:both;"></div>

                    <table style="width: 100%;">
                        <tr><td colspan="2"><h4><?php _e("Vehicle Assessment", "listings"); ?></h4></td></tr>

                        <tr>
                            <td><?php _e("Does all equipment and accessories work correctly?", "listings"); ?><br><textarea name="equipment" rows="5" style="width: 89%;"></textarea></td>
                            <td><?php _e("Did you buy the vehicle new?", "listings"); ?><br><textarea name="vehiclenew" rows="5" style="width: 89%;"></textarea></td>
                        </tr>
                        <tr>
                            <td><?php _e("Has the vehicle ever been in any accidents? Cost of repairs?", "listings"); ?><br><textarea name="accidents" rows="5" style="width: 89%;"></textarea></td>
                            <td><?php _e("Is there existing damage on the vehicle? Where?", "listings"); ?><br><textarea name="damage" rows="5" style="width: 89%;"></textarea></td>
                        </tr>
                        <tr>
                            <td><?php _e("Has the vehicle ever had paint work performed?", "listings"); ?><br><textarea name="paint" rows="5" style="width: 89%;"></textarea></td>
                            <td><?php _e("Is the title designated 'Salvage' or 'Reconstructed'? Any other?", "listings"); ?><br><textarea name="salvage" rows="5" style="width: 89%;"></textarea></td>
                        </tr>
                        <?php

                        if($lwp_options['recaptcha_enabled'] == 1 && isset($lwp_options['recaptcha_public_key']) && !empty($lwp_options['recaptcha_public_key'])){
                            echo "<tr><td colspan='2'>" . __("reCAPTCHA", "listings") . ": <br><div id='trade_fancybox_form_recaptcha' class='recaptcha_holder'></div></td></tr>";
                        }

                        ?>
                        <tr><td colspan="2"><input type="submit" value="<?php _e("Submit", "listings"); ?>"> <i class="fa fa-refresh fa-spin loading_icon_form"></td></tr>
                    </table>

                </form>
            <?php } else {
                echo do_shortcode($lwp_options['tradein_form_shortcode']);
            } ?>
        </div>

        <div id="offer_fancybox_form" class="" style="display: none">
            <?php if(!isset($lwp_options['make_offer_form_shortcode']) || empty($lwp_options['make_offer_form_shortcode'])){ ?>
                <h3><?php _e("Make an Offer", "listings"); ?></h3>

                <form name="make_offer" method="post" class="ajax_form">
                    <table>
                        <tr><td><?php _e("Name", "listings"); ?>: </td> <td> <input type="text" name="name"></td></tr>
                        <tr><td><?php _e("Preferred Contact", "listings"); ?>:</td> <td> <input type="radio" name="contact_method" value="email" id="offer_email"><label for="offer_email"><?php _e("Email", "listings"); ?></label>  <input type="radio" name="contact_method" value="phone" id="offer_phone"> <label for="offer_phone"><?php _e("Phone", "listings"); ?></label></td></tr>
                        <tr><td><?php _e("Email", "listings"); ?>: </td> <td> <input type="text" name="email"></td></tr>
                        <tr><td><?php _e("Phone", "listings"); ?>: </td> <td> <input type="text" name="phone"></td></tr>
                        <tr><td><?php _e("Offered Price", "listings"); ?>: </td> <td> <input type="text" name="offered_price"></td></tr>
                        <tr><td><?php _e("Financing Required", "listings"); ?>: </td> <td> <select name="financing_required" class="css-dropdowns"><option value="yes"><?php _e("Yes", "listings"); ?></option><option value="no"><?php _e("No", "listings"); ?></option></select></td></tr>
                        <tr><td colspan="2"><?php _e("Other Comments/Conditions", "listings"); ?>:<br>
                                <textarea name="other_comments" class="fancybox_textarea"></textarea></td></tr>
                        <?php

                        if($lwp_options['recaptcha_enabled'] == 1 && isset($lwp_options['recaptcha_public_key']) && !empty($lwp_options['recaptcha_public_key'])){
                            echo "<tr><td colspan='2'>" . __("reCAPTCHA", "listings") . ": <br><div id='offer_fancybox_form_recaptcha' class='recaptcha_holder'></div></td></tr>";
                        }

                        ?>
                        <tr><td colspan="2"><input type="submit" value="<?php _e("Submit", "listings"); ?>"> <i class="fa fa-refresh fa-spin loading_icon_form"></td></tr>
                    </table>
                </form>
            <?php } else {
                echo do_shortcode($lwp_options['make_offer_form_shortcode']);
            } ?>
        </div>

        <div id="schedule_fancybox_form" class="" style="display: none">
            <?php if(!isset($lwp_options['schedule_test_drive_form_shortcode']) || empty($lwp_options['schedule_test_drive_form_shortcode'])){ ?>
                <h3><?php _e("Schedule Test Drive", "listings"); ?></h3>

                <form name="schedule" method="post" class="ajax_form">
                    <table>
                        <tr><td><?php _e("Name", "listings"); ?>: </td> <td> <input type="text" name="name"></td></tr>
                        <tr><td><?php _e("Preferred Contact", "listings"); ?>:</td> <td> <input type="radio" name="contact_method" value="email" id="schedule_email"><label for="schedule_email"><?php _e("Email", "listings"); ?></label>  <input type="radio" name="contact_method" value="phone" id="schedule_phone"> <label for="schedule_phone"><?php _e("Phone", "listings"); ?></label></td></tr>
                        <tr><td><?php _e("Email", "listings"); ?>: </td> <td> <input type="text" name="email"></td></tr>
                        <tr><td><?php _e("Phone", "listings"); ?>: </td> <td> <input type="text" name="phone"></td></tr>
                        <tr><td><?php _e("Best Day", "listings"); ?>: </td> <td> <input type="text" name="best_day"></td></tr>
                        <tr><td><?php _e("Best Time", "listings"); ?>: </td> <td> <input type="text" name="best_time"></td></tr>
                        <?php

                        if($lwp_options['recaptcha_enabled'] == 1 && isset($lwp_options['recaptcha_public_key']) && !empty($lwp_options['recaptcha_public_key'])){
                            echo "<tr><td colspan='2'>" . __("reCAPTCHA", "listings") . ": <br><div id='schedule_fancybox_form_recaptcha' class='recaptcha_holder'></div></td></tr>";
                        }

                        ?>
                        <tr><td colspan="2"><input type="submit" value="<?php _e("Submit", "listings"); ?>"> <i class="fa fa-refresh fa-spin loading_icon_form"></td></tr>
                    </table>
                </form>
            <?php } else {
                echo do_shortcode($lwp_options['schedule_test_drive_form_shortcode']);
            } ?>
        </div>

        <div id="request_fancybox_form" class="" style="display: none">
            <?php if(!isset($lwp_options['request_info_form_shortcode']) || empty($lwp_options['request_info_form_shortcode'])){ ?>
                <h3><?php _e("Request More Info", "listings"); ?></h3>

                <form name="request_info" method="post" class="ajax_form">
                    <table>
                        <tr><td><?php _e("Name", "listings"); ?>: </td> <td> <input type="text" name="name"></td></tr>
                        <tr><td><?php _e("Preferred Contact", "listings"); ?>:</td> <td> <input type="radio" name="contact_method" value="email" id="request_more_email"><label for="request_more_email"><?php _e("Email", "listings"); ?></label>  <input type="radio" name="contact_method" value="phone" id="request_more_phone"> <label for="request_more_phone"><?php _e("Phone", "listings"); ?></label></td></tr>
                        <tr><td><?php _e("Email", "listings"); ?>: </td> <td> <input type="text" name="email"></td></tr>
                        <tr><td><?php _e("Phone", "listings"); ?>: </td> <td> <input type="text" name="phone"></td></tr>
                        <?php

                        if($lwp_options['recaptcha_enabled'] == 1 && isset($lwp_options['recaptcha_public_key']) && !empty($lwp_options['recaptcha_public_key'])){
                            echo "<tr><td colspan='2'>" . __("reCAPTCHA", "listings") . ": <br><div id='request_fancybox_form_recaptcha' class='recaptcha_holder'></div></td></tr>";
                        }

                        ?>
                        <tr><td colspan="2"><input type="submit" value="<?php _e("Submit", "listings"); ?>"> <i class="fa fa-refresh fa-spin loading_icon_form"></i></td></tr>
                    </table>
                </form>
            <?php } else {
                echo do_shortcode($lwp_options['request_info_form_shortcode']);
            } ?>
        </div>
    <?php
    }
}
add_action("wp_footer", "automotive_forms_footer");

function listing_orderby(){
    $orderby = get_option("listing_orderby");

    if(isset($orderby) && !empty($orderby)){
        $category = get_single_listing_category($orderby[0]);

        $settings = array(
            "label" => $category['singular'],
            "key"   => $orderby[0],
            "type"  => $orderby[1]
        );

        return $settings;
    } else {
        return array("label" => __("Configure in listing categories", "listings"), "key" => "", "type" => "");
    }
}

// function show_sold_first($args){
// 	$args['meta_query'] = array(array('key' => 'car_sold'));
// 	$args['orderby'] = 'meta_value_num';
// 	$args['order'] = 'DESC';

// 	return $args;
// }
// add_filter( "listing_args", "show_sold_first" );

//********************************************
//	Generate Listing Args
//***********************************************************
if(!function_exists("listing_args")){
    function listing_args($get_or_post, $all = false, $ajax_array = false){
        global $lwp_options, $post;

        if(is_array($ajax_array)){
            $get_or_post = array_merge($get_or_post, $ajax_array);

            foreach($get_or_post as $key => $value){
                if(strstr($key, "_")){
                    $get_or_post[str_replace("_", "-", $key)] = $value;
                    unset($get_or_post[$key]);
                }

                if($key == "paged"){
                    $_REQUEST['paged'] = $value;
                }
            }
        }

        $paged      = (isset($_REQUEST['paged']) && !empty($_REQUEST['paged']) ? $_REQUEST['paged'] : (get_query_var("paged") ? get_query_var("paged") : 1));
        $lwp_options['listings_amount'] = (isset($lwp_options['listings_amount']) && !empty($lwp_options['listings_amount']) ? $lwp_options['listings_amount'] : "");
        $sort_items = array();

        // order by
        $default_orderby = (isset($lwp_options['sortby_default']) && $lwp_options['sortby_default'] == 0 ? "DESC" : "ASC");

        if(isset($get_or_post['order']) && !empty($get_or_post['order'])){
            $ordering = explode("|", $get_or_post['order']);
        }

        $args = array(
            'post_type' 	   		=> 'listings',
            'meta_query'	   		=> array(),
            'paged'      	   		=> (isset($paged) && !empty($paged) ? $paged : get_query_var('paged')),
            'posts_per_page' 		=> ($lwp_options['listings_amount']),
            'order'            	=> (isset($ordering[1]) && !empty($ordering[1]) && $ordering[1] != "undefined" ? $ordering[1] : $default_orderby),
            'suppress_filters' 	=> false
        );

        // keywords
        if(isset($_REQUEST['keywords']) && !empty($_REQUEST['keywords'])){
            $args['s'] = sanitize_text_field($_REQUEST['keywords']);
        }

        $data = array();

        if(isset($lwp_options['sortby']) && $lwp_options['sortby'] != 0){

            if(!empty($ordering[0]) && !empty($ordering[1])){

                $listing_orderby = get_option("listing_orderby");

                $args['meta_key'] = $ordering[0];
                $args['orderby']  = $listing_orderby[$ordering[0]];
            } else {
                $listing_orderby = get_option("listing_orderby");

                if(!empty($listing_orderby)){
                    $selected = reset($listing_orderby);
                    $selected = key($listing_orderby);

                    $args['meta_key'] = $selected;
                    $args['orderby']  = $listing_orderby[$selected];
                }
            }
        }

        $filterable_categories = get_filterable_listing_categories();

        foreach($filterable_categories as $filter){
            $singular = str_replace(" ", "_", mb_strtolower($filter['singular']));

            $get_singular = str_replace(" ", "-", mb_strtolower($filter['singular']));
            $get_singular = str_replace("-", "_", $get_singular);

            // year workaround, bad wordpress >:| ...
            if(mb_strtolower($filter['singular']) == "year" && isset($get_or_post["yr"]) && !empty($get_or_post["yr"])){
                $get_singular = "yr";
            } elseif(mb_strtolower($filter['singular']) == "year" && isset($get_or_post["year"]) && !empty($get_or_post["year"])){
                $get_singular = "year";
            }

            if(isset($get_or_post[$get_singular]) && !empty($get_or_post[$get_singular])){
                // min max values
                if(is_array($get_or_post[$get_singular]) && isset($get_or_post[$get_singular][0]) && !empty($get_or_post[$get_singular][0]) && isset($get_or_post[$get_singular][1]) && !empty($get_or_post[$get_singular][1])){
                    $min = $get_or_post[$get_singular][0];
                    $max = $get_or_post[$get_singular][1];

                    if(is_array($filter['terms']) && in_array($get_or_post[$get_singular][0], $filter['terms']) && in_array($get_or_post[$get_singular][1], $filter['terms'])){

                        $data[] = array(
                            'key'     => str_replace(" ", "_", mb_strtolower($filter['singular'])),
                            'value'   => array($min, $max),
                            'type'    => 'numeric',
                            'compare' => 'BETWEEN'
                        );
                    }
                } elseif(is_array($get_or_post[$get_singular]) && isset($get_or_post[$get_singular][0]) && !empty($get_or_post[$get_singular][0]) && empty($get_or_post[$get_singular][1])){
                    // if one value of min and max
                    $value        = str_replace("--", "-", $get_or_post[$get_singular][0]);
                    $value        = str_replace("-", " ", $get_or_post[$get_singular][0]);
                    $current_data = array("key" => str_replace(" ", "_", mb_strtolower($filter['singular'])), "value" => $value);

                    if(isset($filter['compare_value']) && $filter['compare_value'] != "="){
                        $current_data['compare'] = html_entity_decode($filter['compare_value']);
                        $current_data['type']    = "numeric";
                    }

                    $data[] = $current_data;

                } else {
                    $correct_case = get_category_correct_case($singular, $get_or_post[$get_singular]);
                    $value = (!empty($correct_case) ? $correct_case : $get_or_post[$get_singular]);

                    if(in_array($value, $filter['terms'])){

                        if(!isset($get_or_post['action']) && empty($get_or_post['action'])){
                            $value        = str_replace(array("--", "-"), array("|||", " "), $value);
                        }
                        $value 		  = str_replace("|||", "-", $value);
                        $current_data = array("key" => str_replace(" ", "_", mb_strtolower($filter['singular'])), "value" => urldecode($value));

                        if(isset($filter['compare_value']) && $filter['compare_value'] != "="){
                            $current_data['compare'] = html_entity_decode($filter['compare_value']);
                            $current_data['type']    = "numeric";
                        }

                        $data[] = $current_data;
                    }
                }
            }
        }

        // filter params
        if(isset($get_or_post['filter_params']) && !empty($get_or_post['filter_params'])){
            $filter_params = json_decode(stripslashes($get_or_post['filter_params']));

            // no page id for me
            unset($filter_params->page_id);

            foreach($filter_params as $index => $param){
                unset($param->length);

                $min = $param->{0};
                $max = $param->{1};

                $data[] = array(
                    'key'     => str_replace(" ", "_", mb_strtolower($index)),
                    'value'   => array($min, $max),
                    'type'    => 'numeric',
                    'compare' => 'BETWEEN'
                );
            }
        }

        // additional categories
        if(isset($lwp_options['additional_categories']['value']) && !empty($lwp_options['additional_categories']['value'])){
            foreach($lwp_options['additional_categories']['value'] as $additional_category){
                $check_handle = str_replace(" ", "_", mb_strtolower($additional_category));

                // in url
                if(isset($get_or_post[$check_handle]) && !empty($get_or_post[$check_handle])){
                    $data[] = array("key" => $check_handle, "value" => 1);
                }
            }
        }

        // hide sold vehicles
        /*if(isset($_GET['show_only_sold'])){
            $data[] = array("key"   => "car_sold",
                "value" => "1");
        } elseif(empty($lwp_options['inventory_no_sold']) && !isset($_GET['show_sold'])){
            $data[] = array("key"   => "car_sold",
                "value" => "2");
        }*/

        // order by
        if(isset($get_or_post['order_by']) && isset($get_or_post['order'])){
            $args['orderby'] = $get_or_post['order_by'];
            $args['order']   = $get_or_post['order'];
        }

        if(!empty($data)){
            $args['meta_query'] = $data;
        }

        // D($get_or_post);
        // D($args);

        $args = apply_filters( "listing_args", $args );

        return array($args);
    }
}

if(!function_exists("D")){
    function D($var){
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }
}

//********************************************
//	Get Fontawesome Icons
//***********************************************************
if(!function_exists("get_fontawesome_icons")){
    function get_fontawesome_icons(){
        $pattern = '/\.(fa-(?:\w+(?:-)?)+):before\s+{\s*content:\s*"(.+)";\s+}/';
        $subject = @file_get_contents(LISTING_DIR . 'css/font-awesome.css');

        if($subject){
            preg_match_all($pattern, $subject, $matches, PREG_SET_ORDER);

            $icons = array();

            foreach($matches as $match){
                $icons[$match[1]] = $match[2];
            }

            return $icons;
        } else {
            return "cant find file: " . LISTING_DIR . 'css/font-awesome.css';
        }
    }
}

//********************************************
//	Money Format
//***********************************************************
function format_currency($amount){
    global $lwp_options;

    //$amount = preg_replace("/[^0-9]/", "", $amount);
    $amount = preg_replace('/[^\d,\.]/', '', $amount);
//echo $amount.'<br/>';
    if(empty($amount)){
        return false;
    }

    $currency_symbol    = (isset($lwp_options['currency_symbol']) && !empty($lwp_options['currency_symbol']) ? $lwp_options['currency_symbol'] : "");
    $currency_separator = (isset($lwp_options['currency_separator']) && !empty($lwp_options['currency_separator']) ? $lwp_options['currency_separator'] : "");

    $return = (!empty($currency_separator) ? number_format($amount, 0, '.', $currency_separator) : $amount);

    $return = ($lwp_options['currency_placement'] ? $currency_symbol . $return : $return . $currency_symbol);

    return $return;
}

//********************************************
//	Pagination Boxes
//***********************************************************
if(!function_exists("page_of_box")){
    function page_of_box($load = false, $fake_get = null, $load_posts = false){
        global $lwp_options;

        $get_holder = (!is_null($fake_get) && !empty($fake_get) ? $fake_get : $_REQUEST);

        $return = "";

        if($load != false && !empty($load)){
            $paged = $load;
            $load_number = $load;

            if($load_posts == false){
                $listing_args = listing_args($_POST);
                $args		  = $listing_args[0];

                $args['posts_per_page'] = -1;
                $matches       = get_posts( $args );
            } else {
                $matches = $load_posts;
            }
            $load_number   = count($matches);
        } else {
            $paged_var 	  = (isset($get_holder['paged']) && !empty($get_holder['paged']) ? $get_holder['paged'] : "");
            $paged     	  = (isset($paged_var) && !empty($paged_var) ? $paged_var : (get_query_var("paged") ? get_query_var("paged") : 1));

            if($load_posts == false){
                $listing_args = listing_args($get_holder);
                $args		  = $listing_args[0];

                $args['posts_per_page'] = -1;
                $matches       = get_posts( $args );
            } else {
                $matches = $load_posts;
            }
            $load_number   = count($matches);
        }

        $number = $load_number;
        $total  = ceil($number / (isset($lwp_options['listings_amount']) && !empty($lwp_options['listings_amount']) ? $lwp_options['listings_amount'] : 1));

        //$return .= '<input type="hidden" class="page_of" data-page="' . ($paged ? $paged : 1) . '"/>';
        //$return .= '<input type="hidden" class="current_page" data-page="'.($paged ? $paged : 1).'" />';
        $return .= '<div class="controls full page_of" data-page="' . ($paged ? $paged : 1) . '">
        	<a href="#" class="left-arrow' . ($paged == 1 ? " disabled" : "") . '"><i class="fa fa-angle-left"></i></a>
            <span>' . __("Page", "listings") . ' <span class="current_page">' . ($paged ? $paged : 1) . '</span> ' . __('of', 'listings') . ' <span class="total_pages">' . ($total == 0 || empty($lwp_options['listings_amount']) ? 1 : $total) . '</span></span>
            <a href="#" class="right-arrow'. ($paged == $total || empty($lwp_options['listings_amount']) ? " disabled" : "") . '"><i class="fa fa-angle-right"></i></a>
        </div>';

        return $return;

        if(isset($_POST['action']) && !empty($_POST['action'])){
            die;
        }
    }
}

add_action('wp_ajax_load_page_of_box', 'page_of_box');
add_action('wp_ajax_nopriv_load_page_of_box', 'page_of_box');

if(!function_exists("bottom_page_box")){
    function bottom_page_box($layout = false, $load = false, $fake_get = null, $additional_vars = array(), $load_posts = false){
        global $lwp_options;

        $get_holder = (!is_null($fake_get) && !empty($fake_get) ? $fake_get : $_REQUEST);

        $return = "";

        if($load != false && !empty($load)){
            $paged = $load;
            $load_number = $load;

            $paged_var = get_query_var('paged');
            if(!isset($_REQUEST['action']) && $_REQUEST['action'] != "generate_new_view"){
                $paged   = (isset($paged_var) && !empty($paged_var) ? $paged_var : 1);
            }

            if($load_posts == false){
                $listing_args = listing_args($_POST);
                $args		  = $listing_args[0];

                $args['posts_per_page'] = -1;
                $matches       = get_posts( $args );
            } else {
                $matches = $load_posts;
            }
            $load_number   = count($matches);
        } else {
            $paged_var = (isset($get_holder['paged']) && !empty($get_holder['paged']) ? $get_holder['paged'] : "");
            $paged     = (isset($paged_var) && !empty($paged_var) ? $paged_var : (get_query_var("paged") ? get_query_var("paged") : 1));

            if($load_posts == false){
                $listing_args = listing_args($get_holder);
                $args		  = $listing_args[0];

                $args['posts_per_page'] = -1;
                $matches       = get_posts( $args );
            } else {
                $matches = $load_posts;
            }
            $load_number   = count($matches);

            // if any special layouts
            if($layout == "wide_left" || $layout == "boxed_left"){
                $additional_classes = "col-lg-offset-3";
                $cols = 9;
            } else {
                $cols = 12;
            }

            $return .= '<div class="pagination_container clearfix clear text-center ' . (isset($additional_classes) && !empty($additional_classes) ? " " . $additional_classes : "") . '">';
        }

        $number = $load_number;
        $total = ceil($number / (isset($lwp_options['listings_amount']) && !empty($lwp_options['listings_amount']) ? $lwp_options['listings_amount'] : 1));

        $return .= '<ul class="pagination margin-bottom-none bottom_pagination">';

        $return .= "<li data-page='previous' class='" . ($paged > 1 ? "" : "disabled") . " previous' style='margin-right:2px;'><a href='#'><i class='fa fa-angle-left'></i></a></li>";

        if($total == 0 || empty($lwp_options['listings_amount'])){
            $return .= "<li data-page='1' class='disabled number'><a href='#'>1</a></li>";
        } else {
            $each_side = 3;

            if($total > (($each_side * 2) + 1)){

                // additional options
                if(isset($additional_vars['number']) && !empty($additional_vars['number'])){
                    $number = $additional_vars['number'];
                }

                // before numbers
                if($paged > ($each_side)){
                    $before_start = ($paged - $each_side);
                    $before_pages = (($before_start + $each_side) - 1);
                    // echo "3 after";
                } else {
                    $before_start = 1;
                    $before_pages = (($paged - $each_side) + 2);
                    // echo "less than 3 after";
                }

                // after numbers
                if($total < ($each_side + $paged)){
                    $after_start = ($paged + 1);
                    $after_pages = $total;
                    // echo "less than 3 after";
                } else {
                    $after_start = ($paged + 1);
                    $after_pages = (($after_start + $each_side) - 1);
                    // echo "3 after";
                }

                for($i = $before_start; $i <= $before_pages; $i++){
                    $return .= "<li data-page='" . $i . "' class='number'><a href='#'>" . $i . "</a></li>";
                }

                $return .= "<li data-page='" . $paged . "' class='disabled number'><a href='#'>" . $paged . "</a></li>";

                for($i = $after_start; $i <= $after_pages; $i++){
                    $return .= "<li data-page='" . $i . "' class='number'><a href='#'>" . $i . "</a></li>";
                }
            } else {
                for($i = 1; $i <= $total; $i++){
                    $return .= "<li data-page='" . $i . "' class='" . ($paged != $i ? "" : "disabled") . " number'><a href='#'>" . $i . "</a></li>";
                }
            }
        }

        $return .= "<li data-page='next' class='" . ($paged < $total && !empty($lwp_options['listings_amount']) ? "" : "disabled") . " next'><a href='#'><i class='fa fa-angle-right'></i></a></li>";

        $return .= "</ul></div>";

        return $return;

        // wp_reset_postdata();
        // wp_reset_query();
    }
}



add_action('wp_ajax_load_bottom_page_box', 'bottom_page_box');
add_action('wp_ajax_nopriv_load_bottom_page_box', 'bottom_page_box');

if(!function_exists("get_total_meta")){
    function get_total_meta($meta_key, $meta_value, $is_options = false){
        global $wpdb;

        if(!$is_options){
            $sql = $wpdb->prepare("SELECT count(DISTINCT pm.post_id)
				FROM $wpdb->postmeta pm
				JOIN $wpdb->posts p ON (p.ID = pm.post_id)
				WHERE pm.meta_key = %s
				AND pm.meta_value = %s
				AND p.post_type = 'listings'
				AND p.post_status = 'publish'
			", $meta_key, $meta_value);
        } else {
            $sql = $wpdb->prepare("SELECT count(DISTINCT pm.post_id)
				FROM $wpdb->postmeta pm
				JOIN $wpdb->posts p ON (p.ID = pm.post_id)
				WHERE pm.meta_key = 'multi_options'
				AND pm.meta_value LIKE '%%%s%%'
				AND p.post_type = 'listings'
				AND p.post_status = 'publish'
			", $meta_value);
        }

        $count = $wpdb->get_var($sql);

        return $count;
    }
}

if(!function_exists("comp_thumb")){
    function comp_thumb($number, $dimension){
        switch($number){
            case 2:
                $return = ($dimension == "width" ? 562 : 292);
                break;

            case 3:
                $return = ($dimension == "width" ? 362 : 188);
                break;

            case 4:
                $return = ($dimension == "width" ? 262 : 136);
                break;
        }

        return $return;
    }
}

function remove_shortcode_extras($code){
    $return = preg_replace( '%<p>&nbsp;\s*</p>%', '', $code );
    $return = preg_replace( '%<p>\s*</p>%', '', $code );
    $old    = array( '<br />', '<br>' );
    $new    = array( '', '' );
    $return = str_replace( $old, $new, $return );

    return $return;
}

//********************************************
//	Plugin Modifications
//***********************************************************

if(!function_exists("ksort_deep")){
    function ksort_deep(&$array){
        ksort($array);
        foreach($array as $value)
            if(is_array($value))
                ksort_deep($value);
    }
}

//********************************************
//	Get All Post Meta
//***********************************************************
if( !function_exists("get_post_meta_all") ){
    function get_post_meta_all($post_id){
        global $wpdb;
        $data = array();
        $wpdb->query( "
			SELECT `meta_key`, `meta_value`
			FROM $wpdb->postmeta
			WHERE `post_id` = $post_id");

        foreach($wpdb->last_result as $k => $v){
            $data[$v->meta_key] =   $v->meta_value;
        };
        return $data;
    }
}

//********************************************
//	Listing Video
//***********************************************************
if( !function_exists("listing_video") ){
    function listing_video($url){
        if (strpos($url, 'youtube') > 0) {
            parse_str( parse_url( $url, PHP_URL_QUERY ), $query_string );

            $return['id']      = $query_string['v'];
            $return['service'] = 'youtube';
        } elseif (strpos($url, 'vimeo') > 0) {
            $return['id']      = (int)substr(parse_url($url, PHP_URL_PATH), 1);
            $return['service'] = 'vimeo';
        } else {
            $return['service'] = 'unknown';
        }

        return $return;
    }
}


//********************************************
//	Shortcode / Widget Functions
//***********************************************************
if(!function_exists("testimonial_slider")){
    function testimonial_slider($slide, $speed, $pager, $content, $widget = false){
        // remove br
        $content = str_replace("<br />", "", $content);

        $return  = "<!--Testimonials Start-->";
        $return .= "<div class='testimonial'>";
        $return .= "<ul class=\"testimonial_slider\">";
        if($widget === false){
            $return .= do_shortcode($content);
        } else {

            foreach($widget as $fields){
                $return .= testimonial_slider_quote($fields['name'], $fields['content']);
            }
        }
        $return .= "</ul>";
        $return .= "</div>";
        $return .= "<!--Testimonials End-->";

        $return = remove_shortcode_extras($return);

        return $return;
    }
}

if(!function_exists("testimonial_slider_quote")){
    function testimonial_slider_quote($name, $content){
        $return  = "<li><blockquote class='style1'><span>" . $content;
        $return .= "</span><strong>" . $name . "</strong> ";
        $return .= "</blockquote></li>";

        return $return;
    }
}

if(!function_exists("random_string")){
    function random_string($length = 10) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
}

if(!function_exists("vehicle_scroller")){
    function vehicle_scroller($title = "Recent Vehicles", $description = "Browse through the vast selection of vehicles that have been added to our inventory", $limit = -1, $sort = null, $listings = null, $other_options = array()){
        global $lwp_options;

        $args = array("post_type"  => "listings");
        switch($sort){
            case "newest":
                $args  = array("post_type"      => "listings",
                    "posts_per_page" => $limit,
                    "orderby"        => "date",
                    "order"          => "DESC"
                );
                break;

            case "oldest":
                $args  = array("post_type"      => "listings",
                    "posts_per_page" => $limit,
                    "orderby"        => "date",
                    "order"          => "ASC"
                );
                break;

            case "related":
                //
                $args  = array("post_type"      => "listings",
                    "posts_per_page" => $limit,
                    "order"          => "DESC"
                );
                break;

            default:
                $args = array("post_type"		=> "listings",
                    "posts_per_page" 	=> $limit);
                break;
        }

        $data = array();

        // related
        if($sort == "related" && isset($lwp_options['related_category']) && !empty($lwp_options['related_category'])){
            $data[] = array(
                array(
                    "key" 	=> $lwp_options['related_category'],
                    "value" => $other_options['related_val'],
                )
            );

            unset($other_options['related_val']);
        }

        if(empty($lwp_options['inventory_no_sold'])){
            $data[] = array("key"     => "car_sold",
                "value" => "2");
        }

        if(!empty($data)){
            $args['meta_query'] = $data;
        }

        if(isset($listings) && !empty($listings)){
            $listing_ids = explode(",", $listings);
            $args['post__in'] = $listing_ids;
        }

        $query = new WP_Query( $args );

        ob_start(); ?>

        <div class="recent-vehicles-wrap">
            <div class="row">
                <div class="col-lg-2 col-md-2 col-sm-4 col-xs-12 recent-vehicles padding-left-none xs-padding-bottom-20">
                    <h5 class="margin-top-none"><?php echo $title; ?></h5>
                    <p><?php echo $description; ?></p>

                    <div class="arrow3 clearfix" id="slideControls3"><span class="prev-btn"></span><span class="next-btn"></span></div>
                </div>
                <div class="col-md-10 col-sm-8 padding-right-none sm-padding-left-none xs-padding-left-none">
                    <?php
                    $additional_attr = "";
                    if(!empty($other_options)){
                        foreach($other_options as $key => $option){
                            $additional_attr .= "data-" . $key . "='" . $option . "' ";
                        }
                    }

                    ?>
                    <div class="carasouel-slider3" <?php echo (!empty($additional_attr) ? $additional_attr : ""); ?>>
                        <?php
                        while ( $query->have_posts() ) : $query->the_post();
                            $post_meta       = get_post_meta_all(get_the_ID());
                            if(isset($post_meta['listing_options']) && !empty($post_meta['listing_options'])){
                                $listing_options = unserialize(unserialize($post_meta['listing_options']));
                            }

                            if(isset($post_meta['gallery_images']) && !empty($post_meta['gallery_images']) && !empty($post_meta['gallery_images'][0])){
                                $gallery_images  = unserialize($post_meta['gallery_images']);

                                $thumbnail 		 = auto_image($gallery_images[0], "auto_thumb", true);
                            } else {
                                $thumbnail 		 = LISTING_DIR . "images/pixel.gif";
                            }

                            echo "<div class=\"slide\">";
                            echo "<div class=\"car-block\">";
                            echo "<div class=\"img-flex\">";
                            if(isset($post_meta['car_sold']) && $post_meta['car_sold'] == 1){
                                echo '<span class="sold_text">Sold</span>';
                            }
                            echo "<a href=\"" . get_permalink(get_the_ID()) . "\"><span class=\"align-center\"><i class=\"fa fa-3x fa-plus-square-o\"></i></span></a> <img src=\"" . $thumbnail . "\" alt=\"\" class=\"img-responsive no_border\"> </div>";
                            echo "<div class=\"car-block-bottom\">";
                            echo "<h6><strong>" . get_the_title() . "</strong></h6>";
                            echo "<h6>" . (isset($listing_options['short_desc']) && !empty($listing_options['short_desc']) ? $listing_options['short_desc'] : "") . "</h6>";
                            echo (isset($listing_options['price']['value']) && !empty($listing_options['price']['value']) ? "<h5>" . format_currency($listing_options['price']['value']) . "</h5>" : "");
                            echo "</div>";
                            echo "</div>";
                            echo "</div>";
                        endwhile; ?>
                    </div>
                </div>

                <div class="clear"></div>
            </div>
        </div>
        <?php

        return ob_get_clean();

    }
}

//********************************************
//	Filter Listings
//***********************************************************
function filter_listing_results($var) {
    global $lwp_options, $filterable;

    $listing_args = listing_args($_POST);
    $args		  = $args2 = $listing_args[0];

    //D($listing_args);

    // meta query with dashes
    if(!empty($args['meta_query'])){
        foreach($args['meta_query'] as $key => $meta){
            $args['meta_query'][$key]['value'] = str_replace("%2D", "-", (isset($meta['value']) && !empty($meta['value']) ? $meta['value'] : ""));
        }
    }

    $posts = get_posts($args);

    //D($posts);

    $return = '';
    foreach($posts as $post){
        $return .= (isset($_POST['layout']) && !empty($_POST['layout']) ? inventory_listing($post->ID, $_POST['layout']) : inventory_listing($post->ID));
    }

    $args['posts_per_page'] = -1;

    $total_posts   = get_posts($args);
    $total_matches = count($total_posts);
    $return = ($total_matches == 0 ? do_shortcode('[alert type="2" close="No"]' . __("No match found. Try another combination.", "listings") . '[/alert]') . "<div class='clearfix'></div>" : $return);

    // do_shortcode('[alert type="0" close="No"]' . __("No listings found", "listings") . '[/alert]') . "<div class='clearfix'></div>";

    $paged = (get_query_var('paged') ? get_query_var('paged') : false);


    // generate filter parameters

    if(isset($_POST['filter_params']) && !empty($_POST['filter_params'])){

        $filter     = array();
        $categories = json_decode(stripslashes($_POST['filter_params']), true);

        //{"body-style":"sports-utility-vehicle","model":"cayenne"}
        foreach($categories as $category => $value){
            $singular = get_single_listing_category(str_replace("-", "_", $category));

            if(is_array($value)){
                $filter[$category] = array("value"    => $value,
                    "singular" => $singular['singular']
                );
            } else {
                $filter[$category] = array("value"    => get_category_correct_case($category, $value),
                    "singular" => $singular['singular']
                );
            }
        }

    }

    $return_array = array( "content"     => $return,
        "number"      => $total_matches,
        "top_page"    => page_of_box($paged, false, $total_posts),
        "bottom_page" => bottom_page_box(false, $paged, null, array("number" => $total_matches), $total_posts),
        "args"		 => $args2
    );

    // filter
    if(isset($filter) && !empty($filter)){
        $return_array['filter'] = $filter;
    }

    if($var === true){
        return json_encode( $return_array );
    } else {
        echo json_encode( $return_array );
    }

    die();
}

add_action("wp_ajax_filter_listing", "filter_listing_results");
add_action("wp_ajax_nopriv_filter_listing", "filter_listing_results");

//********************************************
//	Get single permalink
//***********************************************************
add_action('wp_ajax_get_single_permalink', 'get_single_permalink');
add_action('wp_ajax_nopriv_get_single_permalink', 'get_single_permalink');

function get_single_permalink(){
    echo get_permalink(intval($_REQUEST['id']));
    die();
}

if(!function_exists("car_comparison")){
    function car_comparison($car, $class){
        ob_start();

        $all_post_meta   = get_post_meta_all($car);
        $listing_options = unserialize(unserialize($all_post_meta['listing_options']));
        $gallery_images  = unserialize($all_post_meta['gallery_images']); ?>
        <div class='col-lg-<?php echo $class; ?>'>
            <div class="porche margin-bottom-25">
                <div class="porche-header"> <span><?php echo get_the_title($car); ?></span> <strong><?php echo format_currency($listing_options['price']['value']); ?></strong> </div>
                <?php
                if(!empty($gallery_images)){
                    $img = auto_image($gallery_images[0], "auto_slider", true);
                } elseif(empty($gallery_images[0]) && isset($lwp_options['not_found_image']['url']) && !empty($lwp_options['not_found_image']['url'])){
                    $img = $lwp_options['not_found_image']['url'];
                } else {
                    $img = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7";
                }
                ?>
                <div class="porche-img"> <img src="<?php echo $img; ?>" alt="" class="no_border"></div>
                <div class="car-detail clearfix">
                    <div class="table-responsive">
                        <table class="table comparison">
                            <tbody>
                            <?php
                            $listing_categories = get_listing_categories();

                            foreach($listing_categories as $category){
                                $safe_handle = str_replace(" ", "_", strtolower($category['singular']));
                                $value       = (isset($all_post_meta[$safe_handle]) && !empty($all_post_meta[$safe_handle]) ? $all_post_meta[$safe_handle] : "");

                                if(isset($category['currency']) && $category['currency'] == 1){
                                    $value = format_currency($value);
                                }

                                echo "<tr><td>" . wpml_category_translate($category['singular'], "singular", $category['singular']) . ": </td><td>" . $value . "</td></tr>";
                            } ?>
                            <tr>
                                <td><?php _e("OPTIONS", "listings"); ?>:</td>
                                <td></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="option-tick-list clearfix">
                        <div class="row">
                            <div class="col-lg-12">
                                <?php
                                $multi_options = unserialize((isset($all_post_meta['multi_options']) && !empty($all_post_meta['multi_options']) ? $all_post_meta['multi_options'] : ""));

                                if(isset($multi_options) && !empty($multi_options)){

                                    switch($class){
                                        case 6:
                                            $columns = 3;
                                            $column_class = 4;
                                            break;

                                        case 4:
                                            $columns = 2;
                                            $column_class = 6;
                                            break;

                                        case 3:
                                            $columns = 1;
                                            $column_class = 12;
                                            break;
                                    }

                                    $amount = ceil(count($multi_options) / $columns);
                                    $new    = array_chunk($multi_options, $amount);

                                    echo "<div class='row'>";
                                    foreach($new as $section){
                                        echo "<ul class='options col-lg-" . $column_class . "'>";
                                        foreach($section as $option){
                                            echo "<li>" . $option . "</li>";
                                        }
                                        echo "</ul>";
                                    }
                                    echo "</div>";
                                } else {
                                    echo "<ul class='empty'><li>" . __("No options yet", "listings") . "</li></ul>";
                                } ?>
                            </div>
                        </div>
                    </div>
                    <div class="porche-footer margin-top-25 padding-top-20 padding-bottom-15">
                        <form method="post" action="<?php echo get_permalink($car); ?>">
                            <input type="submit" value="<?php _e("View Listing", "listings"); ?>">
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php

        return ob_get_clean();
    }
}

function is_edit_page($new_edit = null){
    global $pagenow;
    //make sure we are on the backend
    if (!is_admin()) return false;


    if($new_edit == "edit")
        return in_array( $pagenow, array( 'post.php',  ) );
    elseif($new_edit == "new") //check for new post page
        return in_array( $pagenow, array( 'post-new.php' ) );
    else //check for either new or edit
        return in_array( $pagenow, array( 'post.php', 'post-new.php' ) );
}

//********************************************
//	Show Social Icons
//***********************************************************
if( !function_exists("show_social_icons") ) {
    function show_social_icons() {
        if ( has_post_thumbnail() ) {
            $image = wp_get_attachment_image_src(get_the_post_thumbnail());
        } elseif(is_singular('listings')) {
            $saved_images   = get_post_meta(get_queried_object_id(), "gallery_images");
            $gallery_images = unserialize($saved_images[0]);

            $image = (isset($gallery_images[0]) && !empty($gallery_images[0]) ? $gallery_images[0] : "");
        } else {
            $image = '';
        }
        ?>
        <ul class="social-likes" data-url="<?php echo get_permalink(); ?>" data-title="<?php echo get_the_title(); ?>">
            <li class="facebook" title="<?php _e("Share link on Facebook", "listings"); ?>"></li>
            <li class="plusone" title="<?php _e("Share link on Google+", "listings"); ?>"></li>
            <li class="pinterest" title="<?php _e("Share image on Pinterest", "listings"); ?>" data-media="<?php echo $image; ?>"></li>
            <li class="twitter" title="<?php _e("Share link on Twitter", "listings"); ?>"></li>
        </ul>
    <?php
    }
}


if(!function_exists("is_ajax")){
    function is_ajax(){
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return true;
        } else {
            return false;
        }
    }
}

function column_maker(){ ?>
	<div id='full_column' class='column_display_container' data-number='0'>
        <div class='empty one'></div>
        <div class='empty one'></div>
        <div class='empty one'></div>
        <div class='empty one'></div>
        <div class='empty one'></div>
        <div class='empty one'></div>
        <div class='empty one'></div>
        <div class='empty one'></div>
        <div class='empty one'></div>
        <div class='empty one'></div>
        <div class='empty one'></div>
        <div class='empty one'></div>
    </div>

    <br />

    <div class='generate_columns button'><?php _e("Generate Columns", "listings"); ?></div>

    <?php
    $i     = 1;
    $width = 31;

    while($i <= 12){
        echo "<div class='column_display_container insert' data-number='" . $i . "'><span class='label'>" . $i . ($i != 1 ? " / 12" : "") . "</span> <div class='full twelve' style='width: " . ($i * $width) . "px;'></div></div><br />";
        $i++;
    }

    die;
}
add_action('wp_ajax_column_maker', 'column_maker');
add_action('wp_ajax_nopriv_column_maker', 'column_maker');

if(!function_exists("is_edit_page")){
    function is_edit_page($new_edit = null){
        global $pagenow;

        if (!is_admin()) return false;


        if($new_edit == "edit"){
            return in_array( $pagenow, array( 'post.php',  ) );
        } elseif($new_edit == "new") {
            return in_array( $pagenow, array( 'post-new.php' ) );
        } else {
            return in_array( $pagenow, array( 'post.php', 'post-new.php' ) );
        }
    }
}


if(!function_exists("remove_editor")){
    function remove_editor() {
        remove_post_type_support('listings', 'editor');
    }
}
add_action('admin_init', 'remove_editor');


if(!function_exists("youtube_video_id")){
    function youtube_video_id($url) {
        if(is_youtube($url)) {
            $pattern = '/^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/';
            preg_match($pattern, $url, $matches);
            if (count($matches) && strlen($matches[7]) == 11) {
                return $matches[7];
            }
        }

        return '';
    }
}

if(!function_exists("is_youtube")){
    function is_youtube($url) {
        return (preg_match('/youtu\.be/i', $url) || preg_match('/youtube\.com\/watch/i', $url));
    }
}


if(!function_exists("get_all_media_images")){
    function get_all_media_images(){
        $query_images_args = array(
            'post_type' => 'attachment', 'post_mime_type' =>'image', 'post_status' => 'inherit', 'posts_per_page' => -1,
        );

        $query_images = new WP_Query( $query_images_args );
        $images = array();

        foreach ( $query_images->posts as $image) {
            $images[]= wp_get_attachment_url( $image->ID );
        }

        return $images;
    }
}

//********************************************
//	Single Listing Template
//***********************************************************
add_filter( 'template_include', 'my_plugin_templates' );
function my_plugin_templates( $template ) {
    $post_types = array(  );

    if ( is_singular( 'listings' ) && ! file_exists( get_stylesheet_directory() . '/single-listings.php' ) ){
        $template = LISTING_HOME . 'single-listings.php';
    } elseif( is_singular( 'listings_portfolio' ) && ! file_exists( get_stylesheet_directory() . '/single-portfolio.php' ) ){
        $template = LISTING_HOME . 'single-portfolio.php';
    }

    return $template;
}

/* Form */
if(!function_exists("listing_form")){
    function listing_form(){
        global $lwp_options;

        $_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $form   = $_POST['form'];
        $errors = array();

        // email headers
        $headers  = "From: " . $_POST['email'] . "\r\n";
        $headers .= "Reply-To: ". $_POST['email'] . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        $subject  = ucwords(str_replace("_", " ", $_POST['form']));

        if($form == "email_friend"){

            // validate email
            if(!filter_var($_POST['friends_email'], FILTER_VALIDATE_EMAIL) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
                $errors[] = "Not a valid email";
            } else {
                $post_meta = get_post_meta_all($_POST['id']);

                $listing_options = (isset($post_meta['listing_options']) && !empty($post_meta['listing_options']) ? unserialize(unserialize($post_meta['listing_options'])) : array());
                $gallery_images  = (isset($post_meta['gallery_images']) && !empty($post_meta['gallery_images']) ? unserialize($post_meta['gallery_images']) : array());

                $name    = (isset($_POST['name']) && !empty($_POST['name']) ? sanitize_text_field($_POST['name']) : "");
                $from    = (isset($_POST['email']) && !empty($_POST['email']) ? sanitize_text_field($_POST['email']) : "");
                $friend  = (isset($_POST['friends_email']) && !empty($_POST['friends_email']) ? sanitize_text_field($_POST['friends_email']) : "");
                $message = (isset($_POST['message']) && !empty($_POST['message']) ? sanitize_text_field($_POST['message']) : "");

                $thumbnail  = auto_image($gallery_images[0], "auto_thumb", true);//$gallery_images[0]['thumb']['url'];

                $categories = get_listing_categories();

                $table   = "<table width='100%' border='0' cellspacing='0' cellpadding='2'><tbody>";

                $table  .= "<tr>
					<td><img src='" . $thumbnail . "'></td>
					<td style='font-weight:bold;color:#000;'>" . get_the_title($_POST['id']) . "</td>
					<td></td>
					<td>" . $listing_options['price']['text'] . ": " . format_currency($listing_options['price']['value']) . "</td>
				</tr>";

                foreach($categories as $category){
                    $safe_handle = str_replace(" ", "_", strtolower($category['singular']));
                    $table .= "<tr><td>" . $category['singular'] . ": </td><td> " . (isset($post_meta[$safe_handle]) && !empty($post_meta[$safe_handle]) ? $post_meta[$safe_handle] : __("N/A", "listings")) . "</td></tr>";
                }

                $table  .= "<tr>
								<td>&nbsp;</td>
								<td align='center' style='background-color:#000;font-weight:bold'><a href='" . get_permalink($_POST['id']) . "' style='color:#fff;text-decoration:none' target='_blank'>" . __('Click for more details', 'listings') . "</a></td>
							</tr>";

                $table  .= "</tbody></table>";

                $search  = array('{table}', '{message}', '{name}');
                $replace = array($table, $message, $name);

                $subject      = str_replace("{name}", $name, $lwp_options['friend_subject']);
                $send_message = str_replace($search, $replace, $lwp_options['friend_layout']);

                $mail         = @wp_mail($friend, $subject, $send_message, $headers);
            }
        } else {

            // validate email
            if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
                $errors[] = __("Not a valid email", "listings");
            } else {

                switch ($form) {
                    case 'request_info':
                        $to      = ($lwp_options['info_to'] ? $lwp_options['info_to'] : get_bloginfo('admin_email'));
                        $subject = $lwp_options['info_subject'];

                        $name           = (isset($_POST['name']) && !empty($_POST['name']) ? sanitize_text_field($_POST['name']) : "");
                        $contact_method = (isset($_POST['contact_method']) && !empty($_POST['contact_method']) ? sanitize_text_field($_POST['contact_method']) : "");
                        $email          = (isset($_POST['email']) && !empty($_POST['email']) ? sanitize_text_field($_POST['email']) : "");
                        $phone          = (isset($_POST['phone']) && !empty($_POST['phone']) ? sanitize_text_field($_POST['phone']) : "");

                        $table   = "<table border='0'>";
                        $table  .= "<tr><td>" . __("First Name", "listings") . ": </td><td> " . $name . "</td></tr>";
                        $table  .= "<tr><td>" . __("Contact Method", "listings") . ": </td><td> " . $contact_method . "</td></tr>";
                        $table  .= "<tr><td>" . __("Phone", "listings") . ": </td><td> " . $phone . "</td></tr>";
                        $table  .= "<tr><td>" . __("Email", "listings") . ": </td><td> " . $email . "</td></tr>";
                        $table  .= "</table>";

                        $link    = get_permalink($_POST['id']);

                        $search  = array("{name}", "{contact_method}", "{email}", "{phone}", "{table}", "{link}");
                        $replace = array($name, $contact_method, $email, $phone, $table, $link);

                        $message = str_replace($search, $replace, $lwp_options['info_layout']);
                        break;

                    case 'schedule':
                        $to      = ($lwp_options['drive_to'] ? $lwp_options['drive_to'] : get_bloginfo('admin_email'));
                        $subject = $lwp_options['drive_subject'];

                        $name           = (isset($_POST['name']) && !empty($_POST['name']) ? sanitize_text_field($_POST['name']) : "");
                        $contact_method = (isset($_POST['contact_method']) && !empty($_POST['contact_method']) ? sanitize_text_field($_POST['contact_method']) : "");
                        $email          = (isset($_POST['email']) && !empty($_POST['email']) ? sanitize_text_field($_POST['email']) : "");
                        $phone          = (isset($_POST['phone']) && !empty($_POST['phone']) ? sanitize_text_field($_POST['phone']) : "");
                        $best_day       = (isset($_POST['best_day']) && !empty($_POST['best_day']) ? sanitize_text_field($_POST['best_day']) : "");
                        $best_time      = (isset($_POST['best_time']) && !empty($_POST['best_time']) ? sanitize_text_field($_POST['best_time']) : "");

                        $table   = "<table border='0'>";
                        $table  .= "<tr><td>" . __("Name", "listings") . ": </td><td> " . $name . "</td></tr>";
                        $table  .= "<tr><td>" . __("Contact Method", "listings") . ": </td><td> " . $contact_method . "</td></tr>";
                        $table  .= "<tr><td>" . __("Phone", "listings") . ": </td><td> " . $phone . "</td></tr>";
                        $table  .= "<tr><td>" . __("Email", "listings") . ": </td><td> " . $email . "</td></tr>";
                        $table  .= "<tr><td>" . __("Best Date", "listings") . ": </td><td> " . $best_day . " " . $best_time . "</td></tr>";
                        $table  .= "</table>";

                        $link    = get_permalink($_POST['id']);

                        $search  = array("{name}", "{contact_method}", "{email}", "{phone}", "{best_day}", "{best_time}", "{table}", "{link}");
                        $replace = array($name, $contact_method, $email, $phone, $best_day, $best_time, $table, $link);

                        $message = str_replace($search, $replace, $lwp_options['drive_layout']);
                        break;

                    case 'make_offer':
                        $to      = ($lwp_options['offer_to'] ? $lwp_options['offer_to'] : get_bloginfo('admin_email'));
                        $subject = $lwp_options['offer_subject'];


                        $name 				= (isset($_POST['name']) && !empty($_POST['name']) ? sanitize_text_field($_POST['name']) : "");
                        $contact_method 	= (isset($_POST['contact_method']) && !empty($_POST['contact_method']) ? sanitize_text_field($_POST['contact_method']) : "");
                        $email 				= (isset($_POST['email']) && !empty($_POST['email']) ? sanitize_text_field($_POST['email']) : "");
                        $phone 				= (isset($_POST['phone']) && !empty($_POST['phone']) ? sanitize_text_field($_POST['phone']) : "");
                        $offered_price 		= (isset($_POST['offered_price']) && !empty($_POST['offered_price']) ? sanitize_text_field($_POST['offered_price']) : "");
                        $financing_required = (isset($_POST['financing_required']) && !empty($_POST['financing_required']) ? sanitize_text_field($_POST['financing_required']) : "");
                        $other_comments 	= (isset($_POST['other_comments']) && !empty($_POST['other_comments']) ? sanitize_text_field($_POST['other_comments']) : "");


                        $table   = "<table border='0'>";
                        $table  .= "<tr><td>" . __("Name", "listings") . ": </td><td> " . $name . "</td></tr>";
                        $table  .= "<tr><td>" . __("Contact Method", "listings") . ": </td><td> " . $contact_method . "</td></tr>";
                        $table  .= "<tr><td>" . __("Phone", "listings") . ": </td><td> " . $phone . "</td></tr>";
                        $table  .= "<tr><td>" . __("Email", "listings") . ": </td><td> " . $email . "</td></tr>";
                        $table  .= "<tr><td>" . __("Offered Price", "listings") . ": </td><td> " . $offered_price . "</td></tr>";
                        $table  .= "<tr><td>" . __("Financing Required", "listings") . ": </td><td> " . $financing_required . "</td></tr>";
                        $table  .= "<tr><td>" . __("Other Comments", "listings") . ": </td><td> " . $other_comments . "</td></tr>";
                        $table  .= "</table>";

                        $link   = get_permalink($_POST['id']);

                        $search  = array("{name}", "{contact_method}", "{email}", "{phone}", "{offered_price}", "{financing_required}", "{other_comments}", "{table}", "{link}");
                        $replace = array($name, $contact_method, $email, $phone, $offered_price, $financing_required, $other_comments, $table, $link);

                        $message = str_replace($search, $replace, $lwp_options['offer_layout']);
                        break;

                    case 'trade_in':
                        $to      = ($lwp_options['trade_to'] ? $lwp_options['trade_to'] : get_bloginfo('admin_email'));
                        $subject = $lwp_options['trade_subject'];

                        $form_items = array("first_name", "last_name", "work_phone", "phone", "email", "contact_method", "comments", "options", "year", "make", "model", "exterior_colour", "vin", "kilometres", "engine", "doors", "transmission", "drivetrain", "body_rating", "tire_rating", "engine_rating", "transmission_rating", "glass_rating", "interior_rating", "exhaust_rating", "rental_rating", "odometer_accurate", "service_records", "lienholder", "titleholder", "equipment", "vehiclenew", "accidents", "damage", "paint", "salvage");

                        $table  = "<table border='0'>";
                        foreach($form_items as $key => $single){
                            $table .= "<tr><td>" . ucwords(str_replace("_", " ", $single)) . ": </td><td> " . (isset($_POST[$single]) && !empty($_POST[$single]) ? $_POST[$single] : "") . "</td></tr>";
                        }
                        $table .= "</table>";

                        $link   = get_permalink($_POST['id']);

                        $search   = array("{table}", "{link}");
                        $replace  = array($table, $link);

                        $message  = str_replace($search, $replace, $lwp_options['trade_layout']);
                        break;
                }

                // if location email
                $location_email    = get_option("location_email");
                $location_category = get_location_email_category();

                // var_dump($location_email);

                // var_dump($location_category);

                if(isset($location_email) && !empty($location_email) && isset($location_category) && !empty($location_category)){
                    $to = (isset($location_email[get_post_meta( (int)$_POST['id'], str_replace(" ", "_", strtolower($location_category)), true )]) && !empty($location_email[get_post_meta( (int)$_POST['id'], str_replace(" ", "_", strtolower($location_category)), true )]) ? $location_email[get_post_meta( (int)$_POST['id'], str_replace(" ", "_", strtolower($location_category)), true )] : $to);
                }


                $mail = @wp_mail($to, $subject, $message, $headers);
            }
        }

        if($mail && empty($errors)){
            echo __("Sent Successfully", "listings");
        } else {
            echo "<ul class='error_list'>";
            foreach($errors as $error){
                echo "<li>" . $error . "</li>";
            }
            echo "</ul>";
        }

        die;
    }
}
add_action("wp_ajax_listing_form", "listing_form");
add_action("wp_ajax_nopriv_listing_form", "listing_form");

function get_first_post_image($post) {
    //global $post, $posts;
    $first_img = false;
    ob_start();
    ob_end_clean();
    $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
    $first_img = (isset($matches[1][0]) && !empty($matches[1][0]) ? $matches[1][0] : "");

    return $first_img;
}



function url_origin($s, $use_forwarded_host=false){
    $ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true:false;
    $sp = strtolower($s['SERVER_PROTOCOL']);
    $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
    $port = $s['SERVER_PORT'];
    $port = ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
    $host = ($use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST'])) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
    $host = isset($host) ? $host : $s['SERVER_NAME'] . $port;
    return $protocol . '://' . $host;
}

function full_url($s, $use_forwarded_host=false){
    return url_origin($s, $use_forwarded_host) . $s['REQUEST_URI'];
}

function recaptcha_check(){
    global $lwp_options;

    require_once(LISTING_HOME . 'recaptchalib.php');

    $resp = recaptcha_check_answer($lwp_options['recaptcha_private_key'],
        $_SERVER["REMOTE_ADDR"],
        $_POST["recaptcha_challenge_field"],
        $_POST["recaptcha_response_field"]);

    if (!$resp->is_valid) {
        echo __("The reCAPTCHA wasn't entered correctly. Go back and try it again.", "listings");// ."(reCAPTCHA said: " . $resp->error . ")");
    } else {
        echo __("success", "listings");
    }

    die;
}
add_action("wp_ajax_recaptcha_check", "recaptcha_check");
add_action("wp_ajax_nopriv_recaptcha_check", "recaptcha_check");

function recursive_get_parent($object_id){

}

function get_all_parent_menu_items(/*$item*/){
    global $lwp_options;

    $return = $parent_items = array();

    $first_parent = "";

    $inventory_id = (isset($lwp_options['inventory_page']) && !empty($lwp_options['inventory_page']) ? $lwp_options['inventory_page'] : "");
    $menu_name    = 'header-menu';

    if ( ( $locations = get_nav_menu_locations() ) && isset( $locations[ $menu_name ] ) ) {
        $menu       = wp_get_nav_menu_object($locations[ $menu_name ]);

        if(isset($menu->term_id) && !empty($menu->term_id)){
            $menu_items = wp_get_nav_menu_items($menu->term_id);

            $readable_array = array();

            if(!empty($menu_items)){
                foreach($menu_items as $key => $item){
                    $readable_array[($item->object_id != $item->ID ? $item->db_id : $item->object_id)] = $item;

                    // get first parent
                    if($item->object_id == $inventory_id && $item->menu_item_parent != 0){
                        $first_parent = $item->menu_item_parent;
                    }
                }
            }

            $parent_items   = array();
            $still_checking = true;
            $check_item     = (isset($first_parent) && !empty($first_parent) ? $first_parent : "");

            while($still_checking){
                // stop, reached the end of the parent ladder
                if($check_item == 0){
                    $still_checking = false;
                } else {
                    // keep on truckin
                    $parent_items[] = $check_item;
                    $check_item = $readable_array[$check_item]->menu_item_parent;
                }
            }
        }
    }

    return $parent_items;
}

function generate_inventory_ids(){
    update_option("inventory_menu_ids", get_all_parent_menu_items());
}
add_action("init", "generate_inventory_ids");

function my_page_css_class($css_class, $page) {
    if (get_post_type()=='listings') {
        if ($page->object_id == get_option('page_for_posts')) {
            foreach ($css_class as $k=>$v) {
                if ($v=='current_page_parent') unset($css_class[$k]);
            }
        }
    }
    return $css_class;
}
add_filter('nav_menu_css_class', 'my_page_css_class', 10, 2);


function inventory_menu_highlight($classes, $item){
    global $lwp_options;

    $inventory_id = (isset($lwp_options['inventory_page']) && !empty($lwp_options['inventory_page']) ? $lwp_options['inventory_page'] : "");

    $inventory_menu_ids = get_option("inventory_menu_ids");

    if(is_singular('listings') &&  ((isset($inventory_id) && $item->object_id == $inventory_id) || (in_array(($item->object_id != $item->ID ? $item->db_id : $item->object_id), $inventory_menu_ids)))){
        $classes[] = "active";
    }

    return $classes;
}
add_filter('nav_menu_css_class', 'inventory_menu_highlight', 10, 2);

function test_themeforest_creds(){
    global $awp_options;

    $options = array(
        'http'=>array(
            'method'=>"GET",
            'header'=>"Accept-language: en\r\n" .
                "Cookie: foo=bar\r\n" .
                "User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n"
        )
    );

    $context = stream_context_create($options);

    $themes  = @file_get_contents("http://marketplace.envato.com/api/edge/" . $awp_options['themeforest_name'] . "/" . $awp_options['themeforest_api'] . "/wp-list-themes.json", false, $context);
    $themes  = json_decode($themes);

    $purchased_auto = false;

    if(!empty($themes)){
        foreach($themes->{'wp-list-themes'} as $theme){
            if($theme->item_id == 9210971){
                $purchased_auto = true;
            }
        }
    }

    return $purchased_auto;
}

// listing categories import
function import_listing_categories(){
    $demo_content = unserialize('a:18:{s:4:"year";a:6:{s:8:"singular";s:4:"Year";s:6:"plural";s:5:"Years";s:10:"filterable";s:1:"1";s:14:"use_on_listing";i:0;s:13:"compare_value";s:1:"=";s:5:"terms";a:6:{i:0;s:4:"2014";i:1;s:4:"2013";i:2;s:4:"2012";i:3;s:4:"2010";i:4;s:4:"2009";i:5;s:4:"2015";}}s:4:"make";a:6:{s:8:"singular";s:4:"Make";s:6:"plural";s:5:"Makes";s:10:"filterable";s:1:"1";s:14:"use_on_listing";i:0;s:13:"compare_value";s:1:"=";s:5:"terms";a:1:{i:0;s:7:"Porsche";}}s:5:"model";a:6:{s:8:"singular";s:5:"Model";s:6:"plural";s:6:"Models";s:10:"filterable";s:1:"1";s:14:"use_on_listing";i:0;s:13:"compare_value";s:1:"=";s:5:"terms";a:5:{i:0;s:7:"Carrera";i:1;s:3:"GTS";i:2;s:7:"Cayenne";i:3;s:7:"Boxster";i:4;s:5:"Macan";}}s:10:"body_style";a:6:{s:8:"singular";s:10:"Body Style";s:6:"plural";s:11:"Body Styles";s:10:"filterable";s:1:"1";s:14:"use_on_listing";s:1:"1";s:13:"compare_value";s:1:"=";s:5:"terms";a:3:{i:0;s:11:"Convertible";i:1;s:5:"Sedan";i:2;s:22:"Sports Utility Vehicle";}}s:7:"mileage";a:6:{s:8:"singular";s:7:"Mileage";s:6:"plural";s:8:"Mileages";s:10:"filterable";s:1:"1";s:14:"use_on_listing";s:1:"1";s:13:"compare_value";s:4:"&lt;";s:5:"terms";a:10:{i:0;s:5:"10000";i:1;s:5:"20000";i:2;s:5:"30000";i:3;s:5:"40000";i:4;s:5:"50000";i:5;s:5:"60000";i:6;s:5:"70000";i:7;s:5:"80000";i:8;s:5:"90000";i:9;s:6:"100000";}}s:12:"transmission";a:6:{s:8:"singular";s:12:"Transmission";s:6:"plural";s:13:"Transmissions";s:10:"filterable";s:1:"1";s:14:"use_on_listing";s:1:"1";s:13:"compare_value";s:1:"=";s:5:"terms";a:8:{i:0;s:14:"6-Speed Manual";i:1;s:17:"5-Speed Automatic";i:2;s:17:"8-Speed Automatic";i:3;s:17:"6-Speed Semi-Auto";i:4;s:17:"6-Speed Automatic";i:5;s:14:"5-Speed Manual";i:6;s:17:"8-Speed Tiptronic";i:7;s:11:"7-Speed PDK";}}s:12:"fuel_economy";a:6:{s:8:"singular";s:12:"Fuel Economy";s:6:"plural";s:14:"Fuel Economies";s:10:"filterable";s:1:"1";s:14:"use_on_listing";i:0;s:13:"compare_value";s:4:"&lt;";s:5:"terms";a:6:{i:0;s:2:"10";i:1;s:2:"20";i:2;s:2:"30";i:3;s:2:"40";i:4;s:2:"50";i:5;s:2:"50";}}s:9:"condition";a:6:{s:8:"singular";s:9:"Condition";s:6:"plural";s:10:"Conditions";s:10:"filterable";s:1:"1";s:14:"use_on_listing";i:0;s:13:"compare_value";s:1:"=";s:5:"terms";a:3:{i:0;s:9:"Brand New";i:1;s:13:"Slightly Used";i:2;s:4:"Used";}}s:8:"location";a:6:{s:8:"singular";s:8:"Location";s:6:"plural";s:9:"Locations";s:10:"filterable";s:1:"1";s:14:"use_on_listing";i:0;s:13:"compare_value";s:1:"=";s:5:"terms";a:1:{i:0;s:7:"Toronto";}}s:5:"price";a:8:{s:8:"singular";s:5:"Price";s:6:"plural";s:6:"Prices";s:10:"filterable";s:1:"1";s:14:"use_on_listing";i:0;s:13:"compare_value";s:4:"&lt;";s:8:"currency";s:1:"1";s:10:"link_value";s:5:"price";s:5:"terms";a:10:{i:0;s:5:"10000";i:1;s:5:"20000";i:2;s:5:"30000";i:3;s:5:"40000";i:4;s:5:"50000";i:5;s:5:"60000";i:6;s:5:"70000";i:7;s:5:"80000";i:8;s:5:"90000";i:9;s:6:"100000";}}s:10:"drivetrain";a:6:{s:8:"singular";s:10:"Drivetrain";s:6:"plural";s:11:"Drivetrains";s:10:"filterable";i:0;s:14:"use_on_listing";s:1:"1";s:13:"compare_value";s:1:"=";s:5:"terms";a:4:{i:0;s:3:"AWD";i:1;s:3:"RWD";i:2;s:3:"4WD";i:3;s:14:"Drivetrain RWD";}}s:6:"engine";a:6:{s:8:"singular";s:6:"Engine";s:6:"plural";s:7:"Engines";s:10:"filterable";i:0;s:14:"use_on_listing";s:1:"1";s:13:"compare_value";s:1:"=";s:5:"terms";a:9:{i:0;s:7:"3.6L V6";i:1;s:17:"4.8L V8 Automatic";i:2;s:13:"4.8L V8 Turbo";i:3;s:7:"4.8L V8";i:4;s:7:"3.8L V6";i:5;s:18:"2.9L Mid-Engine V6";i:6;s:18:"3.4L Mid-Engine V6";i:7;s:14:"3.0L V6 Diesel";i:8;s:13:"3.0L V6 Turbo";}}s:14:"exterior_color";a:6:{s:8:"singular";s:14:"Exterior Color";s:6:"plural";s:15:"Exterior Colors";s:10:"filterable";i:0;s:14:"use_on_listing";s:1:"1";s:13:"compare_value";s:1:"=";s:5:"terms";a:10:{i:0;s:13:"Racing Yellow";i:1;s:23:"Rhodium Silver Metallic";i:2;s:16:"Peridot Metallic";i:3;s:17:"Ruby Red Metallic";i:4;s:5:"White";i:5;s:18:"Aqua Blue Metallic";i:6;s:23:"Chestnut Brown Metallic";i:7;s:10:"Guards Red";i:8;s:18:"Dark Blue Metallic";i:9;s:18:"Lime Gold Metallic";}}s:14:"interior_color";a:6:{s:8:"singular";s:14:"Interior Color";s:6:"plural";s:15:"Interior Colors";s:10:"filterable";i:0;s:14:"use_on_listing";s:1:"1";s:13:"compare_value";s:1:"=";s:5:"terms";a:10:{i:0;s:14:"Interior Color";i:1;s:10:"Agate Grey";i:2;s:15:"Alcantara Black";i:3;s:11:"Marsala Red";i:4;s:5:"Black";i:5;s:13:"Platinum Grey";i:6;s:11:"Luxor Beige";i:7;s:13:"Platinum Grey";i:8;s:21:"Black / Titanium Blue";i:9;s:10:"Agate Grey";}}s:3:"mpg";a:7:{s:8:"singular";s:3:"MPG";s:6:"plural";s:3:"MPG";s:10:"filterable";i:0;s:14:"use_on_listing";s:1:"1";s:13:"compare_value";s:1:"=";s:10:"link_value";s:3:"mpg";s:5:"terms";a:9:{i:0;s:16:"19 city / 27 hwy";i:1;s:16:"16 city / 24 hwy";i:2;s:15:"15 city /21 hwy";i:3;s:16:"15 city / 21 hwy";i:4;s:16:"18 city / 26 hwy";i:5;s:16:"16 city / 24 hwy";i:6;s:16:"20 city / 30 hwy";i:7;s:16:"20 City / 28 Hwy";i:8;s:16:"19 city / 29 hwy";}}s:12:"stock_number";a:6:{s:8:"singular";s:12:"Stock Number";s:6:"plural";s:13:"Stock Numbers";s:10:"filterable";i:0;s:14:"use_on_listing";s:1:"1";s:13:"compare_value";s:1:"=";s:5:"terms";a:12:{i:0;s:6:"590388";i:1;s:6:"590524";i:2;s:6:"590512";i:3;s:6:"590499";i:4;s:6:"590435";i:5;s:6:"590421";i:6;s:6:"590476";i:7;s:6:"590271";i:8;s:6:"590497";i:9;s:5:"16115";i:10;s:6:"590124";i:11;s:6:"590562";}}s:10:"vin_number";a:6:{s:8:"singular";s:10:"VIN Number";s:6:"plural";s:11:"VIN Numbers";s:10:"filterable";i:0;s:14:"use_on_listing";s:1:"1";s:13:"compare_value";s:1:"=";s:5:"terms";a:12:{i:0;s:17:"WP0CB2A92CS376450";i:1;s:17:"WP0AB2A74AL092462";i:2;s:17:"WP1AD29P09LA73659";i:3;s:17:"WP0AB2A74AL079264";i:4;s:17:"WP0CB2A92CS754706";i:5;s:17:"WP0CA2A96AS740274";i:6;s:17:"WP0AB2A74AL060306";i:7;s:17:"WP0AB2A74AL060306";i:8;s:17:"WP1AD29P09LA65818";i:9;s:17:"WP0AB2E81EK190171";i:10;s:17:"WP0CB2A92CS377324";i:11;s:17:"WP0CT2A92CS326491";}}s:7:"options";a:1:{s:5:"terms";a:40:{i:0;s:23:"Adaptive Cruise Control";i:1;s:7:"Airbags";i:2;s:16:"Air Conditioning";i:3;s:12:"Alarm System";i:4;s:21:"Anti-theft Protection";i:5;s:15:"Audio Interface";i:6;s:25:"Automatic Climate Control";i:7;s:20:"Automatic Headlights";i:8;s:15:"Auto Start/Stop";i:9;s:19:"Bi-Xenon Headlights";i:10;s:19:"Bluetooth® Handset";i:11;s:21:"BOSE® Surround Sound";i:12;s:26:"Burmester® Surround Sound";i:13;s:18:"CD/DVD Autochanger";i:14;s:9:"CDR Audio";i:15;s:14:"Cruise Control";i:16;s:21:"Direct Fuel Injection";i:17;s:22:"Electric Parking Brake";i:18;s:10:"Floor Mats";i:19;s:18:"Garage Door Opener";i:20;s:15:"Leather Package";i:21;s:25:"Locking Rear Differential";i:22;s:20:"Luggage Compartments";i:23;s:19:"Manual Transmission";i:24;s:17:"Navigation Module";i:25;s:15:"Online Services";i:26;s:10:"ParkAssist";i:27;s:21:"Porsche Communication";i:28;s:14:"Power Steering";i:29;s:16:"Reversing Camera";i:30;s:20:"Roll-over Protection";i:31;s:12:"Seat Heating";i:32;s:16:"Seat Ventilation";i:33;s:18:"Sound Package Plus";i:34;s:20:"Sport Chrono Package";i:35;s:22:"Steering Wheel Heating";i:36;s:24:"Tire Pressure Monitoring";i:37;s:25:"Universal Audio Interface";i:38;s:20:"Voice Control System";i:39;s:14:"Wind Deflector";}}}');

    $update = update_option("listing_categories", $demo_content);

    if($update){
        update_option("show_listing_categories", "hide");
        _e("The listing categories have been imported.", "listings");
    } else {
        _e("There was an error importing the listing categories, please try again later.", "listings");
    }

    die;
}

add_action("wp_ajax_import_listing_categories", "import_listing_categories");
add_action("wp_ajax_noprive_import_listing_categories", "import_listing_categories");


function convert_seo_string($string){
    global $post;

    $categories = get_listing_categories();
    $post_meta  = get_post_meta_all($post->ID);

    foreach($categories as $category){
        $safe   = str_replace(" ", "-", strtolower($category['singular']));
        $string = str_replace("%" . $safe . "%", (isset($post_meta[$safe]) && !empty($post_meta[$safe]) ? $post_meta[$safe] : ""), $string);
    }

    return $string;
}

function hide_import_listing_categories(){
    update_option("show_listing_categories", "hide");

    die;
}

add_action("wp_ajax_hide_import_listing_categories", "hide_import_listing_categories");
add_action("wp_ajax_noprive_hide_import_listing_categories", "hide_import_listing_categories");


function remove_parent_classes($class) {
    return ($class == 'current_page_item' || $class == 'current_page_parent' || $class == 'current_page_ancestor'  || $class == 'current-menu-item') ? false : true;
}

function add_class_to_wp_nav_menu($classes){
    switch (get_post_type()){
        case 'listings_portfolio':
            // we're viewing a custom post type, so remove the 'current_page_xxx and current-menu-item' from all menu items.
            $classes = array_filter($classes, "remove_parent_classes");

            break;
    }
    return $classes;
}
add_filter('nav_menu_css_class', 'add_class_to_wp_nav_menu');



// gget_child_categories
function get_child_categories(){
    //echo $_POST['name'] . "<br>" . $_POST['value'];
    global $lwp_options;

    $return = array();

    $category        = get_single_listing_category($_POST['name']);
    $load_options    = ($category['dependancies'][$_POST['key']]);
    $second_category = get_single_listing_category($_POST['name']);

    if(isset($second_category['dependancies'][$_POST['key']]) && !empty($second_category['dependancies'][$_POST['key']])){
        // foreach option
        foreach($second_category['dependancies'][$_POST['key']] as $key){
            if(!is_null($category['terms'][$key])){
                $return[] = $category['terms'][$key];
            }
        }
    }

    if(isset($lwp_options['sort_terms']) && $lwp_options['sort_terms'] == "desc"){
        arsort($return);
    } else {
        asort($return);
    }

    $return = array_filter($return, 'is_not_null');
    $return = array_values($return);

    array_multisort(array_map('strtolower', $return), $return);

    echo json_encode($return);

    die;
}

function is_not_null ($var) { return !is_null($var); }

add_action("wp_ajax_get_child_categories", "get_child_categories");
add_action("wp_ajax_nopriv_get_child_categories", "get_child_categories");

//********************************************
//	Add subscriber to mail chimp (WP-AJAX)
//***********************************************************
function add_mailchimp(){
    $email = wp_filter_nohtml_kses( $_POST['email'] );

    if(isset($email)){

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            echo "Not a valid email!";
            die;
        }

        require_once("classes/mailchimp/MCAPI.class.php");

        global $lwp_options;

        $api_key = $lwp_options['mailchimp_api_key'];
        $api     = new MCAPI($api_key);
        $list    = $api->lists();
        $retval  = $api->listSubscribe( $_POST['list'], $email );

        if ($api->errorCode){
            if($api->errorCode == 214){
                echo "Already subscribed.";
            } else {
                echo "Unable to load listSubscribe()!\n";
                //echo "\tCode=".$api->errorCode."\n";
                //echo "\tMsg=".$api->errorMessage."\n";
            }
        } else {
            echo "Subscribed - look for the confirmation email!\n";
        }
    } else {
        echo "Enter an email!";
    }

    die;
}

add_action('wp_ajax_add_mailchimp', 'add_mailchimp');
add_action('wp_ajax_nopriv_add_mailchimp', 'add_mailchimp');
function generate_wpml_xml(){
    $nbsp = "&nbsp;&nbsp;&nbsp;&nbsp;";

    echo "&lt;wpml-config><br>
            " . $nbsp . "&lt;custom-fields><br>
                " . $nbsp . $nbsp . "&lt;custom-field action=\"translate\">secondary_title&lt;/custom-field><br>
                " . $nbsp . $nbsp . "&lt;custom-field action=\"translate\">action_text&lt;/custom-field><br>
                " . $nbsp . $nbsp . "&lt;custom-field action=\"translate\">action_button_text&lt;/custom-field><br>
                " . $nbsp . $nbsp . "&lt;custom-field action=\"ignore\">sidebar&lt;/custom-field><br>
                " . $nbsp . $nbsp . "&lt;custom-field action=\"ignore\">header_image&lt;/custom-field><br>
                " . $nbsp . $nbsp . "&lt;custom-field action=\"ignore\">footer_area&lt;/custom-field><br>
                " . $nbsp . $nbsp . "&lt;custom-field action=\"ignore\">action_link&lt;/custom-field><br>
            " . $nbsp . "&lt;/custom-fields><br>
            " . $nbsp . "&lt;custom-types><br>
                " . $nbsp . $nbsp . "&lt;custom-type translate=\"1\">listings&lt;/custom-type><br>
                " . $nbsp . $nbsp . "&lt;custom-type translate=\"1\">listings_portfolio&lt;/custom-type><br>
            " . $nbsp . "&lt;/custom-types>    <br>
            " . $nbsp . "&lt;language-switcher-settings><br>
                " . $nbsp . $nbsp . "&lt;key name=\"icl_lang_sel_config\"><br>
                    " . $nbsp . $nbsp . $nbsp . "&lt;key name=\"font-current-normal\">#444444&lt;/key><br>
                    " . $nbsp . $nbsp . $nbsp . "&lt;key name=\"font-current-hover\">#000000&lt;/key><br>
                    " . $nbsp . $nbsp . $nbsp . "&lt;key name=\"background-current-normal\">#ffffff&lt;/key><br>
                    " . $nbsp . $nbsp . $nbsp . "&lt;key name=\"background-current-hover\">#eeeeee&lt;/key><br>
                    " . $nbsp . $nbsp . $nbsp . "&lt;key name=\"font-other-normal\">#444444&lt;/key><br>
                    " . $nbsp . $nbsp . $nbsp . "&lt;key name=\"font-other-hover\">#000000&lt;/key><br>
                    " . $nbsp . $nbsp . $nbsp . "&lt;key name=\"background-other-normal\">#ffffff&lt;/key><br>
                    " . $nbsp . $nbsp . $nbsp . "&lt;key name=\"background-other-hover\">#eeeeee&lt;/key><br>
                    " . $nbsp . $nbsp . $nbsp . "&lt;key name=\"border\">#cdcdcd&lt;/key><br>
                " . $nbsp . $nbsp . "&lt;/key><br>
                " . $nbsp . $nbsp . "&lt;key name=\"icl_lang_sel_footer_config\"><br>
                    " . $nbsp . $nbsp . $nbsp . "&lt;key name=\"font-current-normal\">#444444&lt;/key><br>
                    " . $nbsp . $nbsp . $nbsp . "&lt;key name=\"font-current-hover\">#000000&lt;/key><br>
                    " . $nbsp . $nbsp . $nbsp . "&lt;key name=\"background-current-normal\">#ffffff&lt;/key><br>
                    " . $nbsp . $nbsp . $nbsp . "&lt;key name=\"background-current-hover\">#eeeeee&lt;/key><br>
                    " . $nbsp . $nbsp . $nbsp . "&lt;key name=\"font-other-normal\">#444444&lt;/key><br>
                    " . $nbsp . $nbsp . $nbsp . "&lt;key name=\"font-other-hover\">#000000&lt;/key><br>
                    " . $nbsp . $nbsp . $nbsp . "&lt;key name=\"background-other-normal\">#ffffff&lt;/key><br>
                    " . $nbsp . $nbsp . $nbsp . "&lt;key name=\"background-other-hover\">#eeeeee&lt;/key><br>
                    " . $nbsp . $nbsp . $nbsp . "&lt;key name=\"border\">#cdcdcd&lt;/key><br>
                " . $nbsp . $nbsp . "&lt;/key>    <br>
                " . $nbsp . $nbsp . "&lt;key name=\"icl_language_switcher_sidebar\">0&lt;/key>  <br>
                " . $nbsp . $nbsp . "&lt;key name=\"icl_widget_title_show\">0&lt;/key><br>
                " . $nbsp . $nbsp . "&lt;key name=\"icl_lang_sel_type\">dropdown&lt;/key><br>
                " . $nbsp . $nbsp . "&lt;key name=\"icl_lso_link_empty\">0&lt;/key><br>
                " . $nbsp . $nbsp . "&lt;key name=\"icl_lso_flags\">0&lt;/key><br>
                " . $nbsp . $nbsp . "&lt;key name=\"icl_lso_native_lang\">1&lt;/key><br>
                " . $nbsp . $nbsp . "&lt;key name=\"icl_lso_display_lang\">1&lt;/key><br>
                " . $nbsp . $nbsp . "&lt;key name=\"icl_lang_sel_footer\">0&lt;/key><br>
                " . $nbsp . $nbsp . "&lt;key name=\"icl_post_availability\">0&lt;/key><br>
                " . $nbsp . $nbsp . "&lt;key name=\"icl_post_availability_position\">below&lt;/key><br>
                " . $nbsp . $nbsp . "&lt;key name=\"icl_post_availability_text\">This post is also available in: %s&lt;/key>   <br>
            " . $nbsp . "&lt;/language-switcher-settings>  <br>
            " . $nbsp . "&lt;admin-texts><br>";

    // listing_categories
    echo $nbsp . $nbsp . "&lt;key name=\"listing_categories\"><br>";
    foreach(get_listing_categories() as $category => $item){
        echo $nbsp . $nbsp . $nbsp . "&lt;key name=\"" . $category . "\"><br>
                        " . $nbsp . $nbsp . $nbsp . $nbsp . "&lt;key name=\"singular\" /><br>
                        " . $nbsp . $nbsp . $nbsp . $nbsp . "&lt;key name=\"plural\" /><br>
                        " . $nbsp . $nbsp . $nbsp . $nbsp . "&lt;key name=\"terms\"><br>";
        if(!empty($item['terms'])){
            $i = 0;
            foreach($item['terms'] as $term){
                echo $nbsp . $nbsp . $nbsp . $nbsp . $nbsp . "&lt;key name=\"" . $i . "\" /><br>";// . $term . "&lt;/key><br>";
                $i++;
            }
        }
        echo $nbsp . $nbsp . $nbsp . $nbsp . "&lt;/key><br>";

        echo $nbsp . $nbsp . $nbsp . "&lt;/key><br>";
    }
    echo $nbsp . $nbsp . "&lt;/key><br>";

    // theme options
    $theme_options_translate   = array("toolbar_login", "toolbar_languages", "toolbar_search", "toolbar_phone", "toolbar_address", "footer_text", "blog_primary_title", "blog_secondary_title", "fourohfour_page_title", "fourohfour_page_secondary_title", "fourohfour_page_breadcrumb", "search_page_title", "search_page_secondary_title", "search_page_breadcrumb", "category_page_title", "category_page_secondary_title", "category_page_breadcrumb", "tag_page_title", "tag_page_secondary_title", "tag_page_breadcrumb");
    echo $nbsp . $nbsp . "&lt;key name=\"automotive_wp\"><br>";
    foreach($theme_options_translate as $option){
        echo $nbsp . $nbsp . $nbsp . "&lt;key name=\"" . $option . "\" /><br>";
    }
    echo $nbsp . $nbsp . "&lt;/key><br>";

    // listing options //// additional_categories
    $listing_options_translate = array("vehicle_singular_form", "vehicle_plural_form", "inventory_primary_title", "inventory_secondary_title", "sale_value", "tax_label_box", "tax_label_page", "price_text_replacement", "fuel_efficiency_text", "recent_vehicles_title", "recent_vehicles_desc", "previous_vehicle_label", "request_more_label", "schedule_test_label", "make_offer_label", "tradein_label", "pdf_brochure_label", "print_vehicle_label", "email_friend_label", "next_vehicle_label", "first_tab", "second_tab", "third_tab", "fourth_tab", "fifth_tab", "listing_comment_footer", "job_description_title", "project_details_title", "related_projects_title", "default_value_price", "default_value_city", "default_value_hwy", "recent_vehicles_title", "recent_vehicles_desc");
    echo $nbsp . $nbsp . "&lt;key name=\"listing_wp\"><br>";
    foreach($listing_options_translate as $option){
        echo $nbsp . $nbsp . $nbsp . "&lt;key name=\"" . $option . "\" /><br>";
    }
    echo $nbsp . $nbsp . "&lt;/key><br>";

    echo "" . $nbsp . "&lt;/admin-texts><br>
&lt;/wpml-config>";

    die;
}


if(!function_exists("get_page_by_slug")){
    function get_page_by_slug($page_slug, $output = OBJECT ) {
        global $wpdb;

        $post_type = 'listings';

        $page = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type= %s AND post_status = 'publish'", $page_slug, $post_type ) );

        if ( $page )
            return get_post($page, $output);

        return null;
    }
}

/* Post URLs to IDs function, supports custom post types - borrowed and modified from url_to_postid() in wp-includes/rewrite.php */
function auto_url_to_postid($url)
{
    global $wp_rewrite;

    $url = apply_filters('url_to_postid', $url);

    // First, check to see if there is a 'p=N' or 'page_id=N' to match against
    if ( preg_match('#[?&](p|page_id|attachment_id)=(\d+)#', $url, $values) )	{
        $id = absint($values[2]);
        if ( $id )
            return $id;
    }

    // Check to see if we are using rewrite rules
    $rewrite = $wp_rewrite->wp_rewrite_rules();

    // Not using rewrite rules, and 'p=N' and 'page_id=N' methods failed, so we're out of options
    if ( empty($rewrite) )
        return 0;

    // Get rid of the #anchor
    $url_split = explode('#', $url);
    $url = $url_split[0];

    // Get rid of URL ?query=string
    $url_split = explode('?', $url);
    $url = $url_split[0];

    // Add 'www.' if it is absent and should be there
    if ( false !== strpos(home_url(), '://www.') && false === strpos($url, '://www.') )
        $url = str_replace('://', '://www.', $url);

    // Strip 'www.' if it is present and shouldn't be
    if ( false === strpos(home_url(), '://www.') )
        $url = str_replace('://www.', '://', $url);

    // Strip 'index.php/' if we're not using path info permalinks
    if ( !$wp_rewrite->using_index_permalinks() )
        $url = str_replace('index.php/', '', $url);

    if ( false !== strpos($url, home_url()) ) {
        // Chop off http://domain.com
        $url = str_replace(home_url(), '', $url);
    } else {
        // Chop off /path/to/blog
        $home_path = parse_url(home_url());
        $home_path = isset( $home_path['path'] ) ? $home_path['path'] : '' ;
        $url = str_replace($home_path, '', $url);
    }

    // Trim leading and lagging slashes
    $url = trim($url, '/');

    $request = $url;
    // Look for matches.
    $request_match = $request;
    foreach ( (array)$rewrite as $match => $query) {
        // If the requesting file is the anchor of the match, prepend it
        // to the path info.
        if ( !empty($url) && ($url != $request) && (strpos($match, $url) === 0) )
            $request_match = $url . '/' . $request;

        if ( preg_match("!^$match!", $request_match, $matches) ) {
            // Got a match.
            // Trim the query of everything up to the '?'.
            $query = preg_replace("!^.+\?!", '', $query);

            // Substitute the substring matches into the query.
            $query = addslashes(WP_MatchesMapRegex::apply($query, $matches));

            // Filter out non-public query vars
            global $wp;
            parse_str($query, $query_vars);
            $query = array();
            foreach ( (array) $query_vars as $key => $value ) {
                if ( in_array($key, $wp->public_query_vars) )
                    $query[$key] = $value;
            }

            // Taken from class-wp.php
            foreach ( $GLOBALS['wp_post_types'] as $post_type => $t )
                if ( $t->query_var )
                    $post_type_query_vars[$t->query_var] = $post_type;

            foreach ( $wp->public_query_vars as $wpvar ) {
                if ( isset( $wp->extra_query_vars[$wpvar] ) )
                    $query[$wpvar] = $wp->extra_query_vars[$wpvar];
                elseif ( isset( $_POST[$wpvar] ) )
                    $query[$wpvar] = $_POST[$wpvar];
                elseif ( isset( $_GET[$wpvar] ) )
                    $query[$wpvar] = $_GET[$wpvar];
                elseif ( isset( $query_vars[$wpvar] ) )
                    $query[$wpvar] = $query_vars[$wpvar];

                if ( !empty( $query[$wpvar] ) ) {
                    if ( ! is_array( $query[$wpvar] ) ) {
                        $query[$wpvar] = (string) $query[$wpvar];
                    } else {
                        foreach ( $query[$wpvar] as $vkey => $v ) {
                            if ( !is_object( $v ) ) {
                                $query[$wpvar][$vkey] = (string) $v;
                            }
                        }
                    }

                    if ( isset($post_type_query_vars[$wpvar] ) ) {
                        $query['post_type'] = $post_type_query_vars[$wpvar];
                        $query['name'] = $query[$wpvar];
                    }
                }
            }

            // Do the query
            $query = new WP_Query($query);
            if ( !empty($query->posts) && $query->is_singular )
                return $query->post->ID;
            else
                return 0;
        }
    }
    return 0;
}

add_action('wpcf7_before_send_mail', 'wpcf7_update_email_body');

function wpcf7_update_email_body($contact_form) {
    $submission = WPCF7_Submission::get_instance();

    if ( $submission ) {
        $mail = $contact_form->prop('mail');
        $additional_settings = $contact_form->prop('additional_settings');

        if ( get_option('permalink_structure') ) {
            $listing_id     = auto_url_to_postid($_SERVER["HTTP_REFERER"]);
            $listing_object = get_post($listing_id);
        } else {
            $listing_object = get_page_by_slug($_REQUEST['listings']);
        }

        if(isset($listing_object) && !empty($listing_object)){
            $listing_details  = "Listing URL: " . get_permalink($listing_object->ID);
            $listing_details .= "\nListing Title: " . $listing_object->post_title;

            $mail['body'] = str_replace("[_listing_details]", $listing_details, $mail['body']);

            $additional_settings = 'on_sent_ok: "setTimeout(function(){ $.fancybox.close(); }, 2000);"';
        }

        $contact_form->set_properties(array('mail' => $mail, 'additional_settings' => $additional_settings));
    }
}
?>