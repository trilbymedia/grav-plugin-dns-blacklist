<?php
namespace Grav\Plugin;

use Composer\Autoload\ClassLoader;
use Grav\Common\Plugin;
use Grav\Common\Twig\Twig;
use Grav\Common\Uri;
use Grav\Plugin\DNSBlacklist\Blacklist;
use RocketTheme\Toolbox\Event\Event;

/**
 * Class DNSBlacklistPlugin
 * @package Grav\Plugin
 */
class DNSBlacklistPlugin extends Plugin
{
    protected $blacklist;

    public static function getSubscribedEvents(): array
    {
        return [
            'onPluginsInitialized' => [
                ['onPluginsInitialized', 0]
            ]
        ];
    }

    public function autoload(): ClassLoader
    {
        return require __DIR__ . '/vendor/autoload.php';
    }

    public function onPluginsInitialized(): void
    {
        if ($this->isAdmin()) {
            return;
        }

        $this->enable([
            'onFormProcessed' => ['onFormProcessed', 0],
            'onTwigPageVariables' => ['onTwigVariables', 0],
            'onTwigSiteVariables' => ['onTwigVariables', 0],
        ]);

        $this->blacklist = new Blacklist();
        $this->grav['dns-blacklist'] = $this->blacklist;
    }

    public function onFormProcessed(Event $event)
    {
        /** @var Form $form */
        $form = $event['form'];
        $action = $event['action'];
        $params = $event['params'];

        switch ($action) {
            case 'dns-blacklist':

                if (!is_bool($params)) {
                    $ip = $this->grav['twig']->processString((string)$params, array('form' => $form));
                } else {
                    $ip = Uri::ip();
                }

                $blacklisted = $this->blacklist->isBlacklisted($ip);

                if (!empty($blacklisted)) {
                    $custom_form_error = $this->config->get('plugins.dns-blacklist.form_error');
                    $msg = 'Your IP address: ' . $ip . ' is blacklisted by ' . json_encode($blacklisted);
                    $this->grav['log']->notice($msg);

                    $msg = $custom_form_error ?: $msg;
                    $this->grav->fireEvent('onFormValidationError', new Event([
                        'form' => $form,
                        'message' => $msg,
                    ]));
                    $event->stopPropagation();
                    return;
                }

                break;
        }
    }

    public function onTwigVariables(Event $event = null): void
    {
        $twig = $this->grav['twig'];
        $twig->twig_vars['dns_blacklist'] = $this->blacklist;
    }

}
