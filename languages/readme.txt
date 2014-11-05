=== Sell Media I18n ===

Do not put custom translations here. They will be deleted when Sell Media updates.

Keep custom Sell Media translations in /wp-content/languages/sell_media/

To submit your translation and receive a free Business Bundle, please email it to: support@graphpaperpress.com

====== How To Translate Sell Media ======

1. Install [Poedit](http://www.poedit.net/download.php)
2. Open Poedit and go to File -> New catalog from POT file.
3. Open the file sell_media/languages/sell_media.pot
4. A catalog properties box will pop up asking for information about what you are translating. Enter the language you want to translate here along with any other details.
5. After you hit "OK" you'll be asked what you want to name your translation file. The name is important because there's a particular format you should follow for consistency. For example, if you’re translating Chinese for China, the file should be sell_media-zh_CH.po (sell_media for the Sell Media plugin, zh for the language and CH for the country). For this file we are translating to French for France so we should name it sell_media-fr_FR.po
6. Save the file. When we are done translating, we will put it in /wp-content/languages/sell_media/
7. Now you can start translating all the text strings. The user interface is simple: Select the text you want to translate and add the translated text below. Do that for each text string.
8. When you've finishing translating, simply save your file. Poedit will automatically create both .po and .mo files. Put these files in /wp-content/languages/sell_media/
9. Open up the wp_config.php file and add this line of code: define('WPLANG', 'fr_FR'); This tells WordPress to use French for France.
10. Visit the Settings -> General -> Site Language and select your desired Language.