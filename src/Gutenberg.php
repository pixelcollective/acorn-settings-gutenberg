<?php

namespace TinyPixel\Settings;

use function \add_action;
use function \add_filter;
use function \add_theme_support;
use function \get_post_type_object;

use \WP_Post_Type;
use \Illuminate\Support\Collection;
use \Roots\Acorn\Application;

/**
 * Gutenberg
 *
 * @author  Kelly Mears <kelly@tinypixel.dev>
 * @license MIT
 * @since   0.0.1
 */
class Gutenberg
{
    /**
     * Construct
     *
     * @param \Roots\Acorn\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Initializes class
     *
     * @param \Illuminate\Support\Collection $config
     * @return \TinyPixel\Gutenberg\Gutenberg
     */
    public function init(Collection $config)
    {
        $this->settings = $this->deriveSettingsFromConfig($config);
        $this->setOptions($this->settings);

        add_action('init', [$this, 'setReusableBlockOptions']);

        return $this;
    }

    /**
     * Collects valid entries from configuration file
     *
     * @param  \Illuminate\Support\Collection $config
     * @return \Illuminate\Support\Collection $settings
     */
    public function deriveSettingsFromConfig(Collection $config)
    {
        $settings = collect();

        $config->each(function ($setting, $item) use ($settings) {
            if ($this->isValidSetting($setting)) {
                $settings->push([$item => $setting]);
            }
        });

        return $settings;
    }

    /**
     * Ensures config value is set and not null
     *
     * @param  var    $optionValue
     * @return binary
     */
    public function isValidSetting($optionValue)
    {
        if (isset($optionValue) && !is_null($optionValue)) {
            return true;
        }
    }

    /**
     * Set options from processed settings
     *
     * @param  \Illuminate\Support\Collection $settings
     * @return void
     */
    public function setOptions(Collection $settings)
    {
        if (isset($this->settings['disabled'])) {
            $this->disable();
        }

        if (isset($this->settings['colorPalette'])) {
            $this->setColorPalette();
        }

        if (isset($this->settings['fontSizes'])) {
            $this->setFontSizes();
        }

        if (isset($this->settings['useDefaultStyles'])) {
            $this->useDefaultStyles();
        }

        if (isset($this->settings['useDefaultStyles'])) {
            $this->useDefaultStyles();
        }

        if (isset($this->settings['supportEditorStyles'])
        && $this->settings['supportEditorStyles'] == true) {
            $this->supportEditorStyles();
        }

        if (isset($this->settings['supportDarkEditorStyles'])
        && $this->settings['supportDarkEditorStyles'] == true) {
            $this->supportDarkEditorStyles();
        }

        if (isset($this->settings['supportResponsiveEmbeds'])
        && $this->settings['supportResponsiveEmbeds'] == true) {
            $this->supportResponsiveEmbeds();
        }

        if (isset($this->settings['supportWideAlign'])) {
            $this->supportWideAlign();
        }

        if (isset($this->settings['disableCustomUserFontSizes'])) {
            $this->disableCustomFontSizes();
        }

        if (isset($this->settings['disableCustomUserColors'])) {
            $this->disableCustomColors();
        }
    }

    /**
     * Sets reusable block options
     *
     * @return void
     */
    public function setReusableBlockOptions()
    {
        // early exit if they aren't unlocked
        if (!isset($this->settings['unlockReusableBlocks'])
        || $this->settings['unlockReusableBlocks'] == null) {
            return;
        }

        $this->unlockPostType(\get_post_type_object('wp_block'), 'reusableBlocksType');

        if (isset($this->settings['reusableBlocksIcon'])) {
            $this->setReusableBlocksIcon($this->reusableBlocksType);
        }

        if (isset($this->settings['reusableBlocksLabels'])) {
            $this->setReusableBlocksLabels($this->reusableBlocksType);
        }

        if (isset($this->settings['reusableBlocksCapabilityType'])) {
            $this->modifyReusableBlocksCapabilityType(
                $this->reusableBlocksType,
                $settings['reusableBlocksCapabilityType']
            );
        }

        if (isset($this->settings['reusableBlocksCapabilities'])) {
            $this->modifyReusableBlocksCapabilities(
                $this->reusableBlocksType,
                $settings['reusableBlocksCapabilities']
            );
        }

        if (isset($this->settings['reusableBlocksUseGraphQL'])) {
            $this->setReusableBlocksToUseGraphQL($this->reusableBlocksType);
        }
    }

    /**
     * Adds theme support for color palettes
     *
     * @return void
     */
    public function setColorPalette()
    {
        add_theme_support('editor-color-palette', $this->settings['colorPalette']);
    }

    /**
     * Adds theme supports for font size options
     *
     * @return void
     */
    public function setFontSizes()
    {
        add_theme_support('editor-font-sizes', $this->settings['fontSizes']);
    }

    /**
     * Removes theme support for custom font sizes
     *
     * @return void
     */
    public function disableCustomFontSizes()
    {
        add_theme_support('disable-custom-font-sizes');
    }

    /**
     * Removes theme support for custom colors
     *
     * @return void
     */
    public function disableCustomColors()
    {
        add_theme_support('disable-custom-colors');
    }

    /**
     * Adds theme support for wide and full alignments
     *
     * @return void
     */
    public function supportWideAlign()
    {
        add_theme_support('align-wide');
    }

    /**
     * Adds theme supports for default block styles
     *
     * @return void
     */
    public function useDefaultStyles()
    {
        add_theme_support('wp-block-styles');
    }

    /**
     * Adds theme support for custom editor styles
     *
     * @return void
     */
    public function supportEditorStyles()
    {
        add_theme_support('editor-styles');
    }

    /**
     * Adds theme support for dark editor styling
     *
     * @return void
     */
    public function supportDarkEditorStyles()
    {
        add_theme_support('dark-editor-style');
    }

    /**
     * Adds theme support for responsive embeds
     *
     * @return void
     */
    public function supportResponsiveEmbeds()
    {
        add_theme_support('responsive-embeds');
    }

    /**
     * Forces reusable blocks to behave as a regular posttype
     *
     * @param \WP_Post_Type $postType
     * @param string        $handle
     */
    public function unlockPostType(WP_Post_Type $postType, string $handle)
    {
        $this->$handle = $postType;
        $this->$handle->_builtin = false;
        $this->$handle->show_in_menu = true;
    }

    /**
     * Sets icon for reusable blocks
     *
     * @param \WP_Post_Type $postType
     * @return void
     */
    public function setReusableBlocksIcon(WP_Post_Type $postType)
    {
        $postType->menu_icon = $this->settings['reusableBlocksIcon'];
    }

    /**
     * Sets labels for reusable blocks
     *
     * @param \WP_Post_Type $postType
     * @return void
     */
    public function setReusableBlocksLabels(WP_Post_Type $postType)
    {
        $postType->labels = (object) array_merge(
            $this->settings['reusableBlocksLabels'],
            (array) $this->reusableBlocksType->labels
        );
    }

    /**
     * Modify capability required to utilize reusable blocks
     *
     * @param \WP_Post_Type $postType
     * @param string $capabilityType
     */
    public function modifyReusableBlocksCapabilities(WP_Post_Type $postType, array $capabilities)
    {
        $postType->capabilities = $capabilities;
    }

    /**
     * Disable Gutenberg entirely
     *
     * @return void
     */
    public function disable()
    {
        add_filter('use_block_editor_for_post', '__return_false', 10);
        add_filter('use_block_editor_for_post_type', '__return_false', 10);
    }
}
