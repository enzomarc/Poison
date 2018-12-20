<?php

namespace Poison;


class Poison
{

    private static $viewsPath;

    /**
     * @var array Vars globally accessible for all views
     */
    private $globals = [];

    /**
     * Poison constructor.
     * @param string $path Path to the views directory
     */
    public function __construct(string $path)
    {
        if (!is_null($path))
            self::$viewsPath = $path;
    }

    /**
     * Set views path
     * @param string $path Path to the views directory
     */
    public static function setViewsPath(string $path): void
    {
        if (!is_null($path))
            self::$viewsPath = $path;
    }

    /**
     * Render a view
     * @param string $view View to render
     * @param array $params Parameters to pass to the view
     */
    public function render(string $view, array $params = []): void
    {
        $path = self::$viewsPath . DIRECTORY_SEPARATOR . $view . '.php';
         ob_start();
         $poison = $this;
         extract($this->globals);
         extract($params);
         require($path);
         echo ob_get_clean();
    }

    /**
     * Add global variable
     * @param string $key Key of the variable to add
     * @param $value Value of the variable to add
     */
    public function addGlobal(string $key, $value): void
    {
        $this->globals[$key] = $value;
    }

}