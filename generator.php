<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// 创建上传文件夹
if (!file_exists('data/upload')) {
    mkdir('data/upload');
}

function getCandidates($answer)
{
    $candidates = [];
    try {
        // 创建连接
        $config = json_decode(file_get_contents('data/database/config.json'), true);
        $host = $config['host'];
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];
        $pdo = new PDO("mysql:host={$host};dbname={$database}", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // 生成查询
        $head = mb_substr($answer, 0, 1);
        $tail = mb_substr($answer, -1);
        if (mb_strlen($answer) == 1) {
            // 单字词
            $sql = <<<sql
    select `word` from `glossary`
    where length(`word`) = 3
        and `word` <> "{$answer}"
    limit 10;
sql;
        } else {
            // 多字词
            $sql = <<<sql
    select `word` from `glossary`
    where length(`word`) > 3
        and `word` <> "{$answer}"
        and (
            `word` like "%{$head}%"
            or`word` like "%{$tail}%"
        )
    limit 10;
sql;
        }
        $result = $pdo->prepare($sql);
        // 获取数据
        $result->execute();
        $dataSet = $result->fetchAll(PDO::FETCH_ASSOC);
        foreach ($dataSet as $data) {
            $candidates[] = $data['word'];
        }
    } catch (PDOException $e) {
        // 查询错误
        $candidates[] = '[错误:' . $e->getCode() . ']';
    } finally {
        $pdo = null;
    }
    // 补足选项 
    while (count($candidates) < 4) {
        $candidates[] = '[无数据]';
    }
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
    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
    $reader->setLoadSheetsOnly('単語リスト');
    $reader->setReadDataOnly(true);
    $spreadSheet = $reader->load($glossaryTmpFile);
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
        $answerWord = $spreadSheet->getActiveSheet()->getCell('E' . (6 + $questionNo))->getValue();
        $sentence = $spreadSheet->getActiveSheet()->getCell('H' . (6 + $questionNo))->getValue();
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
                $options[] = array_shift($candidates);
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
    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
    $reader->setLoadSheetsOnly('Sheet1');
    $spreadSheet = $reader->load('data/template/KahootQuizTemplate.xlsx');
    $spreadSheet->getActiveSheet()->fromArray($questions, NULL, 'B9');
    // 下载结果
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="KahootQuizTemplate.xlsx"');
    header('Cache-Control: max-age=0');
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadSheet, 'Xlsx');
    $writer->save('php://output');
    // 善后工作
    $writer = $reader = null;
    unlink($glossaryTmpFile);
} else {
    // GET
    header('Location: index.php');
}
