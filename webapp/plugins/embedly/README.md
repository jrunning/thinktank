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
* Make sure a given URL is queried only once (record HTTP status code to
  tt_embedly_embeds?)
* Query the API's `services` method to get a list of supported URLs and avoid
  making unnecessary API calls (cache this and compare to tell when new
  services are added?)
* Make the oEmbed client (PEAR Services_oEmbed) more tolerant of missing
  attributes, or find/write a more tolerant oEmbed client
* Make a configuration page
* Integrate results into ThinkTank front-end