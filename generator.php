<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// 创建上传文件夹
if (!file_exists('data/upload')) {
    mkdir('data/upload');
}

function getCandidates($answerWord)
{
    $candidates = [];
    shuffle($candidates);
    return $candidates;
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
        // 生成填空题面
        $answerWord = $spreadsheet->getActiveSheet()->getCell('E' . (6 + $questionNo))->getValue();
        $sentence = $spreadsheet->getActiveSheet()->getCell('H' . (6 + $questionNo))->getValue();
        $blank = '（';
        for ($i = 0; $i < mb_strlen($answerWord); $i++) {
            $blank .= '　';
        }
        $blank .= '）';
        $questionDescription = preg_replace('/' . $answerWord . '/i', $blank, $sentence);
        // 查找相似词作为选项候选
        $candidates = getCandidates($answerWord);
        // 生成选项
        $options = [];
        $answerOption = mt_rand(1, 4);
        for ($i = 1; $i <= 4; $i++) {
            if ($i == $answerOption) {
                $options[] = $answerWord;
            } else {
                do {
                    $option = array_shift($candidates);
                    if (!$option) {
                        $options[] = '';
                        break;
                    } elseif ($option != $answerWord && !in_array($option, $options)) {
                        $options[] = $option;
                        break;
                    }
                } while (true);
            }
        }
        // 生成题目
        $question = [
            $questionDescription,
            $options[0],
            $options[1],
            $options[2],
            $options[3],
            20, // 时间限制: 20秒
            $answerOption,
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
