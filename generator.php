<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// 创建上传文件夹
if (!file_exists('data/upload')) {
    mkdir('data/upload');
}

// 获取单词表文件
$glossaryFile = $_FILES['glossary'];

if (
    $_SERVER['REQUEST_METHOD'] == 'POST'
    && $glossaryFile
    && $glossaryFile['type'] == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
) {
    // 保存临时单词表文件
    $glossaryTmpFile = 'data/upload/' . time() . '.xlsx';
    move_uploaded_file($glossaryFile['tmp_name'], $glossaryTmpFile);
    // 读取临时单词表文件
    $inputFileType = 'Xlsx';
    $sheetName = '単語リスト';
    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
    $reader->setReadDataOnly(true);
    $reader->setLoadSheetsOnly($sheetName);
    $spreadsheet = $reader->load($glossaryTmpFile);
    // 读入全部单词
    $glossary = [];
    foreach ($spreadsheet->getActiveSheet()->rangeToArray('E6:E2310') as $row) {
        $glossary[] = $row[0];
    }
    // 读入全部例句
    $sentences = [];
    foreach ($spreadsheet->getActiveSheet()->rangeToArray('H6:H2310') as $row) {
        $sentences[] = $row[0];
    }
    // 决定生成问题
    $questionNos = [];
    while (count($questionNos) < $_POST['quantity']) {
        $questionNo = mt_rand($_POST['from'], $_POST['to']) - 1;
        if (!in_array($questionNo, $questionNos)) {
            $questionNos[] = $questionNo;
        }
    }
    // 生成决定问题
    $questions = [];
    foreach ($questionNos as $questionNo) {
        // 生成填空
        $word = $glossary[$questionNo];
        $blank = '（';
        for ($i = 0; $i < mb_strlen($word); $i++) {
            $blank .= '　';
        }
        $blank .= '）';
        // 查找相似词作为选项候选
        $candidates = [];
        foreach ($glossary as $curWord) {
            if (
                mb_strlen($curWord) == mb_strlen($word)
                // && mb_substr($curWord, 0, 1) == mb_substr($word, 0, 1)
            ) {
                $candidates[] = $curWord;
            }
            if (count($candidates) == 4) {
                break;
            }
        }
        shuffle($candidates);
        // 生成选项
        $options = [];
        $answer = mt_rand(1, 4);
        for ($i = 0; $i < 4; $i++) {
            if ($i == $answer - 1) {
                $options[] = $word;
            } else {
                do {
                    $option = array_shift($candidates);
                    if ($option != $word && !in_array($option, $options)) {
                        $options[] = $option;
                        break;
                    }
                } while (true);
            }
        }
        // 生成题目
        $question = [
            preg_replace('/' . $word . '/i', $blank, $sentences[$questionNo]),
            $options[0],
            $options[1],
            $options[2],
            $options[3],
            // 时间限制: 20秒
            20,
            $answer,
        ];
        $questions[] = $question;
    }
    // 输出题目
    echo json_encode($questions, JSON_UNESCAPED_UNICODE);
    // 删除临时单词表文件
    unlink($glossaryTmpFile);
} else {
    // GET
    header('Location: index.php');
}
