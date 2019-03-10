
/**
 * Customizes file input fields across browsers with jQuery
 *
 * @author  Alexander Vourtsis
 * @version	0.6.1
 * @updated	13 March 2013, 11:27 UTC+02:00
 * @license	The Unlicense
 */

$.fn.fileInput = function(options) {

	var defaults = {
		filenameClass: 'filename', //the CSS class for the filename part of the browse field
		browseButtonClass: 'browse', //the CSS class for the browse button on the browse field
		filenameHoverClass: 'filename-hover', //the CSS class for hover and focus states of the filename part
		browseButtonHoverClass: 'browse-hover',  //the CSS class for hover and focus states of the browse button part
		filenameDisabledClass: 'filename-disabled', //the disabled CSS class of the filename part
		browseButtonDisabledClass: 'browse-disabled', //the disabled CSS class of the browse button part
		renderFilename: true, //whether to render the filename part or not
		renderBrowseButton: true, //whether to render the browse button part or not
		labelFilename: 'No file selected', //label text when no file has been selected
		labelBrowseButton: 'Browse&#8230;', //label text for the browse button
		labelMultipleSelected: 'files selected', //label text when seleting multiple files
		labelMultipleCount: false, //file count label text when seleting multiple files
		labelFilenameDisabled: 'Cannot select file', //label text for filename part when file input is disabled
		labelBrowseButtonDisabled: 'Browse&#8230;', //label text for the browse button when file input is disabled
		labelFilenameMaxChars: 0, //maximum characters until the filename text gets cut off (text overflow), use 0 to let CSS take care of that (better)
		labelFilenameEllipsis: '&#8230;', //character to use for the text overflow, usually ellipsis
		fileDragAndDrop: true, //some browsers let you drag and drop files onto the file input, this option can disable that if you have a reason to disallow it
		debugMode: false //whether to show the udnerlying file input and how it behaves, for testing purposes
	};
	
	var options = $.extend(defaults, options);

	return this.each(function() {
		
		/* browser specific: deactivate styling for IE7 and lower */
		if (navigator.appVersion.indexOf("MSIE") != -1) {
			if (parseFloat(navigator.appVersion.split("MSIE")[1]) < 8){
				return false;
			}
		}
	
		var fileContainer = $(this);
		fileContainer.css({'overflow':'hidden'});
		
		var fileContainerPosition = fileContainer.offset();
		
		var fileInput = $(this).children('input[type="file"]');
		
		var offset = {
			width: fileInput.width() - 2,
			height: (fileInput.height() / 2) +3
		};
		
		/* clear any classes or style from file input field */
		fileInput.attr('class','');
		fileInput.attr('style','');
		
		/* add filename part */
		if (options.renderFilename == true) {
			fileContainer.prepend('<span class="'+options.filenameClass+'">'+options.labelFilename+'</span>');
		}
		
		/* add button part */
		if (options.renderBrowseButton == true) {
			fileContainer.prepend('<span class="'+options.browseButtonClass+'">'+options.labelBrowseButton+'</span>');
		}
		
		/* hide the file input field with opacity */
		fileInput.css({
			'position':'absolute',
			'overflow':'hidden',
			'opacity':'0',
			'-moz-opacity':'0',
			'height':'100%',
			'outline':'0',
			'z-index': parseInt(fileContainer.css('z-index')+1, 10)		
		});
		
		if (options.debugMode) {
			fileInput.css({
				'opacity':'0.5',
				'-moz-opacity':'0.5'
			});
		}
			
		var fileName = fileContainer.children('.'+options.filenameClass);
		var fileBrowse = fileContainer.children('.'+options.browseButtonClass);
		
		/* expand widths to 100% if any of the two elements is not set to visible */
		if (options.renderFilename == false && options.renderBrowseButton == true) {
			fileBrowse.css('width', '100%');
		}
		
		if (options.renderBrowseButton == false && options.renderFilename == true) {
			fileName.css('width', '100%');
		}
		
		/* Chrome forces title attribute, let's add our own custom title text instead for all browsers */
		fileInput.attr('title', options.labelFilename);
		
		/* remove drag and drop functionality */
		if (options.fileDragAndDrop == false) {
			$(fileInput).on({
			   dragenter: function (e) {
					e.stopPropagation();
					e.preventDefault();
					var dt = e.originalEvent.dataTransfer;
					dt.effectAllowed = dt.dropEffect = 'none';
			   },
			   dragover: function (e) {
					e.stopPropagation();
					e.preventDefault();
					var dt = e.originalEvent.dataTransfer;
					dt.effectAllowed = dt.dropEffect = 'none';
				}
			});
		}
	
		/* add disabled class if the input field is disabled */
		if (fileInput.attr('disabled')) {
			fileInput.attr('title', options.labelFilenameDisabled);
			
			fileContainer.children('.'+options.filenameClass).addClass(options.filenameDisabledClass);
			fileContainer.children('.'+options.browseButtonClass).addClass(options.browseButtonDisabledClass);
			
			fileContainer.children('.'+options.filenameDisabledClass).html(options.labelFilenameDisabled);
			fileContainer.children('.'+options.browseButtonDisabledClass).html(options.labelBrowseButtonDisabled);
		}
		
		
		if (!(fileInput.attr('disabled'))) {
		
			/* add hover class if user mouse overs the file field */
			
			if (options.filenameHoverClass!='') {
				fileContainer.on('mouseover', function(){
					fileContainer.children('.'+options.filenameClass).addClass(options.filenameHoverClass);
				});
				fileContainer.on('mouseout', function(){
					fileContainer.children('.'+options.filenameClass).removeClass(options.filenameHoverClass);
					fileContainer.children('.'+options.filenameHoverClass).addClass(options.filenameClass);
				});
			}
			
			if (options.browseButtonHoverClass!='') {
				fileContainer.on('mouseover', function(){
					fileContainer.children('.'+options.browseButtonClass).addClass(options.browseButtonHoverClass);
				});
				fileContainer.on('mouseout', function(){
					fileContainer.children('.'+options.browseButtonClass).removeClass(options.browseButtonHoverClass);
					fileContainer.children('.'+options.browseButtonHoverClass).addClass(options.browseButtonClass);
				});
			}
			
			/* add hover class if user focuses the file field and reset CSS left top of actual file input so it doesn't overflow wrong */
			fileInput.on('focus', function(){
				fileContainer.children('.'+options.filenameClass).addClass(options.filenameHoverClass);
				fileContainer.children('.'+options.browseButtonClass).addClass(options.browseButtonHoverClass);
				fileInput.css({
					'outline': '0',
					'top': '0px'
				});
			});
			
			fileInput.on('blur', function(){
				fileContainer.children('.'+options.filenameClass).removeClass(options.filenameHoverClass);
				fileContainer.children('.'+options.browseButtonClass).removeClass(options.browseButtonHoverClass);
			});
			
		}
		
		fileContainer.on('mouseover', function(){
			fileContainerPosition = fileContainer.offset();
		});
		
		/* disable right clicking */
		fileContainer.children().on("contextmenu",function(e){
			return false;
		});
		
		/* return filename */
		fileInput.on('change', function(e){
			
			var selectedFile = fileInput.val();
			selectedFile = selectedFile.replace(/^.*[\\\/]/, '');
			
			var selectedFileTitle = '';
			
			if (this.files) {
				if (fileInput.attr('multiple')) {
					if (this.files.length > 1) {
						if (options.labelMultipleCount == true) {

							for (i=0; i<this.files.length ; i++) {
								selectedFileTitle = selectedFileTitle+this.files[i].name+' \n';
							}
							
							selectedFileTitle = this.files.length+' '+options.labelMultipleSelected+': \n'+selectedFileTitle;
							selectedFile = this.files.length+' '+options.labelMultipleSelected;
							
						} else {
						
							var filenames = [];
							for (i=0; i<this.files.length ; i++) {
								filenames.push(this.files[i].name);
							}
							selectedFile = filenames.join(', ');
							
						}
					} 
				}
			}
			
			if (options.labelFilenameMaxChars!=0) {
				if (selectedFile.length > options.labelFilenameMaxChars) { 
					fileName.html(selectedFile.substr(0, options.labelFilenameMaxChars)+options.labelFilenameEllipsis); 
					fileInput.attr('title', selectedFileTitle);
				} else { 
					fileName.html(selectedFile);
					fileInput.attr('title', selectedFileTitle);
				}
			} else {
				fileInput.attr('title', selectedFileTitle);
				fileName.html(selectedFile);
			}
		});
		
		fileInput.on('mousemove touchmove touchstart touchend', function(e){
			fileInput.css('left', e.pageX - fileContainerPosition.left - offset.width + 8);
			fileInput.css('top', e.pageY - fileContainerPosition.top - offset.height + 4);	
		});
		
		fileContainer.on('mousemove touchmove touchstart touchend', function(e){
			fileInput.css('left', e.pageX - fileContainerPosition.left - offset.width + 8);
			fileInput.css('top', e.pageY - fileContainerPosition.top - offset.height + 4);	
		});
		
	});
};
