<?php
class OptionsAltoRouter extends AltoRouter {
  public function match($requestUrl = null, $requestMethod = null){
    $originalRequestMethod = $requestMethod;
    if($requestMethod == 'OPTIONS'){
      $requestMethod = $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'];
    }
    if($match = parent::match($requestUrl, $requestMethod)){
      $match['request_method'] = $originalRequestMethod;
    }
    return $match;
  }
}

$router = new OptionsAltoRouter();

$routes = [
    ['PUT|OPTIONS', '/auth/email/signup', 'AuthRequest#signupWithEmail'],
    ['POST|OPTIONS', '/auth/email/login', 'AuthRequest#loginWithEmail'],
    ['POST|OPTIONS', '/auth/email/exist', 'AuthRequest#emailExist'],
    ['POST|OPTIONS', '/auth/phone/exist', 'AuthRequest#phoneExist'],
    ['PATCH|OPTIONS', '/auth/new-password', 'AuthRequest#resetPassword'],
    ['POST|OPTIONS', '/auth/token/request/email', 'AuthRequest#requestEmailToken'],
    ['POST|OPTIONS', '/auth/token/verify', 'AuthRequest#verifyToken'],

    ['POST|OPTIONS', '/user/update/name', 'UserRequest#updateName'],

    ['GET|OPTIONS', '/bank/list', 'ServiceRequest#bankList'],
    ['POST|OPTIONS', '/bank/account/resolve', 'ServiceRequest#accountResolve'],
];

// Add the routes
$router->addRoutes($routes);
// match the request
$match = $router->match();

// if request don't match an route set in the array
if ($match === false) {
    // page not found
    app_false_response( 'Page not found', 404 );
} 
else {
    list( $controller, $action ) = explode( '#', $match['target'] );
    if ( is_callable( array( $controller, $action) ) ) {
        $controller = new $controller;
        call_user_func_array( 
            array( $controller, $action ), 
            array( $match['params'] ) 
        );
    } else {
        // here your routes are wrong.
        // Throw an exception in debug, send a  500 error in production
        app_log_error( 'Fail to call object on page request ' . $controller . '->' . $action );
        app_false_response( 'No class found', 500 );
    }
}