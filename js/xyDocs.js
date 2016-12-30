$(function() {


	function nl2br(str) {
		str = str.replace(/\n/g, '<br />')
		str = str.replace(/\r/g, '<br />')
		return str
	}


	function format_bytes(bytes,decimals) {
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


	// xyDocs class
	var xyDocs = function() {
		var self = this

		// Elements
		self.elements = {
			menu: $('#menu'),
			content_path: $('#content_path'),
			content: $('#content'),
			file_content: $('#file_content'),
			loading: $('#loading')
		}

		// Loaded responses.
		self.loaded = {}

		// Init
		self.set_events()

		// Autoload file from URL anchor tag?
		// Click on the link, if it exists.
		$('a.link_file[href="' + window.location.hash + '"]').click()

		// Set file_cocntent position
		// self.fixed_content_position()

		// Open dirs automatically.
		self.pageload_open_dirs()
	}


	// Render file content
	xyDocs.prototype.render_file = function(data) {
		var self = this

		// Reset
		self.elements.file_content.html('')
		self.elements.file_content.css('white-space', 'normal')

		// Set content path
		self.elements.content_path.text(data.relative_path)
		var filesize = $('<span>').addClass('filesize')
		filesize.text(format_bytes(data.filesize))
		self.elements.content_path.append(filesize)


		// Render content
		if (data.extension == 'md' || data.extension == 'markdown') {
			// Markdown
			self.elements.file_content.html(marked(data.content))

			// Syntax highlighting
			self.elements.file_content.find('code').each(function(i, block) {
				if (config.code_transparent_bg) {
					$(this).addClass('nobg')
				}
				hljs.highlightBlock(block)
			})

		} else if (data.type == 'image') {
			// Image.
			var img = $('<img>').attr('src', 'data:image/gif;base64,' + data.content)
			self.elements.file_content.append(img)
		} else if (data.type == 'audio') {
			// Audio.
			var audio = $('<audio>').prop('controls', true).attr('src', 'data:audio/mp3;base64,' + data.content)
			self.elements.file_content.append(audio)
		} else if (data.type == 'video') {
			// Video.
			/*
			<video width="320" height="240" controls>
				<source src="movie.mp4" type="video/mp4">
				<source src="movie.ogg" type="video/ogg">
				Your browser does not support the video tag.
			</video>
			 */
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
			self.elements.file_content.html(nl2br(data.content))

			// Syntax highlighting
			self.elements.file_content.find('code').each(function(i, block) {
				hljs.highlightBlock(block)
			})
		}

		// Set title
		$('title').text(config.title + ' | ' + data.basename)

		// Set position.
		// self.fixed_content_position()
	}


	// Set events like 'click', 'scroll' etc.
	xyDocs.prototype.set_events = function() {
		var self = this

		// Event for clicking menu links.
		$('#menu').on('click', 'a.link_file', function(event) {
			var link = $(this)
			var href = $(this).attr('href').substr(1)

			// Set 'active' class.
			$('.active').removeClass('active')
			link.addClass('active')

			// Already loaded before?
			if (self.loaded[href]) {
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
				},
			})
			.done(function(response) {
				self.loaded[href] = response // testing.
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


		// Scroll
		$(window).scroll(function(e) {
			// self.fixed_content_position()
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
			if ($(this).attr('href').substr(0,1) == '#') {
				// Find the link in the menu and trigger a click on it.
				self.elements.menu.find('a[href="' + $(this).attr('href') + '"]').click()
			}
		})
	}


	// Show or hide loading.
	xyDocs.prototype.set_loading = function(state, message) {
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


	xyDocs.prototype.render_error_message = function(message) {
		var self = this
		self.elements.content_path.text('Error')
		var error_element = $('<div>').addClass('error').html(message)
		self.elements.file_content.html(error_element)
	}


	// Make sure the content always is visible on the screen.
	// Replaced by 'overflow' css... Let's see if it's better.
	/*
	xyDocs.prototype.fixed_content_position = function() {
		var self = this

		var top = self.elements.file_content[0].style.top // jquery returns the pixels instead of 'auto' in Chrome.
		var outerHeight = self.elements.file_content.outerHeight()

		if (outerHeight <= $(window).height()) {
			// If file_content is smaller than window height, just make the position fixed.
			self.elements.file_content
				.css('position', 'fixed')
				.css('top', 'auto')
		} else {
			// The file content is bigger than window height.
			// So make sure the content is following when user scrolls up or down.
			self.elements.file_content.css('position', 'absolute')

			if (top == 'auto') {
				self.elements.file_content.css('top', $(window).scrollTop() + 60 + 'px')
			} else {
				// User scrolled
				top = parseInt(top) - 60
				var bottom = top + outerHeight

				if ($(window).scrollTop() <= top) {
					// User scrolled up, over last top position.
					// So set the top positon up to the scroll position.
					self.elements.file_content.css('top', $(window).scrollTop() + 60 + 'px')
				} else if ($(window).scrollTop() >= (bottom - 240)) {
					// User scrolled down below the bottom content.
					// So make sure the bottom content follows, so it's fast to scroll up again.
					self.elements.file_content.css('top', $(window).scrollTop() - outerHeight + 300)
				}
			}
		}

		// Always make sure 'footer' is at bottom.
		$('footer').css('bottom', 0)
	}*/


	xyDocs.prototype.pageload_open_dirs = function() {
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
	}


	// Init
	new xyDocs()

})
