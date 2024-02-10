# Symfony UX Icons

## Installation

```bash
composer require symfony/ux-icons
```

## Add Icons

No icons are provided by this package. Add your svg icons to the `assets/icons/` directory and commit them.
The name of the file is used as the name of the icon (`name.svg` will be named `name`).

### Import Command

The [Iconify Design](https://iconify.design/) has a huge searchable repository of icons from
many different icon sets. This package provides a command to locally install icons from this
site.

1. Visit [Iconify Design](https://icon-sets.iconify.design/) and search for an icon
   you'd like to use. Once you find one you like, visit the icon's profile page and use the widget
   to copy its name. For instance, https://icon-sets.iconify.design/flowbite/user-solid/ has the name
   `flowbite:user-solid`.
2. Run the following command, replacing `flowbite:user-solid` with the name of the icon you'd like
   to install:

    ```bash
    bin/console ux:icons:import flowbite:user-solid # saved as `user-solid.svg` and name is `user-solid`

    # adjust the local name
    bin/console ux:icons:import flowbite:user-solid@user # saved as `user.svg` and name is `user`
   
    # import several at a time
    bin/console ux:icons:import flowbite:user-solid flowbite:home-solid
    ```

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

## Caching

To avoid having to parse icon files on every request, icons are cached.

During container warmup (`cache:warmup` and `cache:clear`), the icon cache is warmed.

> [!NOTE]
> During development, if you change an icon, you will need to clear the cache (`bin/console cache:clear`)
> to see the changes.

## Full Default Configuration

```yaml
ux_icons:
    # The local directory where icons are stored.
    icon_dir: '%kernel.project_dir%/assets/icons'

    # Default attributes to add to all icons.
    default_icon_attributes:
        # Default:
        fill: currentColor
```
