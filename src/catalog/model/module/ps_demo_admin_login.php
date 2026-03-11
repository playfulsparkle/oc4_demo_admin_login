<?php
namespace Opencart\Catalog\Model\Extension\PsDemoAdminLogin\Module;
/**
 * Class PsDemoAdminLogin
 *
 * @package Opencart\Catalog\Model\Extension\PsDemoAdminLogin\Module
 */
class PsDemoAdminLogin extends \Opencart\System\Engine\Model
{
    /**
     * @param array $args
     *
     * @return array
     */
    public function replaceCatalogViewCommonHeaderViews(array $args): array
    {
        $views = [];

        $views[] = [
            'search' => '<body>',
            'replace' => <<<HTML
            <body>
            <section style="color: {{ banner_text_color }}; background-color: {{ banner_background_color }}; text-align: center; display: block; padding-block: .5rem;">
                <p style="font-size: 1.1rem; font-weight: bold; margin: 0;">{{ banner_description }}</p>
            </section>
            HTML
        ];

        return $views;
    }
}
