Lighthouse module provides a connection to Lighthouse hub.
The hub contains different media, like images and videos, which could be used on a website.

In order to add a field with a connection to Lighthouse, you need to
* create a field with reference to media entities
* make sure that fields restriction includes Lighthouse media bundle
* in content form display you need to choose `Entity browser` widget and set it using `Lighthouse browser`
This will make your field to use a proper gallery view on add/edit form.


To easily pass media data to a theme template
you are highly recommended to use `MediaHelper` service from `mars_common` module.
For example `getMediaParametersById()` function returns you a *src*, *title* and *alt*
for an image regardless of the field's media bundle.

More information you can find in [Wiki](https://dev.azure.com/MarsDevTeam/MarsExperiencePlatform/_wiki/wikis/Mars-Experience-Platform.wiki/2436/Configuration-for-fields-with-Lighthouse)

