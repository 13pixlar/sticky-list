<h1>Gravity Forms Sticky List</h1>

Sticky List is a Gravity Forms add-on that lets you list and **edit entries** from the front end.

<h2>Description</h2>

#### Sticky List
Sticky List is an add-on for the WordPress plugin <a href="http://www.gravityforms.com/" target="_blank">Gravity Forms</a> that lets you list and edit entries from the front end. You can display a list on the front end where users can view, delete and edit submitted entries. 

**Note:** There is a bug in the Gravity Forms API that prevents Sticky List from working correctly. More information and fix, please see the FAQ section of this readme.

#### Features

* Display a list of entries at the front end
* Choose who can se the list; entry creator, all logged in users or anyone.
* Support for multiple lists in the same page
* Support for all Gravity Forms fields
* Conditional logic support
* Edit and re-save existing entries from the front-end
* Delete existing entries from the front end
* Table sorting and search (using <a href="http://www.listjs.com/">list.js</a>)
* Custom column labels
* Uses new Gravity Forms API and the official Gravity Forms Add-on framework
* Fully customizable with dead simple styles to override
* Fully localized (<a href="#">add your translation</a>)
* Fully supported and maintained
* Completely free and open source

#### Planned features

* Add date updated meta field
* Conditional notifications
* Conditional confirmations
* Support for multi page forms

#### List and edit Gravity Form entries on the front end

Front end editing of entries has allways been a problem in Gravity Forms. Solutions that exist are buggy and not very feature rich. Gravity Forms Sticky List aims to fill this gap and provide a simple and solid way to view, edit and delete entry submissions on the front end. The goal of the plugin is not to to diplay entries in a fancy way (excelent <a href="https://gravityview.co/">GravityViews</a> allready does that brilliantly) but to provide a simple, lightweight and rock solid way to list, edit and delete submissions on the front-end. Lists can be embedded in any post or page and you can have as many lists as you want in a single page.

#### Delete Gravity Form submissions from front end

Gravity Forms Sticky List uses a simple ajax approach to deleting entries. Deleted entries are moved to trash or permanently deleted depending och the per form settings. 

#### Sort and search entries

Sticky List uses the fast and light weight <a href="http://www.listjs.com/">list.js</a> to allow for sorting the list and searching the entries. Searching entries is very fast and results are updated immediately. 

#### Usage

1. Upload and activate the plugin
2. Go to the settings page of a form and click the Sticky List settings tab
3. Enable Sticky List for that form att choose your settings
4. Go to the form editor and select what fields should be displayed in the list
5. Put the shortcode in a page/post with the corresponding form id, i.e `[stickylist id="1"]`

#### Styling the list

Sticky List ships with a minimal stylesheet that is easy to override. The table has the class of `.sticky-list` attached to it which can be used to override the default styles. The stylesheet is located in `sticky-list/css/sticky-list_styles.css` in the plugins main directory.

#### Developers
This is the fully documented version of the plugin. This plugin is Open Source and pull requests are welcome.

**Note:** <a href="http://www.gravityforms.com/" target="_blank">Gravity Forms</a> is required for this plugin.

<h3>Installation</h3>

1. Upload extracted folder to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Choose the required Sticky List settings on the individual form settings page.

<h3>Frequently Asked Questions</h3>

<h5>Some fields do not get updated</h5>

There is a bug in the Gravity Forms api that prevent fields from getting saved in the entry. This will supposedly get fixed in Gravity Forms 1.9. In the meantime you can easily apply the patch manually to `plugins/gravityforms/includes/api.php`

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
