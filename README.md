# mediawiki-extensions-WikiUserInfo

An attempt to update Extension:WikiUserInfo to work with MediaWiki 1.35.x.

This is based on the [last available version][1], from the
"Extension:WikiUserInfo" article on mediawiki.org, which had its source code
stored directly on the page, with no license specified.

Assuming public domain for the time being, but I _will_ attempt to check with
the original author (or at least the original _editor_ of the page).

## To-do

- [x] update to use new extension registration system
- [x] get `{{#realname:Username}}` working, because that's the (only?) one I
  really need
    - (bonus: `{{#email:Username}}` seems to be working, too)
- [ ] ExtensionDistributor?
- [ ] Translatewiki.net?

## Credits

* [User:MichaelDubner][2] - original author (?)
* Kevin Ernst ([ernstki -at- mail.uc.edu][3]) - updates for MW 1.35.x

[1]: https://www.mediawiki.org/w/index.php?oldid=4305468
[2]: https://en.wikipedia.org/wiki/User:MichaelDubner
[3]: mailto:ernstki%20-at-%20mail.uc.edu
