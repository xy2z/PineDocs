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
		self.scroll_top = {}

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


		// Render content
		if (data.download_link === true) {
			// File size is too large to render.
			self.render_download_link(data);
		} else if (data.extension == 'md' || data.extension == 'markdown') {
			// Markdown
			self.elements.file_content.html(marked(data.content))

			self.elements.file_content.find('code').each(function(i, block) {
				// Mermaid block
				if (config.enable_mermaidjs && block.classList.contains('language-mermaid')) {
					var new_block = document.createElement('div');
					new_block.classList.add('mermaid');
					new_block.innerHTML = block.innerHTML;
					block.parentNode.replaceChild(new_block, block);
				} else {
					// Syntax highlighting
					if (config.code_transparent_bg) {
						$(this).addClass('nobg')
					}
					hljs.highlightBlock(block)
				}
			})

			// MathJax
			if (config.enable_mathjax) {
				MathJax.typeset();
			}

			// MermaidJS
			if (config.enable_mermaidjs) {
				mermaid.initialize();
			}

		} else if (data.type == 'image') {
			// Image.
			var img = $('<img>').attr('src', 'data:image/gif;base64,' + data.content)
			self.elements.file_content.append(img)
		} else if (data.type == 'svg') {
			var svg = $('<img>').attr('src', 'data:image/svg+xml;base64,' + data.content)
			self.elements.file_content.append(svg)
		} else if (data.type == 'pdf') {
			// PDF.
			var binary = atob(data.content)
			var bytes = new Uint8Array(binary.length)
			for (var i = 0; i < binary.length; i++) {
				bytes[i] = binary.charCodeAt(i)
			}

			var pdf = $('<embed>').attr('src', window.URL.createObjectURL(new Blob([bytes], { type: 'application/pdf' })))
			pdf.attr('width', '100%')
			pdf.attr('height', '100%')
			self.elements.file_content.append(pdf)
		} else if (data.type == 'audio') {
			// Audio.
			var audio = $('<audio>').prop('controls', true).attr('src', 'data:audio/mp3;base64,' + data.content)
			self.elements.file_content.append(audio)
		} else if (data.type == 'video') {
			// Video.
			var video = $('<video>').prop('controls', true)
			var source = $('<source>').attr('src', 'data:video/mp4;base64,' + data.content)
			source.attr('type', 'video/mp4')
			video.append(source)
			self.elements.file_content.append(video)
		} else if (data.type == 'code') {
			var code = $('<code>').text(data.content).addClass('file')
			if (config.code_transparent_bg) {
				code.addClass('nobg')
			}
			self.elements.file_content.append(code)

			// Syntax highlighting
			self.elements.file_content.find('code').each(function(i, block) {
				hljs.highlightBlock(block)
			})
		} else {
			if (typeof data.content == 'string') {
				self.elements.file_content.html(nl2br(data.content))

				// Syntax highlighting
				self.elements.file_content.find('code').each(function(i, block) {
					hljs.highlightBlock(block)
				})
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


		// Click on internal link. (links to other files)
		self.elements.file_content.on('click', 'a', function(event) {
			self.click_hashchange = true

			if ($(this).attr('href').substr(0,1) == '#') {
				// Find the link in the menu and trigger a click on it.
				var link = self.elements.menu.find('a[href="' + $(this).attr('href') + '"]')
				if (link.length) {
					link.click()
				} else {
					// Try loading a hidden file.
					self.render_hidden_file($(this).attr('href').substr(1));
				}
			}
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

	// Init
	new PineDocs()

})
