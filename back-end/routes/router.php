<?php namespace Routes;

require_once 'controllers/userController.php';
require_once 'controllers/homeController.php';
require_once 'controllers/detailController.php';
require_once 'controllers/bioskopController.php';
require_once 'controllers/transactionController.php';
require_once 'controllers/reviewController.php';


class Router
{
    public function route($controller, $action, $params, $connection)
    {
        $controller_class_name = "\\Controllers\\" . ucfirst($controller) . 'Controller';
        // Instantiate object with error handling
        try {
            if (class_exists($controller_class_name)) {
                $object = new $controller_class_name();
                // Call object's method.
                if (method_exists($object, $action)) {
                    $object->$action($connection, $params);
                } else {
                    throw new \Exception('Action not found!');
                }
            } else {
                    throw new \Exception('Class not found');
            }
        } catch (\Exception $e) {
            echo $e;
        }
    }
}
