<?php
namespace ZFTool\Model;

use Zend\Code\Generator\ValueGenerator;

class Skeleton
{

    const SKELETON_URL    = 'https://github.com/zendframework/ZendSkeletonApplication/archive/master.zip';
    const API_LAST_COMMIT = 'https://api.github.com/repos/zendframework/ZendSkeletonApplication/commits?per_page=1';
    const SKELETON_FILE   = 'ZF2SA';

    protected static $valueGenerator;

    /**
     * Get the last commit data of the ZendSkeletonApplication github repository
     *
     * @return array|boolean
     */
    public static function getLastCommit()
    {
        $content = json_decode(@file_get_contents(self::API_LAST_COMMIT, false, self::getContextProxy()), true);
        if (is_array($content)) {
            return $content[0];
        }
        return false;
    }

    /**
     * Download the ZF2 Skeleton Application as .zip in a file
     *
     * @param  string $file
     * @return boolean
     */
    public static function getSkeletonApp($file)
    {
        $content = @file_get_contents(self::SKELETON_URL, false, self::getContextProxy());
        if (empty($content)) {
            return false;
        }
        return (file_put_contents($file, $content) !== false);
    }

    /**
     * Get the most updated .zip skeleton file in $dir
     *
     * @param  string $dir
     * @return string
     */
    public static function getLastZip($dir)
    {
        $files = glob("$dir/" . self::SKELETON_FILE . "_*.zip");
        $last  = 0;
        $file  = '';
        foreach ($files as $f) {
            if (filemtime($f) > $last) {
                $file = $f;
            }
        }
        return $file;
    }

    /**
     * Get the .zip file name based on the last commit
     *
     * @param  string $dir
     * @param  array $commit
     * @return string
     */
    public static function getTmpFileName($dir, $commit)
    {
        $filename = '';
        if (is_array($commit) && isset($commit['sha'])) {
            $filename = $dir . '/' . self::SKELETON_FILE . '_' . $commit['sha'] . '.zip';
        }
        return $filename;
    }

    /**
     * Get stream context for proxy, if necessary
     *
     * @return null|resource
     */
    public static function getContextProxy()
    {

        $proxyURL = getenv('HTTP_PROXY');

        if (!$proxyURL) {
            return;
        }

        $config_env = explode('@', $proxyURL);

        $auth = base64_encode(str_replace('http://', '', $config_env[0]));

        $aContext = array(
            'http' => array(
                'proxy' => 'tcp://' . $config_env[1],
                'request_fulluri' => true,
                'header' => "Proxy-Authorization: Basic $auth",
            ),
        );

        return stream_context_create($aContext);
    }
}
