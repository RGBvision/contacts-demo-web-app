<?php

class Router
{

    static private Controller $controller;
    static private string $method = 'index';
    protected static ?Router $instance = null;

    public static function init(): ?Router
    {
        if (!isset(self::$instance)) {
            self::$instance = new Router();
        }

        return self::$instance;
    }

    public function __construct()
    {
        // Удален код получения модуля и его контроллера по роуту, т.к. фактически используется только единственный модуль App.
        // Получение метода по роуту.
        self::$method = str_replace('/', '_', trim(Request::getPath(), '/')) ?: self::$method;
    }

    public static function execute(): void
    {

        if (strpos(self::$method, '__') === 0) {
            Response::output(['error' => i18n::_('router.error.magic_method')], 405);
        }

        // Удален код получения модуля по роуту, т.к. фактически используется только единственный модуль App.
        // Контроллер вшит хардкодом.

        // Класс контроллера
        $controller = 'AppController';
        $file = BASE_DIR . "/app/$controller.php";

        if (is_file($file)) {
            Loader::addClass($controller, $file);
            self::$controller = new $controller();
        } else {
            Response::output(['error' => i18n::_('router.error.controller')], 501);
        }

        $reflection = null;

        try {
            $reflection = new ReflectionClass($controller);
        } catch (ReflectionException $e) {
            Response::output(['error' => sprintf(i18n::_('router.error.runtime'), $e->getMessage())], 501);
        }

        try {

            if ($reflection && $reflection->hasMethod(self::$method)) {

                $arguments = [];
                $reflectionMethod = new ReflectionMethod($controller, self::$method);

                foreach ($reflectionMethod->getParameters() as $parameter) {

                    $_parameter = Request::request($parameter->name);

                    if ($_parameter !== null) {

                        if ($parameter->hasType()) {

                            $parameterType = $parameter->getType();
                            assert($parameterType instanceof ReflectionNamedType);

                            if ($parameterType->isBuiltin()) {

                                if (in_array($parameterType->getName(), ['int', 'integer'])) {
                                    $strictInt = filter_var($_parameter, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
                                    if ($strictInt === null) {
                                        Response::output(['error' => sprintf(i18n::_('router.error.parameter_type'), $parameter->name, $parameterType->getName())], 400);
                                    }
                                    $_parameter = $strictInt;
                                }

                                if (in_array($parameterType->getName(), ['float', 'double'])) {
                                    $strictFloat = filter_var($_parameter, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
                                    if ($strictFloat === null) {
                                        Response::output(['error' => sprintf(i18n::_('router.error.parameter_type'), $parameter->name, $parameterType->getName())], 400);
                                    }
                                    $_parameter = $strictFloat;
                                }

                                if (in_array($parameterType->getName(), ['bool', 'boolean'])) {
                                    $strictBool = filter_var($_parameter, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                                    if ($strictBool === null) {
                                        Response::output(['error' => sprintf(i18n::_('router.error.parameter_type'), $parameter->name, $parameterType->getName())], 400);
                                    }
                                    $_parameter = $strictBool;
                                }

                                if (!is_array($_parameter) && $parameterType->getName() === 'array') {
                                    Response::output(['error' => sprintf(i18n::_('router.error.parameter_type'), $parameter->name, $parameterType->getName())], 400);
                                }

                                settype($_parameter, $parameterType->getName());
                            }
                        }

                        $arguments[$parameter->name] = $_parameter;

                    } else if (!$parameter->isOptional()) {

                        Response::output(['error' => sprintf(i18n::_('router.error.required_parameter'), $parameter->name)], 400);

                    }

                    unset($_parameter);

                }

                // Вызов функции контроллера
                call_user_func_array([$controller, self::$method], $arguments);

            }

        } catch (ReflectionException $e) {

            Response::output(['error' => sprintf(i18n::_('router.error.runtime'), $e->getMessage())], 500);

        }

        Response::output(['error' => i18n::_('router.error.method')], 500);
    }

}
