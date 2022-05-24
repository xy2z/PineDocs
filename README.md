<div align="center">

# PineDocs

**A fast and lightweight site for viewing files.**

Great for documentation, wiki, examples, notes, documents, galleries, storage, etc.

---

[Docker Hub](https://hub.docker.com/r/xy2z/pinedocs/) | [Screenshots](https://imgur.com/a/15Gq67X) | [Setup](#setup) | [Configuration](#configuration) | [Sponsor](https://github.com/sponsors/xy2z)

<p align="center">
  <a href="https://github.com/xy2z/PineDocs/releases"><img src="https://img.shields.io/github/v/release/xy2z/pinedocs?style=flat-square&include_prereleases&sort=semver"></a>
  <a href="https://hub.docker.com/r/xy2z/pinedocs"><img src="https://img.shields.io/docker/pulls/xy2z/pinedocs?style=flat-square"></a>
  <a href="https://hub.docker.com/r/xy2z/pinedocs"><img src="https://img.shields.io/docker/cloud/build/xy2z/pinedocs?style=flat-square"></a>
  <a href="https://github.com/xy2z/PineDocs/blob/master/LICENSE.txt"><img src="https://img.shields.io/github/license/xy2z/pinedocs?style=flat-square&color=blue"></a>
  <a href="https://github.com/xy2z/PineDocs/graphs/contributors"><img src="https://img.shields.io/github/contributors/xy2z/pinedocs?style=flat-square"></a>
  <a href="https://github.com/xy2z/PineDocs/issues?q=is%3Aopen+is%3Aissue+label%3A%22help+wanted%22"><img src="https://img.shields.io/github/issues/xy2z/pinedocs/help%20wanted?label=help%20wanted%20issues&style=flat-square&color=f26222"></a>
</p>

---

</div>

![Pinedocs Screenshot](https://i.imgur.com/m0EC4U6.png)
_See more screenshots at https://imgur.com/a/15Gq67X_


## Sponsor
💚 Please consider sponsoring or donating to keep this project active - https://github.com/sponsors/xy2z


## Features

- No database.
- No building.
- Fast - using ajax and cache.
- Supports Markdown, plaintext, HTML, code, images, SVG, GIFs, audio and small videos. Basically anything you can render in a browser.
- Responsive layouts and different color schemes (see screenshots [here](https://imgur.com/a/15Gq67X))
- Syntax highlighting for over 169 languages with over 77 themes.
- Configuration (see below).
- Quick filtering in files.
- Use your existing files or existing git repos as content.
- Files are cached client-side after first view, so it loads instantly.
- Hidden folders - only accessible via direct links.
- Remembers scroll position per page.


## Read-Only Site
PineDocs is read-only, meaning you can only view files and not edit them via PineDocs. This is not a wiki replacement with user login, revisions etc. This is basically a simple site that renders everything in your content dir. So you need to edit the files on your server/PC in order to change the content.


## Setup

### Docker

Docker is the easiest way to setup PineDocs.

#### Docker-compose.yml
```yaml
version: '3'

services:
  web:
    image: xy2z/pinedocs:1.2.3
    ports:
      - 3000:80
    volumes:
      - ./data:/data/pinedocs
```

After running `docker-compose up -d` you can change the config in `./data/config/config.yaml`, and add your files (or git clone your repository) in the `./data/files` dir.

Changes will take affect when you reload the page - no need to restart the container.

#### Docker

Altough docker-compose is recommended, you can also use pure Docker:

`docker run -itd -v "$PWD"/data:/data/pinedocs -p 3000:80 xy2z/pinedocs`

See more at https://hub.docker.com/r/xy2z/pinedocs/

### Manual Setup

#### Requirements

- PHP 8.0 (mininum required php version is PHP 7.2.5)
- Composer for dependencies.
- A web server (apache2, nginx, etc.)

#### Setup guide

1. [Download the latest release](https://github.com/xy2z/PineDocs/releases) or run `git clone`
1. Run `composer install` in the root to get dependencies.
1. Setup the web server to the `PineDocs/public` dir (use `php -S localhost:89 -t public` for testing)
1. (Optional) Rename the file `config/config-example.yaml` to `config/config.yaml` for changing settings (see below)
1. (Optional) Rename the file `config/mathjax-example.js` to `config/mathjax.js` for changing settings of Mathjax (see below)


## Configuration

Feel free to edit the `config/config.yaml` file to fit your needs.

You can [configure Mathjax](https://docs.mathjax.org/en/latest/options/index.html#configuring-mathjax-1) by editing the `config/mathjax.js` file.


#### Settings

- **`title`** (string) The title of the site, used in logo, tabs and bookmarks.

- **`content_dir`** (string) Path to the dir you want to use. Default is the PineDocs/content/ dir.

- **`index`** (string) Relative path to the index file. Default is array of `index.md`, `index.html`, `index.txt`, `index`.

- **`layout`** (string) Available layouts: `default`, `wiki`.

- **`color_scheme`** (string) Available color schemes: `pinedocs`, `simplistic`, `simplistic-dark`, `twilight`.

- **`highlight_theme`** (string) The theme to use for code. See a list at https://highlightjs.org/static/demo/

- **`code_transparent_bg`** (bool) If `true`, all will use the highlight theme. If `false`, the background will be transparent.

- **`open_dirs`** (int|string) The number of levels of dirs that should be opnened on page load. Use `all` to open all dirs.

- **`render_footer`** (bool) Render the menu footer?

- **`exclude_files`** (array) List of files or dirs to exclude. Supports regex if the format is /regex/i.

- **`show_file_extension`** (bool) Show file extensions. Default is true.

- **`menu_link_format`** (string) Values: default, ucfirst, ucwords, uppercase, lowercase.

- **`render_max_file_size`** (float) Max file size (in MB) to render. If file is larger, a download link will be shown. Default is `50`.

- **`font_family`** (string) The font used for menu and content. Default is `Tahoma`.

- **`font_size`** (string) The font size in menu and content. Default is `15px`.

- **`no_extension_markdown`** (bool) Render files with no extension as markdown, eg `README`. Default `true`.

- **`break_code_blocks`** (bool) Break code blocks in multiple lines instead of horizontally scrolling. Default `false`.

- **`hide_folders_in_navigation`** (array) A list of folders to hide from frontend navigation. Case-insensitive. Default is none.

- **`enable_mathjax`** (bool) Enable MathJax. Must be enabled for `mathjax.js` to work. Default is `false`.


## License

GNU GPLv3. See LICENSE.txt
