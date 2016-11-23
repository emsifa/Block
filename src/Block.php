<?php

namespace Emsifa;

class Block
{
    
    const PARENT_REPLACER = '<!--block::parent-->';
    const NAMESPACE_SEPARATOR = '::';

    /**
     * @var string $extend
     */
    protected static $extend;

    /**
     * @var string $extension
     */
    protected static $extension = 'php';

    /**
     * @var array $blocks
     */
    protected static $blocks = [];
    
    /**
     * Started blocks
     * @var array $starteds
     */
    protected static $starteds = [];
    
    /**
     * @var array $directory_namespaces
     */
    protected static $directory_namespaces = [];

    /**
     * Set directory and namespace
     *
     * @param string $directory
     * @param string $namespace
     */
    public static function setDirectory($directory, $namespace = '')
    {
        static::$directory_namespaces[trim($namespace)] = $directory;
    }

    /**
     * Get directory
     *
     * @param string $directory
     * @param string $namespace
     */
    public static function getDirectory($namespace = '')
    {
        $namespace = trim($namespace);
        return array_key_exists($namespace, static::$directory_namespaces) ? static::$directory_namespaces[$namespace] : __DIR__;
    }

    /**
     * Set view extension
     *
     * @param string $extension
     */
    public static function setViewExtension($extension)
    {
        static::$extension = $extension;
    }

    /**
     * Get view extension
     *
     * @return string $extension
     */
    public static function getViewExtension()
    {
        return static::$extension;
    }

    /**
     * Check existance view file
     *
     * @param string $view
     */
    public static function has($view)
    {
        $path = static::resolvePath($view);
        return is_file($path);
    }

    /**
     * Render a view
     *
     * @param string $view
     * @param array $__data
     *
     * @return string render result
     */
    public static function render($view, array $__data = array())
    {
        static::makeSureViewExists($view);
        $view_path = static::resolvePath($view);

        extract($__data);
        $get = static::makeGetter($__data);

        ob_start();
        include($view_path);
        $result = ob_get_clean();

        if (static::$extend) {
            $view = static::$extend;
            static::$extend = '';
            $result = static::render($view, $__data);
        }

        return $result;
    }

    /**
     * Include another view in a view
     *
     * @param string $view
     * @param array $__data
     */
    public static function insert($view, array $__data = array())
    {
        static::makeSureViewExists($view);
        $path = static::resolvePath($view);

        extract($__data);
        $get = static::makeGetter($__data);
        include($path);
    }

    /**
     * Extending a view
     *
     * @param string $view
     */
    public static function extend($view)
    {
        static::$extend = $view;
    }

    /**
     * Starting section
     *
     * @param string $block_name
     */
    public static function start($block_name)
    {
        static::$starteds[] = $block_name;
        ob_start();
    }

    /**
     * Alias of static::PARENT_REPLACER
     */
    public static function parent()
    {
        return static::PARENT_REPLACER;
    }

    /**
     * Closing section
     *
     * @param string $block_name
     */
    public static function stop()
    {
        $block_name = array_pop(static::$starteds);
        if (!array_key_exists($block_name, static::$blocks)) {
            static::$blocks[$block_name] = [];
        }
        static::$blocks[$block_name][] = ob_get_clean();
    }

    /**
     * Close and printing section
     */
    public static function show()
    {
        $block_name = static::$starteds[count(static::$starteds)-1];
        static::stop();
        echo static::get($block_name);
    }

    /**
     * Get section
     *
     * @param string $block_name
     *
     * @return string
     */
    public static function get($block_name)
    {
        $stacks = array_key_exists($block_name, static::$blocks)? static::$blocks[$block_name] : null;
        return $stacks? static::renderStacks($stacks) : '';
    }

    /**
     * Render block stacks
     *
     * @param array $stacks
     *
     * @return string
     */
    protected static function renderStacks(array $stacks)
    {
        $current = array_pop($stacks);
        if (count($stacks)) {
            return str_replace(static::PARENT_REPLACER, $current, static::renderStacks($stacks));
        } else {
            return $current;
        }
    }

    /**
     * Resolve view directory
     *
     * @param string $view
     *
     * @return string view directory
     */
    protected static function resolvePath($view)
    {
        $view = str_replace('.', '/', $view);
        $expl = explode(static::NAMESPACE_SEPARATOR, $view);
        list($namespace, $view_path) = (count($expl) > 1) ? $expl : ['', $expl[0]];

        $path = static::getDirectory($namespace) . '/' . $view_path . '.' . static::getViewExtension();
        return $path;
    }

    protected static function makeSureViewExists($view)
    {
        if (!static::has($view)) {
            throw new \Exception("View {$view} is not exists");
        }
    }

    protected static function makeGetter(array $data)
    {
        return function($key, $default = null) use ($data) {
            return isset($data[$key])? $data[$key] : $default;
        };
    }

}
