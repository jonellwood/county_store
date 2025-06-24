<?php
// Created: 2025/03/13 12:02:32
// Last modified: 2025/04/10 13:03:29
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../rootConfig.php';


//include_once APP_ROOT . '/auth/UserAuth.php';
include_once APP_ROOT . '/classes/Logger.php';

// Logger::logError(APP_ROOT . '/auth/UserAuth.php');

// Logger::logError('isLoggedIn: ' . $isLoggedIn);
// if (!$isLoggedIn) {
//     header("Location: /auth/index.php");
// } else {
//     return;
// }

class Layout
{

    // Store template variables
    private array $variables = [];

    // Store content sections (main, sidebar, etc.)
    private array $sections = [];

    // Default layout template
    private string $layoutTemplate = APP_ROOT . '/templates/layouts/default.php';

    private Assets $assets;

    /**
     * Constructor allows setting a specific layout template
     */
    public function __construct(string $layoutTemplate = null)
    {
        if ($layoutTemplate !== null) {
            $this->layoutTemplate = $layoutTemplate;
        }

        // Initialize assets with default files
        $this->assets = new Assets();
        $this->assets->addCss('/style/reset.css')
            ->addCss('/style/custom.css')
            ->addCss('/style/theme.css');

        // Add assets to variables so templates can access them
        $this->variables['assets'] = $this->assets;
    }

    // Add a function to check if user is logged in 
    public static function confirmLoggedIn(): bool
    {
        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != 1) {
            Logger::logAuth('Unknown Russian Hackers attempted access while not logged in!' . json_encode($_SESSION));
            return false;
        } else {
            return true;
        }
    }

    // Add a getter method for assets
    public function getAssets(): Assets
    {
        return $this->assets;
    }

    /**
     * Set a template variable
     */
    public function setVar(string $name, $value): self
    {
        $this->variables[$name] = $value;
        return $this;
    }

    /**
     * Set multiple template variables at once
     */
    public function setVars(array $variables): self
    {
        foreach ($variables as $name => $value) {
            $this->variables[$name] = $value;
        }
        return $this;
    }

    /**
     * Capture content for a specific section
     */
    public function setSection(string $sectionName, string $filePath = null, array $sectionVars = []): self
    {
        // If no file is provided, return the object to allow for alternative content setting
        if ($filePath === null) {
            return $this;
        }

        // Merge global variables with section-specific variables, with section vars taking precedence
        $vars = array_merge($this->variables, $sectionVars);

        // Start output buffering to capture the section content
        ob_start();

        // Extract variables to make them available in the template
        extract($vars);

        // Include the file
        include $filePath;

        // Capture the buffered content
        $this->sections[$sectionName] = ob_get_clean();

        return $this;
    }

    /**
     * Set raw content for a section (instead of from a file)
     */
    public function setSectionContent(string $sectionName, string $content): self
    {
        $this->sections[$sectionName] = $content;
        return $this;
    }

    /**
     * Get section content (used in layout templates)
     */
    public function getSection(string $sectionName, string $default = ''): string
    {
        return $this->sections[$sectionName] ?? $default;
    }

    /**
     * Check if a section exists
     */
    public function hasSection(string $sectionName): bool
    {
        return isset($this->sections[$sectionName]);
    }

    /**
     * Render the complete page with the layout template
     */
    public function render(): void
    {
        // Extract variables to make them available in the template
        extract($this->variables);

        // Include the layout template
        include $this->layoutTemplate;
    }

    /**
     * Render a specific page template with the layout
     */
    public function renderPage(string $contentFile, array $pageVars = []): void
    {
        // Set the main content section from the content file
        $this->setSection('content', $contentFile, $pageVars);

        // Render the layout with all sections
        $this->render();
    }
}

/**
 * Helper class for managing assets (CSS, JS)
 */
class Assets
{
    private array $css = [];
    private array $js = [];

    /**
     * Add a CSS file
     */
    public function addCss(string $path, string $media = 'all'): self
    {
        $this->css[] = [
            'path' => $path,
            'media' => $media
        ];
        return $this;
    }

    /**
     * Add a JavaScript file
     */
    public function addJs(string $path, bool $defer = false): self
    {
        $this->js[] = [
            'path' => $path,
            'defer' => $defer
        ];
        return $this;
    }

    /**
     * Render all CSS tags
     */
    public function renderCss(): string
    {
        $output = '';
        foreach ($this->css as $css) {
            $output .= sprintf(
                '<link rel="stylesheet" href="%s" media="%s">' . PHP_EOL,
                htmlspecialchars($css['path']),
                htmlspecialchars($css['media'])
            );
        }
        return $output;
    }

    /**
     * Render all JavaScript tags
     */
    public function renderJs(): string
    {
        $output = '';
        foreach ($this->js as $js) {
            $defer = $js['defer'] ? ' defer' : '';
            $output .= sprintf(
                '<script src="%s"%s></script>' . PHP_EOL,
                htmlspecialchars($js['path']),
                $defer
            );
        }
        // print_r($output);
        return $output;
    }
}
