<?php

namespace thejoshsmith\prismsyntaxhighlighting\plugins;

use thejoshsmith\prismsyntaxhighlighting\plugins\AbstractPlugin;

class LineNumbers extends AbstractPlugin {

    public function getInputHtml()
    {

    }

    public function registerJs()
    {

    }

    public function registerCss()
    {

    }

    public function getPreClassesHook(array &$context)
    {
        return 'line-numbers';
    }
}
