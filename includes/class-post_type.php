<?php
/**
 * Post_Type.
 *
 * @package CookieFox
 */

namespace CookieFox;

defined('ABSPATH') || exit;

class Post_Type {
	public function __construct() {
		$option_enabled = Helper::get_option("consent_type");		
		
		if($option_enabled !== "category"){
			return;
		}

		add_action("init", array($this, "init"));
		add_action('cmb2_admin_init', array($this, "metabox"));
		add_filter( 'use_block_editor_for_post_type', array($this, "use_block_editor"), 10, 2);		
		add_action( 'admin_init', array( $this, 'admin_order_taxonomy'));
		add_action( 'pre_get_posts', array($this, 'pre_get_posts'));
		add_filter('default_hidden_meta_boxes', array($this, 'default_hidden_meta_boxes'),10,2);
		add_action('admin_head', array( $this, 'admin_head' ));
		add_filter('manage_cookiefox_cookie_posts_columns', array($this, 'manage_posts_columns'));
		add_filter('manage_cookiefox_cookie_posts_custom_column', array($this, 'manage_posts_custom_column'), 10, 2);
		add_filter('manage_edit-cookiefox_cookie_sortable_columns', array($this, 'manage_sortable_columns'), 10, 2);		
		add_filter('parent_file', array($this, 'menu_highlight'), 9999);
		add_filter('submenu_file', array($this, 'submenu_highlight'), 9999, 2);
		add_action("all_admin_notices", array($this, "tabs"));
	}
	
	public function init() {		
		
		$this->register_post_type();
		$this->register_taxonomy();
		
	}
	
	private function register_post_type() {

		$labels = array(
			'name' => _x( 'Cookies', 'Post Type General Name', 'cookiefox' ),
			'singular_name' => _x( 'Cookie', 'Post Type Singular Name', 'cookiefox' ),
			'menu_name' => __( 'Cookies', 'cookiefox' ),
			'name_admin_bar' => __( 'Cookies', 'cookiefox' ),
			'archives' => __( 'Cookie Archives', 'cookiefox' ),
			'attributes' => __( 'Cookie Attributes', 'cookiefox' ),
			'parent_item_colon' => __( 'Parent Item:', 'cookiefox' ),
			'all_items' => __( 'All Cookies', 'cookiefox' ),
			'add_new_item' => __( 'Add New Cookie', 'cookiefox' ),
			'add_new' => __( 'Add New', 'cookiefox' ),
			'new_item' => __( 'New Cookie', 'cookiefox' ),
			'edit_item' => __( 'Edit Cookie', 'cookiefox' ),
			'update_item' => __( 'Update Cookie', 'cookiefox' ),
			'view_item' => __( 'View Cookie', 'cookiefox' ),
			'view_items' => __( 'View Cookies', 'cookiefox' ),
			'search_items' => __( 'Search Cookie', 'cookiefox' ),
			'not_found' => __( 'Not found', 'cookiefox' ),
			'not_found_in_trash' => __( 'Not found in Trash', 'cookiefox' ),
			'insert_into_item' => __( 'Insert into cookie', 'cookiefox' ),
			'uploaded_to_this_item' => __( 'Uploaded to this cookie', 'cookiefox' ),
			'items_list' => __( 'Cookies list', 'cookiefox' ),
			'items_list_navigation' => __( 'Cookies list navigation', 'cookiefox' ),
			'filter_items_list' => __( 'Filter cookies list', 'cookiefox' ),
			'item_published' => __('Cookie published.', 'cookiefox'),
			'item_updated' => __('Cookie updated.', 'cookiefox'),
		);
		$args = array(
			'label' => __( 'Cookie', 'cookiefox' ),
			'labels' => $labels,
			'supports' => array( 'title', 'page-attributes'),
			'hierarchical' => false,
			'public' => false,
			'show_ui' => true,
			'show_in_menu' => false,
			'menu_position' => 80,
			'menu_icon' => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCIgdmlld0JveD0iMCAwIDIwIDIwIj48cGF0aCBmaWxsPSIjRkZGIiBkPSJNMTAuNzEyNTk5NCwxLjUwMDI3MDE3IEMxMi45ODExNjQzLDEuNTE3OTEzNiAxNS4xMDYzMywyLjM5OTAwMTUgMTYuNjk0NjMwMywzLjkzMTA3NjQ1IEwxNi43MTUzMjE2LDMuOTUxMTM5NjQgTDEzLjM5NzM4LDcuMjE0Mjk3MyBMMTMuNDI1MTEwNyw3LjI0MDQxNjYgQzEyLjY4NzQ0MzIsNi41MzUxMzI2OCAxMS42OTc4MjU2LDYuMTMyODgyODggMTAuNjQzMTA5Miw2LjEzMjg4Mjg4IEM4LjQ0OTk4NTI2LDYuMTMyODgyODggNi42NzE5NTA5LDcuODY0MTY0NDQgNi42NzE5NTA5LDEwIEM2LjY3MTk1MDksMTIuMTM1ODM1NiA4LjQ0OTk4NTI2LDEzLjg2NzExNzEgMTAuNjQzMTA5MiwxMy44NjcxMTcxIEMxMi4yNzgyOTIsMTMuODY3MTE3MSAxMy42OTI4NTQ5LDEzLjMwNTU1NTYgMTQuODg2Nzk3OSwxMi4xODI0MzI0IEwxOCwxNC42MzI1NjcxIEMxNi40NTI1OTM5LDE2LjgzNTkxOCAxMy43NzM2NTg0LDE4LjUgMTAuNjQzMTA5MiwxOC41IEM1Ljg2OTY1MTc5LDE4LjUgMiwxNC42OTQ0MjA0IDIsMTAgQzIsNS4zMDU1Nzk2MyA1Ljg2OTY1MTc5LDEuNSAxMC42NDMxMDkyLDEuNSBMMTAuNzEyNTk5NCwxLjUwMDI3MDE3IFogTTE0Ljg4Njc5NzksMTMuMTc3OTI3OSBDMTQuMzI3Mjg5MiwxMy42ODU4NDYgMTMuNTM1ODM1NywxNC4wODg0ODUyIDEyLjUxMjQzNzUsMTQuMzg1ODQ1NCBDMTIuNDcxMjYyOSwxNC4zOTU1ODkyIDEyLjQ0NjY0OTksMTQuNDM3Mzc3MSAxMi40NTcyNDMxLDE0LjQ3ODMzMiBDMTIuNDY2MTg3NiwxNC41MTI5MTMxIDEyLjQ5Nzg0NjksMTQuNTM2NzIxMSAxMi41MzM1NTE5LDE0LjUzNTcxMjQgTDE0LjQ3MDQyODcsMTQuNDc3MzU2IEMxNC41MTI3MjUsMTQuNDc2NjIxNCAxNC41NDc1ODkzLDE0LjUxMDMzMzUgMTQuNTQ4MywxNC41NTI2MzAyIEMxNC41NDg2OTg3LDE0LjU3NjM1ODYgMTQuNTM4MDc2MSwxNC41OTg5MzE3IDE0LjUxNzc3ODUsMTQuNjExNTQ3MSBDMTMuOTAwMzA3MSwxNS4xMTIzMDc0IDEzLjM1NzA4NzksMTUuNDkxOTA2IDEyLjg4ODEyMDcsMTUuNzUwMzQyOCBDMTIuMzkzNDA1NCwxNi4wMjI5NjkgMTMuMDYyODcxMSwxNS45MTgzMTMyIDE0Ljg5NjUxNzgsMTUuNDM2Mzc1NCBDMTQuOTM3Mzg2OCwxNS40MjU0MzUzIDE0Ljk3ODk5MTYsMTUuNDUwMzU2NiAxNC45ODkyODE3LDE1LjQ5MTM4ODYgQzE0Ljk5NDU4MTEsMTUuNTEyNTE5OCAxNC45OTA2MzE1LDE1LjUzNDkwNzkgMTQuOTc4Mzc0MywxNS41NTI5MTg0IEwxNC4xMzg1ODUzLDE2Ljc5MDkxMDQgQzE0LjExNDU1NTMsMTYuODI1NzI2NyAxNC4xMjM2NTg3LDE2Ljg3MzM2MjQgMTQuMTU4NjU0MywxNi44OTcxMjgzIEMxNC4xODA2MjczLDE2LjkxMjA1MDUgMTQuMjA4NzYyNSwxNi45MTQ1MjQxIDE0LjIzMjIxNDMsMTYuOTAxOTA2MyBDMTUuMjAzODgwNCwxNi40MzIzOTU0IDE2LjA5NjkxMjMsMTUuNzE5NDQ3NyAxNi45MTEzMSwxNC43NjMwNjMxIEwxNC44ODY3OTc5LDEzLjE3NzkyNzkgWiIvPjwvc3ZnPg==',
			'show_in_admin_bar' => false,
			'show_in_nav_menus' => false,
			'can_export' => true,
			'has_archive' => false,
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'rewrite' => false,
			'capability_type' => 'page',
			'show_in_rest' => true,
		);
		register_post_type( 'cookiefox_cookie', $args );

	}
	
	private function register_taxonomy() {

		$labels = array(
			'name' => _x( 'Categories', 'Taxonomy General Name', 'cookiefox' ),
			'singular_name' => _x( 'Category', 'Taxonomy Singular Name', 'cookiefox' ),
			'menu_name' => __( 'Categories', 'cookiefox' ),
			'all_items' => __( 'All Categories', 'cookiefox' ),
			'parent_item' => __( 'Parent Category', 'cookiefox' ),
			'parent_item_colon' => __( 'Parent Category:', 'cookiefox' ),
			'new_item_name' => __( 'New Category Name', 'cookiefox' ),
			'add_new_item' => __( 'Add New Category', 'cookiefox' ),
			'edit_item' => __( 'Edit Category', 'cookiefox' ),
			'update_item' => __( 'Update Category', 'cookiefox' ),
			'view_item' => __( 'View Category', 'cookiefox' ),
			'search_items' => __( 'Search Categories', 'cookiefox' ),
			'no_terms' => __( 'No categories', 'cookiefox' ),
		);
		$args = array(
			'labels' => $labels,
			'hierarchical' => false,
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => false,
			'show_tagcloud' => false,
			'rewrite' => false,
			'show_in_rest' => true
		);
		register_taxonomy( 'cookiefox_category', array( 'cookiefox_cookie' ), $args );

	}
	
	public function metabox() {
		$post_type_taxonomy_metabox = new_cmb2_box(array(
			'id' => 'cookiefox_cookie_taxonomy',
			'title' => __('Cookie Category', 'cookiefox'),
			'object_types' => array( 'cookiefox_cookie' ),
			'context' => 'side',
			'priority' => 'default',
			'show_names' => false,
			'attributes' => array(
				'classes' => 'cookiefox-mb'
			)
		));
		
		$post_type_taxonomy_metabox->add_field(array(
			'name' => 'Cookie Category',
			'id' => 'cookiefox_cookie_category',
			'taxonomy' => 'cookiefox_category',
			'type' => 'taxonomy_radio',
			'text' => array(
				'no_terms_text' => __('There are no categories available.', 'cookiefox')
			),
    	'query_args' => array(
        'orderby' => 'meta_value_num',
				'meta_key' => 'order',
			),
			'show_option_none' => true,
			'remove_default' => 'true',
		));

		$post_type_info_metabox = new_cmb2_box(array(
			'id' => 'cookiefox_cookie_info',
			'title' => __('Cookie Information', 'cookiefox'),
			'object_types' => array( 'cookiefox_cookie' ),
			'context' => 'normal',
			'priority' => 'default',
			'attributes' => array(
				'classes' => 'cookiefox-mb'
			)
		));

		$post_type_info_metabox->add_field(array(
			'name' => __('Vendor', 'cookiefox'),
			'id' => 'vendor',
			'type' => 'text',
		));
		
		$post_type_info_metabox->add_field(array(
			'name' => __('Description', 'cookiefox'),
			'id' => 'description',
			'type' => 'textarea_small',
		));

		$post_type_info_metabox->add_field(array(
			'name' => __('Privacy Policy URL', 'cookiefox'),
			'id' => 'privacy_policy',
			'type' => 'text_url',
		));

		$post_type_cookie_metabox = new_cmb2_box(array(
			'id' => 'cookiefox_cookie',
			'title' => __('Scripts & Cookies', 'cookiefox'),
			'object_types' => array( 'cookiefox_cookie' ),
			'context' => 'normal',
			'priority' => 'default',
			'attributes' => array(
				'classes' => 'cookiefox-mb'
			)
		));

		// $post_type_cookie_metabox->add_field(array(
		// 	'name' => esc_html__('Priorize', 'cookiefox'),
		// 	'desc' => __('If activated, scripts will be executed in head section.', 'cookiefox'),
		// 	'id' => 'scripts_prioritize',
		// 	'type' => 'toggle',
		// ));		
		
		$post_type_cookie_metabox->add_field(array(
			'name' => __('Opt-In Scripts', 'cookiefox'),
			'desc' => __('These scripts will be executed when the user opts in to the use of cookies.', 'cookiefox'),
			'id' => 'scripts_consent',
			'type' => 'textarea_code'
		));

		$post_type_cookie_metabox->add_field(array(
			'name' => __('Opt-Out Scripts', 'cookiefox'),
			'desc' => __('These scripts will be executed when the user declines the use of cookies.', 'cookiefox'),
			'id' => 'scripts_no_consent',
			'type' => 'textarea_code'
		));

		$post_type_cookie_metabox->add_field(array(
			'name' => __('Always-On Scripts', 'cookiefox'),
			'desc' => __('These scripts will always be executed.', 'cookiefox'),
			'id' => 'scripts_always',
			'type' => 'textarea_code'
		));

		$post_type_cookie_metabox->add_field(array(
			'name' => __('Cookies', 'cookiefox'),
			'desc' => __('These cookies will be removed after opt-out. Seperate multiple cookies by a comma.', 'cookiefox'),
			'id' => 'cookies',
			'type' => 'text'
		));
		
		$post_type_cookie_metabox->add_field(array(
			'name' => __('Cookie Duration', 'cookiefox'),
			'desc' => __('The duration will be displayed in the detail view.', 'cookiefox'),
			'id' => 'duration',
			'type' => 'text',
		));

		$taxonomy_metabox = new_cmb2_box( array(
			'id' => 'cookiefox_category',
			'title' => esc_html__( 'Category Metabox', 'cmb2' ),
			'object_types' => array( 'term' ),
			'taxonomies' => array( 'cookiefox_category' ), 
			'priority' => 'high',
			'new_term_section' => false,
			'attributes' => array(
				'classes' => 'cookiefox-mb'
			)
		));

		$taxonomy_metabox->add_field(array(
			'name' => esc_html__('Always On', 'cookiefox'),
			'desc' => __('If activated, cookies in this category are always enabled.', 'cookiefox'),
			'id' => 'always_on',
			'type' => 'toggle',
		));		
	
		$taxonomy_metabox->add_field(array(
			'name' => __('Order', 'cookiefox'),
			'id' => 'order',
			'type' => 'text',
			'attributes' => array(
				'type' => 'number',
				'pattern' => '\d*',
			),
			'sanitization_cb' => 'absint',
		  'escape_cb'=> 'absint',	
	    'column' => array(
	      'position' => 4,
		    'name' => __('Order', 'cookiefox'),
	    ),
		));

		$taxonomy_metabox->add_field(array(
			'name' => __('Integrations', 'cookiefox'),
			'type' => 'title',
			'id' => 'title_integrations'
		));
		
		$taxonomy_metabox->add_field(array(
			'name' => esc_html__('Unblock Embedded Content', 'cookiefox'),
			'desc' => __('If activated, embeds will be unblocked when this category is enabled.', 'cookiefox'),
			'id' => 'unblock_embeds',
			'type' => 'toggle',
		));		

		
	}
	
	public function use_block_editor($enabled, $post_type) {
    return $post_type === "cookiefox_cookie" ? false : $enabled;
	}
		
	public function pre_get_posts($query) {
		if(!is_admin()){
			return $query;
		}
		
		if(!$query->is_main_query()){
			return $query;
		}
		
		if($query->get('post_type') != "cookiefox_cookie"){
			return $query;
		}
		
    if($query->get('orderby') == ''){
      $query->set('orderby', 'menu_order');
      $query->set('order', 'asc');
			$_GET["orderby"] = "menu_order";
			$_GET["order"] = "asc";
    }
	}

	public function admin_order_taxonomy() {
		global $pagenow;
		if(!empty($pagenow) && $pagenow === 'edit-tags.php'){
			if(!isset($_GET["orderby"]) || isset($_GET["orderby"]) && $_GET["orderby"] == "order"){
				$_GET["orderby"] = "order";
				add_filter('terms_clauses', array($this, 'set_taxonomy_order'), 10, 3 );
			}
		}
	}
			
	public function set_taxonomy_order($pieces, $taxonomies, $args) {
		global $wpdb;
		
		$join_statement = " LEFT JOIN $wpdb->termmeta AS term_meta ON t.term_id = term_meta.term_id AND term_meta.meta_key = 'order'";
		
		if ( ! strstr( $pieces['join'], $join_statement ) !== false) {
			$pieces['join'] .= $join_statement;
		}
		
		$pieces['orderby'] = 'ORDER BY CAST( term_meta.meta_value AS UNSIGNED )';
    
		return $pieces;
	}
	
	public function default_hidden_meta_boxes($hidden, $screen) {
	  if($screen->base == 'post' && $screen->id == 'cookiefox_cookie'){
			if(in_array("slugdiv", $hidden)){
				$hidden = array_diff($hidden, array("slugdiv"));
			}
	  }

	  return $hidden;
	}
	
	public function manage_posts_columns($columns) {	
		return array_merge(array_slice($columns, 0, 3), array('menu_order' => __("Order", "cookiefox")), array_slice($columns, 3, null));
	}
	
	public function manage_posts_custom_column($column_name, $post_id) {	
	  if ($column_name == 'menu_order') {
	     echo get_post($post_id)->menu_order;
	  }
	}
	
	public function manage_sortable_columns($columns) {	
	  $columns['menu_order'] = 'menu_order';
	  return $columns;
	}

	public function menu_highlight($parent_file) {
    if ($parent_file == "edit.php?post_type=cookiefox_cookie") {
      $parent_file = "options-general.php";
    }
    return $parent_file;
	}
	
	public function submenu_highlight($submenu_file, $parent_file){
    if ($submenu_file == "edit.php?post_type=cookiefox_cookie" || $submenu_file == "edit-tags.php?taxonomy=cookiefox_category&amp;post_type=cookiefox_cookie") {
      $submenu_file = "cookiefox";
    }
    return $submenu_file;
	}
	
	public function tabs() {
		$screen = get_current_screen();
		$screens = array(
			"cookiefox_cookie",
			"edit-cookiefox_cookie",
			"edit-cookiefox_category",
			"settings_page_cookiefox",
		);

		if(!in_array($screen->id, $screens)){
			return;	
		}
		?>
	<div class="wrap cmb2-options-page">
		<h2 class="nav-tab-wrapper">
				<a class="nav-tab <?php if($screen->id == "edit-cookiefox_cookie" || $screen->id == "cookiefox_cookie"): ?>nav-tab-active<?php endif; ?>" href="<?php echo admin_url("edit.php?post_type=cookiefox_cookie"); ?>">Cookies</a>
				<a class="nav-tab <?php if($screen->id == "edit-cookiefox_category"): ?>nav-tab-active<?php endif; ?>" href="<?php echo admin_url("edit-tags.php?taxonomy=cookiefox_category&post_type=cookiefox_cookie"); ?>"><?php _e("Categories", "cookiefox"); ?></a>
				<a class="nav-tab <?php if($screen->id == "settings_page_cookiefox"): ?>nav-tab-active<?php endif; ?> " href="<?php echo admin_url("options-general.php?page=cookiefox"); ?>"><?php _e("Settings", "cookiefox"); ?></a>
		</h2>
	</div>

		<?php
	}
	
	public function admin_head() {
	?>
	<style>
		.post-type-cookiefox_cookie .misc-pub-visibility{
			display: none;
		}
	</style>
	<?php
	}
	
}
new Post_Type();
