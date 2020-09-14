<?php

namespace thejoshsmith\prismsyntaxhighlighting\services;

use Craft;
use craft\base\Component;
use thejoshsmith\prismsyntaxhighlighting\Plugin;

/**
 * Prism Syntax Highlighting
 *
 * @link      https://www.joshsmith.dev
 * @copyright Copyright (c) 2019 Josh Smith
 */
class PrismSyntaxHighlighting extends Component
{
	/**
	 * Define the config file that holds the language definitions
	 * @var string
	 */
    const CONFIG_FILENAME = 'prismsyntaxhighlighting';

    /**
     * Define the languages required by the CraftCMS CP
     * @var array
     */
    const CRAFTCMS_CP_LANGUAGES = ['markup', 'javascript', 'json'];

    /**
     * Define a cache key to hold the definitions
     * @var string
     */
    const DEFINITIONS_CACHE_KEY = 'prismSyntaxHighlighting:definitions';

    /**
     * Define definition types
     * @var  string
     */
    const DEFINITION_TYPE_CORE = 'core';
    const DEFINITION_TYPE_LANGUAGES = 'languages';
    const DEFINITION_TYPE_PLUGINS = 'plugins';
    const DEFINITION_TYPE_THEMES = 'themes';

    /**
     * The internal path to the plugin config file
     * @var string
     */
    public static $configFile = '@thejoshsmith/prismsyntaxhighlighting/config/'.self::CONFIG_FILENAME.'.php';

    /**
     * Path to the Prism components definition file
     * @var string
     */
	public static $componentsDefinitionFile = '@thejoshsmith/prismsyntaxhighlighting/assetbundles/prismsyntaxhighlighting/dist/js/prism/components.json';

    /**
     * Cache to hold the loaded definitions
     * @var object
     */
    protected $prismDefinitions;

	/**
	 * Caches lang configs
	 * @var array
	 */
	protected $config = [];

    /**
     * Returns a single prism definition
     * @see getDefinitions
     * @author Josh Smith <josh@batch.nz>
     * @param  string $type     Type of definition to return
     * @param  array  $config   An array of config filters
     * @return object
     */
    public function getDefinition(string $handle, string $type = '')
    {
        if( empty($type) ) return null;

        $definitions = $this->getPrismDefinitions($type);

        return empty($definitions->$handle) ? null : $definitions->$handle;
    }

    /**
     * Returns a theme definition
     * @author Josh Smith <josh@batch.nz>
     * @param  string $handle Definition
     * @return object
     */
    public function getThemeDefinition(string $handle)
    {
        return $this->getDefinition($handle, self::DEFINITION_TYPE_THEMES);
    }

    /**
     * Returns a theme definition
     * @author Josh Smith <josh@batch.nz>
     * @param  string $handle Definition
     * @return object
     */
    public function getLanguageDefinition(string $handle)
    {
        return $this->getDefinition($handle, self::DEFINITION_TYPE_LANGUAGES);
    }

    /**
     * Returns a theme definition
     * @author Josh Smith <josh@batch.nz>
     * @param  string $handle Definition
     * @return object
     */
    public function getPluginDefinition(string $handle)
    {
        return $this->getDefinition($handle, self::DEFINITION_TYPE_PLUGINS);
    }

    /**
     * Returns parsed Prism definitions for the given type
     *
     * Allowed types are:
     *     - 'core'
     *     - 'themes'
     *     - 'languages'
     *     - 'plugins'
     *
     * Data is returned in the following format:
     *     {{Internal Definition}} => {{Display Title}}
     *
     * @author Josh Smith <me@joshsmith.dev>
     * @param  string $type     Type of definition to return
     * @param  array  $config   An array of config filters
     * @return array            An array of Prism definitions
     */
    public function getDefinitions(string $type = '', array $config = []): array
    {
        if( empty($type) ) return [];

        $config = empty($config) ? $this->getConfig($type) : $config;
        $definitions = $this->getPrismDefinitions($type);

        return $this->parseDefinitions($config, $definitions);
    }

    /**
     * Returns the prism syntax highlighting config
     * Optionally returns a segment of the config, specified by a key
     * User config settings are automatically merged into the default config
     *
     * @author Josh Smith <me@joshsmith.dev>
     * @param  string $key Key of a segment to return
     * @return array       Config array
     */
    public function getConfig(string $key = '')
    {
    	if( empty($this->config) ){
			$defaultConfig = $this->loadDefaultConfig();
			$userConfig = $this->loadUserConfig();
			$this->config = array_merge($defaultConfig, $userConfig);
    	}

        if( empty($key) ) return $this->config;
        if( array_key_exists($key, $this->config) ) return $this->config[$key];

        return [];
    }

    /**
     * Returns the prism JSON definitions
     * @author Josh Smith <me@joshsmith.dev>
     * @return Object
     */
    public function getPrismDefinitions(string $key = '')
    {
        $this->prismDefinitions = Craft::$app->getCache()->getOrSet(self::DEFINITIONS_CACHE_KEY, function() {
            return $this->loadPrismDefinitions();
        });

        if( empty($key) ) return $this->prismDefinitions;
        if( property_exists($this->prismDefinitions, $key) ) return $this->prismDefinitions->{$key};

        return [];
    }

    /**
     * Parses custom theme definitions, added via a user config file
     * All we do here is strip out wildcard definitions, and auto add a title if not specified
     *
     * @author Josh Smith <me@joshsmith.dev>
     * @param  array  $definitions An array of theme definitions
     * @return array
     */
    public function parseCustomThemeDefinitions(array $definitions = []): array
    {
        // Auto set the title if only a handle is defined
        foreach ($definitions as $name => $title) {

            if( $title === '*' ) unset($definitions[$name]); // Remove the all parameter

            if( is_numeric($name) && $title !== '*' ){ // Ignore the All parameter
                unset($definitions[$name]);
                $definitions[$title] = ucwords(implode(' ', explode('-', $title)));
            }
        }

        return $definitions;
    }

    /**
     * Returns definition requirements from the Prism components file
     * Recursively loads all dependencies for each definition (if any)
     *
     * @author Josh Smith <me@joshsmith.dev>
     * @param  string $definition   A prims JS definition
     * @param  array  &$definitions An array of currently loaded requirements
     * @return array                An array of definition requirements
     */
    public function getDefinitionRequirements(string $definition, string $type, array &$definitions = []): array
    {
        $prismDefinition = $this->getDefinition($definition, $type);
        $requirements = $prismDefinition->require ?? '';

        // Add the definition to the array
        $definitions[] = $definition;

        if( empty($requirements) ) return $definitions;
        if( is_string($requirements) ) $requirements = explode(',', $requirements);

        // Recursively parse out other requirements
        foreach ($requirements as $requirement) {
            $this->getDefinitionRequirements($requirement, $type, $definitions);
        }

        // Load dependencies in reverse order
        return array_reverse($definitions);
    }

    /**
     * Returns language definition requirements from the Prism components file
     * Recursively loads all JS dependencies for each language (if any)
     *
     * @author Josh Smith <me@joshsmith.dev>
     * @param  string $definition   A language definition
     * @param  array  &$definitions An array of currently loaded requirements
     * @return array                An array of definition requirements
     */
    public function getLanguageDefinitionRequirements(string $definition = '', array &$definitions = []): array
    {
        return $this->getDefinitionRequirements($definition, self::DEFINITION_TYPE_LANGUAGES, $definitions);
    }

    /**
     * Returns theme definition requirements from the Prism components file
     * Recursively loads all CSS dependencies for each theme (if any)
     *
     * @author Josh Smith <me@joshsmith.dev>
     * @param  string $definition   A theme definition
     * @param  array  &$definitions An array of currently loaded requirements
     * @return array                An array of definition requirements
     */
    public function getThemeDefinitionRequirements(string $definition = '', array &$definitions = []): array
    {
        return $this->getDefinitionRequirements($definition, self::DEFINITION_TYPE_THEMES, $definitions);
    }

    /**
     * Returns plugin definition requirements from the Prism components file
     * Recursively loads all dependencies for each plugin (if any)
     *
     * @author Josh Smith <me@joshsmith.dev>
     * @param  string $definition   A plugin definition
     * @param  array  &$definitions An array of currently loaded requirements
     * @return array                An array of definition requirements
     */
    public function getPluginDefinitionRequirements(string $definition = '', array &$definitions = []): array
    {
        return $this->getDefinitionRequirements($definition, self::DEFINITION_TYPE_PLUGINS, $definitions);
    }

    /**
     * Returns a decoded components JSON object
     * @author Josh Smith <me@joshsmith.dev>
     * @return object
     */
    protected function loadPrismDefinitions()
    {
        return json_decode(file_get_contents(Craft::getAlias(self::$componentsDefinitionFile)));
    }

    /**
     * Loads the default plugin config
     * This should probably be loaded by the plugin class, but it's only really used here for now
     *
     * @author Josh Smith <me@joshsmith.dev>
     * @return array
     */
    protected function loadDefaultConfig(): array
    {
    	return require Craft::getAlias(self::$configFile);
    }

    /**
     * Loads a user config override sitting in CraftCMS
     * @author Josh Smith <me@joshsmith.dev>
     * @return array
     */
    protected function loadUserConfig(): array
    {
    	$configFile = CRAFT_BASE_PATH.'/config/'.self::CONFIG_FILENAME.'.php';

    	if( file_exists($configFile) ){
    		return require $configFile;
    	}

    	return [];
    }

    /**
     * Parses out definitions from the Prism components JSON object
     * Typically this will just parse out a title
     *
     * @author Josh Smith <me@joshsmith.dev>
     * @param  array  $config
     * @param  object $themeDefinitions
     * @return array
     */
    protected function parseDefinitions(array $config, $themeDefinitions): array
    {
        // Remove the meta property, it's not required.
        unset($themeDefinitions->meta);

        $definitions = [];
        foreach ($config as $value) {
            if( $value === '*' ){ // Load all definitions
                $definitions = (array) $themeDefinitions;
                break;
            } else if( !empty($themeDefinitions->{$value}) ){
                $definitions[$value] = $themeDefinitions->{$value};
            }
        }

        return $this->_parseDefinitions($definitions);
    }

    /**
     * Parses out definition titles
     * @author Josh Smith <me@joshsmith.dev>
     * @param  array  $definitions
     * @return array
     */
    private function _parseDefinitions(array $definitions = []): array
    {
        $parsedDefinitions = [];
        foreach ($definitions as $key => $value) {
            if( is_string($value) ){
                $parsedDefinitions[$key] = $value;
            }
            else if( is_object($value) && !empty($value->title) ){
                $parsedDefinitions[$key] = $value->title;
            } else {
                throw new \Exception('Definition is missing a title.');
            }
        }

        return $parsedDefinitions;
    }
}
