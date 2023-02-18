<?php

class FileHandler
{
	private $config = [];
	private $re_partial = '/(?:\.part(?:-Frag\d+)?|\.ytdl)$/m';
    private $re_fragment = '/\.f[0-9]*\.(mp4|webm)$/m';
	private $info_json_extension = '/\.info\.json$/m';
	private $thumb_extension = '/\.(jpg|webp|png)$/m';

	public function __construct()
	{
		$this->config = require dirname(__DIR__).'/config/config.php';
	}

	public function listFiles()
	{
		$files = [];

		if(!$this->output_folder_exists())
			return;

		$folder = $this->get_downloads_folder().'/';

		foreach(glob($folder.'*.*', GLOB_BRACE) as $file)
		{
			$content = [];
			$content["name"] = str_replace($folder, "", $file);
			$content["changed"] = filemtime($file);
			$content["size"] = $this->to_human_filesize(filesize($file));
            $content["meta"] = [];
            $content["thumb"] = "";

            $file_path_info = pathinfo($file);
            $infoFile = $file_path_info['filename'] . '.info.json';
            $thumbFile = $file_path_info['filename'] . '.jpg';

            if(file_exists($folder.$infoFile)) {
                $content["changed"] = filemtime($folder.$infoFile); // the changed date of the infoFile reflects the download date better
                $meta_json = file_get_contents($folder.$infoFile);
                $meta = json_decode($meta_json, false);
                $content["meta"] = $meta;
            }

            if(file_exists($folder.$thumbFile)) {
                $content["thumb"] = $thumbFile;
            }

			if (preg_match($this->re_partial, $content["name"]) === 0 && preg_match($this->re_fragment, $content["name"]) === 0 && preg_match($this->info_json_extension, $content["name"]) === 0 && preg_match($this->thumb_extension, $content["name"]) === 0) {
				$files[] = $content;
			}

		}

        usort($files, function($a, $b) {
            switch ($_GET["sort"]??""){
                case "shortest":
                    return $a["meta"]->duration - $b["meta"]->duration;
                case "longest":
                    return $b["meta"]->duration - $a["meta"]->duration;
                case "a-z":
                    return strcasecmp($a["meta"]->title, $b["meta"]->title);
                case "z-a":
                    return strcasecmp($b["meta"]->title, $a["meta"]->title);
                case "oldest":
                    return $a["changed"] - $b["changed"];
                default:
                    return $b["changed"] - $a["changed"];
            }
        });
		return $files;
	}

	public function listParts()
	{
		$files = [];

		if(!$this->output_folder_exists())
			return;

		$folder = $this->get_downloads_folder().'/';

		foreach(glob($folder.'*.*', GLOB_BRACE) as $file)
		{
			$content = [];
			$content["name"] = str_replace($folder, "", $file);
			$content["size"] = $this->to_human_filesize(filesize($file));

			if (preg_match($this->re_partial, $content["name"]) !== 0) {
				$files[] = $content;
			}

		}

		return $files;
	}

	public function is_log_enabled()
	{
		return !!($this->config["log"]);
	}

	public function countLogs()
	{
		if(!$this->config["log"])
			return;

		if(!$this->logs_folder_exists())
			return;

		$folder = $this->get_logs_folder().'/';
		return count(glob($folder.'*.txt', GLOB_BRACE));
	}

	public function listLogs()
	{
		$files = [];

		if(!$this->config["log"])
			return;

		if(!$this->logs_folder_exists())
			return;

		$folder = $this->get_logs_folder().'/';

		foreach(glob($folder.'*.txt', GLOB_BRACE) as $file)
		{
			$content = [];
			$content["name"] = str_replace($folder, "", $file);
			$content["size"] = $this->to_human_filesize(filesize($file));

			try {
				$lines = explode("\r", file_get_contents($file));
				$content["lastline"] = array_slice($lines, -1)[0];
				$content["100"] = strpos($lines[count($lines)-1], ' 100% of ') > 0;
			} catch (Exception $e) {
				$content["lastline"] = '';
				$content["100"] = False;
			}
			try {
				$handle = fopen($file, 'r');
				fseek($handle, filesize($file) - 1);
				$lastc = fgets($handle);
				fclose($handle);
				$content["ended"] = ($lastc === "\n");
			} catch (Exception $e) {
				$content["ended"] = False;
			}


			$files[] = $content;
		}

		return $files;
	}

	public function delete($id)
	{
		$folder = $this->get_downloads_folder().'/';

		foreach(glob($folder.'*.*', GLOB_BRACE) as $file)
		{
			if(sha1(str_replace($folder, "", $file)) == $id)
			{
                $file_path_info = pathinfo($file);
                $infoFile = $file_path_info['filename'] . '.info.json';
                $jpg = $file_path_info['filename'] . '.jpg';
                $webp = $file_path_info['filename'] . '.webp';

                if(file_exists($folder.$infoFile)) {
                    unlink($folder.$infoFile);
                }
                if(file_exists($folder.$jpg)) {
                    unlink($folder.$jpg);
                }
                if(file_exists($folder.$webp)) {
                    unlink($folder.$webp);
                }
				unlink($file);
			}
		}
	}

	public function deleteLog($id)
	{
		$folder = $this->get_logs_folder().'/';

		foreach(glob($folder.'*.txt', GLOB_BRACE) as $file)
		{
			if(sha1(str_replace($folder, "", $file)) == $id)
			{
				unlink($file);
			}
		}
	}

	private function output_folder_exists()
	{
		if(!is_dir($this->get_downloads_folder()))
		{
			//Folder doesn't exist
			if(!mkdir($this->get_downloads_folder(),0777))
			{
				return false; //No folder and creation failed
			}
		}

		return true;
	}

	public function to_human_filesize($bytes, $decimals = 1)
	{
		$sz = 'BKMGTP';
		$factor = floor((strlen($bytes) - 1) / 3);
		return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
	}

	public function free_space()
	{
		return $this->to_human_filesize(disk_free_space(realpath($this->get_downloads_folder())));
	}

	public function used_space()
	{
		$path = realpath($this->get_downloads_folder());
		$bytestotal = 0;
		foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object){
			$bytestotal += $object->getSize();
		}
		return $this->to_human_filesize($bytestotal);
	}

	public function get_downloads_folder()
	{
		$path =  $this->config["outputFolder"];
		if(strpos($path , "/") !== 0)
		{
				$path = dirname(__DIR__).'/' . $path;
		}
		return $path;
	}

	public function get_logs_folder()
	{
		$path =  $this->config["logFolder"];
		if(strpos($path , "/") !== 0)
		{
				$path = dirname(__DIR__).'/' . $path;
		}
		return $path;
	}

	public function get_relative_downloads_folder()
	{
		$path =  $this->config["outputFolder"];
		if(strpos($path , "/") !== 0)
		{
				return $this->config["outputFolder"];
		}
		return false;
	}

	public function get_relative_log_folder()
	{
		$path =  $this->config["logFolder"];
		if(strpos($path , "/") !== 0)
		{
				return $this->config["logFolder"];;
		}
		return false;
	}

	private function logs_folder_exists()
	{
		if(!is_dir($this->get_logs_folder()))
		{
			//Folder doesn't exist
			if(!mkdir($this->get_logs_folder(),0777))
			{
				return false; //No folder and creation failed
			}
		}

		return true;
	}
}

?>
