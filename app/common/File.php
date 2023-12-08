<?php

namespace app\common;

use think\Facade;

class File extends Facade
{
    /*
		@function  		创建目录
		
		@var:$filename  目录名
			
		@return:   		true
	*/

    protected static function mk_dir($dir)
    {
        $dir = rtrim($dir, '/') . '/';
        if (!is_dir($dir)) {
            if (mkdir($dir, 0700) == false) {
                return false;
            }
            return true;
        }
        return true;
    }

    /**
     * 读取文件内容
     *
     * @param string $filename 
     * @param boolean $ignoreCase 忽略大小写
     * @return string
     */
    protected static function read_file($filename, $ignoreCase = false): string
    {
        $content = '';

        if (!$ignoreCase) {
            if (function_exists('file_get_contents')) {
                @$content = file_get_contents($filename);
            } else {
                if (@$fp = fopen($filename, 'r')) {
                    @$content = fread($fp, filesize($filename));
                    @fclose($fp);
                }
            }
        } else {
            $fileList = glob($filename, GLOB_NOCHECK);
            if ($fileList !== false && count($fileList) > 0) {
                $filename = $fileList[0];
                if (function_exists('file_get_contents')) {
                    @$content = file_get_contents($filename);
                } else {
                    if (@$fp = fopen($filename, 'r')) {
                        @$content = fread($fp, filesize($filename));
                        @fclose($fp);
                    }
                }
            }
        }

        return $content;
    }

    /*
		@function  		写文件
		
		@var:$filename  文件名
		
		@var:$writetext 文件内容
		
		@var:$openmod 	打开方式
			
		@return:   		成功=true
	*/

    protected static function write_file($filename, $writetext, $openmod = 'w')
    {
        if (@$fp = fopen($filename, $openmod)) {
            flock($fp, 2);
            fwrite($fp, $writetext);
            fclose($fp);
            return true;
        } else {
            return false;
        }
    }


    /*
		@function  		删除目录
		
		@var:$dirName  	原目录
			
		@return:   		成功=true
	*/

    protected static function del_dir($dirName)
    {
        if (!file_exists($dirName)) {
            return false;
        }

        $dir = opendir($dirName);
        while ($fileName = readdir($dir)) {
            $file = $dirName . '/' . $fileName;
            if ($fileName != '.' && $fileName != '..') {
                if (is_dir($file)) {
                    self::del_dir($file);
                } else {
                    unlink($file);
                }
            }
        }
        closedir($dir);
        return rmdir($dirName);
    }

    /*
		@function  		复制目录
		
		@var:$surDir  	原目录
		
		@var:$toDir  	目标目录
		
		@return:   		true
	*/

    protected static function copy_dir($surDir, $toDir)
    {
        $surDir = rtrim($surDir, '/') . '/';
        $toDir = rtrim($toDir, '/') . '/';
        if (!file_exists($surDir)) {
            return  false;
        }

        if (!file_exists($toDir)) {
            self::mk_dir($toDir);
        }
        $file = opendir($surDir);
        while ($fileName = readdir($file)) {
            $file1 = $surDir . '/' . $fileName;
            $file2 = $toDir . '/' . $fileName;
            if ($fileName != '.' && $fileName != '..') {
                if (is_dir($file1)) {
                    self::copy_dir($file1, $file2);
                } else {
                    copy($file1, $file2);
                }
            }
        }
        closedir($file);
        return true;
    }


    /*
		@function  列出目录
		
		@var:$dir  目录名
		
		@return:   目录数组
		
		列出文件夹下内容，返回数组 $dirArray['dir']:存文件夹；$dirArray['file']：存文件
	*/

    protected static function get_dirs($dir)
    {
        $dir = rtrim($dir, '/') . '/';
        $dirArray = array('dir' => array(), 'file' => array());

        if (false !== ($handle = opendir($dir))) {
            while (false !== ($file = readdir($handle))) {
                if (is_dir($dir . $file)) {
                    $dirArray['dir'][] = $file;
                } else {
                    $dirArray['file'][] = $file;
                }
            }
            closedir($handle);
        }

        // 对子数组进行排序
        sort($dirArray['dir']);
        sort($dirArray['file']);

        return $dirArray;
    }

    /*
		@function  统计文件夹大小
		
		@var:$dir  目录名
		
		@return:   文件夹大小(单位 B)
	*/

    protected static function get_size($dir)
    {
        $dirlist = opendir($dir);
        $dirsize = 0;
        while (false !==  ($folderorfile = readdir($dirlist))) {
            if ($folderorfile != "." && $folderorfile != "..") {
                if (is_dir("$dir/$folderorfile")) {
                    $dirsize += self::get_size("$dir/$folderorfile");
                } else {
                    $dirsize += filesize("$dir/$folderorfile");
                }
            }
        }
        closedir($dirlist);
        return $dirsize;
    }

    /*
		@function  检测是否为空文件夹
		
		@var:$dir  目录名
		
		@return:   存在则返回true
	*/

    protected static function empty_dir($dir)
    {
        return (($files = @scandir($dir)) && count($files) <= 2);
    }
}
