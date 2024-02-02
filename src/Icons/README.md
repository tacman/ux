# Symfony UX Icons

## Installation

```bash
composer require symfony/ux-icons
```

## Add Icons

No icons are provided by this package. Add your svg icons to the `templates/icons/` directory and commit them.
The name of the file is used as the name of the icon (`name.svg` will be named `name`).

When icons are rendered, any attributes (except `viewBox`) on the file's `<svg>` element will
be removed. This allows you to copy/paste icons from sites like
[heroicons.com](https://heroicons.com/) and not worry about hard-coded attributes interfering with
your design.

## Usage

```twig
{{ ux_icon('user-profile', {class: 'w-4 h-4'}) }} <!-- renders "user-profile.svg" -->

{{ ux_icon('sub-dir:user-profile', {class: 'w-4 h-4'}) }} <!-- renders "sub-dir/user-profile.svg" (sub-directory) -->
```

### HTML Syntax

> [!NOTE]
> `symfony/ux-twig-component` is required to use the HTML syntax.

```html
<twig:UX:Icon name="user-profile" class="w-4 h-4" /> <!-- renders "user-profile.svg" -->

<twig:UX:Icon name="sub-dir:user-profile" class="w-4 h-4" /> <!-- renders "sub-dir/user-profile.svg" (sub-directory) -->
```

> [!TIP]
> The Twig component _name_ can be [configured](#full-default-configuration). For instance, you can change
> it to `Icon` to use `<twig:Icon ...>`.

## Caching

To avoid having to parse icon files on every request, icons are cached.

During container warmup (`cache:warmup` and `cache:clear`), the icon cache is warmed.

> [!NOTE]
> During development, if you change an icon, you will need to clear the cache (`bin/console cache:clear`)
> to see the changes.

### Manual Cache Warmup

If you chose to disable container icon cache warmup, a warmup command is provided:

```bash
bin/console ux:icons:warm-cache
```

## Full Default Configuration

```yaml
ux_icons:
    # The local directory where icons are stored.
    icon_dir: '%kernel.project_dir%/templates/icons'

    # The name of the Twig component to use for rendering icons.
    twig_component_name: 'UX:Icon'

    # Default attributes to add to all icons.
    default_icon_attributes:
        # Default:
        fill: currentColor
```
