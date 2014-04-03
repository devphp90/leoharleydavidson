<?php


/*
    DivX WebPlayer PHP Light Video Streaming Class with implemented range support
    Source: ruTorrent File Manager plugin
                    HWK [at] robits.org
*/

class VSTREAM { 

    private $mime_types = array
            (
                'divx' => 'video/divx',
                'mp4' => 'video/mp4',
                'avi' => 'video/x-msvideo',
                'mpeg' => 'video/mpeg',
                'mkv' => 'video/x-matroska');


    public function fext($file) {
        return (pathinfo($file, PATHINFO_EXTENSION));
    }
    

   private function send_file($file, $large = FALSE) {
        global $HTTP_SERVER_VARS;

        set_time_limit(0);
        error_reporting (0);

        if ($large) {
            passthru('cat '.escapeshellarg($file), $err); 
        } else { 

            $seek_start=0;
            $seek_end=-1;
            $fs = filesize($file);

            if (ob_get_length() === false) {ob_start();}

            if(isset($_SERVER['HTTP_RANGE']) || isset($HTTP_SERVER_VARS['HTTP_RANGE'])) { 
 
                $seek_range = isset($HTTP_SERVER_VARS['HTTP_RANGE']) ? substr($HTTP_SERVER_VARS['HTTP_RANGE'] , strlen('bytes=')) : substr($_SERVER['HTTP_RANGE'] , strlen('bytes='));
                $range=explode('-',$seek_range); 

                if($range[0] > 0) {$seek_start = intval($range[0]); }

                $seek_end = ($range[1] > 0) ? intval($range[1]) : -1;


                    header('HTTP/1.0 206 Partial Content'); 
                    header('Status: 206 Partial Content'); 
                    header('Accept-Ranges: bytes'); 
                    header("Content-Range: bytes $seek_start-$seek_end/".$fs); 

                    
            } 

        if($seek_end < $seek_start) {$seek_end=$fs - 1;}
        $cl = $seek_end - $seek_start + 1;

        header('Expires: 0');  
        header('Pragma: public');  
        header('Cache-Control: must-revalidate');  
        
        header('Content-Length: '.$cl);
            
            
            ob_flush();

            $fo = fopen($file, 'rb');

                fseek($fo, $seek_start);

            while(!feof($fo)){
                    set_time_limit(0);
                    print(fread($fo, 1024*8));
                    ob_flush();
                    flush();
                }

                fclose($fo);
        }
        
        exit;
    }



    public function stream($file) {
        
        
        $extension = $this->fext($file);

        if (!isset($this->mime_types[$extension])) {die('404 Invalid format');}

        if (!is_file($file)) {die('404 File not found');}

        header('Content-Type: '.$this->mime_types[$extension]);
        header('Content-Disposition: inline; filename="'.basename($file).'"');

        $this->send_file($file);
    }

}



/*
 Working example:
$st = new VSTREAM();
$st->stream('/home/storage/videos/video.mkv');        // your video file
 
*/ 
