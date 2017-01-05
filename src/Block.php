<?php

namespace Emsifa;

class Block
{
    
    const PARENT_REPLACER = '<!--block::parent-->';
    const NAMESPACE_SEPARATOR = '::';

    /**
     * @var string $extend
     */
    protected $extend;

    /**
     * @var string $extension
     */
    protected $extension = 'php';

    /**
     * @var array $blocks
     */
    protected $blocks = [];
    
    /**
     * Started blocks
     * @var array $sections
     */
    protected $sections = [];
    
    /**
     * View composers
     * @var array $composers
     */
    protected $composers = [];

    /**
     * Shared variables
     * @var array $shared_vars
     */
    protected $shared_vars = [];
    
    /**
     * @var array $directory_namespaces
     */
    protected $directory_namespaces = [];

    public function __construct($directory, $extension = 'php')
    {
        $this->setDirectory($directory);
        $this->setViewExtension($extension);
    }

    /**
     * Set directory and namespace
     *
     * @param string $directory
     * @param string $namespace
     */
    public function setDirectory($directory, $namespace = '')
    {
        $this->directory_namespaces[trim($namespace)] = $directory;
    }

    /**
     * Get directory
     *
     * @param string $directory
     * @param string $namespace
     */
    public function getDirectory($namespace = '')
    {
        $namespace = trim($namespace);
        return array_key_exists($namespace, $this->directory_namespaces) ? $this->directory_namespaces[$namespace] : __DIR__;
    }

    /**
     * Set view extension
     *
     * @param string $extension
     */
    public function setViewExtension($extension)
    {
        $this->extension = $extension;
    }

    /**
     * Get view extension
     * @return string $extension
     */
    public function getViewExtension()
    {
        return $this->extension;
    }

    /**
     * Check existance view file
     *
     * @param string $view
     */
    public function has($view)
    {
        $path = $this->resolvePath($view);
        return is_file($path);
    }

    /**
     * Share variable to all views
     *
     * @param string $key
     * @param mixed $value
     */
    public function share($key, $value)
    {
        $this->shared_vars[$key] = $value;
    }

    /**
     * Register view composer
     *
     * @param string|array $views
     */
    public function composer($views, callable $composer)
    {
        $views = (array) $views;
        foreach($views as $view) {
            if (!isset($this->composers[$view])) {
                $this->composers[$view] = [];
            }

            $this->composers[$view][] = $composer;
        }
    }

    /**
     * Render a view
     *
     * @param string $view
     * @param array $__data
     * @return string render result
     */
    public function render($view, array $__data = array())
    {
        $__data = $this->resolveData($view, $__data);
        $this->makeSureViewExists($view);
        $view_path = $this->resolvePath($view);

        extract($__data);
        $get = $this->makeGetter($__data);
        $e = function($value) {
            return $this->escape($value);
        };

        ob_start();
        include($view_path);
        $result = ob_get_clean();

        if ($this->extend) {
            $view = $this->extend;
            $this->extend = '';
            $result = $this->render($view, $__data);
        }

        return $result;
    }

    /**
     * Include another view in a view
     *
     * @param string $view
     * @param array $__data
     */
    public function insert($view, array $__data = array())
    {
        $__data = $this->resolveData($view, $__data);
        $this->makeSureViewExists($view);
        $path = $this->resolvePath($view);

        extract($__data);
        $get = $this->makeGetter($__data);
        $e = function($value) {
            return $this->escape($value);
        };

        include($path);
    }

    /**
     * Extending a view
     *
     * @param string $view
     */
    public function extend($view)
    {
        $this->extend = $view;
    }

    /**
     * Starting section
     *
     * @param string $block_name
     */
    public function section($block_name)
    {
        $this->sections[] = $block_name;
        ob_start();
    }

    /**
     * Alias of static::PARENT_REPLACER
     */
    public function parent()
    {
        return static::PARENT_REPLACER;
    }

    /**
     * Closing section
     *
     * @param string $block_name
     */
    public function stop()
    {
        $block_name = array_pop($this->sections);
        if (!array_key_exists($block_name, $this->blocks)) {
            $this->blocks[$block_name] = [];
        }
        $this->blocks[$block_name][] = ob_get_clean();
    }

    /**
     * Close and printing section
     */
    public function show()
    {
        $block_name = $this->sections[count($this->sections)-1];
        $this->stop();
        echo $this->get($block_name);
    }

    /**
     * Get section
     *
     * @param string $block_name
     * @return string
     */
    public function get($block_name)
    {
        $stacks = array_key_exists($block_name, $this->blocks)? $this->blocks[$block_name] : null;
        return $stacks? $this->renderStacks($stacks) : '';
    }

    /**
     * Escaping html
     *
     * @param string $value
     * @return string
     */
    public function escape($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
    }

    /**
     * Render block stacks
     *
     * @param array $stacks
     * @return string
     */
    protected function renderStacks(array $stacks)
    {
        $current = array_pop($stacks);
        if (count($stacks)) {
            return str_replace(static::PARENT_REPLACER, $current, $this->renderStacks($stacks));
        } else {
            return $current;
        }
    }

    /**
     * Resolve view directory
     *
     * @param string $view
     * @return string view directory
     */
    protected function resolvePath($view)
    {
        $view = str_replace('.', '/', $view);
        $expl = explode(static::NAMESPACE_SEPARATOR, $view);
        list($namespace, $view_path) = (count($expl) > 1) ? $expl : ['', $expl[0]];

        $path = $this->getDirectory($namespace) . '/' . $view_path . '.' . $this->getViewExtension();
        return $path;
    }

    protected function makeSureViewExists($view)
    {
        if (!$this->has($view)) {
            throw new \Exception("View {$view} is not exists");
        }
    }

    protected function makeGetter(array $data)
    {
        return function($key, $default = null) use ($data) {
            if (array_key_exists($key, $data)) {
                return $data[$key];
            }
            foreach (explode('.', $key) as $segment) {
                if (is_array($data) && array_key_exists($segment, $data)) {
                    $data = $data[$segment];
                } else {
                    return $default;
                }
            }
            return $data;
        };
    }

    protected function resolveData($view, array $data)
    {
        $data = array_merge($this->shared_vars, $data);
        $composers = isset($this->composers[$view]) ? $this->composers[$view] : [];
        foreach($composers as $composer) {
            $data = array_merge($data, (array) call_user_func_array($composer, [$data, $view]));
        }   

        return $data;
    }

}
