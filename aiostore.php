<?php
/**
 * Plugin Name:       Aiostore
 * Author:            27th.
 * Text Domain:       aiostore
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Plugin for custom api
 * Version:           0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 */

/**
 * References: 
 * https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/
 * https://developer.wordpress.org/plugins/hooks/actions/
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!defined("VPE_NONCE")) {
  define("VPE_NONCE", "wp_rest");
}
if (!defined("VPE_URL_PREFIX")) {
  define("VPE_URL_PREFIX", "aio/v1");
}
if (!defined("JWT_SECRET")) {
  define("JWT_SECRET", "example_key"); // The secret for HMAC algorithms
}

if (!class_exists('Vpe')) :

  class Vpe {
    /** @var string The plugin version number. */
    var $version = '0.0';

    /** @var array The plugin data array. */
    var $data = array();

    /**
     * __construct
     *
     * @date	27/02/2021
     * @since	0.0
     *
     * @param	void
     * @return	void
     */
    function __construct() {
      register_activation_hook(__FILE__, array($this, 'check_environment'));

      // It make sure that Always run this plugin after Woocommerce Plugin so we can re-use WC class
      add_action('woocommerce_init', array($this, "initialize"));
    }

    /**
     * Sets up plugin.
     *
     * @date	1/03/2021
     * @since	0.0
     *
     * @param	void
     * @return	void
     */
    function initialize() {
      // Define constants.
      $this->define('VPE_BASE_URL', get_bloginfo('url')); // https://localhost:8443/{project_name}
      $this->define('VPE', 'VPE');
      $this->define('VPE_PATH', plugin_dir_path(__FILE__)); // /opt/lampp/htdocs/{project_name}/wp-content/plugins/{plugin_name}/
      $this->define('VPE_BASENAME', plugin_basename(__FILE__)); // {plugin_name}/{plugin_main_file}.php
      $this->define('VPE_DIRECTORY_URL_PATH', plugin_dir_url(__FILE__)); // https://localhost:8443/{project_name}/wp-content/plugins/{plugin_name}/
      $this->define('VPE_DIRECTORY_NAME_FILE', dirname(__FILE__)); // /opt/lampp/htdocs/{project_name}/wp-content/plugins/{plugin_name}
      $this->define('VPE_DIRECTORY_NAME_DIRECTORY', dirname(__DIR__) . '/'); // /opt/lampp/htdocs/{project_name}/wp-content/plugins/
      $this->define('VPE_VERSION', $this->version);

      require_once(__DIR__ . "/vendor/autoload.php"); // Auto load package from "vendor" folder which created by "composer" package management of PHP
      require_once("helpers/index.php");
      VPE_Common_Helper::include("api/index.php");

      add_action("admin_menu", array($this, "func_add_admin_menu"));
    }

    /**
     * This action is used to add extra submenus and menu options
     * to the admin panel’s menu structure
     * 
     * @date	1/3/2021
     * @since	0.0
     * 
     * @return void
     */
    function func_add_admin_menu() {
      add_menu_page(
        'AIO Store', // Page title
        'AIO Store', // Memu option title
        'manage_options', // capability
        constant('VPE_PATH') . 'menu-views/setting.view.php', // PHP file path as the $menu_slug
        null, // callable as null
        'dashicons-database', // it's dash icon name, icon will be shown on the left sidebar
        3, // 3 is position of dashboard in admin menu, this plugin will appear after dashboard
      );

      add_submenu_page(
        constant('VPE_PATH') . 'menu-views/setting.view.php', // specify parent_slug
        'AIO Store', // Page title
        'AIO Store', // Memu option title
        'manage_options', // capability
        constant('VPE_PATH') . 'menu-views/setting.view.php', // PHP file path as the $menu_slug
      );
    }

    /**
     * Defines a constant if doesnt already exist.
     * After defined, call "constant(string_name)" or just "string_name" to get value.
     * Constants can be accessed regardless of scope.
     * 
     * @date	1/3/2021
     * @since 0.0
     *
     * @param	string $name The constant name.
     * @param	mixed $value The constant value.
     * @return	void
     */
    function define($name, $value = true) {
      if (!defined($name)) {
        define($name, $value);
      }
    }

    /**
     * Check environment before run main function of plugin.
     * 
     * @date	1/3/2021
     * @since 0.0
     * 
     * @return	void
     */
    function check_environment() {
      if (
        !is_plugin_active('woocommerce/woocommerce.php') and
        current_user_can('activate_plugins')
      ) {
        // Stop activation redirect and show error
        $this->deactivate_plugin();
        wp_die(
          'Sorry, but this plugin requires the Woocommerce plugin to be installed and active. <br>
          <a href="' . admin_url('plugins.php') . '">&laquo; Return to Plugins</a>'
        );
      }
    }

    /**
     * Deactivates the plugin.
     *
     * @date	1/3/2021
     * @since 0.0
     */
    function deactivate_plugin() {
      deactivate_plugins(constant('VPE_BASENAME'));
    }

    /**
     * Returns data or null if doesn't exist.
     *
     * @date	1/3/2021
     * @since	0.0
     *
     * @param	string $name The data name.
     * @return	mixed
     */
    function get_data($name) {
      return isset($this->data[$name]) ? $this->data[$name] : null;
    }

    /**
     * Sets data for the given name and value.
     *
     * @date	1/3/2021
     * @since	0.0
     *
     * @param	string $name The data name.
     * @param	mixed $value The data value.
     * @return	void
     */
    function set_data($name, $value) {
      $this->data[$name] = $value;
    }
  }

  function vpe() {
    // Instantiate only once.
    if (!defined('VPE')) {
      $aio = new Vpe();
    }
  }

  // Instantiate.
  vpe();

endif;
