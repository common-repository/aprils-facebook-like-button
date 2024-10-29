=== April's Facebook Like Button ===
Contributors: springthistle
Tags: button, facebook, iframe button
Requires at least: 2.7.2
Tested up to: 3.0.1
Stable tag: 1.4

== Description ==

Allows you to easily add a facebook like button to your posts. You can specify whether to show the like on your main page or not, on pages or not, in your rss feeds or not. You can choose position (before post, after post) and also specify css to be included in the containing div tag. You can choose from all of the facebook like button options (color, style, verb, etc).

Future features:
* Include the button in feeds

= Features =

* Specify position
* Specify manual or use shortcode
* Specify specific css
* Customize display as far as FB allowed
* Choose inclusion in posts and/or pages
* Exclude specific pages
* If you've got the get-the-image plugin, adds meta tags for post's image

== Installation ==

Follow the steps below to install the plugin.

1. Upload the ahs_facebooklike directory to the /wp-content/plugins/ directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Appearance/Facebook Like to configure the button

== Screenshots ==

1. Facebook Like Settings Page
2. Facebook Like Button

== Frequently Asked Questions ==

= How do I include the button in feeds? =

Currently that functionality has been disabled. It was producing an error and I will release a fix with the next version.
 
= How do I use 'manual' for the position? =

Just use the function <code>echo ahsfl_generate_button()</code> and insert it anywhere in your WordPress theme files.

= How do I exclude only some pages from having the button? =

Whichever pages you want to not have the like button, give them the custom field "facebooklikebutton" with value "exclude".

= Does the plugin have any other useful functions? =

Yes! You can call the function <code>ahsfl_get_image()</code> elsewhere in your theme. It has one required argument (the post id) and one optional argument (the size, which defaults to 'thumbnail').

== Upgrade Notice ==

None yet.

== Help ==

For help and support please contact us at [Springthistle Design](http://springthistle.com/ "Springthistle Design").

== Changelog ==

= 1.4 =
* Fixed bug introduced in 1.3

= 1.3 = 
* Upgraded image fetching

= 1.2 =
* Fixed bug in RSS display

= 1.1 =
* Minor fix

= 1.0 =
* Initial plugin released
