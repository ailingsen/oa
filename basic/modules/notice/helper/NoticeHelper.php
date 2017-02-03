<?php

namespace app\modules\notice\helper;

//控制器辅助类

class NoticeHelper {

    const PAGE_SIZE1 =10;

    /**
     * 处理翻页数据
     */
    public static function setPage($type,$page)
    {
        $res=['offset'=>0,'limit'=>self::PAGE_SIZE1];
        if($type==1){
            $res['offset'] = self::PAGE_SIZE1 * ($page - 1);
            $res['limit'] =self::PAGE_SIZE1;
        }
        return $res;
    }

    /**
     * 处理保存附件的格式
    */
    public static function setAtt($att,$notice_id)
    {
        $res = [];
        foreach($att as $key=>$val){
            $res[$key]['notice_id'] = $notice_id;
            $res[$key]['file_name'] = $val['file_name'];
            $res[$key]['real_name'] = $val['real_name'];
            $res[$key]['file_size'] = $val['file_size'];
            $res[$key]['file_path'] = $val['file_path'];
            $res[$key]['file_type'] = $val['file_type'];
            $res[$key]['create_time'] = time();
        }
        return $res;
    }

    /**
     * 处理是否有附件已经时间转换
    */
    public static function setData($data)
    {
        foreach($data as $key=>$val){
            if(count($val['att'])>0){
                $data[$key]['isatt'] = 1;
            }else{
                $data[$key]['isatt'] = 0;
            }
            $data[$key]['create_time_f'] = date('Y-m-d H:i:s',$val['create_time']);
        }
        return $data;
    }

    public static function setIsNew($data)
    {
        $today = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        foreach($data as $key=>$val){
            if($val['create_time']>$today){
                $data[$key]['is_new'] = 1;
            }else{
                $data[$key]['is_new'] = 0;
            }
            $data[$key]['create_time'] = date('Y-m-d H:i:s', $data[$key]['create_time'] );
        }
        return $data;
    }

    //附件下载
    public static function getDownFile($filepath,$file_name)
    {
        $filepath=iconv("utf-8","gb2312",$filepath);
        if(!file_exists($filepath)) {
            return 1;
        }
        header("Content-type: text/html; charset=utf-8");
        //$file = fopen($filepath, "r");
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Content-Disposition: attachment; filename=\"" .$file_name ."\"");
        ob_clean();
        flush();
        readfile($filepath);
        //echo fread($file,$size);
        //fclose($file);
    }
    
}