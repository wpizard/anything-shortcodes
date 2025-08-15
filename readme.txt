=== Anything Shortcodes ===
Contributors: wpizard
Tags: shortcode, post, post meta, user, options
Requires at least: 5.0
Tested up to: 6.8.2
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Retrieve and display any WordPress data with shortcodes — posts, users, options, and more, with flexible formatting and customization.

== Description ==

A powerful WordPress plugin that lets you retrieve and display virtually any data in WordPress using simple shortcodes. Effortlessly pull information from posts, users, options, and more — with support for dynamic attribute parsing, flexible formatting, and customizable output wrapping.

It supports:
- Post fields
- Post meta
- User fields
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

| Attribute | Example |
|-----------|---------|
| `name="post_title"` | `[anys type="post-field" name="post_title"]` |
| `name="post_date"` | `[anys type="post-field" name="post_date" format="date"]` |
| `name="post_author"` | `[anys type="post-field" name="post_author" id="123"]` |
| `name="post_content"` | `[anys type="post-field" name="post_content" fallback="No content"]` |

Other fields supported: ID, post_name, post_excerpt, post_status, comment_status, ping_status, post_password, post_parent, menu_order, guid, post_type, post_mime_type, post_modified, post_modified_gmt

**Post Meta**
Retrieve post meta by key.

| Attribute | Example |
|-----------|---------|
| `name="my_meta_key"` | `[anys type="post-meta" name="my_meta_key"]` |
| `name="price"` | `[anys type="post-meta" name="price" id="456" format="number"]` |
| `name="release_date"` | `[anys type="post-meta" name="release_date" format="date"]` |

**User Field**
Retrieve user standard fields.

| Attribute | Example |
|-----------|---------|
| `name="user_email"` | `[anys type="user-field" name="user_email" id="12"]` |
| `name="display_name"` | `[anys type="user-field" name="display_name"]` |
| `name="user_registered"` | `[anys type="user-field" name="user_registered" format="date"]` |

Common user fields: ID, user_login, user_nicename, user_url, user_activation_key, user_status, description

**User Meta**
Retrieve user meta by key.

| Attribute | Example |
|-----------|---------|
| `name="favorite_color"` | `[anys type="user-meta" name="favorite_color" id="12"]` |
| `name="profile_phone"` | `[anys type="user-meta" name="profile_phone"]` |

**Option**
Retrieve WordPress option values.

| Attribute | Example |
|-----------|---------|
| `name="blogname"` | `[anys type="option" name="blogname"]` |
| `name="admin_email"` | `[anys type="option" name="admin_email"]` |

**Function**
Execute a whitelisted PHP function and optionally pass arguments.

| Attribute | Example |
|-----------|---------|
| `name="date_i18n, F j, Y"` | `[anys type="function" name="date_i18n, F j, Y"]` |
| `name="sanitize_text_field, (anys type='option' name='blogdescription')"` | `[anys type="function" name="sanitize_text_field, (anys type='option' name='blogdescription')"]` |
| `before="Today is "` | `[anys type="function" name="date_i18n, F j, Y" before="Today is "]` |
| `after="."` | `[anys type="function" name="date_i18n, F j, Y" after="."]` |
| `fallback="N/A"` | `[anys type="function" name="my_custom_function" fallback="N/A"]` |
| `format="capitalize"` | `[anys type="function" name="my_custom_function" format="capitalize"]` |
| `delimiter=", "` | `[anys type="function" name="my_custom_function" delimiter=", "]` |

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
- Function calls restricted to allowed list.
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

= 1.0.0 =
* Initial release.
