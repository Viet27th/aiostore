<?php
require_once( __DIR__ . '/VPE-Base.api.php');

class VPE_Order_Api extends WC_REST_Orders_Controller {
  /**
   * Endpoint namespace
   *
   * @var string
   */
  protected $namespace = '/order';

  protected $VPE_Base_Api_Instance;

  /**
   * Register all routes releated with stores 
   *
   * @return void
   */
  public function __construct() {
    $this->VPE_Base_Api_Instance = new VPE_Base_Api();
    add_action('rest_api_init', array($this, 'register_endpoints'));
  }

  public function register_endpoints() {
    register_rest_route(VPE_URL_PREFIX . $this->namespace,  '/create', array(
      array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => array($this, 'create_item'),
        'permission_callback' => array($this, 'permissions_check_create_order'),
      ),
      'schema' => array( $this, 'get_public_item_schema' ),
    ));

    register_rest_route(VPE_URL_PREFIX . $this->namespace, '/update-order-by-id' . '/(?P<id>[\d]+)', array(
      array(
        'methods'             => "PUT",
        'callback'            => array($this, 'update_order_by_id'),
        'permission_callback' => array($this, 'permissions_check_update_order'),
      ),
      'schema' => array( $this, 'get_public_item_schema' ),
    ));

    register_rest_route(VPE_URL_PREFIX . $this->namespace,  '/get-all-orders', array(
      'methods'             => WP_REST_Server::READABLE,
      'callback'            => array($this, 'get_all_orders'),
      'permission_callback' => array($this->VPE_Base_Api_Instance, "permissionCheck"),
    ));

    register_rest_route(VPE_URL_PREFIX . $this->namespace, '/get-order-by-id' . '/(?P<id>[\d]+)', array(
      array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => array($this, 'get_order_by_id'),
        'permission_callback' => array($this->VPE_Base_Api_Instance, "permissionCheck"),
      ),
      'schema' => array( $this, 'get_public_item_schema' ),
    ));

    register_rest_route(VPE_URL_PREFIX . $this->namespace, '/delete-order-by-id' . '/(?P<id>[\d]+)', array(
      'methods'             => WP_REST_Server::DELETABLE,
      'callback'            => array($this, "delete_order_by_id"),
      'permission_callback' => array($this->VPE_Base_Api_Instance, "permissionCheck"),
    ));
  }

  public function permissions_check_create_order(WP_REST_Request $request): bool {
    $request = $this->VPE_Base_Api_Instance::permissionCheck($request);
    if(!$request) {
      return false;
    } else {
      // Set "customer_id" for "create_item" function of Woocommerce to know that who is creating an order. 
      $request->set_param("customer_id", $request->current_user_id);
      return true;
    }
  }

  public function permissions_check_update_order(WP_REST_Request $request): bool {
    $request = $this->VPE_Base_Api_Instance::permissionCheck($request);
    if(!$request) {
      return false;
    } else {
      // Set "customer_id" for "create_item" function of Woocommerce to know that who is creating an order. 
      $request->set_param("customer_id", $request->current_user_id);
      return true;
    }
  }

  public function update_order_by_id(WP_REST_Request $request) {
    $current_order = $this->get_order_by_id($request);
    if(is_wp_error($current_order)) {
      return $current_order;
    } else {
      $current_user_id = $request->current_user_id;
      $order_info = $current_order->data["data"];
      if($current_user_id === $order_info["customer_id"]) {
        $deleted_order = $this->update_item($request);
        if(is_wp_error($deleted_order)) {
          return $deleted_order;
        } else {
          return $this->VPE_Base_Api_Instance::sendSuccess($deleted_order->data);
        }
      } else {
        return $this->VPE_Base_Api_Instance::sendError("order_error", "You don't have permission to delete this order.");
      }
    }
  }

  public function get_all_orders(WP_REST_Request $request) {
    $data = wc_get_orders(array(
      "customer_id" => $request->current_user_id,
      // 'type'        => 'shop_order',
      // 'limit'       => - 1,
      // 'status'      => "",
    ));
    $orders = [];
    foreach ($data as $order) {
      $order = $order->get_data();
      array_push($orders, $order);
    }
    return $this->VPE_Base_Api_Instance::sendSuccess($orders);
  }

  public function get_order_by_id(WP_REST_Request $request) {
    $current_customer_id = $request->current_user_id;
    $order_id = $request->get_param("id");

    $req = new WP_REST_Request('GET');
    $req->set_query_params(["id" => $order_id]);
    $response = $this->get_item($req);

    if(is_wp_error($response)) {
      return $response;
    } else {
      if($response->data["customer_id"] === $current_customer_id) {
        return $this->VPE_Base_Api_Instance::sendSuccess($response->data);
      } else {
        return $this->VPE_Base_Api_Instance::sendError("order_error", "You don't have permission to get this order.");
      }
    }
  }

  public function delete_order_by_id(WP_REST_Request $request) {
    $current_order = $this->get_order_by_id($request);
    if(is_wp_error($current_order)) {
      return $current_order;
    } else {
      $current_user_id = $request->current_user_id;
      $order_info = $current_order->data["data"];
      if($current_user_id === $order_info["customer_id"]) {
        $deleted_order = $this->delete_item($request);
        if(is_wp_error($deleted_order)) {
          return $deleted_order;
        } else {
          return $this->VPE_Base_Api_Instance::sendSuccess($deleted_order->data);
        }
      } else {
        return $this->VPE_Base_Api_Instance::sendError("order_error", "You don't have permission to delete this order.");
      }
    }
  }
}
new VPE_Order_Api();
