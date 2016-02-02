<?php

namespace Genetsis\core;

/**
 * Manages OAuth configuration file.
 *
 * @package   Genetsis
 * @category  Helper
 * @version   1.0
 * @access    private
 */
class OauthTemplate
{
    /** @var array Var to load into template. */
    protected $vars = array();
    /** @var string The template file. */
    private $tpl_file = '';

    /**
     * Set template path and template file
     *
     * @param $template
     * @throws /Exception if template file not exist
     */
    public function __construct($template)
    {
        $this->tpl_file = $template;
    }

    /**
     * Render template file with vars passed
     *
     * @return mixed|string Html rendered
     */
    public function render()
    {
        $html = $this->tpl_file;
        $html = str_replace("'", "\'", $html);
        $html = preg_replace('#\{([a-z0-9\-_]*?)\}#is', "' . $\\1 . '", $html);
        reset($this->vars);
        while (list($key, $val) = each($this->vars)) {
            $$key = $val;
        }
        eval("\$html = '$html';");
        reset($this->vars);
        while (list($key, $val) = each($this->vars)) {
            unset($$key);
        }
        $html = str_replace("\'", "'", $html);
        return $html;
    }

    public function __get($name)
    {
        return $this->vars[$name];
    }

    public function __set($name, $value)
    {
        $this->vars[$name] = $value;
    }
}