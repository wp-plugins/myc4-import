=== Plugin Name ===
Contributors: prof. David Costa, Mihai Secasiu 
Tags: myc4 microfinance
Requires at least: 2.7
Tested up to: 2.7
Stable tag: 0.1

MYC4 bid importer 

== Description ==


"MYC4 is an online marketplace that connects you directly with African
entrepreneurs, who lack capital to develop their businesses" - From MYC4 About page

This plugin gives you the ability to show your blog readers the businesses 
in which you invested using the MYC4 platform.


== Installation ==

The "MYC4 Import" plugin can be installed by following this steps:

	1. Unzip "myc4-import" archive and put all files into your "plugins"
	folder (/wp-content/plugins/) or to create a sub directory into the
	plugins folder (recommanded), like /wp-content/plugins/myc4-import/

	2. Make sure the webserver can write files and create folders inside
	the folders "Images" and "Portal" inside your plugin directory
		on Unix this should do: 
		mkdir Images Portal
		chmod 777 Images Portal 
	
	3. Activate the plugin

	4. Inside the Wordpress admin, go to Settings > MYC4 import, adjust the parameters according to your needs, and save them.
	The format for your MYC4 feed link is: https://www.myc4.com/RSS/UserBidsRSS.ashx?c=<UserID>
	To find your UserId view your investor profile from https://www.myc4.com/Invest/Investors and look at the last number in the 
	url, that's your MYC4 id

	5. click Import and Publish Bids to fetch your active bids from myc4
	and import them as posts in your blog.

== Changelog ==

= 0.2 =
* modified to work with the new myc4 site layout
* added details about the MYC4 rss link

