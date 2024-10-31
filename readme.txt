=== Resource Management for Wordpress ===
Contributors: soumenroy111
Donate link: soumenroy111@gmail.com
Tags: Resource managment, Documents, Documents List, Downloads, Uploads, Resources, Library
Requires at least: 4.0
Tested up to: 4.8
Stable tag: 4.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add and Manage various type of resources/ documents from wp-admin and get any resource list with download option in frontend by using shortcode. 

== Description ==

### Admin Panel

A plugin for uploading and managing resources or documents from admin. There is another option to add and manage any resource category/ sub category. In the resource entry form there is a 'drag n drop' option to upload any resource file along with traditional browse facility. Even according to resource type only your uploaded file will be submitted. Also there are category/ sub category selection fields as well as a description box in the resource entry form.


### Frontend Display

Use shortcode: `[resources]` for all resources to any post or in pages  at anywhere. 
Use `[resources res_cat=Category ID]` for any particular resource category even multiple category IDs can be included by separating a comma(,).

Ex. [resources res_cat=2] or [resources res_cat=1,2,3] 
N.B.: You will get any `Category ID` from resource category section. This ID should always be numeric numbers and be sure there is no such blank space around the IDs.

== Installation ==

To install the plugin and get it working follow the steps below: 

1. Upload the plugin files to the `/wp-content/plugins/resource_management` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Find `Resources` menu after `Settings` menu to use the plugin


== Frequently Asked Questions ==

= The plugin Requires PHP 5.5 =

Ask your host about upgrading your server at least to 5.5 as earlier versions of PHP are no longer secure.(https://secure.php.net/supported-versions.php).

= Is there any shortcode support? =

Yes, it has already mentioned in the plugin details section.
Use shortcode: `[resources]` for all resources to any post or in pages . 
Use `[resources res_cat=Category ID]` for any particular resource category even multiple category IDs can be included by separating a comma(,).

Ex. [resources res_cat=2] or [resources res_cat=1,2,3] 
N.B.: You will get any `Category ID` from resource category section. This ID always should be numeric numbers and be sure there is no such blank space around the IDs.

= Do I need an account for this plugin? =

Not now :).

== Screenshots ==

1. Resource List in Admin section.
2. Add New Resource form in Admin section.
3. Resource Categories entry/list in Admin section.
4. Edit Resource form in Admin section.
5. Resource List in Frontend section.
6. On hover Resource details in Frontend section.

== Changelog ==

= 1.0 =
* Initial release
