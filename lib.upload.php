<?php

/**
 * Handles HTTP file uploads
 *
 * @author  Alexander Vourtsis
 * @version	1.0.0
 * @updated	05 November 2012, 04:47 UTC+02:00
 * @license	The Unlicense
 */

class lib_upload
{
	
	private $upload_max_filesize = 20000000;
	private $upload_max_width = 1000;
	private $upload_max_height = 1000;
	private $upload_min_width = 0;
	private $upload_min_height = 0;
	
	private $upload_allowed_extensions = array('jpg', 'png', 'gif');
	private $upload_allowed_mime_types = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif');
	
	private $magic_filename = ''; /* /usr/share/file/magic */
	private $upload_folder = 'uploads/';
	private $upload_error = 0;
	private $uploaded_file = '';
	
	private $upload_mime_type_check = true;
	private $upload_filename_hash = false;
	private $upload_filename_date = false;
	private $upload_filename_time = false;
	private $upload_file_overwrite = false;
	
	/**
	 * Uploads a file to the server
	 *
	 * @param  upload_input_file The input file
	 * @param  file_index        The index in the file array, useful when multi uploading
	 * @return The full filepath if file has been uploaded successfully
	 */
	public function file_upload($upload_input_file, $file_index = -1)
	{
		if ($file_index != -1) {
			$upload_file_name = $_FILES[$upload_input_file]['name'][$file_index];
			$upload_temp_name = $_FILES[$upload_input_file]['tmp_name'][$file_index];
			$upload_file_size = $_FILES[$upload_input_file]['size'][$file_index];
			$upload_file_error = $_FILES[$upload_input_file]['error'][$file_index];
		} else {
			$upload_file_name = $_FILES[$upload_input_file]['name'];
			$upload_temp_name = $_FILES[$upload_input_file]['tmp_name'];
			$upload_file_size = $_FILES[$upload_input_file]['size'];
			$upload_file_error = $_FILES[$upload_input_file]['error'];
		}
		
		$this->uploaded_file = '';
		
		if (!is_dir($this->upload_folder)) {
			$this->upload_error = 1;
			return false;
		}
		
		$upload_folder = $this->upload_folder;
		
		if ($upload_file_name == '' || $upload_file_name == null || empty($upload_file_name)) {
			$this->upload_error = 2;
			return false;
		}
		
		$upload_file_info = pathinfo($upload_file_name);
		$upload_file_base_name = $upload_file_info['filename'];
		$upload_file_extension = $upload_file_info['extension'];
		
		if (isset($this->upload_allowed_extensions)) {
			if (!in_array($upload_file_extension, $this->upload_allowed_extensions)) {
				$this->upload_error = 3;
				return false;
			} 
		}
		
		if ($upload_file_error == 2 || $upload_file_size > $this->upload_max_filesize) {
			$this->upload_error = 5;
			return false;
		}
		
		list($fileWidth, $fileHeight) = getimagesize($upload_temp_name);
		
		if (($fileWidth > $this->upload_max_width || $fileHeight > $this->upload_max_height)) {
			$this->upload_error = 6;
			return false;
		}
		if (($fileWidth < $this->upload_min_width || $fileHeight < $this->upload_min_height)) {
			$this->upload_error = 7;
			return false;
		}
		
		if ($this->upload_filename_hash == true) {
			$upload_file_base_name = md5($upload_file_base_name);
		} else {
			$upload_file_base_name = $this->_slug($upload_file_base_name, 255);
		}
		
		if (file_exists($upload_folder.$upload_file_base_name.'.'.$upload_file_extension) && $this->upload_file_overwrite == false) {
			$upload_file_base_name = $upload_file_base_name.'-'.uniqid();
		} 
		
		if ($this->upload_filename_date == true) {
			$upload_file_base_name = $upload_file_base_name.'_'.date("d-m-Y");
		}
		if ($this->upload_filename_time == true) {
			$upload_file_base_name = $upload_file_base_name.'_'.date("H-i-s");
		}
		
		$upload_file_name = $upload_file_base_name.'.'.$upload_file_extension;
		
		if (move_uploaded_file($upload_temp_name, $upload_folder.$upload_file_name)) {
			
			if (isset($this->upload_allowed_mime_types) && $this->upload_mime_type_check == true) {
				
				if (function_exists('finfo_open')) {
				
					if ($this->magic_filename !='') {
						$finfo = finfo_open(FILEINFO_MIME, $this->magic_filename);
					} else {
						$finfo = finfo_open(FILEINFO_MIME);
					}
					
					if (!$finfo) {
						$this->upload_error = 9;
						return false;
					}
					
					$get_mimetype = finfo_file($finfo, realpath($upload_folder).DIRECTORY_SEPARATOR.$upload_file_name);
					$get_mimetype = explode(';', $get_mimetype);
					finfo_close($finfo);
					
					if (!in_array($get_mimetype[0], $this->upload_allowed_mime_types)) {
						unlink(realpath($upload_folder).DIRECTORY_SEPARATOR.$upload_file_name);
						$this->upload_error = 4;
						return false;
					}
				}
			}
		
			$this->upload_error = 0;
			$this->uploaded_file = realpath($upload_folder).DIRECTORY_SEPARATOR.$upload_file_name;
			return $this->uploaded_file;
			
		} else {
			$this->upload_error = 8;
			return false;
		}
	}

	
	/**
	 * Sets an option for the uploader object
	 *
	 * @param option The option to change
	 * @param value  The new value for the option
	 */
	public function set_option($option, $value)
	{
		if ($option == 'uploadfolder') {
			$this->upload_folder = $value;
		}
		
		else if ($option == 'maxfilesize') {
			$this->upload_max_filesize = $value;
		}
		
		else if ($option == 'maxwidth') {
			$this->upload_max_width = $value;
		}
		
		else if ($option == 'maxheight') {
			$this->upload_max_height = $value;
		}
		
		else if ($option == 'minwidth') {
			$this->upload_min_width = $value;
		}
		
		else if ($option == 'minheight') {
			$this->upload_min_height = $value;
		}
		
		else if ($option == 'filenamehash') {
			$this->upload_filename_hash = $value;
		}
		
		else if ($option == 'filenamedate') {
			$this->upload_filename_date = $value;
		}
		
		else if ($option == 'filenametime') {
			$this->upload_filename_time = $value;
		}
		
		else if ($option == 'overwrite') {
			$this->upload_file_overwrite = $value;
		}
		
		else if ($option == 'mimetypecheck') {
			$this->upload_mime_type_check = $value;
		}
		
		else if ($option == 'allowedextensions') {
			$this->upload_allowed_extensions = $value;
		}
		
		else if ($option == 'allowedmimetypes') {
			$this->upload_allowed_mime_types = $value;
		}
	}
	
	
	/**
	 * Returns an option of the uploader object
	 *
	 * @param  option The option to return its value
	 * @return The value of the selected option
	 */
	public function get_option($option)
	{
		if ($option == 'uploadfolder') {
			return $this->upload_folder;
		} 
		
		else if ($option == 'maxfilesize') {
			return $this->upload_max_filesize;
		}
		
		else if ($option == 'maxwidth') {
			return $this->upload_max_width;
		}
		
		else if ($option == 'maxheight') {
			return $this->upload_max_height;
		}
		
		else if ($option == 'minwidth') {
			return $this->upload_min_width;
		}
		
		else if ($option == 'minheight') {
			return $this->upload_min_height;
		}
		
		else if ($option == 'filenamehash') {
			return $this->upload_filename_hash;
		}
		
		else if ($option == 'filenamedate') {
			return $this->upload_filename_date;
		}
		
		else if ($option == 'filenametime') {
			return $this->upload_filename_time;
		}
		
		else if ($option == 'overwrite') {
			return $this->upload_file_overwrite;
		}
		
		else if ($option == 'mimetypecheck') {
			return $this->upload_mime_type_check;
		}
		
		else if ($option == 'allowedextensions') {
			return $this->upload_allowed_extensions;
		}
		
		else if ($option == 'allowedmimetypes') {
			return $this->upload_allowed_mime_types;
		}
		
		return false;
	}
	
	
	/**
	 * Returns the uploaded filename from the last upload operation
	 *
	 * @return The filename in full path format
	 */
	public function get_uploaded_file()
	{
		return $this->uploaded_file;
	}
	
	
	/**
	 * Returns the max filesize setting of the uploader object in a human readable format
	 *
	 * @param  format The format to return the filesize into, e.g. 'bytes', 'kb', 'mb', 'gb'
	 * @return The filesize in the specified format
	 */
	public function get_max_filesize($format = 'kb')
	{
		$size = $this->upload_max_filesize;
		
		if ($format == 'bytes') {
			return round($size, 2);
		}
		if ($format == 'kb') {
			$size = $size/1024;
			return round($size, 2);
		}
		if ($format == 'mb') {
			$size = $size/1024;
			$size = $size/1024;
			return round($size, 2);
		}
		if ($format == 'gb') {
			$size = $size/1024;
			$size = $size/1024;
			$size = $size/1024;
			return round($size, 2);
		}
	}
	
	
	/**
	 * Returns the allowed extensions of the uploader object in a human readable format
	 *
	 * @param  delimiter The delimiter with which to separate the extensions
	 * @return A string containing all the extensions separated by the delimiter
	 */
	public function get_allowed_extensions($delimiter = ', ')
	{
		return implode($delimiter, $this->upload_allowed_extensions);
	}
	
	
	/**
	 * Returns the allowed mime types of the uploader object in a human readable format
	 *
	 * @param  delimiter The delimiter with which to separate the mime types
	 * @return A string containing all the mime types separated by the delimiter
	 */
	public function get_allowed_mime_types($delimiter = ',')
	{
		return implode($delimiter, $this->upload_allowed_mime_types);
	}
	
	
	/**
	 * Returns the minimum dimensions of the uploader object in a human readable format
	 *
	 * @param  x The "x" symbol between the width and height values
	 * @return A string rendered as "width x height"
	 */
	public function get_min_dimensions($x = ' &#215; ')
	{
		return $this->upload_min_width.$x.$this->upload_min_height;
	}

	
	/**
	 * Returns the maximum dimensions of the uploader object in a human readable format
	 *
	 * @param  x The "x" symbol between the width and height values
	 * @return A string rendered as "width x height"
	 */
	public function get_max_dimensions($x = ' &#215; ')
	{
		return $this->upload_max_width.$x.$this->upload_max_height;
	}
	
	
	/**
	 * Returns the text of the error message from previous upload operation
	 *
	 * @return The text of the error message from previous upload operation
	 */
	public function get_error()
	{
		switch ($this->upload_error) {
			case 0:
				return '';
			break;
			case 1:
				return 'No upload folder found';
			break;
			case 2:
				return 'No file selected';
			break;
			case 3:
				return 'Invalid file extension';
			break;
			case 4:
				return 'Invalid file mime type';
			break;
			case 5:
				return 'Maximum filesize over the limit';
			break;
			case 6:
				return 'Maximum dimensions over the limit';
			break;
			case 7:
				return 'Minimum dimensions under the limit';
			break;
			case 8:
				return 'File could not be uploaded';
			break;
			case 9:
				return 'Could not check mime type';
			break;
			default:
				return 'An upload error occured';
		}
	}
	
	
	/**
	 * Returns the code of the error message from previous upload operation
	 *
	 * @return The code of the error message from previous upload operation
	 */
	public function get_error_code()
	{
		return $this->upload_error;
	}
	
	
	/**
	 * Creates a URL friendly string
	 *
	 * @param  string         The input string
	 * @param  max_characters A limit of characters for the output string
	 * @return A url friendly string
	 */
	private function _slug($string, $max_characters = 0)
	{
		$invalid_characters = array('à', 'á', 'â', 'ã', 'å', 'ǻ', 'ā', 'ă', 'ą', 'ǎ', 'ß', 'ç', 'ć', 'ĉ', 'ċ', 'č', 'ð', 'ď', 'đ', 'è', 'é', 'ê', 'ë', 'ē', 'ĕ', 'ė', 'ę', 'ě', 'ƒ', 'ĝ', 'ğ', 'ġ', 'ģ', 'ĥ', 'ħ', 'ì', 'í', 'î', 'ï', 'ĩ', 'ī', 'ĭ', 'ǐ', 'į', 'ı', 'ĵ', 'ķ', 'ĺ', 'ļ', 'ľ', 'ŀ', 'ł', 'ñ', 'ń', 'ņ', 'ň', 'ŉ', 'ò', 'ó', 'ô', 'õ', 'ō', 'ŏ', 'ǒ', 'ő', 'ơ', 'ø', 'ǿ', 'ŕ', 'ŗ', 'ř', 'ś', 'ŝ', 'ş', 'š', 'ſ', 'ţ', 'ť', 'ŧ', 'ù', 'ú', 'û', 'ũ', 'ū', 'ŭ', 'ů', 'ű', 'ų', 'ư', 'ǔ', 'ü', 'ǖ', 'ǘ', 'ǚ', 'ǜ', 'ý', 'ÿ', 'ŷ', 'ŵ', 'ź', 'ż', 'ž', 'ĳ', 'ä', 'æ', 'ǽ', 'ö', 'œ', 'α', 'β', 'γ', 'δ', 'ε', 'ζ', 'η', 'θ', 'ι', 'κ', 'λ', 'μ', 'ν', 'ξ', 'ο', 'π', 'ρ', 'σ', 'τ', 'υ', 'φ', 'χ', 'ψ', 'ω', 'ς', 'ά', 'έ', 'ή', 'ό', 'ί', 'ύ', 'ΰ', 'ώ', 'ϊ', 'ΐ', 'ϋ');

		$valid_characters = array('a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'b', 'c', 'c', 'c', 'c', 'c', 'd', 'd', 'd', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'f', 'g', 'g', 'g', 'g', 'h', 'h', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'j', 'k', 'l', 'l', 'l', 'l', 'l', 'n', 'n', 'n', 'n', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'r', 'r', 'r', 's', 's', 's', 's', 's', 't', 't', 't', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'y', 'y', 'y', 'w', 'z', 'z', 'z', 'ij', 'a', 'ae', 'ae', 'o', 'oe', 'a', 'v', 'g', 'd', 'e', 'z', 'i', 'th', 'i', 'k', 'l', 'm', 'n', 'ks', 'o', 'p', 'r', 's', 't', 'u', 'f', 'x', 'ps', 'o', 's', 'a', 'e', 'i', 'o', 'i', 'u', 'u', 'o', 'i', 'i', 'u');

		$string = strip_tags(mb_strtolower($string, 'UTF-8'));
		$string = str_replace($invalid_characters, $valid_characters, $string);

		$string = str_replace('.', '-', $string);
		$string = preg_replace('/[^a-z0-9\s-]/', '', $string);
		$string = trim(preg_replace('/[\s-]+/', ' ', $string));

		if ($max_characters != 0) {
			$string = trim(substr($string, 0, $max_characters));
		}

		$string = preg_replace('/\s/', '-', $string);

		return $string;
	}
	
}
?>