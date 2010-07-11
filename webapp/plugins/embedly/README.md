Embed.ly Plugin for ThinkTank
=============================

The Embed.ly Plugin uses the Embed.ly API to retrieve embed codes for linked
videos, pictures, and other content

Crawler Plugin
--------------
During the crawl process, the Embed.ly Plugin selects the unique URLs from the
last 200 links in the links table which it has not processed already, retrieves
the embed information if possible, and saves it to the database.

TODO
----
* Make it work with PHP < 5.2.4 which doesn't support CURLINFO_PRIVATE (for
  passing link_ids through cURL)
* Make a configuration page
* Integrate results into ThinkTank front-end