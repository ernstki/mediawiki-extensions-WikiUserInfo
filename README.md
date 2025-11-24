# mediawiki-extensions-WikiUserInfo

An attempt to update Extension:WikiUserInfo to work with MediaWiki 1.35.x.

This is based on the [last available version][1], from the
"Extension:WikiUserInfo" article on mediawiki.org, which had its source code
stored directly on the page, with no license specified.

Assuming [CC-BY-SA-4.0][1.1] for the time being (the default license for
MediaWiki.org content), but I _will_ attempt to check with the original author.

## Installation

Clone this repository into `extension` within your MediaWiki's installation
directory. Then, in your `LocalSettings.php`:

```php
# source: https://github.com/ernstki/mediawiki-extensions-WikiUserInfo
wfLoadExtension( 'WikiUserInfo' );
# "safe" options that users are allowed to see with this extension
# ('nickname' is the default if not specified)
$wgWikiUserInfoSafeOptions = [ 'realname', 'nickname' ];

# see https://www.mediawiki.org/wiki/Manual:User_rights#List_of_groups
$wgGroupPermissions['user']['showuseremail'] = true;
# 'nickname', 'fullname' and others to be supported later
$wgGroupPermissions['user']['showuseroption'] = true;
```

Bureaucrats and Administrators (sysops) will have the above extensions by
default. The "safe" options specify which additional bits of information about
your users _other_ users in the named `$wgGroupPermissions` will be able to see
through the use of this extension.

## About that "1.0" release

It's because it [breaks the API](https://semver.org) in a
non-backward-compatible way.

Some of the original user options are not exposed, and `realname` is an option
now that didn't need to be added to `$wgGroupPermissions`.

## Bugs

The nickname is already visible in signatures, so it doesn't really make sense
to add _extra_ restrictions with this extension.

## To-do

- [x] update to use new extension registration system
- [x] get `{{#realname:Username}}` working, because that's the (only?) one I
  really need
    - (bonus: `{{#email:Username}}` seems to be working, too)
- [ ] ExtensionDistributor?
- [ ] Translatewiki.net?

## Credits

* [User:MichaelDubner][2] - original author
* Kevin Ernst ([ernstki -at- mail.uc.edu][3]) - updates for MW 1.35.x

[1]: https://www.mediawiki.org/w/index.php?oldid=4305468
[2]: https://en.wikipedia.org/wiki/User:MichaelDubner
[3]: mailto:ernstki%20-at-%20mail.uc.edu
[1.1]: https://creativecommons.org/licenses/by-sa/4.0/