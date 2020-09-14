<?php

namespace thejoshsmith\prismsyntaxhighlighting\plugins;

abstract class AbstractPlugin {

    public $handle = '';
    public $title = '';
    public $description = '';
    public $owner = '';
    public $noCSS = '';
    public $require = '';

    /**
     * Registers field input html with the twig template
     * @author Josh Smith <josh@batch.nz>
     * @return string
     */
    abstract public function getInputHtml();

    /**
     * Registers JS files with the view
     * @author Josh Smith <josh@batch.nz>
     * @return void
     */
    abstract public function registerJs();

    /**
     * Registers CSS files with the view
     * @author Josh Smith <josh@batch.nz>
     * @return void
     */
    abstract public function registerCss();

    /**
     * Constructor function
     * @author Josh Smith <josh@batch.nz>
     * @param  string $handle     Plugin handle
     * @param  array  $definition Plugin prism definition
     */
    public function __construct(string $handle, array $definition = [])
    {
        $this->handle = $handle;

        foreach ($definition as $key => $value) {
            if( property_exists($this, $key) ){
                $this->$key = $value;
            }
        }
    }

    public function getPreClassesHook(array &$context)
    {
        return '';
    }

    public function getCodeClassesHook(array &$context)
    {
        return '';
    }
}
