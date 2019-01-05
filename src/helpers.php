<?php

/**
 * Render a view
 * @param string $view View to render
 * @param array $params Parameters to pass to the view
 */
function view(string $view, array $params = []): void
{
    if($GLOBALS['poison'] != null)
        $GLOBALS['poison']->render($view, $params);
}

function dd($expression)
{
    echo '<pre>';
    var_dump($expression);
    echo '</pre>';
    die();
}

function startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}
