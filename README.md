# PineDocs

**THIS PROJECT IS STILL UNDER DEVELOPMENT.**

PineDocs is a simple, lightweight tool for viewing files in a browser. Great for documentation, wiki, notes, etc.
Renders Markdown, HTML, syntax highlighting code, images, GIFs, audio and videos.


## Screenshots
(Coming when v1.0 is done)


## Features
- Cross-platform (currently tested on Ubuntu and Windows)
- Supports unlimited folders (and files of course)
- Supports Markdown, text, HTML, images, GIFs, audio and videos.
- Syntax highlighting for over 169 languages with over 77 themes
- Multiple responsive themes and color schemes. You can easily make your own color scheme in CSS.
- A simple YAML config file to change the settings


## Setup
### Requirements
- PHP 7.0 or above
	- YAML extension for PHP (https://pecl.php.net/package/yaml)
- Web server: Apache, nginx, etc.


### Setup guide
1. [Download the latest release](https://github.com/xy2z/PineDocs/releases) or `git clone`.
1. Copy `config/config-example.yaml` to `config/config.yaml`.
1. Edit the `config/config.yaml` file to fit your needs.
1. Setup the web server (or use `php -S localhost:89` for testing).
	- [Recommended Apache conf file](#)


## Configuration
Feel free to edit the `config.yaml` file to fit your needs.


#### Required settings
- **`title`**  (string) The title of the site, used in tabs and bookmarks.

- **`content_dir`** (string) Path to the dir you want to use.

#### Optional settings
- **`logo`** (string) Path to the logo. If nothing is set, the PineDocs logo will be used.

- **`index`** (string) Relative path to the index file. Default is array of `index.md`, `index.html`, `index.txt`, `index`.

- **`theme`** (string) Available themes: `default`, `default-dark`, `wiki`, `wiki-dark`.

- **`highlight_theme`** (string) The theme to use for code. See a list at https://highlightjs.org/static/demo/

- **`code_transparent_bg`** (bool) If `true`, all  will use the highlight theme. If `false`, the background will be transparent.

- **`open_dirs`** (int|string) The number of levels of dirs that should be opnened on page load. Use `all` to open all dirs.

- **`render_footer`** (bool) Render the menu footer?

- **`exclude_files`** (array) List of files to exclude. Supports regex if the format is /regex/i.


## Future features
- Support filetypes:
	- Audi FLAC for (Firefox 51)
	- WebM
	https://www.reddit.com/r/linux/comments/5pood2/firefox_51_released_with_flac_audio_support_webgl/
- htaccess (Apache)
- File icons should match file types.
- Make a Recommended nginx conf
- Remember scroll position when browsing between files (should also work when using the browsers back/forward)
- Bug: Can't render (binary) files (images/videos/audio etc) bigger than ~50MB. (Possible solution: if the file is over XXMB then show a download link instead)
- Search (filenames, dirs and content) (with keypress 'F' to focus, config for binding key)
- More themes (sepia)
- Markdown: Generate table of contents (config)
- Show error message if filesize is bigger than PHP memory_limit. (instead of php's fatal error)
- Create/delete/edit files directly on the site?
- Bind '*' key to open all dirs.
- Support for more markup languages (https://en.wikipedia.org/wiki/Lightweight_markup_language)
- Make all content links open in new tab (setting, default true unless it's internal links)
- Work on shared network drives (need test)
- File actions (in content_path)
	- Fullscreen file-content (and/or raw file)
	- Copy file content to clipboard
	- Download file
	- Switch between raw/rendered (for html, markdown, etc.) (bind to key 'R'?)
	- File details (filesize, created, edited, etc.) (bind to key 'D'?)


## License
GNU GPLv3. See LICENSE.txt
