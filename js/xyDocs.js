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

		// Load index file.
		self.render_file(config.index_data)
		window.location.hash = config.index_data.relative_path;

		// Autoload file from URL anchor tag?
		// Click on the link, if it exists.
		$('a.link_file[href="' + window.location.hash + '"]').click()

		// Open dirs automatically.
		self.pageload_open_dirs()
	}


	// Render file content
	xyDocs.prototype.render_file = function(data) {
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

		// Scroll to top.
		self.elements.file_content.scrollTop(0)
	}


	// Set events
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

		// URL Hashtag change (user probably went back or forward in browser history)
		$(window).bind('hashchange', function(e) {
			// Click on the menu link if it exists.
			self.elements.menu.find('a[href="' + document.location.hash + '"]').click()
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

		// Open all dirs the current file is in. (Reveal in side bar)
		$('a.link_file.active').parents('ul').prev().find('a.link_dir').not('.link_dir_open').each(function() {
			$(this).click()
		})
	}


	// Init
	new xyDocs()

})
