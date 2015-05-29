// https://github.com/blazersix/grunt-wp-i18n
module.exports = {
    target: {
        options: {
            cwd: '../',
            //expand: true,
            domainPath: 'sell-media/<%= pkg.directories.languages %>', // Where to save the POT file.
            exclude: [
                'sell-media/build/.*',
                'sell-media/node_modules/.*'
            ],
            include: [
                'sell-media/.*',
                'sell-media-access-control/.*',
                'sell-media-commissions/.*',
                'sell-media-discount-codes/.*',
                'sell-media-expander/.*',
                'sell-media-free-downloads/.*',
                'sell-media-magnifier/.*',
                'sell-media-mailchimp/.*',
                'sell-media-model-release/.*',
                'sell-media-reprints/.*',
                'sell-media-s3/.*',
                'sell-media-stripe/.*',
                'sell-media-watermark/.*'
            ],
            mainFile: 'sell-media/<%= pkg.pot.src %>', // Main project file.
            potFilename:  '<%= pkg.pot.textdomain %>' + '.pot', // Name of the POT file.
            potHeaders: {
                poedit: true, // Includes common Poedit headers.
                'x-poedit-keywordslist': true, // Include a list of all possible gettext functions.
                'report-msgid-bugs-to': '<%= pkg.pot.header.bugs %>',
                'last-translator': '<%= pkg.pot.header.last_translator %>',
                'language-team': '<%= pkg.pot.header.team %>',
                'language': 'en_US'
            },
            type: '<%= pkg.pot.type %>', // Type of project (wp-plugin or wp-theme).
            updateTimestamp: true, // Whether the POT-Creation-Date should be updated without other changes.
            updatePoFiles: true, // Whether to update PO files in the same directory as the POT file.
            processPot: function(pot, options) {
                var translation, // Exclude meta data from pot.
                    excluded_meta = [
                        'Plugin Name of the plugin/theme',
                        'Plugin URI of the plugin/theme',
                        'Author of the plugin/theme',
                        'Author URI of the plugin/theme'
                    ];
                for (translation in pot.translations['']) {
                    if ('undefined' !== typeof pot.translations[''][translation].comments.extracted) {
                        if (excluded_meta.indexOf(pot.translations[''][translation].comments.extracted) >= 0) {
                            console.log('Excluded meta: ' + pot.translations[''][translation].comments.extracted);
                            delete pot.translations[''][translation];
                        }
                    }
                }
                return pot;
            }
        }
    }
};