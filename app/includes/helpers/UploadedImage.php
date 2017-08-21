<?php

ini_set('memory_limit', '256M');

/**
 * UploadedImage: Represents PNG or JPEG images which have been uploaded using
 * a form in the multipart/form-data format
 */
class UploadedImage {

	/**
	 * Stretches the image to the given dimensions, irrespective of the target aspect ratio.
	 */
	const SCALE_MODE_STRETCH = 0;

	/**
	 * Fills the entire resulting image by scaling the minimum dimension (width or height) to be the
	 * respective target dimension, while preserving its aspect ratio. The image is then centred
	 * while maintaining this relationship.
	 */
	const SCALE_MODE_ASPECT_FILL = 1;

	/**
	 * Represents the resource of the image read using GD.
	 */
	public $image_resource;

	/**
	 * Represents the location of the image on the file system
	 */
	private $path;

	/**
	 * Constructs a new UploadedImage object.
	 * @param string $uploaded_file The object stored in $_FILES corresponding to the image
	 */
	function __construct($uploaded_file) {

		if (!isset($uploaded_file['tmp_name'])) {
			throw new APIError(400, 'Attempted to save a file which was not uploaded');
		}

		//Now we set the path to the one assigned by PHP on upload

		$this->path = $uploaded_file['tmp_name'];

		if (!file_exists($this->path)) {
			throw new APIError(500, 'Attempted to read an uploaded file which no longer exists');
		}

		//Before we can load the image, we need to figure out what type it is

		$info = finfo_open(FILEINFO_MIME_TYPE);
		$mime_type = finfo_file($info, $this->path);

		switch ($mime_type) {
			case 'image/png':
				$this->image_resource = imagecreatefrompng($this->path);
				break;
			case 'image/jpeg':
				$this->image_resource = imagecreatefromjpeg($this->path);
				$this->exif_auto_rotate();
				break;
			default:
				throw new APIError(400, 'The uploaded file is in an unsupported format, only PNG and JPEG images are supported');
		}

	}


	/**
	 * Determines if the EXIF orientiation property is set, and rotates the image if necessary.
	 */
	private function exif_auto_rotate() {

		$exif_data = exif_read_data($this->path);

		if (!$exif_data || empty($exif_data)) {
			return;
		}

		$exif_rotate_map = [
			3 => 180,
			6 => -90,
			8 => 90
		];

		if (in_array($exif_data['Orientation'] ?? 0, array_keys($exif_rotate_map))) {
			$this->image_resource = imagerotate($this->image_resource, $exif_rotate_map[$exif_data['Orientation']], 0);
		}

	}


	/**
	 * Resizes the image to the given width and height, using one of two scale modes.
	 *
	 * @param integer $target_width The desired height of the resized image.
	 * @param integer $target_height The desired height of the resized image.
	 *
	 * @param integer $scale_mode The scale mode to use when resizing the image.
	 * Default is UploadedImage::SCALE_MODE_STRETCH, which stretches the image to the given dimensions.
	 *
	 */
	public function resize($target_width, $target_height, $scale_mode = SCALE_MODE_STRETCH) {

		$new_image = imagecreatetruecolor($target_width, $target_height);
		$white = imagecolorallocate($new_image, 255, 255, 255);

		//Make the background white in case there's any parts which aren't filled
		imagefill($new_image, 0, 0, $white);

		//Get its dimensions

		$dimensions = [
			'width' => imagesx($this->image_resource),
			'height' => imagesy($this->image_resource)
		];

		if ($scale_mode == self::SCALE_MODE_ASPECT_FILL) {

			$aspect_ratio = $dimensions['width'] / $dimensions['height'];

			//We need to determine if scaling the horizontal or vertical axis will produce dimensions
			//which are at least those required.

			$scale_height_dimensions = [
				'width' => $aspect_ratio * $target_height,
				'height' => $target_height
			];

			$scale_width_dimensions = [
				'width' => $target_width,
				'height' => 1 / $aspect_ratio * $target_width
			];

			//Figure out if we need to scale the width or the height

			if ($scale_height_dimensions['width'] >= $target_width && $scale_height_dimensions['height'] >= $target_height) {
				$new_dimensions = $scale_height_dimensions;
			} else {
				$new_dimensions = $scale_width_dimensions;
			}

			//Scale the image

			$new_image = imagescale($this->image_resource, $new_dimensions['width'], $new_dimensions['height']);

			//Now we crop the rectangle of the desired size out of the original image, but first we need to determine
			//its position within its container (i.e. we need to centre it)

			$bounding_rect = [
				'x' => $new_dimensions['width'] / 2 - $target_width / 2,
				'y' => $new_dimensions['height'] / 2 - $target_height / 2,
				'width' => $target_width,
				'height' => $target_height
			];

			//Now we can actually crop the thing

			$new_image = imagecrop($new_image, $bounding_rect);

		} else {

			//Just stretch it otherwise

			imagecopyresampled($new_image, $this->image_resource, 0, 0, 0, 0, $target_width, $target_height, $dimensions['width'], $dimensions['height']);

		}

		//We're done with the original now

		imagedestroy($this->image_resource);

		//And new point that to the new one

		$this->image_resource = $new_image;

	}


	/**
	 * Returns the JPEG representation of the modified image.
	 */
	public function jpeg_data($quality = 75) {

		ob_start();
		imagejpeg($this->image_resource, NULL, $quality);

		return ob_get_clean();

	}


	/**
	 * Returns the PNG representation of the modified image
	 */
	public function png_data() {

		ob_start();
		imagepng($this->image_resource);

		return ob_get_clean();

	}

	/**
	 * Cleans up now that we're done processing
	 */
	function __destruct() {
		if (!is_null($this->image_resource)) {
			imagedestroy($this->image_resource);
		}
	}

}

?>
