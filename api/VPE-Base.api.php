<?php
class VPE_Base_Api {
    /**
     * Response an error for client
     * if error occurred on server or you're using this function to response an error for client,
     * the error body look like as below:
     * {
        "code" => "",
        "message" => "",
        "data"? => {status: 500}
     * }
     *
     * @param string $code - Error codes are slugs that are used to identify each error. They are mostly useful when a piece of code can produce several different errors, and you want to handle each of those errors differently.
     * @param string $message - Whatever you want.
     * @param number $statusCode - Whatever you want.
     * 
     * @return WP_REST_Request $request Current request.
     */
    public static function sendError( $code, $message ) {
        return new WP_Error($code, $message);
    }

    public static function sendSuccess( $data ) {
        return new WP_REST_Response(
            array(
                "code" => "success",
                "message" => "Success",
                "data" => $data,
            ), 200
        );
    }

    /**
     * 
     * @return false|WP_REST_Request
     */
    public static function permissionCheck(WP_REST_Request $request) {
        $token = $request->get_header("User-Token");

        if(isset($token) && $token != null) {
            $decoded = VPE_Jwt_Helper::decoded_jwt_token($token);
            if(!is_wp_error($decoded)) {
            $user = $decoded["user"];
            $request->current_user_id = $user->id;

            return $request;
            } else {
            return false;
            }
        } else {
            return false;
        }
    }
}