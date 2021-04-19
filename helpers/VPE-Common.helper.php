<?php
class VPE_Common_Helper {
	/**
	 *
	 * Includes a file within this plugin.
	 *
	 * @date	1/3/2021
	 * @since	0.0
	 *
	 * @param	string $filename The specified file.
	 * @return	void
	 */
	public static function include($filename = '')
	{
		$file_path = constant('VPE_PATH') . ltrim($filename, '/');
		if (file_exists($file_path)) {
			include_once($file_path);
		}
	}
}
