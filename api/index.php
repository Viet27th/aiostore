<?php
/**
 * About third parameter of register_rest_route function:
 * It's an array include these properties
 * * methods: 
 * To define the method of this api is GET or POST etc.
 * 
 * * callback: 
 * Function will be execute, this function take one parameter type as $request.
 * 
 * * args: 
 * Set "args" for $request parameter of "callback".
 * 
 * * permission_callback: 
 * It's a function know as middleware for this "route", it's take one parameter type
 * as $request and it also is $request of "callback" so you can edit $request at this place
 * You can do something like as $request->set_param("customer_id", $user->id); to add more
 * properties to $request params and you can found them
 * by $request->get_params() in "callback". You can see the data have just added combine
 * with old params sent from client(original params) or maybe override original params
 * If this function return false, a WP_Error will be sent to the client with
 * "code" is "rest_forbidden" and "Message" is "Sorry, you are not allowed to do that."
 * and "status" is 403.
 * If it return true or other value, the "callback" will be execute.
 * 
 * References:
 * 
 * * To create my endpoint:
 * https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
 * 
 * * To extends class Woocommerce
 * https://woocommerce.github.io/code-reference/
 */
require_once("VPE-User.api.php");
require_once("VPE-Order.api.php");