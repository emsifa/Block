<?php

namespace Emsifa;

class Block
{

    const PARENT_REPLACER = '<!--block::parent-->';
    const NAMESPACE_SEPARATOR = '::';

    const APPEND = 'append';
    const PREPEND = 'prepend';

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
     * @var array $shared_data
     */
    protected $shared_data = [];

    /**
     * Render variables
     * @var array $render_data
     */
    protected $render_data = [];

    /**
     * Extend data
     * @var array $extend_data
     */
    protected $extend_data = [];

    /**
     * Component data
     * @var array $components
     */
    protected $components = [];

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
        $this->shared_data[$key] = $value;
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
        $this->render_data = $__data;

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
            $result = $this->render($view, array_merge($__data, $this->extend_data));
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
     * Alias of insert
     *
     * @param string $view
     * @param array $__data
     */
    public function put($view, array $__data = array())
    {
        return $this->insert($view, $__data);
    }

    /**
     * Extending a view
     *
     * @param string $view
     */
    public function extend($view, array $data = [])
    {
        $this->extend = $view;
        $this->extend_data = $data;
    }

    /**
     * Starting section
     *
     * @param string $block_name
     */
    public function section($block_name, $type = null)
    {
        $this->sections[] = ['name' => $block_name, 'type' => $type];
        ob_start();
        if ($type === static::APPEND) {
            echo $this->parent();
        }
    }

    /**
     * Append section
     *
     * @param string $block_name
     */
    public function append($block_name)
    {
        $this->section($block_name, static::APPEND);
    }

    /**
     * Append section
     *
     * @param string $block_name
     */
    public function prepend($block_name)
    {
        $this->section($block_name, static::PREPEND);
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
        $block = array_pop($this->sections);

        if (!array_key_exists($block['name'], $this->blocks)) {
            $this->blocks[$block['name']] = [];
        }

        if ($block['type'] === static::PREPEND) {
            echo $this->parent();
        }

        $this->blocks[$block['name']][] = ob_get_clean();
    }

    /**
     * Close and printing section
     */
    public function show()
    {
        $block = $this->sections[count($this->sections)-1];
        $this->stop();
        echo $this->get($block['name']);
    }

    /**
     * Open component
     *
     * @param string $view
     * @param array $data
     */
    public function component($view, array $data = array())
    {
        $this->components[] = [
            'view' => $view,
            'data' => $data,
            'slots' => []
        ];

        ob_start();
    }

    /**
     * Close and render component
     *
     * @param string $view
     * @param array $data
     */
    public function endcomponent()
    {
        $component = array_pop($this->components);
        if (!$component) {
            throw new \Exception("No active component in this block. Make sure you have open component using 'component' method.", 1);
        }

        $component['data']['slot'] = ob_get_clean();
        return $this->insert($component['view'], $component['data']);
    }

    /**
     * Open slot
     *
     * @param string $slot_name
     */
    public function slot($slot_name)
    {
        $count_components = count($this->components);
        if (0 === $count_components) {
            throw new \Exception("Slot can only used inside component definition.", 1);
        }

        $index = $count_components - 1;
        $this->components[$index]['slots'][] = $slot_name;

        ob_start();
    }

    /**
     * Close slot
     */
    public function endslot()
    {
        $count_components = count($this->components);
        if (0 === $count_components) {
            throw new \Exception("Slot can only used inside component definition.", 1);
        }

        $index = $count_components - 1;
        $slot_name = array_pop($this->components[$index]['slots']);
        if (!$slot_name) {
            throw new \Exception("No active slot in this block. Make sure you have open slot using 'slot' method.", 1);
        }

        $this->components[$index]['data'][$slot_name] = ob_get_clean();
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
        $data = array_merge($this->shared_data, $this->render_data, $data);
        $composers = isset($this->composers[$view]) ? $this->composers[$view] : [];
        foreach($composers as $composer) {
            $data = array_merge($data, (array) call_user_func_array($composer, [$data, $view]));
        }

        return $data;
    }

}
