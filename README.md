symlinker
=========

Symlinker is a minimalistic web-based file manager written in PHP that is built to primarily operate on symlinks and here's how it looks:

![](symlinker/raw/master/screenshots/symlinker-cropped-screenshot.png)

Why would I use it?
-------------------

There are many PHP hosting providers nowadays. Unfortunately most of them only provides you FTP to upload your files to their servers which imposes several restrictions on you.

You probably use a content magagement systems (CMS) on your site and you may want to make multiply installations of the same CMS. Such an installation traditionally involves duplicating the complete directory structure of your CMS, but that may take much disk space and it's painful to upgrade the individual installations this way. There is a much better way.

You can solve this problem by placing the directory structure of your CMS in only one directory and symlink the individual CMS instances that you want to use to point to this particular directory. This is called a symlink farm. Unfortunately it's not possible to handle symlinks with FTP because the FTP protocol doesn't have such commands that operate on symlinks.

This scenario is a perfect one where Symlinker comes handy.

How can I install it?
---------------------

1. Download `symlinker.php` and put it somewhere under your webspace.
2. Set the `$password` variable in the beginning of `symlinker.php`. This will be your login password that Symlinker will ask you upon login.
3. You may also set the `$default_path` variable in the beginning of `symlinker.php`. This will be your default path after login.

How can I use it?
-----------------

First, point your browser to the URL where you put `symlinker.php`. Symlinker will ask you the login password, so enter it correctly and log in.

If everything went well, you should see a page that resembles the picture that you can see on the top of this page. Let's see what operations you can use here:

* You can navigate in the directory tree by using the navigation bar which resides in the top left corner of the page or editing the path argument in the URL.
* You can create a new symlink by entering the filename and the target of the symlink into the upper text fields and pressing the symlink button.
* You can update the target of the symlinks of the current directory by editing the text fields related to the specific filenames and pressing the update button.
* You can log out by visiting the logout link which resides in the top right corner of the page.

How can I set up a symlink farm?
--------------------------------

To better understand how to set up a symlink farm, take a look at the this picture:

![](symlinker/raw/master/screenshots/symlinker-full-screenshot.png)

I use the MediaWiki content management system here. I have a special directory, `/web/monda/apps` where I keep the installations of the CMSes that I use.

First, I create a symlink with the filename `mediawiki` which points to `../../apps/mediawiki-1.9.3`, the exact version of the MediaWiki installation which I want to use within this directory. Later when I want to upgrade MediaWiki, I can just change the target of this symlink to point to a more recent version of MediaWiki.

As you can see, most of the filenames in this directory are symlinked to `mediawiki`, but some are not. This is because most CMS holds some data which are specific to the individual CMS instances so they must have their own separate directory.