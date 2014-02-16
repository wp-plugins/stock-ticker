=== Stock Ticker ===
Contributors: urkekg
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=Q6Q762MQ97XJ6
Tags: widget, stock, ticker, securities, quote, financial, exchange, bank, market, nasdaq, stock symbols, stock quotes
Requires at least: 3.7.1
Tested up to: 3.8.1
Stable tag: 0.1.1
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Easy display ticker tape with stock prices information with data provided by Yahoo Finance.

== Description ==

A simple and easy configurable plugin that allows you to insert stock ticker with stock prices information with data provided by Yahoo Finance. Insertion is enabled by shortcode or multi instance widget.

= Features =
* Configure default set of stock symbols that will be displayed in ticker
* Configure default presence of company as Company Name or as Stock Symbol
* Configure colours for unchanged quote, negative and positive changes
* Both, global and widget settings provides easy colour picker for selecting all three colour values
* Tooltip for ticker item display company name, stock volume and change percentage
* Plugin uses jQuery to parse data from Yahoo Finance and render ticker content
* No images, for stock indicators we uses WordPress dashicons
* Ready to be translated to non-english languages

For feature requests or help [send feedback](http://urosevic.net/wordpress/plugins/stock-ticker/ "Official plugin page") or use support forum on WordPress.

= Shortcode =
Use simple shortcode `[stock_ticker]` without any parameter in post or page, to display ticker with default (global) settings.

You can tune single shortcode with parameters:
* `symbols` - string with single or comma separated array of stock symbols
* `show` - string that define how will company be represent on ticker; can be `name` for Company Name, or `symbol` for Stock Symbol
* `zero` - string with HEX colour value of unchanged quote
* `minus` - string with HEX colour value of negative quote change
* `plus` - string with HEX colour value of positive quote change

Example:
`[stock_ticker symbols="IBM,CSCO,AAPL,HP" show="symbol" zero="#000" minus="#f00" plus="#0f0"]`

== Installation ==

Easy install Stock Ticker as any other ordinary WordPress plugin

1. Go to `Plugins` -> `Add New`
1. Search for `Stock Ticker` plugin
1. Install and activate `Stock Ticker`
1. Configure default plugin options and insert shortcode `[stock_ticker]` to page or post, or `Stock Ticker` Widget to preferred Widget Area

== Screenshots ==

1. Global plugin settings page
2. Widget settings
3. Stock ticker in action

== Frequently Asked Questions ==

= How to know which stock symbols to use? =

Visit [Yahoo Finance Stock Center](http://finance.yahoo.com/stock-center/) and look for preferred symbols that you need/wish to display on your site.
For start you can try with AAPL,MSFT,IBM,CSCO,GOOG,YHOO,AMZN (Apple, Microsoft, IBM, Cisco, Google, Yahoo, Amazon)

== TODO ==

* Caching quotes

== Changelog ==

= 0.1.1 =
* Add: stock parser message when fail fetching quotes
* Fix: initializing widget syntax error: unexpected T_FUNCTION
* Remove: closing PHP tags

= 0.1.0 =
* Initial public release

= 0.0.9 =
* Private release
* Improved reusable jQuery code

= 0.0.8 =
* Fix: usable colour picker in widgets after add new widget (before widget save)

= 0.0.7 =
* Add: configurable widget
* Add: help section to settings page

= 0.0.6 =
* Add: settings page

= 0.0.5 =
* Add: shortcode option show - what to dsplay in ticker (company name or stock symbol)

= 0.0.4 =
* Add: shortcode option for custom symbols set
* Add: shortcode option for custom colours (zero/minus/plus)

= 0.0.3 =
* Add: shortcode with embedded options

= 0.0.2 =
* packaged JS code to WordPress plugin

= 0.0.1 =
* developed JavaScript code for parsing stock data

== Upgrade Notice ==

= 0.1.1 =
Fixed error for websites that run on PHP <5.3.0

= 0.1.0 =
Initial public release
