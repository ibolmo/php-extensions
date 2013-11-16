<?php

function array2style($array)
{
	$buffer = '';
	foreach ($array as $key => $value) $buffer .= sprintf('%s:%s;', $key, $value);
	return $buffer;
}

function array_clone($array)
{
	return array_merge(array(), $array);
}

function array_contains($array, $item){
	return array_search($item, $array) !== false;
}

function array_erase(&$array, $item){
	foreach ($array as $i => $v){
		if ($array[$i] === $item) array_splice($array, $i, 1);
	}
	return $array;
}

function array_get($object, $key, $default = null){
	if (empty($object)) return null;
	$props = explode('.', $key);
	foreach ($props as $prop){
		if (isset($object[$prop])){
			$object = $object[$prop];
		} else {
			$object = $default;
			break;
		}
	}
	return $object;
}

function array_has($array, $key){
	return !empty($array) && array_key_exists($key, $array);
}

function array_include(&$array, $item){
	if (!array_contains($array, $item)) $array[] = $item;
	return $array;
}

function camelize($string){
	$string = str_replace(array('-', '_'), ' ', $string);
	return str_replace(' ', '', ucwords($string));
}

/*
	note(ibolmo): Assumes headers are clean and formatted correctly.
*/
function csv2json($input, $output)
{
	if (!is_readable($input)) throw new \Exception("Input file '$input' is not readable.");
	$csv = file_get_contents($input);

	$data = str_getcsv($csv, "\n");
	$headers = str_getcsv(array_shift($data));
	foreach ($data as $i => $datum) $data[$i] = array_combine($headers, str_getcsv($datum));

	$dir = dirname($output);
	if (!is_writable($dir)) throw new sfException("Output directory '$dir' is not writable.");
	return file_put_contents($output, json_encode($data));
}

function curl_get_contents($url){
	$ch = curl_init();
	return do_curl($ch, array(
		CURLOPT_HEADER => 0,
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => 1
	));
}

function curl_post_contents($url, $fields)
{
	$ch = curl_init();
	return do_curl($ch, array(
		CURLOPT_POST => 1,
		CURLOPT_HEADER => 0,
		CURLOPT_URL => $url,
		CURLOPT_FRESH_CONNECT => 1,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_FORBID_REUSE => 1,
		CURLOPT_POSTFIELDS => http_build_query($fields)
	));
}

function do_curl($ch, $options)
{
	$output = null;

	curl_setopt_array($ch, $options);

	if (defined('CURL_USE_CACHE')){
		$file = sys_get_temp_dir().'/curl_cache.json';
		$cache = array();
		if (file_exists($file) && time() < filemtime($file) + 600) $cache = json_decode(file_get_contents($file), true);
		$key = serialize($options);
		if (array_key_exists($key, $cache)) $output = $cache[$key];
	}

	if (!$output){
		$output = curl_exec($ch);
		if ($output && defined('CURL_USE_CACHE')){
			$cache[$key] = $output;
			file_put_contents($file, json_encode($cache));
		}
	}

	if (!$output) trigger_error(curl_error($ch));
	curl_close($ch);

	return $output;
}

function dd($object)
{
	var_dump($object);
	die;
}

function encode($string, $flags = ENT_COMPAT, $encoding = 'UTF-8')
{
	return htmlentities($string, $flags, $encoding);
}

function favorable_prompt($question)
{
	return preg_match('/^y/i', prompt($question));
}

function hostname()
{
	return basename(realpath(__DIR__ . '/../../'));
}

function hyphenate($string)
{
	return str_replace(' ', '_', trim(preg_replace('/[A-Z]/e', 'strtolower(" $0")', $string)));
}

function is_local()
{
	if (class_exists('sfConfig') && sfConfig::get('sf_environment') !== null){
		return sfConfig::get('sf_environment') == 'dev';
	}
	return in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1', @$_SERVER['SERVER_ADDR']));
}

function is_url($url)
{
	return strpos(trim($url), 'http') === 0;
}

function json_file_to_array($file)
{
	if (!is_readable($file)) throw new \Exception("Missing '$file' file.");

	$content = file_get_contents($file);
	if (!$content) throw new \Exception("Could not open, or empty file: '$file'.");

	$content = json_decode($content, true);
	if ($content == null) throw new \Exception("Could not decode JSON file: '$file'.");

	return $content;
}

function by_chance($chance = 0.5)
{
	if ($chance >= 1) return true;
	if ($chance <= 0) return false;
	$size = pow($chance, -1);
	return rand(0, $size) < ($chance * $size);
}

function pluralize($singular)
{
	return $singular . 's';
}

function prompt($query)
{
	return readline($query . ' ');
}

function singularize($string)
{
	return substr($string, 0, -1);
}

function slugify($text)
{
	$text = preg_replace('~[^\\pL\d]+~u', '-', $text);
	$text = trim($text, '-');
	$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
	$text = strtolower($text);
	$text = preg_replace('~[^-\w]+~', '', $text);
	return $text ?: '';
}

function substitute($string, array $object)
{
	return preg_replace_callback('/\{([^{}]+)\}/', function($matches) use ($object){
		return (string) ((isset($matches[1]) && isset($object[$matches[1]])) ? $object[$matches[1]] : $matches[0]);
	}, $string);
}

function truncate($string, $words){
	$bits = explode(' ', trim($string));
	$subset = array_slice($bits, 0, $words);
	return array(implode(' ', $subset), count($subset) < count($bits));
}

function location()
{
	return sprintf('%s://%s%s', (isset($_SERVER['HTTPS']) ? 'https' : 'http'), $_SERVER['SERVER_NAME'], $_SERVER['REQUEST_URI']);
}

function unset_each(&$object, $keys = array())
{
	foreach ($keys as $key) unset($object[$key]);
}
