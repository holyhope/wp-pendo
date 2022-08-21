=== Pendhope ===
Contributors: holyhope, seanmrice (US Specific Updates)
Donate link: https://github.com/holyhope/
Tags: analyze, pendo.io, statistics, tracking
Requires at least: 4.5
Tested up to: 5.8.1
Requires PHP: 5.6
Stable tag: {{release_version}}
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Empower your blog with insights and understand what users are doing during their visit.

== Description ==

[![Test](https://github.com/holyhope/wp-pendo/actions/workflows/test.yml/badge.svg?branch=v{{release_version}})](https://github.com/holyhope/wp-pendo/actions/workflows/test.yml)

# Pendo.io

[pendo.io](https://www.pendo.io) is a software that makes your blog better.

## Analytics

Insights to understand what users are doing across their product journey. [Learn more](https://www.pendo.io/product/analytics/).

## In-App guides

Targeted messaging and walkthroughs in-app to improve onboarding and feature adoption. [Learn more](https://www.pendo.io/product/in-app-guides/).

## Feedback

Centralize and prioritize product feedback to determine which features to build next. [Learn more](https://www.pendo.io/product/feedback/).

## Mobile

Create a connected digital experience across web and mobile. [Learn more](https://www.pendo.io/product/mobile/).

== Installation ==

[You will need a pendo.io account](https://app.pendo.io/register) to proceed.

1. Upload `pendhope` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Navigate to `Settings` > `pendhope` and set the Pendo API key.  
   The applicaton key is included in the snippet provided on the Install page or in your App Settings. The application key maps the data the agent collects to your app in your Pendo subscription.

== Frequently Asked Questions ==

= How can I exclude some users? =

Multiple answer to this question:

1. To ignore a single visitor, you can exclude him/her in pendo dashboard.
1. To ignore a all visitor having a role. You can use the plugin settings page.
1. To ignore visits on admin side (which is the default behaviour). See the plugin settings page.

= What data are sent to Pendo? =

The Pendo script use cookie and fallback with localstorage to identify a visitor. Please see pendo.io documentation for more information.  
If the user is logged in, the data is then enhanced with wordpress:

- user id
- email
- fullname
- role

== Screenshots ==

1. Plugin settings page

== Changelog ==

= 0.2.2 =
* Add test badge to readme.
* Generate package version from git tag.

= 0.2.1 =
* Add sync action from github.com to wordpress.org.
* Add linters to continous integration.

= 0.2.0 =
* Change option name.

= 0.1.0 =
* First release.
