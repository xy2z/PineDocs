$(function() {


	function nl2br(str) {
		str = str.replace(/\n/g, '<br />')
		str = str.replace(/\r/g, '<br />')
		return str
	}


	function format_bytes(bytes, decimals) {
		if (bytes == 0) {
			return '0 Byte'
		}

		var k = 1024 // 1024 for binary
		// var dm = decimals + 1 || 3
		var dm = decimals + 1 || 1
		var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']
		var i = Math.floor(Math.log(bytes) / Math.log(k))
		return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i]
	}


	// PineDocs class
	var PineDocs = function() {
		var self = this

		// Elements
		self.elements = {
			menu_wrapper: $('#menu_wrapper'),
			menu_close: $('#menu_close'),
			search: $('#search'),
			menu: $('#menu'),
			content_path: $('#content_path'),
			file_content: $('#file_content'),
			loading: $('#loading'),
			mobile_nav_icon: $('#mobile_nav_icon')
		}

		// Properties
		self.click_hashchange = false
		self.loaded = {}
		self.black_list = {}
		self.scroll_top = {}
		self.hljs_plugin_loaded = false

		// Init
		self.set_events()

		self.render_errors()

		// Autoload file from URL anchor tag.
		if (window.location.hash.length >= 2) {
			// Check if file exists.
			var file = $('a.link_file[href="' + decodeURIComponent(window.location.hash) + '"]')
			if (file.length) {
				// File exists
				$('a.link_file[href="' + decodeURIComponent(window.location.hash) + '"]').click()
			} else {
				// File does not exist.
				self.render_hidden_file(window.location.hash.substr(1));
			}
		} else {
			// Load index file.
			self.render_file(config.index_data)
			if (config.index_data.relative_path) {
				window.location.hash = config.index_data.relative_path
			}
		}

		// Open dirs automatically.
		self.pageload_open_dirs()
	}


	// Render file content
	PineDocs.prototype.render_file = function(data) {
		var self = this

		// Reset
		self.set_loading(false)
		self.elements.file_content.html('')
		self.elements.file_content.css('white-space', 'normal')

		// Set content path
		self.elements.content_path.text(data.relative_path)
		var filesize = $('<span>').addClass('filesize')
		filesize.text(format_bytes(data.filesize))
		self.elements.content_path.append(filesize)

		// HLJS Plugins
		if (!self.hljs_plugin_loaded) {
			self.hljs_plugin_loaded = true

			// Copy button in codeblocks
			hljs.addPlugin(new CopyButtonPlugin())
		}

		// Render content
		if (data.download_link === true) {
			// File size is too large to render.
			self.render_download_link(data);
		} else if (data.extension == 'md' || data.extension == 'markdown') {
			// Markdown
			self.elements.file_content.html(marked(data.content))

			// Syntax highlighting
			self.elements.file_content.find('code').each(function(_, block) {
				if (config.code_transparent_bg) {
					$(this).addClass('nobg')
				}
				if(block.className === 'language-c++') {
					block.className = 'language-cpp'
				}
			})
			hljs.highlightAll()

			// Assets
			const block_types = ['img', 'audio', 'video', 'embed', 'source', 'a']
			block_types.forEach(block_type => {
				self.elements.file_content.find(block_type).each(function(_, block) {
					if (block.nodeName == 'A') {
						if ($(block).attr('href') === undefined || $(block).attr('href').length === 0) {
							// "href" attribute is empty.
							return // continue.
						}

						// Hyperlink types (https://developer.mozilla.org/en-US/docs/Web/API/Navigator/registerProtocolHandler#permitted_schemes)
						if ([
							'mailto', 'magnet', 'ftp', 'tel',
							'irc', 'geo', 'ftps', 'im', 'ircs',
							'bitcoin', 'matrix', 'mms', 'news',
							'nntp', 'openpgp4fpr', 'sftp', 'sip',
							'sms', 'smsto', 'ssh', 'urn', 'webcal',
							'wtai', 'xmpp'
						].every(hyperlink_type => {
							if (block.attributes.href.value.startsWith(hyperlink_type)) {
								// URL is a link to a specific scheme
								return false
							}
							return true
						}) === false) {
							return // continue.
						}

						// Check if link refer to a header
						if (block.attributes.href.value.startsWith('#')) {
							// If header exists
							const header = document.getElementById(decodeURIComponent(block.attributes.href.value.slice(1)))
							if (header != null) {
								block.addEventListener("click", function(event) {
									event.preventDefault()
									header.scrollIntoView({ behavior: 'smooth' })
								})
							}
						} else {
							// Add the correct link if internal
							const url = self.get_asset_path(data.relative_path, block.attributes.href.value)
							if (url != '#') {
								block.href = '#' + self.get_asset_path(data.relative_path, block.attributes.href.value)
							}
						}

						return // continue.
					}

					if ($(block).attr('src') === undefined || $(block).attr('src').length === 0) {
						// "src" attribute is empty.
						return // continue.
					}

					const url = self.get_asset_path(data.relative_path, block.attributes.src.value)
					if (url == "#") {
						// URL asks for an inaccessible path
						return // continue.
					}

					if (self.black_list[url]) {
						// URL already called and we know it doesn't exists.
						return // continue.
					}

					if (self.loaded[url]) {
						block.src = self.readable_data(self.loaded[url])
						return // continue.
					}

					// Reset the "src" so the browser doesn't try to load the file, and ignore the file after ajax.
					block.src = '#'

					// URL is neither loaded or blacklisted.
					$.ajax({
						url: '?',
						type: 'GET',
						dataType: 'json',
						data: {
							action: 'get_file_data',
							relative_path: url
						},
					})
					.done(function(response) {
						if (response.content === null) {
							self.black_list[url] = true
							return
						}

						block.src = self.readable_data(response)
						self.loaded[url] = response

						if (block.nodeName == 'SOURCE') {
							// Apparently "<source>" elements has to call "load" on
							// their parents (eg. <audio> or <video>).
							block.parentElement.load()
						}

					}).fail(function() {
						self.black_list[url] = true
					})
				})
			})

			// MathJax
			if (config.enable_mathjax) {
				MathJax.typeset();
			}

		} else if (data.type == 'image') {
			// Image.
			var img = $('<img>').attr('src', self.readable_data(data))
			img.attr('alt', data.basename)
			self.elements.file_content.append(img)
		} else if (data.type == 'svg') {
			var svg = $('<img>').attr('src', self.readable_data(data))
			svg.attr('alt', data.basename)
			self.elements.file_content.append(svg)
		} else if (data.type == 'pdf') {
			// PDF.
			var pdf = $('<embed>').attr('src', self.readable_data(data))
			pdf.attr('width', '100%')
			pdf.attr('height', '99%')
			self.elements.file_content.append(pdf)
		} else if (data.type == 'audio') {
			// Audio.
			var audio = $('<audio>').prop('controls', true).attr('src', self.readable_data(data))
			self.elements.file_content.append(audio)
		} else if (data.type == 'video') {
			// Video.
			var video = $('<video>').prop('controls', true)
			var source = $('<source>').attr('src', self.readable_data(data))
			source.attr('type', 'video/mp4')
			video.append(source)
			self.elements.file_content.append(video)
		} else if (data.type == 'code') {
			// Source code file
			var pre = $('<pre>')
			var code = $('<code>').text(data.content).addClass('file')
			if (config.code_transparent_bg) {
				code.addClass('nobg')
			}
			pre.append(code)
			self.elements.file_content.append(pre)

			// Syntax highlighting
			hljs.highlightAll()
		} else {
			if (typeof data.content == 'string') {
				self.elements.file_content.html(nl2br(data.content))

				// Syntax highlighting
				hljs.highlightAll()
			}
			if (data.content === null) {
				self.render_download_link(data);
			}
		}

		// Set title
		$('title').text(config.title + ' | ' + data.basename)

		// Scroll to last position.
		if (self.scroll_top[window.location.hash]) {
			self.elements.file_content.scrollTop(self.scroll_top[window.location.hash])
		} else {
			self.elements.file_content.scrollTop(0)
		}

		// Hide menu on mobile.
		if (self.elements.menu_close.is(':visible')) {
			self.hide_mobile_menu()
		}
	}


	// Set events
	PineDocs.prototype.set_events = function() {
		var self = this

		// Event for any external link, open in new window
		$('body').on('click', 'a', function(event) {
			var a = new RegExp('/' + window.location.host + '/')
			if (this.href != '' && !a.test(this.href)) {
				event.preventDefault()
				event.stopPropagation()
				window.open(this.href, '_blank')
			}
		})

		// Event for clicking menu links.
		$('#menu').on('click', 'a.link_file', function(event) {
			if (event.originalEvent !== undefined) {
				// User clicked this, so don't trigger this click event twice.
				self.click_hashchange = true
			}

			var link = $(this)
			var href = $(this).attr('href').substr(1)

			// Set 'active' class.
			$('.active').removeClass('active')
			link.addClass('active')

			// Remember scroll position.
			self.scroll_top[window.location.hash] = self.elements.file_content.scrollTop()

			// Already loaded before?
			if (self.loaded[href]) {
				window.location.hash = href
				self.render_file(self.loaded[href])
				return
			}

			// Set loading...
			self.set_loading(true, '<i class="fa fa-spinner fa-spin fa-4x" aria-hidden="true"></i>')

			// Get file data
			$.ajax({
				url: '?',
				type: 'GET',
				dataType: 'json',
				data: {
					action: 'get_file_data',
					relative_path: href
					// relative_path: encodeURIComponent(href)
				},
			})
			.done(function(response) {
				self.loaded[href] = response
				self.render_file(response)
			})
			.fail(function(response) {
				// Show error message.
				self.render_error_message('Error: could not load file: <u>' + href + '</u><br />See console for response')
				console.log('Error in response from ' + href)
				if (response.responseText) {
					console.log(response.responseText)
				} else {
					console.log('Empty response')
				}
			})
			.always(function(response) {
				// Remove loading...
				self.set_loading(false)
			})

		})


		// Click on folder
		$('body').on('click', 'a.link_dir', function(event) {
			event.preventDefault()
			/* Act on the event */
			$(this).parent().next().toggle('fast')
			$(this).toggleClass('link_dir_open')
			$(this).find('i.fa').toggleClass('fa-folder-open')
		})

		// URL Hashtag change (user probably went back or forward in browser history)
		$(window).bind('hashchange', function(e) {
			if (self.click_hashchange) {
				// The hash changed because of the user clicked a new item, so don't click it twice.
				self.click_hashchange = false
				return
			}
			// Click on the menu link if it exists.
			var link = self.elements.menu.find('a[href="' + document.location.hash + '"]')
			if (link.length) {
				link.click()
			} else {
				// Try loading from a "hidden" folder.
				self.render_hidden_file(document.location.hash.substr(1))
			}
		})


		// Show Mobile menu
		self.elements.mobile_nav_icon.click(function() {
			self.show_mobile_menu()
		})


		// Hide mobile menu
		self.elements.file_content.click(function() {
			self.hide_mobile_menu()
		})

		self.elements.menu_close.click(function() {
			// self.elements.menu_wrapper.hide()
			self.hide_mobile_menu()
		})

		window.addEventListener("keyup", function(e) {
			if (e.keyCode == 70) {
				// Focus on search bar.
				self.elements.search.focus()
			}

			if (e.keyCode == 106) {
				// * = Show all directories.
				self.elements.menu_wrapper.find('.link_dir').not('.link_dir_open').click()
			}
		})

		self.elements.search.on('keyup', function(e) {
			if (e.keyCode == 27) {
				// ESC on search bar.
				$(this).val('')
			}

			self.filter_items($(this))
		})
	}


	// Show or hide loading.
	PineDocs.prototype.set_loading = function(state, message) {
		var self = this

		if (state) {
			self.elements.file_content.html('').hide()
			self.elements.content_path.text('Loading...')
			self.elements.loading.html(message).show()
		} else {
			self.elements.loading.hide()
			self.elements.file_content.show()
		}
	}


	PineDocs.prototype.render_error_message = function(message) {
		var self = this
		self.elements.content_path.text('Error')
		var error_element = $('<div>').addClass('error').html(message)
		self.elements.file_content.html(error_element)
	}


	PineDocs.prototype.pageload_open_dirs = function() {
		var self = this

		if (config.open_dirs == 'all') {
			// Open all dirs.
			$('a.link_dir').click()
		} else if (config.open_dirs > 0) {
			// Open all dirs in the selected level.
			self.elements.menu.find('a.link_dir').each(function(index, el) {
				if (config.open_dirs >= $(this).parents('ul').length) {
					$(this).click()
				}
			})
		}

		// Open all dirs the current file is in. (Reveal in side bar)
		$('a.link_file.active').parents('ul').prev().find('a.link_dir').not('.link_dir_open').each(function() {
			$(this).click()
		})

	}


	PineDocs.prototype.show_mobile_menu = function() {
		var self = this
		self.elements.mobile_nav_icon.attr('aria-expanded', 'true');
		self.elements.menu_wrapper.hide().removeClass('hidden').slideDown('fast')
	}


	PineDocs.prototype.hide_mobile_menu = function() {
		var self = this
		self.elements.mobile_nav_icon.attr('aria-expanded', 'false');
		self.elements.menu_wrapper.addClass('hidden')
	}


	PineDocs.prototype.filter_items = function(input) {
		var self = this
			filter = input.val().toUpperCase(),
			li = self.elements.menu.find('li')

		if (filter == '') {
			li.show()
			return
		}

		li.css('display', 'none')

		for (var i = 0; i < li.length; i++) {
			var list = $(li[i]),
				item = list.find("a")

			if (!list.hasClass('folder') && item.text().toUpperCase().indexOf(filter) > -1) {
				list.css('display', 'block')
				list.parents('ul').css('display', 'block')

				var folderLi = list.siblings('li.folder')
				folderLi.css('display', 'block')
				folderLi.find('i').addClass('fa-folder-open')
			}
		}
	}

	/** @arg file json object with response */
	PineDocs.prototype.render_download_link = function (file) {
		var self = this,
			div = document.createElement('div'),
			link = document.createElement('a'),
			p = document.createElement('p')

		div.classList.add('download');

		link.setAttribute('href', 'index.php?action=download&relative_path='+ file.relative_path);
		link.classList.add('download__link');
		link.innerText = 'Download file';
		link.setAttribute('target', '_blank');

		if (file.download_link) {
			p.innerText = 'File size is too large to render (max ' + config.render_max_file_size + ' MB)';
		} else {
			p.innerText = 'Unable to render requested file.';
		}

		div.appendChild(p);
		div.appendChild(link);

		self.elements.file_content.append(div);

		return div.length;
	}


	PineDocs.prototype.render_errors = function() {
		var self = this

		alertify.set('notifier','position', 'top-right');

		$.each(errors, function(key, error_message) {
			alertify.error(error_message);
		})
	}

	PineDocs.prototype.render_hidden_file = function(path) {
		var self = this

		// Already loaded before?
		if (self.loaded[path]) {
			window.location.hash = path
			self.render_file(self.loaded[path])
			$('.active').removeClass('active')
			return
		}

		$.ajax({
			url: '?',
			type: 'GET',
			dataType: 'json',
			data: {
				action: 'get_file_data',
				relative_path: path
			},
		}).done(function(response) {
			self.loaded[path] = response
			self.render_file(response)
			$('.active').removeClass('active')
		}).fail(function(response) {
			self.render_error_message('Error: could not load file: <u>' + decodeURIComponent(path) + '</u><br />File not found.')
		});
	}

	// Make data readable by the browser
	PineDocs.prototype.readable_data = function(data) {
		if ((data.type == 'svg') || (data.type == 'image' && data.extension == 'svg')) {
			return 'data:image/svg+xml;base64,' + data.content
		}

		switch (data.type) {
			case 'image':
				return 'data:image/gif;base64,' + data.content

			case 'pdf':
				var binary = atob(data.content)
				var bytes = new Uint8Array(binary.length)
				for (var i = 0; i < binary.length; i++) {
					bytes[i] = binary.charCodeAt(i)
				}

				return window.URL.createObjectURL(new Blob([bytes], { type: 'application/pdf' }))

			case 'audio':
				return 'data:audio/mp3;base64,' + data.content

			case 'video':
				return 'data:video/mp4;base64,' + data.content

			default:
				break
		}
	}

	// Get asset path
	PineDocs.prototype.get_asset_path = function(file_path, asset_path) {
		// Final URL
		let url = "#"

		// Check if the file is local
		if (asset_path.includes('://')) {
			// asset isn't local
			return url
		}

		// Path to file
		let base = /(.*\/)/g.exec(file_path)
		if (base !== null) {
			base = base[0].slice(0, -1)
		} else {
			base = ""
		}

		// Count the number of available parent files
		const available_parents = base.split('/').length

		// Count the number of times we have to go to the parent folder to find the file
		let requested_parents = asset_path.split('../').length - 1

		// Check if file is available
		if (available_parents >= requested_parents) {
			if (available_parents == requested_parents) {
				// If file is at the root of content_dir
				base = ""
				asset_path = asset_path.replaceAll('../', '')
			} else {
				// Goes up the directories
				while (requested_parents > 0) {
					base = base.split('/')
					base.pop()
					base = base.join('/')
					asset_path = asset_path.split("../").join("")
					requested_parents--
				}
			}

			url = base + "/" + asset_path
		}

		return url
	}

	// Init
	new PineDocs()

})
