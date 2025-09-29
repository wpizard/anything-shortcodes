=== Anything Shortcodes ===
Contributors: wpizard
Tags: shortcode, post, post meta, user, options
Requires at least: 5.0
Tested up to: 6.8.2
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Retrieve and display any WordPress data with shortcodes — posts, users, options, and more, with flexible formatting and customization.

== Description ==

A powerful WordPress plugin that lets you retrieve and display virtually any data in WordPress using simple shortcodes. Effortlessly pull information from posts, users, options, and more — with support for dynamic attribute parsing, flexible formatting, and customizable output wrapping.

It supports:
- Post field
- Post meta
- Term field
- User field
- User meta
- Options
- Functions (whitelisted)
- Nested shortcodes inside attributes
- URL parameters as values

You can also:
- Apply custom formatting (date, datetime, number, capitalize, uppercase, lowercase, strip_tags, etc.)
- Add before/after text
- Use fallback values
- Cache output
- Secure and sanitize output

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin via the "Plugins" menu in WordPress.
3. Use the `[anys]` shortcode anywhere you want.

== Shortcode Usage ==

**General syntax:**
```
[anys type="TYPE" name="KEY" id="ID" before="TEXT" after="TEXT" fallback="TEXT" format="FORMAT" delimiter="DELIMITER"]
```

**Attributes:**
- `type` — `post-field`, `post-meta`, `user-field`, `user-meta`, `option`, `function` (required)
- `name` — The field name, meta key, option name, or function call (required)
- `id` — Post ID or User ID (optional; defaults to current context)
- `before` — Text to prepend before output (optional)
- `after` — Text to append after output (optional)
- `fallback` — Value if empty (optional)
- `format` — Output formatting type (optional: `date`, `datetime`, `number`, `capitalize`, `uppercase`, `lowercase`, `strip_tags`, `values`, `keys_values`, etc.)
- `delimiter` — Separator used to join multiple values (optional)

== Supported Types and Examples ==

**Post Field**
Retrieve standard post fields by name.

Examples:

- `[anys type="post-field" name="post_title"]` — Shows the post title.
- `[anys type="post-field" name="post_date" format="date"]` — Shows the post publish date (formatted).
- `[anys type="post-field" name="post_author" id="123"]` — Shows the author ID of post `123`.
- `[anys type="post-field" name="post_content" fallback="No content"]` — Shows the post content or fallback text.

Other fields supported: `ID`, `post_name`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_parent`, `menu_order`, `guid`, `post_type`, `post_mime_type`, `post_modified`, `post_modified_gmt`

**Post Meta**
Retrieve post meta by key.

Examples:

- `[anys type="post-meta" name="my_meta_key"]` — Shows value of `my_meta_key`.
- `[anys type="post-meta" name="price" id="456" format="number"]` — Shows the `price` of post `456`, formatted as number.
- `[anys type="post-meta" name="release_date" format="date"]` — Shows release date formatted as date.

**Term Field**
Retrieve standard term fields by name.

Examples:

- `[anys type="term-field" name="name"]` — Shows the term name.
- `[anys type="term-field" name="slug"]` — Shows the term slug.
- `[anys type="term-field" name="term_id" id="15"]` — Shows the ID of term `15`.
- `[anys type="term-field" name="taxonomy" id="15"]` — Shows the taxonomy of term `15`.
- `[anys type="term-field" name="description" fallback="No description"]` — Shows term description or fallback text.
- `[anys type="term-field" name="count" id="15" format="number"]` — Shows the number of posts in term `15`.

Other fields supported: `term_group`, `parent`

Notes:
- If no "id" is provided, it defaults to the current queried term (e.g., category/tag archive page).
- Supports "before", "after", "fallback", and "format" (for number, date, etc.).

**User Field**
Retrieve user standard fields.

Examples:

- `[anys type="user-field" name="user_email" id="12"]` — Shows email of user `12`.
- `[anys type="user-field" name="display_name"]` — Shows current user display name.
- `[anys type="user-field" name="user_registered" format="date"]` — Shows user registration date.

Common user fields: `ID`, `user_login`, `user_nicename`, `user_url`, `user_activation_key`, `user_status`, `description`

**User Meta**
Retrieve user meta by key.

Examples:

- `[anys type="user-meta" name="favorite_color" id="12"]` — Shows favorite_color of user `12`.
- `[anys type="user-meta" name="profile_phone"]` — Shows current user’s phone.

**Option**
Retrieve WordPress option values.

Examples:

- `[anys type="option" name="blogname"]` — Shows site title.
- `[anys type="option" name="admin_email"]` — Shows site admin email.

**Function**
Execute a whitelisted PHP function and optionally pass arguments.

Examples:

- `[anys type="function" name="date_i18n, F j, Y"]` — Shows today’s date.
- `[anys type="function" name="sanitize_text_field, (anys type='option' name='blogdescription')"]` — Sanitizes and shows site description.
- `[anys type="function" name="date_i18n, F j, Y" before="Today is "]` — Shows today’s date with custom prefix.
- `[anys type="function" name="date_i18n, F j, Y" after="."]` — Shows today’s date with custom suffix.
- `[anys type="function" name="my_custom_function" fallback="N/A"]` — Shows output of custom function or fallback.
- `[anys type="function" name="my_custom_function" format="capitalize"]` — Shows output of custom function and automatically capitalizes the output (e.g., "hello world" → "Hello World").
- `[anys type="function" name="my_custom_function" delimiter=", "]` — Shows output of custom function. If the function returns an array, the values are joined using the given delimiter (e.g., `["apple", "banana"]` → "apple, banana").

Notes:
- Only functions whitelisted in plugin settings can be executed.
- Arguments can include other `[anys]` shortcodes using `()` instead of `[]`.
- Output can be formatted or wrapped with `before`/`after` content and fallback.

== Dynamic Attribute Parsing ==
Supports dynamic placeholders inside attribute values:
- `{get:param}` — gets value from $_GET['param']
- `{post:param}` — gets value from $_POST['param']
- `{func:function_name,arg1,arg2}` — calls a whitelisted PHP function
- `{shortcode:(tag)}` — parses nested shortcode (use `()` instead of `[]`)
- `{const:CONSTANT_NAME}` — replaces with PHP constant value

Example:
```
[anys type="post_field" name="post_title" id="{get:post_id}" before="Title: "]
```

== Formatting Options ==
- `date` — Format timestamps using WordPress date format.
- `datetime` — Format timestamps using WordPress date and time format.
- `number` — Localized number format.
- `json` — Encode value as JSON string.
- `serialize` — Serialize PHP value.
- `unserialize` — Unserialize string if serialized.
- `print_r` — Human-readable output of variable.
- `var_export` — Parsable string representation.
- `implode` — Join array values.
- `values` — Join array values only.
- `keys` — Join array keys only.
- `keys_values` — Join array key-value pairs.
- `capitalize` — Capitalize words.
- `uppercase` — Uppercase all characters.
- `lowercase` — Lowercase all characters.
- `strip_tags` — Remove HTML/PHP tags.

Custom formats are supported via filters.

== Hooks ==

**Filters**
- `anys/attributes` — Filter attributes before processing.
- `anys/{type}/attributes` — Filter attributes dynamically by type.
- `anys/output` — Filter final output.
- `anys/{type}/output` — Filter output dynamically by type.

**Actions**
- `anys/output/before` — Fires before output.
- `anys/{type}/output/before` — Fires before output for specific type.
- `anys/{type}/missing` — Fires when handler file missing.
- `anys/output/after` — Fires after output.
- `anys/{type}/output/after` — Fires after output for specific type.

== Security ==
- All inputs sanitized using WordPress functions.
- Function calls restricted to whitelisted list.
- Outputs sanitized with `wp_kses_post()`.
- Dynamic parsing uses caching for performance.

== Support & Contribution ==
For bugs, feature requests, or contributions, open an issue or PR on the [plugin repository](https://github.com/wpizard/anything-shortcodes).

Thank you for using Anything Shortcodes!

== Frequently Asked Questions ==

= Can I run PHP functions in attributes? =
Yes — use `{func:function_name(arguments)}` syntax in any attribute.

= Can I use URL parameters? =
Yes — `{get:param_name}` will be replaced with the value from the query string.

= Is the output safe? =
Yes — all values are escaped using `wp_kses_post()` by default.

== Changelog ==

= [1.2.0] - 2025-09-29 =
- Added Term Field type.

= 1.1.0 - 2025-08-15 =
- Added Function type.
- Added Settings page.
- Added Whitelisted Functions setting for better security control.
- Added more formats (json, serialize, unserialize, print_r, var_export, implode, keys, capitalize, uppercase, lowercase, strip_tags, values, keys_values).
- Improved shortcode registration for future shortcodes.
- Improved hooks naming conventions.
- Improved docs.

= 1.0.0 - 2025-08-04 =
- Initial release.
