=== Github2SVN ===
Contributors: FolioVision
Donate link: https://foliovision.com/donate
Tags: github, svn, plugin, release
Requires at least: 3.0.1
Tested up to: 4.42
Stable tag: 0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Beta version for developers only! Sync your Github repositories to WordPress SVN for plugin updates.

== Description ==

This plugin will only help developers. Its purpose is to allow a developer to update his or her WordPress.org SVN plugins (distribution) from his or her GitHub repositories (development). With fifteen plugins at WordPress.org, a simpler way to do updates was very important to us.

Normally we only release publicly production level code. This plugin is partly finished work. It was mainly coded by a journeyman programmer albeit under the supervision of a senior programmer so any good developer will find lacunas in the code to fix. There may even be security issues. That said we actively use this technology ourselves so it does work.

If you find such issues, please let us know or fix them. We'll actively be merging back any pull requests. If you feel passionately about this plugin, feel free to fork and improve it. We are fine with shared ownership.

Foliovision will not charge for the code or for a service based on this code. GitHub2WPSVN is an entirely GPLv3 effort funded by Foliovision to make both our work lives and the work lives of other developers easier. We'd much rather see Otto/Samuel Wood and Mika fix the existing WordPress.org plugin SVN to connect directly with GitHub. We've stopped holding our breath so we are releasing this code to other developers.

If the release of this plugin, helps them focus on their minds on GitHub to WP SVN workflow and make this plugin obsolete, we'll be very happy about that.

== Installation ==

Your server need to have git and svn installed and the exec() PHP call has to be enabled.

Before committing any plugin you have to enter the plugin information and click "Add Plugin".

== Frequently Asked Questions ==

= Do you store the WordPress.org login? =

No, you have to provide it with each commit, it's not stored in any way.

= Do you support the assets/ directory? =

Not yet. Assets would have to be synced separately. As assets change less often it's easier to update them by hand for now. We'd love to see an automated way to update assets.

= Do you support tagging? =

Yes, you can tag your releases.

== Screenshots ==

1. The plugin screen.

== Changelog ==

= 0.1 =

First public release
