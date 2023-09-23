<?php
/**
 * Sample_Content.
 *
 * @package CookieFox
 */

namespace CookieFox;

defined('ABSPATH') || exit;

class Sample_Content {
	private $terms;
	private $posts;
	
	public function __construct() {
		add_action("admin_init", array($this, "init"));
		add_action("admin_notices", array($this, "admin_notices"));			
	}
	public function getPosts() {
		return array(
			array(
				"post_status" => "publish",
				"post_title" => __("Sample Cookie", "cookiefox"),
				"post_type" => "cookiefox_cookie",
				"post_name" => "sample-cookie",
				"meta_input" => array(
					"vendor" => "Cookiefox",
					"description" => __("This is a sample cookie installed by the CookieFox plugin", "cookiefox"),
					"privacy_policy" => "https://sample.org/privacy-policy/",
					"scripts_prioritize" => "off",
					"scripts_consent" => "<script>console.log('cookiefox consent');</script>",
					"scripts_no_consent" => "<script>console.log('cookiefox no consent');</script>",
					"scripts_always" => "<script>console.log('cookiefox always');</script>",
					"cookies" => "cookiefox_sample,cookiefox_sample2",
					"duration" => "1 year",
				),
				"tax_input" => array("cookiefox_category" => array("marketing")),
			)
		);
	}
	
	public function getTerms() {
		return array(
			array(
				"name" => __("Functional", "cookiefox"),
				"slug" => "functional",
				"description" => __("Essential cookies that are required for the website to function.", "cookiefox"),
				"order" => 0,
				"always_on" => "on",
				"unblock_embeds" => "off"
			),
			array(
				"name" => __("Statistics", "cookiefox"),
				"slug" => "statistics",
				"description" => __("Statisticial cookies track user behaviour and are used to collect information anonymously. This helps us to understand how our visitors use this website.", "cookiefox"),
				"order" => 1,
				"always_on" => "off",
				"unblock_embeds" => "off"
			),
			array(
				"name" => __("Marketing", "cookiefox"),
				"slug" => "marketing",
				"description" => __("Marketing cookies are used by third-party advertisers and publishers to display personalized ads and support online marketing. They do this by tracking visitors across websites.", "cookiefox"),
				"order" => 2,
				"always_on" => "off",
				"unblock_embeds" => "off"
			),
			array(
				"name" => __("External Media", "cookiefox"),
				"slug" => "external-media",
				"description" => __("External content from video and social media platforms can track visitors across websites when they are embedded.", "cookiefox"),
				"order" => 3,
				"always_on" => "off",
				"unblock_embeds" => "on"
			)
		);
	}
	
	public function init() {
    if (isset($_GET['action']) && isset($_GET['_wpnonce']) && $_GET['action'] === 'cookiefox_sample_content' && wp_verify_nonce($_GET['_wpnonce'], 'cookiefox-install-sample-content' ) ) {
			
			$terms = $this->getTerms();
			
			foreach ($terms as $term) {
				if (term_exists($term["slug"], "cookiefox_category")) {
					continue;
				}
				
				$result = wp_insert_term($term["name"], "cookiefox_category", $term);
				if (!empty($result)) {
					add_term_meta($result["term_id"], "order", $term["order"], true);
					add_term_meta($result["term_id"], "always_on", $term["always_on"], true);
					add_term_meta($result["term_id"], "unblock_embeds", $term["unblock_embeds"], true);
				}
			}
			
			$posts = $this->getPosts();
			
			foreach ($posts as $post) {
				if (post_exists($post["post_title"])) {
					continue;
				}
		
				wp_insert_post($post, true);
			}
			
			wp_redirect(admin_url("options-general.php?page=cookiefox&cookiefox_sample_content_created=true"));
			exit;
		}
	}
	
	public function admin_notices() {
    if(!isset($_GET['cookiefox_sample_content_created'])) {
			return;
		}
    ?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e( 'The sample content has been created. Check it out by visiting the Cookies and Categories sections.', 'cookiefox' ); ?></p>
    </div>
    <?php
	}
}
new Sample_Content();
