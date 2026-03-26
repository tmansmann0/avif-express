<?php

namespace Avife\backend;

if (!defined('ABSPATH')) exit;

final class Enqueue
{

    private static $globalScopeName = 'Avife\backend\Enqueue';

    private static function assetVersion($relativePath)
    {
        $absolutePath = AVIFE_ABS . ltrim($relativePath, '/');

        if (file_exists($absolutePath)) {
            return (string) filemtime($absolutePath);
        }

        return AVIFE_VERSION;
    }

    public static function do()
    {
        add_action('admin_enqueue_scripts', array(self::$globalScopeName, 'add'));
    }

    public static function add($hook)
    {
        if ($hook != 'toplevel_page_' . AVIFE_SPA_SLUG) return;

        if (file_exists(AVIFE_ABS . 'core/app/backend/assets/dist/app.js')) {
            wp_enqueue_script(
                'avife-vue-script',
                AVIFE_REL . '/core/app/backend/assets/dist/app.js',
                array(),
                self::assetVersion('/core/app/backend/assets/dist/app.js'),
                true
            );
        }

        if (file_exists(AVIFE_ABS . 'core/app/backend/assets/dist/app.css')) {
            wp_enqueue_style(
                'avife-tailwind-style',
                AVIFE_REL . '/core/app/backend/assets/dist/app.css',
                array(),
                self::assetVersion('/core/app/backend/assets/dist/app.css')
            );
        }

        if (file_exists(AVIFE_ABS . 'core/app/backend/assets/fonts/fonts.css')) {
            wp_enqueue_style(
                'avife-font-style',
                AVIFE_REL . '/core/app/backend/assets/fonts/fonts.css',
                array(),
                self::assetVersion('/core/app/backend/assets/fonts/fonts.css')
            );
        }
    }
}
