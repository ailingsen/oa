<?php
/**
 * Created by PhpStorm.
 * User: pengyanzhang
 * Date: 2016/8/19
 * Time: 15:43
 */

namespace app\lib;


class OutExcel
{
    //输出Excel
    public function getExcel($filename, $headArr, $data) {
        if (empty($data) || !is_array($data)) {
            die("data must be a array");
        }
        if (empty($filename)) {
            die("filename is empty");
        }
        $date = date("Ymd", time());
        $filename .= "_{$date}.xls";
        require dirname(dirname(__FILE__)) . '/vendor/phpexcel/PHPExcel.php';
        //创建新的PHPExcel对象
        $objPHPExcel = new \PHPExcel();
        //设置列名
        $key = ord("A");
        $key2 = ord("@");
        foreach ($headArr as $value) {
            if($key>ord("Z")){
                $key2 += 1;
                $key = ord("A");
                $colum = chr($key2).chr($key);//超过26个字母时才会启用
            }else{
                if($key2>=ord("A")){
                    $colum = chr($key2).chr($key);//超过26个字母时才会启用
                }else{
                    $colum = chr($key);
                }
            }
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $value);
            $key += 1;
        }
        //设置列值
        $column = 2;
        $objActSheet = $objPHPExcel->getActiveSheet();
        foreach ($data as $key => $rows) { //行写入
            $span = ord("A");
            $span2 = ord("@");
            foreach ($rows as $k => $value) {// 列写入
                if ($k !== 'id') {
                    if($span>ord("Z")){
                        $span2 += 1;
                        $span = ord("A");
                        $j = chr($span2).chr($span);//超过26个字母时才会启用
                    }else{
                        if($span2>=ord("A")){
                            $j = chr($span2).chr($span);//超过26个字母时才会启用
                        }else{
                            $j = chr($span);
                        }
                    }
                    $objActSheet->setCellValue($j . $column, $value);
                    $span++;
                }
            }
            $column++;
        }
        require dirname(dirname(__FILE__)) . '/vendor/phpexcel/PHPExcel/IOFactory.php';
        //输出文件
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        // 从浏览器直接输出$filename
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type: application/vnd.ms-excel;");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header("Content-Disposition:attachment;filename=" . $filename);
        header("Content-Transfer-Encoding:binary");
        $objWriter->save("php://output");
    }
}