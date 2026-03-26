<?php
namespace Opencart\Admin\Controller\Extension\PsDemoAdminLogin\Module;
/**
 * Class PsDemoAdminLogin
 *
 * @package Opencart\Admin\Controller\Extension\PsDemoAdminLogin\Module
 */
class PsDemoAdminLogin extends \Opencart\System\Engine\Controller
{
    /**
     * @var string The support email address.
     */
    const EXTENSION_EMAIL = 'support@playfulsparkle.com';

    /**
     * @var string The documentation URL for the extension.
     */
    const EXTENSION_DOC = 'https://github.com/playfulsparkle/oc4_demo_admin_login.git';

    /**
     * @return void
     */
    public function index(): void
    {
        $this->load->language('extension/ps_demo_admin_login/module/ps_demo_admin_login');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true),
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/ps_demo_admin_login/module/ps_demo_admin_login', 'user_token=' . $this->session->data['user_token'], true),
        ];


        $separator = version_compare(VERSION, '4.0.2.0', '>=') ? '.' : '|';

        $data['action'] = $this->url->link('extension/ps_demo_admin_login/module/ps_demo_admin_login' . $separator . 'save', 'user_token=' . $this->session->data['user_token']);
        $data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');

        $data['user_token'] = $this->session->data['user_token'];

        $data['module_ps_demo_admin_login_status'] = (bool) $this->config->get('module_ps_demo_admin_login_status');
        $data['module_ps_demo_admin_login_username'] = $this->config->get('module_ps_demo_admin_login_username');
        $data['module_ps_demo_admin_login_password'] = $this->config->get('module_ps_demo_admin_login_password');
        $data['module_ps_demo_admin_login_banner_status'] = (bool) $this->config->get('module_ps_demo_admin_login_banner_status');
        $data['module_ps_demo_admin_login_banner_description'] = (array) $this->config->get('module_ps_demo_admin_login_banner_description');
        $data['module_ps_demo_admin_login_banner_text_color'] = $this->config->get('module_ps_demo_admin_login_banner_text_color');
        $data['module_ps_demo_admin_login_banner_background_color'] = $this->config->get('module_ps_demo_admin_login_banner_background_color');

        $this->load->model('localisation/language');

        $data['languages'] = $this->model_localisation_language->getLanguages();

        $data['text_contact'] = sprintf($this->language->get('text_contact'), self::EXTENSION_EMAIL, self::EXTENSION_EMAIL, self::EXTENSION_DOC);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/ps_demo_admin_login/module/ps_demo_admin_login', $data));
    }

    public function save(): void
    {
        $this->load->language('extension/ps_demo_admin_login/module/ps_demo_admin_login');

        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/ps_demo_admin_login/module/ps_demo_admin_login')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (!$json) {
            if (
                $this->_strlen(trim($this->request->post['module_ps_demo_admin_login_username'])) <= 1 ||
                $this->_strlen(trim($this->request->post['module_ps_demo_admin_login_username'])) >= 255
            ) {
                $json['error']['input-username'] = $this->language->get('error_username');
            }

            if (
                $this->_strlen(trim($this->request->post['module_ps_demo_admin_login_password'])) <= 1 ||
                $this->_strlen(trim($this->request->post['module_ps_demo_admin_login_password'])) >= 255
            ) {
                $json['error']['input-password'] = $this->language->get('error_password');
            }

            if (isset($this->request->post['module_ps_demo_admin_login_banner_description'])) {
                foreach ($this->request->post['module_ps_demo_admin_login_banner_description'] as $language_id => $value) {
                    if ($this->_strlen(trim(($value))) === 0) {
                        $json['error']['input-banner-description-' . $language_id] = $this->language->get('error_description');
                    }
                }
            }
        }

        if (!$json) {
            $this->load->model('setting/setting');

            $this->model_setting_setting->editSetting('module_ps_demo_admin_login', $this->request->post);

            $json['success'] = $this->language->get('text_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function install(): void
    {
        $this->load->model('setting/setting');

        $data = [
            'module_ps_demo_admin_login_status' => 0,
            'module_ps_demo_admin_login_username' => '',
            'module_ps_demo_admin_login_password' => '',
            'module_ps_demo_admin_login_banner_status' => 0,
            'module_ps_demo_admin_login_banner_description' => [],
            'module_ps_demo_admin_login_banner_text_color' => '#000000',
            'module_ps_demo_admin_login_banner_background_color' => '#ffa800',
        ];

        $this->model_setting_setting->editSetting('module_ps_demo_admin_login', $data);

        $this->load->model('setting/event');

        $this->registerEvents();
    }

    public function uninstall(): void
    {
        $this->load->model('setting/event');

        $this->unregisterEvents();
    }

    private function unregisterEvents(): void
    {
        $this->model_setting_event->deleteEventByCode('module_ps_demo_admin_login');
    }

    private function registerEvents(): bool
    {
        $separator = version_compare(VERSION, '4.0.2.0', '>=') ? '.' : '|';

        $events = [
            ['trigger' => 'admin/view/common/header/before', 'description' => '', 'actionName' => 'eventAdminViewCommonHeaderBefore'],
            ['trigger' => 'admin/view/extension/*/before', 'description' => '', 'actionName' => 'eventAdminViewExtensionBefore'],
            ['trigger' => 'admin/controller/common/login/before', 'description' => '', 'actionName' => 'eventAdminControllerCommonLoginBefore'],
            ['trigger' => 'catalog/view/common/header/before', 'description' => '', 'actionName' => 'eventCatalogViewCommonHeaderBefore'],
        ];

        $result = 0;

        if (version_compare(VERSION, '4.0.1.0', '>=')) {
            foreach ($events as $event) {
                $result += $this->model_setting_event->addEvent([
                    'code' => 'module_ps_demo_admin_login',
                    'description' => $event['description'],
                    'trigger' => $event['trigger'],
                    'action' => 'extension/ps_demo_admin_login/module/ps_demo_admin_login' . $separator . $event['actionName'],
                    'status' => '1',
                    'sort_order' => '0'
                ]);
            }
        } else {
            foreach ($events as $event) {
                $result += $this->model_setting_event->addEvent(
                    'module_ps_demo_admin_login',
                    $event['description'],
                    $event['trigger'],
                    'extension/ps_demo_admin_login/module/ps_demo_admin_login' . $separator . $event['actionName']
                );
            }
        }

        return $result > 0;
    }

    /**
     * Event: admin/controller/common/login/before
     *
     * @param string $route
     * @param array $args
     *
     * @return void
     */
    public function eventAdminControllerCommonLoginBefore(&$route, &$args)
    {
        if (!$this->config->get('module_ps_demo_admin_login_status')) {
            return;
        }

        // Ensure all required GET parameters exist
        if (
            !isset(
            $this->request->get['username'],
            $this->request->get['password'],
            $this->request->get['redirect']
        )
        ) {
            return;
        }

        $config_username = $this->config->get('module_ps_demo_admin_login_username');
        $config_password = $this->config->get('module_ps_demo_admin_login_password');

        $get_username = $this->request->get['username'];
        $get_password = $this->request->get['password'];
        $get_redirect = str_replace('&amp;', '&', urldecode($this->request->get['redirect']));

        if (
            $config_username === '' ||
            $config_password === '' ||
            $get_redirect === ''
        ) {
            return;
        }

        // Validate credentials and redirect safety
        if (
            $get_username !== $config_username ||
            $get_password !== $config_password ||
            stripos($get_redirect, HTTP_SERVER) !== 0 // does NOT start with HTTP_SERVER
        ) {
            return;
        }

        $this->load->model('user/user');

        if ($this->user->login($get_username, $get_password)) {
            $login_data = [
                'ip' => oc_get_ip(),
                'user_agent' => $this->request->server['HTTP_USER_AGENT']
            ];

            $this->model_user_user->addLogin($this->user->getId(), $login_data);

            $this->session->data['user_token'] = oc_token(32);

            $new_url = $get_redirect . '&user_token=' . $this->session->data['user_token'];

            echo <<<HTML
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <title>Redirecting to $new_url</title>
                </head>
                <body>
                    <script>window.location = "$new_url";</script>
                    <p>Redirecting to $new_url ...</p>
                </body>
                </html>
            HTML;
            exit;
        }
    }

    /**
     * Event: admin/view/common/header/before
     *
     * @param string $route
     * @param array $args
     * @param string $output
     *
     * @return void
     */
    public function eventAdminViewCommonHeaderBefore(&$route, &$args, &$output)
    {
        if (!$this->config->get('module_ps_demo_admin_login_status')) {
            return;
        }

        if (!$this->config->get('module_ps_demo_admin_login_banner_status')) {
            return;
        }

        $this->load->model('extension/ps_demo_admin_login/module/ps_demo_admin_login');

        $banner_description = (array) $this->config->get('module_ps_demo_admin_login_banner_description');

        $language_id = $this->config->get('config_language_id');

        $args['banner_description'] = isset($banner_description[$language_id]) ? $banner_description[$language_id] : '';
        $args['banner_text_color'] = $this->config->get('module_ps_demo_admin_login_banner_text_color');
        $args['banner_background_color'] = $this->config->get('module_ps_demo_admin_login_banner_background_color');


        $views = $this->model_extension_ps_demo_admin_login_module_ps_demo_admin_login->replaceAdminViewCommonHeaderBeforeViews($args);

        $output = $this->replaceViews($route, $output, $views);
    }

    /**
     * Event: admin/view/extension/*\/before
     *
     * @param string $route
     * @param array $args
     * @param string $output
     *
     * @return void
     */
    public function eventAdminViewExtensionBefore(&$route, &$args, &$output)
    {
        if (!$this->config->get('module_ps_demo_admin_login_status')) {
            return;
        }

        if (
            in_array($route, ['extension/ps_demo_admin_login/module/ps_demo_admin_login', 'extension/module']) ||
            stripos($route, 'extension/opencart') === 0
        ) {
            return;
        }

        $this->load->language('extension/ps_demo_admin_login/module/ps_demo_admin_login', 'ps');

        $this->load->model('extension/ps_demo_admin_login/module/ps_demo_admin_login');

        $args['button_copy_url'] = $this->language->get('ps_button_copy_url');
        $args['text_url_copied'] = $this->language->get('ps_text_url_copied');

        if (
            isset($this->request->get['store_id']) &&
            (strpos($route, '/analytics/') !== 0 ||
            strpos($route, '/advertise/') !== 0 ||
            strpos($route, '/theme/') !== 0)
        ) {
            $extension_params = '&store_id=' . $this->request->get['store_id'];
        } else if (
            isset($this->request->get['module_id']) &&
            strpos($route, '/module/') !== 0
        ) {
            $extension_params = '&module_id=' . $this->request->get['module_id'];
        } else {
            $extension_params = '';
        }

        $extension_url = $this->url->link($route, $extension_params);

        $args['demo_url'] = $this->url->link(
            'common/login',
            'username=' . $this->config->get('module_ps_demo_admin_login_username') .
            '&password=' . $this->config->get('module_ps_demo_admin_login_password') .
            '&redirect=' . urlencode($extension_url),
            true
        );


        $views = $this->model_extension_ps_demo_admin_login_module_ps_demo_admin_login->replaceAdminViewExtensionViews($args);

        $output = $this->replaceViews($route, $output, $views);
    }

    /**
     * Retrieves the contents of a template file based on the provided route.
     *
     * This method checks if an event template buffer is provided. If so, it returns that buffer.
     * If not, it constructs the template file path based on the current theme settings and checks
     * for the existence of the template file. If the file exists, it reads and returns its contents.
     * It supports loading templates from both the specified theme directory and the default template directory.
     *
     * @param string $route The route for which the template is being retrieved.
     *                      This should match the naming convention for the template files.
     * @param string $event_template_buffer The template buffer that may be passed from an event.
     *                                       If provided, this buffer will be returned directly,
     *                                       bypassing file retrieval.
     *
     * @return mixed Returns the contents of the template file as a string if it exists,
     *               or false if the template file cannot be found or read.
     */
    protected function getTemplateBuffer(string $route, string $event_template_buffer): mixed
    {
        if ($event_template_buffer) {
            return $event_template_buffer;
        }

        // Support for OC4 extension
        $firstSlash = strpos($route, '/');
        $secondSlash = strpos($route, '/', $firstSlash + 1);
        $template_file = DIR_OPENCART . substr($route, 0, $secondSlash + 1) . 'admin/view/template/' . substr($route, $secondSlash + 1) . '.twig';

        if (file_exists($template_file) && is_file($template_file)) {
            $template_file = $this->modCheck($template_file);

            return file_get_contents($template_file);
        }

        if (defined('DIR_CATALOG')) {
            $dir_template = DIR_TEMPLATE;
        } else {
            if ($this->config->get('config_theme') == 'default') {
                $theme = $this->config->get('theme_default_directory');
            } else {
                $theme = $this->config->get('config_theme');
            }

            $dir_template = DIR_TEMPLATE . $theme . '/template/';
        }

        $template_file = $dir_template . $route . '.twig';

        if (file_exists($template_file) && is_file($template_file)) {
            $template_file = $this->modCheck($template_file);

            return file_get_contents($template_file);
        }

        if (defined('DIR_CATALOG')) {
            return false;
        }

        $dir_template = DIR_TEMPLATE . 'default/template/';
        $template_file = $dir_template . $route . '.twig';

        if (file_exists($template_file) && is_file($template_file)) {
            $template_file = $this->modCheck($template_file);

            return file_get_contents($template_file);
        }

        // Support for OC4 catalog
        $dir_template = DIR_TEMPLATE;
        $template_file = $dir_template . $route . '.twig';

        if (file_exists($template_file) && is_file($template_file)) {
            $template_file = $this->modCheck($template_file);

            return file_get_contents($template_file);
        }

        return false;
    }

    /**
     * Checks and modifies the provided file path based on predefined directory constants.
     *
     * This method checks if the file path starts with specific directory constants (`DIR_MODIFICATION`,
     * `DIR_APPLICATION`, and `DIR_SYSTEM`). Depending on these conditions, it modifies the file path to
     * point to the appropriate directory under `DIR_MODIFICATION`.
     *
     * - If the file path starts with `DIR_MODIFICATION`, it checks if it should point to either the
     *   `admin` or `catalog` directory based on the definition of `DIR_CATALOG`.
     * - If `DIR_CATALOG` is defined, the method checks for the file in the `admin` directory.
     *   Otherwise, it checks in the `catalog` directory.
     * - If the file path starts with `DIR_SYSTEM`, it checks for the file in the `system` directory
     *   within `DIR_MODIFICATION`.
     *
     * The method ensures that the returned file path exists before modifying it.
     *
     * @param string $file The original file path to check and modify.
     * @return string|null The modified file path if found, or null if it does not exist.
     */
    protected function modCheck(string $file): mixed
    {
        if (defined('DIR_MODIFICATION')) {
            if ($this->startsWith($file, DIR_MODIFICATION)) {
                if (defined('DIR_CATALOG')) {
                    if (file_exists(DIR_MODIFICATION . 'admin/' . substr($file, strlen(DIR_APPLICATION)))) {
                        $file = DIR_MODIFICATION . 'admin/' . substr($file, strlen(DIR_APPLICATION));
                    }
                } else {
                    if (file_exists(DIR_MODIFICATION . 'catalog/' . substr($file, strlen(DIR_APPLICATION)))) {
                        $file = DIR_MODIFICATION . 'catalog/' . substr($file, strlen(DIR_APPLICATION));
                    }
                }
            } elseif ($this->startsWith($file, DIR_SYSTEM)) {
                if (file_exists(DIR_MODIFICATION . 'system/' . substr($file, strlen(DIR_SYSTEM)))) {
                    $file = DIR_MODIFICATION . 'system/' . substr($file, strlen(DIR_SYSTEM));
                }
            }
        }

        return $file;
    }

    /**
     * Checks if a given string starts with a specified substring.
     *
     * This method determines if the string $haystack begins with the substring $needle.
     *
     * @param string $haystack The string to be checked.
     * @param string $needle The substring to search for at the beginning of $haystack.
     *
     * @return bool Returns true if $haystack starts with $needle; otherwise, false.
     */
    protected function startsWith(string $haystack, string $needle): bool
    {
        if (strlen($haystack) < strlen($needle)) {
            return false;
        }

        return (substr($haystack, 0, strlen($needle)) == $needle);
    }

    /**
     * Replaces specific occurrences of a substring in a string with a new substring.
     *
     * This method searches for all occurrences of a specified substring ($search) in a given string ($string)
     * and replaces the occurrences at the positions specified in the $nthPositions array with a new substring ($replace).
     *
     * @param string $search The substring to search for in the string.
     * @param string $replace The substring to replace the found occurrences with.
     * @param string $string The input string in which replacements will be made.
     * @param array $nthPositions An array of positions (1-based index) indicating which occurrences
     *                            of the search substring to replace.
     *
     * @return mixed The modified string with the specified occurrences replaced, or the original string if no matches are found.
     */
    protected function replaceNth(string $search, string $replace, string $string, array $nthPositions): mixed
    {
        $pattern = '/' . preg_quote($search, '/') . '/';
        $matches = [];
        $count = preg_match_all($pattern, $string, $matches, PREG_OFFSET_CAPTURE);

        if ($count > 0) {
            foreach ($nthPositions as $nth) {
                if ($nth > 0 && $nth <= $count) {
                    $offset = $matches[0][$nth - 1][1];
                    $string = substr_replace($string, $replace, $offset, strlen($search));
                }
            }
        }

        return $string;
    }

    /**
     * Replaces placeholders in a template with corresponding values from the views array.
     *
     * This method retrieves the template content based on the given route and template name,
     * then replaces specified search strings with their corresponding replace strings.
     * If positions are specified, the method performs replacements only at those positions.
     *
     * @param string $route The route associated with the template.
     * @param string|null $template The name of the template to be processed.
     * @param array $views An array of associative arrays where each associative array contains:
     *                     - string 'search': The string to search for in the template.
     *                     - string 'replace': The string to replace the 'search' string with.
     *                     - array|null 'positions': (Optional) An array of positions
     *                     where replacements should occur. If not provided,
     *                     all occurrences will be replaced.
     *
     * @return mixed The modified template content after performing the replacements.
     */
    protected function replaceViews(string $route, string|null $template, array $views): mixed
    {
        if (is_null($template)) {
            $template = '';
        }

        if (empty($views)) {
            return $this->getTemplateBuffer($route, $template);
        }

        $output = $this->getTemplateBuffer($route, $template);

        foreach ($views as $view) {
            if (isset($view['positions']) && $view['positions']) {
                $output = $this->replaceNth($view['search'], $view['replace'], $output, $view['positions']);
            } else {
                $output = str_replace($view['search'], $view['replace'], $output);
            }
        }

        return $output;
    }

    /**
     * Get the length of a string while ensuring compatibility across OpenCart versions.
     *
     * This method returns the length of the provided string. It utilizes different
     * string length functions based on the OpenCart version being used to ensure
     * accurate handling of UTF-8 characters.
     *
     * - For OpenCart versions before 4.0.1.0, it uses `utf8_strlen()`.
     * - For OpenCart versions from 4.0.1.0 up to (but not including) 4.0.2.0,
     *   it uses `\Opencart\System\Helper\Utf8\strlen()`.
     * - For OpenCart version 4.0.2.0 and above, it uses `oc_strlen()`.
     *
     * @param string $string The input string whose length is to be calculated.
     *
     * @return int The length of the input string.
     */
    private function _strlen(string $string): int
    {
        if (version_compare(VERSION, '4.0.1.0', '<')) { // OpenCart versions before 4.0.1.0
            return utf8_strlen($string);
        } elseif (version_compare(VERSION, '4.0.2.0', '<')) { // OpenCart version 4.0.1.0 up to, but not including, 4.0.2.0
            return \Opencart\System\Helper\Utf8\strlen($string);
        }

        return oc_strlen($string); // OpenCart version 4.0.2.0 and above
    }
}
