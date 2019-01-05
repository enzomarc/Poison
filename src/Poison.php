<?php

namespace App;

require_once 'helpers.php';

class Poison
{

    private static $viewsPath;

    private static $cachePath;

    private static $viewExtension = '.poison.php';

    /* Instance properties */

    private $views_path;

    private $cache_path;

    private $view_extension = '.poison.php';

    /**
     * @var array Vars globally accessible for all views
     */
    private $globals = [];

    /**
     * Poison constructor.
     * @param string $path Path to the views directory
     */
    public function __construct(string $path, string $cache)
    {
        if (!is_null($path))
            $this->views_path = $path;
        else
            $this->views_path = self::$viewsPath;

        if (!is_null($cache))
            $this->cache_path = $cache;
        else
            $this->cache_path = self::$cachePath;
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
     * Set cache path
     * @param string $path Path to the cache directory
     */
    public static function setCachePath(string $path): void
    {
        if (!is_null($path))
            self::$cachePath = $path;
    }

    /**
     * Change global views extension
     * @param string $extension View extension in the format ".extension"
     */
    public static function setViewExtension(string $extension): void
    {
        if (!is_null($extension))
            self::$viewExtension = $extension;
    }

    /**
     * Change the instance views extension
     * @param string $extension View extension in the format ".extension"
     */
    public function setExtension(string $extension): void
    {
        if (!is_null($extension))
            $this->view_extension = $extension;
    }

    /**
     * Clear the views cache folder
     */
    private function clearCache(): void
    {
        $files = scandir(self::$cachePath);

        if (count($files) >= 8) {
            foreach ($files as $file) {
                if ($file != '.' && $file != '..')
                    unlink(self::$cachePath . DIRECTORY_SEPARATOR . $file);
            }
        }
    }

    private function parseTag(string $tag): string
    {
        $real = $tag;

        if (startsWith($tag, '@include')) {
            $real = str_replace("@include", "<?= \$poison->include", $tag);
            $real = $real . '; ?>';
        }

        if (startsWith($tag, '{{') && endsWith($tag, '}}')) {
            $real = str_replace("{{", "<?=", $tag);
            $real = str_replace("}}", ";?>", $real);
        }

        if (startsWith($tag, '@url')) {
            $real = str_replace("@url", "<?= \$router->url", $tag);
            $real = str_replace(")", "); ?>", $real);
        }

        if(startsWith($tag, '@foreach')) {
            $real = str_replace("@foreach", "<?php foreach", $tag);
            $real = $real . ": ?>";
        }

        if(startsWith($tag, '@endforeach')) {
            $real = str_replace("@endforeach", "<?php endforeach; ?>", $tag);
        }

        if(startsWith($tag, '@if')) {
            $real = str_replace("@if", "<?php if", $tag);
            $real = $real . ": ?>";
        }

        if(startsWith($tag, '@else')) {
            $real = str_replace("@else", "<?php else: ?>", $tag);
        }

        if(startsWith($tag, '@elseif')) {
            $real = str_replace("@elseif", "<?php elseif", $tag);
            $real = $real . ": ?>";
        }

        if(startsWith($tag, '@endif')) {
            $real = str_replace("@endif", "<?php endif; ?>", $tag);
        }

        if (startsWith($tag, '{?')) {
            $real = str_replace("{?", "<?php", $tag);
        }

        if (startsWith($tag, '?}')) {
            $real = str_replace("?}", "?>", $tag);
        }

        if (startsWith($tag, '@extend')) {
            $real = str_replace('@extend', '', $tag);
            $real = str_replace('(', '[', $real);
            $real = str_replace(')', ']', $real);
            $real = "<?php \$extension=" . $real;
            $real = $real . "; ?>";
            $real = str_replace("\"", "'", $real);
        }

        if (startsWith($tag, '@content')) {
            $real = str_replace("@content", "<?php ob_start(); ?>", $tag);
        }

        if (startsWith($tag, '@endcontent')) {
            $real = str_replace("@endcontent", "<?php \$extension[1]['content'] = ob_get_clean(); \$poison->render(\$extension[0], \$extension[1]); ?>", $tag);
        }

        return $real;
    }

    /**
     * Create a formatted PHP file and store it to the cache folder
     * @param string $contents Unformatted PHP Contents
     * @param array $params Parameters to include in the view
     * @return string Formatted output for the web browser
     */
    private function parseTemplate(string $contents, array $params = []): string
    {

        preg_match_all("#(\{[@?!])(.*){1,}([@?!]\})|(\@\w+)([\(]*(.+)+?[\)]*)|(\{{2}(.*){1,}\}{2})|(\{[@?!]+)|([@?!]+\})#imxU", $contents, $matches);

        if (count($matches) > 0)
        {

            $tags = $matches[0];

            foreach ($tags as $tag):

                $real = $this->parseTag($tag);

                $contents = str_replace($tag, $real, $contents);

            endforeach;

            $cachedFile = $this->cache_path . DIRECTORY_SEPARATOR . uniqid("poison.") . '.php';

            file_put_contents($cachedFile, $contents);

            ob_start();

            extract($this->globals);
            extract($params);
            require($cachedFile);

            return ob_get_clean();

        } else {
            return $contents;
        }

    }

    /**
     * Render a view
     * @param string $view View to render
     * @param array $params Parameters to include in the view
     */
    public function render(string $view, array $params = []): void
    {
        $this->clearCache();

        $view = str_replace('.', DIRECTORY_SEPARATOR, $view);
        $path = $this->views_path . DIRECTORY_SEPARATOR . $view . $this->view_extension;

        if (file_exists($path)) {
            $output = $this->parseTemplate(file_get_contents($path), $params);
            echo $output;
        } else {
            throw new \Exception("View [{$view}] not found.");
        }
    }

    /**
     * Add global variable
     * @param string $key Key of the variable to add
     * @param $value Value of the variable to add
     */
    public function addGlobal(string $key, $value)
    {
        $this->globals[$key] = $value;

        return $this;
    }

    /**
     * Include a file to the view to render
     * @param string $file File to include in the final ouput
     */
    public function include(string $file): void
    {
        $file = str_replace('.', DIRECTORY_SEPARATOR, $file);
        $path = $this->views_path . DIRECTORY_SEPARATOR . $file . $this->view_extension;

        if (file_exists($path)) {
            $output = $this->parseTemplate(file_get_contents($path));
            echo $output;
        } else {
            throw new \Exception("File [{$file}] not found.");
        }
    }

}