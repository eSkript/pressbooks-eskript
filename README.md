# Pressbooks eSkript Plugin

This WordPress plugin extends the Pressbooks plugin with additional features used by the ETH eSkript platform.

## Features

### Custom LaTeX Handler

Allows using a custom LaTeX image producer by defining `ESCRIPT_LATEX_URL` in `wp-config.php`. This feature can also be used to serve SVG instead of PNG images.

Formulas are adjusted to the current text color by requesting new images with the right color when needed. (See `fixes.js`.)

Implemented in `components/latex.php`.

### Custom Shortcode Handlers

Within the custom `eskript_overrides` action, existing shortcodes can be removed and replaced with `the_content` filters using the `eskript_shortcode_handler` helper function.

* `eskript_overrides` will be called after all other plugins have been initialized, so `remove_shortcode` is not called before the shortcode to remove has been added.
* Handling shortcodes within a custom `the_content` filter allows to support shortcodes within shortcodes (e.g. formulas inside captions).
* `eskript_overrides` will not be called on `wp_loaded`, but also while exporting books, so the custom shorttags will still work.
* Used for the `latex` and the `ref` shortcodes.

Implemented in `components/helpers.php`.

### Download Latest Exports

Includes feature to download the most recent export from a books landing page. This feature is copied from the [Pressbooks Textbook](https://wordpress.org/plugins/pressbooks-textbook/) plugin. (Since this feature has since been included in the main Pressbooks plugin, it is likely to be removed from this plugin in the future.)

This feature can be enabled at `Settings > Privacy > Share Latest Export Files`.

Implemented in `components/legacy.php`.

### Enumeration

Enumerates chapters, sections and the referenceable items. The class `not-in-list` can be added to items in order to not assign a number. 

In contrast to the "Two-Level TOC" option offered by Pressbooks, this allows to e.g. prevent headers within boxes to get a number. Instead of only numbering the `h1` headers, this plugin will enumerate all subsections.

Requires `Appearance > Theme Options > Chapter Numbers` to be enabled.

Implemented in `components/theme.php` and `components/references.php`.

### References

Allows referencing sections, images, tables, formulas and anchors using the `[ref id="foo" /]` shortcode, similarly as described in the [manual for the previous eskript version](https://eskript.ethz.ch/lists/chapter/references/).

Requires `Appearance > Theme Options > Chapter Numbers` to be enabled (since all items will be numbered by chapter).

Implemented in `components/references.php`.

### Table of Content

Since the original implementation would include headers tagged with the `not-in-list` class, the eskript theme uses its own implementation for tables of content based on its own reference system. They are used for a books start page, the side bar and can be included into a post using shortcodes.

While it respects the `Appearance > Theme Options > Two-Level TOC` option, it is also capable of generating deeper ToCs.

The shortcode `[toc /]` will insert a table of content inside a post. `[posttoc /]` will add a table of content including only the sections of the current post. Both shortcodes support the attribute `levels` to modify the number of subsection levels that should be displayed.

Implemented in `components/toc.php`.

### Subscriber Management

Using the [Shibboleth plugin](https://wordpress.org/plugins/shibboleth/), users belonging to a certain organization can be granted subscriber permissions automatically. The groups being able to access a certain book can be selected at `Settings > Privacy`.

For this feature to work properly, a small modification needs to be made to the source code of the Shibboleth plugin.

Implemented in `components/user.php`.

### More Features

* The `[upload_dir_url]` shortcode resolves to the url of the upload dir, and can be used inside `src="..."` attributes to reference local resources.
* Limits selectable themes to the `eskript` theme.
* Adds CSS and JavaScript to the visual editor in order to support custom boxes and references.
* Experimental feature to simulate having sections on their own page. Enable at `Appearance > Theme Options > Chapter Subdivision`. (Likely to be removed in the future.)

Implemented in `components/theme.php`.

### Debug Tools

Reference debugger in the admin screen under `Tools > eskript Debug`. Shows duplicate reference IDs and dead references.

Implemented in `components/admin.php`.
