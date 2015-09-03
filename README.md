# Simple User Adding #
Contributors:      wearerequired, swissspidy  
Donate link:       http://required.ch  
Tags:              admin, email, users, form, registration  
Requires at least: 3.1  
Tested up to:      4.3  
Stable tag:        1.0.0  
License:           GPLv2 or later  
License URI:       http://www.gnu.org/licenses/gpl-2.0.html  

This plugin makes adding users to your WordPress site easier than ever before.

## Description ##

Simple User Adding declutters the form used to add new users to your site and strips it down to a minimum.

Highlights:

* You no longer have to enter a custom password. It is automatically created for you.
* The plugin tries to detect the new user’s name from the email address you entered.
* Also: A “Show More” link reveals the first and last name fields, plus a new field to enter a custom welcome message for the new user.

## Installation ##

### Manual Installation ###

1. Upload the entire `/simple-user-adding` directory to the `/wp-content/plugins/` directory.
2. Activate Simple User Adding through the 'Plugins' menu in WordPress.
3. Add new users through Users -> Add New

## Frequently Asked Questions ##

### Why can’t I enter a custom welcome message? ###

If the message field is missing, another plugin is already defining the `wp_new_user_notification` function. This means we can’t define our own mail function and therefore you can’t enter a custom message.

### I want more control! Where can I add more info? ###

Well, edit the user’s profile afterwards or just go to the original form to add new users. You can find a link to it in the admin footer.

### Is Multisite supported? ###

Not 100% yet, but we’re working on it!

## Screenshots ##

1. This plugin makes adding new users to your WordPress site simpler than ever.
2. You can even enter a custom message that is shown in the confirmation email the user receives.

## Contribute ##

If you would like to contribute to this plugin, report an isse or anything like that, please note that we develop this plugin on [GitHub](https://github.com/wearerequired/simple-user-adding).

Developed by [required+](http://required.ch/ "Team of experienced web professionals from Switzerland & Germany")

## Changelog ##

### 1.1.0 ###
* Enhancement: Now supports the Digest Notifications plugin.
* Enhancement: Improved name guessing from email address.
* Fixed: The plugin now works with WordPress 4.3.

### 1.0.0 ###
* First release

## Upgrade Notice ##

### 1.1.0 ###
Being 100% compatible with WordPress 4.3, this update includes some smaller enhancements.

### 1.0.0 ###
First release
