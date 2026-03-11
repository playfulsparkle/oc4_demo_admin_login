<?php
namespace Opencart\Admin\Model\Extension\PsDemoAdminLogin\Module;
/**
 * Class PsDemoAdminLogin
 *
 * @package Opencart\Admin\Model\Extension\PsDemoAdminLogin\Module
 */
class PsDemoAdminLogin extends \Opencart\System\Engine\Model
{
    /**
     * @param array $args
     *
     * @return array
     */
    public function replaceAdminViewCommonHeaderBeforeViews(array $args): array
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

    /**
     * @param array $args
     *
     * @return array
     */
    public function replaceAdminViewExtensionViews(array $args): array
    {
        $views = [];

        $views[] = [
            'search' => '<i class="fa-solid fa-save"></i></button>',
            'replace' => <<<HTML
                <i class="fa-solid fa-save"></i></button>
                <button type="button" class="btn btn-info btn-copy-demo-url" data-demo-url="{{ demo_url }}" data-bs-toggle="tooltip" title="{{ button_copy_url }}"><i class="fa-solid fa-share-nodes"></i></button>
                <script>
                    function copyToClipboardLegacy(text) {
                        var \$temp = $('<textarea>');
                        $('body').append(\$temp);
                        \$temp.val(text).select();
                        document.execCommand('copy');
                        \$temp.remove();
                    }

                    function showCopyMessage() {
                        $('#alert').prepend('<div class="alert alert-success alert-dismissible"><i class="fa-solid fa-check-circle"></i> {{ text_url_copied }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
                    }

                    $(document).ready(function() {
                        $('.btn-copy-demo-url').on('click', function () {
                            var copyText = $(this).attr('data-demo-url');

                            if (copyText && copyText.length) {
                                if (navigator.clipboard && navigator.clipboard.writeText) {
                                    navigator.clipboard.writeText(copyText).then(function() { showCopyMessage(); // Try using the modern Clipboard API first
                                    }).catch(function(err) { copyToClipboardLegacy(copyText); showCopyMessage(); });
                                } else { // Clipboard API not available, use legacy method
                                    copyToClipboardLegacy(copyText);
                                    showCopyMessage();
                                }
                            }
                        });
                    });
                </script>
            HTML
        ];

        return $views;
    }
}
