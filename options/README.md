## Welcome to the GPP Theme Options Framework.

To get started using the framework:

1. Add the GPP Theme Options Framework as a GIT submodule to your GIT repo'd theme. This makes it easy for you to stay updated with the latest version of the framework. Any time we update the module, you can run `git submodule update` and grab the latest stable version of the framework.

    ```
    $ cd theme_name
    $ git submodule add git://github.com/graphpaperpress/gpp-theme-options.git options
    ```
2. Copy theme-options-example.txt to theme-options.php
3. Bootstrap the framework by adding this to functions.php:

    ```php
    include_once(get_template_directory().'/options/options.php');
    ```
4. Bootstrap the theme options by adding this to functions.php below the include above:

    ```php
    include_once(get_template_directory().'/theme-options.php');
    ```
5. Customize theme-options.php. Options include:

    ```
    'type' => 'text' - standard text box
    'type' => 'textarea' - standard textarea
    'type' => 'select' - drop-down select
    'type' => 'checkbox' - checkbox
    'type' => 'color' - colorpicker
    'type' => 'image' - image uploader
    'type' => 'gallery' - gallery uploader
    'type' => 'css' - css textarea
    'type' => 'fonts' - Google font picker
    ```

To register theme options tabs, simply use `gpp_register_plugin_options()` and `gpp_register_plugin_options_tab()` within your existing code as shown in the above example. This makes it extremely easy to attach your theme options to specific functionality. That way if you have a lite version and a pro version of a theme, you can keep the theme options separated easily.