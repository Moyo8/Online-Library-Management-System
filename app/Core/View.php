<?php
namespace App\Core;

/**
 * View class for rendering templates
 */
class View {
    protected $data = [];

    /**
     * Assign data to view
     * @param string $key Data key
     * @param mixed $value Data value
     */
    public function assign($key, $value) {
        $this->data[$key] = $value;
    }

    /**
     * Render a template
     * @param string $template Template name (without .php)
     * @param string $layout Layout template (optional)
     */
    public function render($template, $layout = null) {
        // Extract data to variables
        extract($this->data);

        // Start output buffering
        ob_start();

        // Include template file
        $templateFile = APP . 'View/' . $template . '.php';
        if (file_exists($templateFile)) {
            require $templateFile;
        } else {
            // Template not found - this should not happen in production
            return;
        }

        // Get content
        $content = ob_get_clean();

        // If layout specified, render layout with content
        if ($layout !== null) {
            $this->assign('content', $content);
            $layoutFile = APP . 'View/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                require $layoutFile;
            } else {
                // Layout not found - fallback to just showing content
                echo $content;
            }
        } else {
            echo $content;
        }
    }
}
?>