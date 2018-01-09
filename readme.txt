=== Gravity Forms Sticky List ===
Contributors: fried_eggz
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8R393YVXREFN6
Tags: gravity forms, edit, list, delete
Requires at least: 3.0.1
Tested up to: 4.9
Stable tag: 1.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: sticky-list
Domain Path: /languages

Sticky List is a Gravity Forms add-on that lets you list and edit entries from the front end.

== Description ==

#### Sticky List
Sticky List is an add-on for the WordPress plugin <a href="http://www.gravityforms.com/" target="_blank">Gravity Forms</a> that lets you list and edit entries from the front end. You can display a list on the front end where users can view, delete and edit submitted entries.

#### New Features

* Download entries as PDF (requires <a href="https://gravitypdf.com/">Gravity PDF</a>)
* Added support for "limit entries" and "No Duplicates"

#### All Features

* Display a list of entries on the front end
* Choose who can se the list; specific role, entry creator, all logged in users or anyone.
* Support for (almost) all Gravity Forms fields
* Create/edit/delete Wordpress posts from the front end
* Conditional logic support
* View, edit and delete existing entries from the front-end
* Download entries as PDF (requires <a href="https://gravitypdf.com/">Gravity PDF</a>)
* Use existing entries as templates for duplication
* Conditional notifications
* Conditional confirmations
* List sorting and search (using <a href="http://www.listjs.com/">list.js</a>)
* List pagination
* Custom column labels
* Multiple lists in same page or post
* Mark entries as read when viewed or edited on frontend
* Fully compatible with Gravity Forms "limit entries" and "No Duplicates" features
* Uses new <a href="http://www.gravityhelp.com/documentation/page/Gravity_Forms_API">Gravity Forms API</a> and the official <a href="http://www.gravityhelp.com/documentation/page/Add-On_Framework">Gravity Forms Add-on framework</a>
* Fully customizable with dead simple styles to override
* Fully localized. You can <a href="https://github.com/13pixlar/sticky-list/tree/master/languages">add your translation</a>
* Fully supported and maintained
* Completely free and open source

#### Planned features

* Log deletes, edits and views
* Graphic shortcode builder
* Export list to .csv from front end
* Abillity to control ALL settings from within the shortcode
* Support for multiple uploads in file field
* Support for full multi page forms

#### Usage

1. Upload and activate the plugin
2. Go to the settings page of a form and click the Sticky List settings tab
3. Enable Sticky List for that form and choose your settings
4. Select the page/post where the **form** is embedded
5. Go to the form editor and select what fields should be displayed in the list
6. Put the shortcode in a page/post with the corresponding form id, i.e: `[stickylist id="1"]`

If you want to display entries only from a specific user you can include the user ID like so:

`[stickylist id="1" user="5"]`

To use this in a template file, for example on the user profile page (make user that the variable **$user_id** holds the ID of the user who's profile is being viewed):

`echo do_shortcode( "[stickylist id='1' user='" . $user_id . "']");`

If you want to use the list on different pages and restrict viewing of entries to different groups you can include the `showto` parameter in the shot code. This parameter has three possible settings: `creator`, `loggedin` or `everyone`.

`[stickylist id="1" user="5" showto="creator"]`

If you want to filter out some entries depending on a value of a field you can use the attributes **field** and **value** like so:

`[stickylist id="1" field="5" value="Test"]`

The shortcode above would produce a list that only contained entries where the field with an ID of 5 had a value of "Test". You can check all field ID's in the form editor.

#### List and edit Gravity Form entries on the front end

Front end editing of entries has always been a problem in Gravity Forms. Solutions that exist are buggy and not very feature rich. Gravity Forms Sticky List aims to fill this gap and provide a simple and solid way to view, edit and delete entry submissions from the front end. The goal of the plugin is not to to display entries in a fancy way (<a href="https://gravityview.co/">GravityView</a> already does that brilliantly) but to provide a simple, lightweight and rock solid way to list, edit and delete submissions on the front-end. Lists can be embedded in any post or page.

#### Delete Gravity Form submissions from front end

Gravity Forms Sticky List uses a simple ajax approach to deleting entries. Deleted entries are moved to trash or permanently deleted depending on the per form settings.

#### Create, edit and delete Wordpress posts on the front end

If you attach a Post Field to your form you can use Sticky List to let your users create, edit and delete Wordpress posts from the front end. This makes Gravity Forms more powerful and allows you to create all sorts of features for your users.

#### List entries from a specific user

You can use the list to display entries from a specified user. This is helpful when building for example a user profile and are looking to display that user's submissions on the front end at his or hers profile page. Se the **usage** section form more info.

#### Sort and search entries

Sticky List uses the fast and lightweight <a href="http://www.listjs.com/">list.js</a> to allow for sorting the list and searching the entries. Searching entries is fast and results are updated immediately.

#### Conditional confirmations and notifications

Gravity Forms Sticky List adds conditional confirmations and notifications so that different confirmations messages can be shown depending on if a new entry was submitted or if an existing entry was updated, and diffrent email notifications can be sent if an entry was added, updated or deleted.

#### Styling the list

Sticky List ships with a minimal stylesheet that is easy to override. The table has the class of `.sticky-list` attached to it which can be used to override the default styles. The stylesheet is located in `sticky-list/css/sticky-list_styles.css` in the plugins main directory. To override a style just copy it from <a href="https://github.com/13pixlar/sticky-list/blob/master/css/sticky-list_styles.css">sticky-list_styles.css</a> and paste it in your themes css-file, then modify the style to your liking.

To style the View, Edit, Delete, Post, and Duplicate links you can use these CSS classes:

`
.sticky-list-view
.sticky-list-edit
.sticky-list-delete
.sticky-list-postlink
.sticky-list-duplicate
`

To style read and unread entries in the list you can use these CSS classes

`
.is_read
.not_read
`

#### Custom capabilities

Sticky List adds two capabilities that can be used to allow users to edit and delete entries in the list. These are `stickylist_edit_entries` and `stickylist_delete_entries`. Users/roles with these capabilities will be able to edit/delete entries in the list.

#### Developers

**Avalible filters**<br>
The filter `filter_entries` allows for filtering of the entries in the list.<br>
Paramters: $entries (array of entry objects)<br>

**Example**<br>
This code (when placed in functions.php) would filter out all entries where field ID 1 equals "some-text"<br>
`
add_filter('filter_entries','hide_some_rows' );
function hide_some_rows($entries) {
	foreach ($entries as $entryKey => $entryValue) {
		if ($entryValue["1"] == "some-text") {
			unset($entries[$entryKey]);
		}
	}
	return $entries;
}
`

**Avalible actions**<br>
The action `stickylist_entry_edited` fires after an entry has been edited.<br>
Paramters: $old_entry, $new_entry (entry objects)<br>

**Example**<br>
Use this to perform acions after an entry has been edited.<br>
`
add_action('stickylist_entry_edited','my_entry_edited_function', 10, 2 );
function my_entry_edited_function($old_entry, $new_entry) {
    // Do something
}
`

The action `stickylist_entry_deleted` fires after an entry has been deleted.<br>
Paramters: $entry (entry object)<br>

**Example**<br>
Use this to perform acions after an entry has been edited.<br>
`
add_action('stickylist_entry_deleted','my_entry_deleted_function', 10, 1 );
function my_entry_deleted_function($old_entry, $new_entry) {
    // Do something
}
`

**Documentation**<br>
There is a fully documented version of the plugin on the <a href="https://github.com/13pixlar/sticky-list">Github project page</a>. This plugin is Open Source and pull requests are welcome.

**Note:** <a href="http://www.gravityforms.com/" target="_blank">Gravity Forms</a> version 1.8.19.2+ is required for this plugin.

#### Known issues

**Multi page file uploads**<br>
In multi page forms, file uploads must be on the last page to be editable.

**Multiple file uploads in same field does not work**<br>
This will be addressed in a future version of Sticky List.

**Post image meta fields are not populated when editing an entry**<br>
When editing an entry that has Wordpress Post Image Field the meta inputs are not populated with existing values. This is due to how Gravity Form saves the data. This issue wont get fixed unless Rocket Genious changes the way it handles these fields.

== Installation ==

1. Upload extracted folder to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Choose the required Sticky List settings on the individual form settings page.

== Frequently Asked Questions ==

= The plugin is activated but doesnt seem to work =

Sometimes after an upgrade Gravity Forms will fail to reactivate add-ons properly. Just deactivate and activate Sticky List manually from the plugins page in the WordPress admin.

= The list is empty, why? =

You need to check "Show in list" on the fields that you would like to appear in the list. This is done in the form editor when edtiting a field. <a href="https://ps.w.org/gravity-forms-sticky-list/assets/screenshot-4.png">See screenshot</a>.

= The View and Edit links don't do anything =

In Sticky List settings for that form: make sure that you select the page/post where the FORM is embedded. The actual form is then used to view and/or edit the entries.

= Where are the Sticky List settings? =

Sticky List is activated on a per form basis. The settings are located in the individual form settings, right under Notifications.

= I still cant find any sticky list settings =

Make sure that the plugin is activated **and** that your Gravity Forms version is 1.8.19.2 or higher.

Make sure that your user/role has the correct capabilities. You can use a <a href="https://wordpress.org/plugins/user-role-editor/">role editor plugin</a> to check this. The capabilities you are looking for are `gravityforms_stickylist` and `gravityforms_stickylist_uninstall`.

= File uploads can't be edited =

Sticky List does not support multi file uploads (where you can upload multiple files to a single field). Multi file uploads are in the roadmap for a future release. In the meantime you can use single file uploads which are supported.

= Can I display a thumbnail/icon instead of the file name in the list? =

This can be done using jQuery.

Thumbnail:

`
jQuery(document).ready(function($) {
    cell = $('.stickylist-fileupload a');
    cell.each(function(index) {
        image = $(this).attr('href');
        $(this).html('<img width="50" src="' + image + '">');
    });
});
`

Icon:

`
jQuery(document).ready(function($) {
    cell = $('.stickylist-fileupload a');
    cell.each(function(index) {
        $(this).html('<img width="50" src="path/to/icon.jpg">');
    });
});
`

Note that the code above assumes that you have "Make files clickable" checked. Also note that you might want to tweak the code a little to fit your needs.

= How can I add the entry ID to the list? =

Add a field to your form and note the ID of that field and then add this code to your functions.php

`
add_filter('filter_entries','add_entry_id' );
function add_entry_id($entries) {
    foreach ($entries as &$entry) {
    	$entry["xxx"] = $entry["id"];
    }
    return $entries;
}
`

Change xxx in the code above to the ID of your new field.

= How can I stop some entries from showing up in the list? =

To filter out entries from the list depending on  a value you can use the `filter_entries` filter. For example; if you want to show only approved entries you could use this code in your functions.php:

`add_filter('filter_entries','show_only_approved' );
function show_only_approved($entries) {
    foreach ($entries as $entryKey => &$entryValue) {
        if ($entryValue["xxx"] == NULL) {
            unset($entries[$entryKey]);
        }
    }
    return $entries;
}`

Then create a field in your form with a checkbox that says "Approved". Note the ID of the new field and replace xxx above with the fields ID.

== Screenshots ==

1. Sticky List settings page

2. Sticky List settings page

3. Sticky List settings page

4. Sticky List field settings

5. Front end list

== Changelog ==

= 1.5 =
* Added support for Gravity PDF
* Added support for "limit entries" and "No Duplicates"
* Fixed an issue with embedd dropdown
* Fixed an issue with filtering on user id in shortcode
* Added multiple values separator setting
* Fixed an issue with file uploads

= 1.4.5.1 =
* Keep original post date when editing WordPress post
* Fixed a problem where files were missing from the plugin directory
* Changed hook name (see Developers section)

= 1.4.5 =
* Added action hooks for edit entries and delete entries

= 1.4.4 =
* Fixed a bug that would leave empty entries in the database on edit
* Fixed a bug where fields were not grayed out during view
* Added currency formating
* Fixed a view entries bug
* Improved initial sort of list to work with complex fields (Name, address)
* Fixed an issue where a form in ajax mode would create a new entry in a multi page form

= 1.4.3 =
* Improved formating of numbers field

= 1.4.2 =
* Mark entries as read when viewed or edited on frontend
* Fixed bad formating in readme that caused problems with copy/paste
* Fixed double spacing bug that caused search not to match in some conditions
* Updated stable tag to 4.8.1
* Fixed an undefined index notice
* Removed unwanted spaces

= 1.4.1 =
* Added limit for nr of posts in embedd dropdown

= 1.4 =
* Multi page support
* Remove table header when last list item is deleted
* Filter by empty value in shortcode
* Better support for multiple lists in one page

= 1.3.4 =
* Added ability to filter entries via shortcode
* Fixed an issue where an embty list would still shot table headers

= 1.3.3 =
* Added support for displaying list-field values in the list

= 1.3.2 =
* Various updates to faq and description
* Minor code enhancements

= 1.3.1 =
* Added support for "Save & continue"
* Fixed a bug where duplicate entries would not work

= 1.3.0.1 =
* Fixed a bug where notifications were not sent

= 1.3 =
* Revamped settings UI
* Added new capabilities
* Added option to update entry ID on edit
* New banner and icon
* New screenshots
* Fixed a confirmation redirect bug
* Fixed an issue with confirmation and notification ID
* Updated the FAQ
* Updated a depricated hook

= 1.2.14 =
* Fixed a problem with checking if the list was enabled

= 1.2.13 =
* Added support for post category fields

= 1.2.12 =
* Edited entries now retain admin only field values

= 1.2.11 =
* Edited entries now keep the original poster

= 1.2.10 =
* Added support for multiple lists in one page
* Added class to table headers

= 1.2.9 =
* Add css-classes to view, edit, delete, update and post links

= 1.2.8 =
* Feature: Display list entries only to a selected role

= 1.2.7 =
* Feature: Uppdated the shortcode to accept a "showto" parameter

= 1.2.6 =
* Update: Make URL's clickable

= 1.2.5 =
* Feature: Allow duplication of entries in the list

= 1.2.4 =
* Added better date field support

= 1.2.3 =
* Added two custom capabilities for edit and delete

= 1.2.2 =
* Added better support for price option fields
* Fixed an undefined index notice

= 1.2.1 =
* Added better price field support
* Updated styles and enque priority

= 1.2 =
* Updated the code to use a better way of getting the currently logged in user

= 1.1.9.1 =
* Fixed a bug that would show the Sticky List menu item twice

= 1.1.9 =
* Update: Updated Sticky List to user Gravity Forms self initiation to prevent Sticky List from beeing deactivated on update

= 1.1.8 =
* Feature: Optionally display a confirmation on delete

= 1.1.7.1 =
* Fixed a fatal error that would crash the add-on if no user was logged in (Sorry!)

= 1.1.7 =
* Fixed an issue that prevented redirects from working
* Fixed an where list would be empty on BuddyPress
* Fixed some possible bugs with file uploads

= 1.1.6 =
* Feature: Added the option select a field by which the list is sorted

= 1.1.5 =
* Feature: Added the option show a link to WordPress post

= 1.1.4 =
* Added option to enable/disable file hyperlinks in list

= 1.1.3 =
* Fixed an issue where required file upload fields would not validate
* Added support for custom fields in WordPress post fields

= 1.1.2 =
* Fixed an issue where editing a WordPress post would disable comments

= 1.1.1 =
* Feature: Added the option to specify a user ID in the shortcode

= 1.1 =
* Feature: Added pagination support

= 1.0.9 =
* Added support for file upload field (no multi upload yet unfortunately)
* Added option to prevent text wraping

= 1.0.8 =
* Added a filter for entries that are shown in the list
* Added setting for maximum number of entries to be shown in the list

= 1.0.7 =
* Added support for Post fields (create/edit/delete Wordpress posts).

= 1.0.6 =
* Updated confirmations to properly render merge tags.

= 1.0.5 =
* Fixed a nasty bug that prevented confirmation and notification settings from being displayed if Stickly List was active but not enabled for a form.

= 1.0.4 =
* Update: Added alphabetic sorting of pages/posts in embedd dropdown
* Update: Increased number of visible pages/posts in embedd dropdown
* Feature: Added the option to manually input an embedd url

= 1.0.3 =
* Fixed an issue where last name was displayed before first name in the list
* Fixed an issue that prevented some confirmations from displaying
* Fixed some undefined index warnings

= 1.0.2 =
* Fixed a problem where dependencies would not get included

= 1.0.1 =
* Fixed an issue where only the first 20 entries in the list were shown
* Fixed a bug where translation would not get loaded

= 1.0 =
* Initial release
