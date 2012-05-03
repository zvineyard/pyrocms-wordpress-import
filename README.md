# PyroCMS WordPress Import Module

## Legal

This module was originally written by [Zac Vineyard](http://zacvineyard.com).

## Description

Import your exisiting WordPress site into PyroCMS.

## Usage

Please Note: It is best to only use this module with a new (content free) instance of PyroCMS.
Step 1: Login to your WordPress admin panel and export your WordPress site to an XML file using the WordPress export tool.
Step 2: Upload that XML file using the PyroCMS WordPress Import Module.
Step 3: Watch the magic happen.

## Known Issues

This module does not currently import pages form a WordPress site into PyroCMS. That feature will be part of the next release.
This module does not copy images from WordPress posts or pages.
Running the import process more than once will throw sql errors if the following tables aren't empty: default_blog,default_blog_categories,default_comments,default_keywords,default_keywords_applied. Here is a little SQL to help: 

	TRUNCATE `default_blog`;
	TRUNCATE `default_blog_categories`;
	TRUNCATE `default_comments`;
	TRUNCATE `default_keywords`;
	TRUNCATE `default_keywords_applied`;