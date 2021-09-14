# Changelog


## 1.0.5 - 2018-02-18
- Major updates to the plugins.
- Created a base class to move a lot of repeated functionality out of each plugin into a single file to help maintenance. The base class is currently in mu-plugins, but can be copied into each plugin directly to keep them self-contained.
- Moved templates into the plugins.
- Created fake pages for the forms for adding items to the plugin post types. Now pages don't have to be created on every new site.
