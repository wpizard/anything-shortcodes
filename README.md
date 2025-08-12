# Anything Shortcodes

Powerful WordPress plugin to create a single, flexible `[anys]` shortcode that supports retrieving and displaying data from posts, users, options, and more — with dynamic attribute parsing, formatting, and output wrapping.

---

## Table of Contents

- [Anything Shortcodes](#anything-shortcodes)
  - [Table of Contents](#table-of-contents)
  - [Installation](#installation)
  - [Shortcode Usage](#shortcode-usage)
  - [Supported Types and Examples](#supported-types-and-examples)
    - [Post Field](#post-field)
    - [Post Meta](#post-meta)
    - [User Field](#user-field)
    - [User Meta](#user-meta)
    - [Option](#option)
  - [Dynamic Attribute Parsing](#dynamic-attribute-parsing)
  - [Formatting Options](#formatting-options)
  - [Security](#security)
  - [Support \& Contribution](#support--contribution)

---

## Installation

Install **Anything Shortcodes** plugin from [WordPress Plugins](https://wordpress.org/plugins/anything-shortcodes/) directory.

---

## Shortcode Usage

```
[anys type="TYPE" name="KEY" id="ID" before="TEXT" after="TEXT" fallback="TEXT" format="FORMAT"]
```

- type - Source type (post-field, post-meta, user-field, user-meta, option).
- name - Field or meta key name (required).
- id - ID of post/user (optional; defaults to current context).
- before - Content to prepend before output (optional).
- after - Content to append after output (optional).
- fallback - Content to display if no value found (optional).
- format - Formatting type (date, datetime, number, etc.) (optional).

---

## Supported Types and Examples

### Post Field
Retrieve standard post fields by name.

| Attribute | Example |
|-----------|---------|
| `name="post_title"` | `[anys type="post_field" name="post_title"]` |
| `name="post_date"` | `[anys type="post_field" name="post_date" format="date"]` |
| `name="post_author"` | `[anys type="post_field" name="post_author" id="123"]` |
| `name="post_content"` | `[anys type="post_field" name="post_content" fallback="No content"]` |

Other fields supported:
ID, post_name, post_excerpt, post_status, comment_status, ping_status, post_password, post_parent, menu_order, guid, post_type, post_mime_type, post_modified, post_modified_gmt

---

### Post Meta
Retrieve post meta by key.

| Attribute | Example |
|-----------|---------|
| `name="my_meta_key"` | `[anys type="post_meta" name="my_meta_key"]` |
| `name="price"` | `[anys type="post_meta" name="price" id="456" format="number"]` |
| `name="release_date"` | `[anys type="post_meta" name="release_date" format="date"]` |

### User Field
Retrieve user standard fields.

| Attribute | Example |
|-----------|---------|
| `name="user_email"` | `[anys type="user_field" name="user_email" id="12"]` |
| `name="display_name"` | `[anys type="user_field" name="display_name"]` |
| `name="user_registered"` | `[anys type="user_field" name="user_registered" format="date"]` |

Common user fields: ID, user_login, user_nicename, user_url, user_activation_key, user_status, description

### User Meta
Retrieve user meta by key.

| Attribute | Example |
|-----------|---------|
| `name="favorite_color"` | `[anys type="user_meta" name="favorite_color" id="12"]` |
| `name="profile_phone"` | `[anys type="user_meta" name="profile_phone"]` |

### Option
Retrieve WordPress option values.

| Attribute | Example |
|-----------|---------|
| `name="blogname"` | `[anys type="option" name="blogname"]` |
| `name="admin_email"` | `[anys type="option" name="admin_email"]` |

## Dynamic Attribute Parsing
Supports dynamic placeholders inside attribute values, for example:
- {get:param} - gets value from $_GET['param']
- {post:param} - gets value from $_POST['param']
- {func:function_name,arg1,arg2} - calls a whitelisted PHP function with optional arguments
- {shortcode:[tag]} - parses nested shortcode
- {const:CONSTANT_NAME} - replaces with PHP constant value

Example:
```
[anys type="post_field" name="post_title" id="{get:post_id}" before="Title: "]
```

---

## Formatting Options
- `date` – Format timestamps to WordPress date format.
- `datetime` – Format timestamps to WordPress date and time format.
- `number` – Format numeric values with localization.

Custom formats are supported via filters.

---

## Security
- All inputs are sanitized using WordPress functions.
- Function calls in `{func:}` placeholders are restricted to a whitelist.
- Outputs are sanitized and escaped with `wp_kses_post()` before rendering.
- Dynamic attribute parsing includes caching for performance.

---

## Support & Contribution
For bugs, feature requests, or contributions, please open an issue or pull request on the [plugin repository](https://github.com/wpizard/anything-shortcodes).

Thank you for using Anything Shortcodes!
