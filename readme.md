# xyDocs

**THIS PROJECT IS STILL UNDER DEVELOPMENT.**


## About
xyDocs is a lightweight tool for viewing your wiki/notes/documentation. Simple yet powerfull.
Renders markdown, html, code with highlighting, images, GIF's, audio files and videos.

**Security in case of links are "../**

## Screenshots
(Coming when v1.0 is done)


## Setup
### Requirements
- PHP 5.?+ _(PHP 7.1 is recommended)_ (need test php 5.6)
- Apache or alike


### Setup guide
1. git clone
1. Edit YAML config (link to example)
1. Apache config (link to example) or use 'php -S localhost:89' for testing.
1. Enjoy!


## Version 1.0 will include (when done)
- Multiple RESPONSIVE themes
	- Default (grey)
	- Dark (twilight)
	- Wiki
	- Sepia
- Create your own theme in CSS (or extend an existing theme)
- YAML configuration.
- Key bind to easily search (press 'F')
- Render any filetype
	- Markdown ('view raw' button)
	- HTML ('view raw' button)
	- Code files with syntax highlighting (JS, PHP, JSON, YAML)
	- Images and GIFs
	- Videos
	- Audio files
- Link to other files within the wiki.


## Known issues
- Work when browser goes back/forward between pages.
- Menu must follow content (if menu is smaller than file height)
- Rendering binary files (images/videos/audio etc) bigger than ~50MB. The browser doesn't like that. (Possible solution: if the file is over XXMB then show a download link instead)


## Future
- More themes (sepia)
- Markdown: Generate table of contents (config)
- Show error message if filesize is bigger than PHP memory_limit. (instead of php's fatal error)
- Create/delete/edit files directly on the site?
- YAML exclude files with filter ("\*.git") (set in default config)
- Bind '*' key to open all dirs.
- File actions
	- Copy data to clipboard
	- Download file
	- Switch between raw/rendered (only for html, markdown, etc.) (bind to key 'R')
	- File details (filesize, created, last edited, etc.) (bind to key 'D' ?)
	- ?
