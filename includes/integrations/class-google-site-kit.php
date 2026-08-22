<?php
/**
 * Integration: Site Kit by Google.
 *
 * @package CookieFox
 */

namespace CookieFox\Integrations;

defined('ABSPATH') || exit;

class Google_Site_Kit extends Integration {
	private $slug = 'google-site-kit';
	private $done_analytics = false;
	private $done_adsense = false;
	private $done_tagmanager = false;

	public function __construct() {
		add_filter('cookiefox_integrations', array($this, 'add_integration'));

		add_filter("cookiefox_consent_{$this->slug}--adsense", array($this, 'consent_adsense'));
		add_action("cookiefox_integration_{$this->slug}--adsense", array($this, 'do_integration_adsense'));

		add_filter("cookiefox_consent_{$this->slug}--analytics", array($this, 'consent_analytics'));
		add_action("cookiefox_integration_{$this->slug}--analytics", array($this, 'do_integration_analytics'));

		add_filter("cookiefox_consent_{$this->slug}--tagmanager", array($this, 'consent_tagmanager'));
		add_action("cookiefox_integration_{$this->slug}--tagmanager", array($this, 'do_integration_tagmanager'));
	}

	public function add_integration($integrations) {
		if (!is_array($integrations)) {
			$integrations = array();
		}

		$integrations["{$this->slug}--analytics"] = 'Google Site Kit - Analytics';
		$integrations["{$this->slug}--tagmanager"] = 'Google Site Kit - Tag Manager';
		$integrations["{$this->slug}--adsense"] = 'Google Site Kit - AdSense';

		return $integrations;
	}

	public function consent_adsense() {
		$selector = 'script[data-block-on-consent][src*="pagead2.googlesyndication.com"]';
		return '<script>if(window.cookiefox&&window.cookiefox.api&&window.cookiefox.api.activateBlockedScripts){window.cookiefox.api.activateBlockedScripts(' . wp_json_encode($selector) . ');}</script>';
	}

	public function do_integration_adsense() {
		if ($this->done_adsense) {
			return;
		}

		add_filter('googlesitekit_adsense_tag_block_on_consent', '__return_true');
		$this->done_adsense = true;
	}

	public function consent_analytics() {
		$selector = 'script[data-block-on-consent][src*="googletagmanager.com/gtag"]';
		$slug = $this->slug . '--analytics';

		return '<script>if(window.cookiefox&&window.cookiefox.api){if(window.cookiefox.api.activateBlockedScripts){window.cookiefox.api.activateBlockedScripts(' . wp_json_encode($selector) . ');}if(window.cookiefox.api.activateIntegration){window.cookiefox.api.activateIntegration(' . wp_json_encode($slug) . ');}}</script>';
	}

	public function do_integration_analytics() {
		if ($this->done_analytics) {
			return;
		}

		add_filter('googlesitekit_analytics-4_tag_block_on_consent', '__return_true');
		add_filter('googlesitekit_analytics_tag_block_on_consent', '__return_true');
		add_filter('script_loader_tag', array($this, 'analytics_script_loader_tag'), 10, 2);
		$this->done_analytics = true;
	}

	public function analytics_script_loader_tag($tag, $handle) {
		if (!is_string($tag)) {
			return $tag;
		}

		if (strpos($tag, 'data-block-on-consent') !== false || strpos($tag, 'data-cookiefox-integration') !== false) {
			return $tag;
		}

		$is_gtag_handle = ($handle === 'google_gtagjs' || $handle === 'googlesitekit-gtag');
		$is_gtag_src = (strpos($tag, 'googletagmanager.com/gtag') !== false);

		if (!$is_gtag_handle && !$is_gtag_src) {
			return $tag;
		}

		if (!$is_gtag_src && strpos($tag, 'src=') === false) {
			return $tag;
		}

		return preg_replace('/<script\b/i', '<script type="text/plain" data-cookiefox-integration="' . esc_attr($this->slug . '--analytics') . '"', $tag, 1);
	}

	public function consent_tagmanager() {
		return '<script>(function(){if(!window.cookiefox||!window.cookiefox.api||!window.cookiefox.api.cloneElement){return;}document.querySelectorAll("script[data-block-on-consent]").forEach(function(script){var src=script.getAttribute("src")||"";var text=script.textContent||"";if(src.indexOf("googletagmanager.com/gtm")===-1&&text.indexOf("googletagmanager.com/gtm.js")===-1){return;}var el=window.cookiefox.api.cloneElement(script);el.removeAttribute("data-block-on-consent");if(el.getAttribute("type")==="text/plain"){el.removeAttribute("type");}if(script.parentNode){script.parentNode.insertBefore(el,script);script.remove();}});})();</script>';
	}

	public function do_integration_tagmanager() {
		if ($this->done_tagmanager) {
			return;
		}

		add_filter('googlesitekit_tagmanager_tag_block_on_consent', '__return_true');
		$this->done_tagmanager = true;
	}
}
new Google_Site_Kit();
