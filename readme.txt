=== Anything Shortcodes ===
Contributors: wpizard
Tags: shortcode, post, post meta, user, options
Requires at least: 5.0
Tested up to: 6.8.2
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create one shortcode to display anything — post fields, post meta, user fields, user meta, options, and more. Fully dynamic, secure, and scalable.

== Description ==

Anything Shortcodes lets you retrieve and display **any kind of WordPress data** using a single shortcode: `[anys]`.

It supports:
- Post fields
- Post meta
- User fields
- User meta
- Options
- Functions
- Nested shortcodes inside attributes
- URL parameters as values

You can also:
- Apply custom formatting (date, datetime, number, etc.)
- Add before/after text
- Use fallback values
- Cache output
- Secure and sanitize output

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin via the "Plugins" menu in WordPress.
3. Use the `[anys]` shortcode anywhere you want.

== Usage ==

**General syntax:**
```
[anys type="TYPE" name="KEY" id="ID" before="TEXT" after="TEXT" fallback="TEXT" format="FORMAT"]
```

**Parameters:**
- `type` — `post_field`, `post_meta`, `user_field`, `user_meta`, `option`
- `name` — The field name, meta key, or option name (required)
- `id` — Post ID or User ID (optional)
- `before` — Text before output (optional)
- `after` — Text after output (optional)
- `fallback` — Value if empty (optional)
- `format` — Format output (optional: `date`, `datetime`, `number`, etc.)

== Examples ==

**Post Field**
```
[anys type="post_field" name="post_title"]
[anys type="post_field" name="post_content" id="123" before="<h2>" after="</h2>"]
```

**Post Meta**
```
[anys type="post_meta" name="custom_key"]
[anys type="post_meta" name="custom_key" id="45" fallback="No value found"]
```

**User Field**
```
[anys type="user_field" name="display_name"]
[anys type="user_field" name="user_email" id="2"]
```

**User Meta**
```
[anys type="user_meta" name="profile_picture"]
[anys type="user_meta" name="twitter_handle" fallback="Not set"]
```

**Options**
```
[anys type="option" name="siteurl"]
[anys type="option" name="blogname" before="<strong>" after="</strong>"]
```

**With Function in Attribute**
```
[anys type="post_meta" name="last_login" fallback="{func:date('Y-m-d')}"]
```

**With URL Parameter**
```
[anys type="post_meta" name="{get:user_id}" fallback="No user ID"]
```

**With Nested Shortcode**
```
[anys type="post_field" name="[another_shortcode]"]
```

== Frequently Asked Questions ==

= Can I run PHP functions in attributes? =
Yes — use `{func:function_name(arguments)}` syntax in any attribute.

= Can I use URL parameters? =
Yes — `{get:param_name}` will be replaced with the value from the query string.

= Is the output safe? =
Yes — all values are escaped using `wp_kses_post()` by default.

= Does it support caching? =
Yes — you can enable caching in the plugin settings for performance.

== Changelog ==

= 1.0.0 =
* Initial release.
