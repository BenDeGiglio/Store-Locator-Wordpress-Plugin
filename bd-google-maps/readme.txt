Settings

API Settings
In order to get the map to work you must generate a google maps api key and enable the geocoding api.

Checking the "get visitors location" box enqueues the javascript that finds the users lat and lng based on ip and sets a cookie. 
Due to security this requires the site to have a secure connection (ssl) in order to work properly.

Fields Settings
This plugin has 10 fields that are available to use. But, you may not need them all so you can uncheck the box of any fields you do not need. This will remove those fiels from the app until the box is unchecked. 

Display Settings
Allows you to choose the google maps marker color.
Allows you to add the style array(see google guildlines or use generators like snazzy maps) to style your google map.
Choose searchbox place holder text and button text.



Shortcode Usage

[bd_print_map]
Displays map on front end.
Params:
scrollwheel(bool), default true - determines if the mouses scroll wheel zooms in and out on the map
zoom(number), default 5 - sets the zoom of the map
Example usage: [bd_print_map scrollwheel="false" zoom="6"]


[bd_locations]
Displays location on front end.
Params:
json(bool), default false - if true will return json array instead of html
user_location(bool), default false - will filter locations based on proximity to users location
amount(number), default 5 - determines the number of locations returned 
Example usage: [bd_locations json="false" amount="6" user_location="false"]


[bd_search]
Displays search box on front end for location searching **.
Params:
json(bool), default false - if true will return json array instead of html
amount(number), default 5 - determines the number of locations returned 
services_offered(bool), default false - if set to true will display a services offered dropdown (based on services offered assigned to location). Will allow search to use both location and services offered to filter locations.
** Search requires AJAX I have set up the nessesary scripts and have provided a basic scripts file to get you started. 
** Go into the plugins root folder and move the bd-js folder into your themes root. 
** This scrip will work out of the box if json is set to false. If it is set to true you will have to handle the json array as you please.
Example usage: [bd_locations json="false" amount="6" services_offered="true"]

FAQ
Does this work with multi site?
Yes, each site will have its own locations, services tables. The API settings and Display settings will stay the same for every site.

Where can I get an API key?
https://console.developers.google.com/
Go to the above url, select the dropdown next to "Google APIs" logo and select create project.
Once created and in the dashboard click "Enable API". Choose "Google Maps JavaScript API" and  click enable at the top. Then, repeat those steps only this time enable "Google Maps Geocoding API".

Where can I get some precoded map styles?
https://snazzymaps.com/ is a great(free) tool to use. Just select the map you like and copy the code.



