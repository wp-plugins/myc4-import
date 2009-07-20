=== Plugin Name ===
Contributors: prof. David Costa, mihai 
Tags: myc4 microfinance
Requires at least: 2.7
Tested up to: 2.8
Stable tag: 0.6

MYC4 bid importer 

== Description ==


"MYC4 is an online marketplace that connects you directly with African
entrepreneurs, who lack capital to develop their businesses" - From MYC4 About 
page

This plugin gives you the ability to show your blog readers the businesses 
in which you invested using the MYC4 platform.


== Installation ==

The "MYC4 Import" plugin can be installed by following this steps:

	1. Unzip "myc4-import" archive and put all files into your "plugins"
	folder (/wp-content/plugins/) or to create a sub directory into the
	plugins folder (recommanded), like /wp-content/plugins/myc4-import/

	2. Make sure the webserver can write files and create folders inside
	the folder wp-content/myc4
		on Unix this should do: 
		mkdir wp-content/myc4 
		chmod 777 wp-content/myc4 
	
	3. Activate the plugin

	4. Inside the Wordpress admin, go to Settings > MYC4 import, adjust the 
	parameters according to your needs, and save them.
	The format for your MYC4 feed link is: 
	https://www.myc4.com/RSS/UserBidsRSS.ashx?c=<UserID>
	To find your UserId view your investor profile from 
	https://www.myc4.com/Invest/Investors and look at the last number in the 
	url, that's your MYC4 id
	
	The templates for the post title and content use the values storred in the
	$b array. 
	Here are the possible values you can use:
	<?=$b['name']?>  - name on Myc4 profile
	<?=$b['smallimg']?> - full link to the avatar image ( locally hosted ) 
	<?=$b['cimg']?> - full link to country flag image ( locally hosted )
	<?=$b['largeimg']?> - full link to large image ( locally hosted )
	<?=$b['cname']?> - country name
	<?=$b['shortDesc']?> - short description, the one you can see on the loan 
	page
	<?=$b['longDesc']?> - long description, this is the one from the 
	"Background" 
	page
	<?=$b['largeimg']?> - the first large image from the "Background" page. 
	<?=$b['amount']?> - bid amount
	<?=$b['irate']?> - interest rate
	<?=$b['link']?> - link to profile page
	

	5. click Import and Publish Bids to fetch your active bids from myc4
	and import them as posts in your blog.

== Upgrade ==
If you want to upgrade from version 0.4 and bellow you will have to move the 
Images and Resources folders from the plugin directory to the wp-content/myc4 
directory before you upgrade. This way the images are saved. 

If you didn't read this before you upgraded you will have to delete the 
imported posts and reimport otherwise the images in those posts will not be 
available anymore.


== Changelog ==

= 0.5 =
* changed the data dir to wp-content/myc4 insead of the plugin dir so that 
plugin upgrades would not delete the data
* integrated with twitter tools so now you can chose to send the new myc4 
profiles to twitter or not.

= 0.4 =
* added dashboard widget with rss feeds from http://microfinance.fm
* bumped stable version number

= 0.3 =
* removed some debugging text
* added some description about the variables that can be used in the post title
and body templates

= 0.2 =
* modified to work with the new myc4 site layout
* added details about the MYC4 rss link

