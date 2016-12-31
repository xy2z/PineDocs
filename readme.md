# xyDocs

**THIS PROJECT IS STILL UNDER DEVELOPMENT.**

xyDocs is a simple, lightweight tool for viewing files in a browser. Great for documentation, wiki, notes, etc.

Renders Markdown, HTML, syntax highlighting code, images, GIFs, audio and videos.


## Screenshots
(Coming when v1.0 is done)


## Setup
### Requirements
- PHP 7.0 or above
- Web server (Nginx/Apache/etc)


### Setup guide
1. [Download the latest release](https://github.com/xy2z/xyDocs/releases) or `git clone`.
1. Edit the `config.yaml` file
1. Setup the web server (link to examples) - or use `php -S localhost:89` for testing.


## Features
- Cross-platform (currently tested on Ubuntu and Windows)
- Supports unlimited folders (and files of course)
- Supports Markdown and HTML files
- Supports images and GIFs
- Supports audio and video files
- Support text files (regardless of the extension)
- Syntax highlighting for over 169 languages with over 77 themes
- Multiple responsive themes. You can easily create your own theme in CSS
- A simple YAML config file to change the settings


## Configuration
Feel free to edit the `config.yaml` file to fit your needs.


#### Required settings
- **`title`**  (string) The title of the site, used in tabs and bookmarks.

- **`content_dir`** (string) Path to the dir you want to use.

#### Optional settings
- **`logo`** (string) Path to the logo. If nothing is set, the `xyDocs` logo will be used.

- **`index`** (string) Relative path to the index file. Default is array of `index.md`, `index.html`, `index.txt`, `index`.

- **`theme`** (string) Available themes: `default`, `default-dark`, `wiki`, `wiki-dark`.

- **`highlight_theme`** (string) The theme to use for code. See a list at https://highlightjs.org/static/demo/

- **`code_transparent_bg`** (bool) If `true`, all  will use the highlight theme. If `false`, the background will be transparent.

- **`open_dirs`** (int|string) The number of levels of dirs that should be opnened on page load. Use `all` to open all dirs.

- **`render_footer`** (bool) Render the menu footer?


## Todo v1.0
- Responsive themes: default + wiki
- Themes should only be in 1 theme (not array)? (so you can't fuck up) ('theme_structure' and 'theme_color'?)
- Theme template (in 'themes' dir)
- Security: Are there other ways to go back in a dir like '../'?
- Config should be ignored? How to keep it from being overwritten.
- "Start" page should start at 'index.*'.
- Apache config example file (for github wiki)
- Test:
	- Test on ubuntu with content_dir? :) (testpingu01)
	- Test images: jpg, png, gif + more?
	- Test videos: ogg, etc.
	- Test audio files: avi?
	- Test in all browser + mobile browsers.


## Known issues
- Can't load files with special chars path (maybe encoding?)


## Future features
- htaccess
- File icons should match file types.
- Remember scroll position when browsing between files (should also work when using the browsers back/forward)
- Bug: Can't render (binary) files (images/videos/audio etc) bigger than ~50MB. (Possible solution: if the file is over XXMB then show a download link instead)
- Search (filenames, dirs and content) (with keypress 'F' to focus, config for binding key)
- Exclude files/folders in config (glob).
- More themes (sepia)
- Markdown: Generate table of contents (config)
- Show error message if filesize is bigger than PHP memory_limit. (instead of php's fatal error)
- Create/delete/edit files directly on the site?
- YAML exclude files with filter ("\*.git") (set in default config)
- Bind '*' key to open all dirs.
- Support for more markup languages (https://en.wikipedia.org/wiki/Lightweight_markup_language)
- Config: show_file_extensions,
- Make all content links open in new tab (setting)
- File actions (in content_path)
	- Fullscreen file-content
	- Copy file content to clipboard
	- Download file
	- Switch between raw/rendered (for html, markdown, etc.) (bind to key 'R'?)
	- File details (filesize, created, edited, etc.) (bind to key 'D'?)
