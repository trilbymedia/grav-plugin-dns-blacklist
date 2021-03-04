# DNS Blacklist Plugin

The **DNS Blacklist** Plugin is an extension for [Grav CMS](http://github.com/getgrav/grav). Checks an IP address via mutliple DNS Blacklists to see if it's banned.  It can be used as a PHP function, Twig function as well as via a Form action.

## Installation

Installing the DNS Blacklist plugin can be done in one of three ways: The GPM (Grav Package Manager) installation method lets you quickly install the plugin with a simple terminal command, the manual method lets you do so via a zip file, and the admin method lets you do so via the Admin Plugin.

### GPM Installation (Preferred)

To install the plugin via the [GPM](http://learn.getgrav.org/advanced/grav-gpm), through your system's terminal (also called the command line), navigate to the root of your Grav-installation, and enter:

    bin/gpm install dns-blacklist

This will install the DNS Blacklist plugin into your `/user/plugins`-directory within Grav. Its files can be found under `/your/site/grav/user/plugins/dns-blacklist`.

### Admin Plugin

If you use the Admin Plugin, you can install the plugin directly by browsing the `Plugins`-menu and clicking on the `Add` button.

## Configuration

If you are not using the admin, you should copy the `user/plugins/dns-blacklist/dns-blacklist.yaml` to `user/config/plugins/dns-blacklist.yaml` and only edit that copy.

Here is the default configuration and an explanation of available options:

```yaml
enabled: true
form_error:
list:
  - css.spamhaus.org
  - xbl.spamhaus.org
  - sbl.spamhaus.org
  - smtp.dnsbl.sorbs.net
  - web.dnsbl.sorbs.net
  - recent.spam.dnsbl.sorbs.net
  - virus.dnsbl.sorbs.net
```

Note that if you use the Admin Plugin, a file with your configuration named `dns-blacklist.yaml` will be saved in the `user/config/plugins/`-folder once the configuration is saved in the Admin.

## Usage

#### PHP Usage

You can use this plugin in your own plugin or theme specific PHP code by accessing it via the global Grav object. For example:

```php
$blacklisted = Grav::instance()['dns-blacklist']->isBlacklisted();
if (!empty($blacklisted)) {
    echo "Your IP is blacklisted by: " . json_encode($blacklisted);
} else {
    echo "Your IP is good!";
}
```

You can also pass in a specific IP address to check:

```php
$blacklisted = Grav::instance()['dns-blacklist']->isBlacklisted('127.0.0.1');
```

#### Twig Usage

Very similar to the PHP usage, you can use the same blacklist class via Twig.  Notice the name is `dns_blacklist` compared to `dns-blacklist` from regular PHP to make it more Twig-friendly:

```twig
{% if dns_blacklist.isBlacklisted  %}
  <h2 class="Error">Your IP is blacklisted, no form for you!</h2>
{% else %}
  {% include "forms/form.html.twig" with {form: forms('contact-form')} %}
{% endif %}
```

#### Form Action Usage

You can also use this logic directly in a form action, so that it's checked during form submission.  For example, this is a sample page which defines a very simple form and simply checks for blacklisted IPs.

```yaml
---
title: 'DNS Blacklist'
form:
    name: dns-blacklist
    fields:
        name:
            label: Name
            placeholder: Name
            type: text
            validate:
                required: true
    buttons:
        -
            type: submit
            html: true
            value: Submit
    process:
        dns-blacklist: true
        message: '<b>Thanks!</b> All good'
---

# IP Blacklist Testing

This is a simple blacklisting form action test page.
```

If you want to provide a custom error message instead of one that references the IP address and the DNSBL providers that block it, you can simply add a custom message in the `form_error:` property of the configuration yaml.

## Blacklist Providers

There are many blacklist providers.  The plugin includes just a few, I suggest checking out https://w3dt.net/tools/dnsbl to test an IP and get a huge list or providers to potentially include.

