# Anything Shortcodes

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

## Table of Contents

- [Anything Shortcodes](#anything-shortcodes)
  - [Table of Contents](#table-of-contents)
  - [Installation](#installation)
  - [Shortcode Usage](#shortcode-usage)
    - [General syntax](#general-syntax)
    - [Attributes](#attributes)
  - [Supported Types and Examples](#supported-types-and-examples)
    - [Post Field](#post-field)
    - [Post Meta](#post-meta)
    - [User Field](#user-field)
    - [User Meta](#user-meta)
    - [Option](#option)
    - [Function](#function)
  - [Dynamic Attribute Parsing](#dynamic-attribute-parsing)
  - [Formatting Options](#formatting-options)
  - [Hooks](#hooks)
    - [Filters](#filters)
    - [Actions](#actions)
  - [Security](#security)
  - [Support \& Contribution](#support--contribution)

## Installation

Install **Anything Shortcodes** plugin from [WordPress Plugins](https://wordpress.org/plugins/anything-shortcodes/) directory.


## Shortcode Usage

### General syntax
```
[anys type="TYPE" name="KEY" id="ID" before="TEXT" after="TEXT" fallback="TEXT" format="FORMAT" delimiter="DELIMITER"]
```

### Attributes
- type - Source type (post-field, post-meta, user-field, user-meta, option).
- name - Field or meta key name (required).
- id - ID of post/user (optional; defaults to current context).
- before - Content to prepend before output (optional).
- after - Content to append after output (optional).
- fallback - Content to display if no value found (optional).
- format - Formatting type (date, datetime, number, etc.) (optional).
- delimiter - Separator used to join multiple values (optional).

## Supported Types and Examples

### Post Field
Retrieve standard post fields by name.

| Attribute | Example |
|-----------|---------|
| `name="post_title"` | `[anys type="post-field" name="post_title"]` |
| `name="post_date"` | `[anys type="post-field" name="post_date" format="date"]` |
| `name="post_author"` | `[anys type="post-field" name="post_author" id="123"]` |
| `name="post_content"` | `[anys type="post-field" name="post_content" fallback="No content"]` |

Other fields supported:
ID, post_name, post_excerpt, post_status, comment_status, ping_status, post_password, post_parent, menu_order, guid, post_type, post_mime_type, post_modified, post_modified_gmt

### Post Meta
Retrieve post meta by key.

| Attribute | Example |
|-----------|---------|
| `name="my_meta_key"` | `[anys type="post-meta" name="my_meta_key"]` |
| `name="price"` | `[anys type="post-meta" name="price" id="456" format="number"]` |
| `name="release_date"` | `[anys type="post-meta" name="release_date" format="date"]` |

### User Field
Retrieve user standard fields.

| Attribute | Example |
|-----------|---------|
| `name="user_email"` | `[anys type="user-field" name="user_email" id="12"]` |
| `name="display_name"` | `[anys type="user-field" name="display_name"]` |
| `name="user_registered"` | `[anys type="user-field" name="user_registered" format="date"]` |

Common user fields: ID, user_login, user_nicename, user_url, user_activation_key, user_status, description

### User Meta
Retrieve user meta by key.

| Attribute | Example |
|-----------|---------|
| `name="favorite_color"` | `[anys type="user-meta" name="favorite_color" id="12"]` |
| `name="profile_phone"` | `[anys type="user-meta" name="profile_phone"]` |

### Option
Retrieve WordPress option values.

| Attribute | Example |
|-----------|---------|
| `name="blogname"` | `[anys type="option" name="blogname"]` |
| `name="admin_email"` | `[anys type="option" name="admin_email"]` |

### Function
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

## Dynamic Attribute Parsing
Supports dynamic placeholders inside attribute values, for example:
- {get:param} - gets value from $_GET['param']
- {post:param} - gets value from $_POST['param']
- {func:function_name,arg1,arg2} - calls a whitelisted PHP function with optional arguments. You can whitelist functions in Settings > Anything shortcodes settings page.
- {shortcode:(tag)} - parses nested shortcode. It needs to use `()` instead of `[]`.
- {const:CONSTANT_NAME} - replaces with PHP constant value.

Example:
```
[anys type="post_field" name="post_title" id="{get:post_id}" before="Title: "]
```

## Formatting Options
- `date` – Format timestamps using the WordPress date format.
- `datetime` – Format timestamps using the WordPress date and time formats.
- `number` – Format numeric values with localization for thousands separator and decimals.
- `json` – Encode a value into a JSON string using WordPress's JSON handling.
- `serialize` – Convert a value into a serialized string if not already serialized.
- `unserialize` – Convert a serialized string back into a PHP value if serialized.
- `print_r` – Return a human-readable string representation of a variable using `print_r()`.
- `var_export` – Return a parsable string representation of a variable using `var_export()`.
- `implode` – Combine array values into a string separated by the given delimiter.
- `keys` – Return array keys as a string separated by the given delimiter.
- `capitalize` – Capitalize the first letter of each word in a string (title case).
- `uppercase` – Convert all characters in a string to uppercase.
- `lowercase` – Convert all characters in a string to lowercase.
- `strip_tags` – Remove HTML and PHP tags from a string.
- `values` – Return array values as a string separated by the given delimiter.
- `keys_values` – Return array key-value pairs in the format `key: value` separated by the given delimiter.

Custom formats are supported via filters.

## Hooks

The `[anys]` shortcode provides several actions and filters that allow you to customize its behavior.

### Filters

- `anys/attributes`
  Filters the shortcode attributes before processing.
  **Parameters:**
  - `$attributes` *(array)* – The shortcode attributes.
  - `$content` *(string)* – The shortcode content.

- `anys/{type}/attributes`
  Dynamic filter for attributes based on the shortcode `type`.
  **Parameters:**
  - `$attributes` *(array)* – The shortcode attributes.
  - `$content` *(string)* – The shortcode content.

- `anys/output`
  Filters the final shortcode output before it is returned.
  **Parameters:**
  - `$output` *(string)* – The rendered output of the shortcode.
  - `$attributes` *(array)* – The shortcode attributes.
  - `$content` *(string)* – The shortcode content.

- `anys/{type}/output`
  Dynamic filter for the final output based on the shortcode `type`.
  **Parameters:**
  - `$output` *(string)* – The rendered output of the shortcode.
  - `$attributes` *(array)* – The shortcode attributes.
  - `$content` *(string)* – The shortcode content.

### Actions

- `anys/output/before`
  Fires before rendering the shortcode output.
  **Parameters:**
  - `$attributes` *(array)* – The shortcode attributes.
  - `$content` *(string)* – The shortcode content.

- `anys/{type}/output/before`
  Dynamic action fired before rendering output for a specific shortcode `type`.
  **Parameters:**
  - `$attributes` *(array)* – The shortcode attributes.
  - `$content` *(string)* – The shortcode content.

- `anys/{type}/missing`
  Fires when the handler file for a specific shortcode `type` is missing.
  **Parameters:**
  - `$attributes` *(array)* – The shortcode attributes.
  - `$content` *(string)* – The shortcode content.

- `anys/output/after`
  Fires after rendering the shortcode output.
  **Parameters:**
  - `$attributes` *(array)* – The shortcode attributes.
  - `$content` *(string)* – The shortcode content.

- `anys/{type}/output/after`
  Dynamic action fired after rendering output for a specific shortcode `type`.
  **Parameters:**
  - `$attributes` *(array)* – The shortcode attributes.
  - `$content` *(string)* – The shortcode content.

## Security
- All inputs are sanitized using WordPress functions.
- Function calls in `{func:}` placeholders are restricted to a allowed functions.
- Outputs are sanitized and escaped with `wp_kses_post()` before rendering.
- Dynamic attribute parsing includes caching for performance.

## Support & Contribution
For bugs, feature requests, or contributions, please open an issue or pull request on the [plugin repository](https://github.com/wpizard/anything-shortcodes).

Thank you for using Anything Shortcodes!
