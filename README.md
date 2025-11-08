# Anything Shortcodes

A powerful WordPress plugin that lets you retrieve and display virtually any data in WordPress using simple shortcodes. Effortlessly pull information from posts, users, options, and more — with support for dynamic attribute parsing, flexible formatting, and customizable output wrapping.

It supports:
- Post Field
- Post Meta
- Term Field
- Term Meta (coming soon)
- User Field
- User Meta
- Link
- Option
- Function (whitelisted)

You can also:
- Apply custom formatting (date, datetime, number, capitalize, uppercase, lowercase, strip_tags, etc.)
- Add before/after text
- Use fallback values
- Use nested shortcodes inside attributes
- Use URL parameters as values
- Cache output
- Secure and sanitize output

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin via the "Plugins" menu in WordPress.
3. Use the `[anys]` shortcode anywhere you want.

## Shortcode Usage

### General syntax:
```
[anys type="TYPE" name="KEY" id="ID" before="TEXT" after="TEXT" fallback="TEXT" format="FORMAT" delimiter="DELIMITER"]
```

**Attributes:**
- `type` — `post-field`, `post-meta`, `user-field`, `user-meta`, `link`, `option`, `function` (required)
- `name` — The field name, meta key, option name, or function call (required)
- `id` — Post ID or User ID (optional; defaults to current context)
- `before` — Text to prepend before output (optional)
- `after` — Text to append after output (optional)
- `fallback` — Value if empty (optional)
- `format` — Output formatting type (optional: `date`, `datetime`, `number`, `capitalize`, `uppercase`, `lowercase`, `strip_tags`, `values`, `keys_values`, etc.)
- `delimiter` — Separator used to join multiple values (optional)

## Supported Types and Examples

### Post Field
Retrieve standard post fields by name.

Examples:

- `[anys type="post-field" name="post_title"]` — Shows the post title.
- `[anys type="post-field" name="post_date" format="date"]` — Shows the post publish date (formatted).
- `[anys type="post-field" name="post_author" id="123"]` — Shows the author ID of post `123`.
- `[anys type="post-field" name="post_content" fallback="No content"]` — Shows the post content or fallback text.

Other fields supported: `ID`, `post_name`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_parent`, `menu_order`, `guid`, `post_type`, `post_mime_type`, `post_modified`, `post_modified_gmt`

### Post Meta
Retrieve post meta by key.

Examples:

- `[anys type="post-meta" name="my_meta_key"]` — Shows value of `my_meta_key`.
- `[anys type="post-meta" name="price" id="456" format="number"]` — Shows the `price` of post `456`, formatted as number.
- `[anys type="post-meta" name="release_date" format="date"]` — Shows release date formatted as date.

### Term Field
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

### User Field
Retrieve user standard fields.

Examples:

- `[anys type="user-field" name="user_email" id="12"]` — Shows email of user `12`.
- `[anys type="user-field" name="display_name"]` — Shows current user display name.
- `[anys type="user-field" name="user_registered" format="date"]` — Shows user registration date.

Common user fields: `ID`, `user_login`, `user_nicename`, `user_url`, `user_activation_key`, `user_status`, `description`

### User Meta
Retrieve user meta by key.

Examples:

- `[anys type="user-meta" name="favorite_color" id="12"]` — Shows favorite_color of user `12`.
- `[anys type="user-meta" name="profile_phone"]` — Shows current user’s phone.

### Link
Retrieve URLs or generate link anchors for common WordPress locations.

Examples:

- `[anys type="link" name="logout"]` — Returns the logout URL.
- `[anys type="link" name="logout" redirect="/"]` — Returns the logout URL and redirects to home after logout.
- `[anys type="link" name="login" redirect="/dashboard"]` — Returns the login URL with redirect to `/dashboard`.
- `[anys type="link" name="register"]` — Returns the registration URL.
- `[anys type="link" name="home"]` — Returns the home page URL.
- `[anys type="link" name="siteurl"]` — Returns the main site URL.
- `[anys type="link" name="admin"]` — Returns the WordPress admin URL.
- `[anys type="link" name="profile"]` — Returns the user profile page URL.
- `[anys type="link" name="post" id="123"]` — Returns the permalink for post with ID `123`.
- `[anys type="link" name="term" id="45"]` — Returns the archive link for term with ID `45`.
- `[anys type="link" name="current"]` — Returns the current page URL.
- `[anys type="link" name="auth"]` — Dynamically returns the login or logout URL based on user status.

Formatting options:

- `[anys type="link" name="logout" format="anchor"]` — Outputs a clickable link: `<a href="...">Logout</a>`.
- `[anys type="link" name="auth" format="anchor"]` — Dynamically shows Login or Logout link depending on user state.
- `[anys type="link" name="auth" format="anchor" label_logged_in="Sign Out" label_logged_out="Sign In"]` — Customizes labels for both states.

Attributes supported:
- `name`: The link type (e.g., `logout`, `login`, `home`, `admin`, etc.).
- `redirect`: Optional redirect URL (used for login/logout links).
- `format`: Either `url` (default) or `anchor` for clickable links.
- `target`: Optional anchor target (e.g., `_blank`).
- `label`: Optional link text (default varies by type).
- `label_logged_in`: Custom label when the user is logged in (used with `auth`).
- `label_logged_out`: Custom label when the user is logged out (used with `auth`).
- `id`: Used for post and term links.

### Option
Retrieve WordPress option values.

Examples:

- `[anys type="option" name="blogname"]` — Shows site title.
- `[anys type="option" name="admin_email"]` — Shows site admin email.

### Function
Execute a whitelisted PHP function and optionally pass arguments.

Examples:

- `[anys type="function" name="date_i18n, F j, Y"]` — Shows today’s date.
- `[anys type="function" name="date_i18n, F j, Y" before="Today is "]` — Shows today’s date with custom prefix.
- `[anys type="function" name="date_i18n, F j, Y" after="."]` — Shows today’s date with custom suffix.
- `[anys type="function" name="wp_get_attachment_url, 99 | full"]` — Gets the URL of attachment ID 99 in full size. Use the pipe (`|`) to separate multiple arguments.
- `[anys type="function" name="sanitize_text_field, (anys type='option' name='blogdescription')"]` — Sanitizes and shows site description.
- `[anys type="function" name="my_custom_function" fallback="N/A"]` — Shows output of custom function or fallback.
- `[anys type="function" name="my_custom_function" format="capitalize"]` — Shows output of custom function and automatically capitalizes the output (e.g., "hello world" → "Hello World").
- `[anys type="function" name="my_custom_function" delimiter=", "]` — Shows output of custom function. If the function returns an array, the values are joined using the given delimiter (e.g., `["apple", "banana"]` → "apple, banana").


Notes:
- Only functions whitelisted in plugin settings can be executed.
- Additional arguments can be passed to the function using a pipe (`|`).
- Arguments can include other `[anys]` shortcodes using `()` instead of `[]`.
- Output can be formatted or wrapped with `before`/`after` content and fallback.

## Dynamic Attribute Parsing
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

## Formatting Options
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

## Integrations

### Navigation Menu
The plugin now supports using shortcodes directly in navigation menu items.
You can add dynamic values in both the **Menu Label** and **Menu Link** fields using the `[anys]` shortcode.
For example:
- Use `[anys type="link" name="logout"]` in the **Link** field.
- Use `[anys type="user-field" name="display_name"]` in the **Label** field.

This allows creating dynamic and personalized menus effortlessly.

## Hooks

**Filters**
- `anys/attributes` — Filter attributes before processing.
- `anys/{type}/attributes` — Filter attributes dynamically by type.
- `anys/output` — Filter final output.
- `anys/{type}/output` — Filter output dynamically by type.
- `anys/link/handlers` - Filter link handlers.

**Actions**
- `anys/output/before` — Fires before output.
- `anys/{type}/output/before` — Fires before output for specific type.
- `anys/{type}/missing` — Fires when handler file missing.
- `anys/output/after` — Fires after output.
- `anys/{type}/output/after` — Fires after output for specific type.

## Security
- All inputs sanitized using WordPress functions.
- Function calls restricted to whitelisted list.
- Outputs sanitized with `wp_kses_post()`.
- Dynamic parsing uses caching for performance.

## Support & Contribution
For bugs, feature requests, or contributions, open an issue or PR on the [plugin repository](https://github.com/wpizard/anything-shortcodes).

Thank you for using Anything Shortcodes!
