<?php

class ClassLoader {
    // class ファイルがあるディレクトリのリスト
    private static $dirs;

    /**
     * クラスが見つからなかった場合呼び出されるメソッド
     * spl_autoload_register でこのメソッドを登録してください
     * @param  string $class 名前空間など含んだクラス名
     * @return bool 成功すればtrue
     */
    public static function loadClass($class){
    	echo "test:" . $class . "\n";
        foreach (self::directories() as $directory) {
            $file_name = $directory . "/". $class . ".php";
            
            if (is_file($file_name)) {
                require $file_name;

                return true;
            }
        }
    }

    /**
     * ディレクトリリスト
     * @return array フルパスのリスト
     */
    private static function directories()
    {
        if (empty(self::$dirs)) {
            $base = dirname(__DIR__);
            self::$dirs = array(
                // ここに読み込んでほしいディレクトリを足していきます
                $base,
                $base . "/provider",
                $base . "/utils",
                $base . "/parser"
            );
        }

        return self::$dirs;
    }

    public static function register() {
    	spl_autoload_register(array('ClassLoader', 'loadClass'));
    }
}