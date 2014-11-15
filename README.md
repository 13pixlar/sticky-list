<h1>Gravity Forms Sticky List</h1>

Sticky List is an Gravity Forms (for wordpress) add-on that lets you list and **edit entries** from the front end.

<h2>Description</h2>

#### Sticky List
Sticky List is a WordPress plugin for <a href="http://www.gravityforms.com/" target="_blank">Gravity Forms</a> that lets you list and edit entries from the front end. You can display a list on the front end where users can view, delete and edit submitted entries. 

**Note:** There is a bug in the Gravity Forms API that prevents Sticky List from working correctly. More information and fix, please see the FAQ section of this readme.

#### Features

* Display a list of entries at the front end
* Choose who can se the list; entry crator, all logged in users or anyone.
* Support for multiple lists in the same page
* Support for all gravity forms fields
* Conditional logic support
* Edit and re-save existing entries from the front-end
* Delete existing entries from the front end
* Custom column labels
* Uses new Gravity Forms API and the official Gravity Forms Add-on framework
* Fully customizable with dead simple styles to override
* Fully supported and maintained
* Completely free and open source

#### Panned features

* Customized text for view, edit, delete and update buttons
* Localization
* Add date updated meta field
* Table sorting and search (using list.js)
* Ajax delete with confirmation
* Conditional notifications
* Conditional confirmations
* Support for multi page forms

#### List and edit Gravity Form entries on the front end

Front end editing of entries has allways been a problem in gravity forms. Solutions that exist are bugg and not very feature rich. Gravity Forms Sticky List aims to fill that gap and provida a simple and solid way to view, edit and delete entry submissions on the front end. The goal of the plugin is not to to diplay entries in a fancy way (excelent GravityViews allready does that brilliantly) but to provide a simple, lightweight and rock solid way to list, edit and delete submissions on the front-end. Lists can be embedded in any post or page and you can have as many lists as you want in a single page.

#### Edit Gravity Form entries from front end

#### Usage

1. Upload and activate the plugin
2. Go to the settings page of a form and click the Sticky List settings tab
3. Enable Sticky List for that form att choose your settings
4. Go to the form editor and select what fields should be displayed in the list
5. Put the shortcode in a page/post with the corresponding form id, i.e `[stickylist id="1"]`

#### Developers
This is the fully documented version of the plugin. This plugin is Open Source and pull requests are welcome.

**Note:** <a href="http://www.gravityforms.com/" target="_blank">Gravity Forms</a> is required for this plugin.

<h3>Installation</h3>

1. Upload extracted folder to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Choose the required Sticky List settings on the individual form settings page.

<h3>Frequently Asked Questions</h3>

<h5>Question</h5>

Answer

<h5>Some fields do not get updated</h5>

There is a bug in the Gravity Forms api that prevent fields from getting saved in the entry. This will supposedly get fixed in Gravity Forms 1.9. In the meantime, download an <a href="https://downloads.wordpress.org/plugin/gravity-forms-sticky-form.1.0.1.zip">earlier version</a> of this plugin, (that uses a different way to save the entries) or apply the patch manually to `plugins/gravityforms/includes/api.php`

On line `510`, remove 
```PHP
if (empty($entry_id))
    $entry_id = $entry["id"];
```
and replace with
```PHP
if (empty($entry_id)) {
    $entry_id = $entry["id"];
}else{
    $entry["id"] = $entry_id;
}
```

<h3>Changelog</h3>

**1.0**
* Initial release
