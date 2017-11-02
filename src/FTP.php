<?php

namespace Apility;

class FTP
{
    /**
     * @var resource
     */
    private $connection;

    /**
     * Instantiate a FTP instance
     *
     * @param string $host
     * @param string $user = null
     * @param string $password = null
     * @param int $port = 21
     * @param int $timeout = 90
     */
    public function __construct($host, $user = null, $password = null, int $port = 21, $timeout = 90)
    {
        if (!$this->connection = ftp_connect($host, $port, $timeout)) {
            throw new Exception('Unable to establish connection to ' . $host . ':' . $port);
        }
        ftp_pasv($this->connection, true);
        if (!is_null($user) && !is_null($password)) {
            $this->login($user, $password);
        }
    }

    /**
     * Authenticates the FTP session
     *
     * @param string $user
     * @param string $password
     */
    public function login($user, $password)
    {
        return ftp_login($this->connection, $user, $password);
    }

    /**
     * Downloads a file
     *
     * @param string $path
     * @param int $mode = FTP_ASCII
     * @throws Exception
     */
    public function download($path, $mode = FTP_ASCII)
    {
        $tmp = tmpfile();
        if (!ftp_get($this->connection, $tmp, $path, $mode)) {
            throw new Exception('Download of ' . $path . 'failed');
        };
        $data = fread($tmp, filesize(stream_get_meta_data($tmp)['uri']));
        fclose($tmp);
        return $data;
    }

    /**
     * Uploads a file
     *
     * @param string $data
     * @param string $path
     * @param int $mode = FTP_ASCII
     * @throws Exception
     */
    public function upload($data = '', $path = null, $mode = FTP_ASCII)
    {
        $file;
        if (file_exists($data)) {
            $file = fopen($data);
        } else {
            $file = tmpfile();
            fwrite($file, $data);
        }
        $path = is_null($path) ? $name : $path;
        if (!ftp_put($this->connection, $path, stream_get_meta_data($file)['uri'], $mode)) {
            ftp_pasv($this->connection, false);
            if (!ftp_put($this->connection, $path, stream_get_meta_data($file)['uri'], $mode)) {
                throw new Exception('Upload to ' . $path . 'failed');
            }
        };
        fclose($file);
        return true;
    }

    /**
     * Class destructor
     */
    public function __destruct()
    {
        ftp_close($this->connection);
    }
}
