# PyroCMS WordPress Import Module

## Legal

This module was originally written by [Zac Vineyard](http://zacvineyard.com).

## Description

Import your exisiting WordPress site into PyroCMS. See the source code at [https://github.com/zvineyard/pyro_wordpress_import](https://github.com/zvineyard/pyro_wordpress_import).

## Usage

Please Note: It is best to only use this module with a new (content free) instance of PyroCMS.

Step 1: Extract contents of this zip to a new folder:

	addons/<site-ref>/modules/wordpress_import

Step 2: Login to your WordPress admin panel and export your WordPress site to an XML file using the WordPress export tool.

Step 3: Upload that XML file using the PyroCMS WordPress Import Module.

Step 4: Watch the magic happen.

## Known Issues

While thi smodule imports pages, it doesn't not keep track of their hierarchy (parent vs. child).

This module does not currently transfer draft posts from WordPress to PyroCMS.

This module does not copy images from WordPress posts or pages.

Running the import process more than once will throw SQL errors if the following tables aren't empty: default_blog, default_blog_categories, default_comments, default_keywords, default_keywords_applied.

Here is a little SQL to help: 

	TRUNCATE `default_blog`;
	TRUNCATE `default_blog_categories`;
	TRUNCATE `default_comments`;
	TRUNCATE `default_keywords`;
	TRUNCATE `default_keywords_applied`;