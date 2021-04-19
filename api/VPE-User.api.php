<?php
require_once( __DIR__ . '/VPE-Base.api.php');

class VPE_User_Api {
  /**
   * Endpoint namespace
   *
   * @var string
   */
  protected $namespace = '/user';

  protected $VPE_Base_Api_Instance;

  /**
   * Register all routes releated with stores
   *
   * @return void
   */
  public function __construct()
  {
    $this->VPE_Base_Api_Instance = new VPE_Base_Api();
    add_action('rest_api_init', array($this, "register_endpoints"));
  }

  public function register_endpoints() {
    register_rest_route(VPE_URL_PREFIX . $this->namespace, '/traditional-register-for-customer', array(
      'methods' => 'POST',
      'callback' => array($this, "func_traditional_register_for_customer"),
      'args' => array(),
      'permission_callback' => function (WP_REST_Request $request) {
        return true;
      },
    ));

    register_rest_route(VPE_URL_PREFIX . $this->namespace, '/traditional-login', array(
      'methods' => 'POST',
      'callback' => array($this, "func_traditional_login"),
      'args' => array(),
      'permission_callback' => function (WP_REST_Request $request) {
        return true;
      },
    ));
  }

  public function func_traditional_register_for_customer(WP_REST_Request $request) {
    $data = $request->get_params();
    $user_info = [
      "user_login" => isset($data["userName"]) ? $data["userName"] : "",
      "user_pass" => isset($data["password"]) ? $data["password"] : "",
      "user_email" => isset($data["email"]) ? sanitize_email($data["email"]) : "",
      "role" => "customer"
    ];
    $error_message = "";

    if(!$user_info["user_login"]) {
      $error_message .= "User name isn't empty. ";
    }
    if(!$user_info["user_pass"]) {
      $error_message .= "Password isn't empty. ";
    }
    if(!$user_info["user_email"]) {
      $error_message .= "Email isn't empty. ";
    }

    if($error_message) {
      return $this->VPE_Base_Api_Instance->sendError("register_error", $error_message);
    }

    if(!is_email($user_info["user_email"])) {
      return $this->VPE_Base_Api_Instance->sendError("register_error", "Email invalid");
    }

    // Validate password strength
    $uppercase = preg_match('@[A-Z]@', $user_info["user_pass"]);
    $lowercase = preg_match('@[a-z]@', $user_info["user_pass"]);
    $number    = preg_match('@[0-9]@', $user_info["user_pass"]);
    $specialChars = preg_match('@[^\w]@', $user_info["user_pass"]);
    if(strlen($user_info["user_pass"]) < 6) {
      return $this->VPE_Base_Api_Instance->sendError("register_error", "Password should be at least 6 characters in length.");
    }
   
    // wp_insert_user function default validate duplicate user name and email
    $result = wp_insert_user($user_info);
    if(is_wp_error($result)) {
      return $result;
    } else {
      return $this->VPE_Base_Api_Instance->sendSuccess(true);
    }
  }

  public function func_traditional_login(WP_REST_Request $request) {
    $user_info = [
      "user_login" => $request->get_param("userName"), // User's username or email address.
      "user_pass" => $request->get_param("password")
    ];
    $error_message = "";

    if(!$user_info["user_login"]) {
      $error_message .= "User name isn't empty. ";
    }
    if(!$user_info["user_pass"]) {
      $error_message .= "Password isn't empty. ";
    }

    if($error_message) {
      return $this->VPE_Base_Api_Instance->sendError("register_error", $error_message);
    }

    $user_data = wp_authenticate($user_info["user_login"], $user_info["user_pass"]);
    if(is_wp_error($user_data)) {
      return $user_data;
    } else {
      $user_data = (array) $user_data->data;

      $api = new WC_REST_Customers_Controller();
      $req = new WP_REST_Request('GET');
      $req->set_query_params(["id" => $user_data["ID"]]);
      $response = $api->get_item($req);

      if(is_wp_error($response)) {
        return $response;
      } else {
        $user = (array)$response->data;
        $jwt = VPE_Jwt_Helper::create_jwt_token($user);
        if(is_wp_error($jwt)) {
          return $jwt;
        } else {
          return $this->VPE_Base_Api_Instance->sendSuccess($jwt);
        }
      }
    }
  }
}

new VPE_User_Api();
