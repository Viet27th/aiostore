<?php
use \Firebase\JWT\JWT;

class VPE_Jwt_Helper {
  /**
   * @param array $user  
   * @return string|WP_Error
   */
  public static function create_jwt_token(array $user)  {
    $secret_key = defined('JWT_SECRET') ? JWT_SECRET : false;

    /** First thing, check the secret key if not exist return a error*/
    if (!$secret_key) {
      return new WP_Error(
          "jwt_auth_bad_config",
          "JWT is not configurated properly, please contact the admin"
      );
    }
    $payload = array();
    $issuedAt = time();
    $expire = $issuedAt + (365 * 24 * 60 * 60); // second
    $payload["iss"] = get_bloginfo('url');
    $payload["iat"] = $issuedAt;
    $payload["exp"] = $expire;
    $payload["user"] = $user;
    $jwt = JWT::encode($payload, $secret_key);
    return $jwt;
  }

  /**
   * @param string $jwt  
   * @return array|WP_Error
   */
  public static function decoded_jwt_token(string $jwt) {
    $secret_key = defined('JWT_SECRET') ? JWT_SECRET : false;

    /** First thing, check the secret key if not exist return a error*/
    if (!$secret_key) {
      return new WP_Error(
          "jwt_auth_bad_config",
          "JWT is not configurated properly, please contact the admin"
      );
    }

    try {
      $decoded = JWT::decode($jwt, $secret_key, array('HS256'));
      /** The Token is decoded now validate the iss */
      if ($decoded->iss != get_bloginfo('url')) {
        /** The iss do not match, return error */
        return new WP_Error(
            'jwt_auth_bad_iss',
            'The iss do not match with this server'
        );
      }

      return (array)$decoded;
    } catch (Exception $e) {
      /** Something is wrong trying to decode the token, send back the error */
      return new WP_Error('jwt_auth_invalid_token', $e->getMessage());
    }
  }
}